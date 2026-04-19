<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Helpers\ImageCompressor;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\FieldDuty;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\OfficeLocation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function checkIn(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'photo' => 'required|file|mimes:jpg,jpeg,png|max:10240',
        ]);

        $user = $request->user();
        $today = Carbon::today()->toDateString();

        // Validasi holiday/weekend
        $holidayCheck = Holiday::isHoliday($today);
        if ($holidayCheck['is_holiday']) {
            return ApiResponse::error(
                'Tidak dapat melakukan absensi pada hari libur: ' . $holidayCheck['holiday_name'],
                [
                    'is_holiday' => true,
                    'holiday_name' => $holidayCheck['holiday_name'],
                    'holiday_type' => $holidayCheck['type']
                ],
                400
            );
        }

        $existingAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($existingAttendance && $existingAttendance->check_in_time) {
            return ApiResponse::error('Anda sudah melakukan check in hari ini', null, 400);
        }

        $currentTime = Carbon::now('Asia/Jakarta');
        $checkInStart = Carbon::parse('06:00:00', 'Asia/Jakarta');
        $checkInEnd = Carbon::parse('08:30:00', 'Asia/Jakarta');

        if ($currentTime->lt($checkInStart) || $currentTime->gt($checkInEnd)) {
            return ApiResponse::error('Check in hanya dapat dilakukan antara jam 06:00 - 08:30 WIB', null, 400);
        }

        $office = OfficeLocation::where('is_active', true)->first();
        if (!$office) {
            return ApiResponse::error('Lokasi kantor belum dikonfigurasi', null, 404);
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

        $compressor = new ImageCompressor(maxSizeMB: 1.9);
        $compressedPhoto = $compressor->compress($request->file('photo'));
        $photoPath = $compressedPhoto->store('attendance/check-in', 'public');

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
            'photo' => 'required|file|mimes:jpg,jpeg,png|max:10240',
        ]);

        $user = $request->user();
        $today = Carbon::today()->toDateString();

        // Validasi holiday/weekend
        $holidayCheck = Holiday::isHoliday($today);
        if ($holidayCheck['is_holiday']) {
            return ApiResponse::error(
                'Tidak dapat melakukan absensi pada hari libur: ' . $holidayCheck['holiday_name'],
                [
                    'is_holiday' => true,
                    'holiday_name' => $holidayCheck['holiday_name'],
                    'holiday_type' => $holidayCheck['type']
                ],
                400
            );
        }

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
        $checkOutStart = Carbon::parse('15:00:00', 'Asia/Jakarta');
        $checkOutEnd = Carbon::parse('17:00:00', 'Asia/Jakarta');

        if ($currentTime->lt($checkOutStart) || $currentTime->gt($checkOutEnd)) {
            return ApiResponse::error('Check out hanya dapat dilakukan antara jam 15:00 - 17:00 WIB', null, 400);
        }

        $office = OfficeLocation::where('is_active', true)->first();
        if (!$office) {
            return ApiResponse::error('Lokasi kantor belum dikonfigurasi', null, 404);
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

        $compressor = new ImageCompressor(maxSizeMB: 1.9);
        $compressedPhoto = $compressor->compress($request->file('photo'));
        $photoPath = $compressedPhoto->store('attendance/check-out', 'public');

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
        $today = Carbon::today();
        $todayString = $today->toDateString();

        // Check holiday/weekend
        $holidayCheck = Holiday::isHoliday($today);
        if ($holidayCheck['is_holiday']) {
            return ApiResponse::success([
                'id' => null,
                'date' => $todayString,
                'check_in_time' => null,
                'check_out_time' => null,
                'check_in_photo' => null,
                'check_out_photo' => null,
                'working_hours' => null,
                'status' => 'holiday',
                'status_message' => $holidayCheck['holiday_name'],
                'description' => $holidayCheck['description'] ?? null,
                'is_holiday' => true,
                'holiday_type' => $holidayCheck['type'],
                'can_check_in' => false,
                'can_check_out' => false,
            ]);
        }

        // Check if user has approved field duty today
        $fieldDuty = FieldDuty::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();

        if ($fieldDuty) {
            return ApiResponse::success([
                'id' => null,
                'date' => $todayString,
                'check_in_time' => null,
                'check_out_time' => null,
                'check_in_photo' => null,
                'check_out_photo' => null,
                'working_hours' => null,
                'status' => 'field_duty',
                'status_message' => 'Sedang Dinas Luar',
                'description' => $fieldDuty->destination,
                'can_check_in' => false,
                'can_check_out' => false,
            ]);
        }

        // Check if user has approved leave today
        $leave = Leave::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();

        if ($leave) {
            $statusMessages = [
                'sakit' => 'Sedang Sakit',
                'izin' => 'Sedang Izin',
                'cuti' => 'Sedang Cuti',
            ];

            return ApiResponse::success([
                'id' => null,
                'date' => $todayString,
                'check_in_time' => null,
                'check_out_time' => null,
                'check_in_photo' => null,
                'check_out_photo' => null,
                'working_hours' => null,
                'status' => 'leave',
                'status_message' => $statusMessages[$leave->type] ?? 'Sedang Izin/Cuti',
                'description' => $leave->reason,
                'leave_type' => $leave->type,
                'can_check_in' => false,
                'can_check_out' => false,
            ]);
        }

        // Check attendance record
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $todayString)
            ->first();

        if (!$attendance) {
            return ApiResponse::success([
                'id' => null,
                'date' => $todayString,
                'check_in_time' => null,
                'check_out_time' => null,
                'check_in_photo' => null,
                'check_out_photo' => null,
                'working_hours' => null,
                'status' => 'not_checked_in',
                'status_message' => 'Belum Absen',
                'can_check_in' => true,
                'can_check_out' => false,
            ]);
        }

        $status = 'not_checked_in';
        $statusMessage = 'Belum Absen';
        $canCheckIn = true;
        $canCheckOut = false;

        if ($attendance->check_in_time && $attendance->check_out_time) {
            $status = 'checked_out';
            $statusMessage = 'Sudah Absen Keluar';
            $canCheckIn = false;
            $canCheckOut = false;
        } elseif ($attendance->check_in_time) {
            $status = 'checked_in';
            $statusMessage = 'Sudah Absen Masuk';
            $canCheckIn = false;
            $canCheckOut = true;
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
            'status_message' => $statusMessage,
            'can_check_in' => $canCheckIn,
            'can_check_out' => $canCheckOut,
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
