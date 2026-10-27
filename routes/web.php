<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MarkController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\RoutineController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\ExamRuleController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\SyllabusController;
use App\Http\Controllers\GradeRuleController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\RollNumberController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\QrAttendanceController;
use App\Http\Controllers\StaffAttendanceController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\GradingSystemController;
use App\Http\Controllers\SchoolSessionController;
use App\Http\Controllers\AcademicSettingController;
use App\Http\Controllers\AssignedTeacherController;
use App\Http\Controllers\ClassTeacherPortalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\UpdatePasswordController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\BookCategoryController;
use App\Http\Controllers\BookIssueController;
use App\Http\Controllers\LibraryMemberController;
use App\Http\Controllers\EbookController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\TransportRouteController;
use App\Http\Controllers\StudentTransportController;
use App\Http\Controllers\TransportAttendanceController;
use App\Http\Controllers\TransportController;
// Module 10 — Finance
use App\Http\Controllers\FeeController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\FinanceReportController;
// Module 11 — HR
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffContractController;
use App\Http\Controllers\StaffDocumentController;
use App\Http\Controllers\StaffTrainingController;
use App\Http\Controllers\StaffExitController;
use App\Http\Controllers\RecruitmentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\StudentDocumentController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\EmergencyContactController;
use App\Http\Controllers\DisciplinaryRecordController;
use App\Http\Controllers\HouseAssignmentController;
use App\Http\Controllers\ScholarshipController;
use App\Http\Controllers\SchoolProfileController;
use App\Http\Controllers\SchoolLandingController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\StudentTransferController;
use App\Http\Controllers\StudentIdCardController;
use App\Http\Controllers\IdCardTemplateController;
use App\Http\Controllers\GraduationController;
use App\Http\Controllers\AchievementController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\TeacherAttendanceController;
use App\Http\Controllers\TeacherContractController;
use App\Http\Controllers\TeacherDocumentController;
use App\Http\Controllers\TeacherQualificationController;
use App\Http\Controllers\TeacherTrainingController;
use App\Http\Controllers\TeacherPayrollController;
use App\Http\Controllers\PerformanceReviewController;
use App\Http\Controllers\ParentPortalController;
use App\Http\Controllers\ExamScheduleController;
use App\Http\Controllers\ExamHallController;
use App\Http\Controllers\ResultSheetController;
use App\Http\Controllers\TranscriptController;
use App\Http\Controllers\ReExamController;
use App\Http\Controllers\QuestionPaperController;
use App\Http\Controllers\QuestionBankController;
use App\Http\Controllers\QuestionPaperTemplateController;
use App\Http\Controllers\QuestionApprovalController;
use App\Http\Controllers\AiFeatureWebController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ReportCenterController;
use App\Http\Controllers\CommunicationController;
use App\Http\Controllers\HrController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/





// Dynamic Multi-Tenant PWA Manifest (Tailored to active school logo, name & theme)
Route::get('/manifest.json', [\App\Http\Controllers\PwaController::class, 'manifest'])->name('pwa.manifest');
Route::get('/pwa-manifest.json', [\App\Http\Controllers\PwaController::class, 'manifest'])->name('pwa.manifest.dynamic');

Auth::routes();

// QR attendance scan — public endpoint (auth enforced inside controller)
Route::get('/attendance/scan/{token}', [QrAttendanceController::class, 'scan'])->name('attendance.qr.scan');

// Public Online Admission Application Routes (no auth required — rate limited to prevent spam)
Route::get('/admissions/apply',  [\App\Http\Controllers\PublicAdmissionController::class, 'create'])->name('admissions.public.apply');
Route::post('/admissions/apply', [\App\Http\Controllers\PublicAdmissionController::class, 'store'])->middleware('throttle:10,1')->name('admissions.public.store');

// Official CEHRD IEMIS School Performance Dashboard (Public & Authenticated)
Route::get('/iemis-school-report-card-dashboard', [\App\Http\Controllers\HomeController::class, 'iemisDashboard'])->name('iemis.school.dashboard');

// IEMIS data export (admin/principal only)
Route::middleware(['auth'])->prefix('iemis')->name('iemis.')->group(function () {
    Route::get('/export',       [\App\Http\Controllers\IemisExportController::class, 'index'])->name('export');
    Route::post('/export/excel',[\App\Http\Controllers\IemisExportController::class, 'exportExcel'])->name('export.excel');
    Route::post('/export/csv',  [\App\Http\Controllers\IemisExportController::class, 'exportCsv'])->name('export.csv');
    Route::post('/export/pdf',  [\App\Http\Controllers\IemisExportController::class, 'exportPdf'])->name('export.pdf');
});

