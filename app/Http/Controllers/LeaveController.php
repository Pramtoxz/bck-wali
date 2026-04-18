<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $status = $request->query('status', '');
        $type = $request->query('type', '');
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);
        $perPage = $request->query('per_page', 10);

        $query = Leave::with('user')
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('username', 'ilike', "%{$search}%")
                    ->orWhere('nip', 'ilike', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($month && $year) {
            $query->whereYear('start_date', $year)
                ->whereMonth('start_date', $month);
        } elseif ($year) {
            $query->whereYear('start_date', $year);
        }

        $leaves = $query->paginate($perPage);

        return Inertia::render('leaves/index', [
            'leaves' => $leaves,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'type' => $type,
                'month' => (string) $month,
                'year' => (string) $year,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function show(Leave $leave)
    {
        $leave->load('user');

        return Inertia::render('leaves/show', [
            'leave' => $leave,
        ]);
    }

    public function updateStatus(Request $request, Leave $leave)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $leave->update([
            'status' => $validated['status'],
        ]);

        return redirect()->route('leaves.index')
            ->with('success', 'Status izin/cuti berhasil diperbarui');
    }
}
