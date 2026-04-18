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
}