Route::middleware(['auth'])->group(function () {

    Route::prefix('school')->name('school.')->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator|hr-manager|hr|admission-officer|receptionist|teacher|class-teacher'])->group(function () {
        Route::post('session/create', [SchoolSessionController::class, 'store'])->name('session.store');
        Route::post('session/browse', [SchoolSessionController::class, 'browse'])->name('session.browse');

        Route::post('semester/create', [SemesterController::class, 'store'])->name('semester.create');
        Route::post('final-marks-submission-status/update', [AcademicSettingController::class, 'updateFinalMarksSubmissionStatus'])->name('final.marks.submission.status.update');

        Route::post('attendance/type/update', [AcademicSettingController::class, 'updateAttendanceType'])->name('attendance.type.update');

        // Class
        Route::post('class/create', [SchoolClassController::class, 'store'])->name('class.create');
        Route::post('class/update', [SchoolClassController::class, 'update'])->name('class.update');
        Route::post('class/bulk-delete', [SchoolClassController::class, 'bulkDestroy'])->name('class.bulk.delete');
        Route::post('class/delete/{id}', [SchoolClassController::class, 'destroy'])->name('class.delete');

        // Sections
        Route::post('section/create', [SectionController::class, 'store'])->name('section.create');
        Route::post('section/update', [SectionController::class, 'update'])->name('section.update');
        Route::post('section/bulk-delete', [SectionController::class, 'bulkDestroy'])->name('section.bulk.delete');
        Route::post('section/delete/{id}', [SectionController::class, 'destroy'])->name('section.delete');

        // Courses
        Route::post('course/create', [CourseController::class, 'store'])->name('course.create');
        Route::post('course/update', [CourseController::class, 'update'])->name('course.update');

        // Teacher
        Route::post('teacher/create', [UserController::class, 'storeTeacher'])->name('teacher.create');
        Route::post('teacher/update', [UserController::class, 'updateTeacher'])->name('teacher.update');
        Route::post('teacher/assign', [AssignedTeacherController::class, 'store'])->name('teacher.assign');
        Route::get('teacher/matrix-data', [AssignedTeacherController::class, 'getMatrixData'])->name('teacher.matrix.data');
        Route::post('teacher/assign-matrix', [AssignedTeacherController::class, 'saveMatrix'])->name('teacher.assign.matrix');
        Route::post('teacher/assign-bulk', [AssignedTeacherController::class, 'storeBulk'])->name('teacher.assign.bulk');
        Route::post('teacher/clone-assignments', [AssignedTeacherController::class, 'cloneSemesterAssignments'])->name('teacher.clone.assignments');
        Route::delete('teacher/assigned/{id}', [AssignedTeacherController::class, 'destroy'])->name('teacher.assign.destroy');
        Route::post('teacher/toggle-class-teacher/{id}', [AssignedTeacherController::class, 'toggleClassTeacher'])->name('teacher.toggle-class-teacher');
        Route::post('classes/{classId}/sections/{sectionId}/class-teacher', [AssignedTeacherController::class, 'assignSectionClassTeacher'])->name('class.section.class-teacher.assign');

        // Student
        Route::post('student/create', [UserController::class, 'storeStudent'])->name('student.create');
        Route::post('student/update', [UserController::class, 'updateStudent'])->name('student.update');
    });

    // Direct named route aliases for teachers and class teacher management
    Route::post('teacher/assign', [AssignedTeacherController::class, 'store'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator'])->name('teacher.assign');
    Route::post('teacher/toggle-class-teacher/{id}', [AssignedTeacherController::class, 'toggleClassTeacher'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator'])->name('teacher.toggle-class-teacher');
    Route::post('classes/{classId}/sections/{sectionId}/class-teacher', [AssignedTeacherController::class, 'assignSectionClassTeacher'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator'])->name('class.section.class-teacher.assign');

    // Class In-Charge (कक्षा शिक्षक) Command Center
    Route::prefix('class-incharge')->name('class-incharge.')->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator|teacher|class-teacher'])->group(function () {
        Route::get('/',                   [ClassTeacherPortalController::class, 'index'])->name('dashboard');
        Route::get('students',            [ClassTeacherPortalController::class, 'students'])->name('students');
        Route::get('gradebook',           [ClassTeacherPortalController::class, 'gradebook'])->name('gradebook');
        Route::post('leaves/{id}/action', [ClassTeacherPortalController::class, 'handleLeave'])->name('leaves.action');
        Route::post('diary',              [ClassTeacherPortalController::class, 'postDiary'])->name('diary');
    });

    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Attendance — core (original)
    Route::get('/attendance', [AttendanceController::class, 'index']);
    Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendances/view', [AttendanceController::class, 'show'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator|teacher|class-teacher|attendance-officer'])->name('attendance.list.show');
    Route::get('/attendances/take', [AttendanceController::class, 'create'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator|teacher|class-teacher|attendance-officer'])->name('attendance.create.show');
    Route::post('/attendances', [AttendanceController::class, 'store'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator|teacher|class-teacher|attendance-officer'])->name('attendances.store');

    // Attendance — analytics
    Route::get('/attendances/analytics', [AttendanceController::class, 'analytics'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator|attendance-officer'])->name('attendance.analytics');

    // Attendance — shortage alerts
    Route::get('/attendances/shortage', [AttendanceController::class, 'shortage'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator|attendance-officer'])->name('attendance.shortage');

    // Attendance — monthly reports (PDF + Excel)
    Route::get('/attendances/report',       [AttendanceController::class, 'reportForm'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator|attendance-officer'])->name('attendance.report.form');
    Route::get('/attendances/report/pdf',   [AttendanceController::class, 'monthlyReportPdf'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator|attendance-officer'])->name('attendance.report.pdf');
    Route::get('/attendances/report/excel', [AttendanceController::class, 'monthlyReportExcel'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator|attendance-officer'])->name('attendance.report.excel');

    // Attendance — bulk CSV import
    Route::get('/attendances/import',  [AttendanceController::class, 'importForm'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator|attendance-officer'])->name('attendance.import.form');
    Route::post('/attendances/import', [AttendanceController::class, 'import'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator|attendance-officer'])->name('attendance.import');

    // Attendance — QR code (teacher side + public scan endpoint)
    Route::prefix('attendance/qr')->name('attendance.qr.')->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator|teacher|class-teacher|attendance-officer'])->group(function () {
        Route::get('/',           [QrAttendanceController::class, 'index'])->name('index');
        Route::get('/create',     [QrAttendanceController::class, 'create'])->name('create');
        Route::post('/',          [QrAttendanceController::class, 'store'])->name('store');
        Route::get('/{id}',       [QrAttendanceController::class, 'show'])->name('show');
        Route::delete('/{id}',    [QrAttendanceController::class, 'destroy'])->name('destroy');
    }); // QR scan public endpoint is registered at top of file (Route::get attendance/scan/{token})

    // Attendance — staff
    Route::get('/staff/attendance',                      [StaffAttendanceController::class, 'index'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|hr-manager|hr|attendance-officer'])->name('staff.attendance.index');
    Route::post('/staff/attendance',                     [StaffAttendanceController::class, 'store'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|hr-manager|hr|attendance-officer'])->name('staff.attendance.store');
    Route::get('/staff/{staffId}/attendance',            [StaffAttendanceController::class, 'show'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|hr-manager|hr|attendance-officer'])->name('staff.attendance.show');
    Route::patch('/staff/attendance/{id}/time',          [StaffAttendanceController::class, 'updateTime'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|hr-manager|hr|attendance-officer'])->name('staff.attendance.time');

    // Classes and sections
    Route::get('/classes', [SchoolClassController::class, 'index']);
    Route::get('/class/edit/{id}', [SchoolClassController::class, 'edit'])->name('class.edit');
    Route::get('/sections', [SectionController::class, 'getByClassId'])->name('get.sections.courses.by.classId');
    Route::get('/section/edit/{id}', [SectionController::class, 'edit'])->name('section.edit');

    // ── TEACHER BULK IMPORT (must be declared before the /teachers/{id} wildcard) ──
    Route::get('/teachers/bulk-import',            [\App\Http\Controllers\TeacherImportController::class, 'index'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator|hr-manager|hr'])->name('teachers.import.index');
    Route::post('/teachers/bulk-import',           [\App\Http\Controllers\TeacherImportController::class, 'importCsv'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator|hr-manager|hr'])->name('teachers.import.process');
    Route::post('/teachers/bulk-import/direct',    [\App\Http\Controllers\TeacherImportController::class, 'importDirect'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator|hr-manager|hr'])->name('teachers.import.direct');
    Route::get('/teachers/bulk-import/sample-csv', [\App\Http\Controllers\TeacherImportController::class, 'downloadSample'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator|hr-manager|hr'])->name('teachers.import.sample');

    // Teachers
    Route::get('/teachers', [UserController::class, 'getTeacherList'])->name('teachers.index');
    Route::get('/teacher',  [UserController::class, 'getTeacherList']);
    Route::get('/teachers/view', [UserController::class, 'getTeacherList']);
    Route::get('/teachers/add', function () {
        return view('teachers.add');
    })->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|hr-manager|hr'])->name('teacher.create.show');
    Route::get('/teachers/edit/{id}', [UserController::class, 'editTeacher'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|hr-manager|hr'])->name('teacher.edit.show');
    Route::get('/teachers/view/list', [UserController::class, 'getTeacherList'])->name('teacher.list.show');
    Route::get('/teachers/view/profile/{id}', [UserController::class, 'showTeacherProfile'])->name('teacher.profile.show');
    Route::get('/teachers/view/{id}', [UserController::class, 'showTeacherProfile']);
    Route::get('/teachers/{id}', [UserController::class, 'showTeacherProfile'])->where('id', '[0-9]+');
    // Teacher timetable slot management (admin)
    Route::post('/teachers/{id}/timetable',          [\App\Http\Controllers\RoutineController::class, 'storeForTeacher'])->name('teacher.timetable.store');
    Route::delete('/teachers/timetable/{routineId}', [\App\Http\Controllers\RoutineController::class, 'destroySlot'])->name('teacher.timetable.destroy');
    // AJAX: get courses by class
    Route::get('/ajax/courses-by-class', [\App\Http\Controllers\RoutineController::class, 'coursesByClass'])->name('ajax.courses.by.class');
    // Class detail view
    Route::get('/classes/{id}/show', [\App\Http\Controllers\SchoolClassController::class, 'detail'])->name('class.detail.show');

    //Students
    Route::get('/students', [UserController::class, 'getStudentList'])->name('students.index');
    Route::get('/student',  [UserController::class, 'getStudentList']);
    Route::get('/students/view', [UserController::class, 'getStudentList']);
    Route::get('/students/bulk-import', [\App\Http\Controllers\StudentImportController::class, 'index'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator|admission-officer|receptionist'])->name('students.import.index');
    Route::post('/students/bulk-import', [\App\Http\Controllers\StudentImportController::class, 'importCsv'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator|admission-officer|receptionist'])->name('students.import.process');
    Route::post('/students/bulk-import/direct', [\App\Http\Controllers\StudentImportController::class, 'importDirect'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator|admission-officer|receptionist'])->name('students.import.direct');
    Route::get('/students/bulk-import/sample-csv', [\App\Http\Controllers\StudentImportController::class, 'downloadSample'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator|admission-officer|receptionist'])->name('students.import.sample');
    Route::get('/students/add', [UserController::class, 'createStudent'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator|admission-officer|receptionist'])->name('student.create.show');
    Route::get('/students/edit/{id}', [UserController::class, 'editStudent'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator|admission-officer'])->name('student.edit.show');
    Route::get('/students/view/list', [UserController::class, 'getStudentList'])->name('student.list.show');
    Route::get('/students/view/profile/{id}', [UserController::class, 'showStudentProfile'])->name('student.profile.show');
    Route::get('/students/view/{id}', [UserController::class, 'showStudentProfile']);
    Route::get('/students/{id}', [UserController::class, 'showStudentProfile'])->where('id', '[0-9]+');
    Route::get('/students/view/attendance/{id}', [AttendanceController::class, 'showStudentAttendance'])->name('student.attendance.show');

    // Marks
    Route::get('/marks/create', [MarkController::class, 'create'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator|teacher|class-teacher'])->name('course.mark.create');
    Route::post('/marks/store', [MarkController::class, 'store'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator|teacher|class-teacher'])->name('course.mark.store');
    Route::get('/marks/results', [MarkController::class, 'index'])->name('course.mark.list.show');
    // Route::get('/marks/view', function () {
    //     return view('marks.view');
    // });
    Route::get('/marks/view', [MarkController::class, 'showCourseMark'])->name('course.mark.show');
    Route::get('/marks/final/submit', [MarkController::class, 'showFinalMark'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator|teacher|class-teacher'])->name('course.final.mark.submit.show');
    Route::post('/marks/final/submit', [MarkController::class, 'storeFinalMark'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|academic-coordinator|teacher|class-teacher'])->name('course.final.mark.submit.store');

    // Exams
    Route::get('/exams/view', [ExamController::class, 'index'])->name('exam.list.show');
    Route::post('/exams/create', [ExamController::class, 'store'])->name('exam.create');
    Route::get('/exams/create', [ExamController::class, 'create'])->name('exam.create.show');
    Route::post('/exams/bulk-create', [ExamController::class, 'bulkStore'])->name('exam.bulk.create');
    Route::post('/exams/delete', [ExamController::class, 'destroy'])->name('exam.delete');
    Route::get('/exams/add-rule', [ExamRuleController::class, 'create'])->name('exam.rule.create');
    Route::post('/exams/add-rule', [ExamRuleController::class, 'store'])->name('exam.rule.store');
    Route::get('/exams/edit-rule', [ExamRuleController::class, 'edit'])->name('exam.rule.edit');
    Route::post('/exams/edit-rule', [ExamRuleController::class, 'update'])->name('exam.rule.update');
    Route::get('/exams/view-rule', [ExamRuleController::class, 'index'])->name('exam.rule.show');
    Route::get('/exams/grade/create', [GradingSystemController::class, 'create'])->name('exam.grade.system.create');
    Route::post('/exams/grade/create', [GradingSystemController::class, 'store'])->name('exam.grade.system.store');
    Route::get('/exams/grade/view', [GradingSystemController::class, 'index'])->name('exam.grade.system.index');
    Route::get('/exams/grade/add-rule', [GradeRuleController::class, 'create'])->name('exam.grade.system.rule.create');
    Route::post('/exams/grade/add-rule', [GradeRuleController::class, 'store'])->name('exam.grade.system.rule.store');
    Route::get('/exams/grade/view-rules', [GradeRuleController::class, 'index'])->name('exam.grade.system.rule.show');
    // Exam Admit Cards (प्रवेश-पत्र)
    Route::get('/exams/{exam}/admit-card/{student}', [\App\Http\Controllers\AdmitCardController::class, 'single'])->name('exam.admit-card.single');
    Route::get('/exams/{exam}/admit-cards/batch', [\App\Http\Controllers\AdmitCardController::class, 'batch'])->name('exam.admit-card.batch');

    // Dynamic exam routes — must come AFTER all static /exams/* paths
    Route::get('/exams/{exam}/edit', [ExamController::class, 'edit'])->name('exam.edit');
    Route::put('/exams/{exam}', [ExamController::class, 'update'])->name('exam.update');
    Route::get('/exams/{exam}', [ExamController::class, 'show'])->name('exam.show');

    // Promotions
    Route::get('/promotions/index', [PromotionController::class, 'index'])->name('promotions.index');
    Route::get('/promotions/promote', [PromotionController::class, 'create'])->name('promotions.create');
    Route::post('/promotions/promote', [PromotionController::class, 'store'])->name('promotions.store');

    // Roll Numbers
    Route::get('/roll-numbers', [RollNumberController::class, 'index'])->name('roll-numbers.index');
    Route::post('/roll-numbers/update-bulk', [RollNumberController::class, 'updateBulk'])->name('roll-numbers.update-bulk');
    Route::post('/roll-numbers/auto-assign', [RollNumberController::class, 'autoAssign'])->name('roll-numbers.auto-assign');

    // School Setup & Academic Settings
    Route::get('/school/setup', [AcademicSettingController::class, 'index'])->name('school.setup');
    Route::get('/academics/settings', [AcademicSettingController::class, 'index'])->name('academic.settings');

    // Calendar & Academic Events
    Route::get('calendar-event', [EventController::class, 'index'])->name('events.show');
    Route::post('calendar-crud-ajax', [EventController::class, 'calendarEvents'])->name('events.crud');
    Route::post('calendar/presets/generate', [EventController::class, 'generatePresets'])->name('events.presets.generate');
    Route::get('calendar/print', [EventController::class, 'printCalendar'])->name('events.print');

    // Routines
    Route::get('/routine/create', [RoutineController::class, 'create'])->name('section.routine.create');
    Route::get('/routine/view', [RoutineController::class, 'show'])->name('section.routine.show');
    Route::post('/routine/store', [RoutineController::class, 'store'])->name('section.routine.store');
    Route::get('/routine/matrix-data', [RoutineController::class, 'matrixData'])->name('routine.matrix.data');
    Route::post('/routine/matrix-save', [RoutineController::class, 'matrixSave'])->name('routine.matrix.save');
    Route::post('/routine/clone-section', [RoutineController::class, 'cloneSection'])->name('routine.clone.section');

    // Syllabus
    Route::get('/syllabus/create', [SyllabusController::class, 'create'])->name('class.syllabus.create');
    Route::post('/syllabus/create', [SyllabusController::class, 'store'])->name('syllabus.store');
    Route::get('/syllabus/index', [SyllabusController::class, 'index'])->name('course.syllabus.index');

    // Notices & Circulars
    Route::get('/notices',               [NoticeController::class, 'index'])->name('notice.index');
    Route::get('/notice/create',         [NoticeController::class, 'create'])->name('notice.create');
    Route::post('/notice/create',        [NoticeController::class, 'store'])->name('notice.store');
    Route::get('/notice/{notice}',       [NoticeController::class, 'show'])->name('notice.show');
    Route::get('/notice/{notice}/edit',  [NoticeController::class, 'edit'])->name('notice.edit');
    Route::put('/notice/{notice}',       [NoticeController::class, 'update'])->name('notice.update');
    Route::delete('/notice/{notice}',    [NoticeController::class, 'destroy'])->name('notice.destroy');

    // Courses
    Route::get('courses/teacher/index', [AssignedTeacherController::class, 'getTeacherCourses'])->name('course.teacher.list.show');
    Route::get('courses/student/index/{student_id}', [CourseController::class, 'getStudentCourses'])->name('course.student.list.show');
    Route::get('course/edit/{id}', [CourseController::class, 'edit'])->name('course.edit');

    // Assignment
    Route::get('courses/assignments/index', [AssignmentController::class, 'getCourseAssignments'])->name('assignment.list.show');
    Route::get('courses/assignments/create', [AssignmentController::class, 'create'])->name('assignment.create');
    Route::post('courses/assignments/create', [AssignmentController::class, 'store'])->name('assignment.store');
    Route::get('assignments', [AssignmentController::class, 'teacherIndex'])->name('assignment.teacher.index');
    Route::delete('assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('assignment.destroy');

    // Gradebook + Student Progress
    Route::get('gradebook',                         [\App\Http\Controllers\GradebookController::class, 'index'])->name('gradebook.index');
    Route::get('student-progress/{studentId}',      [\App\Http\Controllers\GradebookController::class, 'studentProgress'])->name('student.progress');

    // Discipline log (teacher view)
    Route::get('discipline',                        [\App\Http\Controllers\DisciplinaryRecordController::class, 'index'])->name('student.discipline.index');

    // User profile & account settings (Name, Gmail/Email, Phone, Photo, Password)
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/',                 [ProfileController::class, 'edit'])->name('edit');
        Route::put('/',                 [ProfileController::class, 'update'])->name('update');
        Route::post('/',                [ProfileController::class, 'update']);
        Route::put('/password',        [ProfileController::class, 'updatePassword'])->name('password.update');
        Route::post('/password',       [ProfileController::class, 'updatePassword']);
        Route::delete('/photo',         [ProfileController::class, 'removePhoto'])->name('photo.remove');
    });

    // Update password
    Route::get('password/edit', [UpdatePasswordController::class, 'edit'])->name('password.change');
    Route::post('password/edit', [UpdatePasswordController::class, 'update'])->name('password.change.update');

    // ── PAYMENTS & INVOICES (Module 10) ────────────────────────────────────────
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/',                          [PaymentController::class, 'index'])->name('index');
        Route::get('/create',                    [PaymentController::class, 'create'])->name('create');
        Route::post('/',                         [PaymentController::class, 'store'])->name('store');
        Route::post('/bulk-generate',            [PaymentController::class, 'bulkGenerate'])->name('bulk-generate');
        Route::get('/student/{studentId}/ledger',   [PaymentController::class, 'studentLedger'])->name('student-ledger');
        Route::get('/student/{studentId}/fees-json',[PaymentController::class, 'studentFeesJson'])->name('student-fees-json');
        Route::get('/{id}',                         [PaymentController::class, 'show'])->name('show');
        Route::get('/{id}/pay',                  [PaymentController::class, 'pay'])->name('pay');
        Route::post('/{id}/pay',                 [PaymentController::class, 'processPayment'])->name('process');
        Route::get('/receipt/{paymentId}',          [PaymentController::class, 'receiptPage'])->name('receipt');
        Route::get('/receipt/{paymentId}/page',     [PaymentController::class, 'receiptPage'])->name('receipt.page');
        Route::get('/receipt/{paymentId}/download', [PaymentController::class, 'receipt'])->name('receipt-download');
    });

    // ── LIBRARY (Module 12) ────────────────────────────────────────────────────

    // Book catalog
    Route::prefix('library')->name('library.')->group(function () {
        Route::get('/',                [LibraryController::class, 'index'])->name('index');
        Route::get('/create',          [LibraryController::class, 'create'])->name('create');
        Route::post('/',               [LibraryController::class, 'store'])->name('store');
        Route::get('/analytics',       [LibraryController::class, 'analytics'])->name('analytics');
        Route::get('/reports',         [LibraryController::class, 'reportForm'])->name('reports.form');
        Route::get('/reports/export',  [LibraryController::class, 'reportExport'])->name('reports.export');

        // Book categories
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/',         [BookCategoryController::class, 'index'])->name('index');
            Route::post('/',        [BookCategoryController::class, 'store'])->name('store');
            Route::put('/{id}',     [BookCategoryController::class, 'update'])->name('update')->where('id', '[0-9]+');
            Route::delete('/{id}',  [BookCategoryController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
        });

        // Book issue / return
        Route::prefix('issues')->name('issues.')->group(function () {
            Route::get('/',                        [BookIssueController::class, 'index'])->name('index');
            Route::get('/create',                  [BookIssueController::class, 'create'])->name('create');
            Route::post('/',                       [BookIssueController::class, 'store'])->name('store');
            Route::get('/return',                  [BookIssueController::class, 'returnForm'])->name('return');
            Route::post('/{id}/return',            [BookIssueController::class, 'processReturn'])->name('return.process')->where('id', '[0-9]+');
            Route::post('/{id}/lost',              [BookIssueController::class, 'markLost'])->name('lost')->where('id', '[0-9]+');
            Route::get('/scan',                    [BookIssueController::class, 'scanLookup'])->name('scan');
        });

        // Library members
        Route::prefix('members')->name('members.')->group(function () {
            Route::get('/',                        [LibraryMemberController::class, 'index'])->name('index');
            Route::get('/create',                  [LibraryMemberController::class, 'create'])->name('create');
            Route::post('/',                       [LibraryMemberController::class, 'store'])->name('store');
            Route::get('/{id}',                    [LibraryMemberController::class, 'show'])->name('show')->where('id', '[0-9]+');
            Route::get('/{id}/edit',               [LibraryMemberController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
            Route::put('/{id}',                    [LibraryMemberController::class, 'update'])->name('update')->where('id', '[0-9]+');
            Route::delete('/{id}',                 [LibraryMemberController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
            Route::post('/{id}/fine/settle',       [LibraryMemberController::class, 'settleFine'])->name('fine.settle')->where('id', '[0-9]+');
        });

        // E-books / digital library
        Route::prefix('ebooks')->name('ebooks.')->group(function () {
            Route::get('/',                        [EbookController::class, 'index'])->name('index');
            Route::get('/create',                  [EbookController::class, 'create'])->name('create');
            Route::post('/',                       [EbookController::class, 'store'])->name('store');
            Route::get('/{id}',                    [EbookController::class, 'show'])->name('show')->where('id', '[0-9]+');
            Route::get('/{id}/edit',               [EbookController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
            Route::put('/{id}',                    [EbookController::class, 'update'])->name('update')->where('id', '[0-9]+');
            Route::delete('/{id}',                 [EbookController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
            Route::get('/{id}/download',           [EbookController::class, 'download'])->name('download')->where('id', '[0-9]+');
            Route::post('/{id}/toggle',            [EbookController::class, 'toggleActive'])->name('toggle')->where('id', '[0-9]+');
        });

        // Wildcard book routes — MUST come after all named sub-routes
        Route::get('/{id}',            [LibraryController::class, 'show'])->name('show')->where('id', '[0-9]+');
        Route::get('/{id}/edit',       [LibraryController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
        Route::put('/{id}',            [LibraryController::class, 'update'])->name('update')->where('id', '[0-9]+');
        Route::delete('/{id}',         [LibraryController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
    });

    // ── TRANSPORT (Module 13) ─────────────────────────────────────────────────

    Route::prefix('transport')->name('transport.')->group(function () {

        // Dashboard
        Route::get('/',          [TransportController::class, 'index'])->name('index');
        Route::get('/analytics', [TransportController::class, 'analytics'])->name('analytics');
        Route::get('/reports',   [TransportController::class, 'reportForm'])->name('reports.form');
        Route::get('/reports/export', [TransportController::class, 'reportExport'])->name('reports.export');

        // Vehicles
        Route::prefix('vehicles')->name('vehicles.')->group(function () {
            Route::get('/',           [VehicleController::class, 'index'])->name('index');
            Route::get('/create',     [VehicleController::class, 'create'])->name('create');
            Route::post('/',          [VehicleController::class, 'store'])->name('store');
            Route::get('/{id}',       [VehicleController::class, 'show'])->name('show');
            Route::get('/{id}/edit',  [VehicleController::class, 'edit'])->name('edit');
            Route::put('/{id}',       [VehicleController::class, 'update'])->name('update');
            Route::delete('/{id}',    [VehicleController::class, 'destroy'])->name('destroy');
            // Fuel logs
            Route::post('/{id}/fuel',              [VehicleController::class, 'fuelStore'])->name('fuel.store');
            Route::delete('/{id}/fuel/{logId}',    [VehicleController::class, 'fuelDestroy'])->name('fuel.destroy');
            // Maintenance logs
            Route::post('/{id}/maintenance',               [VehicleController::class, 'maintStore'])->name('maintenance.store');
            Route::put('/{id}/maintenance/{logId}',        [VehicleController::class, 'maintUpdate'])->name('maintenance.update');
            // GPS hook
            Route::post('/{id}/gps',               [VehicleController::class, 'updateGps'])->name('gps.update');
        });

        // Drivers
        Route::prefix('drivers')->name('drivers.')->group(function () {
            Route::get('/',          [DriverController::class, 'index'])->name('index');
            Route::get('/create',    [DriverController::class, 'create'])->name('create');
            Route::post('/',         [DriverController::class, 'store'])->name('store');
            Route::get('/{id}',      [DriverController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [DriverController::class, 'edit'])->name('edit');
            Route::put('/{id}',      [DriverController::class, 'update'])->name('update');
            Route::delete('/{id}',   [DriverController::class, 'destroy'])->name('destroy');
        });

        // Routes
        Route::prefix('routes')->name('routes.')->group(function () {
            Route::get('/',          [TransportRouteController::class, 'index'])->name('index');
            Route::get('/create',    [TransportRouteController::class, 'create'])->name('create');
            Route::post('/',         [TransportRouteController::class, 'store'])->name('store');
            Route::get('/{id}',      [TransportRouteController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [TransportRouteController::class, 'edit'])->name('edit');
            Route::put('/{id}',      [TransportRouteController::class, 'update'])->name('update');
            Route::delete('/{id}',   [TransportRouteController::class, 'destroy'])->name('destroy');
            // Stops (nested under route)
            Route::post('/{routeId}/stops',              [TransportRouteController::class, 'stopsStore'])->name('stops.store');
            Route::put('/{routeId}/stops/{stopId}',      [TransportRouteController::class, 'stopsUpdate'])->name('stops.update');
            Route::delete('/{routeId}/stops/{stopId}',   [TransportRouteController::class, 'stopsDestroy'])->name('stops.destroy');
            // AJAX — get stops list for a route
            Route::get('/{routeId}/stops',               [StudentTransportController::class, 'getStops'])->name('stops.list');
        });

        // Student allocations
        Route::prefix('students')->name('students.')->group(function () {
            Route::get('/',          [StudentTransportController::class, 'index'])->name('index');
            Route::get('/create',    [StudentTransportController::class, 'create'])->name('create');
            Route::post('/',         [StudentTransportController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [StudentTransportController::class, 'edit'])->name('edit');
            Route::put('/{id}',      [StudentTransportController::class, 'update'])->name('update');
            Route::delete('/{id}',   [StudentTransportController::class, 'destroy'])->name('destroy');
        });

        // Attendance
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/',        [TransportAttendanceController::class, 'index'])->name('index');
            Route::post('/',       [TransportAttendanceController::class, 'store'])->name('store');
            Route::get('/report',  [TransportAttendanceController::class, 'report'])->name('report');
        });
    });

    // ── FINANCE (Module 10) ───────────────────────────────────────────────────

    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/',                         [FeeController::class, 'overview'])->name('overview');
        Route::get('/pos',                      [\App\Http\Controllers\PaymentController::class, 'posIndex'])->name('pos');
        Route::get('/pos/search-students',      [\App\Http\Controllers\PaymentController::class, 'posSearchStudents'])->name('pos.search-students');
        Route::get('/pos/student/{id}/details', [\App\Http\Controllers\PaymentController::class, 'posStudentDetails'])->name('pos.student-details');
        Route::post('/pos/collect',             [\App\Http\Controllers\PaymentController::class, 'posCollect'])->name('pos.collect');
        Route::get('/fee-profiles/{studentId?}', [FeeController::class, 'feeProfiles'])->name('fee-profiles');

        // Fee categories
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/',         [FeeController::class, 'categories'])->name('index');
            Route::post('/',        [FeeController::class, 'storeCategory'])->name('store');
            Route::put('/{id}',     [FeeController::class, 'updateCategory'])->name('update');
            Route::delete('/{id}',  [FeeController::class, 'destroyCategory'])->name('destroy');
        });

        // Fee structures
        Route::get('/structures',        [FeeController::class, 'structures'])->name('structures');
        Route::post('/structures',       [FeeController::class, 'storeStructure'])->name('structures.store');
        Route::put('/structures/{id}',   [FeeController::class, 'updateStructure'])->name('structures.update');
        Route::delete('/structures/{id}',[FeeController::class, 'destroyStructure'])->name('structures.destroy');

        // Discounts
        Route::get('/discounts',            [FeeController::class, 'discounts'])->name('discounts');
        Route::post('/discounts',           [FeeController::class, 'storeDiscount'])->name('discounts.store');
        Route::put('/discounts/{id}',       [FeeController::class, 'updateDiscount'])->name('discounts.update');
        Route::delete('/discounts/{id}',    [FeeController::class, 'destroyDiscount'])->name('discounts.destroy');
        Route::post('/discounts/assign',    [FeeController::class, 'assignDiscount'])->name('discounts.assign');
        Route::delete('/discounts/revoke/{id}', [FeeController::class, 'revokeDiscount'])->name('discounts.revoke');

        // Expenses
        Route::get('/expenses',           [ExpenseController::class, 'index'])->name('expenses');
        Route::post('/expenses',          [ExpenseController::class, 'store'])->name('expenses.store');
        Route::put('/expenses/{id}',      [ExpenseController::class, 'update'])->name('expenses.update');
        Route::delete('/expenses/{id}',   [ExpenseController::class, 'destroy'])->name('expenses.destroy');

        // Other income
        Route::get('/income',             [IncomeController::class, 'index'])->name('income');
        Route::post('/income',            [IncomeController::class, 'store'])->name('income.store');
        Route::put('/income/{id}',        [IncomeController::class, 'update'])->name('income.update');
        Route::delete('/income/{id}',     [IncomeController::class, 'destroy'])->name('income.destroy');

        // Reports + PDF receipt
        Route::get('/reports',            [FinanceReportController::class, 'form'])->name('reports');        // left-menu uses finance.reports
        Route::get('/reports/export',     [FinanceReportController::class, 'export'])->name('reports.export');
        Route::get('/receipt/{id}',       [FinanceReportController::class, 'receipt'])->name('receipt');
    });

    // Legacy payment routes handled by the payments prefix group above

    // ── HR & STAFF (Module 11) ────────────────────────────────────────────────

    // Staff CRUD (existing — keep names)
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
    Route::post('/staff/store', [StaffController::class, 'store'])->name('staff.store');
    Route::get('/staff/edit/{id}', [StaffController::class, 'edit'])->name('staff.edit');
    Route::match(['POST', 'PUT', 'PATCH'], '/staff/update/{id}', [StaffController::class, 'update'])->name('staff.update');
    Route::match(['POST', 'DELETE'], '/staff/delete/{id}', [StaffController::class, 'destroy'])->name('staff.destroy');
    Route::delete('/staff/{id}', [StaffController::class, 'destroy']);

    // Contracts
    Route::prefix('staff/contracts')->name('staff.contracts.')->group(function () {
        Route::get('/',          [StaffContractController::class, 'index'])->name('index');
        Route::post('/',         [StaffContractController::class, 'store'])->name('store');
        Route::put('/{id}',      [StaffContractController::class, 'update'])->name('update');
        Route::delete('/{id}',   [StaffContractController::class, 'destroy'])->name('destroy');
    });

    // Documents
    Route::prefix('staff/documents')->name('staff.documents.')->group(function () {
        Route::get('/',                 [StaffDocumentController::class, 'index'])->name('index');
        Route::post('/',                [StaffDocumentController::class, 'store'])->name('store');
        Route::post('/{id}/verify',     [StaffDocumentController::class, 'verify'])->name('verify');
        Route::delete('/{id}',          [StaffDocumentController::class, 'destroy'])->name('destroy');
    });

    // Training
    Route::prefix('staff/training')->name('staff.training.')->group(function () {
        Route::get('/',         [StaffTrainingController::class, 'index'])->name('index');
        Route::post('/',        [StaffTrainingController::class, 'store'])->name('store');
        Route::put('/{id}',     [StaffTrainingController::class, 'update'])->name('update');
        Route::delete('/{id}',  [StaffTrainingController::class, 'destroy'])->name('destroy');
    });

    // Exit records
    Route::prefix('staff/exit')->name('staff.exit.')->group(function () {
        Route::get('/',         [StaffExitController::class, 'index'])->name('index');
        Route::post('/',        [StaffExitController::class, 'store'])->name('store');
        Route::put('/{id}',     [StaffExitController::class, 'update'])->name('update');
        Route::delete('/{id}',  [StaffExitController::class, 'destroy'])->name('destroy');
    });

    // Recruitment
    Route::prefix('staff/recruitment')->name('staff.recruitment.')->group(function () {
        Route::get('/postings',                    [RecruitmentController::class, 'postings'])->name('postings');
        Route::post('/postings',                   [RecruitmentController::class, 'storePosting'])->name('postings.store');
        Route::put('/postings/{id}',               [RecruitmentController::class, 'updatePosting'])->name('postings.update');
        Route::delete('/postings/{id}',            [RecruitmentController::class, 'destroyPosting'])->name('postings.destroy');
        Route::get('/postings/{id}/applications',  [RecruitmentController::class, 'applications'])->name('applications');
        Route::put('/applications/{id}',           [RecruitmentController::class, 'updateApplication'])->name('applications.update');
        Route::delete('/applications/{id}',        [RecruitmentController::class, 'destroyApplication'])->name('applications.destroy');
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/',            [\App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::post('{id}/read',   [\App\Http\Controllers\NotificationController::class, 'markRead'])->name('read');
        Route::post('read-all',    [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('read-all');
        Route::delete('{id}',      [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('destroy');
    });

    // File Manager
    Route::prefix('files')->name('file.')->group(function () {
        Route::get('/',            [\App\Http\Controllers\FileManagerController::class, 'index'])->name('index');
        Route::post('upload',      [\App\Http\Controllers\FileManagerController::class, 'upload'])->name('upload');
        Route::get('{id}/serve',   [\App\Http\Controllers\FileManagerController::class, 'serve'])->name('serve');
        Route::delete('{id}',      [\App\Http\Controllers\FileManagerController::class, 'destroy'])->name('destroy');
    });

    // Two-Factor Authentication
    Route::prefix('two-factor')->name('two-factor.')->group(function () {
        Route::get('challenge',         [\App\Http\Controllers\TwoFactorController::class, 'showChallenge'])->name('challenge');
        Route::post('challenge',        [\App\Http\Controllers\TwoFactorController::class, 'verifyChallenge'])->name('challenge.verify');
        Route::get('setup',             [\App\Http\Controllers\TwoFactorController::class, 'showSetup'])->name('setup');
        Route::post('confirm',          [\App\Http\Controllers\TwoFactorController::class, 'confirmSetup'])->name('confirm');
        Route::get('recovery-codes',    [\App\Http\Controllers\TwoFactorController::class, 'recoveryCodes'])->name('recovery-codes');
        Route::post('disable',          [\App\Http\Controllers\TwoFactorController::class, 'disable'])->name('disable');
    });

    // ── TEACHER MANAGEMENT (Module 5) ────────────────────────────────────────
    Route::prefix('departments')->name('departments.')->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|hr-manager|hr|academic-coordinator'])->group(function () {
        Route::get('/',                              [DepartmentController::class, 'index'])->name('index');
        Route::post('/',                             [DepartmentController::class, 'store'])->name('store');
        Route::put('/{id}',                          [DepartmentController::class, 'update'])->name('update');
        Route::delete('/{id}',                       [DepartmentController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/teachers',                [DepartmentController::class, 'assignTeacher'])->name('teacher.assign');
        Route::delete('/{id}/teachers/{teacherId}',  [DepartmentController::class, 'removeTeacher'])->name('teacher.remove');
    });

    Route::prefix('teacher/{teacherId}')->name('teacher.')->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|hr-manager|hr'])->group(function () {
        Route::post('qualifications',       [TeacherQualificationController::class, 'store'])->name('qualifications.store');
        Route::delete('qualifications/{id}',[TeacherQualificationController::class, 'destroy'])->name('qualifications.destroy');
        Route::post('contracts',            [TeacherContractController::class, 'store'])->name('contracts.store');
        Route::put('contracts/{id}',        [TeacherContractController::class, 'update'])->name('contracts.update');
        Route::delete('contracts/{id}',     [TeacherContractController::class, 'destroy'])->name('contracts.destroy');
        Route::post('documents',            [TeacherDocumentController::class, 'store'])->name('documents.store');
        Route::post('documents/{id}/verify',[TeacherDocumentController::class, 'verify'])->name('documents.verify');
        Route::delete('documents/{id}',     [TeacherDocumentController::class, 'destroy'])->name('documents.destroy');
        Route::post('training',             [TeacherTrainingController::class, 'store'])->name('training.store');
        Route::delete('training/{id}',      [TeacherTrainingController::class, 'destroy'])->name('training.destroy');
        Route::post('reviews',              [PerformanceReviewController::class, 'store'])->name('reviews.store');
    });

    Route::get('teacher/attendance',          [TeacherAttendanceController::class, 'index'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|hr-manager|hr|teacher|class-teacher'])->name('teacher.attendance.index');
    Route::post('teacher/attendance',         [TeacherAttendanceController::class, 'store'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|hr-manager|hr'])->name('teacher.attendance.store');
    Route::get('teacher/{teacherId}/attendance', [TeacherAttendanceController::class, 'show'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|hr-manager|hr|teacher|class-teacher'])->name('teacher.attendance.show');

    Route::prefix('leave')->name('leave.')->group(function () {
        Route::get('/',              [LeaveController::class, 'index'])->name('index');
        Route::get('/apply',         [LeaveController::class, 'applyForm'])->name('apply');
        Route::get('/types',         [LeaveController::class, 'types'])->name('types');
        Route::post('/types',        [LeaveController::class, 'storeType'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|hr-manager|hr'])->name('types.store');
        Route::put('/types/{id}',    [LeaveController::class, 'updateType'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|hr-manager|hr'])->name('types.update');
        Route::delete('/types/{id}', [LeaveController::class, 'destroyType'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|hr-manager|hr'])->name('types.destroy');
        Route::post('/apply',        [LeaveController::class, 'apply'])->name('apply.store');
        Route::post('/{id}/review',  [LeaveController::class, 'review'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|hr-manager|hr'])->name('review');
        Route::post('/{id}/cancel',  [LeaveController::class, 'cancel'])->name('cancel');
    });

    Route::prefix('teacher/payroll')->name('teacher.payroll.')->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|hr-manager|hr|teacher|class-teacher'])->group(function () {
        Route::get('/',          [TeacherPayrollController::class, 'index'])->name('index');
        Route::post('/',         [TeacherPayrollController::class, 'store'])->name('store');
        Route::post('/{id}/paid',[TeacherPayrollController::class, 'markPaid'])->name('paid');
        Route::get('/{id}/slip', [TeacherPayrollController::class, 'slip'])->name('slip');
        Route::delete('/{id}',   [TeacherPayrollController::class, 'destroy'])->name('destroy');
    });

    Route::get('performance-reviews',      [PerformanceReviewController::class, 'index'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|hr-manager|hr'])->name('reviews.index');
    Route::put('performance-reviews/{id}', [PerformanceReviewController::class, 'update'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|hr-manager|hr'])->name('reviews.update');
    Route::delete('performance-reviews/{id}', [PerformanceReviewController::class, 'destroy'])->middleware(['role:super-admin|organization-owner|admin|principal|vice-principal|hr-manager|hr'])->name('reviews.destroy');
    Route::prefix('admissions')->name('admissions.')->group(function () {
        Route::get('/',              [AdmissionController::class, 'index'])->name('index');
        Route::get('/create',        [AdmissionController::class, 'create'])->name('create');
        Route::post('/',             [AdmissionController::class, 'store'])->name('store');
        Route::get('/{id}',          [AdmissionController::class, 'show'])->name('show');
        Route::post('/{id}/review',  [AdmissionController::class, 'review'])->name('review');
        Route::post('/{id}/enroll',  [AdmissionController::class, 'enroll'])->name('enroll');
        Route::delete('/{id}',       [AdmissionController::class, 'destroy'])->name('destroy');
    });

    // ── STUDENT DOCUMENTS ────────────────────────────────────────────────────
    Route::prefix('students/{studentId}/documents')->name('student.documents.')->group(function () {
        Route::post('/',         [StudentDocumentController::class, 'store'])->name('store');
        Route::post('/{id}/verify', [StudentDocumentController::class, 'verify'])->name('verify');
        Route::delete('/{id}',   [StudentDocumentController::class, 'destroy'])->name('destroy');
    });

    // ── MEDICAL RECORDS ──────────────────────────────────────────────────────
    Route::post('students/{studentId}/medical', [MedicalRecordController::class, 'upsert'])->name('student.medical.upsert');

    // ── EMERGENCY CONTACTS ───────────────────────────────────────────────────
    Route::prefix('students/{studentId}/contacts')->name('student.contacts.')->group(function () {
        Route::post('/',         [EmergencyContactController::class, 'store'])->name('store');
        Route::put('/{id}',      [EmergencyContactController::class, 'update'])->name('update');
        Route::delete('/{id}',   [EmergencyContactController::class, 'destroy'])->name('destroy');
    });

    // ── DISCIPLINARY RECORDS ─────────────────────────────────────────────────
    Route::prefix('students/{studentId}/discipline')->name('student.discipline.')->group(function () {
        Route::post('/',         [DisciplinaryRecordController::class, 'store'])->name('store');
    });
    Route::post('discipline/{id}/resolve', [DisciplinaryRecordController::class, 'resolve'])->name('student.discipline.resolve');
    Route::delete('discipline/{id}',       [DisciplinaryRecordController::class, 'destroy'])->name('student.discipline.destroy');

    // ── HOUSE ASSIGNMENTS ────────────────────────────────────────────────────
    Route::post('students/{studentId}/house',   [HouseAssignmentController::class, 'store'])->name('student.house.store');
    Route::delete('house/{id}',                 [HouseAssignmentController::class, 'destroy'])->name('student.house.destroy');

    // ── SCHOLARSHIPS ─────────────────────────────────────────────────────────
    Route::post('students/{studentId}/scholarships',     [ScholarshipController::class, 'store'])->name('student.scholarships.store');
    Route::put('scholarships/{id}',                       [ScholarshipController::class, 'update'])->name('student.scholarships.update');
    Route::delete('scholarships/{id}',                    [ScholarshipController::class, 'destroy'])->name('student.scholarships.destroy');

    // ── TRANSFERS ────────────────────────────────────────────────────────────
    Route::get('transfers',                                    [StudentTransferController::class, 'index'])->name('student.transfers.index');
    Route::get('transfers/{id}',                               [StudentTransferController::class, 'show'])->name('student.transfer.show');
    // Outgoing transfer (from a student's profile)
    Route::get('students/{studentId}/transfer/create',         [StudentTransferController::class, 'create'])->name('student.transfer.create');
    Route::post('students/{studentId}/transfer',               [StudentTransferController::class, 'store'])->name('student.transfer.store');
    // Incoming transfer (Scenario 3) — admit a new student with TC
    Route::get('transfers/incoming/create',                    [StudentTransferController::class, 'createIncoming'])->name('student.transfer.incoming.create');
    Route::post('transfers/incoming',                          [StudentTransferController::class, 'storeIncoming'])->name('student.transfer.incoming.store');
    // Approve / Reject
    Route::post('transfers/{id}/approve',                      [StudentTransferController::class, 'approve'])->name('student.transfer.approve');
    // Accept (destination school accepts a same-platform Scenario 1 transfer)
    Route::post('transfers/{id}/accept',                       [StudentTransferController::class, 'accept'])->name('student.transfer.accept');
    // TC PDF download
    Route::get('transfers/{id}/tc',                            [StudentTransferController::class, 'downloadTC'])->name('student.transfer.tc');
    // Delete
    Route::delete('transfers/{id}',                            [StudentTransferController::class, 'destroy'])->name('student.transfer.destroy');

    // ── STUDENT ID CARDS ─────────────────────────────────────────────────────
    Route::get('students/{studentId}/id-card',   [StudentIdCardController::class, 'generate'])->name('student.id-card');
    Route::get('students/id-cards/bulk',         [StudentIdCardController::class, 'bulkGenerate'])->name('student.id-cards.bulk');
    Route::post('students/id-cards/bulk',        [StudentIdCardController::class, 'bulkGenerate'])->name('student.id-card.bulk');

    // ── ID CARD TEMPLATES ──────────────────────────────────────────────────
    Route::prefix('id-card-templates')->name('id-card-templates.')->group(function () {
        Route::get('/',                          [IdCardTemplateController::class, 'index'])   ->name('index');
        Route::get('/new',                       [IdCardTemplateController::class, 'designer'])->name('create');
        Route::post('/',                         [IdCardTemplateController::class, 'save'])    ->name('store');
        Route::get('/{id}/edit',                 [IdCardTemplateController::class, 'designer'])->name('edit');
        Route::put('/{id}',                      [IdCardTemplateController::class, 'save'])    ->name('save');
        Route::patch('/{id}/activate',           [IdCardTemplateController::class, 'activate'])->name('activate');
        Route::delete('/{id}',                   [IdCardTemplateController::class, 'destroy']) ->name('destroy');
        Route::post('/preview',                  [IdCardTemplateController::class, 'preview']) ->name('preview');
    });

    // ── GRADUATION / STATUS MANAGEMENT ───────────────────────────────────────
    Route::get('graduation',               [GraduationController::class, 'index'])->name('students.graduation.index');
    Route::post('graduation/{id}/process', [GraduationController::class, 'process'])->name('students.graduation.process');
    Route::post('graduation/bulk',         [GraduationController::class, 'bulkGraduate'])->name('students.graduation.bulk');
    Route::get('alumni',                   [GraduationController::class, 'alumni'])->name('students.alumni');

    // ── ACHIEVEMENTS ─────────────────────────────────────────────────────────
    Route::post('students/{studentId}/achievements',  [AchievementController::class, 'store'])->name('student.achievements.store');
    Route::put('achievements/{id}',                   [AchievementController::class, 'update'])->name('student.achievements.update');
    Route::delete('achievements/{id}',                [AchievementController::class, 'destroy'])->name('student.achievements.destroy');
    Route::get('achievements/leaderboard',            [AchievementController::class, 'leaderboard'])->name('achievements.leaderboard');

    // ── MODULE 7: ACADEMIC — PROGRAMS ────────────────────────────────────────
    Route::prefix('programs')->name('programs.')->group(function () {
        Route::get('/',            [\App\Http\Controllers\ProgramController::class, 'index'])->name('index');
        Route::post('/',           [\App\Http\Controllers\ProgramController::class, 'store'])->name('store');
        Route::get('/{id}/edit',   [\App\Http\Controllers\ProgramController::class, 'edit'])->name('edit');
        Route::put('/{id}',        [\App\Http\Controllers\ProgramController::class, 'update'])->name('update');
        Route::delete('/{id}',     [\App\Http\Controllers\ProgramController::class, 'destroy'])->name('destroy');
    });

    // ── MODULE 7: ACADEMIC — TERMS ────────────────────────────────────────────
    Route::prefix('terms')->name('terms.')->group(function () {
        Route::get('/',         [\App\Http\Controllers\TermController::class, 'index'])->name('index');
        Route::post('/',        [\App\Http\Controllers\TermController::class, 'store'])->name('store');
        Route::put('/{id}',     [\App\Http\Controllers\TermController::class, 'update'])->name('update');
        Route::delete('/{id}',  [\App\Http\Controllers\TermController::class, 'destroy'])->name('destroy');
    });

    // ── MODULE 7: ACADEMIC — CURRICULUM ──────────────────────────────────────
    Route::prefix('curriculums')->name('curriculums.')->group(function () {
        Route::get('/',                              [\App\Http\Controllers\CurriculumController::class, 'index'])->name('index');
        Route::get('/create',                        [\App\Http\Controllers\CurriculumController::class, 'create'])->name('create');
        Route::post('/',                             [\App\Http\Controllers\CurriculumController::class, 'store'])->name('store');
        Route::get('/{id}',                          [\App\Http\Controllers\CurriculumController::class, 'show'])->name('show');
        Route::put('/{id}',                          [\App\Http\Controllers\CurriculumController::class, 'update'])->name('update');
        Route::delete('/{id}',                       [\App\Http\Controllers\CurriculumController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/topics',                  [\App\Http\Controllers\CurriculumController::class, 'storeTopic'])->name('topics.store');
        Route::delete('/topics/{topicId}',           [\App\Http\Controllers\CurriculumController::class, 'destroyTopic'])->name('topics.destroy');
    });

    // ── MODULE 7: ACADEMIC — LESSON PLANS ────────────────────────────────────
    Route::prefix('lesson-plans')->name('lesson-plans.')->group(function () {
        Route::get('/',            [\App\Http\Controllers\LessonPlanController::class, 'index'])->name('index');
        Route::get('/create',      [\App\Http\Controllers\LessonPlanController::class, 'create'])->name('create');
        Route::post('/',           [\App\Http\Controllers\LessonPlanController::class, 'store'])->name('store');
        Route::get('/{id}',        [\App\Http\Controllers\LessonPlanController::class, 'show'])->name('show');
        Route::get('/{id}/edit',   [\App\Http\Controllers\LessonPlanController::class, 'edit'])->name('edit');
        Route::put('/{id}',        [\App\Http\Controllers\LessonPlanController::class, 'update'])->name('update');
        Route::delete('/{id}',     [\App\Http\Controllers\LessonPlanController::class, 'destroy'])->name('destroy');
    });

    // ── MODULE 7: ACADEMIC — ADVANCED TIMETABLE ───────────────────────────────
    Route::get('/routine',                     [\App\Http\Controllers\RoutineController::class, 'index'])->name('routine.index');
    Route::get('/routine/{routine}/edit',      [\App\Http\Controllers\RoutineController::class, 'edit'])->name('routine.edit');
    Route::put('/routine/{routine}',           [\App\Http\Controllers\RoutineController::class, 'update'])->name('routine.update');
    Route::delete('/routine/{routine}',        [\App\Http\Controllers\RoutineController::class, 'destroy'])->name('routine.destroy');
    Route::get('/routine/teacher-timetable',   [\App\Http\Controllers\RoutineController::class, 'teacherTimetable'])->name('routine.teacher-timetable');

    // ── MODULE 7: ACADEMIC — HOMEWORK ─────────────────────────────────────────
    Route::prefix('homework')->name('homework.')->group(function () {
        Route::get('/',                          [\App\Http\Controllers\HomeworkController::class, 'index'])->name('index');
        Route::get('/create',                    [\App\Http\Controllers\HomeworkController::class, 'create'])->name('create');
        Route::post('/',                         [\App\Http\Controllers\HomeworkController::class, 'store'])->name('store');
        Route::get('/{id}',                      [\App\Http\Controllers\HomeworkController::class, 'show'])->name('show');
        Route::delete('/{id}',                   [\App\Http\Controllers\HomeworkController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/submit',              [\App\Http\Controllers\HomeworkController::class, 'submit'])->name('submit');
        Route::post('/submissions/{id}/grade',   [\App\Http\Controllers\HomeworkController::class, 'grade'])->name('grade');
        Route::post('/{id}/toggle-status',       [\App\Http\Controllers\HomeworkController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/submissions/{id}/annotate', [\App\Http\Controllers\HomeworkController::class, 'annotateView'])->name('submission.annotate');
        Route::post('/submissions/{id}/annotate/save', [\App\Http\Controllers\HomeworkController::class, 'saveAnnotation'])->name('submission.annotate.save');
    });

    // ── MODULE 7: ACADEMIC — PROJECTS ─────────────────────────────────────────
    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/',                          [\App\Http\Controllers\ProjectController::class, 'index'])->name('index');
        Route::get('/create',                    [\App\Http\Controllers\ProjectController::class, 'create'])->name('create');
        Route::post('/',                         [\App\Http\Controllers\ProjectController::class, 'store'])->name('store');
        Route::get('/{id}',                      [\App\Http\Controllers\ProjectController::class, 'show'])->name('show');
        Route::delete('/{id}',                   [\App\Http\Controllers\ProjectController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/submit',              [\App\Http\Controllers\ProjectController::class, 'submit'])->name('submit');
        Route::post('/submissions/{id}/grade',   [\App\Http\Controllers\ProjectController::class, 'grade'])->name('grade');
    });

    // ── MODULE 7: ACADEMIC — STUDY NOTES / LMS-LITE ──────────────────────────
    Route::prefix('study-notes')->name('study-notes.')->group(function () {
        Route::get('/',            [\App\Http\Controllers\StudyNoteController::class, 'index'])->name('index');
        Route::get('/create',      [\App\Http\Controllers\StudyNoteController::class, 'create'])->name('create');
        Route::post('/',           [\App\Http\Controllers\StudyNoteController::class, 'store'])->name('store');
        Route::delete('/{id}',     [\App\Http\Controllers\StudyNoteController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle',[\App\Http\Controllers\StudyNoteController::class, 'togglePublish'])->name('toggle');
    });

    // ── MODULE 7: ACADEMIC — ONLINE CLASSES ───────────────────────────────────
    Route::prefix('online-classes')->name('online-classes.')->group(function () {
        Route::get('/',                   [\App\Http\Controllers\OnlineClassController::class, 'index'])->name('index');
        Route::get('/create',             [\App\Http\Controllers\OnlineClassController::class, 'create'])->name('create');
        Route::post('/',                  [\App\Http\Controllers\OnlineClassController::class, 'store'])->name('store');
        Route::get('/{id}/edit',          [\App\Http\Controllers\OnlineClassController::class, 'edit'])->name('edit');
        Route::put('/{id}',               [\App\Http\Controllers\OnlineClassController::class, 'update'])->name('update');
        Route::delete('/{id}',            [\App\Http\Controllers\OnlineClassController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/status',       [\App\Http\Controllers\OnlineClassController::class, 'updateStatus'])->name('status');
    });

    // ── MODULE: DISCUSSION FORUM ──────────────────────────────────────────────
    Route::prefix('forum')->name('forum.')->group(function () {
        Route::get('/',                     [\App\Http\Controllers\ForumController::class, 'index'])->name('index');
        Route::get('/create',               [\App\Http\Controllers\ForumController::class, 'create'])->name('create');
        Route::post('/',                    [\App\Http\Controllers\ForumController::class, 'store'])->name('store');
        Route::get('/{id}',                 [\App\Http\Controllers\ForumController::class, 'show'])->name('show');
        Route::post('/{id}/reply',          [\App\Http\Controllers\ForumController::class, 'reply'])->name('reply.store');
        Route::post('/reply/{id}/vote',     [\App\Http\Controllers\ForumController::class, 'toggleVote'])->name('reply.vote');
        Route::post('/reply/{id}/solution', [\App\Http\Controllers\ForumController::class, 'markSolution'])->name('reply.solution');
        Route::post('/{id}/pin',            [\App\Http\Controllers\ForumController::class, 'togglePin'])->name('pin');
        Route::post('/{id}/lock',           [\App\Http\Controllers\ForumController::class, 'toggleLock'])->name('lock');
        Route::delete('/{id}',              [\App\Http\Controllers\ForumController::class, 'destroy'])->name('destroy');
    });

    // ── MODULE: FEEDBACK & GRIEVANCES ──────────────────────────────────────────
    Route::prefix('feedback')->name('feedback.')->group(function () {
        Route::get('/',                     [\App\Http\Controllers\FeedbackController::class, 'index'])->name('index');
        Route::get('/create',               [\App\Http\Controllers\FeedbackController::class, 'create'])->name('create');
        Route::post('/',                    [\App\Http\Controllers\FeedbackController::class, 'store'])->name('store');
        Route::get('/my',                   [\App\Http\Controllers\FeedbackController::class, 'myFeedback'])->name('my');
        Route::get('/{id}',                 [\App\Http\Controllers\FeedbackController::class, 'show'])->name('show');
        Route::post('/{id}/status',         [\App\Http\Controllers\FeedbackController::class, 'updateStatus'])->name('update-status');
    });

    // ── MODULE 6: PARENT PORTAL ───────────────────────────────────────────────
    Route::prefix('parent')->name('parent.')->middleware('role:parent')->group(function () {
        // Per-child views
        Route::get('/{studentId}/attendance', [\App\Http\Controllers\ParentPortalController::class, 'attendance'])->name('attendance');
        Route::get('/{studentId}/results',    [\App\Http\Controllers\ParentPortalController::class, 'results'])->name('results');
        Route::get('/{studentId}/fees',       [\App\Http\Controllers\ParentPortalController::class, 'fees'])->name('fees');
        Route::get('/{studentId}/fees/{invoiceId}/pay', [\App\Http\Controllers\ParentPortalController::class, 'payInvoice'])->name('fees.pay');
        Route::post('/{studentId}/fees/{invoiceId}/process', [\App\Http\Controllers\ParentPortalController::class, 'processInvoicePayment'])->name('fees.process');
        Route::get('/{studentId}/assignments', [\App\Http\Controllers\ParentPortalController::class, 'assignments'])->name('assignments');
        Route::get('/{studentId}/performance', [\App\Http\Controllers\ParentPortalController::class, 'performance'])->name('performance');
        Route::get('/{studentId}/leave',       [\App\Http\Controllers\ParentPortalController::class, 'leave'])->name('leave');
        Route::post('/{studentId}/leave',      [\App\Http\Controllers\ParentPortalController::class, 'submitLeave'])->name('leave.submit');
        Route::post('/{studentId}/leave/{applicationId}/cancel', [\App\Http\Controllers\ParentPortalController::class, 'cancelLeave'])->name('leave.cancel');
        // New routes
        Route::get('/{studentId}/homework',    [\App\Http\Controllers\ParentPortalController::class, 'homework'])->name('homework');
        Route::post('/{studentId}/homework/{homeworkId}/acknowledge', [\App\Http\Controllers\ParentPortalController::class, 'acknowledgeHomework'])->name('homework.acknowledge');
        Route::get('/{studentId}/timetable',   [\App\Http\Controllers\ParentPortalController::class, 'timetable'])->name('timetable');
        Route::get('/{studentId}/transport',   [\App\Http\Controllers\ParentPortalController::class, 'transport'])->name('transport');
        Route::get('/{studentId}/notices',     [\App\Http\Controllers\ParentPortalController::class, 'notices'])->name('notices');
        Route::get('/{studentId}/online-classes', [\App\Http\Controllers\ParentPortalController::class, 'onlineClasses'])->name('online-classes');
        
        // Modules from API translation
        Route::get('/{studentId}/teachers',    [\App\Http\Controllers\ParentPortalController::class, 'teachers'])->name('teachers');
        Route::get('/{studentId}/meetings',    [\App\Http\Controllers\ParentPortalController::class, 'meetings'])->name('meetings');
        Route::post('/{studentId}/meetings',   [\App\Http\Controllers\ParentPortalController::class, 'storeMeeting'])->name('meetings.store');
        Route::get('/{studentId}/events',      [\App\Http\Controllers\ParentPortalController::class, 'events'])->name('events');
        Route::get('/{studentId}/health',      [\App\Http\Controllers\ParentPortalController::class, 'health'])->name('health');
        Route::post('/{studentId}/health',     [\App\Http\Controllers\ParentPortalController::class, 'updateHealth'])->name('health.update');
        Route::get('/{studentId}/behavior',    [\App\Http\Controllers\ParentPortalController::class, 'behavior'])->name('behavior');
        Route::get('/{studentId}/documents',   [\App\Http\Controllers\ParentPortalController::class, 'documents'])->name('documents');
        Route::post('/{studentId}/documents',  [\App\Http\Controllers\ParentPortalController::class, 'uploadDocument'])->name('documents.upload');
        Route::get('/{studentId}/documents/{id}/download', [\App\Http\Controllers\ParentPortalController::class, 'downloadDocument'])->name('documents.download');

        // Messaging
        Route::get('/conversations',           [\App\Http\Controllers\ParentPortalController::class, 'conversations'])->name('conversations');
        Route::get('/conversations/new',       [\App\Http\Controllers\ParentPortalController::class, 'newConversation'])->name('conversation.new');
        Route::post('/conversations',          [\App\Http\Controllers\ParentPortalController::class, 'storeConversation'])->name('conversation.store');
        Route::get('/conversations/{id}',      [\App\Http\Controllers\ParentPortalController::class, 'showConversation'])->name('conversation.show');
        Route::post('/conversations/{id}/reply', [\App\Http\Controllers\ParentPortalController::class, 'sendMessage'])->name('conversation.reply');
    });

    // Teacher messaging (parent ↔ teacher inbox)
    Route::get('/teacher/messages',              [\App\Http\Controllers\ParentPortalController::class, 'teacherInbox'])->name('teacher.messages');
    Route::get('/teacher/messages/{id}',         [\App\Http\Controllers\ParentPortalController::class, 'teacherShowConversation'])->name('teacher.messages.show');
    Route::post('/teacher/messages/{id}/reply',  [\App\Http\Controllers\ParentPortalController::class, 'teacherReply'])->name('teacher.messages.reply');

    // Admin: link/unlink parent-student
    Route::get('/admin/parents/link',                            [\App\Http\Controllers\ParentPortalController::class, 'adminLinkPage'])->name('admin.parents.link');
    Route::get('/admin/parents/search',                          [\App\Http\Controllers\ParentPortalController::class, 'searchStudentsForLink'])->name('admin.parents.search');
    Route::post('/admin/parents/{parentId}/link',                [\App\Http\Controllers\ParentPortalController::class, 'linkStudent'])->name('admin.parents.link.store');
    Route::delete('/admin/parents/{parentId}/unlink/{studentId}',[\App\Http\Controllers\ParentPortalController::class, 'unlinkStudent'])->name('admin.parents.unlink');
    
    // Admin: Manage Parent Accounts
    Route::resource('parents', \App\Http\Controllers\AdminParentController::class)->except(['show']);
    Route::put('parents/{parent}/password', [\App\Http\Controllers\AdminParentController::class, 'updatePassword'])->name('parents.password.update');

    // ── MODULE 9: EXAM HALLS ──────────────────────────────────────────────────
    Route::get('/exam/halls',                   [ExamHallController::class, 'index'])->name('exam.hall.index');
    Route::post('/exam/halls',                  [ExamHallController::class, 'store'])->name('exam.hall.store');
    Route::put('/exam/halls/{id}',              [ExamHallController::class, 'update'])->name('exam.hall.update');
    Route::delete('/exam/halls/{id}',           [ExamHallController::class, 'destroy'])->name('exam.hall.destroy');
    Route::get('/exam/halls/{scheduleId}/seats',      [ExamHallController::class, 'seats'])->name('exam.hall.seats');
    Route::get('/exam/halls/{scheduleId}/seats/print',[ExamHallController::class, 'printSeats'])->name('exam.hall.seats.print');
    Route::post('/exam/halls/{scheduleId}/seats/auto',[ExamHallController::class, 'autoAllocate'])->name('exam.hall.seats.auto');
    Route::post('/exam/halls/{scheduleId}/seats',     [ExamHallController::class, 'saveSeats'])->name('exam.hall.seats.save');
    Route::delete('/exam/halls/{scheduleId}/seats',   [ExamHallController::class, 'clearSeats'])->name('exam.hall.seats.clear');

    // ── MODULE 9: EXAM SCHEDULE ───────────────────────────────────────────────
    Route::get('/exam/schedule',                [ExamScheduleController::class, 'index'])->name('exam.schedule.index');
    Route::get('/exam/schedule/create',         [ExamScheduleController::class, 'create'])->name('exam.schedule.create');
    Route::post('/exam/schedule',               [ExamScheduleController::class, 'store'])->name('exam.schedule.store');
    Route::get('/exam/schedule/{id}/edit',      [ExamScheduleController::class, 'edit'])->name('exam.schedule.edit');
    Route::put('/exam/schedule/{id}',           [ExamScheduleController::class, 'update'])->name('exam.schedule.update');
    Route::delete('/exam/schedule/{id}',        [ExamScheduleController::class, 'destroy'])->name('exam.schedule.destroy');
    Route::get('/exam/schedule/exams-for',      [ExamScheduleController::class, 'examsForFilter'])->name('exam.schedule.exams-for');

    // ── MODULE 9: RE-EXAM ─────────────────────────────────────────────────────
    Route::prefix('re-exam')->name('re-exam.')->group(function () {
        Route::get('/',               [ReExamController::class, 'index'])->name('index');
        Route::get('/apply',          [ReExamController::class, 'create'])->name('create');
        Route::post('/',              [ReExamController::class, 'store'])->name('store');
        Route::post('/{id}/review',   [ReExamController::class, 'review'])->name('review');
        Route::post('/{id}/result',   [ReExamController::class, 'enterResult'])->name('result');
        Route::post('/{id}/complete', [ReExamController::class, 'complete'])->name('complete');
    });

    // ── MODULE 9: ONLINE EXAMS ────────────────────────────────────────────────
    Route::prefix('online-exams')->name('online-exams.')->group(function () {
        // Student-facing exam experience
        Route::get('/my-exams',                       [\App\Http\Controllers\OnlineExamController::class, 'studentIndex'])->name('student.index');
        Route::get('/take/{id}',                      [\App\Http\Controllers\OnlineExamController::class, 'startAttempt'])->name('student.start');
        Route::post('/attempt/{attemptId}/save',      [\App\Http\Controllers\OnlineExamController::class, 'saveAnswer'])->name('student.save-answer');
        Route::post('/attempt/{attemptId}/submit',    [\App\Http\Controllers\OnlineExamController::class, 'submitAttempt'])->name('student.submit');
        Route::get('/attempt/{attemptId}/result',     [\App\Http\Controllers\OnlineExamController::class, 'result'])->name('student.result');

        // Admin / Teacher management
        Route::get('/',                               [\App\Http\Controllers\OnlineExamController::class, 'index'])->name('index');
        Route::get('/create',                         [\App\Http\Controllers\OnlineExamController::class, 'create'])->name('create');
        Route::post('/',                              [\App\Http\Controllers\OnlineExamController::class, 'store'])->name('store');
        Route::get('/{id}',                           [\App\Http\Controllers\OnlineExamController::class, 'show'])->name('show');
        Route::get('/{id}/edit',                      [\App\Http\Controllers\OnlineExamController::class, 'edit'])->name('edit');
        Route::put('/{id}',                           [\App\Http\Controllers\OnlineExamController::class, 'update'])->name('update');
        Route::delete('/{id}',                        [\App\Http\Controllers\OnlineExamController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-publish',          [\App\Http\Controllers\OnlineExamController::class, 'togglePublish'])->name('toggle-publish');
        Route::patch('/{id}/close',                   [\App\Http\Controllers\OnlineExamController::class, 'close'])->name('close');

        // Question management
        Route::get('/{id}/questions',                 [\App\Http\Controllers\OnlineExamController::class, 'questions'])->name('questions');
        Route::post('/{id}/questions',                [\App\Http\Controllers\OnlineExamController::class, 'storeQuestion'])->name('questions.store');
        Route::put('/questions/{questionId}',         [\App\Http\Controllers\OnlineExamController::class, 'updateQuestion'])->name('questions.update');
        Route::delete('/questions/{questionId}',      [\App\Http\Controllers\OnlineExamController::class, 'destroyQuestion'])->name('questions.destroy');

        // Attempts (grading view)
        Route::get('/{id}/attempts',                  [\App\Http\Controllers\OnlineExamController::class, 'attempts'])->name('attempts');
        Route::post('/attempt/{attemptId}/grade',     [\App\Http\Controllers\OnlineExamController::class, 'grade'])->name('grade');
    });

    // ── MODULE 9: PRACTICAL EXAMS ─────────────────────────────────────────────
    Route::prefix('practical-exams')->name('practical-exams.')->group(function () {
        Route::get('/',           [\App\Http\Controllers\PracticalExamController::class, 'index'])->name('index');
        Route::post('/',          [\App\Http\Controllers\PracticalExamController::class, 'store'])->name('store');
        Route::get('/{id}',       [\App\Http\Controllers\PracticalExamController::class, 'show'])->name('show');
        Route::put('/{id}',       [\App\Http\Controllers\PracticalExamController::class, 'update'])->name('update');
        Route::delete('/{id}',    [\App\Http\Controllers\PracticalExamController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/marks',[\App\Http\Controllers\PracticalExamController::class, 'saveMarks'])->name('marks.save');
        Route::get('/{id}/pdf',   [\App\Http\Controllers\PracticalExamController::class, 'pdf'])->name('pdf');
    });

    // ── MODULE 9: RESULTS & TRANSCRIPTS ──────────────────────────────────────
    Route::prefix('results')->name('results.')->group(function () {
        Route::get('/',                                   [ResultSheetController::class, 'index'])->name('index');
        Route::get('/class',                              [ResultSheetController::class, 'classResult'])->name('class');
        Route::get('/merit',                              [ResultSheetController::class, 'meritList'])->name('merit');
        Route::get('/analytics',                          [ResultSheetController::class, 'analytics'])->name('analytics');
        Route::get('/class/pdf',                          [ResultSheetController::class, 'classResultPdf'])->name('class.pdf');
        Route::get('/class/report-cards',                 [ResultSheetController::class, 'classReportCardsPdf'])->name('class.report-cards');
        Route::get('/class/excel',                        [ResultSheetController::class, 'classResultExcel'])->name('class.excel');
        Route::get('/student/{studentId}',                [ResultSheetController::class, 'studentResult'])->name('student');
        Route::get('/student/{studentId}/report-card',    [ResultSheetController::class, 'reportCard'])->name('report-card');
        Route::get('/transcript/{studentId}',             [TranscriptController::class, 'show'])->name('transcript');
        Route::get('/transcript/{studentId}/pdf',         [TranscriptController::class, 'pdf'])->name('transcript.pdf');
    });

    // ── MODULE 9: QUESTION PAPER TEMPLATES ───────────────────────────────────
    Route::prefix('question-paper-templates')->name('question-paper-templates.')->group(function () {
        Route::get('/',           [QuestionPaperTemplateController::class, 'index'])->name('index');
        Route::get('/create',     [QuestionPaperTemplateController::class, 'create'])->name('create');
        Route::post('/',          [QuestionPaperTemplateController::class, 'store'])->name('store');
        Route::get('/{id}',       [QuestionPaperTemplateController::class, 'show'])->name('show');
        Route::get('/{id}/edit',  [QuestionPaperTemplateController::class, 'edit'])->name('edit');
        Route::put('/{id}',       [QuestionPaperTemplateController::class, 'update'])->name('update');
        Route::delete('/{id}',    [QuestionPaperTemplateController::class, 'destroy'])->name('destroy');
    });

    // ── MODULE 9: QUESTION BANK ───────────────────────────────────────────────
    Route::prefix('question-bank')->name('question-bank.')->group(function () {
        Route::get('/',           [QuestionBankController::class, 'index'])->name('index');
        Route::get('/create',     [QuestionBankController::class, 'create'])->name('create');
        Route::post('/',          [QuestionBankController::class, 'store'])->name('store');
        Route::get('/{id}',       [QuestionBankController::class, 'show'])->name('show');
        Route::get('/{id}/edit',  [QuestionBankController::class, 'edit'])->name('edit');
        Route::put('/{id}',       [QuestionBankController::class, 'update'])->name('update');
        Route::delete('/{id}',    [QuestionBankController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/duplicate', [QuestionBankController::class, 'duplicate'])->name('duplicate');
        Route::post('/{id}/images',    [QuestionBankController::class, 'uploadImage'])->name('images.store');
        Route::delete('/images/{imageId}', [QuestionBankController::class, 'destroyImage'])->name('images.destroy');
    });

    // ── MODULE 9: QUESTION PAPERS ─────────────────────────────────────────────
    Route::prefix('question-papers')->name('question-papers.')->group(function () {
        Route::get('/',               [QuestionPaperController::class, 'index'])->name('index');
        Route::get('/create',         [QuestionPaperController::class, 'create'])->name('create');
        Route::post('/',              [QuestionPaperController::class, 'store'])->name('store');
        Route::get('/{id}',           [QuestionPaperController::class, 'show'])->name('show');
        Route::get('/{id}/edit',      [QuestionPaperController::class, 'edit'])->name('edit');
        Route::put('/{id}',           [QuestionPaperController::class, 'update'])->name('update');
        Route::delete('/{id}',        [QuestionPaperController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/pdf',       [QuestionPaperController::class, 'exportPdf'])->name('pdf');
        Route::get('/{id}/docx',      [QuestionPaperController::class, 'exportDocx'])->name('docx');
        Route::get('/{id}/versions',  [QuestionPaperController::class, 'versions'])->name('versions');
        Route::post('/versions/{versionId}/restore', [QuestionPaperController::class, 'restoreVersion'])->name('versions.restore');
        Route::post('/{id}/auto-save',[QuestionPaperController::class, 'autoSave'])->name('auto-save');
        Route::post('/{id}/ai-generate', [QuestionPaperController::class, 'aiGenerate'])->name('ai-generate');
        // Approval workflow
        Route::post('/{id}/submit',   [QuestionApprovalController::class, 'submit'])->name('submit');
        Route::post('/{id}/review',   [QuestionApprovalController::class, 'review'])->name('review');
        Route::post('/{id}/approve',  [QuestionApprovalController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject',   [QuestionApprovalController::class, 'reject'])->name('reject');
        Route::post('/{id}/lock',     [QuestionApprovalController::class, 'lock'])->name('lock');
        // Section + question AJAX
        Route::post('/{paperId}/sections',            [QuestionPaperController::class, 'addSection'])->name('sections.store');
        Route::put('/sections/{sectionId}',           [QuestionPaperController::class, 'updateSection'])->name('sections.update');
        Route::delete('/sections/{sectionId}',        [QuestionPaperController::class, 'deleteSection'])->name('sections.destroy');
        Route::post('/sections/{sectionId}/questions',[QuestionPaperController::class, 'addQuestion'])->name('questions.store');
        Route::put('/questions/{questionId}',         [QuestionPaperController::class, 'updateQuestion'])->name('questions.update');
        Route::delete('/questions/{questionId}',      [QuestionPaperController::class, 'deleteQuestion'])->name('questions.destroy');
        Route::post('/{paperId}/reorder-sections',    [QuestionPaperController::class, 'reorderSections'])->name('sections.reorder');
        Route::post('/sections/{sectionId}/reorder',  [QuestionPaperController::class, 'reorderQuestions'])->name('questions.reorder');
        Route::get('/{paperId}/bank-search',          [QuestionPaperController::class, 'bankSearch'])->name('bank-search');
    });

    // Approval dashboard (separate page)
    Route::get('/question-papers-pending', [QuestionApprovalController::class, 'index'])->name('question-papers.approvals');
    // Backward compatibility alias for exam.paper.create
    Route::get('/question-papers/create-redirect', [QuestionPaperController::class, 'create'])->name('exam.paper.create');

    // ── ROLE & PERMISSION MANAGEMENT ────────────────────────────────────────
    Route::middleware('permission:manage roles')->prefix('roles')->name('roles.')->group(function () {
        Route::get('/',                         [RoleController::class, 'index'])->name('index');
        Route::get('/users',                    [RoleController::class, 'assignableUsers'])->name('assignable-users');
        Route::get('/create',                   [RoleController::class, 'create'])->name('create');
        Route::post('/',                        [RoleController::class, 'store'])->name('store');
        Route::get('/{role}/edit',              [RoleController::class, 'edit'])->name('edit');
        Route::put('/{role}',                   [RoleController::class, 'update'])->name('update');
        Route::delete('/{role}',                [RoleController::class, 'destroy'])->name('destroy');
        Route::get('/matrix',                   [RoleController::class, 'matrix'])->name('matrix')->withoutMiddleware('permission:manage roles');
        Route::post('/matrix',                  [RoleController::class, 'matrixUpdate'])->name('matrix.update')->withoutMiddleware('permission:manage roles');
        Route::get('/users/{user}',             [RoleController::class, 'userRoles'])->name('user-roles');
        Route::put('/users/{user}',             [RoleController::class, 'updateUserRoles'])->name('user-roles.update');
    });

    // ── HOSTEL MANAGEMENT (Module 14) ────────────────────────────────────────
    Route::prefix('hostel')->name('hostel.')->group(function () {
        Route::resource('hostels',    \App\Http\Controllers\HostelController::class)
             ->only(['index','store','update','destroy']);
        Route::resource('rooms',      \App\Http\Controllers\HostelRoomController::class)
             ->only(['index','store','update','destroy']);
        Route::resource('beds',       \App\Http\Controllers\HostelBedController::class)
             ->only(['index','store','update','destroy']);
        Route::resource('allocations',\App\Http\Controllers\HostelAllocationController::class)
             ->only(['index','store','update','destroy']);
        Route::resource('attendances',\App\Http\Controllers\HostelAttendanceController::class)
             ->only(['index','store']);
        Route::resource('visitors',   \App\Http\Controllers\HostelVisitorController::class)
             ->only(['index','store','update','destroy']);
        Route::resource('maintenance',\App\Http\Controllers\HostelMaintenanceRequestController::class)
             ->only(['index','store','update','destroy']);
    });

    // ── RECEPTION & VISITOR MANAGEMENT ─────────────────────────────────────────
    Route::prefix('visitors')->name('visitors.')->group(function () {
        Route::get('/',                 [\App\Http\Controllers\VisitorController::class, 'index'])->name('index');
        Route::post('/',                [\App\Http\Controllers\VisitorController::class, 'store'])->name('store');
        Route::post('/{id}/checkout',   [\App\Http\Controllers\VisitorController::class, 'checkOut'])->name('checkout');
        Route::get('/{id}/badge',       [\App\Http\Controllers\VisitorController::class, 'printBadge'])->name('badge');
        Route::delete('/{id}',          [\App\Http\Controllers\VisitorController::class, 'destroy'])->name('destroy');
    });

    // ── AI FEATURES (Module 19) ──────────────────────────────────────────────
    Route::prefix('ai-features')->name('ai_features.web.')->group(function () {
        Route::get('/',                 [AiFeatureWebController::class, 'index'])->name('index');
        Route::get('/homework',         [AiFeatureWebController::class, 'homework'])->name('homework');
        Route::get('/notice',           [AiFeatureWebController::class, 'notice'])->name('notice');
        Route::get('/attendance',       [AiFeatureWebController::class, 'attendance'])->name('attendance');
        Route::get('/fee-prediction',   [AiFeatureWebController::class, 'feePrediction'])->name('fee');
        Route::get('/performance',      [AiFeatureWebController::class, 'performance'])->name('performance');
        Route::get('/chatbot',          [AiFeatureWebController::class, 'chatbot'])->name('chatbot');
        Route::get('/timetable',        [AiFeatureWebController::class, 'timetable'])->name('timetable');
        Route::get('/report',           [AiFeatureWebController::class, 'reportSummariser'])->name('report');
        Route::get('/remedial',         [AiFeatureWebController::class, 'remedial'])->name('remedial');
    });

    // ── CENTRAL ANALYTICS (Module 18) ─────────────────────────────────────────
    Route::get('/analytics/dashboard', [AnalyticsController::class, 'index'])
         ->name('central_analytics.index')
         ->middleware('role:admin|principal');

    // ── REPORT CENTER (Module 17) ─────────────────────────────────────────────
    Route::prefix('reports')->name('reports.')->middleware('role:admin|principal')->group(function () {
        Route::get('/',                [ReportCenterController::class, 'index'])->name('index');
        Route::get('/builder',         [ReportCenterController::class, 'builder'])->name('builder');
        Route::get('/builder/preview', [ReportCenterController::class, 'builderPreview'])->name('builder.preview');
        Route::get('/builder/export',  [ReportCenterController::class, 'builderExport'])->name('builder.export');
        Route::get('/schedule',        [ReportCenterController::class, 'schedule'])->name('schedule');
    });

    // ── RFID / BIOMETRIC (Module 8) ───────────────────────────────────────────
    Route::prefix('rfid')->name('rfid.')->middleware('role:admin|principal|attendance-officer')->group(function () {
        // Devices
        Route::get('/devices',             [\App\Http\Controllers\RfidController::class, 'devices'])->name('devices');
        Route::post('/devices',            [\App\Http\Controllers\RfidController::class, 'storeDevice'])->name('devices.store');
        Route::patch('/devices/{id}/toggle',[\App\Http\Controllers\RfidController::class, 'toggleDevice'])->name('devices.toggle');
        Route::delete('/devices/{id}',     [\App\Http\Controllers\RfidController::class, 'destroyDevice'])->name('devices.destroy');
        // Cards
        Route::get('/cards',               [\App\Http\Controllers\RfidController::class, 'cards'])->name('cards');
        Route::post('/cards',              [\App\Http\Controllers\RfidController::class, 'storeCard'])->name('cards.store');
        Route::patch('/cards/{id}/revoke', [\App\Http\Controllers\RfidController::class, 'revokeCard'])->name('cards.revoke');
        Route::delete('/cards/{id}',       [\App\Http\Controllers\RfidController::class, 'destroyCard'])->name('cards.destroy');
        // Scan log
        Route::get('/scans',               [\App\Http\Controllers\RfidController::class, 'scans'])->name('scans');
    });
    Route::prefix('communication')->name('communication.')->group(function () {
        Route::get('/',                    [CommunicationController::class, 'index'])->name('index');
        Route::get('/bulk',                [CommunicationController::class, 'bulk'])->name('bulk');
        Route::post('/bulk/send',          [CommunicationController::class, 'sendBulk'])->name('bulk.send');
        Route::get('/individual',          [CommunicationController::class, 'individual'])->name('individual');
        Route::get('/individual/search',   [CommunicationController::class, 'searchUsers'])->name('individual.search');
        Route::post('/individual/send',    [CommunicationController::class, 'sendIndividual'])->name('individual.send');
        Route::get('/chat',                [CommunicationController::class, 'chat'])->name('chat');
        Route::get('/settings',            [CommunicationController::class, 'settings'])->name('settings');
    });
    // WhatsApp webhook (no CSRF — Meta posts here)
    Route::get('/webhooks/whatsapp',  [CommunicationController::class, 'whatsappWebhookVerify'])->name('webhooks.whatsapp.verify');
    Route::post('/webhooks/whatsapp', [CommunicationController::class, 'whatsappWebhookReceive'])->name('webhooks.whatsapp.receive');

    // ── INVENTORY (Module 15) ─────────────────────────────────────────────────
    Route::prefix('inventory')->name('inventory.')->middleware('role:admin|principal')->group(function () {
        Route::get('/',                [App\Http\Controllers\InventoryController::class, 'index'])->name('dashboard');
        
        // Consumables (Items)
        Route::get('/items',           [App\Http\Controllers\InventoryController::class, 'items'])->name('items');
        Route::get('/items/create',    [App\Http\Controllers\InventoryController::class, 'createItem'])->name('items.create');
        Route::post('/items',          [App\Http\Controllers\InventoryController::class, 'storeItem'])->name('items.store');
        
        // Fixed Assets
        Route::get('/assets',          [App\Http\Controllers\InventoryController::class, 'assets'])->name('assets');
        Route::get('/assets/create',   [App\Http\Controllers\InventoryController::class, 'createAsset'])->name('assets.create');
        Route::post('/assets',         [App\Http\Controllers\InventoryController::class, 'storeAsset'])->name('assets.store');
        
        // Suppliers
        Route::get('/suppliers',            [App\Http\Controllers\InventoryController::class, 'suppliers'])->name('suppliers');
        Route::get('/suppliers/create',     [App\Http\Controllers\InventoryController::class, 'createSupplier'])->name('suppliers.create');
        Route::post('/suppliers',           [App\Http\Controllers\InventoryController::class, 'storeSupplier'])->name('suppliers.store');
        Route::put('/suppliers/{id}',       [App\Http\Controllers\InventoryController::class, 'updateSupplier'])->name('suppliers.update');
        Route::delete('/suppliers/{id}',    [App\Http\Controllers\InventoryController::class, 'destroySupplier'])->name('suppliers.destroy');
        
        // Purchase Orders
        Route::get('/purchase-orders', [App\Http\Controllers\InventoryController::class, 'purchaseOrders'])->name('purchase-orders');
        Route::get('/purchase-orders/create', [App\Http\Controllers\InventoryController::class, 'createPurchaseOrder'])->name('purchase-orders.create');
        Route::post('/purchase-orders',[App\Http\Controllers\InventoryController::class, 'storePurchaseOrder'])->name('purchase-orders.store');
    });

    // ── HR MANAGEMENT (Module 11) ─────────────────────────────────────────────
    Route::prefix('hr')->name('hr.')->middleware('role:super-admin|organization-owner|admin|principal|vice-principal|hr-manager|hr')->group(function () {
        Route::get('/',            [HrController::class, 'index'])->name('dashboard');
        Route::get('/leaves',      [HrController::class, 'leaves'])->name('leaves');
        Route::post('/leaves/{id}/status', [HrController::class, 'updateLeaveStatus'])->name('leaves.status');
        Route::get('/payroll',     [HrController::class, 'payroll'])->name('payroll');
        Route::get('/recruitment', [HrController::class, 'recruitment'])->name('recruitment');
        Route::get('/employees',   [HrController::class, 'employees'])->name('employees');
    });

    // ── FINANCE EXTRAS (Module 10) — Salary Slips, Tax Rates, Ledger, P&L ────
    Route::prefix('finance')->name('finance.')->group(function () {
        // Salary Slips (staff payroll)
        Route::prefix('salary')->name('salary.')->group(function () {
            Route::get('/',               [\App\Http\Controllers\SalarySlipController::class, 'index'])->name('index');
            Route::post('/',              [\App\Http\Controllers\SalarySlipController::class, 'store'])->name('store');
            Route::post('/bulk-generate', [\App\Http\Controllers\SalarySlipController::class, 'bulkGenerate'])->name('bulk-generate');
            Route::patch('/{id}/approve', [\App\Http\Controllers\SalarySlipController::class, 'approve'])->name('approve');
            Route::patch('/{id}/paid',    [\App\Http\Controllers\SalarySlipController::class, 'markPaid'])->name('paid');
            Route::delete('/{id}',        [\App\Http\Controllers\SalarySlipController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/pdf',       [\App\Http\Controllers\SalarySlipController::class, 'pdf'])->name('pdf');

            // Salary components (master definitions)
            Route::get('/components',         [\App\Http\Controllers\SalarySlipController::class, 'components'])->name('components');
            Route::post('/components',        [\App\Http\Controllers\SalarySlipController::class, 'storeComponent'])->name('components.store');
            Route::put('/components/{id}',    [\App\Http\Controllers\SalarySlipController::class, 'updateComponent'])->name('components.update');
            Route::delete('/components/{id}', [\App\Http\Controllers\SalarySlipController::class, 'destroyComponent'])->name('components.destroy');

            // Per-staff salary structure
            Route::get('/structure/{staffId}',  [\App\Http\Controllers\SalarySlipController::class, 'staffStructure'])->name('structure');
            Route::post('/structure/{staffId}', [\App\Http\Controllers\SalarySlipController::class, 'saveStaffStructure'])->name('structure.save');
        });

        // Tax rates
        Route::prefix('tax')->name('tax.')->group(function () {
            Route::get('/',        [\App\Http\Controllers\SalarySlipController::class, 'taxRates'])->name('index');
            Route::post('/',       [\App\Http\Controllers\SalarySlipController::class, 'storeTaxRate'])->name('store');
            Route::put('/{id}',    [\App\Http\Controllers\SalarySlipController::class, 'updateTaxRate'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\SalarySlipController::class, 'destroyTaxRate'])->name('destroy');
        });

        // Accounting ledger
        Route::get('/ledger',      [\App\Http\Controllers\LedgerController::class, 'index'])->name('ledger');
        Route::post('/ledger/sync',[\App\Http\Controllers\LedgerController::class, 'sync'])->name('ledger.sync');

        // Profit & Loss
        Route::get('/profit-loss',     [\App\Http\Controllers\LedgerController::class, 'profitLoss'])->name('profit-loss');
        Route::get('/profit-loss/pdf', [\App\Http\Controllers\LedgerController::class, 'profitLossPdf'])->name('profit-loss.pdf');
        Route::get('/balance-sheet',   [\App\Http\Controllers\LedgerController::class, 'balanceSheet'])->name('balance-sheet');
        Route::get('/balance-sheet/pdf',[\App\Http\Controllers\LedgerController::class, 'balanceSheetPdf'])->name('balance-sheet.pdf');

        // Day Book (रोजनाम्चा / दैनिक हिसाब)
        Route::get('/day-book',        [\App\Http\Controllers\LedgerController::class, 'dayBook'])->name('day-book');
        Route::get('/day-book/pdf',    [\App\Http\Controllers\LedgerController::class, 'dayBookPdf'])->name('day-book.pdf');

        // Fee types master list
        Route::prefix('fee-types')->name('fee-types.')->group(function () {
            Route::get('/',        [\App\Http\Controllers\FeeController::class, 'feeTypes'])->name('index');
            Route::post('/',       [\App\Http\Controllers\FeeController::class, 'storeFeeType'])->name('store');
            Route::put('/{id}',    [\App\Http\Controllers\FeeController::class, 'updateFeeType'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\FeeController::class, 'destroyFeeType'])->name('destroy');
        });
    });

    // ── FEE INSTALLMENT PLANS ─────────────────────────────────────────────────
    Route::prefix('finance/invoices/{invoiceId}/installments')->name('finance.installments.')->group(function () {
        Route::get('/',                [\App\Http\Controllers\FeeInstallmentController::class, 'index'])->name('index');
        Route::post('/',               [\App\Http\Controllers\FeeInstallmentController::class, 'store'])->name('store');
        Route::delete('/',             [\App\Http\Controllers\FeeInstallmentController::class, 'destroy'])->name('destroy');
        Route::get('/auto-split',      [\App\Http\Controllers\FeeInstallmentController::class, 'autoSplit'])->name('auto-split');
    });
    Route::post('finance/installments/{id}/pay', [\App\Http\Controllers\FeeInstallmentController::class, 'markPaid'])->name('finance.installments.pay');

    // ── TDS REMITTANCE TRACKER ────────────────────────────────────────────────
    Route::prefix('finance/tds')->name('finance.tds.')->group(function () {
        Route::get('/',        [\App\Http\Controllers\TdsRemittanceController::class, 'index'])->name('index');
        Route::post('/deposit',[\App\Http\Controllers\TdsRemittanceController::class, 'deposit'])->name('deposit');
    });

    // Payment gateway callbacks (outside auth middleware — gateways hit these)
    Route::get('/finance/paypal/return',          fn() => redirect()->route('finance.overview')->with('status', 'PayPal payment captured.'))->name('finance.paypal.return');
    Route::get('/finance/paypal/cancel',          fn() => redirect()->route('finance.overview')->with('error', 'PayPal payment cancelled.'))->name('finance.paypal.cancel');
    Route::get('/finance/flutterwave/callback',   fn() => redirect()->route('finance.overview')->with('status', 'Flutterwave payment received.'))->name('finance.flutterwave.callback');

    // ── ESEWA / KHALTI GATEWAY CALLBACKS (authenticated — redirect after login) ─
    Route::get('/finance/esewa/success',  [\App\Http\Controllers\OnlinePaymentController::class, 'esewaSuccess'])->name('finance.esewa.success');
    Route::get('/finance/esewa/failure',  [\App\Http\Controllers\OnlinePaymentController::class, 'esewaFailure'])->name('finance.esewa.failure');
    Route::get('/finance/khalti/callback',[\App\Http\Controllers\OnlinePaymentController::class, 'khaltiCallback'])->name('finance.khalti.callback');
    Route::post('/finance/initiate',      [\App\Http\Controllers\OnlinePaymentController::class, 'initiate'])->middleware('throttle:15,1')->name('finance.payment.initiate');

    // ── PAYMENT PROOF (QR payment evidence submission & approval) ─────────
    // Parent/student: submit proof after scanning QR (rate limited)
    Route::get('/payment-proof/{invoiceId}',  [\App\Http\Controllers\PaymentProofController::class, 'showForm'])->name('payment-proof.show');
    Route::post('/payment-proof/{invoiceId}', [\App\Http\Controllers\PaymentProofController::class, 'submit'])->middleware('throttle:10,1')->name('payment-proof.submit');

    // Admin/Accountant: review queue
    Route::get('/payment-proofs',                   [\App\Http\Controllers\PaymentProofController::class, 'index'])->name('payment-proofs.index');
    Route::post('/payment-proofs/{proofId}/approve',[\App\Http\Controllers\PaymentProofController::class, 'approve'])->name('payment-proofs.approve');
    Route::post('/payment-proofs/{proofId}/reject', [\App\Http\Controllers\PaymentProofController::class, 'reject'])->name('payment-proofs.reject');

    // ── CERTIFICATE MANAGEMENT ────────────────────────────────────────────
    // Template CRUD
    Route::prefix('certificates')->name('certificates.')->group(function () {
        Route::get('/',                           [CertificateController::class, 'index'])->name('index');
        Route::get('/designs',                    [CertificateController::class, 'designsGallery'])->name('designs.index');
        Route::get('/designs/{designId}/preview', [CertificateController::class, 'designPreview'])->name('design.preview');
        Route::get('/designs/{designId}/apply',   [CertificateController::class, 'designApply'])->name('design.apply');
        Route::get('/create',                     [CertificateController::class, 'create'])->name('create');
        Route::post('/',                          [CertificateController::class, 'store'])->name('store');
        Route::get('/{id}/edit',                  [CertificateController::class, 'edit'])->name('edit');
        Route::put('/{id}',                       [CertificateController::class, 'update'])->name('update');
        Route::delete('/{id}',                    [CertificateController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/preview',               [CertificateController::class, 'preview'])->name('preview');

        // ── Visual Drag & Drop Builder ────────────────────────────────
        Route::get('/{id}/builder',               [CertificateController::class, 'builder'])->name('builder');
        Route::post('/{id}/builder',              [CertificateController::class, 'builderSave'])->name('builder.save');
        Route::post('/{id}/builder/background',   [CertificateController::class, 'builderUploadBackground'])->name('builder.background');

        // Issued certificates ledger
        Route::get('/issued',                     [CertificateController::class, 'issued'])->name('issued');
        Route::get('/issued/{id}/download',       [CertificateController::class, 'download'])->name('download');
        Route::post('/issued/{id}/revoke',        [CertificateController::class, 'revoke'])->name('revoke');

        // Bulk & Single issue
        Route::get('/bulk',                       [CertificateController::class, 'bulkForm'])->name('bulk');
        Route::post('/bulk',                      [CertificateController::class, 'bulkIssue'])->name('bulk.issue');
        Route::post('/issue-single',              [CertificateController::class, 'issueSingle'])->name('issue.single');
    });

    // Issue certificate to a specific student
    Route::get('/students/{studentId}/certificates',        [CertificateController::class, 'issueForm'])->name('certificates.issue.form');
    Route::post('/students/{studentId}/certificates',       [CertificateController::class, 'issue'])->name('certificates.issue');

});

// ── Certificate public verification (no auth required — accessed via QR) ──
Route::get('/certificates/verify/{token}', [CertificateController::class, 'verify'])->name('certificates.verify');

// Marksheet / Grade Sheet public verification (no auth required — scanned via QR on Marksheet PDF)
Route::get('/results/verify/{token}', [\App\Http\Controllers\ResultSheetController::class, 'verifyMarksheet'])->name('results.verify');

// TC public verification (no auth required — receiving school scans QR)
Route::get('/transfer/verify/{token}', [StudentTransferController::class, 'verifyTC'])->name('student.transfer.verify');

// ── Firebase Push Notification token endpoints ────────────────────────────
Route::middleware('auth')->group(function () {
    Route::post  ('/push/token', [\App\Http\Controllers\PushTokenController::class, 'store'])->name('push.token.store');
    Route::delete('/push/token', [\App\Http\Controllers\PushTokenController::class, 'destroy'])->name('push.token.destroy');
});

// ── Student ID Card Public Verification & QR Quick Login (no auth required) ──
Route::get('/verify/student/{token}', [\App\Http\Controllers\StudentVerificationController::class, 'show'])->name('student.verify');
Route::get('/qr-login/{token}', [\App\Http\Controllers\StudentVerificationController::class, 'qrLogin'])->name('student.qr-login');
Route::post('/login/qr-scan', [\App\Http\Controllers\StudentVerificationController::class, 'loginFromScan'])->name('login.qr-scan');

// ── Teacher ID Card Public Verification & QR Quick Login (no auth required) ──
Route::get('/verify/teacher/{token}', [\App\Http\Controllers\TeacherVerificationController::class, 'show'])->name('teacher.verify');
Route::get('/qr-login-teacher/{token}', [\App\Http\Controllers\TeacherVerificationController::class, 'qrLogin'])->name('teacher.qr-login');

// ── Teacher ID Card PDF Generation (authenticated) ──
Route::middleware(['auth'])->get('/teachers/id-card/{teacherId}', [\App\Http\Controllers\TeacherIdCardController::class, 'single'])->name('teacher.id-card');
Route::middleware(['auth'])->get('/teachers/id-cards/bulk', [\App\Http\Controllers\TeacherIdCardController::class, 'bulk'])->name('teacher.id-cards.bulk');

// ── Global search for command palette (authenticated) ──
Route::middleware(['auth'])->get('/search', [SettingController::class, 'globalSearch'])->name('global.search');

// ── Offline PWA — seed data (GET) + sync batch (POST) ─────────────────────
Route::middleware(['auth'])->get('/api/offline/seed-data', \App\Http\Controllers\OfflineSeedController::class)->name('offline.seed');
Route::middleware(['auth'])->post('/api/offline/sync', [\App\Http\Controllers\OfflineSyncController::class, 'sync'])->name('offline.sync');

// ── School Profile Management (authenticated) ──
Route::middleware(['auth'])->prefix('school/profile')->name('school.profile.')->group(function () {
    Route::get('/', [SchoolProfileController::class, 'edit'])->name('edit');
    Route::post('/', [SchoolProfileController::class, 'update'])->name('update');
    Route::get('/preview', [SchoolLandingController::class, 'preview'])->name('preview');
    // Dedicated Payment QR management page
    Route::get('/payment-qr',  [SchoolProfileController::class, 'paymentQr'])->name('payment-qr');
    Route::post('/payment-qr', [SchoolProfileController::class, 'updatePaymentQr'])->name('payment-qr.update');
});
// ── Public School Multi-Page Portal Routes (no auth required) ──
Route::prefix('school/{slug}')->group(function () {
    Route::get('/',            [SchoolLandingController::class, 'show'])->name('school.landing');
    Route::get('/about',       [SchoolLandingController::class, 'about'])->name('school.landing.about');
    Route::get('/academics',   [SchoolLandingController::class, 'academics'])->name('school.landing.academics');
    Route::get('/admissions',  [SchoolLandingController::class, 'admissions'])->name('school.landing.admissions');
    Route::get('/notices',     [SchoolLandingController::class, 'notices'])->name('school.landing.notices');
    Route::get('/facilities',  [SchoolLandingController::class, 'facilities'])->name('school.landing.facilities');
    Route::get('/faculty',     [SchoolLandingController::class, 'faculty'])->name('school.landing.faculty');
    Route::get('/gallery',     [SchoolLandingController::class, 'gallery'])->name('school.landing.gallery');
    Route::get('/contact',     [SchoolLandingController::class, 'contact'])->name('school.landing.contact');
});

Route::domain('{subdomain}.' . env('APP_DOMAIN', 'nshlms.com'))->group(function () {
    Route::get('/',            [SchoolLandingController::class, 'showBySubdomain'])->name('school.subdomain');
    Route::get('/about',       [SchoolLandingController::class, 'aboutBySubdomain'])->name('school.subdomain.about');
    Route::get('/academics',   [SchoolLandingController::class, 'academicsBySubdomain'])->name('school.subdomain.academics');
    Route::get('/admissions',  [SchoolLandingController::class, 'admissionsBySubdomain'])->name('school.subdomain.admissions');
    Route::get('/notices',     [SchoolLandingController::class, 'noticesBySubdomain'])->name('school.subdomain.notices');
    Route::get('/facilities',  [SchoolLandingController::class, 'facilitiesBySubdomain'])->name('school.subdomain.facilities');
    Route::get('/faculty',     [SchoolLandingController::class, 'facultyBySubdomain'])->name('school.subdomain.faculty');
    Route::get('/gallery',     [SchoolLandingController::class, 'galleryBySubdomain'])->name('school.subdomain.gallery');
    Route::get('/contact',     [SchoolLandingController::class, 'contactBySubdomain'])->name('school.subdomain.contact');
    Route::get('/school/{slug}', [SchoolLandingController::class, 'showBySubdomain']);
});

// Payment gateway webhooks — no CSRF (raw POST from gateways)
Route::post('/webhooks/stripe',      [\App\Http\Controllers\WebhookController::class, 'stripe'])->name('webhooks.stripe');
Route::post('/webhooks/paypal',      [\App\Http\Controllers\WebhookController::class, 'paypal'])->name('webhooks.paypal');
Route::post('/webhooks/razorpay',    [\App\Http\Controllers\WebhookController::class, 'razorpay'])->name('webhooks.razorpay');
Route::post('/webhooks/flutterwave', [\App\Http\Controllers\WebhookController::class, 'flutterwave'])->name('webhooks.flutterwave');
Route::post('/webhooks/esewa',       [\App\Http\Controllers\WebhookController::class, 'esewa'])->name('webhooks.esewa');
Route::post('/webhooks/khalti',      [\App\Http\Controllers\WebhookController::class, 'khalti'])->name('webhooks.khalti');

// ═══════════════════════════════════════════════════════════════════════════
// NEPALSCHOOLHUB SAAS ROUTES
// ═══════════════════════════════════════════════════════════════════════════

// Dynamic Auto-SEO Sitemap
Route::get('/sitemap.xml', function () {
    $lastmod = now()->toAtomString();
    $schools = \App\Models\School::where('status', 'active')->get();

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    $xml .= '    <url>' . "\n";
    $xml .= '        <loc>' . htmlspecialchars(url('/')) . '</loc>' . "\n";
    $xml .= '        <lastmod>' . $lastmod . '</lastmod>' . "\n";
    $xml .= '        <changefreq>daily</changefreq>' . "\n";
    $xml .= '        <priority>1.0</priority>' . "\n";
    $xml .= '    </url>' . "\n";
    $xml .= '    <url>' . "\n";
    $xml .= '        <loc>' . htmlspecialchars(route('saas.register')) . '</loc>' . "\n";
    $xml .= '        <lastmod>' . $lastmod . '</lastmod>' . "\n";
    $xml .= '        <changefreq>weekly</changefreq>' . "\n";
    $xml .= '        <priority>0.9</priority>' . "\n";
    $xml .= '    </url>' . "\n";
    $xml .= '    <url>' . "\n";
    $xml .= '        <loc>' . htmlspecialchars(route('login')) . '</loc>' . "\n";
    $xml .= '        <lastmod>' . $lastmod . '</lastmod>' . "\n";
    $xml .= '        <changefreq>monthly</changefreq>' . "\n";
    $xml .= '        <priority>0.8</priority>' . "\n";
    $xml .= '    </url>' . "\n";

    foreach ($schools as $school) {
        $schoolUrl = url('/school/' . $school->slug);
        $schoolMod = $school->updated_at ? $school->updated_at->toAtomString() : $lastmod;
        $xml .= '    <url>' . "\n";
        $xml .= '        <loc>' . htmlspecialchars($schoolUrl) . '</loc>' . "\n";
        $xml .= '        <lastmod>' . $schoolMod . '</lastmod>' . "\n";
        $xml .= '        <changefreq>daily</changefreq>' . "\n";
        $xml .= '        <priority>0.8</priority>' . "\n";
        $xml .= '    </url>' . "\n";
    }

    $xml .= '</urlset>';

    return response($xml, 200)->header('Content-Type', 'text/xml; charset=UTF-8');
});

Route::get('/robots.txt', function () {
    $content = "User-agent: *\n";
    $content .= "Allow: /\n";
    $content .= "Disallow: /home\n";
    $content .= "Disallow: /admin\n";
    $content .= "Disallow: /super\n";
    $content .= "Disallow: /api/\n";
    $content .= "Disallow: /storage/\n";
    $content .= "\n";
    $content .= "User-agent: GPTBot\n";
    $content .= "Allow: /\n";
    $content .= "\n";
    $content .= "User-agent: PerplexityBot\n";
    $content .= "Allow: /\n";
    $content .= "\n";
    $content .= "User-agent: ClaudeBot\n";
    $content .= "Allow: /\n";
    $content .= "\n";
    $content .= "User-agent: Google-Extended\n";
    $content .= "Allow: /\n";
    $content .= "\n";
    $content .= "Sitemap: ".url('/sitemap.xml')."\n";
    return response($content, 200)->header('Content-Type', 'text/plain');
});

// Public landing page
Route::get('/', [\App\Http\Controllers\SaasLandingController::class, 'index'])->name('saas.landing');
Route::post('/contact', [\App\Http\Controllers\SaasLandingController::class, 'contact'])->name('saas.contact');

// Test Email endpoint
Route::get('/send-test-email', function (\Illuminate\Http\Request $request) {
    $to = $request->query('email', '98059shankar@gmail.com');
    try {
        $logoUrl = 'https://nshlms.com/imgs/logo_icon_192.webp';
        $currentYear = date('Y');
        $timestamp = now()->toDayDateTimeString();

        $htmlContent = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='utf-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Nepal School Hub</title>
            </head>
            <body style='margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
                <table width='100%' border='0' cellspacing='0' cellpadding='0' style='background-color: #f1f5f9; padding: 40px 16px;'>
                    <tr>
                        <td align='center'>
                            <table width='100%' border='0' cellspacing='0' cellpadding='0' style='max-width: 600px; background-color: #ffffff; border-radius: 24px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;'>
                                
                                <!-- Brand Header -->
                                <tr>
                                    <td style='background: linear-gradient(135deg, #1e1b4b 0%, #4338ca 50%, #3b82f6 100%); padding: 36px 32px; text-align: center;'>
                                        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                            <tr>
                                                <td align='center'>
                                                    <div style='display: inline-block; background: #ffffff; padding: 8px; border-radius: 20px; box-shadow: 0 8px 20px rgba(0,0,0,0.18); margin-bottom: 16px;'>
                                                        <img src='{$logoUrl}' alt='Nepal School Hub' width='56' height='56' style='display: block; border-radius: 14px; object-contain: cover;'>
                                                    </div>
                                                    <h1 style='color: #ffffff; font-size: 24px; font-weight: 800; margin: 0 0 6px 0; letter-spacing: -0.5px;'>Nepal School Hub</h1>
                                                    <p style='color: rgba(255,255,255,0.85); font-size: 13px; font-weight: 500; margin: 0;'>Next-Gen Smart Campus &amp; School ERP Platform</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Main Body Card -->
                                <tr>
                                    <td style='padding: 36px 32px;'>
                                        
                                        <!-- Verification Badge -->
                                        <div style='background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 14px; padding: 16px 20px; margin-bottom: 28px;'>
                                            <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                                                <tr>
                                                    <td width='28' valign='top' style='padding-right: 12px;'>
                                                        <div style='background-color: #10b981; color: #ffffff; width: 24px; height: 24px; border-radius: 50%; text-align: center; line-height: 24px; font-size: 14px; font-weight: bold;'>✓</div>
                                                    </td>
                                                    <td>
                                                        <strong style='color: #065f46; font-size: 15px; display: block; margin-bottom: 2px;'>SMTP Service Operational</strong>
                                                        <span style='color: #047857; font-size: 13px;'>Your mail delivery system is online and verified.</span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>

                                        <h2 style='color: #0f172a; font-size: 18px; font-weight: 700; margin: 0 0 12px 0;'>Hello Shankar,</h2>
                                        <p style='color: #475569; font-size: 14px; line-height: 1.65; margin: 0 0 24px 0;'>
                                            This official test message confirms that automated email dispatch, Firebase Cloud integration, and custom domain delivery for <strong>Nepal School Hub (NSH LMS)</strong> are configured properly and operating smoothly.
                                        </p>

                                        <!-- Diagnostic Details Table -->
                                        <table width='100%' border='0' cellspacing='0' cellpadding='0' style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; margin-bottom: 28px;'>
                                            <tr>
                                                <td style='padding: 14px 18px; border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 13px; font-weight: 600; width: 130px;'>Recipient</td>
                                                <td style='padding: 14px 18px; border-bottom: 1px solid #e2e8f0; color: #0f172a; font-size: 13px; font-weight: 700;'>{$to}</td>
                                            </tr>
                                            <tr>
                                                <td style='padding: 14px 18px; border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 13px; font-weight: 600;'>Sender Identity</td>
                                                <td style='padding: 14px 18px; border-bottom: 1px solid #e2e8f0; color: #0f172a; font-size: 13px; font-weight: 600;'>noreply@nepalschoolhub.firebaseapp.com</td>
                                            </tr>
                                            <tr>
                                                <td style='padding: 14px 18px; border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 13px; font-weight: 600;'>Transport</td>
                                                <td style='padding: 14px 18px; border-bottom: 1px solid #e2e8f0; color: #4338ca; font-size: 13px; font-weight: 700;'>smtp.gmail.com : 587 (TLS Secured)</td>
                                            </tr>
                                            <tr>
                                                <td style='padding: 14px 18px; color: #64748b; font-size: 13px; font-weight: 600;'>Dispatched At</td>
                                                <td style='padding: 14px 18px; color: #0f172a; font-size: 13px;'>{$timestamp}</td>
                                            </tr>
                                        </table>

                                        <!-- Call to action button -->
                                        <div style='text-align: center; margin-bottom: 16px;'>
                                            <a href='https://nshlms.com' target='_blank' style='display: inline-block; background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); color: #ffffff; font-size: 14px; font-weight: 700; text-decoration: none; padding: 14px 32px; border-radius: 12px; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);'>
                                                Open Nepal School Hub Portal &rarr;
                                            </a>
                                        </div>

                                    </td>
                                </tr>

                                <!-- Footer -->
                                <tr>
                                    <td style='background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 24px 32px; text-align: center;'>
                                        <p style='color: #64748b; font-size: 12px; line-height: 1.5; margin: 0 0 8px 0;'>
                                            <strong>Nepal School Hub (NSH LMS)</strong> &middot; Empowering 200+ Institutions across Nepal
                                        </p>
                                        <p style='color: #94a3b8; font-size: 11px; margin: 0;'>
                                            &copy; {$currentYear} Tech Procod Pvt. Ltd. &middot; Kathmandu, Nepal. All rights reserved.
                                        </p>
                                    </td>
                                </tr>

                            </table>
                        </td>
                    </tr>
                </table>
            </body>
            </html>
        ";

        \Illuminate\Support\Facades\Mail::html($htmlContent, function ($message) use ($to) {
            $message->to($to)
                ->subject('✓ Official Confirmation: Nepal School Hub Mail System Online');
        });

        return response()->json([
            'success' => true,
            'message' => 'Professional test email successfully sent to ' . $to,
            'recipient' => $to,
            'timestamp' => now()->toDateTimeString(),
            'sender' => config('mail.from.address'),
            'mailer' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
});

// School registration (public) — throttled to prevent spam
Route::get('/register/school',         [\App\Http\Controllers\SchoolRegistrationController::class, 'create'])->name('saas.register');
Route::post('/register/school',        [\App\Http\Controllers\SchoolRegistrationController::class, 'store'])->middleware('throttle:5,1')->name('saas.register.store');
Route::get('/register/school/success', [\App\Http\Controllers\SchoolRegistrationController::class, 'registered'])->name('saas.registered');

// Super-admin school management panel
Route::middleware(['auth', 'role:super-admin'])
     ->prefix('super')
     ->name('super.')
     ->group(function () {
         Route::get('/',                           [\App\Http\Controllers\HomeController::class, 'index']);
         Route::get('/dashboard',                 [\App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');
         Route::get('/schools',                    [\App\Http\Controllers\SuperAdmin\SchoolManagementController::class, 'index'])->name('schools.index');
         Route::get('/schools/{school}',           [\App\Http\Controllers\SuperAdmin\SchoolManagementController::class, 'show'])->name('schools.show');
         Route::post('/schools/{school}/approve',  [\App\Http\Controllers\SuperAdmin\SchoolManagementController::class, 'approve'])->name('schools.approve');
         Route::post('/schools/{school}/suspend',  [\App\Http\Controllers\SuperAdmin\SchoolManagementController::class, 'suspend'])->name('schools.suspend');
         Route::post('/schools/{school}/reactivate',[\App\Http\Controllers\SuperAdmin\SchoolManagementController::class, 'reactivate'])->name('schools.reactivate');
         Route::patch('/schools/{school}/plan',    [\App\Http\Controllers\SuperAdmin\SchoolManagementController::class, 'changePlan'])->name('schools.plan');
         Route::delete('/schools/{school}',        [\App\Http\Controllers\SuperAdmin\SchoolManagementController::class, 'destroy'])->name('schools.destroy');
         Route::post('/schools/{school}/impersonate', [\App\Http\Controllers\SuperAdmin\SchoolManagementController::class, 'impersonate'])->name('schools.impersonate');
         Route::get('/impersonate/stop',           [\App\Http\Controllers\SuperAdmin\SchoolManagementController::class, 'stopImpersonate'])->name('impersonate.stop');

         // ── SMC / Governance ────────────────────────────────────────────────
         Route::get('/schools/{school}/committee',          [\App\Http\Controllers\SuperAdmin\SchoolManagementController::class, 'showCommittee'])->name('schools.committee');
         Route::post('/schools/{school}/committee/members', [\App\Http\Controllers\SuperAdmin\SchoolManagementController::class, 'storeCommitteeMember'])->name('schools.committee.members.store');
         Route::patch('/schools/{school}/committee/members/{member}', [\App\Http\Controllers\SuperAdmin\SchoolManagementController::class, 'updateCommitteeMember'])->name('schools.committee.members.update');
         Route::delete('/schools/{school}/committee/members/{member}', [\App\Http\Controllers\SuperAdmin\SchoolManagementController::class, 'destroyCommitteeMember'])->name('schools.committee.members.destroy');
         Route::post('/schools/{school}/committee/members/{member}/document', [\App\Http\Controllers\SuperAdmin\SchoolManagementController::class, 'uploadCommitteeDocument'])->name('schools.committee.document.upload');
         Route::delete('/schools/{school}/committee/documents/{mediaFile}', [\App\Http\Controllers\SuperAdmin\SchoolManagementController::class, 'destroyCommitteeDocument'])->name('schools.committee.document.destroy');

         // ── Contact Messages Inbox ────────────────────────────────────────
         Route::get('/contact-messages',                                    [\App\Http\Controllers\ContactMessagesController::class, 'index'])->name('contact-messages.index');
         Route::get('/contact-messages/{contactMessage}',                   [\App\Http\Controllers\ContactMessagesController::class, 'show'])->name('contact-messages.show');
         Route::patch('/contact-messages/{contactMessage}/status',          [\App\Http\Controllers\ContactMessagesController::class, 'updateStatus'])->name('contact-messages.status');
         Route::patch('/contact-messages/{contactMessage}/notes',           [\App\Http\Controllers\ContactMessagesController::class, 'saveNotes'])->name('contact-messages.notes');
         Route::delete('/contact-messages/{contactMessage}',                [\App\Http\Controllers\ContactMessagesController::class, 'destroy'])->name('contact-messages.destroy');
     });


// ═══════════════════════════════════════════════════════════════════════════
// MODULE 23 — INFRASTRUCTURE ROUTES
// ═══════════════════════════════════════════════════════════════════════════

// ── PWA offline fallback page (cached by service worker) ──────────────────
Route::get('/offline', function () {
    return view('offline');
})->name('pwa.offline');

// ── Laravel Horizon dashboard ──────────────────────────────────────────────
// Access is gated inside HorizonServiceProvider::gate() to super-admin / Admin roles.
// The Horizon package self-registers its routes (including 'horizon.index') via HorizonServiceProvider.


// ═══════════════════════════════════════════════════════════════════════════
// MODULE 24 — INTERACTIVE LIVE QUIZ COMPETITION SYSTEM
// ═══════════════════════════════════════════════════════════════════════════

// ── Teacher / Host Management & Projector Console (Authenticated) ────────
Route::middleware(['auth'])->group(function () {
    Route::resource('live-quiz', \App\Http\Controllers\LiveQuizController::class);
    Route::post('live-quiz/seed-demo', [\App\Http\Controllers\LiveQuizController::class, 'seedDemoQuiz'])->name('live-quiz.seed-demo');
    Route::post('live-quiz/{quiz}/questions', [\App\Http\Controllers\LiveQuizController::class, 'storeQuestion'])->name('live-quiz.questions.store');
    Route::delete('live-quiz/{quiz}/questions/{question}', [\App\Http\Controllers\LiveQuizController::class, 'deleteQuestion'])->name('live-quiz.questions.destroy');

    // Host Projector Views
    Route::get('live-quiz/host/create/{quiz}', [\App\Http\Controllers\LiveQuizHostController::class, 'createSession'])->name('live-quiz.host.create-session');
    Route::get('live-quiz/host/lobby/{session}', [\App\Http\Controllers\LiveQuizHostController::class, 'lobbyView'])->name('live-quiz.host.lobby');
    Route::get('quiz/host/game/{session}', [\App\Http\Controllers\LiveQuizHostController::class, 'gameView'])->name('live-quiz.host.game');

    // Host Real-Time Control APIs
    Route::get('quiz/host/api/state/{session}', [\App\Http\Controllers\LiveQuizHostController::class, 'getHostLiveState'])->name('live-quiz.host.state');
    Route::post('quiz/host/api/start/{session}', [\App\Http\Controllers\LiveQuizHostController::class, 'startLobbyGame'])->name('live-quiz.host.start');
    Route::post('quiz/host/api/next/{session}', [\App\Http\Controllers\LiveQuizHostController::class, 'nextQuestion'])->name('live-quiz.host.next');
    Route::post('quiz/host/api/lock/{session}', [\App\Http\Controllers\LiveQuizHostController::class, 'lockQuestion'])->name('live-quiz.host.lock');
    Route::post('quiz/host/api/pass/{session}', [\App\Http\Controllers\LiveQuizHostController::class, 'passQuestion'])->name('live-quiz.host.pass');
    Route::post('quiz/host/api/judge/{session}', [\App\Http\Controllers\LiveQuizHostController::class, 'judgeDirectAnswer'])->name('live-quiz.host.judge');
    Route::post('quiz/host/api/reveal/{session}', [\App\Http\Controllers\LiveQuizHostController::class, 'revealAnswer'])->name('live-quiz.host.reveal');
    Route::post('quiz/host/api/leaderboard/{session}', [\App\Http\Controllers\LiveQuizHostController::class, 'showLeaderboard'])->name('live-quiz.host.leaderboard');
    Route::post('quiz/host/api/finish/{session}', [\App\Http\Controllers\LiveQuizHostController::class, 'finishGame'])->name('live-quiz.host.finish');
    Route::delete('quiz/host/api/team/{session}/{team}', [\App\Http\Controllers\LiveQuizHostController::class, 'removeTeam'])->name('live-quiz.host.remove-team');
});

// ── Captain Mobile Player Routes (Public Access via 6-Digit PIN) ──────────
Route::get('quiz/join', [\App\Http\Controllers\LiveQuizPlayerController::class, 'joinView'])->name('live-quiz.player.join');
Route::get('quiz/play/{pin}', [\App\Http\Controllers\LiveQuizPlayerController::class, 'playerGameView'])->name('live-quiz.player.play');
Route::post('quiz/player/api/join', [\App\Http\Controllers\LiveQuizPlayerController::class, 'joinSession'])->name('live-quiz.player.api-join');
Route::post('quiz/player/api/submit', [\App\Http\Controllers\LiveQuizPlayerController::class, 'submitAnswer'])->name('live-quiz.player.submit');
Route::post('quiz/player/api/buzz', [\App\Http\Controllers\LiveQuizPlayerController::class, 'buzzIn'])->name('live-quiz.player.buzz');
Route::post('quiz/player/api/pass', [\App\Http\Controllers\LiveQuizPlayerController::class, 'passMyTurn'])->name('live-quiz.player.pass');
Route::post('quiz/player/api/security-event', [\App\Http\Controllers\LiveQuizPlayerController::class, 'recordSecurityEvent'])->name('live-quiz.player.security-event');
Route::get('quiz/player/api/state/{pin}', [\App\Http\Controllers\LiveQuizPlayerController::class, 'getPlayerLiveState'])->name('live-quiz.player.state');

// Error Page Previews
Route::get('/500', fn() => view('errors.500'));
Route::get('/419', fn() => view('errors.419'));
Route::get('/404', fn() => view('errors.404'));
Route::get('/403', fn() => view('errors.403'));
Route::get('/503', fn() => view('errors.503'));
Route::get('/401', fn() => view('errors.401'));
Route::get('/429', fn() => view('errors.429'));

// Navigation & Shortcut Compatibility Redirects
Route::middleware(['auth'])->group(function () {
    Route::redirect('/students/id-card', '/students/id-cards/bulk');
    Route::redirect('/grades', '/exams/grade/view');
    Route::redirect('/invoices', '/payments');
    Route::redirect('/library/books', '/library');
});







