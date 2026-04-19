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
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AllEmployeesSummarySheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected $month;

    public function __construct($month)
    {
        $this->month = $month;
    }

    public function collection()
    {
        $startDate = Carbon::parse($this->month . '-01')->startOfMonth();
        $endDate = Carbon::parse($this->month . '-01')->endOfMonth();

        $users = User::orderBy('name')->get();
        
        $data = collect();
        
        foreach ($users as $user) {
            $attendances = Attendance::where('user_id', $user->id)
                ->whereYear('date', $startDate->year)
                ->whereMonth('date', $startDate->month)
                ->get()
                ->keyBy(function ($item) {
                    return Carbon::parse($item->date)->format('Y-m-d');
                });

            $fieldDuties = FieldDuty::where('user_id', $user->id)
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

            $leaves = Leave::where('user_id', $user->id)
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

            $summary = [
                'working_days' => 0,
                'present' => 0,
                'late' => 0,
                'absent' => 0,
                'field_duty' => 0,
                'leave' => 0,
            ];

            $current = $startDate->copy();
            while ($current <= $endDate) {
                $dateString = $current->format('Y-m-d');
                
                if ($current->isWeekend()) {
                    $current->addDay();
                    continue;
                }

                $summary['working_days']++;

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

                if ($isFieldDuty) {
                    $summary['field_duty']++;
                } elseif ($isLeave) {
                    $summary['leave']++;
                } elseif (isset($attendances[$dateString])) {
                    $attendance = $attendances[$dateString];
                    $checkInTime = Carbon::parse($attendance->check_in_time);
                    $isLate = $checkInTime->format('H:i:s') > '08:00:00';
                    
                    if ($isLate) {
                        $summary['late']++;
                    } else {
                        $summary['present']++;
                    }
                } else {
                    $summary['absent']++;
                }

                $current->addDay();
            }

            $totalPresent = $summary['present'] + $summary['late'];
            $attendanceRate = $summary['working_days'] > 0 
                ? round(($totalPresent / $summary['working_days']) * 100, 2) 
                : 0;

            $data->push((object) [
                'user' => $user,
                'summary' => $summary,
                'attendance_rate' => $attendanceRate,
            ]);
        }

        return $data;
    }

    public function map($row): array
    {
        return [
            $row->user->name,
            $row->user->nip,
            $row->user->position,
            $row->user->department,
            $row->summary['working_days'],
            $row->summary['present'],
            $row->summary['late'],
            $row->summary['field_duty'],
            $row->summary['leave'],
            $row->summary['absent'],
            $row->attendance_rate . '%',
        ];
    }

    public function headings(): array
    {
        return [
            'Nama',
            'NIP',
            'Jabatan',
            'Departemen',
            'Hari Kerja',
            'Hadir',
            'Terlambat',
            'Dinas',
            'Izin/Cuti',
            'Tidak Hadir',
            'Tingkat Kehadiran',
        ];
    }

    public function title(): string
    {
        $monthName = Carbon::parse($this->month . '-01')->locale('id')->monthName;
        $year = Carbon::parse($this->month . '-01')->year;
        return 'Ringkasan ' . $monthName . ' ' . $year;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Title row
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2e7d32']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
            // Header row
            3 => [
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
            'A' => 25,  // Nama
            'B' => 20,  // NIP
            'C' => 20,  // Jabatan
            'D' => 20,  // Departemen
            'E' => 12,  // Hari Kerja
            'F' => 10,  // Hadir
            'G' => 12,  // Terlambat
            'H' => 10,  // Dinas
            'I' => 12,  // Izin/Cuti
            'J' => 12,  // Tidak Hadir
            'K' => 18,  // Tingkat Kehadiran
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                
                // Merge and set title
                $sheet->mergeCells('A1:K1');
                $monthName = Carbon::parse($this->month . '-01')->locale('id')->monthName;
                $year = Carbon::parse($this->month . '-01')->year;
                $sheet->setCellValue('A1', 'RINGKASAN ABSENSI SEMUA PEGAWAI - ' . strtoupper($monthName) . ' ' . $year);
                $sheet->getRowDimension(1)->setRowHeight(30);
                
                // Add borders to all data cells
                $sheet->getStyle('A3:' . $highestColumn . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
                
                // Center align for numeric columns
                $sheet->getStyle('E4:K' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Left align for text columns
                $sheet->getStyle('A4:D' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                
                // Set row height
                $sheet->getRowDimension(3)->setRowHeight(25);
                for ($i = 4; $i <= $highestRow; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(20);
                }
            },
        ];
    }
}
