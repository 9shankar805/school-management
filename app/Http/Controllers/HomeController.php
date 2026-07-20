<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\AssignedTeacher;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\Exam;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Promotion;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\User;
use App\Repositories\NoticeRepository;
use App\Repositories\PromotionRepository;
use App\Traits\SchoolSession as SchoolSessionTrait;
use App\Interfaces\UserInterface;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SchoolSessionInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeController extends Controller
{
    use SchoolSessionTrait;

    protected $schoolSessionRepository;
    protected $schoolClassRepository;
    protected $userRepository;

    public function __construct(
        UserInterface         $userRepository,
        SchoolSessionInterface $schoolSessionRepository,
        SchoolClassInterface  $schoolClassRepository
    ) {
        $this->userRepository          = $userRepository;
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository   = $schoolClassRepository;
    }

    /**
     * Dispatch to the correct role-specific dashboard view.
     */
    public function index(): View
    {
        $user = auth()->user();

        return match (true) {
            $user->isSuperAdmin()
                => $this->adminDashboard('dashboards.super-admin'),

            $user->isOrgOwner()
                => $this->adminDashboard('dashboards.organization-owner'),

            $user->isPrincipal()
                => $this->adminDashboard('dashboards.principal'),

            $user->isVicePrincipal()
                => $this->adminDashboard('dashboards.vice-principal'),

            $user->isAdmin()
                => $this->adminDashboard('dashboards.admin'),

            $user->isAcademicCoord()
                => $this->adminDashboard('dashboards.academic-coordinator'),

            $user->isExamController()
                => $this->examControllerDashboard(),

            $user->isAttendanceOfficer()
                => $this->attendanceDashboard(),

            $user->isAdmissionOfficer()
                => $this->admissionDashboard(),

            $user->isTeacher()
                => $this->teacherDashboard(),

            $user->isStudent()
                => $this->studentDashboard(),

            $user->isParent()
                => $this->parentDashboard(),

            $user->isAccountant()
                => $this->accountantDashboard(),

            $user->isLibrarian()
                => $this->librarianDashboard(),

            $user->isHrManager()
                => $this->hrDashboard(),

            $user->isReceptionist()
                => $this->receptionistDashboard(),

            $user->isTransportManager()
                => $this->transportDashboard(),

            $user->isHostelManager()
                => $this->hostelDashboard(),

            default => $this->adminDashboard('dashboards.admin'),
        };
    }

    // -------------------------------------------------------------------------
    // Admin / Principal / Management dashboard
    // -------------------------------------------------------------------------
    private function adminDashboard(string $view = 'dashboards.admin'): View
    {
        $sessionId = $this->getSchoolCurrentSession();
        $today     = now()->toDateString();
        $month     = now()->month;
        $year      = now()->year;

        $studentCount  = $this->userRepository->getAllStudentsBySessionCount($sessionId);
        $teacherCount  = $this->userRepository->getAllTeachers()->count();
        $classCount    = $this->schoolClassRepository->getAllBySession($sessionId)->count();

        $promotionRepo = new PromotionRepository();
        $maleStudents  = $promotionRepo->getMaleStudentsBySessionCount($sessionId);
        $noticeRepo    = new NoticeRepository();
        $notices       = $noticeRepo->getAll($sessionId);

        // Attendance today (status column is 'present' / 'absent')
        $todayPresent  = Attendance::whereDate('created_at', $today)->where('status', 'present')->count();
        $todayAbsent   = Attendance::whereDate('created_at', $today)->where('status', 'absent')->count();
        $todayTotal    = $todayPresent + $todayAbsent;
        $attendancePct = $todayTotal > 0 ? round(($todayPresent / $todayTotal) * 100) : 0;

        // Finance — amount_paid column on payments
        $monthRevenue    = Payment::whereMonth('created_at', $month)->whereYear('created_at', $year)->sum('amount_paid');
        $pendingInvoices = Invoice::where('status', 'unpaid')->count();

        // Attendance trend (last 7 days) via created_at + status string
        $attendanceTrend = Attendance::select(
                DB::raw('DATE(created_at) as day'),
                DB::raw("SUM(status = 'present') as present"),
                DB::raw("SUM(status = 'absent') as absent")
            )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // Monthly revenue chart (12 months)
        $monthlyRevenue = Payment::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(amount_paid) as total')
            )
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Staff count (all non-student non-parent roles)
        $staffCount = User::role([
            'teacher','class-teacher','accountant','librarian','receptionist',
            'hr-manager','transport-manager','hostel-manager','exam-controller',
            'attendance-officer','admission-officer',
        ])->count();

        // Upcoming exams (next 30 days)
        $upcomingExams = Exam::where('session_id', $sessionId)
            ->where('start_date', '>=', $today)
            ->where('start_date', '<=', now()->addDays(30)->toDateString())
            ->with('course')
            ->orderBy('start_date')
            ->take(5)
            ->get();

        // Upcoming events (next 14 days)
        $upcomingEvents = Event::where('session_id', $sessionId)
            ->where('start', '>=', $today)
            ->orderBy('start')
            ->take(5)
            ->get();

        // Recent admissions (students created in last 30 days)
        $recentAdmissions = User::role('student')
            ->where('created_at', '>=', now()->subDays(30))
            ->latest()
            ->take(5)
            ->get();

        // Recent payments with invoice → student
        $recentPayments = Payment::with('invoice.student')
            ->latest()
            ->take(6)
            ->get();

        // Birthday widget — students/staff with birthday today or in next 7 days
        $birthdayUsers = User::whereNotNull('birthday')
            ->whereRaw('DATE_FORMAT(birthday, "%m-%d") BETWEEN ? AND ?', [
                now()->format('m-d'),
                now()->addDays(7)->format('m-d'),
            ])
            ->take(8)
            ->get();

        // Recent activity log
        $activityLog = AuditLog::with('user')
            ->latest()
            ->take(8)
            ->get();

        // Gender distribution
        $female  = max(0, $studentCount - $maleStudents);
        $malePct = $studentCount > 0 ? round(($maleStudents / $studentCount) * 100) : 50;

        // Today's class schedule (weekday: Carbon 0=Monday...6=Sunday for isoWeekday,
        // but stored as PHP date('N') 1=Mon..7=Sun — we store 1-7 matching Carbon::isoWeekday)
        $todayWeekday = now()->isoWeekday(); // 1=Mon .. 7=Sun
        $todaySchedule = \App\Models\Routine::where('session_id', $sessionId)
            ->where('weekday', $todayWeekday)
            ->with('course','section','schoolClass')
            ->orderBy('start')
            ->get();

        // Top performers: top 8 students by average marks this session
        $topPerformers = \App\Models\Mark::where('session_id', $sessionId)
            ->select('student_id', DB::raw('ROUND(AVG(marks), 1) as avg_marks'), DB::raw('COUNT(*) as exams_count'))
            ->groupBy('student_id')
            ->orderByDesc('avg_marks')
            ->take(8)
            ->with('student')
            ->get();

        // Student performance: average marks per course this session (for chart)
        $coursePerformance = \App\Models\Mark::where('session_id', $sessionId)
            ->select('course_id', DB::raw('ROUND(AVG(marks), 1) as avg_marks'))
            ->groupBy('course_id')
            ->orderByDesc('avg_marks')
            ->take(10)
            ->with('course')
            ->get();

        // Teacher performance: marks entered + attendance taken per teacher
        $teacherPerformance = \App\Models\AssignedTeacher::with('teacher')
            ->get()
            ->map(function ($at) use ($sessionId) {
                $marksEntered = \App\Models\Mark::where('session_id', $sessionId)
                    ->where('course_id', $at->course_id)
                    ->count();
                $attendanceTaken = Attendance::where('session_id', $sessionId)
                    ->where('course_id', $at->course_id)
                    ->select(DB::raw('COUNT(DISTINCT DATE(created_at)) as days'))
                    ->value('days') ?? 0;
                return (object) [
                    'teacher'         => $at->teacher,
                    'marks_entered'   => $marksEntered,
                    'attendance_days' => $attendanceTaken,
                ];
            })
            ->filter(fn($t) => $t->teacher !== null)
            ->unique(fn($t) => $t->teacher->id)
            ->take(6)
            ->values();

        // Attendance heatmap: daily attendance % for last 10 weeks (70 days)
        $heatmapData = Attendance::select(
                DB::raw('DATE(created_at) as day'),
                DB::raw("ROUND(SUM(status='present') * 100.0 / COUNT(*), 0) as pct")
            )
            ->where('created_at', '>=', now()->subDays(70))
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $data = compact(
            'studentCount','teacherCount','classCount','staffCount',
            'maleStudents','female','malePct',
            'notices',
            'todayPresent','todayAbsent','attendancePct',
            'monthRevenue','pendingInvoices',
            'attendanceTrend','monthlyRevenue',
            'upcomingExams','upcomingEvents',
            'recentAdmissions','recentPayments',
            'birthdayUsers','activityLog',
            'todaySchedule','topPerformers','coursePerformance',
            'teacherPerformance','heatmapData'
        );

        return view($view, $data);
    }

    // -------------------------------------------------------------------------
    // Teacher dashboard
    // -------------------------------------------------------------------------
    private function teacherDashboard(): View
    {
        $teacher   = auth()->user();
        $today     = now()->toDateString();
        $sessionId = $this->getSchoolCurrentSession();

        $myCourses = \App\Models\AssignedTeacher::where('teacher_id', $teacher->id)
            ->with('course','section')
            ->get();

        $noticeRepo = new NoticeRepository();
        $notices    = $noticeRepo->getAll($sessionId);

        // Attendance trend for this teacher's courses (last 7 days)
        $courseIds = $myCourses->pluck('course_id')->filter();
        $recentAttendance = Attendance::whereIn('course_id', $courseIds)
            ->where('created_at', '>=', now()->subDays(7))
            ->select(
                DB::raw('DATE(created_at) as day'),
                DB::raw("SUM(status='present') as present"),
                DB::raw("SUM(status='absent') as absent")
            )
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // Today's attendance summary per course
        $todaySummary = Attendance::whereIn('course_id', $courseIds)
            ->whereDate('created_at', $today)
            ->select('course_id',
                DB::raw("SUM(status='present') as present"),
                DB::raw("SUM(status='absent') as absent")
            )
            ->groupBy('course_id')
            ->get()
            ->keyBy('course_id');

        // Upcoming exams for this teacher's courses
        $upcomingExams = Exam::whereIn('course_id', $courseIds)
            ->where('start_date', '>=', $today)
            ->with('course')
            ->orderBy('start_date')
            ->take(5)
            ->get();

        return view('dashboards.teacher', compact(
            'teacher','myCourses','notices',
            'recentAttendance','todaySummary','upcomingExams'
        ));
    }

    // -------------------------------------------------------------------------
    // Student dashboard
    // -------------------------------------------------------------------------
    private function studentDashboard(): View
    {
        $student   = auth()->user();
        $sessionId = $this->getSchoolCurrentSession();

        $noticeRepo = new NoticeRepository();
        $notices    = $noticeRepo->getAll($sessionId);

        $marks = $student->marks()->with('exam')->latest()->take(10)->get();

        $totalAttendance = Attendance::where('student_id', $student->id)->count();
        $presentCount    = Attendance::where('student_id', $student->id)->where('status', 'present')->count();
        $attendancePct   = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100) : 0;

        $invoices = Invoice::where('student_id', $student->id)->latest()->take(5)->get();

        $promotionRepo = new PromotionRepository();
        $classInfo     = $promotionRepo->getStudentCurrentClass($student->id, $sessionId);

        // Upcoming exams for this student's class
        $classId = $classInfo?->class_id;
        $upcomingExams = $classId
            ? Exam::where('class_id', $classId)
                ->where('start_date', '>=', now()->toDateString())
                ->orderBy('start_date')
                ->take(5)
                ->get()
            : collect();

        // Monthly attendance trend (last 6 months)
        $attendanceTrend = Attendance::where('student_id', $student->id)
            ->where('created_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw("SUM(status='present') as present"),
                DB::raw("SUM(status='absent') as absent")
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('dashboards.student', compact(
            'student','marks','attendancePct','presentCount','totalAttendance',
            'invoices','notices','classInfo','upcomingExams','attendanceTrend'
        ));
    }

    // -------------------------------------------------------------------------
    // Parent dashboard
    // -------------------------------------------------------------------------
    private function parentDashboard(): View
    {
        $parent    = auth()->user();
        $sessionId = $this->getSchoolCurrentSession();

        $noticeRepo = new NoticeRepository();
        $notices    = $noticeRepo->getAll($sessionId);

        // Children linked via parent_student pivot table
        $children = $parent->children()->with('academic_info')->get();

        // Per-child attendance + invoice summary
        $childData = $children->map(function ($child) use ($sessionId) {
            $total    = Attendance::where('student_id', $child->id)->count();
            $present  = Attendance::where('student_id', $child->id)->where('status', 'present')->count();
            $pct      = $total > 0 ? round(($present / $total) * 100) : 0;
            $invoices = Invoice::where('student_id', $child->id)->where('status', 'unpaid')->count();
            $marks    = $child->marks()->with('exam')->latest()->take(5)->get();

            return (object) compact('child','total','present','pct','invoices','marks');
        });

        return view('dashboards.parent', compact('parent','children','childData','notices'));
    }

    // -------------------------------------------------------------------------
    // Accountant dashboard
    // -------------------------------------------------------------------------
    private function accountantDashboard(): View
    {
        $month = now()->month;
        $year  = now()->year;

        $totalInvoices  = Invoice::count();
        $unpaidInvoices = Invoice::where('status', 'unpaid')->count();
        $paidInvoices   = Invoice::where('status', 'paid')->count();
        $monthRevenue   = Payment::whereMonth('created_at', $month)->whereYear('created_at', $year)->sum('amount_paid');
        $yearRevenue    = Payment::whereYear('created_at', $year)->sum('amount_paid');

        $recentPayments = Payment::with('invoice.student')->latest()->take(10)->get();

        $monthlyRevenue = Payment::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(amount_paid) as total')
            )
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Overdue invoices (unpaid + past due_date)
        $overdueInvoices = Invoice::where('status', 'unpaid')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->with('student')
            ->latest('due_date')
            ->take(8)
            ->get();

        return view('dashboards.accountant', compact(
            'totalInvoices','unpaidInvoices','paidInvoices',
            'monthRevenue','yearRevenue',
            'recentPayments','monthlyRevenue','overdueInvoices'
        ));
    }

    // -------------------------------------------------------------------------
    // Librarian dashboard
    // -------------------------------------------------------------------------
    private function librarianDashboard(): View
    {
        $totalBooks   = \App\Models\Book::count();
        $issuedBooks  = 0;   // TODO: from book_issues table (Module 12)
        $overdueBooks = 0;   // TODO: from book_issues table (Module 12)

        $recentBooks = \App\Models\Book::latest()->take(10)->get();

        return view('dashboards.librarian', compact('totalBooks','issuedBooks','overdueBooks','recentBooks'));
    }

    // -------------------------------------------------------------------------
    // HR Manager dashboard
    // -------------------------------------------------------------------------
    private function hrDashboard(): View
    {
        $staffRoles   = ['teacher','class-teacher','receptionist','hr-manager',
            'transport-manager','hostel-manager','exam-controller','attendance-officer','admission-officer'];
        $staffCount   = User::role($staffRoles)->count();
        $teacherCount = User::role(['teacher','class-teacher'])->count();

        // Staff breakdown by role for chart
        $roleBreakdown = collect($staffRoles)->map(function ($role) {
            return ['role' => $role, 'count' => User::role($role)->count()];
        })->filter(fn($r) => $r['count'] > 0)->values();

        // Recent staff additions
        $recentStaff = User::role($staffRoles)->latest()->take(8)->get();

        // Birthdays this month
        $birthdayStaff = User::role($staffRoles)
            ->whereNotNull('birthday')
            ->whereRaw('MONTH(birthday) = ?', [now()->month])
            ->get();

        return view('dashboards.hr-manager', compact(
            'staffCount','teacherCount','roleBreakdown','recentStaff','birthdayStaff'
        ));
    }

    // -------------------------------------------------------------------------
    // Exam Controller dashboard
    // -------------------------------------------------------------------------
    private function examControllerDashboard(): View
    {
        $sessionId = $this->getSchoolCurrentSession();
        $today     = now()->toDateString();

        $totalExams    = Exam::where('session_id', $sessionId)->count();
        $upcomingExams = Exam::where('session_id', $sessionId)
            ->where('start_date', '>=', $today)
            ->with('course')
            ->orderBy('start_date')
            ->take(10)
            ->get();
        $pastExams = Exam::where('session_id', $sessionId)
            ->where('end_date', '<', $today)
            ->with('course')
            ->orderByDesc('end_date')
            ->take(5)
            ->get();

        $studentCount = $this->userRepository->getAllStudentsBySessionCount($sessionId);

        return view('dashboards.exam-controller', compact(
            'totalExams','upcomingExams','pastExams','studentCount','sessionId'
        ));
    }

    // -------------------------------------------------------------------------
    // Attendance Officer dashboard
    // -------------------------------------------------------------------------
    private function attendanceDashboard(): View
    {
        $sessionId = $this->getSchoolCurrentSession();
        $today     = now()->toDateString();

        $todayPresent  = Attendance::whereDate('created_at', $today)->where('status', 'present')->count();
        $todayAbsent   = Attendance::whereDate('created_at', $today)->where('status', 'absent')->count();
        $todayTotal    = $todayPresent + $todayAbsent;
        $attendancePct = $todayTotal > 0 ? round(($todayPresent / $todayTotal) * 100) : 0;

        $weekTrend = Attendance::select(
                DB::raw('DATE(created_at) as day'),
                DB::raw("SUM(status='present') as present"),
                DB::raw("SUM(status='absent') as absent")
            )
            ->where('created_at', '>=', now()->subDays(14))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // Students with low attendance (below 75% in last 30 days)
        $studentCount = $this->userRepository->getAllStudentsBySessionCount($sessionId);

        return view('dashboards.attendance-officer', compact(
            'todayPresent','todayAbsent','attendancePct','weekTrend','studentCount'
        ));
    }

    // -------------------------------------------------------------------------
    // Admission Officer dashboard
    // -------------------------------------------------------------------------
    private function admissionDashboard(): View
    {
        $sessionId = $this->getSchoolCurrentSession();

        $totalStudents   = $this->userRepository->getAllStudentsBySessionCount($sessionId);
        $thisMonthAdmissions = User::role('student')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $recentAdmissions = User::role('student')
            ->latest()
            ->take(10)
            ->get();

        $classCount = $this->schoolClassRepository->getAllBySession($sessionId)->count();

        return view('dashboards.admission-officer', compact(
            'totalStudents','thisMonthAdmissions','recentAdmissions','classCount'
        ));
    }

    // -------------------------------------------------------------------------
    // Receptionist dashboard
    // -------------------------------------------------------------------------
    private function receptionistDashboard(): View
    {
        $sessionId = $this->getSchoolCurrentSession();

        $noticeRepo = new NoticeRepository();
        $notices    = $noticeRepo->getAll($sessionId);

        $upcomingEvents = Event::where('session_id', $sessionId)
            ->where('start', '>=', now()->toDateString())
            ->orderBy('start')
            ->take(8)
            ->get();

        $studentCount = $this->userRepository->getAllStudentsBySessionCount($sessionId);
        $teacherCount = $this->userRepository->getAllTeachers()->count();

        return view('dashboards.receptionist', compact(
            'notices','upcomingEvents','studentCount','teacherCount'
        ));
    }

    // -------------------------------------------------------------------------
    // Transport Manager dashboard
    // -------------------------------------------------------------------------
    private function transportDashboard(): View
    {
        // Transport module (Module 13) not yet implemented
        $studentCount = User::role('student')->count();

        return view('dashboards.transport-manager', compact('studentCount'));
    }

    // -------------------------------------------------------------------------
    // Hostel Manager dashboard
    // -------------------------------------------------------------------------
    private function hostelDashboard(): View
    {
        // Hostel module (Module 14) not yet implemented
        $studentCount = User::role('student')->count();

        return view('dashboards.hostel-manager', compact('studentCount'));
    }
}
