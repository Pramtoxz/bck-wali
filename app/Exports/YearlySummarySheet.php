<?php

namespace App\Exports;

use App\Models\Attendance;
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

class YearlySummarySheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected $year;

    public function __construct($year)
    {
        $this->year = $year;
    }

    public function collection()
    {
        $users = User::orderBy('name')->get();
        $data = collect();

        foreach ($users as $user) {
            $yearlyStats = [
                'total_working_days' => 0,
                'present' => 0,
                'late' => 0,
                'absent' => 0,
            ];

            for ($month = 1; $month <= 12; $month++) {
                $startDate = Carbon::create($this->year, $month, 1)->startOfMonth();
                $endDate = Carbon::create($this->year, $month, 1)->endOfMonth();

                $attendances = Attendance::where('user_id', $user->id)
                    ->whereYear('date', $this->year)
                    ->whereMonth('date', $month)
                    ->get()
                    ->keyBy(function ($item) {
                        return Carbon::parse($item->date)->format('Y-m-d');
                    });

                $current = $startDate->copy();
                while ($current <= $endDate) {
                    if ($current->isWeekend()) {
                        $current->addDay();
                        continue;
                    }

                    $yearlyStats['total_working_days']++;
                    $dateString = $current->format('Y-m-d');

                    if (isset($attendances[$dateString])) {
                        $attendance = $attendances[$dateString];
                        $checkInTime = Carbon::parse($attendance->check_in_time);
                        $isLate = $checkInTime->format('H:i:s') > '08:00:00';
                        
                        if ($isLate) {
                            $yearlyStats['late']++;
                        } else {
                            $yearlyStats['present']++;
                        }
                    } else {
                        $yearlyStats['absent']++;
                    }

                    $current->addDay();
                }
            }

            $totalPresent = $yearlyStats['present'] + $yearlyStats['late'];
            $attendanceRate = $yearlyStats['total_working_days'] > 0 
                ? round(($totalPresent / $yearlyStats['total_working_days']) * 100, 2) 
                : 0;

            $data->push((object) [
                'user' => $user,
                'stats' => $yearlyStats,
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
            $row->stats['total_working_days'],
            $row->stats['present'],
            $row->stats['late'],
            $row->stats['absent'],
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
            'Total Hari Kerja',
            'Hadir',
            'Terlambat',
            'Tidak Hadir',
            'Tingkat Kehadiran',
        ];
    }

    public function title(): string
    {
        return 'Ringkasan Tahunan ' . $this->year;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2e7d32']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
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
            'A' => 25,
            'B' => 20,
            'C' => 20,
            'D' => 20,
            'E' => 18,
            'F' => 10,
            'G' => 12,
            'H' => 12,
            'I' => 18,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                
                $sheet->mergeCells('A1:I1');
                $sheet->setCellValue('A1', 'RINGKASAN ABSENSI TAHUNAN ' . $this->year);
                $sheet->getRowDimension(1)->setRowHeight(30);
                
                $sheet->getStyle('A3:I' . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
                
                $sheet->getStyle('E4:I' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A4:D' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                
                $sheet->getRowDimension(3)->setRowHeight(25);
                for ($i = 4; $i <= $highestRow; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(20);
                }
            },
        ];
    }
}
