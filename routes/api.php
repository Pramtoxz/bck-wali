<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AttendanceRecapController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FieldDutyController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\OfficeLocationController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/user', [AuthController::class, 'user']);
    
    Route::post('field-duty', [FieldDutyController::class, 'store']);
    Route::get('field-duty', [FieldDutyController::class, 'index']);
    
    Route::post('leave', [LeaveController::class, 'store']);
    Route::get('leave', [LeaveController::class, 'index']);

    Route::get('office/location', [OfficeLocationController::class, 'getActive']);
    Route::post('attendance/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('attendance/check-out', [AttendanceController::class, 'checkOut']);
    Route::get('attendance/today', [AttendanceController::class, 'today']);
    Route::get('attendance/recap', [AttendanceRecapController::class, 'getMonthlyRecap']);
    Route::get('attendance/detail/{date}', [AttendanceRecapController::class, 'getAttendanceDetail']);
    Route::get('attendance/statistics', [AttendanceRecapController::class, 'getStatistics']);
});
