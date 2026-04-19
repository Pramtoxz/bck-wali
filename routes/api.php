<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AttendanceRecapController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FieldDutyController;
use App\Http\Controllers\Api\HolidayController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\OfficeLocationController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');
});

Route::middleware(['auth:sanctum', 'block.admin.api'])->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/user', [AuthController::class, 'user']);
    
    Route::prefix('profile')->group(function () {
        Route::put('/', [ProfileController::class, 'update']);
        Route::post('/avatar', [ProfileController::class, 'updateAvatar'])->middleware('throttle:uploads');
        Route::put('/password', [ProfileController::class, 'updatePassword']);
    });
    
    // Field duty & leave submissions with rate limiting
    Route::middleware('throttle:submissions')->group(function () {
        Route::post('field-duty', [FieldDutyController::class, 'store']);
        Route::post('leave', [LeaveController::class, 'store']);
    });
    
    Route::get('field-duty', [FieldDutyController::class, 'index']);
    Route::get('leave', [LeaveController::class, 'index']);

    Route::get('office/location', [OfficeLocationController::class, 'getActive']);
    
    // Attendance with rate limiting
    Route::middleware('throttle:attendance')->group(function () {
        Route::post('attendance/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('attendance/check-out', [AttendanceController::class, 'checkOut']);
    });
    
    Route::get('attendance/today', [AttendanceController::class, 'today']);
    Route::get('attendance/recap', [AttendanceRecapController::class, 'getMonthlyRecap']);
    Route::get('attendance/detail/{date}', [AttendanceRecapController::class, 'getAttendanceDetail']);
    Route::get('attendance/statistics', [AttendanceRecapController::class, 'getStatistics']);
    
    Route::get('holidays/check', [HolidayController::class, 'checkToday']);
    Route::get('holidays', [HolidayController::class, 'index']);
    
    Route::get('notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::get('notifications/unread-count', [\App\Http\Controllers\Api\NotificationController::class, 'unreadCount']);
    Route::post('notifications/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
    Route::post('notifications/read-all', [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
    Route::post('notifications/update-token', [\App\Http\Controllers\Api\NotificationController::class, 'updateToken']);
    Route::post('notifications/test', [\App\Http\Controllers\Api\NotificationController::class, 'sendTest']);
    
    // Dev mode routes for API testing
    if (config('app.dev_mode', false)) {
        Route::post('dev/set-date', function (\Illuminate\Http\Request $request) {
            $validated = $request->validate(['date' => 'required|date_format:Y-m-d']);
            \App\Helpers\DateHelper::setTestDate($validated['date']);
            return \App\Helpers\ApiResponse::success([
                'test_date' => $validated['date'],
                'message' => 'Test date set successfully'
            ]);
        });
        
        Route::post('dev/clear-date', function () {
            \App\Helpers\DateHelper::clearTestDate();
            return \App\Helpers\ApiResponse::success(['message' => 'Test date cleared']);
        });
        
        Route::get('dev/date-info', function () {
            return \App\Helpers\ApiResponse::success([
                'test_mode' => \App\Helpers\DateHelper::isTestMode(),
                'current_date' => \App\Helpers\DateHelper::today(),
                'real_date' => now()->format('Y-m-d'),
                'test_date_info' => \App\Helpers\DateHelper::getTestDateInfo(),
            ]);
        });
    }
});
