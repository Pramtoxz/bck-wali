<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\FieldDuty;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $totalUsers = User::role('user')->count();
        $totalAdmins = User::role('admin')->count();
        
        $pendingFieldDuties = FieldDuty::where('status', 'pending')->count();
        $pendingLeaves = Leave::where('status', 'pending')->count();
        $pendingApprovals = $pendingFieldDuties + $pendingLeaves;
        
        $approvedFieldDuties = FieldDuty::where('status', 'approved')->count();
        $approvedLeaves = Leave::where('status', 'approved')->count();
        $rejectedFieldDuties = FieldDuty::where('status', 'rejected')->count();
        $rejectedLeaves = Leave::where('status', 'rejected')->count();
        
        $currentMonth = Carbon::now()->format('Y-m');
        $startDate = Carbon::parse($currentMonth . '-01')->startOfMonth();
        $endDate = Carbon::parse($currentMonth . '-01')->endOfMonth();
        
        $workingDays = 0;
        $current = $startDate->copy();
        while ($current <= $endDate) {
            if (!$current->isWeekend()) {
                $workingDays++;
            }
            $current->addDay();
        }
        
        $totalAttendances = Attendance::whereYear('date', $startDate->year)
            ->whereMonth('date', $startDate->month)
            ->count();
        
        $expectedAttendances = $totalUsers * $workingDays;
        $attendanceRate = $expectedAttendances > 0 
            ? round(($totalAttendances / $expectedAttendances) * 100, 1) 
            : 0;
        
        $presentCount = Attendance::whereYear('date', $startDate->year)
            ->whereMonth('date', $startDate->month)
            ->whereRaw("check_in_time::time <= '08:00:00'")
            ->count();
        
        $lateCount = Attendance::whereYear('date', $startDate->year)
            ->whereMonth('date', $startDate->month)
            ->whereRaw("check_in_time::time > '08:00:00'")
            ->count();
        
        $attendanceChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            if (!$date->isWeekend()) {
                $dayPresent = Attendance::whereDate('date', $date)
                    ->whereRaw("check_in_time::time <= '08:00:00'")
                    ->count();
                
                $dayLate = Attendance::whereDate('date', $date)
                    ->whereRaw("check_in_time::time > '08:00:00'")
                    ->count();
                
                $attendanceChart[] = [
                    'date' => $date->format('d M'),
                    'hadir' => $dayPresent,
                    'terlambat' => $dayLate,
                ];
            }
        }
        
        $monthlyChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $monthStart = $monthDate->copy()->startOfMonth();
            $monthEnd = $monthDate->copy()->endOfMonth();
            
            $monthPresent = Attendance::whereBetween('date', [$monthStart, $monthEnd])
                ->whereRaw("check_in_time::time <= '08:00:00'")
                ->count();
            
            $monthLate = Attendance::whereBetween('date', [$monthStart, $monthEnd])
                ->whereRaw("check_in_time::time > '08:00:00'")
                ->count();
            
            $monthlyChart[] = [
                'month' => $monthDate->locale('id')->format('M'),
                'hadir' => $monthPresent,
                'terlambat' => $monthLate,
            ];
        }
        
        $departmentStats = User::role('user')
            ->selectRaw('department, COUNT(*) as count')
            ->groupBy('department')
            ->get()
            ->map(function ($item) {
                return [
                    'department' => $item->department ?? 'Tidak Ada',
                    'count' => $item->count,
                ];
            });
        
        $positionStats = User::role('user')
            ->selectRaw('position, COUNT(*) as count')
            ->groupBy('position')
            ->get()
            ->map(function ($item) {
                return [
                    'position' => $item->position ?? 'Tidak Ada',
                    'count' => $item->count,
                ];
            });
        
        $recentAttendances = Attendance::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'user_name' => $attendance->user->name,
                    'user_avatar' => $attendance->user->avatar ?? asset('images/logo-transparan.png'),
                    'date' => $attendance->date->format('d M Y'),
                    'check_in_time' => $attendance->check_in_time,
                    'check_out_time' => $attendance->check_out_time,
                    'status' => $attendance->check_in_time && Carbon::parse($attendance->check_in_time)->format('H:i:s') > '08:00:00' ? 'late' : 'present',
                ];
            });
        
        $recentApprovals = collect();
        
        $recentFieldDuties = FieldDuty::with('user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($duty) {
                return [
                    'id' => $duty->id,
                    'type' => 'field_duty',
                    'user_name' => $duty->user->name,
                    'user_avatar' => $duty->user->avatar ?? asset('images/logo-transparan.png'),
                    'description' => $duty->destination,
                    'date' => $duty->created_at->format('d M Y'),
                    'status' => $duty->status,
                ];
            });
        
        $recentLeaveRequests = Leave::with('user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($leave) {
                return [
                    'id' => $leave->id,
                    'type' => 'leave',
                    'user_name' => $leave->user->name,
                    'user_avatar' => $leave->user->avatar ?? asset('images/logo-transparan.png'),
                    'description' => ucfirst($leave->type),
                    'date' => $leave->created_at->format('d M Y'),
                    'status' => $leave->status,
                ];
            });
        
        $recentApprovals = $recentFieldDuties->concat($recentLeaveRequests)
            ->sortByDesc('date')
            ->take(5)
            ->values();

        return Inertia::render('dashboard', [
            'statistics' => [
                'total_users' => $totalUsers,
                'total_admins' => $totalAdmins,
                'pending_approvals' => $pendingApprovals,
                'attendance_rate' => $attendanceRate,
                'present_count' => $presentCount,
                'late_count' => $lateCount,
                'pending_field_duties' => $pendingFieldDuties,
                'pending_leaves' => $pendingLeaves,
                'approved_field_duties' => $approvedFieldDuties,
                'approved_leaves' => $approvedLeaves,
                'rejected_field_duties' => $rejectedFieldDuties,
                'rejected_leaves' => $rejectedLeaves,
            ],
            'charts' => [
                'attendance_weekly' => $attendanceChart,
                'attendance_monthly' => $monthlyChart,
                'department_stats' => $departmentStats,
                'position_stats' => $positionStats,
            ],
            'recent_attendances' => $recentAttendances,
            'recent_approvals' => $recentApprovals,
        ]);
    }
}
