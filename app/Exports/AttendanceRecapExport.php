<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\FieldDuty;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;

class AttendanceRecapExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected $userId;
    protected $month;
    protected $userName;
    protected $userNip;
    protected $userPosition;
    protected $userDepartment;

    public function __construct($userId, $month)
    {
        $this->userId = $userId;
        $this->month = $month;
        
        $user = User::find($userId);
        if ($user) {
            $this->userName = $user->name;
            $this->userNip = $user->nip;
            $this->userPosition = $user->position;
            $this->userDepartment = $user->department;
        }
    }

    public function collection()
    {
        $startDate = Carbon::parse($this->month . '-01')->startOfMonth();
        $endDate = Carbon::parse($this->month . '-01')->endOfMonth();

        $attendances = Attendance::where('user_id', $this->userId)
            ->whereYear('date', $startDate->year)
            ->whereMonth('date', $startDate->month)
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });

        $fieldDuties = FieldDuty::where('user_id', $this->userId)
            ->where('status', 'approved')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                          ->where('end_date', '>=', $endDate);
                    });
            })
            ->get();

        $leaves = Leave::where('user_id', $this->userId)
            ->where('status', 'approved')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                          ->where('end_date', '>=', $endDate);
                    });
            })
            ->get();

        $data = collect();
        $current = $startDate->copy();
        
        while ($current <= $endDate) {
            $dateString = $current->format('Y-m-d');
            
            if ($current->isWeekend()) {
                $current->addDay();
                continue;
            }

            $isFieldDuty = $fieldDuties->first(function ($duty) use ($current) {
                return $current->between(
                    Carbon::parse($duty->start_date),
                    Carbon::parse($duty->end_date)
                );
            });

            $isLeave = $leaves->first(function ($leave) use ($current) {
                return $current->between(
                    Carbon::parse($leave->start_date),
                    Carbon::parse($leave->end_date)
                );
            });

            $row = [
                'date' => $current,
                'day_name' => $current->locale('id')->dayName,
                'status' => 'absent',
                'check_in' => null,
                'check_out' => null,
                'working_hours' => null,
                'description' => null,
            ];

            if ($isFieldDuty) {
                $row['status'] = 'field_duty';
                $row['description'] = $isFieldDuty->purpose;
            } elseif ($isLeave) {
                $row['status'] = 'leave';
                $row['description'] = $isLeave->reason;
            } elseif (isset($attendances[$dateString])) {
                $attendance = $attendances[$dateString];
                $checkInTime = Carbon::parse($attendance->check_in_time);
                $isLate = $checkInTime->format('H:i:s') > '08:00:00';
                
                $row['status'] = $isLate ? 'late' : 'present';
                $row['check_in'] = $attendance->check_in_time;
                $row['check_out'] = $attendance->check_out_time;
                $row['working_hours'] = $attendance->working_hours;
            }

            $data->push((object) $row);
            $current->addDay();
        }

        return $data;
    }

    public function map($row): array
    {
        $statusLabels = [
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'absent' => 'Tidak Hadir',
            'field_duty' => 'Dinas Luar',
            'leave' => 'Izin/Cuti',
        ];

        return [
            $row->date->format('d/m/Y'),
            $row->day_name,
            $statusLabels[$row->status] ?? $row->status,
            $row->check_in ? substr($row->check_in, 0, 5) : '-',
            $row->check_out ? substr($row->check_out, 0, 5) : '-',
            $row->working_hours ? substr($row->working_hours, 0, 5) : '-',
            $row->description ?? '-',
        ];
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Hari',
            'Status',
            'Jam Masuk',
            'Jam Keluar',
            'Jam Kerja',
            'Keterangan',
        ];
    }

    public function title(): string
    {
        $monthName = Carbon::parse($this->month . '-01')->locale('id')->monthName;
        return substr($this->userName, 0, 20) . ' - ' . $monthName;
    }

    public function styles(Worksheet $sheet)
    {
        $monthName = Carbon::parse($this->month . '-01')->locale('id')->monthName;
        $year = Carbon::parse($this->month . '-01')->year;
        
        return [
            // Title row (row 1)
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2e7d32']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
            // Info rows (2-5)
            'A2:B5' => [
                'font' => ['size' => 10],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ],
            // Header row (row 7)
            7 => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2e7d32']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,  // Tanggal
            'B' => 12,  // Hari
            'C' => 15,  // Status
            'D' => 12,  // Jam Masuk
            'E' => 12,  // Jam Keluar
            'F' => 12,  // Jam Kerja
            'G' => 40,  // Keterangan
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                
                // Merge title
                $sheet->mergeCells('A1:G1');
                $sheet->setCellValue('A1', 'REKAPITULASI ABSENSI');
                $sheet->getRowDimension(1)->setRowHeight(30);
                
                // Add info
                $monthName = Carbon::parse($this->month . '-01')->locale('id')->monthName;
                $year = Carbon::parse($this->month . '-01')->year;
                
                $sheet->setCellValue('A2', 'Nama');
                $sheet->setCellValue('B2', ': ' . $this->userName);
                $sheet->setCellValue('A3', 'NIP');
                $sheet->setCellValue('B3', ': ' . $this->userNip);
                $sheet->setCellValue('A4', 'Jabatan');
                $sheet->setCellValue('B4', ': ' . $this->userPosition);
                $sheet->setCellValue('A5', 'Periode');
                $sheet->setCellValue('B5', ': ' . $monthName . ' ' . $year);
                
                // Make info bold
                $sheet->getStyle('A2:A5')->getFont()->setBold(true);
                
                // Add borders to all data cells
                $sheet->getStyle('A7:' . $highestColumn . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
                
                // Center align for specific columns
                $sheet->getStyle('A8:F' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Left align for keterangan column
                $sheet->getStyle('G8:G' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                
                // Wrap text for keterangan
                $sheet->getStyle('G8:G' . $highestRow)->getAlignment()->setWrapText(true);
                
                // Set row height for data rows
                for ($i = 8; $i <= $highestRow; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(20);
                }
            },
        ];
    }
}
