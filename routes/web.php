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
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
