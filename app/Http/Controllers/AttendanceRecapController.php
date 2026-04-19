<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\FieldDuty;
use App\Models\Leave;
use App\Models\User;
use App\Exports\AttendanceRecapExport;
use App\Exports\AllEmployeesRecapExport;
use App\Exports\DailyAttendanceExport;
use App\Exports\YearlyAttendanceExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceRecapController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $userId = $request->input('user_id');
        
        $users = User::select('id', 'name', 'nip', 'position', 'department')
            ->orderBy('name')
            ->get();

        $recapData = null;
        $selectedUser = null;

        if ($userId) {
            $selectedUser = User::find($userId);
            $recapData = $this->getRecapData($userId, $month);
        }

        return Inertia::render('attendance-recap/index', [
            'users' => $users,
            'selectedUser' => $selectedUser,
            'recapData' => $recapData,
            'filters' => [
                'month' => $month,
                'user_id' => $userId,
            ],
        ]);
    }

    private function getRecapData($userId, $month)
    {
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = Carbon::parse($month . '-01')->endOfMonth();

        $attendances = Attendance::where('user_id', $userId)
            ->whereYear('date', $startDate->year)
            ->whereMonth('date', $startDate->month)
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });

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

        return [
            'month' => $month,
            'month_name' => $startDate->locale('id')->monthName,
            'year' => $startDate->year,
            'summary' => $summary,
            'calendar' => $calendar,
            'details' => $details,
        ];
    }

    public function export(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $userId = $request->input('user_id');

        if (!$userId) {
            return redirect()->back()->with('error', 'Pilih karyawan terlebih dahulu');
        }

        $user = User::find($userId);
        $monthName = Carbon::parse($month . '-01')->locale('id')->monthName;
        $year = Carbon::parse($month . '-01')->year;
        
        $filename = 'Rekap_Absensi_' . str_replace(' ', '_', $user->name) . '_' . $monthName . '_' . $year . '.xlsx';

        return Excel::download(new AttendanceRecapExport($userId, $month), $filename);
    }

    public function exportAll(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        
        $monthName = Carbon::parse($month . '-01')->locale('id')->monthName;
        $year = Carbon::parse($month . '-01')->year;
        
        $filename = 'Rekap_Absensi_Semua_Pegawai_' . $monthName . '_' . $year . '.xlsx';

        return Excel::download(new AllEmployeesRecapExport($month), $filename);
    }

    public function exportDaily(Request $request)
    {
        $date = $request->input('date', Carbon::now()->format('Y-m-d'));
        
        $dateFormatted = Carbon::parse($date)->format('d_M_Y');
        $filename = 'Absensi_Harian_' . $dateFormatted . '.xlsx';

        return Excel::download(new DailyAttendanceExport($date), $filename);
    }

    public function exportYearly(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        
        $filename = 'Rekap_Absensi_Tahunan_' . $year . '.xlsx';

        return Excel::download(new YearlyAttendanceExport($year), $filename);
    }
}
