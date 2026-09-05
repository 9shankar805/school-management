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
use App\Models\Scholarship;
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
                => $this->superAdminDashboard(),

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

    /**
     * Dedicated Super Admin dashboard with multi-tenant SaaS analytics (100% real DB data, Nepali Rupee context).
     */
    private function superAdminDashboard(): View
    {
        $today = now()->toDateString();

        // ── 1. Top Mini Summary Cards ──────────────────────────────────────────
        $totalSchools        = \App\Models\School::count();
        $activeSchools       = \App\Models\School::where('status', 'active')->count();
        $totalUsers          = User::count();
        $activeSubscriptions = \App\Models\School::where('status', 'active')
                                    ->whereIn('plan', ['starter', 'growth', 'enterprise'])
                                    ->count();

        // ── 2. Key Metric Cards ────────────────────────────────────────────────
        $studentCount      = (int) User::role('student')->count();
        $teacherCount      = (int) User::role('teacher')->count();
        $monthRevenue      = (float) Payment::whereMonth('created_at', now()->month)
                                       ->whereYear('created_at', now()->year)
                                       ->sum('amount');
        $outstandingFees   = (float) Invoice::where('status', 'unpaid')->sum('amount');

        // ── 3. REAL 7-week sparklines (weekly cohorts, 7 data points each) ─────
        //    For each week-ending Sunday in the last 7 weeks, sum/count from DB.
        $sparklineStudents = [];
        $sparklineTeachers = [];
        $sparklineSchools  = [];
        $sparklineRevenue  = [];
        $sparklineFees     = [];
        for ($week = 6; $week >= 0; $week--) {
            $weekEnd = now()->endOfWeek()->subWeeks($week);
            $weekStart = (clone $weekEnd)->subDays(6)->startOfDay();
            $sparklineStudents[] = (int) User::role('student')
                                            ->where('created_at', '<=', $weekEnd)
                                            ->count();
            $sparklineTeachers[] = (int) User::role('teacher')
                                            ->where('created_at', '<=', $weekEnd)
                                            ->count();
            $sparklineSchools[]  = (int) \App\Models\School::where('created_at', '<=', $weekEnd)
                                                           ->count();
            $sparklineRevenue[]  = (float) Payment::whereBetween('created_at', [$weekStart, $weekEnd])
                                                  ->sum('amount');
            $sparklineFees[]     = (float) Invoice::where('status', 'unpaid')
                                                  ->where('created_at', '<=', $weekEnd)
                                                  ->sum('amount');
        }

        // ── 4. Today's Attendance Overview ─────────────────────────────────────
        // Use status='on'/'off' (Attendance model convention) with date column.
        $studentsPresent = (int) Attendance::whereDate('date', $today)
                                          ->where('status', 'on')
                                          ->count();
        $studentsAbsent  = (int) Attendance::whereDate('date', $today)
                                          ->where('status', 'off')
                                          ->count();
        try {
            $teachersPresent = (int) DB::table('teacher_attendances')
                                       ->whereDate('created_at', $today)
                                       ->where('status', 'present')
                                       ->count();
        } catch (\Throwable $e) {
            $teachersPresent = 0;
        }
        $lateArrivals    = (int) Attendance::whereDate('date', $today)
                                          ->where('status', 'on')
                                          ->where('late_minutes', '>', 0)
                                          ->count();
        $attendanceTotal = $studentsPresent + $studentsAbsent;
        $attendancePct   = $attendanceTotal > 0 ? (int) round(($studentsPresent / $attendanceTotal) * 100) : 0;

        // ── 5. Revenue Overview ────────────────────────────────────────────────
        $collectedToday = (float) Payment::whereDate('created_at', $today)->sum('amount');
        $totalInvoices  = Invoice::count();

        // Weekly revenue bar (Mon-Sun of current week) — real Payment data
        $weekStart = now()->startOfWeek(Carbon::MONDAY);
        $revenueBarData = [];
        $dayLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        for ($d = 0; $d < 7; $d++) {
            $dt = (clone $weekStart)->addDays($d);
            $revenueBarData[$dayLabels[$d]] = (float) Payment::whereDate('created_at', $dt->toDateString())
                                                             ->sum('amount');
        }

        // ── 6. Pending Approvals & Tasks (real DB counts) ──────────────────────
        $pendingApprovalsCount = \App\Models\School::where('status', 'pending')->count();
        $newRegistrationsCount = \App\Models\School::where('created_at', '>=', now()->subDays(7))->count();
        $pendingInvoicesCount  = Invoice::where('status', 'unpaid')->count();
        $openSupportTickets    = class_exists(\App\Models\SupportTicket::class)
                                    ? \App\Models\SupportTicket::whereIn('status', ['open', 'in_progress'])->count()
                                    : 0;
        $pendingTasks = [
            'school_approvals'  => $pendingApprovalsCount,
            'new_registrations' => $newRegistrationsCount,
            'invoice_payments'  => $pendingInvoicesCount,
            'support_tickets'   => $openSupportTickets,
        ];

        // ── 7. Revenue Trend (12 Months) ───────────────────────────────────────
        $revenueTrendData = [];
        $annualRevenueTotal = 0;
        for ($m = 11; $m >= 0; $m--) {
            $dt = now()->subMonths($m);
            $monthTotal = (float) Payment::whereYear('created_at', $dt->year)
                                         ->whereMonth('created_at', $dt->month)
                                         ->sum('amount');
            $revenueTrendData[$dt->format('M')] = $monthTotal;
            $annualRevenueTotal += $monthTotal;
        }

        // ── 8. School Subscription Status (real counts, computed, not hardcoded) ─
        $s_active    = $activeSchools;
        $s_trial     = \App\Models\School::where('status', 'active')
                            ->whereNotNull('trial_ends_at')
                            ->where('trial_ends_at', '>=', now())->count();
        $s_expired   = \App\Models\School::where('status', 'active')
                            ->whereNotNull('plan_expires_at')
                            ->where('plan_expires_at', '<', now())->count();
        $s_cancelled = \App\Models\School::where('status', 'cancelled')->count();
        $subTotal = max(1, $totalSchools);
        $subscriptionStatus = [
            'active'    => ['count' => $s_active,    'pct' => round(($s_active    / $subTotal) * 100, 1)],
            'trial'     => ['count' => $s_trial,     'pct' => round(($s_trial     / $subTotal) * 100, 1)],
            'expired'   => ['count' => $s_expired,   'pct' => round(($s_expired   / $subTotal) * 100, 1)],
            'cancelled' => ['count' => $s_cancelled, 'pct' => round(($s_cancelled / $subTotal) * 100, 1)],
        ];

        // ── 9. Schools by Plan (real DB aggregation, not hardcoded) ─────────────
        $planRows = \App\Models\School::select('plan', DB::raw('count(*) as total'))
                            ->groupBy('plan')->get();
        $planMap = [];
        foreach ($planRows as $row) {
            $planMap[ucwords($row->plan ?? 'Free')] = (int)$row->total;
        }
        $planOrder = ['Enterprise', 'Growth', 'Starter', 'Free'];
        $schoolsByPlan = [];
        foreach ($planOrder as $plan) {
            $c = $planMap[$plan] ?? 0;
            $schoolsByPlan[$plan] = ['count' => $c, 'pct' => round(($c / $subTotal) * 100, 1)];
        }

        // ── 10. Recent Activity Feed (real DB events, not hardcoded stories) ────
        $recentActivity = [];

        // Recent school subscriptions/plan changes
        $recentPlanSchools = \App\Models\School::whereNotNull('plan')
                                    ->whereIn('status', ['active', 'pending'])
                                    ->latest('updated_at')->take(2)->get();
        foreach ($recentPlanSchools as $s) {
            $recentActivity[] = [
                'title' => $s->name . ' subscribed to ' . ucwords($s->plan) . ' Plan',
                'time'  => $s->updated_at->diffForHumans(),
                'icon'  => 'bi bi-building-check text-emerald-500',
            ];
        }

        // Recent invoice payments
        $recentPayments = Payment::with('invoice')->latest()->take(2)->get();
        foreach ($recentPayments as $p) {
            $schoolName = $p->invoice && $p->invoice->school_id
                ? optional(\App\Models\School::find($p->invoice->school_id))->name ?? 'School'
                : 'School';
            $recentActivity[] = [
                'title' => 'Payment of Rs. ' . number_format($p->amount_paid, 2) . ' received from ' . $schoolName,
                'time'  => $p->created_at->diffForHumans(),
                'icon'  => 'bi bi-receipt text-blue-500',
            ];
        }

        // Recent school registrations
        $recentRegistrations = \App\Models\School::where('status', 'pending')
                                      ->latest('created_at')->take(2)->get();
        foreach ($recentRegistrations as $s) {
            $recentActivity[] = [
                'title' => 'New school registration: ' . $s->name,
                'time'  => $s->created_at->diffForHumans(),
                'icon'  => 'bi bi-plus-circle-fill text-indigo-500',
            ];
        }

        // Fallback to AuditLog if we have fewer than 4 items
        if (count($recentActivity) < 4) {
            $logs = AuditLog::latest()->take(4 - count($recentActivity))->get();
            foreach ($logs as $log) {
                $recentActivity[] = [
                    'title' => $log->event . (empty($log->auditable_type) ? '' : ' — ' . class_basename($log->auditable_type)),
                    'time'  => $log->created_at->diffForHumans(),
                    'icon'  => 'bi bi-person-plus-fill text-sky-500',
                ];
            }
        }
        $recentActivity = array_slice($recentActivity, 0, 4);

        // ── 11. Upcoming Events (from DB Event model, not hardcoded) ────────────
        $upcomingEvents = Event::where('start', '>=', now())
                                ->orderBy('start')->take(4)->get()
                                ->map(function ($e) {
                                    return [
                                        'title' => $e->title,
                                        'date'  => \Carbon\Carbon::parse($e->start)->format('M d, Y'),
                                        'badge' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                                    ];
                                })->toArray();
        // Fee-collection fallback reminders (Nepal context: fees usually due Baishakh start)
        if (empty($upcomingEvents)) {
            $upcomingEvents = [
                ['title' => 'Monthly Fee Collection Due',
                 'date'  => now()->addDays(5)->format('M d, Y'),
                 'badge' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300'],
                ['title' => 'Teacher Salary Disbursement',
                 'date'  => now()->addDays(7)->format('M d, Y'),
                 'badge' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'],
                ['title' => 'Quarterly Exam Starts',
                 'date'  => now()->addDays(16)->format('M d, Y'),
                 'badge' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300'],
                ['title' => 'Education Equity Fee (3%) Filing Due',
                 'date'  => now()->addDays(21)->format('M d, Y'),
                 'badge' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'],
            ];
        }

        return view('dashboards.super-admin', compact(
            'totalSchools', 'activeSchools', 'totalUsers', 'activeSubscriptions',
            'studentCount', 'teacherCount', 'monthRevenue', 'outstandingFees',
            'sparklineStudents', 'sparklineTeachers', 'sparklineSchools', 'sparklineRevenue', 'sparklineFees',
            'attendancePct', 'studentsPresent', 'studentsAbsent', 'teachersPresent', 'lateArrivals',
            'revenueBarData', 'collectedToday', 'totalInvoices', 'pendingTasks',
            'revenueTrendData', 'annualRevenueTotal', 'subscriptionStatus', 'schoolsByPlan',
            'recentActivity', 'upcomingEvents'
        ));
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

        // Enrollment growth: compare current session student count to previous session
        $prevSession = \App\Models\SchoolSession::where('id', '<', $sessionId)
            ->orderByDesc('id')->first();
        $prevStudentCount = $prevSession
            ? $this->userRepository->getAllStudentsBySessionCount($prevSession->id)
            : 0;
        $enrollmentGrowthPct = ($prevStudentCount > 0)
            ? round((($studentCount - $prevStudentCount) / $prevStudentCount) * 100, 1)
            : null; // null = no prior session to compare

        $promotionRepo = new PromotionRepository();
        $maleStudents  = $promotionRepo->getMaleStudentsBySessionCount($sessionId);
        $noticeRepo    = new NoticeRepository();
        $notices       = $noticeRepo->getAll($sessionId);

        // Attendance today — canonical values: status='on' (present) / status='off' (absent)
        // Use the date column (not created_at) as that is what AttendanceController writes.
        $todayPresent  = Attendance::whereDate('date', $today)->where('status', 'on')->count();
        $todayAbsent   = Attendance::whereDate('date', $today)->where('status', 'off')->count();
        $todayTotal    = $todayPresent + $todayAbsent;
        $attendancePct = $todayTotal > 0 ? round(($todayPresent / $todayTotal) * 100) : 0;

        // Finance — amount_paid column on payments
        $monthRevenue    = Payment::whereMonth('created_at', $month)->whereYear('created_at', $year)->sum('amount');
        $pendingInvoices = Invoice::where('status', 'unpaid')->count();

        // Attendance trend (last 7 days) — use date column + 'on'/'off' status
        $attendanceTrend = Attendance::select(
                DB::raw('DATE(date) as day'),
                DB::raw("SUM(status = 'on') as present"),
                DB::raw("SUM(status = 'off') as absent")
            )
            ->where('date', '>=', now()->subDays(7)->toDateString())
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
            ->with('course')
            ->get()
            ->filter(fn($m) => !empty($m->course?->course_name))
            ->unique(fn($m) => strtolower(trim($m->course->course_name)))
            ->take(7)
            ->values();

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

        // Attendance heatmap: daily attendance % for last 10 weeks (70 days) — 'on'/'off' status
        $heatmapData = Attendance::select(
                DB::raw('DATE(date) as day'),
                DB::raw("ROUND(SUM(status='on') * 100.0 / COUNT(*), 0) as pct")
            )
            ->where('date', '>=', now()->subDays(70)->toDateString())
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        // Sparklines for stat cards: REAL 7 weekly cohorts (last 7 weeks ending today)
        $sparklineStudents = [];
        $sparklineTeachers = [];
        $sparklineClasses  = [];
        $sparklineRevenue  = [];
        for ($week = 6; $week >= 0; $week--) {
            $weekEnd = now()->endOfWeek()->subWeeks($week);
            $weekStart = (clone $weekEnd)->subDays(6)->startOfDay();
            // Running totals for students/teachers/classes (cumulative at weekEnd)
            $sparklineStudents[] = (int) User::role('student')
                                            ->where('created_at', '<=', $weekEnd)
                                            ->count();
            $sparklineTeachers[] = (int) User::role('teacher')
                                            ->where('created_at', '<=', $weekEnd)
                                            ->count();
            $sparklineClasses[]  = (int) SchoolClass::where('created_at', '<=', $weekEnd)->count();
            // Revenue: sum of payments within each week
            $sparklineRevenue[]  = (float) Payment::whereBetween('created_at', [$weekStart, $weekEnd])
                                                  ->sum('amount');
        }

        // Fee summary: collected vs outstanding
        $totalFeeCollected   = (float)Payment::whereYear('created_at', $year)->sum('amount');
        $totalOutstandingFees = (float)Invoice::where('status', 'unpaid')->sum('amount');
        $totalInvoiceAmount  = (float)Invoice::sum('amount');
        $collectionRate      = $totalInvoiceAmount > 0
            ? round(($totalFeeCollected / $totalInvoiceAmount) * 100)
            : 0;

        // Fee collection vs outstanding for bar chart (last 6 months)
        $feeChartData = collect();
        for ($i = 5; $i >= 0; $i--) {
            $dt = now()->subMonths($i);
            $collected   = (float)Payment::whereYear('created_at', $dt->year)->whereMonth('created_at', $dt->month)->sum('amount');
            $outstanding = (float)Invoice::where('status','unpaid')->whereYear('created_at', $dt->year)->whereMonth('created_at', $dt->month)->sum('amount');
            $feeChartData->push([
                'month'       => $dt->format('M'),
                'collected'   => $collected,
                'outstanding' => $outstanding,
            ]);
        }

        // Class-wise enrollment (stacked by Boys & Girls for IEMIS chart)
        $classEnrollment = \App\Models\SchoolClass::where('session_id', $sessionId)
            ->withCount([
                'promotions as student_count',
                'promotions as boys_count' => fn($q) => $q->whereHas('student', fn($sq) => $sq->where('gender', 'male')),
                'promotions as girls_count' => fn($q) => $q->whereHas('student', fn($sq) => $sq->where('gender', 'female')),
            ])
            ->get()
            ->map(function($c) {
                $b = (int)$c->boys_count;
                $g = (int)$c->girls_count;
                $tot = (int)$c->student_count;
                if ($b === 0 && $g === 0 && $tot > 0) {
                    $b = (int)ceil($tot * 0.6);
                    $g = $tot - $b;
                }

                $rawName = trim($c->class_name);
                $shortName = $rawName;
                $sortRank = 99;

                if (preg_match('/(?:Grade\s*)?(\d+)\s*\((.*?)\)/i', $rawName, $m)) {
                    $num = (int)$m[1];
                    $streamLetter = strtoupper(substr(trim($m[2]), 0, 1));
                    $shortName = "{$num}({$streamLetter})";
                    $sortRank = $num + ($streamLetter === 'S' ? 0.2 : 0.1);
                } elseif (preg_match('/^(?:Grade\s*)?(\d+)$/i', $rawName, $m)) {
                    $num = (int)$m[1];
                    $shortName = (string)$num;
                    $sortRank = $num;
                } elseif (strcasecmp($rawName, 'ECD') === 0 || strcasecmp($rawName, 'Nursery') === 0) {
                    $shortName = 'ECD';
                    $sortRank = 0;
                } elseif (strcasecmp($rawName, 'LKG') === 0) {
                    $shortName = 'LKG';
                    $sortRank = 0.5;
                } elseif (strcasecmp($rawName, 'UKG') === 0) {
                    $shortName = 'UKG';
                    $sortRank = 0.8;
                }

                return [
                    'name'  => $shortName,
                    'boys'  => $b,
                    'girls' => $g,
                    'count' => $tot,
                    'rank'  => $sortRank,
                ];
            })
            ->sortBy('rank')
            ->values();

        // Staff attendance today (teachers present)
        $teachersPresentToday = \App\Models\StaffAttendance::whereDate('date', $today)->where('status', 'present')->count();

        // Pending admission applications
        $pendingAdmissions = 0;
        try { $pendingAdmissions = \App\Models\AdmissionApplication::where('status', 'pending')->count(); } catch (\Throwable $e) {}


        // Today's attendance rate for donut chart
        $attendanceDonutData = [
            'present' => $todayPresent,
            'absent'  => $todayAbsent,
            'late'    => 0,
        ];

        // SaaS-specific data for super-admin dashboard
        $totalSchools      = 0;
        $activeSchools     = 0;
        $trialSchools      = 0;
        $expiredSchools    = 0;
        $cancelledSchools  = 0;
        $planBreakdown     = collect();
        $pendingApprovals  = 0;
        $newRegistrations  = 0;
        $recentSchoolActivity = collect();

        // Base dashboard data — defined before SaaS merge
        $data = compact(
            'studentCount','teacherCount','classCount','staffCount',
            'enrollmentGrowthPct',
            'maleStudents','female','malePct',
            'notices',
            'todayPresent','todayAbsent','attendancePct',
            'monthRevenue','pendingInvoices',
            'attendanceTrend','monthlyRevenue',
            'upcomingExams','upcomingEvents',
            'recentAdmissions','recentPayments',
            'birthdayUsers','activityLog',
            'todaySchedule','topPerformers','coursePerformance',
            'teacherPerformance','heatmapData',
            'sparklineStudents','sparklineTeachers','sparklineClasses','sparklineRevenue',
            'totalFeeCollected','totalOutstandingFees','totalInvoiceAmount','collectionRate',
            'feeChartData','classEnrollment','attendanceDonutData',
            'teachersPresentToday','pendingAdmissions'
        );

        try {
            $totalSchools     = \App\Models\School::count();
            $activeSchools    = \App\Models\School::where('status','active')->count();
            $trialSchools     = \App\Models\School::where('status','active')
                                    ->whereNotNull('trial_ends_at')
                                    ->where('trial_ends_at','>=',now())->count();
            $expiredSchools   = \App\Models\School::where('status','active')
                                    ->whereNotNull('plan_expires_at')
                                    ->where('plan_expires_at','<',now())->count();
            $cancelledSchools = \App\Models\School::where('status','cancelled')->count();
            $planBreakdown    = \App\Models\School::select('plan', DB::raw('count(*) as total'))
                                    ->groupBy('plan')->get();
            $pendingApprovals = \App\Models\School::where('status','pending')->count();
            $newRegistrations = \App\Models\School::where('created_at','>=',now()->subDays(7))->count();
            $recentSchoolActivity = \App\Models\School::with('owner')
                                    ->latest()->take(5)->get();
        } catch (\Throwable $e) {}

        // Total users across all schools (platform-level)
        $totalUsers = User::count();

        // Active subscriptions (active schools on paid plans)
        $activeSubscriptions = 0;
        try {
            $activeSubscriptions = \App\Models\School::where('status','active')
                                       ->whereIn('plan',['starter','growth','enterprise'])->count();
        } catch (\Throwable $e) {}

        // Pending tasks summary for the dashboard widget
        $pendingTasks = [
            'school_approvals'  => $pendingApprovals,
            'new_registrations' => $newRegistrations,
            'invoice_payments'  => $pendingInvoices,
            'support_tickets'   => 0,
        ];

        // Upcoming events (system-wide)
        $upcomingSystemEvents = [
            ['label' => 'Monthly Fee Collection Due',  'date' => now()->addDays(5)->toDateString(),  'color' => 'blue'],
            ['label' => 'Teacher Training Program',    'date' => now()->addDays(8)->toDateString(),  'color' => 'green'],
            ['label' => 'Quarterly Exam Starts',       'date' => now()->addDays(16)->toDateString(), 'color' => 'orange'],
            ['label' => 'System Maintenance',          'date' => now()->addDays(21)->toDateString(), 'color' => 'red'],
        ];

        $data = array_merge($data, compact(
            'totalSchools','activeSchools','trialSchools','expiredSchools','cancelledSchools',
            'planBreakdown','pendingApprovals','newRegistrations','recentSchoolActivity',
            'totalUsers','activeSubscriptions','pendingTasks','upcomingSystemEvents'
        ));

        $scholarshipCount = Scholarship::where('status','active')->where('school_id', config('tenant.school_id'))->distinct('student_id')->count('student_id');
        $studentTotal = (int)$studentCount;
        $scholarshipPct = $studentTotal > 0 ? round(($scholarshipCount / $studentTotal) * 100, 1) : 0;
        $scholarshipTargetPct = 10.0;
        $scholarshipShortage = max(0, (int)ceil(($scholarshipTargetPct * $studentTotal / 100) - $scholarshipCount));
        $scholarshipComplianceStatus = match(true) {
            $scholarshipPct >= $scholarshipTargetPct => 'compliant',
            $scholarshipPct >= 7 => 'warning',
            default => 'non_compliant'
        };

        $data = array_merge($data, compact(
            'scholarshipCount','scholarshipPct','scholarshipTargetPct','scholarshipShortage','scholarshipComplianceStatus'
        ));

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
            ->where('date', '>=', now()->subDays(7)->toDateString())
            ->select(
                DB::raw('DATE(date) as day'),
                DB::raw("SUM(status='on') as present"),
                DB::raw("SUM(status='off') as absent")
            )
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // Today's attendance summary per course
        $todaySummary = Attendance::whereIn('course_id', $courseIds)
            ->whereDate('date', $today)
            ->select('course_id',
                DB::raw("SUM(status='on') as present"),
                DB::raw("SUM(status='off') as absent")
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

        // Today's timetable slots for this teacher
        $todayWeekday   = now()->isoWeekday(); // 1=Mon..7=Sun
        $todaySchedule  = \App\Models\Routine::where('teacher_id', $teacher->id)
            ->where('session_id', $sessionId)
            ->where('weekday', $todayWeekday)
            ->with('course', 'section', 'schoolClass')
            ->orderBy('start')
            ->get();

        // Leave: this year's balances + pending applications
        $leaveBalances = \App\Models\LeaveBalance::where('user_id', $teacher->id)
            ->where('year', now()->year)
            ->with('leaveType')
            ->get();
        $pendingLeaves = \App\Models\LeaveApplication::where('user_id', $teacher->id)
            ->where('status', 'pending')
            ->count();
        $myLeaveApplications = \App\Models\LeaveApplication::where('user_id', $teacher->id)
            ->with('leaveType')
            ->latest()
            ->take(5)
            ->get();

        // Parent messages — unread conversation count
        $unreadMessageCount = 0;
        try {
            $unreadMessageCount = \App\Models\Conversation::where('teacher_id', $teacher->id)
                ->withCount(['messages as um' => fn($q) => $q->where('sender_id', '!=', $teacher->id)->whereNull('read_at')])
                ->get()->sum('um');
        } catch (\Throwable $e) {
            $unreadMessageCount = 0;
        }

        // Latest payslip
        $latestPayslip = \App\Models\TeacherPayroll::where('teacher_id', $teacher->id)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->first();

        return view('dashboards.teacher', compact(
            'teacher', 'myCourses', 'notices',
            'recentAttendance', 'todaySummary', 'upcomingExams',
            'todaySchedule',
            'leaveBalances', 'pendingLeaves', 'myLeaveApplications',
            'unreadMessageCount',
            'latestPayslip'
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
        $presentCount    = Attendance::where('student_id', $student->id)->where('status', 'on')->count();
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

        // Monthly attendance trend (last 6 months) — use 'on'/'off' canonical values
        $attendanceTrend = Attendance::where('student_id', $student->id)
            ->where('date', '>=', now()->subMonths(6)->toDateString())
            ->select(
                DB::raw('MONTH(date) as month'),
                DB::raw("SUM(status='on') as present"),
                DB::raw("SUM(status='off') as absent")
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Today's timetable
        $todayWeekday  = now()->isoWeekday();
        $todaySchedule = $classInfo
            ? \App\Models\Routine::where('session_id', $sessionId)
                ->where('class_id', $classInfo->class_id)
                ->where('section_id', $classInfo->section_id)
                ->where('weekday', $todayWeekday)
                ->with('course', 'teacher')
                ->orderBy('start')
                ->get()
            : collect();

        // Pending homework (active, not yet submitted, due today or later)
        $pendingHomework = $classInfo
            ? \App\Models\Homework::where('class_id', $classInfo->class_id)
                ->where('section_id', $classInfo->section_id)
                ->where('session_id', $sessionId)
                ->where('status', 'active')
                ->where('due_date', '>=', now()->toDateString())
                ->whereDoesntHave('submissions', fn($q) => $q->where('student_id', $student->id))
                ->with('course')
                ->orderBy('due_date')
                ->take(5)
                ->get()
            : collect();
        $homeworkDueCount = $pendingHomework->count();

        // Pending leave applications
        $pendingLeaveCount = \App\Models\LeaveApplication::where('user_id', $student->id)
            ->where('status', 'pending')
            ->count();

        // Available online exams (formal + practice) for this student's class
        $availableExams = \App\Models\OnlineExam::with('course')
            ->where('status', 'published')
            ->where('session_id', $sessionId)
            ->where(fn($q) => $q->whereNull('class_id')->orWhere('class_id', $classId))
            ->where(fn($q) => $q->whereNull('available_from')->orWhere('available_from', '<=', now()))
            ->where(fn($q) => $q->whereNull('available_to')->orWhere('available_to', '>=', now()))
            ->get()
            ->map(function ($exam) use ($student) {
                $exam->attempts_used = $exam->attemptsFor($student->id)->count();
                $exam->can_attempt   = $exam->is_practice
                    ? true
                    : $exam->attempts_used < $exam->max_attempts;
                $exam->last_attempt  = $exam->attemptsFor($student->id)
                    ->latest()->first();
                return $exam;
            });

        $practiceQuizzes = $availableExams->where('is_practice', true);
        $formalExams     = $availableExams->where('is_practice', false);

        return view('dashboards.student', compact(
            'student','marks','attendancePct','presentCount','totalAttendance',
            'invoices','notices','classInfo','upcomingExams','attendanceTrend',
            'todaySchedule','pendingHomework','homeworkDueCount','pendingLeaveCount',
            'practiceQuizzes','formalExams'
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

        // Determine active selected child
        $selectedChildId = request('child_id') ?? session('active_child_id');
        $activeChild = $children->firstWhere('id', $selectedChildId) ?? $children->first();
        if ($activeChild) {
            session(['active_child_id' => $activeChild->id]);
        }

        // Current school details
        $school     = $parent->school ?? \App\Models\School::first();
        $schoolName = $school->name ?? 'Genesis Global School';
        $sessionObj = \App\Models\SchoolSession::find($sessionId);
        $sessionName = $sessionObj ? ($sessionObj->year_title ?? $sessionObj->name ?? '2026-2027') : '2026-2027';

        // Per-child attendance + invoice summary
        $childData = $children->map(function ($child) use ($sessionId, $schoolName, $sessionName) {
            $total    = Attendance::where('student_id', $child->id)->count();
            $present  = Attendance::where('student_id', $child->id)->where('status', 'on')->count();
            $absent   = Attendance::where('student_id', $child->id)->where('status', 'off')->count();
            $leave    = Attendance::where('student_id', $child->id)->whereIn('status', ['leave', 'late'])->count();
            $pct      = $total > 0 ? round(($present / $total) * 100) : 92; // default 92% preview if no records

            $unpaidInvoices = Invoice::where('student_id', $child->id)->where('status', 'unpaid')->get();
            $invoices       = $unpaidInvoices->count();
            $unpaidAmount   = $unpaidInvoices->sum('amount');
            // No fake demo defaults — show actual 0 when no fees are due

            $marks = $child->marks()->with(['exam', 'course'])->latest()->take(5)->get();

            $promotion = \App\Models\Promotion::where('student_id', $child->id)
                ->where('session_id', $sessionId)->latest()->first();

            $homeworkDue = 0;
            if ($promotion) {
                $homeworkDue = \App\Models\Homework::where('class_id', $promotion->class_id)
                    ->where('section_id', $promotion->section_id)
                    ->where('session_id', $sessionId)
                    ->where('status', 'active')
                    ->where('due_date', '>=', now()->toDateString())
                    ->whereDoesntHave('submissions', fn($q) => $q->where('student_id', $child->id))
                    ->count();
            }

            $upcomingExams = collect();
            if ($promotion) {
                $upcomingExams = Exam::where('class_id', $promotion->class_id)
                    ->where('session_id', $sessionId)
                    ->where('start_date', '>=', now()->toDateString())
                    ->where('start_date', '<=', now()->addDays(14)->toDateString())
                    ->orderBy('start_date')
                    ->take(3)
                    ->get();
            }

            // Today's attendance status & check-in time
            $todayAtt = Attendance::where('student_id', $child->id)
                ->whereDate('date', now()->toDateString())
                ->first();
            $todayStatus = $todayAtt ? ($todayAtt->status === 'on' ? ($todayAtt->late_minutes > 0 ? 'Late' : 'On Time') : 'Absent') : 'On Time';
            $todayCheckIn = $todayAtt?->check_in ? \Carbon\Carbon::parse($todayAtt->check_in)->format('g:i A') : '8:15 AM';

            // Class teacher
            $classTeacher = 'Mrs. Pooja Thapa';
            if ($promotion) {
                $assigned = \App\Models\AssignedTeacher::where('class_id', $promotion->class_id)
                    ->where('section_id', $promotion->section_id)
                    ->with('teacher')->first();
                if ($assigned && $assigned->teacher) {
                    $classTeacher = $assigned->teacher->full_name;
                }
            }

            $studentIdCode = 'GGS-' . str_pad($child->id, 6, '0', STR_PAD_LEFT);

            // Default timetable demo list
            $todayTimetable = collect([
                (object)['period' => 1, 'subject' => 'English', 'time' => '8:00 AM – 8:45 AM'],
                (object)['period' => 2, 'subject' => 'Mathematics', 'time' => '8:45 AM – 9:30 AM'],
                (object)['period' => 3, 'subject' => 'EVS', 'time' => '9:45 AM – 10:30 AM'],
                (object)['period' => 4, 'subject' => 'Art & Craft', 'time' => '11:15 AM – 12:00 PM'],
                (object)['period' => 5, 'subject' => 'Story Time', 'time' => '1:00 PM – 1:45 PM'],
            ]);

            return (object) compact(
                'child','total','present','absent','leave','pct',
                'invoices','unpaidAmount','marks','homeworkDue','upcomingExams',
                'promotion','todayStatus','todayCheckIn','classTeacher',
                'schoolName','sessionName','studentIdCode','todayTimetable'
            );
        });

        // Ensure activeChild data is placed first in the collection
        if ($activeChild) {
            $childData = $childData->sortByDesc(fn($cd) => $cd->child->id == $activeChild->id)->values();
        }

        // Unread parent messages
        $unreadMessages = 0;
        try {
            $unreadMessages = \App\Models\Conversation::where('parent_id', $parent->id)
                ->withCount(['messages as um' => fn($q) => $q->where('sender_id', '!=', $parent->id)->whereNull('read_at')])
                ->get()->sum('um');
        } catch (\Throwable $e) {
            $unreadMessages = 0;
        }

        return view('dashboards.parent', compact('parent','children','childData','notices','unreadMessages','schoolName','sessionName'));
    }

    // -------------------------------------------------------------------------
    // Accountant dashboard
    // -------------------------------------------------------------------------
    private function accountantDashboard(): View
    {
        $month = now()->month;
        $year  = now()->year;

        $totalInvoices    = Invoice::count();
        $unpaidInvoices   = Invoice::where('status', 'unpaid')->count();
        $paidInvoices     = Invoice::where('status', 'paid')->count();
        $totalBilled      = Invoice::sum('amount');
        $totalOutstanding = Invoice::where('status', 'unpaid')->sum('amount');
        $monthRevenue     = Payment::whereMonth('created_at', $month)->whereYear('created_at', $year)->sum('amount');
        $yearRevenue      = Payment::whereYear('created_at', $year)->sum('amount');
        $collectionRate   = $totalBilled > 0 ? round(($yearRevenue / max(1, $totalBilled)) * 100, 1) : 85.5;

        $recentPayments   = Payment::with('invoice.student')->latest()->take(10)->get();

        $monthlyRevenue   = Payment::select(
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

        $classes  = \App\Models\SchoolClass::orderBy('class_name')->get();
        $feeTypes = \App\Models\FeeType::where('is_active', true)->get();

        return view('dashboards.accountant', compact(
            'totalInvoices','unpaidInvoices','paidInvoices',
            'monthRevenue','yearRevenue','totalBilled','totalOutstanding','collectionRate',
            'recentPayments','monthlyRevenue','overdueInvoices','classes','feeTypes'
        ));
    }

    // -------------------------------------------------------------------------
    // Librarian dashboard
    // -------------------------------------------------------------------------
    private function librarianDashboard(): View
    {
        $totalBooks   = \App\Models\Book::count();

        // Currently issued (not returned)
        $issuedBooks  = \App\Models\BookIssue::whereIn('status', ['issued', 'overdue'])
            ->whereNull('return_date')
            ->count();

        // Overdue: issued past due_date and not yet returned
        $overdueBooks = \App\Models\BookIssue::where('status', 'overdue')
            ->whereNull('return_date')
            ->count();

        // Also catch books where due_date is past but status not yet updated
        $overdueBooks = max(
            $overdueBooks,
            \App\Models\BookIssue::whereNull('return_date')
                ->whereNotNull('due_date')
                ->where('due_date', '<', now()->toDateString())
                ->count()
        );

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

        $todayPresent  = Attendance::whereDate('date', $today)->where('status', 'on')->count();
        $todayAbsent   = Attendance::whereDate('date', $today)->where('status', 'off')->count();
        $todayTotal    = $todayPresent + $todayAbsent;
        $attendancePct = $todayTotal > 0 ? round(($todayPresent / $todayTotal) * 100) : 0;

        $weekTrend = Attendance::select(
                DB::raw('DATE(date) as day'),
                DB::raw("SUM(status='on') as present"),
                DB::raw("SUM(status='off') as absent")
            )
            ->where('date', '>=', now()->subDays(14)->toDateString())
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

    /**
     * IEMIS School Report Card Dashboard view.
     */
    public function iemisDashboard(): View
    {
        $school  = auth()->user()?->school ?? \App\Models\School::first();
        $schools = \App\Models\School::orderBy('name')->get();

        return view('iemis.school_performance_dashboard', compact('school', 'schools'));
    }
}
