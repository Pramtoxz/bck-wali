<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::get('attendances', [\App\Http\Controllers\AttendanceController::class, 'index'])->name('attendances.index');

    Route::middleware('role:admin')->group(function () {
        Route::resource('users', \App\Http\Controllers\UserManagementController::class);
        Route::get('office-locations', [\App\Http\Controllers\OfficeLocationController::class, 'index'])->name('office-locations.index');
        Route::get('office-locations/{officeLocation}/edit', [\App\Http\Controllers\OfficeLocationController::class, 'edit'])->name('office-locations.edit');
        Route::put('office-locations/{officeLocation}', [\App\Http\Controllers\OfficeLocationController::class, 'update'])->name('office-locations.update');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
