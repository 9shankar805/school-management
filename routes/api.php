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
use App\Http\Controllers\FreeAiController;
use App\Http\Controllers\Api\V1\AiFeatureController;

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

    // AI Chat test endpoint (public for easy testing)
    Route::post('ai/chat', [FreeAiController::class, 'chat'])->name('ai.chat');

    // Global search (students, users, etc.) - public for command palette
    Route::get('search', [SettingController::class, 'globalSearch'])->name('search');

    // RFID / Biometric scan webhook — authenticated by device API key (not Sanctum)
    Route::post('rfid/scan', [\App\Http\Controllers\RfidController::class, 'incomingScan'])->name('rfid.scan');

    // Module 19: AI Features
    Route::prefix('ai-features')->name('ai_features.')->group(function () {
        Route::post('attendance-analysis', [AiFeatureController::class, 'attendanceAnalysis'])->name('attendance');
        Route::post('fee-prediction', [AiFeatureController::class, 'feePrediction'])->name('fee');
        Route::post('performance-prediction', [AiFeatureController::class, 'performancePrediction'])->name('performance');
        Route::post('chatbot', [AiFeatureController::class, 'chatbot'])->name('chatbot');
        Route::post('homework-generator', [AiFeatureController::class, 'homeworkGenerator'])->name('homework');
        Route::post('timetable-optimizer', [AiFeatureController::class, 'timetableOptimizer'])->name('timetable');
        Route::post('report-summariser', [AiFeatureController::class, 'reportSummariser'])->name('report');
        Route::post('notice-writer', [AiFeatureController::class, 'noticeWriter'])->name('notice');
        Route::post('recommend-remedial', [AiFeatureController::class, 'recommendRemedial'])->name('remedial');
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
        Route::prefix('students')->name('students.')->middleware('scope.own')->group(function () {
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

        Route::prefix('marks')->name('marks.')->middleware('scope.own')->group(function () {
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

        // ── Finance API (accountant / admin) ───────────────────────────────
        Route::prefix('finance')->name('finance.')->group(function () {
            // Invoices
            Route::get('invoices',              [\App\Http\Controllers\Api\V1\FinanceApiController::class, 'invoices'])->name('invoices');
            Route::get('invoices/{id}',         [\App\Http\Controllers\Api\V1\FinanceApiController::class, 'invoice'])->name('invoice');
            Route::get('invoices/{id}/payments',[\App\Http\Controllers\Api\V1\FinanceApiController::class, 'invoicePayments'])->name('invoice.payments');
            // Student ledger
            Route::get('students/{id}/ledger',  [\App\Http\Controllers\Api\V1\FinanceApiController::class, 'studentLedger'])->name('student.ledger');
            // Payments
            Route::get('payments',              [\App\Http\Controllers\Api\V1\FinanceApiController::class, 'payments'])->name('payments');
            Route::post('payments',             [\App\Http\Controllers\Api\V1\FinanceApiController::class, 'storePayment'])->name('payments.store');
            // Summary
            Route::get('summary',               [\App\Http\Controllers\Api\V1\FinanceApiController::class, 'summary'])->name('summary');
        });

        // ── Payroll API ────────────────────────────────────────────────────
        Route::prefix('payroll')->name('payroll.')->group(function () {
            Route::get('teacher',           [\App\Http\Controllers\Api\V1\PayrollApiController::class, 'teacherIndex'])->name('teacher.index');
            Route::get('teacher/{id}',      [\App\Http\Controllers\Api\V1\PayrollApiController::class, 'teacherShow'])->name('teacher.show');
            Route::get('staff',             [\App\Http\Controllers\Api\V1\PayrollApiController::class, 'staffIndex'])->name('staff.index');
            Route::get('staff/{id}',        [\App\Http\Controllers\Api\V1\PayrollApiController::class, 'staffShow'])->name('staff.show');
            Route::get('tax-rates',         [\App\Http\Controllers\Api\V1\PayrollApiController::class, 'taxRates'])->name('tax-rates');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Parents App API Routes
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Api\ParentsApp\AuthController as ParentAuthController;
use App\Http\Controllers\Api\ParentsApp\ChildController as ParentChildController;
use App\Http\Controllers\Api\ParentsApp\AcademicController as ParentAcademicController;
use App\Http\Controllers\Api\ParentsApp\AttendanceController as ParentAttendanceController;
use App\Http\Controllers\Api\ParentsApp\AssignmentController as ParentAssignmentController;
use App\Http\Controllers\Api\ParentsApp\ExamController as ParentExamController;
use App\Http\Controllers\Api\ParentsApp\FeeController as ParentFeeController;
use App\Http\Controllers\Api\ParentsApp\MessageController as ParentMessageController;
use App\Http\Controllers\Api\ParentsApp\NotificationController as ParentNotificationController;
use App\Http\Controllers\Api\ParentsApp\BusTrackingController as ParentBusTrackingController;
use App\Http\Controllers\Api\ParentsApp\ScheduleController as ParentScheduleController;
use App\Http\Controllers\Api\ParentsApp\TeacherController as ParentTeacherController;
use App\Http\Controllers\Api\ParentsApp\MeetingController as ParentMeetingController;
use App\Http\Controllers\Api\ParentsApp\EventController as ParentEventController;
use App\Http\Controllers\Api\ParentsApp\HealthController as ParentHealthController;
use App\Http\Controllers\Api\ParentsApp\BehaviorController as ParentBehaviorController;
use App\Http\Controllers\Api\ParentsApp\LeaveRequestController as ParentLeaveRequestController;
use App\Http\Controllers\Api\ParentsApp\SettingController as ParentSettingController;
use App\Http\Controllers\Api\ParentsApp\DashboardController as ParentDashboardController;
use App\Http\Controllers\Api\ParentsApp\DocumentController as ParentDocumentController;

Route::name('parents.')->group(function () {
    
    // Public Authentication Routes
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('login', [ParentAuthController::class, 'login']);
        Route::post('forgot-password', [ParentAuthController::class, 'forgotPassword']);
        Route::post('reset-password', [ParentAuthController::class, 'resetPassword']);
        Route::post('social/google', [ParentAuthController::class, 'socialGoogle']);
        Route::post('social/microsoft', [ParentAuthController::class, 'socialMicrosoft']);
    });

    // Authenticated Parents App Routes
    Route::middleware(['auth:sanctum'])->group(function () {
        
        // Protected Authentication Routes
        Route::prefix('auth')->name('auth.')->group(function () {
            Route::post('logout', [ParentAuthController::class, 'logout']);
            Route::post('refresh-token', [ParentAuthController::class, 'refreshToken']);
            Route::get('me', [ParentAuthController::class, 'me']);
            Route::put('profile', [ParentAuthController::class, 'updateProfile']);
        });

    // Children Module
    Route::prefix('children')->name('children.')->group(function () {
        Route::get('/', [ParentChildController::class, 'index']);
        Route::get('{id}', [ParentChildController::class, 'show']);
        Route::get('{id}/overview', [ParentChildController::class, 'overview']);
        Route::get('{id}/stats', [ParentChildController::class, 'stats']);
    });

    // Academic Performance Module
    Route::get('grades', [ParentAcademicController::class, 'grades']);
    Route::get('grades/child/{childId}', [ParentAcademicController::class, 'gradesByChild']);
    Route::get('grades/subject/{subjectId}', [ParentAcademicController::class, 'gradesBySubject']);
    Route::get('gpa/child/{childId}', [ParentAcademicController::class, 'gpaByChild']);
    
    Route::get('report-cards', [ParentAcademicController::class, 'reportCards']);
    Route::get('report-cards/child/{childId}', [ParentAcademicController::class, 'reportCardsByChild']);
    Route::get('report-cards/{id}', [ParentAcademicController::class, 'reportCardShow']);
    Route::get('report-cards/term/{term}', [ParentAcademicController::class, 'reportCardsByTerm']);
    
    Route::get('subjects', [ParentAcademicController::class, 'subjects']);
    Route::get('subjects/{id}', [ParentAcademicController::class, 'subjectShow']);

    // Attendance Module
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [ParentAttendanceController::class, 'index']);
        Route::get('child/{childId}', [ParentAttendanceController::class, 'byChild']);
        Route::get('subject/{subjectId}', [ParentAttendanceController::class, 'bySubject']);
        Route::get('child/{childId}/summary', [ParentAttendanceController::class, 'summaryByChild']);
        Route::get('child/{childId}/calendar/{month}', [ParentAttendanceController::class, 'calendarByChild']);
        Route::get('child/{childId}/subject/{subjectId}', [ParentAttendanceController::class, 'subjectByChild']);
    });

    // Assignments Module
    Route::prefix('assignments')->name('assignments.')->group(function () {
        Route::get('/', [ParentAssignmentController::class, 'index']);
        Route::get('pending', [ParentAssignmentController::class, 'pending']);
        Route::get('completed', [ParentAssignmentController::class, 'completed']);
        Route::get('child/{childId}', [ParentAssignmentController::class, 'byChild']);
        Route::get('subject/{subjectId}', [ParentAssignmentController::class, 'bySubject']);
        Route::get('{id}', [ParentAssignmentController::class, 'show']);
        Route::put('{id}/status', [ParentAssignmentController::class, 'updateStatus']);
    });

    // Exams Module
    Route::prefix('exams')->name('exams.')->group(function () {
        Route::get('/', [ParentExamController::class, 'index']);
        Route::get('upcoming', [ParentExamController::class, 'upcoming']);
        Route::get('past', [ParentExamController::class, 'past']);
        Route::get('child/{childId}', [ParentExamController::class, 'byChild']);
        Route::get('child/{childId}/schedule', [ParentExamController::class, 'scheduleByChild']);
        Route::get('{id}', [ParentExamController::class, 'show']);
    });

    // Fees Module
    Route::prefix('fees')->name('fees.')->group(function () {
        Route::get('/', [ParentFeeController::class, 'index']);
        Route::get('summary', [ParentFeeController::class, 'summary']);
        Route::get('status/{status}', [ParentFeeController::class, 'byStatus']);
        Route::get('child/{childId}', [ParentFeeController::class, 'byChild']);
        Route::get('{id}', [ParentFeeController::class, 'show']);
        Route::post('{id}/pay', [ParentFeeController::class, 'pay']);
        Route::get('{id}/receipt', [ParentFeeController::class, 'receipt']);
    });

    // Messaging Module
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('conversations', [ParentMessageController::class, 'conversations']);
        Route::post('conversations', [ParentMessageController::class, 'startConversation']);
        Route::get('conversations/{id}', [ParentMessageController::class, 'conversationShow']);
        Route::get('conversations/{id}/messages', [ParentMessageController::class, 'messages']);
        Route::post('conversations/{id}/messages', [ParentMessageController::class, 'sendMessage']);
        Route::get('contacts', [ParentMessageController::class, 'contacts']);
        Route::get('online', [ParentMessageController::class, 'online']);
        Route::put('{id}/read', [ParentMessageController::class, 'markRead']);
        Route::delete('{id}', [ParentMessageController::class, 'destroy']);
    });

    // Notifications Module
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [ParentNotificationController::class, 'index']);
        Route::get('unread', [ParentNotificationController::class, 'unread']);
        Route::get('type/{type}', [ParentNotificationController::class, 'byType']);
        Route::put('mark-all-read', [ParentNotificationController::class, 'markAllRead']);
        Route::post('settings', [ParentNotificationController::class, 'updateSettings']);
        Route::put('{id}/read', [ParentNotificationController::class, 'markRead']);
        Route::delete('{id}', [ParentNotificationController::class, 'destroy']);
    });

    // Bus Tracking Module
    Route::prefix('bus')->name('bus.')->group(function () {
        Route::get('location', [ParentBusTrackingController::class, 'location']);
        Route::get('stops', [ParentBusTrackingController::class, 'stops']);
        Route::get('status', [ParentBusTrackingController::class, 'status']);
        Route::get('child/{childId}', [ParentBusTrackingController::class, 'byChild']);
        Route::get('route/{routeId}', [ParentBusTrackingController::class, 'byRoute']);
        Route::get('driver/{driverId}', [ParentBusTrackingController::class, 'byDriver']);
        Route::get('eta/{stopId}', [ParentBusTrackingController::class, 'eta']);
    });

    // Schedule Module
    Route::prefix('schedule')->name('schedule.')->group(function () {
        Route::get('/', [ParentScheduleController::class, 'index']);
        Route::get('week', [ParentScheduleController::class, 'week']);
        Route::get('child/{childId}', [ParentScheduleController::class, 'byChild']);
        Route::get('day/{day}', [ParentScheduleController::class, 'byDay']);
        Route::get('subject/{subjectId}', [ParentScheduleController::class, 'bySubject']);
    });

    // Teacher Contact Module
    Route::prefix('teachers')->name('teachers.')->group(function () {
        Route::get('/', [ParentTeacherController::class, 'index']);
        Route::get('available', [ParentTeacherController::class, 'available']);
        Route::get('search', [ParentTeacherController::class, 'search']);
        Route::get('subject/{subjectId}', [ParentTeacherController::class, 'bySubject']);
        Route::get('{id}', [ParentTeacherController::class, 'show']);
    });

    // Meeting Module
    Route::prefix('meetings')->name('meetings.')->group(function () {
        Route::get('/', [ParentMeetingController::class, 'index']);
        Route::post('/', [ParentMeetingController::class, 'store']);
        Route::get('upcoming', [ParentMeetingController::class, 'upcoming']);
        Route::get('past', [ParentMeetingController::class, 'past']);
        Route::get('available-slots', [ParentMeetingController::class, 'availableSlots']);
        Route::get('child/{childId}', [ParentMeetingController::class, 'byChild']);
        Route::get('{id}', [ParentMeetingController::class, 'show']);
        Route::put('{id}/reschedule', [ParentMeetingController::class, 'reschedule']);
        Route::put('{id}/cancel', [ParentMeetingController::class, 'cancel']);
        Route::put('{id}/confirm', [ParentMeetingController::class, 'confirm']);
    });

    // Events Module
    Route::prefix('events')->name('events.')->group(function () {
        Route::get('/', [ParentEventController::class, 'index']);
        Route::get('upcoming', [ParentEventController::class, 'upcoming']);
        Route::get('past', [ParentEventController::class, 'past']);
        Route::get('type/{type}', [ParentEventController::class, 'byType']);
        Route::get('month/{month}', [ParentEventController::class, 'byMonth']);
        Route::get('{id}', [ParentEventController::class, 'show']);
        Route::post('{id}/rsvp', [ParentEventController::class, 'rsvp']);
        Route::delete('{id}/rsvp', [ParentEventController::class, 'cancelRsvp']);
        Route::post('{id}/reminder', [ParentEventController::class, 'reminder']);
    });

    // Health Module
    Route::prefix('health')->name('health.')->group(function () {
        Route::get('child/{childId}', [ParentHealthController::class, 'byChild']);
        Route::get('child/{childId}/overview', [ParentHealthController::class, 'overviewByChild']);
        
        Route::get('vaccinations', [ParentHealthController::class, 'vaccinations']);
        Route::post('vaccinations', [ParentHealthController::class, 'storeVaccination']);
        Route::get('vaccinations/due', [ParentHealthController::class, 'vaccinationsDue']);
        Route::get('vaccinations/child/{childId}', [ParentHealthController::class, 'vaccinationsByChild']);
        Route::put('vaccinations/{id}', [ParentHealthController::class, 'updateVaccination']);
        
        Route::get('nurse-visits', [ParentHealthController::class, 'nurseVisits']);
        Route::post('nurse-visits', [ParentHealthController::class, 'storeNurseVisit']);
        Route::get('nurse-visits/child/{childId}', [ParentHealthController::class, 'nurseVisitsByChild']);
        
        Route::get('medical-info/child/{childId}', [ParentHealthController::class, 'medicalInfoByChild']);
        Route::put('medical-info/child/{childId}', [ParentHealthController::class, 'updateMedicalInfo']);
    });

    // Behavior Module
    Route::prefix('behavior')->name('behavior.')->group(function () {
        Route::get('child/{childId}', [ParentBehaviorController::class, 'byChild']);
        Route::get('summary/child/{childId}', [ParentBehaviorController::class, 'summaryByChild']);
        
        Route::get('commendations', [ParentBehaviorController::class, 'commendations']);
        Route::post('commendations', [ParentBehaviorController::class, 'storeCommendation']);
        Route::get('commendations/child/{childId}', [ParentBehaviorController::class, 'commendationsByChild']);
        
        Route::get('warnings', [ParentBehaviorController::class, 'warnings']);
        Route::post('warnings', [ParentBehaviorController::class, 'storeWarning']);
        Route::get('warnings/child/{childId}', [ParentBehaviorController::class, 'warningsByChild']);
    });

    // Leave Request Module
    Route::prefix('leave-requests')->name('leave_requests.')->group(function () {
        Route::get('/', [ParentLeaveRequestController::class, 'index']);
        Route::post('/', [ParentLeaveRequestController::class, 'store']);
        Route::get('status/{status}', [ParentLeaveRequestController::class, 'byStatus']);
        Route::get('child/{childId}', [ParentLeaveRequestController::class, 'byChild']);
        Route::get('{id}', [ParentLeaveRequestController::class, 'show']);
        Route::put('{id}/cancel', [ParentLeaveRequestController::class, 'cancel']);
        Route::put('{id}/approve', [ParentLeaveRequestController::class, 'approve']);
        Route::put('{id}/reject', [ParentLeaveRequestController::class, 'reject']);
    });

    // Settings Module
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [ParentSettingController::class, 'index']);
        Route::put('/', [ParentSettingController::class, 'update']);
        Route::put('notifications', [ParentSettingController::class, 'updateNotifications']);
        Route::put('theme', [ParentSettingController::class, 'updateTheme']);
        Route::put('language', [ParentSettingController::class, 'updateLanguage']);
        Route::get('privacy', [ParentSettingController::class, 'privacy']);
        Route::put('privacy', [ParentSettingController::class, 'updatePrivacy']);
    });

    // Dashboard Module
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/', [ParentDashboardController::class, 'index']);
        Route::get('quick-stats', [ParentDashboardController::class, 'quickStats']);
        Route::get('recent-activity', [ParentDashboardController::class, 'recentActivity']);
        Route::get('today-schedule', [ParentDashboardController::class, 'todaySchedule']);
        Route::get('upcoming-exams', [ParentDashboardController::class, 'upcomingExams']);
        Route::get('bus-status', [ParentDashboardController::class, 'busStatus']);
    });

    // File/Document Module
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [ParentDocumentController::class, 'index']);
        Route::post('upload', [ParentDocumentController::class, 'upload']);
        Route::get('type/{type}', [ParentDocumentController::class, 'byType']);
        Route::get('child/{childId}', [ParentDocumentController::class, 'byChild']);
        Route::get('{id}/download', [ParentDocumentController::class, 'download']);
        Route::delete('{id}', [ParentDocumentController::class, 'destroy']);
    });

    }); // End auth:sanctum middleware

});

