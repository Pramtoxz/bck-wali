<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\FieldDuty;
use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $userId = 2;
        $month = '2026-04';
        
        Attendance::where('user_id', $userId)->delete();
        FieldDuty::where('user_id', $userId)->delete();
        Leave::where('user_id', $userId)->delete();
        
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = Carbon::parse($month . '-01')->endOfMonth();
        
        $fieldDutyDates = ['2026-04-07', '2026-04-08', '2026-04-09'];
        $leaveDates = ['2026-04-21', '2026-04-22'];
        $absentDates = ['2026-04-14', '2026-04-28'];
        $lateDates = ['2026-04-03', '2026-04-10', '2026-04-17', '2026-04-24'];
        
        FieldDuty::create([
            'user_id' => $userId,
            'start_date' => '2026-04-07',
            'end_date' => '2026-04-09',
            'total_days' => 3,
            'destination' => 'Padang',
            'purpose' => 'Rapat koordinasi dengan Pemda Sumatera Barat',
            'document_path' => 'documents/field-duty/surat_tugas_april.pdf',
            'status' => 'approved',
            'created_at' => Carbon::parse('2026-04-04 09:00:00'),
            'updated_at' => Carbon::parse('2026-04-04 14:30:00'),
        ]);
        
        Leave::create([
            'user_id' => $userId,
            'start_date' => '2026-04-21',
            'end_date' => '2026-04-22',
            'total_days' => 2,
            'type' => 'sakit',
            'reason' => 'Demam tinggi dan perlu istirahat',
            'document_path' => 'documents/leave/surat_sakit_april.pdf',
            'status' => 'approved',
            'created_at' => Carbon::parse('2026-04-21 07:00:00'),
            'updated_at' => Carbon::parse('2026-04-21 08:15:00'),
        ]);
        
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $dateString = $current->format('Y-m-d');
            
            if ($current->isWeekend()) {
                $current->addDay();
                continue;
            }
            
            if (in_array($dateString, $fieldDutyDates) || in_array($dateString, $leaveDates)) {
                $current->addDay();
                continue;
            }
            
            if (in_array($dateString, $absentDates)) {
                $current->addDay();
                continue;
            }
            
            $isLate = in_array($dateString, $lateDates);
            
            $checkInHour = $isLate ? rand(8, 9) : rand(7, 7);
            $checkInMinute = $isLate ? rand(5, 55) : rand(0, 59);
            $checkInSecond = rand(0, 59);
            
            $checkOutHour = rand(16, 17);
            $checkOutMinute = rand(0, 59);
            $checkOutSecond = rand(0, 59);
            
            $checkInTime = sprintf('%02d:%02d:%02d', $checkInHour, $checkInMinute, $checkInSecond);
            $checkOutTime = sprintf('%02d:%02d:%02d', $checkOutHour, $checkOutMinute, $checkOutSecond);
            
            $checkIn = Carbon::parse($dateString . ' ' . $checkInTime);
            $checkOut = Carbon::parse($dateString . ' ' . $checkOutTime);
            $workingHours = $checkIn->diff($checkOut)->format('%H:%I:%S');
            
            Attendance::create([
                'user_id' => $userId,
                'date' => $dateString,
                'check_in_time' => $checkInTime,
                'check_out_time' => $checkOutTime,
                'check_in_photo' => 'attendance/checkin_' . $dateString . '.jpg',
                'check_out_photo' => 'attendance/checkout_' . $dateString . '.jpg',
                'check_in_latitude' => -0.9492 + (rand(-10, 10) / 10000),
                'check_in_longitude' => 100.3543 + (rand(-10, 10) / 10000),
                'check_out_latitude' => -0.9492 + (rand(-10, 10) / 10000),
                'check_out_longitude' => 100.3543 + (rand(-10, 10) / 10000),
                'working_hours' => $workingHours,
                'created_at' => $checkIn,
                'updated_at' => $checkOut,
            ]);
            
            $current->addDay();
        }
    }
}
