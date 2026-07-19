<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\StudentController;
use App\Http\Controllers\Api\V1\TeacherController;
use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\V1\ExamController;
use App\Http\Controllers\Api\V1\MarkController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes — v1
| Base URL: /api/v1/
| Auth:      Laravel Sanctum (token-based, stateless)
| Rate Limit: 60 req/min (unauthenticated), 600 req/min (authenticated)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->name('api.v1.')->group(function () {

    // ------------------------------------------------------------------
    // Public routes — no auth required
    // ------------------------------------------------------------------
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('login',          [AuthController::class, 'login'])->name('login');
        Route::post('forgot-password',[AuthController::class, 'forgotPassword'])->name('forgot-password');
        Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('reset-password');
    });

    // ------------------------------------------------------------------
    // Authenticated routes — requires Sanctum token
    // ------------------------------------------------------------------
    Route::middleware(['auth:sanctum', 'throttle:600,1'])->group(function () {

        // Auth
        Route::prefix('auth')->name('auth.')->group(function () {
            Route::post('logout',  [AuthController::class, 'logout'])->name('logout');
            Route::get('me',       [AuthController::class, 'me'])->name('me');
            Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
            // 2FA
            Route::post('2fa/enable',   [AuthController::class, 'enableTwoFactor'])->name('2fa.enable');
            Route::post('2fa/verify',   [AuthController::class, 'verifyTwoFactor'])->name('2fa.verify');
            Route::post('2fa/disable',  [AuthController::class, 'disableTwoFactor'])->name('2fa.disable');
        });

        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Students
        Route::prefix('students')->name('students.')->group(function () {
            Route::get('/',          [StudentController::class, 'index'])->name('index');
            Route::post('/',         [StudentController::class, 'store'])->name('store');
            Route::get('{id}',       [StudentController::class, 'show'])->name('show');
            Route::put('{id}',       [StudentController::class, 'update'])->name('update');
            Route::delete('{id}',    [StudentController::class, 'destroy'])->name('destroy');
            Route::get('{id}/attendance', [StudentController::class, 'attendance'])->name('attendance');
            Route::get('{id}/results',    [StudentController::class, 'results'])->name('results');
        });

        // Teachers
        Route::prefix('teachers')->name('teachers.')->group(function () {
            Route::get('/',       [TeacherController::class, 'index'])->name('index');
            Route::post('/',      [TeacherController::class, 'store'])->name('store');
            Route::get('{id}',    [TeacherController::class, 'show'])->name('show');
            Route::put('{id}',    [TeacherController::class, 'update'])->name('update');
            Route::delete('{id}', [TeacherController::class, 'destroy'])->name('destroy');
        });

        // Attendance
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/',    [AttendanceController::class, 'index'])->name('index');
            Route::post('/',   [AttendanceController::class, 'store'])->name('store');
            Route::get('report', [AttendanceController::class, 'report'])->name('report');
        });

        // Exams & Marks
        Route::prefix('exams')->name('exams.')->group(function () {
            Route::get('/',    [ExamController::class, 'index'])->name('index');
            Route::post('/',   [ExamController::class, 'store'])->name('store');
            Route::get('{id}', [ExamController::class, 'show'])->name('show');
        });

        Route::prefix('marks')->name('marks.')->group(function () {
            Route::get('/',    [MarkController::class, 'index'])->name('index');
            Route::post('/',   [MarkController::class, 'store'])->name('store');
            Route::get('results', [MarkController::class, 'results'])->name('results');
        });

        // Notifications
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/',          [NotificationController::class, 'index'])->name('index');
            Route::post('{id}/read', [NotificationController::class, 'markRead'])->name('read');
            Route::post('read-all',  [NotificationController::class, 'markAllRead'])->name('read-all');
            Route::delete('{id}',    [NotificationController::class, 'destroy'])->name('destroy');
        });

        // Settings (admin only)
        Route::prefix('settings')->name('settings.')->middleware('role:admin')->group(function () {
            Route::get('/',          [SettingController::class, 'index'])->name('index');
            Route::post('/',         [SettingController::class, 'upsert'])->name('upsert');
            Route::get('{group}',    [SettingController::class, 'group'])->name('group');
        });
    });
});
