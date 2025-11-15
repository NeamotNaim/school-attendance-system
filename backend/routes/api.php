<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\AttendanceController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Student routes
    Route::apiResource('students', StudentController::class);

    // Attendance routes
    Route::post('/attendances/bulk', [AttendanceController::class, 'storeBulk']);
    Route::get('/attendances/report', [AttendanceController::class, 'monthlyReport']);
    Route::get('/attendances/statistics', [AttendanceController::class, 'statistics']);
    Route::apiResource('attendances', AttendanceController::class);
});

