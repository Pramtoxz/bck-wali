<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\OfficeLocation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function getOfficeLocation()
    {
        $office = OfficeLocation::where('is_active', true)->first();

        if (!$office) {
            return ApiResponse::error('Office location not configured', null, 404);
        }

        return ApiResponse::success([
            'latitude' => $office->latitude,
            'longitude' => $office->longitude,
            'radius' => $office->radius,
            'name' => $office->name,
        ]);
    }

    public function checkIn(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'photo' => 'required|file|mimes:jpg,jpeg|max:5120',
        ]);

        $user = $request->user();
        $today = Carbon::today()->toDateString();

        $existingAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($existingAttendance && $existingAttendance->check_in_time) {
            return ApiResponse::error('Anda sudah melakukan check in hari ini', null, 400);
        }

        $currentTime = Carbon::now('Asia/Jakarta');
        $checkInStart = Carbon::parse('04:00:00', 'Asia/Jakarta');
        $checkInEnd = Carbon::parse('10:00:00', 'Asia/Jakarta');

        if ($currentTime->lt($checkInStart) || $currentTime->gt($checkInEnd)) {
            return ApiResponse::error('Check in hanya dapat dilakukan antara jam 06:00 - 10:00 WIB', null, 400);
        }

        $office = OfficeLocation::where('is_active', true)->first();
        if (!$office) {
            return ApiResponse::error('Office location not configured', null, 404);
        }

        $distance = $this->calculateDistance(
            $validated['latitude'],
            $validated['longitude'],
            $office->latitude,
            $office->longitude
        );

        if ($distance > $office->radius) {
            return ApiResponse::error(
                'Anda berada di luar radius kantor. Jarak: ' . round($distance) . 'm, Radius: ' . $office->radius . 'm',
                null,
                400
            );
        }

        $photoPath = $request->file('photo')->store('attendance/check-in', 'public');

        $attendance = Attendance::updateOrCreate(
            [
                'user_id' => $user->id,
                'date' => $today,
            ],
            [
                'check_in_time' => $currentTime->format('H:i:s'),
                'check_in_photo' => $photoPath,
                'check_in_latitude' => $validated['latitude'],
                'check_in_longitude' => $validated['longitude'],
            ]
        );

        return ApiResponse::success([
            'id' => $attendance->id,
            'user_id' => $attendance->user_id,
            'check_in_time' => $attendance->check_in_time,
            'check_in_photo' => asset('storage/' . $attendance->check_in_photo),
            'check_in_latitude' => $attendance->check_in_latitude,
            'check_in_longitude' => $attendance->check_in_longitude,
            'date' => $attendance->date->format('Y-m-d'),
            'created_at' => $attendance->created_at->toIso8601String(),
        ], 'Check in berhasil', 201);
    }

    public function checkOut(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'photo' => 'required|file|mimes:jpg,jpeg|max:5120',
        ]);

        $user = $request->user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$attendance || !$attendance->check_in_time) {
            return ApiResponse::error('Anda belum melakukan check in hari ini', null, 400);
        }

        if ($attendance->check_out_time) {
            return ApiResponse::error('Anda sudah melakukan check out hari ini', null, 400);
        }

        $currentTime = Carbon::now('Asia/Jakarta');
        $checkOutStart = Carbon::parse('04:00:00', 'Asia/Jakarta');
        $checkOutEnd = Carbon::parse('20:00:00', 'Asia/Jakarta');

        if ($currentTime->lt($checkOutStart) || $currentTime->gt($checkOutEnd)) {
            return ApiResponse::error('Check out hanya dapat dilakukan antara jam 15:00 - 20:00 WIB', null, 400);
        }

        $office = OfficeLocation::where('is_active', true)->first();
        if (!$office) {
            return ApiResponse::error('Office location not configured', null, 404);
        }

        $distance = $this->calculateDistance(
            $validated['latitude'],
            $validated['longitude'],
            $office->latitude,
            $office->longitude
        );

        if ($distance > $office->radius) {
            return ApiResponse::error(
                'Anda berada di luar radius kantor. Jarak: ' . round($distance) . 'm, Radius: ' . $office->radius . 'm',
                null,
                400
            );
        }

        $photoPath = $request->file('photo')->store('attendance/check-out', 'public');

        $checkInTime = Carbon::parse($attendance->check_in_time, 'Asia/Jakarta');
        $workingSeconds = $checkInTime->diffInSeconds($currentTime);
        $workingHours = gmdate('H:i:s', $workingSeconds);

        $attendance->update([
            'check_out_time' => $currentTime->format('H:i:s'),
            'check_out_photo' => $photoPath,
            'check_out_latitude' => $validated['latitude'],
            'check_out_longitude' => $validated['longitude'],
            'working_hours' => $workingHours,
        ]);

        return ApiResponse::success([
            'id' => $attendance->id,
            'user_id' => $attendance->user_id,
            'check_in_time' => $attendance->check_in_time,
            'check_out_time' => $attendance->check_out_time,
            'check_out_photo' => asset('storage/' . $attendance->check_out_photo),
            'check_out_latitude' => $attendance->check_out_latitude,
            'check_out_longitude' => $attendance->check_out_longitude,
            'working_hours' => $attendance->working_hours,
            'date' => $attendance->date->format('Y-m-d'),
            'updated_at' => $attendance->updated_at->toIso8601String(),
        ], 'Check out berhasil');
    }

    public function today(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            return ApiResponse::success([
                'id' => null,
                'date' => $today,
                'check_in_time' => null,
                'check_out_time' => null,
                'check_in_photo' => null,
                'check_out_photo' => null,
                'working_hours' => null,
                'status' => 'not_checked_in',
            ]);
        }

        $status = 'not_checked_in';
        if ($attendance->check_in_time && $attendance->check_out_time) {
            $status = 'checked_out';
        } elseif ($attendance->check_in_time) {
            $status = 'checked_in';
        }

        return ApiResponse::success([
            'id' => $attendance->id,
            'date' => $attendance->date->format('Y-m-d'),
            'check_in_time' => $attendance->check_in_time,
            'check_out_time' => $attendance->check_out_time,
            'check_in_photo' => $attendance->check_in_photo ? asset('storage/' . $attendance->check_in_photo) : null,
            'check_out_photo' => $attendance->check_out_photo ? asset('storage/' . $attendance->check_out_photo) : null,
            'working_hours' => $attendance->working_hours,
            'status' => $status,
        ]);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;

        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLon = deg2rad($lon2 - $lon1);

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
            cos($lat1Rad) * cos($lat2Rad) *
            sin($deltaLon / 2) * sin($deltaLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
