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
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\GradingSystemController;
use App\Http\Controllers\SchoolSessionController;
use App\Http\Controllers\AcademicSettingController;
use App\Http\Controllers\AssignedTeacherController;
use App\Http\Controllers\Auth\UpdatePasswordController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\StudentDocumentController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\EmergencyContactController;
use App\Http\Controllers\DisciplinaryRecordController;
use App\Http\Controllers\HouseAssignmentController;
use App\Http\Controllers\ScholarshipController;
use App\Http\Controllers\StudentTransferController;
use App\Http\Controllers\StudentIdCardController;
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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::middleware(['auth'])->group(function () {

    Route::prefix('school')->name('school.')->group(function () {
        Route::post('session/create', [SchoolSessionController::class, 'store'])->name('session.store');
        Route::post('session/browse', [SchoolSessionController::class, 'browse'])->name('session.browse');

        Route::post('semester/create', [SemesterController::class, 'store'])->name('semester.create');
        Route::post('final-marks-submission-status/update', [AcademicSettingController::class, 'updateFinalMarksSubmissionStatus'])->name('final.marks.submission.status.update');

        Route::post('attendance/type/update', [AcademicSettingController::class, 'updateAttendanceType'])->name('attendance.type.update');

        // Class
        Route::post('class/create', [SchoolClassController::class, 'store'])->name('class.create');
        Route::post('class/update', [SchoolClassController::class, 'update'])->name('class.update');

        // Sections
        Route::post('section/create', [SectionController::class, 'store'])->name('section.create');
        Route::post('section/update', [SectionController::class, 'update'])->name('section.update');

        // Courses
        Route::post('course/create', [CourseController::class, 'store'])->name('course.create');
        Route::post('course/update', [CourseController::class, 'update'])->name('course.update');

        // Teacher
        Route::post('teacher/create', [UserController::class, 'storeTeacher'])->name('teacher.create');
        Route::post('teacher/update', [UserController::class, 'updateTeacher'])->name('teacher.update');
        Route::post('teacher/assign', [AssignedTeacherController::class, 'store'])->name('teacher.assign');

        // Student
        Route::post('student/create', [UserController::class, 'storeStudent'])->name('student.create');
        Route::post('student/update', [UserController::class, 'updateStudent'])->name('student.update');
    });


    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Attendance
    Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendances/view', [AttendanceController::class, 'show'])->name('attendance.list.show');
    Route::get('/attendances/take', [AttendanceController::class, 'create'])->name('attendance.create.show');
    Route::post('/attendances', [AttendanceController::class, 'store'])->name('attendances.store');

    // Classes and sections
    Route::get('/classes', [SchoolClassController::class, 'index']);
    Route::get('/class/edit/{id}', [SchoolClassController::class, 'edit'])->name('class.edit');
    Route::get('/sections', [SectionController::class, 'getByClassId'])->name('get.sections.courses.by.classId');
    Route::get('/section/edit/{id}', [SectionController::class, 'edit'])->name('section.edit');

    // Teachers
    Route::get('/teachers/add', function () {
        return view('teachers.add');
    })->name('teacher.create.show');
    Route::get('/teachers/edit/{id}', [UserController::class, 'editTeacher'])->name('teacher.edit.show');
    Route::get('/teachers/view/list', [UserController::class, 'getTeacherList'])->name('teacher.list.show');
    Route::get('/teachers/view/profile/{id}', [UserController::class, 'showTeacherProfile'])->name('teacher.profile.show');

    //Students
    Route::get('/students/add', [UserController::class, 'createStudent'])->name('student.create.show');
    Route::get('/students/edit/{id}', [UserController::class, 'editStudent'])->name('student.edit.show');
    Route::get('/students/view/list', [UserController::class, 'getStudentList'])->name('student.list.show');
    Route::get('/students/view/profile/{id}', [UserController::class, 'showStudentProfile'])->name('student.profile.show');
    Route::get('/students/view/attendance/{id}', [AttendanceController::class, 'showStudentAttendance'])->name('student.attendance.show');

    // Marks
    Route::get('/marks/create', [MarkController::class, 'create'])->name('course.mark.create');
    Route::post('/marks/store', [MarkController::class, 'store'])->name('course.mark.store');
    Route::get('/marks/results', [MarkController::class, 'index'])->name('course.mark.list.show');
    // Route::get('/marks/view', function () {
    //     return view('marks.view');
    // });
    Route::get('/marks/view', [MarkController::class, 'showCourseMark'])->name('course.mark.show');
    Route::get('/marks/final/submit', [MarkController::class, 'showFinalMark'])->name('course.final.mark.submit.show');
    Route::post('/marks/final/submit', [MarkController::class, 'storeFinalMark'])->name('course.final.mark.submit.store');

    // Exams
    Route::get('/exams/view', [ExamController::class, 'index'])->name('exam.list.show');
    // Route::get('/exams/view/history', function () {
    //     return view('exams.history');
    // });
    Route::post('/exams/create', [ExamController::class, 'store'])->name('exam.create');
    // Route::post('/exams/delete', [ExamController::class, 'delete'])->name('exam.delete');
    Route::get('/exams/create', [ExamController::class, 'create'])->name('exam.create.show');
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
    Route::post('/exams/grade/delete-rule', [GradeRuleController::class, 'destroy'])->name('exam.grade.system.rule.delete');

    // Promotions
    Route::get('/promotions/index', [PromotionController::class, 'index'])->name('promotions.index');
    Route::get('/promotions/promote', [PromotionController::class, 'create'])->name('promotions.create');
    Route::post('/promotions/promote', [PromotionController::class, 'store'])->name('promotions.store');

    // Academic settings
    Route::get('/academics/settings', [AcademicSettingController::class, 'index']);

    // Calendar events
    Route::get('calendar-event', [EventController::class, 'index'])->name('events.show');
    Route::post('calendar-crud-ajax', [EventController::class, 'calendarEvents'])->name('events.crud');

    // Routines
    Route::get('/routine/create', [RoutineController::class, 'create'])->name('section.routine.create');
    Route::get('/routine/view', [RoutineController::class, 'show'])->name('section.routine.show');
    Route::post('/routine/store', [RoutineController::class, 'store'])->name('section.routine.store');

    // Syllabus
    Route::get('/syllabus/create', [SyllabusController::class, 'create'])->name('class.syllabus.create');
    Route::post('/syllabus/create', [SyllabusController::class, 'store'])->name('syllabus.store');
    Route::get('/syllabus/index', [SyllabusController::class, 'index'])->name('course.syllabus.index');

    // Notices
    Route::get('/notice/create', [NoticeController::class, 'create'])->name('notice.create');
    Route::post('/notice/create', [NoticeController::class, 'store'])->name('notice.store');

    // Courses
    Route::get('courses/teacher/index', [AssignedTeacherController::class, 'getTeacherCourses'])->name('course.teacher.list.show');
    Route::get('courses/student/index/{student_id}', [CourseController::class, 'getStudentCourses'])->name('course.student.list.show');
    Route::get('course/edit/{id}', [CourseController::class, 'edit'])->name('course.edit');

    // Assignment
    Route::get('courses/assignments/index', [AssignmentController::class, 'getCourseAssignments'])->name('assignment.list.show');
    Route::get('courses/assignments/create', [AssignmentController::class, 'create'])->name('assignment.create');
    Route::post('courses/assignments/create', [AssignmentController::class, 'store'])->name('assignment.store');

    // Update password
    Route::get('password/edit', [UpdatePasswordController::class, 'edit'])->name('password.change');
    Route::post('password/edit', [UpdatePasswordController::class, 'update'])->name('password.change.update');

    // Library
    Route::get('/library', [LibraryController::class, 'index'])->name('library.index');
    Route::get('/library/create', [LibraryController::class, 'create'])->name('library.create');
    Route::post('/library/store', [LibraryController::class, 'store'])->name('library.store');
    Route::get('/library/edit/{id}', [LibraryController::class, 'edit'])->name('library.edit');
    Route::post('/library/update/{id}', [LibraryController::class, 'update'])->name('library.update');
    Route::post('/library/delete/{id}', [LibraryController::class, 'destroy'])->name('library.destroy');

    // Staff
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
    Route::post('/staff/store', [StaffController::class, 'store'])->name('staff.store');
    Route::get('/staff/edit/{id}', [StaffController::class, 'edit'])->name('staff.edit');
    Route::post('/staff/update/{id}', [StaffController::class, 'update'])->name('staff.update');
    Route::post('/staff/delete/{id}', [StaffController::class, 'destroy'])->name('staff.destroy');

    // Payment
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments/store', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/pay/{id}', [PaymentController::class, 'pay'])->name('payments.pay');
    Route::post('/payments/process/{id}', [PaymentController::class, 'processPayment'])->name('payments.process');

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
        Route::get('challenge',    [\App\Http\Controllers\TwoFactorController::class, 'showChallenge'])->name('challenge');
        Route::post('challenge',   [\App\Http\Controllers\TwoFactorController::class, 'verifyChallenge'])->name('challenge.verify');
        Route::get('setup',        [\App\Http\Controllers\TwoFactorController::class, 'showSetup'])->name('setup');
        Route::post('disable',     [\App\Http\Controllers\TwoFactorController::class, 'disable'])->name('disable');
    });

    // ── TEACHER MANAGEMENT (Module 5) ────────────────────────────────────────
    Route::prefix('departments')->name('departments.')->group(function () {
        Route::get('/',                              [DepartmentController::class, 'index'])->name('index');
        Route::post('/',                             [DepartmentController::class, 'store'])->name('store');
        Route::put('/{id}',                          [DepartmentController::class, 'update'])->name('update');
        Route::delete('/{id}',                       [DepartmentController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/teachers',                [DepartmentController::class, 'assignTeacher'])->name('teacher.assign');
        Route::delete('/{id}/teachers/{teacherId}',  [DepartmentController::class, 'removeTeacher'])->name('teacher.remove');
    });

    Route::prefix('teacher/{teacherId}')->name('teacher.')->group(function () {
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

    Route::get('teacher/attendance',          [TeacherAttendanceController::class, 'index'])->name('teacher.attendance.index');
    Route::post('teacher/attendance',         [TeacherAttendanceController::class, 'store'])->name('teacher.attendance.store');
    Route::get('teacher/{teacherId}/attendance', [TeacherAttendanceController::class, 'show'])->name('teacher.attendance.show');

    Route::prefix('leave')->name('leave.')->group(function () {
        Route::get('/',              [LeaveController::class, 'index'])->name('index');
        Route::get('/types',         [LeaveController::class, 'types'])->name('types');
        Route::post('/types',        [LeaveController::class, 'storeType'])->name('types.store');
        Route::put('/types/{id}',    [LeaveController::class, 'updateType'])->name('types.update');
        Route::delete('/types/{id}', [LeaveController::class, 'destroyType'])->name('types.destroy');
        Route::post('/apply',        [LeaveController::class, 'apply'])->name('apply');
        Route::post('/{id}/review',  [LeaveController::class, 'review'])->name('review');
        Route::post('/{id}/cancel',  [LeaveController::class, 'cancel'])->name('cancel');
    });

    Route::prefix('teacher/payroll')->name('teacher.payroll.')->group(function () {
        Route::get('/',          [TeacherPayrollController::class, 'index'])->name('index');
        Route::post('/',         [TeacherPayrollController::class, 'store'])->name('store');
        Route::post('/{id}/paid',[TeacherPayrollController::class, 'markPaid'])->name('paid');
        Route::get('/{id}/slip', [TeacherPayrollController::class, 'slip'])->name('slip');
        Route::delete('/{id}',   [TeacherPayrollController::class, 'destroy'])->name('destroy');
    });

    Route::get('performance-reviews',      [PerformanceReviewController::class, 'index'])->name('reviews.index');
    Route::put('performance-reviews/{id}', [PerformanceReviewController::class, 'update'])->name('reviews.update');
    Route::delete('performance-reviews/{id}', [PerformanceReviewController::class, 'destroy'])->name('reviews.destroy');
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
    Route::get('students/{studentId}/transfer/create',         [StudentTransferController::class, 'create'])->name('student.transfer.create');
    Route::post('students/{studentId}/transfer',               [StudentTransferController::class, 'store'])->name('student.transfer.store');
    Route::post('transfers/{id}/approve',                      [StudentTransferController::class, 'approve'])->name('student.transfer.approve');
    Route::delete('transfers/{id}',                            [StudentTransferController::class, 'destroy'])->name('student.transfer.destroy');

    // ── STUDENT ID CARDS ─────────────────────────────────────────────────────
    Route::get('students/{studentId}/id-card',   [StudentIdCardController::class, 'generate'])->name('student.id-card');
    Route::post('students/id-cards/bulk',         [StudentIdCardController::class, 'bulkGenerate'])->name('student.id-card.bulk');

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

    // ── CERTIFICATE TEMPLATES ─────────────────────────────────────────────────
    Route::prefix('certificates')->name('certificates.')->group(function () {
        Route::get('/',         [CertificateController::class, 'index'])->name('index');
        Route::get('/create',   [CertificateController::class, 'create'])->name('create');
        Route::post('/',        [CertificateController::class, 'store'])->name('store');
        Route::get('/{id}/edit',[CertificateController::class, 'edit'])->name('edit');
        Route::put('/{id}',     [CertificateController::class, 'update'])->name('update');
        Route::delete('/{id}',  [CertificateController::class, 'destroy'])->name('destroy');
    });

    // ── CERTIFICATE GENERATION ────────────────────────────────────────────────
    Route::get('students/{studentId}/certificate',   [CertificateController::class, 'generate'])->name('student.certificate.generate');
    Route::post('certificates/bulk-generate',        [CertificateController::class, 'bulkGenerate'])->name('certificates.bulk-generate');

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

    // ── ROLE & PERMISSION MANAGEMENT ────────────────────────────────────────
    Route::middleware('permission:manage roles')->prefix('roles')->name('roles.')->group(function () {
        Route::get('/',                         [RoleController::class, 'index'])->name('index');
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
});
