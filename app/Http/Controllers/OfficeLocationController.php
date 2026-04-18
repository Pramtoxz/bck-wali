<?php

namespace App\Http\Controllers;

use App\Models\OfficeLocation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OfficeLocationController extends Controller
{
    public function index()
    {
        $location = OfficeLocation::where('is_active', true)->first();

        if (!$location) {
            $location = OfficeLocation::first();
        }

        return Inertia::render('office-locations/index', [
            'location' => $location,
        ]);
    }

    public function edit(OfficeLocation $officeLocation)
    {
        return Inertia::render('office-locations/edit', [
            'location' => $officeLocation,
        ]);
    }

    public function update(Request $request, OfficeLocation $officeLocation)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:1|max:1000',
            'is_active' => 'boolean',
            'map_iframe' => 'nullable|string',
        ]);

        if ($validated['is_active'] ?? false) {
            OfficeLocation::where('id', '!=', $officeLocation->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $officeLocation->update($validated);

        return redirect()->route('office-locations.index')
            ->with('success', 'Lokasi kantor berhasil diperbarui');
    }
}
