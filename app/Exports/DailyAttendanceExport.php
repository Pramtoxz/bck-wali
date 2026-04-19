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
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class DailyAttendanceExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected $date;

    public function __construct($date)
    {
        $this->date = $date;
    }

    public function collection()
    {
        $users = User::orderBy('name')->get();
        $data = collect();

        foreach ($users as $user) {
            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('date', $this->date)
                ->first();

            $status = 'absent';
            $checkIn = null;
            $checkOut = null;
            $workingHours = null;

            if ($attendance) {
                $checkInTime = Carbon::parse($attendance->check_in_time);
                $isLate = $checkInTime->format('H:i:s') > '08:00:00';
                $status = $isLate ? 'late' : 'present';
                $checkIn = $attendance->check_in_time;
                $checkOut = $attendance->check_out_time;
                $workingHours = $attendance->working_hours;
            }

            $data->push((object) [
                'user' => $user,
                'status' => $status,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'working_hours' => $workingHours,
            ]);
        }

        return $data;
    }

    public function map($row): array
    {
        $statusLabels = [
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'absent' => 'Tidak Hadir',
        ];

        return [
            $row->user->name,
            $row->user->nip,
            $row->user->position,
            $row->user->department,
            $statusLabels[$row->status] ?? $row->status,
            $row->check_in ? substr($row->check_in, 0, 5) : '-',
            $row->check_out ? substr($row->check_out, 0, 5) : '-',
            $row->working_hours ? substr($row->working_hours, 0, 5) : '-',
        ];
    }

    public function headings(): array
    {
        return [
            'Nama',
            'NIP',
            'Jabatan',
            'Departemen',
            'Status',
            'Jam Masuk',
            'Jam Keluar',
            'Jam Kerja',
        ];
    }

    public function title(): string
    {
        return 'Absensi ' . Carbon::parse($this->date)->format('d-m-Y');
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
            'E' => 15,
            'F' => 12,
            'G' => 12,
            'H' => 12,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                
                $sheet->mergeCells('A1:H1');
                $dateFormatted = Carbon::parse($this->date)->locale('id')->isoFormat('dddd, D MMMM YYYY');
                $sheet->setCellValue('A1', 'ABSENSI HARIAN - ' . strtoupper($dateFormatted));
                $sheet->getRowDimension(1)->setRowHeight(30);
                
                $sheet->getStyle('A3:H' . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
                
                $sheet->getStyle('E4:H' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A4:D' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                
                $sheet->getRowDimension(3)->setRowHeight(25);
                for ($i = 4; $i <= $highestRow; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(20);
                }
            },
        ];
    }
}
