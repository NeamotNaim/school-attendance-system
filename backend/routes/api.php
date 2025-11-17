<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\ClassController;
use App\Http\Controllers\Api\SectionController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\HolidayController;
use App\Http\Controllers\Api\NotificationController;
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
    Route::get('/students/{id}/attendance-history', [StudentController::class, 'attendanceHistory']);

    // Class routes
    Route::apiResource('classes', ClassController::class);

    // Section routes
    Route::apiResource('sections', SectionController::class);

    // Attendance routes
    Route::post('/attendances/bulk', [AttendanceController::class, 'storeBulk']);
    Route::get('/attendances/report', [AttendanceController::class, 'monthlyReport']);
    Route::get('/attendances/statistics', [AttendanceController::class, 'statistics']);
    Route::get('/attendances/student/{studentId}', [AttendanceController::class, 'studentHistory']);
    Route::get('/attendances/daily/{date}', [AttendanceController::class, 'dailyReport']);
    Route::apiResource('attendances', AttendanceController::class);

    // Report routes
    Route::prefix('reports')->group(function () {
        Route::get('/daily', [ReportController::class, 'daily']);
        Route::get('/weekly', [ReportController::class, 'weekly']);
        Route::get('/monthly', [ReportController::class, 'monthly']);
        Route::get('/class-comparison', [ReportController::class, 'classComparison']);
        Route::get('/low-attendance', [ReportController::class, 'lowAttendance']);
        Route::get('/trends', [ReportController::class, 'trends']);
        Route::get('/export/monthly', [ReportController::class, 'exportMonthly']);
        Route::get('/export/daily', [ReportController::class, 'exportDaily']);
        Route::get('/export/student/{studentId}', [ReportController::class, 'exportStudentHistory']);
    });

    // Holiday routes
    Route::get('/holidays/check', [HolidayController::class, 'checkDate']);
    Route::apiResource('holidays', HolidayController::class);

    // Notification routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::post('/clear-read', [NotificationController::class, 'clearRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
    });

    // Dashboard routes
    Route::get('/dashboard/overview', [AttendanceController::class, 'dashboardOverview']);
});

