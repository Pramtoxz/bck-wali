<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\OfficeLocation;

class OfficeLocationController extends Controller
{
    public function getActive()
    {
        $office = OfficeLocation::where('is_active', true)->first();

        if (!$office) {
            return ApiResponse::error('Lokasi kantor belum dikonfigurasi', null, 404);
        }

        return ApiResponse::success([
            'latitude' => $office->latitude,
            'longitude' => $office->longitude,
            'radius' => $office->radius,
            'name' => $office->name,
        ], 'Lokasi kantor berhasil Di Temukan');
    }
}
