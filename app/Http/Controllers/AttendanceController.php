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
        $startDate = $request->query('start_date', '');
        $endDate = $request->query('end_date', '');

        $query = Attendance::with('user')
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('username', 'ilike', "%{$search}%")
                    ->orWhere('nip', 'ilike', "%{$search}%");
            });
        }

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        $attendances = $query->paginate($perPage);

        return Inertia::render('attendances/index', [
            'attendances' => $attendances,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
                'start_date' => $startDate,
                'end_date' => $endDate,
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
