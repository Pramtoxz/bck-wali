<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\OfficeLocation;
use Illuminate\Http\Request;

class OfficeLocationController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $search = $request->query('search', '');

        $query = OfficeLocation::query();

        if ($search) {
            $query->where('name', 'ilike', "%{$search}%");
        }

        $locations = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return ApiResponse::success($locations);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:1|max:1000',
            'is_active' => 'boolean',
        ]);

        if ($validated['is_active'] ?? false) {
            OfficeLocation::where('is_active', true)->update(['is_active' => false]);
        }

        $location = OfficeLocation::create($validated);

        return ApiResponse::success($location, 'Lokasi kantor berhasil ditambahkan', 201);
    }

    public function show(OfficeLocation $officeLocation)
    {
        return ApiResponse::success($officeLocation);
    }

    public function update(Request $request, OfficeLocation $officeLocation)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:1|max:1000',
            'is_active' => 'boolean',
        ]);

        if ($validated['is_active'] ?? false) {
            OfficeLocation::where('id', '!=', $officeLocation->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $officeLocation->update($validated);

        return ApiResponse::success($officeLocation, 'Lokasi kantor berhasil diperbarui');
    }

    public function destroy(OfficeLocation $officeLocation)
    {
        $officeLocation->delete();

        return ApiResponse::success(null, 'Lokasi kantor berhasil dihapus');
    }
}
