<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $perPage = $request->query('per_page', 10);
        $date = $request->query('date', now()->format('Y-m-d'));

        $users = \App\Models\User::role('user')
            ->with([
                'attendances' => function ($query) use ($date) {
                    $query->whereDate('date', $date);
                },
            ])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                        ->orWhere('username', 'ilike', "%{$search}%")
                        ->orWhere('nip', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage);

        $fieldDuties = \App\Models\FieldDuty::with('user')
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->get()
            ->keyBy('user_id');

        $leaves = \App\Models\Leave::with('user')
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->get()
            ->keyBy('user_id');

        $usersData = $users->through(function ($user) use ($fieldDuties, $leaves) {
            $attendance = $user->attendances->first();
            $fieldDuty = $fieldDuties->get($user->id);
            $leave = $leaves->get($user->id);

            $status = 'absent';
            $statusLabel = 'Tidak Hadir';
            $statusColor = 'secondary';
            $description = null;

            if ($fieldDuty) {
                $status = 'field_duty';
                $statusLabel = 'Dinas Luar';
                $statusColor = 'warning';
                $description = $fieldDuty->destination;
            } elseif ($leave) {
                $status = 'leave';
                $statusLabel = ucfirst($leave->type);
                $statusColor = 'info';
                $description = $leave->reason;
            } elseif ($attendance) {
                if ($attendance->check_in_time && $attendance->check_out_time) {
                    $checkInTime = \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i:s');
                    $isLate = $checkInTime > '08:00:00';
                    $status = $isLate ? 'late' : 'present';
                    $statusLabel = $isLate ? 'Terlambat' : 'Hadir';
                    $statusColor = $isLate ? 'destructive' : 'success';
                } elseif ($attendance->check_in_time) {
                    $checkInTime = \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i:s');
                    $isLate = $checkInTime > '08:00:00';
                    $status = 'checked_in';
                    $statusLabel = $isLate ? 'Check In (Terlambat)' : 'Check In';
                    $statusColor = $isLate ? 'destructive' : 'warning';
                }
            }

            return [
                'id' => $user->id,
                'name' => $user->name,
                'nip' => $user->nip,
                'position' => $user->position,
                'department' => $user->department,
                'avatar' => $user->avatar ?? asset('images/logo-transparan.png'),
                'attendance' => $attendance ? [
                    'id' => $attendance->id,
                    'check_in_time' => $attendance->check_in_time,
                    'check_out_time' => $attendance->check_out_time,
                    'working_hours' => $attendance->working_hours,
                ] : null,
                'status' => $status,
                'status_label' => $statusLabel,
                'status_color' => $statusColor,
                'description' => $description,
            ];
        });

        $summary = [
            'total' => $users->total(),
            'present' => $usersData->where('status', 'present')->count(),
            'late' => $usersData->where('status', 'late')->count(),
            'checked_in' => $usersData->where('status', 'checked_in')->count(),
            'field_duty' => $usersData->where('status', 'field_duty')->count(),
            'leave' => $usersData->where('status', 'leave')->count(),
            'absent' => $usersData->where('status', 'absent')->count(),
        ];

        return Inertia::render('attendances/index', [
            'users' => $usersData,
            'summary' => $summary,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
                'date' => $date,
            ],
        ]);
    }

    public function show($id)
    {
        $attendance = Attendance::with('user')->findOrFail($id);

        return Inertia::render('attendances/show', [
            'attendance' => [
                'id' => $attendance->id,
                'date' => $attendance->date->format('Y-m-d'),
                'day_name' => $attendance->date->locale('id')->dayName,
                'user' => [
                    'id' => $attendance->user->id,
                    'name' => $attendance->user->name,
                    'nip' => $attendance->user->nip,
                    'position' => $attendance->user->position,
                    'department' => $attendance->user->department,
                ],
                'check_in_time' => $attendance->check_in_time,
                'check_out_time' => $attendance->check_out_time,
                'check_in_photo' => $attendance->check_in_photo ? asset('storage/' . $attendance->check_in_photo) : null,
                'check_out_photo' => $attendance->check_out_photo ? asset('storage/' . $attendance->check_out_photo) : null,
                'check_in_location' => [
                    'latitude' => $attendance->check_in_latitude,
                    'longitude' => $attendance->check_in_longitude,
                ],
                'check_out_location' => $attendance->check_out_latitude ? [
                    'latitude' => $attendance->check_out_latitude,
                    'longitude' => $attendance->check_out_longitude,
                ] : null,
                'working_hours' => $attendance->working_hours,
                'is_late' => $attendance->check_in_time && \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i:s') > '08:00:00',
            ],
        ]);
    }
}
