<?php

use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    // Specific routes MUST come BEFORE resource routes
    
    // Monthly report
    Route::get('attendance/reports/monthly', [AttendanceController::class, 'monthlyReport'])
        ->name('attendance.reports.monthly');

    // Bulk attendance recording
    Route::post('attendance/bulk', [AttendanceController::class, 'bulkStore'])
        ->name('attendance.bulk.store');

    // Attendance resource routes (MUST be last)
    Route::resource('attendance', AttendanceController::class);
});

