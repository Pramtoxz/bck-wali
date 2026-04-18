<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PositionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $perPage = $request->query('per_page', 10);

        $query = Position::query();

        if ($search) {
            $query->where('name', 'ilike', "%{$search}%");
        }

        $positions = $query->orderBy('name', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('positions/index', [
            'positions' => $positions,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('positions/create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:positions,name|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        Position::create($validated);

        return redirect()->route('positions.index')
            ->with('success', 'Jabatan berhasil ditambahkan');
    }

    public function edit(Position $position)
    {
        return Inertia::render('positions/edit', [
            'position' => $position,
        ]);
    }

    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:positions,name,' . $position->id,
            'description' => 'nullable|string|max:500',
        ]);

        $position->update($validated);

        return redirect()->route('positions.index')
            ->with('success', 'Jabatan berhasil diperbarui');
    }

    public function destroy(Position $position)
    {
        $position->delete();

        return redirect()->route('positions.index')
            ->with('success', 'Jabatan berhasil dihapus');
    }
}
