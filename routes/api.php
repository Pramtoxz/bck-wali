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

    Route::get('office/location', [AttendanceController::class, 'getOfficeLocation']);
    Route::post('attendance/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('attendance/check-out', [AttendanceController::class, 'checkOut']);
    Route::get('attendance/today', [AttendanceController::class, 'today']);
    Route::get('attendance/recap', [AttendanceRecapController::class, 'getMonthlyRecap']);
    Route::get('attendance/detail/{date}', [AttendanceRecapController::class, 'getAttendanceDetail']);
    Route::get('attendance/statistics', [AttendanceRecapController::class, 'getStatistics']);

    Route::middleware('role:admin')->group(function () {
        Route::apiResource('users', \App\Http\Controllers\Api\UserManagementController::class)->names([
            'index' => 'api.users.index',
            'store' => 'api.users.store',
            'show' => 'api.users.show',
            'update' => 'api.users.update',
            'destroy' => 'api.users.destroy',
        ]);
        Route::apiResource('office-locations', OfficeLocationController::class)->names([
            'index' => 'api.office-locations.index',
            'store' => 'api.office-locations.store',
            'show' => 'api.office-locations.show',
            'update' => 'api.office-locations.update',
            'destroy' => 'api.office-locations.destroy',
        ]);
    });
});
