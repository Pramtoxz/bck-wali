<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\FieldDuty;
use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceRecapController extends Controller
{
    public function getMonthlyRecap(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        $userId = $request->user()->id;
        $month = $validated['month'];
        
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = Carbon::parse($month . '-01')->endOfMonth();

        $attendances = Attendance::where('user_id', $userId)
            ->whereYear('date', $startDate->year)
            ->whereMonth('date', $startDate->month)
            ->get()
            ->keyBy('date');

        $fieldDuties = FieldDuty::where('user_id', $userId)
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

        $leaves = Leave::where('user_id', $userId)
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

        $calendar = [];
        $details = [];
        $summary = [
            'total_days' => $endDate->day,
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
            $dayName = $current->locale('id')->dayName;
            
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
                $calendar[$dateString] = [
                    'status' => 'field_duty',
                    'description' => $isFieldDuty->purpose,
                ];
                $details[] = [
                    'date' => $dateString,
                    'day_name' => $dayName,
                    'status' => 'field_duty',
                    'description' => $isFieldDuty->purpose,
                    'check_in' => null,
                    'check_out' => null,
                    'working_hours' => null,
                    'is_late' => false,
                ];
                $summary['field_duty']++;
            } elseif ($isLeave) {
                $calendar[$dateString] = [
                    'status' => 'leave',
                    'description' => $isLeave->reason,
                ];
                $details[] = [
                    'date' => $dateString,
                    'day_name' => $dayName,
                    'status' => 'leave',
                    'description' => $isLeave->reason,
                    'check_in' => null,
                    'check_out' => null,
                    'working_hours' => null,
                    'is_late' => false,
                ];
                $summary['leave']++;
            } elseif (isset($attendances[$dateString])) {
                $attendance = $attendances[$dateString];
                $checkInTime = Carbon::parse($attendance->check_in_time);
                $isLate = $checkInTime->format('H:i:s') > '08:00:00';
                $status = $isLate ? 'late' : 'present';
                
                $calendar[$dateString] = [
                    'status' => $status,
                    'check_in' => $attendance->check_in_time,
                    'check_out' => $attendance->check_out_time,
                    'is_late' => $isLate,
                ];
                
                $details[] = [
                    'date' => $dateString,
                    'day_name' => $dayName,
                    'status' => $status,
                    'check_in' => $attendance->check_in_time,
                    'check_out' => $attendance->check_out_time,
                    'working_hours' => $attendance->working_hours,
                    'is_late' => $isLate,
                ];
                
                if ($isLate) {
                    $summary['late']++;
                } else {
                    $summary['present']++;
                }
            } else {
                $calendar[$dateString] = [
                    'status' => 'absent',
                ];
                $details[] = [
                    'date' => $dateString,
                    'day_name' => $dayName,
                    'status' => 'absent',
                    'check_in' => null,
                    'check_out' => null,
                    'working_hours' => null,
                    'is_late' => false,
                ];
                $summary['absent']++;
            }

            $current->addDay();
        }

        usort($details, function($a, $b) {
            return strcmp($b['date'], $a['date']);
        });

        return ApiResponse::success([
            'month' => $month,
            'month_name' => $startDate->locale('id')->monthName,
            'year' => $startDate->year,
            'summary' => $summary,
            'calendar' => $calendar,
            'details' => $details,
        ], 'Rekapitulasi absensi berhasil dimuat');
    }

    public function getAttendanceDetail(Request $request, $date)
    {
        $userId = $request->user()->id;
        
        $attendance = Attendance::where('user_id', $userId)
            ->where('date', $date)
            ->first();

        if (!$attendance) {
            return ApiResponse::error('Data absensi tidak ditemukan', null, 404);
        }

        $carbonDate = Carbon::parse($date);
        $isLate = $attendance->check_in_time && Carbon::parse($attendance->check_in_time)->format('H:i:s') > '08:00:00';

        return ApiResponse::success([
            'date' => $date,
            'day_name' => $carbonDate->locale('id')->dayName,
            'status' => $isLate ? 'late' : 'present',
            'check_in_time' => $attendance->check_in_time,
            'check_out_time' => $attendance->check_out_time,
            'check_in_photo' => $attendance->check_in_photo ? asset('storage/' . $attendance->check_in_photo) : null,
            'check_out_photo' => $attendance->check_out_photo ? asset('storage/' . $attendance->check_out_photo) : null,
            'check_in_location' => [
                'latitude' => $attendance->check_in_latitude,
                'longitude' => $attendance->check_in_longitude,
                'address' => 'Kantor Wali Nagari',
            ],
            'check_out_location' => $attendance->check_out_latitude ? [
                'latitude' => $attendance->check_out_latitude,
                'longitude' => $attendance->check_out_longitude,
                'address' => 'Kantor Wali Nagari',
            ] : null,
            'working_hours' => $attendance->working_hours,
            'is_late' => $isLate,
            'notes' => null,
        ], 'Detail absensi berhasil dimuat');
    }

    public function getStatistics(Request $request)
    {
        $month = $request->query('month', Carbon::now()->format('Y-m'));
        $userId = $request->user()->id;
        
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = Carbon::parse($month . '-01')->endOfMonth();

        $workingDays = 0;
        $current = $startDate->copy();
        while ($current <= $endDate) {
            if (!$current->isWeekend()) {
                $workingDays++;
            }
            $current->addDay();
        }

        $attendances = Attendance::where('user_id', $userId)
            ->whereYear('date', $startDate->year)
            ->whereMonth('date', $startDate->month)
            ->get();

        $presentDays = 0;
        $lateDays = 0;
        
        foreach ($attendances as $attendance) {
            $checkInTime = Carbon::parse($attendance->check_in_time);
            if ($checkInTime->format('H:i:s') > '08:00:00') {
                $lateDays++;
            } else {
                $presentDays++;
            }
        }

        $fieldDutyDays = FieldDuty::where('user_id', $userId)
            ->where('status', 'approved')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate]);
            })
            ->sum('total_days');

        $leaveDays = Leave::where('user_id', $userId)
            ->where('status', 'approved')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate]);
            })
            ->sum('total_days');

        $absentDays = $workingDays - $presentDays - $lateDays - $fieldDutyDays - $leaveDays;
        $totalPresent = $presentDays + $lateDays;
        $attendanceRate = $workingDays > 0 ? round(($totalPresent / $workingDays) * 100, 2) : 0;
        $punctualityRate = $totalPresent > 0 ? round(($presentDays / $totalPresent) * 100, 2) : 0;

        $previousMonth = $startDate->copy()->subMonth();
        $previousMonthStart = $previousMonth->copy()->startOfMonth();
        $previousMonthEnd = $previousMonth->copy()->endOfMonth();
        
        $previousWorkingDays = 0;
        $current = $previousMonthStart->copy();
        while ($current <= $previousMonthEnd) {
            if (!$current->isWeekend()) {
                $previousWorkingDays++;
            }
            $current->addDay();
        }
        
        $previousAttendances = Attendance::where('user_id', $userId)
            ->whereYear('date', $previousMonth->year)
            ->whereMonth('date', $previousMonth->month)
            ->count();
        
        $previousAttendanceRate = $previousWorkingDays > 0 ? round(($previousAttendances / $previousWorkingDays) * 100, 2) : 0;
        $rateChange = $attendanceRate - $previousAttendanceRate;

        return ApiResponse::success([
            'period' => [
                'month' => $startDate->locale('id')->monthName,
                'year' => $startDate->year,
                'month_number' => $startDate->month,
            ],
            'statistics' => [
                'total_working_days' => $workingDays,
                'present_days' => $presentDays,
                'late_days' => $lateDays,
                'absent_days' => max(0, $absentDays),
                'field_duty_days' => $fieldDutyDays,
                'leave_days' => $leaveDays,
                'attendance_rate' => $attendanceRate,
                'punctuality_rate' => $punctualityRate,
            ],
            'comparison' => [
                'previous_month' => [
                    'attendance_rate' => $previousAttendanceRate,
                    'change' => round($rateChange, 2),
                ],
            ],
        ], 'Statistik absensi berhasil dimuat');
    }
}
