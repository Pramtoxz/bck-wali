<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->query('year', now()->year);
        $search = $request->query('search', '');
        $perPage = $request->query('per_page', 10);

        $query = Holiday::query();

        if ($year) {
            $query->whereYear('date', $year);
        }

        if ($search) {
            $query->where('name', 'ilike', "%{$search}%");
        }

        $holidays = $query->orderBy('date', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        $years = Holiday::selectRaw('EXTRACT(YEAR FROM date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return Inertia::render('holidays/index', [
            'holidays' => $holidays,
            'filters' => [
                'year' => $year,
                'search' => $search,
                'per_page' => $perPage,
            ],
            'years' => $years,
        ]);
    }

    public function create()
    {
        return Inertia::render('holidays/create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:holidays,date',
            'name' => 'required|string|max:255',
            'type' => 'required|in:national,company',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Holiday::create($validated);

        return redirect()->route('holidays.index')
            ->with('success', 'Hari libur berhasil ditambahkan');
    }

    public function edit(Holiday $holiday)
    {
        return Inertia::render('holidays/edit', [
            'holiday' => $holiday,
        ]);
    }

    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:holidays,date,' . $holiday->id,
            'name' => 'required|string|max:255',
            'type' => 'required|in:national,company',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $holiday->update($validated);

        return redirect()->route('holidays.index')
            ->with('success', 'Hari libur berhasil diupdate');
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return redirect()->route('holidays.index')
            ->with('success', 'Hari libur berhasil dihapus');
    }

    public function toggleActive(Holiday $holiday)
    {
        $holiday->update(['is_active' => !$holiday->is_active]);

        return redirect()->back()
            ->with('success', 'Status hari libur berhasil diubah');
    }
}