/*
|--------------------------------------------------------------------------
| Teacher App API Routes
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Api\TeacherApp\AuthController as TeacherAuthController;
use App\Http\Controllers\Api\TeacherApp\ProfileController as TeacherProfileController;
use App\Http\Controllers\Api\TeacherApp\CommonController as TeacherCommonController;
use App\Http\Controllers\Api\TeacherApp\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Api\TeacherApp\ClassController as TeacherClassController;
use App\Http\Controllers\Api\TeacherApp\StudentController as TeacherStudentController;
use App\Http\Controllers\Api\TeacherApp\AttendanceController as TeacherAttendanceController;
use App\Http\Controllers\Api\TeacherApp\ScheduleController as TeacherScheduleController;
use App\Http\Controllers\Api\TeacherApp\SeatingChartController as TeacherSeatingChartController;
use App\Http\Controllers\Api\TeacherApp\TodoController as TeacherTodoController;
use App\Http\Controllers\Api\TeacherApp\AssignmentController as TeacherAssignmentController;
use App\Http\Controllers\Api\TeacherApp\GradebookController as TeacherGradebookController;
use App\Http\Controllers\Api\TeacherApp\ExamController as TeacherExamController;
use App\Http\Controllers\Api\TeacherApp\LessonPlanController as TeacherLessonPlanController;
use App\Http\Controllers\Api\TeacherApp\MessageController as TeacherMessageController;
use App\Http\Controllers\Api\TeacherApp\LeaveController as TeacherLeaveController;
use App\Http\Controllers\Api\TeacherApp\AnnouncementController as TeacherAnnouncementController;
use App\Http\Controllers\Api\TeacherApp\NotificationController as TeacherNotificationController;
use App\Http\Controllers\Api\TeacherApp\SettingController as TeacherSettingController;
use App\Http\Controllers\Api\TeacherApp\SecurityController as TeacherSecurityController;
use App\Http\Controllers\Api\TeacherApp\AppInfoController as TeacherAppInfoController;
use App\Http\Controllers\Api\TeacherApp\SupportController as TeacherSupportController;

Route::prefix('teacher-app')->name('teacher_app.')->group(function () {
    
    // 18. App Info (Public)
    Route::prefix('app-info')->group(function () {
        Route::get('version', [TeacherAppInfoController::class, 'version']);
        Route::get('privacy-policy', [TeacherAppInfoController::class, 'privacyPolicy']);
        Route::get('terms', [TeacherAppInfoController::class, 'terms']);
    });

    // 1. Auth (Public)
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('login', [TeacherAuthController::class, 'login']);
        Route::post('forgot-password', [TeacherAuthController::class, 'forgotPassword']);
        Route::post('google', [TeacherAuthController::class, 'googleAuth']);
        Route::post('microsoft', [TeacherAuthController::class, 'microsoftAuth']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        // 12. Profile
        Route::get('teacher/profile', [TeacherProfileController::class, 'show']);
        Route::put('teacher/profile', [TeacherProfileController::class, 'update']);
        Route::put('teacher/settings', [TeacherProfileController::class, 'updateSettings']);
        Route::post('teacher/logout', [TeacherAuthController::class, 'logout']);
        Route::get('teacher/achievements', [TeacherProfileController::class, 'achievements']);
        Route::get('teacher/semester-stats', [TeacherProfileController::class, 'semesterStats']);

        // 22. Common
        Route::get('teacher/classes', [TeacherCommonController::class, 'classes']);
        Route::get('teacher/students', [TeacherCommonController::class, 'students']);
        Route::get('config/dropdowns', [TeacherCommonController::class, 'dropdowns']);
        Route::post('upload', [TeacherCommonController::class, 'upload']);
        Route::get('search', [TeacherCommonController::class, 'search']);

        // 2. Dashboard
        Route::prefix('dashboard')->group(function () {
            Route::get('stats', [TeacherDashboardController::class, 'stats']);
            Route::get('next-class', [TeacherDashboardController::class, 'nextClass']);
            Route::get('timeline', [TeacherDashboardController::class, 'timeline']);
            Route::get('pending-assignments', [TeacherDashboardController::class, 'pendingAssignments']);
            Route::get('upcoming-exam', [TeacherDashboardController::class, 'upcomingExam']);
            Route::get('low-attendance', [TeacherDashboardController::class, 'lowAttendance']);
        });
        Route::get('teaching-tips', [TeacherDashboardController::class, 'teachingTips']);

        // 3. Classes
        Route::prefix('classes')->group(function () {
            Route::get('summary', [TeacherClassController::class, 'summary']);
            Route::get('{id}', [TeacherClassController::class, 'show']);
            Route::get('{id}/top-students', [TeacherClassController::class, 'topStudents']);
        });

        // 4. Students
        Route::prefix('students')->group(function () {
            Route::get('/', [TeacherStudentController::class, 'index']);
            Route::get('search', [TeacherStudentController::class, 'search']);
            Route::get('{id}', [TeacherStudentController::class, 'show']);
            Route::get('{id}/performance', [TeacherStudentController::class, 'performance']);
            Route::get('{id}/assignments', [TeacherStudentController::class, 'assignments']);
        });

        // 5. Attendance
        Route::prefix('attendance')->group(function () {
            Route::get('classes', [TeacherAttendanceController::class, 'classes']);
            Route::get('{classId}/students', [TeacherAttendanceController::class, 'students']);
            Route::get('{classId}/date/{date}', [TeacherAttendanceController::class, 'attendanceByDate']);
            Route::post('{classId}', [TeacherAttendanceController::class, 'store']);
            Route::get('{studentId}/history', [TeacherAttendanceController::class, 'history']);
        });

        // 10. Schedule
        Route::prefix('schedule')->group(function () {
            Route::get('weekly', [TeacherScheduleController::class, 'weekly']);
            Route::get('daily/{date}', [TeacherScheduleController::class, 'daily']);
            Route::get('monthly/{month}', [TeacherScheduleController::class, 'monthly']);
            Route::get('stats/{date}', [TeacherScheduleController::class, 'stats']);
        });

        // 19. Seating Chart
        Route::prefix('seating')->group(function () {
            Route::get('{classId}', [TeacherSeatingChartController::class, 'show']);
            Route::post('{classId}', [TeacherSeatingChartController::class, 'store']);
            Route::post('{classId}/randomize', [TeacherSeatingChartController::class, 'randomize']);
            Route::get('{classId}/template', [TeacherSeatingChartController::class, 'template']);
        });

        // 21. To-Do
        Route::prefix('todos')->group(function () {
            Route::get('/', [TeacherTodoController::class, 'index']);
            Route::post('/', [TeacherTodoController::class, 'store']);
            Route::put('{id}', [TeacherTodoController::class, 'update']);
            Route::delete('{id}', [TeacherTodoController::class, 'destroy']);
            Route::put('{id}/toggle', [TeacherTodoController::class, 'toggle']);
        });

        // 6. Assignments
        Route::prefix('assignments')->group(function () {
            Route::get('/', [TeacherAssignmentController::class, 'index']);
            Route::post('/', [TeacherAssignmentController::class, 'store']);
            Route::get('{id}', [TeacherAssignmentController::class, 'show']);
            Route::put('{id}', [TeacherAssignmentController::class, 'update']);
            Route::delete('{id}', [TeacherAssignmentController::class, 'destroy']);
            Route::get('{id}/submissions', [TeacherAssignmentController::class, 'submissions']);
        });

        // 7. Gradebook
        Route::prefix('gradebook')->group(function () {
            Route::get('{classId}', [TeacherGradebookController::class, 'show']);
            Route::get('{classId}/distribution', [TeacherGradebookController::class, 'distribution']);
            Route::get('{classId}/export', [TeacherGradebookController::class, 'export']);
            Route::put('{studentId}/{assignmentId}', [TeacherGradebookController::class, 'updateGrade']);
            Route::get('{studentId}/history', [TeacherGradebookController::class, 'history']);
        });

        // 9. Exams
        Route::prefix('exams')->group(function () {
            Route::get('/', [TeacherExamController::class, 'index']);
            Route::post('/', [TeacherExamController::class, 'store']);
            Route::get('{id}', [TeacherExamController::class, 'show']);
            Route::put('{id}', [TeacherExamController::class, 'update']);
            Route::delete('{id}', [TeacherExamController::class, 'destroy']);
            Route::get('{id}/results', [TeacherExamController::class, 'results']);
            Route::put('{id}/marks', [TeacherExamController::class, 'submitMarks']);
        });

        // 13. Lesson Planner
        Route::prefix('lesson-plans')->group(function () {
            Route::get('/', [TeacherLessonPlanController::class, 'index']);
            Route::post('/', [TeacherLessonPlanController::class, 'store']);
            Route::get('detail/{id}', [TeacherLessonPlanController::class, 'show']);
            Route::get('{date}', [TeacherLessonPlanController::class, 'byDate']);
            Route::put('{id}', [TeacherLessonPlanController::class, 'update']);
            Route::delete('{id}', [TeacherLessonPlanController::class, 'destroy']);
        });

        // 8. Messages
        Route::prefix('messages')->group(function () {
            Route::get('/', [TeacherMessageController::class, 'index']);
            Route::post('/', [TeacherMessageController::class, 'store']);
            Route::get('{id}', [TeacherMessageController::class, 'show']);
            Route::put('{id}/read', [TeacherMessageController::class, 'markAsRead']);
            Route::delete('{id}', [TeacherMessageController::class, 'destroy']);
        });

        // 11. Leaves
        Route::prefix('leaves')->group(function () {
            Route::get('/', [TeacherLeaveController::class, 'index']);
            Route::post('/', [TeacherLeaveController::class, 'store']);
            Route::get('{id}', [TeacherLeaveController::class, 'show']);
            Route::put('{id}/status', [TeacherLeaveController::class, 'updateStatus']);
        });

        // 14. Announcements
        Route::prefix('announcements')->group(function () {
            Route::get('/', [TeacherAnnouncementController::class, 'index']);
            Route::post('/', [TeacherAnnouncementController::class, 'store']);
            Route::get('{id}', [TeacherAnnouncementController::class, 'show']);
        });

        // 15. Notifications
        Route::prefix('notifications')->group(function () {
            Route::get('/', [TeacherNotificationController::class, 'index']);
            Route::put('read-all', [TeacherNotificationController::class, 'markAllAsRead']);
            Route::get('unread-count', [TeacherNotificationController::class, 'unreadCount']);
            Route::put('{id}/read', [TeacherNotificationController::class, 'markAsRead']);
            Route::delete('{id}', [TeacherNotificationController::class, 'destroy']);
        });

        // 16. Settings
        Route::prefix('settings')->group(function () {
            Route::get('/', [TeacherSettingController::class, 'show']);
            Route::put('notifications', [TeacherSettingController::class, 'updateNotifications']);
            Route::put('language', [TeacherSettingController::class, 'updateLanguage']);
            Route::put('theme', [TeacherSettingController::class, 'updateTheme']);
        });

        // 17. Security
        Route::prefix('security')->group(function () {
            Route::put('change-password', [TeacherSecurityController::class, 'changePassword']);
            Route::post('two-factor/enable', [TeacherSecurityController::class, 'enableTwoFactor']);
            Route::post('two-factor/verify', [TeacherSecurityController::class, 'verifyTwoFactor']);
            Route::post('two-factor/disable', [TeacherSecurityController::class, 'disableTwoFactor']);
        });

        // 20. Support/Help
        Route::prefix('support')->group(function () {
            Route::get('faqs', [TeacherSupportController::class, 'faqs']);
            Route::post('contact', [TeacherSupportController::class, 'contact']);
            Route::get('tickets', [TeacherSupportController::class, 'tickets']);
            Route::get('tickets/{id}', [TeacherSupportController::class, 'showTicket']);
        });
    });
});
