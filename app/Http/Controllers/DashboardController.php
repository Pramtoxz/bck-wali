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
        
        $pendingFieldDuties = FieldDuty::where('status', 'pending')->count();
        $pendingLeaves = Leave::where('status', 'pending')->count();
        $pendingApprovals = $pendingFieldDuties + $pendingLeaves;
        
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
        
        $recentAttendances = Attendance::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'user_name' => $attendance->user->name,
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
                'pending_approvals' => $pendingApprovals,
                'attendance_rate' => $attendanceRate,
            ],
            'recent_attendances' => $recentAttendances,
            'recent_approvals' => $recentApprovals,
        ]);
    }
}
