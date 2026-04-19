<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    Route::get('attendances', [\App\Http\Controllers\AttendanceController::class, 'index'])->name('attendances.index');
    Route::get('attendances/{attendance}', [\App\Http\Controllers\AttendanceController::class, 'show'])->name('attendances.show');

    Route::middleware('role:admin')->group(function () {
        Route::resource('users', \App\Http\Controllers\UserManagementController::class);
        Route::resource('positions', \App\Http\Controllers\PositionController::class)->except(['show']);
        Route::resource('departments', \App\Http\Controllers\DepartmentController::class)->except(['show']);
        Route::get('office-locations', [\App\Http\Controllers\OfficeLocationController::class, 'index'])->name('office-locations.index');
        Route::get('office-locations/{officeLocation}/edit', [\App\Http\Controllers\OfficeLocationController::class, 'edit'])->name('office-locations.edit');
        Route::put('office-locations/{officeLocation}', [\App\Http\Controllers\OfficeLocationController::class, 'update'])->name('office-locations.update');
        
        Route::get('field-duties', [\App\Http\Controllers\FieldDutyController::class, 'index'])->name('field-duties.index');
        Route::get('field-duties/{fieldDuty}', [\App\Http\Controllers\FieldDutyController::class, 'show'])->name('field-duties.show');
        Route::patch('field-duties/{fieldDuty}/status', [\App\Http\Controllers\FieldDutyController::class, 'updateStatus'])->name('field-duties.update-status');
        
        Route::get('leaves', [\App\Http\Controllers\LeaveController::class, 'index'])->name('leaves.index');
        Route::get('leaves/{leave}', [\App\Http\Controllers\LeaveController::class, 'show'])->name('leaves.show');
        Route::patch('leaves/{leave}/status', [\App\Http\Controllers\LeaveController::class, 'updateStatus'])->name('leaves.update-status');
        
        Route::get('attendance-recap', [\App\Http\Controllers\AttendanceRecapController::class, 'index'])->name('attendance-recap.index');
        Route::get('attendance-recap/export', [\App\Http\Controllers\AttendanceRecapController::class, 'export'])->name('attendance-recap.export');
        Route::get('attendance-recap/export-all', [\App\Http\Controllers\AttendanceRecapController::class, 'exportAll'])->name('attendance-recap.export-all');
        Route::get('attendance-recap/export-daily', [\App\Http\Controllers\AttendanceRecapController::class, 'exportDaily'])->name('attendance-recap.export-daily');
        Route::get('attendance-recap/export-yearly', [\App\Http\Controllers\AttendanceRecapController::class, 'exportYearly'])->name('attendance-recap.export-yearly');
        
        Route::resource('holidays', \App\Http\Controllers\HolidayController::class);
        Route::patch('holidays/{holiday}/toggle', [\App\Http\Controllers\HolidayController::class, 'toggleActive'])->name('holidays.toggle');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

// Testing Routes - Controlled by DEV_MODE
if (config('app.dev_mode', false)) {
    Route::middleware(['auth'])->prefix('dev')->group(function () {
        Route::get('set-date/{date}', function ($date) {
            session(['test_date' => $date]);
            return redirect()->back()->with('success', "Test date set to: {$date}");
        })->name('dev.set-date');
        
        Route::get('clear-date', function () {
            session()->forget('test_date');
            return redirect()->back()->with('success', 'Test date cleared. Using real date now.');
        })->name('dev.clear-date');
        
        Route::get('date-info', function () {
            $info = \App\Helpers\DateHelper::getTestDateInfo();
            return response()->json([
                'test_mode' => \App\Helpers\DateHelper::isTestMode(),
                'test_date_info' => $info,
                'real_date' => now()->format('Y-m-d'),
                'current_date' => \App\Helpers\DateHelper::today(),
            ]);
        })->name('dev.date-info');
    });
}
