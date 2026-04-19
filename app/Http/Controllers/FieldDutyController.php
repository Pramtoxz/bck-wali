<?php

namespace App\Http\Controllers;

use App\Models\FieldDuty;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FieldDutyController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $status = $request->query('status', '');
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);
        $perPage = $request->query('per_page', 10);

        $query = FieldDuty::with('user')
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

        if ($month && $year) {
            $query->whereYear('start_date', $year)
                ->whereMonth('start_date', $month);
        } elseif ($year) {
            $query->whereYear('start_date', $year);
        }

        $fieldDuties = $query->paginate($perPage);

        return Inertia::render('field-duties/index', [
            'fieldDuties' => $fieldDuties,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'month' => (string) $month,
                'year' => (string) $year,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function show(FieldDuty $fieldDuty)
    {
        $fieldDuty->load('user');

        return Inertia::render('field-duties/show', [
            'fieldDuty' => $fieldDuty,
        ]);
    }

    public function updateStatus(Request $request, FieldDuty $fieldDuty)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $fieldDuty->update([
            'status' => $validated['status'],
        ]);

        \App\Helpers\NotificationHelper::sendFieldDutyStatusNotification($fieldDuty, $validated['status']);

        return redirect()->route('field-duties.index')
            ->with('success', 'Status dinas berhasil diperbarui');
    }
}
