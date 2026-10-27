<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignedTeacher;
use App\Models\Exam;
use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\Feedback;
use App\Models\Homework;
use App\Models\HostelRoom;
use App\Models\Invoice;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\Mark;
use App\Models\Notice;
use App\Models\Promotion;
use App\Models\Routine;
use App\Models\SchoolClass;
use App\Models\Staff;
use App\Models\Subject;
use App\Models\Syllabus;
use App\Models\TransportRoute;
use App\Models\Visitor;
use App\Repositories\SchoolSessionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * OfflineSeedController (v4 — Comprehensive Offline Architecture)
 *
 * Provides the full offline-first data payload for the PWA layer.
 * Called by public/js/offline-seed.js on login / page load (same-origin cookie auth).
 *
 * V1: classes, students
 * V2: + staff, fee_structures, fee_types, subjects, hostels, transport, reference
 * V3: + syllabi, assignments, marks, notices, exams
 * V4: + homework, routines, invoices, visitors, leave_applications, feedbacks
 *
 * GET /api/offline/seed-data
 */
class OfflineSeedController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user      = $request->user();
        $schoolId  = config('tenant.school_id');
        $sessionId = $this->currentSessionId();

        // ── 1. Classes + Sections ──────────────────────────────────────────
        $classes = SchoolClass::with('sections')
            ->whereHas('promotions', fn ($q) => $q->where('session_id', $sessionId))
            ->get()
            ->map(fn ($cls) => [
                'id'         => $cls->id,
                'class_name' => $cls->class_name,
                'sections'   => $cls->sections->map(fn ($sec) => [
                    'id'           => $sec->id,
                    'section_name' => $sec->section_name,
                    'room_no'      => $sec->room_no,
                ])->values()->all(),
            ])
            ->values()->all();

        // ── 2. Students (role-scoped) ──────────────────────────────────────
        $promotionQuery = Promotion::with('student:id,first_name,last_name,roll_no,admission_no,photo')
            ->where('session_id', $sessionId);

        if ($user && method_exists($user, 'isTeacher') && ($user->isTeacher() || $user->isClassTeacher())) {
            $assignedClassIds = AssignedTeacher::where('teacher_id', $user->id)
                ->where('session_id', $sessionId)
                ->pluck('class_id')->unique()->all();
            if ($assignedClassIds) {
                $promotionQuery->whereIn('class_id', $assignedClassIds);
            }
        }

        $students = $promotionQuery->get()->map(fn ($promo) => [
            'student_id'   => $promo->student_id,
            'class_id'     => $promo->class_id,
            'section_id'   => $promo->section_id ?? 0,
            'roll_no'      => $promo->student?->roll_no ?? $promo->roll_no ?? null,
            'admission_no' => $promo->student?->admission_no ?? null,
            'first_name'   => $promo->student?->first_name ?? '—',
            'last_name'    => $promo->student?->last_name  ?? '',
            'photo'        => $promo->student?->photo
                ? asset('storage/' . $promo->student->photo) : null,
        ])->values()->all();

        // ── 3. Staff / Teachers ────────────────────────────────────────────
        $staff = [];
        try {
            $staff = Staff::with(['user:id,email,phone'])->limit(500)->get()
                ->map(fn ($s) => [
                    'staff_id'      => $s->id,
                    'user_id'       => $s->user_id,
                    'first_name'    => $s->first_name,
                    'last_name'     => $s->last_name,
                    'role_name'     => $s->designation ?? $s->role_name ?? 'staff',
                    'department_id' => $s->department_id,
                    'is_active'     => (bool) ($s->status === 'active' || $s->is_active ?? true),
                    'email'         => $s->user?->email,
                    'phone'         => $s->user?->phone ?? $s->phone,
                ])->values()->all();
        } catch (\Throwable) {}

        // ── 4. Fee Structures ──────────────────────────────────────────────
        $feeStructures = [];
        try {
            $feeStructures = FeeStructure::where('session_id', $sessionId)
                ->with(['feeType'])->limit(500)->get()
                ->map(fn ($fs) => [
                    'id'          => $fs->id,
                    'class_id'    => $fs->class_id,
                    'session_id'  => $sessionId,
                    'fee_type_id' => $fs->fee_type_id,
                    'fee_name'    => optional($fs->feeType)->name ?? ($fs->fee_name ?? 'Fee'),
                    'amount'      => (float) ($fs->amount ?? 0),
                    'installment' => $fs->installment ?? 'Annual',
                    'due_date'    => optional($fs->due_date)->toDateString(),
                ])->values()->all();
        } catch (\Throwable) {}

        // ── 5. Fee Types ───────────────────────────────────────────────────
        $feeTypes = [];
        try {
            $feeTypes = FeeType::limit(100)->get()
                ->map(fn ($ft) => [
                    'id'           => $ft->id,
                    'name'         => $ft->name,
                    'code'         => $ft->code ?? null,
                    'is_refundable'=> (bool) ($ft->is_refundable ?? false),
                ])->values()->all();
        } catch (\Throwable) {}

        // ── 6. Subjects ────────────────────────────────────────────────────
        $subjects = [];
        try {
            $subjects = Subject::limit(500)->get()
                ->map(fn ($sub) => [
                    'id'         => $sub->id,
                    'class_id'   => $sub->class_id,
                    'code'       => $sub->code ?? null,
                    'name'       => $sub->name,
                    'full_marks' => (float) ($sub->full_marks ?? 100),
                    'pass_marks' => (float) ($sub->pass_marks ?? 33),
                    'teacher_id' => $sub->teacher_id,
                    'type'       => $sub->type ?? 'theory',
                ])->values()->all();
        } catch (\Throwable) {}

        // ── 7. Hostels ─────────────────────────────────────────────────────
        $hostels = [];
        try {
            $hostels = HostelRoom::with(['hostel'])->limit(500)->get()
                ->map(fn ($r) => [
                    'id'          => 'room-' . $r->id,
                    'room_id'     => $r->id,
                    'hostel_id'   => $r->hostel_id,
                    'hostel_name' => optional($r->hostel)->name ?? 'Hostel',
                    'room_no'     => $r->room_number ?? $r->room_no ?? null,
                    'beds'        => (int) ($r->total_beds ?? $r->bed_count ?? 0),
                    'type'        => $r->type ?? optional($r->hostel)->type ?? 'general',
                    'fee_amount'  => (float) ($r->fee_amount ?? 0),
                ])->values()->all();
        } catch (\Throwable) {}

        // ── 8. Transport Routes ────────────────────────────────────────────
        $transport = [];
        try {
            $transport = TransportRoute::limit(200)->get()
                ->map(fn ($tr) => [
                    'id'          => $tr->id,
                    'route_name'  => $tr->route_name ?? $tr->title ?? null,
                    'route_code'  => $tr->route_code ?? null,
                    'vehicle_id'  => $tr->vehicle_id,
                    'stops_count' => (int) ($tr->stops_count ?? 0),
                    'fee_amount'  => (float) ($tr->fee_amount ?? $tr->fare ?? 0),
                ])->values()->all();
        } catch (\Throwable) {}

        // ── 9. Syllabi  (V3) ───────────────────────────────────────────────
        $syllabi = [];
        try {
            $query = Syllabus::where('session_id', $sessionId)->limit(500);
            if ($user && method_exists($user, 'isTeacher') && ($user->isTeacher() || $user->isClassTeacher())) {
                $assignedCourseIds = AssignedTeacher::where('teacher_id', $user->id)
                    ->where('session_id', $sessionId)
                    ->pluck('course_id')->unique()->filter()->all();
                if ($assignedCourseIds) {
                    $query->whereIn('course_id', $assignedCourseIds);
                }
            }
            $syllabi = $query->get()->map(fn ($s) => [
                'id'          => $s->id,
                'title'       => $s->title ?? $s->topic ?? '',
                'class_id'    => $s->class_id,
                'course_id'   => $s->course_id,
                'section_id'  => $s->section_id ?? 0,
                'session_id'  => $sessionId,
                'details'     => $s->details ?? $s->description ?? '',
                'created_at'  => optional($s->created_at)->toDateString(),
            ])->values()->all();
        } catch (\Throwable) {}

        // ── 10. Assignments  (V3) ──────────────────────────────────────────
        $assignments = [];
        try {
            $query = Assignment::where('session_id', $sessionId)->limit(300);
            if ($user && method_exists($user, 'isTeacher') && ($user->isTeacher() || $user->isClassTeacher())) {
                $query->where('teacher_id', $user->id);
            }
            $assignments = $query->get()->map(fn ($a) => [
                'id'          => $a->id,
                'title'       => $a->title ?? '',
                'class_id'    => $a->class_id,
                'section_id'  => $a->section_id ?? 0,
                'course_id'   => $a->course_id,
                'session_id'  => $sessionId,
                'teacher_id'  => $a->teacher_id,
                'due_date'    => optional($a->due_date)->toDateString(),
                'description' => $a->description ?? '',
                'total_marks' => (int) ($a->total_marks ?? 0),
                'created_at'  => optional($a->created_at)->toDateString(),
            ])->values()->all();
        } catch (\Throwable) {}

        // ── 11. Marks — recent (V3) ────────────────────────────────────────
        // Only seed the last 2 exam cycles to keep payload manageable.
        $marks = [];
        try {
            $recentExamIds = Exam::where('session_id', $sessionId)
                ->latest()->limit(4)->pluck('id');

            $marksQuery = Mark::whereIn('exam_id', $recentExamIds)->limit(2000);
            if ($user && method_exists($user, 'isTeacher') && ($user->isTeacher() || $user->isClassTeacher())) {
                $assignedCourseIds = AssignedTeacher::where('teacher_id', $user->id)
                    ->where('session_id', $sessionId)
                    ->pluck('course_id')->unique()->filter()->all();
                if ($assignedCourseIds) {
                    $marksQuery->whereIn('course_id', $assignedCourseIds);
                }
            }

            $marks = $marksQuery->get()->map(fn ($m) => [
                'id'         => $m->id,
                'student_id' => $m->student_id,
                'course_id'  => $m->course_id,
                'exam_id'    => $m->exam_id,
                'session_id' => $sessionId,
                'marks'      => (float) ($m->marks ?? 0),
                'grade'      => $m->grade ?? null,
            ])->values()->all();
        } catch (\Throwable) {}

        // ── 12. Notices — recent 50  (V3) ─────────────────────────────────
        $notices = [];
        try {
            $notices = Notice::latest()->limit(50)->get()
                ->map(fn ($n) => [
                    'id'           => $n->id,
                    'notice_title' => $n->notice_title ?? $n->title ?? '',
                    'description'  => $n->description ?? $n->notice_description ?? '',
                    'notice_date'  => optional($n->notice_date ?? $n->created_at)->toDateString(),
                    'class_id'     => $n->class_id ?? 0,
                    'created_at'   => optional($n->created_at)->toDateString(),
                ])->values()->all();
        } catch (\Throwable) {}

        // ── 13. Exams for current session  (V3) ────────────────────────────
        $exams = [];
        try {
            $exams = Exam::where('session_id', $sessionId)->latest()->limit(50)->get()
                ->map(fn ($e) => [
                    'id'         => $e->id,
                    'exam_name'  => $e->exam_name ?? $e->name ?? '',
                    'class_id'   => $e->class_id ?? 0,
                    'session_id' => $sessionId,
                    'start_date' => optional($e->start_date)->toDateString(),
                    'end_date'   => optional($e->end_date)->toDateString(),
                ])->values()->all();
        } catch (\Throwable) {}

        // ── 14. Homework & Assignments (V4) ──────────────────────────────
        $homework = [];
        try {
            $hwQuery = Homework::where('session_id', $sessionId)
                ->with(['course:id,course_name', 'teacher:id,first_name,last_name'])
                ->latest()->limit(200);
            if ($user && method_exists($user, 'isTeacher') && ($user->isTeacher() || $user->isClassTeacher())) {
                $hwQuery->where('teacher_id', $user->id);
            }
            $homework = $hwQuery->get()->map(fn ($h) => [
                'id'          => $h->id,
                'title'       => $h->title,
                'class_id'    => $h->class_id,
                'section_id'  => $h->section_id ?? 0,
                'course_id'   => $h->course_id,
                'course_name' => $h->course?->course_name ?? 'General',
                'teacher_name'=> $h->teacher ? ($h->teacher->first_name . ' ' . $h->teacher->last_name) : 'Teacher',
                'due_date'    => optional($h->due_date)->toDateString(),
                'description' => $h->description ?? '',
                'total_marks' => (int) ($h->total_marks ?? 0),
                'status'      => $h->status ?? 'active',
            ])->values()->all();
        } catch (\Throwable) {}

        // ── 15. Routines / Class Timetables (V4) ─────────────────────────
        $routines = [];
        try {
            $routines = Routine::where('session_id', $sessionId)
                ->with(['schoolClass:id,class_name', 'teacher:id,first_name,last_name'])
                ->get()->map(fn ($r) => [
                    'id'          => $r->id,
                    'class_id'    => $r->class_id,
                    'section_id'  => $r->section_id ?? 0,
                    'weekday'     => $r->weekday,
                    'start'       => $r->start,
                    'end'         => $r->end,
                    'room'        => $r->room ?? '',
                    'course_id'   => $r->course_id,
                    'teacher_id'  => $r->teacher_id,
                    'teacher_name'=> $r->teacher ? ($r->teacher->first_name . ' ' . $r->teacher->last_name) : '',
                    'color'       => $r->color ?? '#6366f1',
                ])->values()->all();
        } catch (\Throwable) {}

        // ── 16. Fee Invoices (V5) ─────────────────────────────────────────
        $invoices = [];
        try {
            $invQuery = Invoice::with(['student:id,first_name,last_name,roll_no,admission_no', 'items.feeType'])
                ->where('session_id', $sessionId)
                ->latest()
                ->limit(500);
            if ($user && method_exists($user, 'isStudent') && $user->isStudent()) {
                $invQuery->where('student_id', $user->id);
            }
            $invoices = $invQuery->get()->map(fn ($inv) => [
                'id'             => $inv->id,
                'student_id'     => $inv->student_id,
                'student_name'   => $inv->student ? ($inv->student->first_name . ' ' . $inv->student->last_name) : 'Student #' . $inv->student_id,
                'roll_no'        => $inv->student?->roll_no,
                'admission_no'   => $inv->student?->admission_no,
                'title'          => $inv->title ?? 'Tuition & Academic Fees',
                'invoice_number' => $inv->invoice_number,
                'total_payable'  => (float) ($inv->total_payable ?? $inv->amount ?? 0),
                'paid_amount'    => (float) ($inv->paid_amount ?? 0),
                'due_amount'     => (float) max(0, ($inv->total_payable ?? $inv->amount ?? 0) - ($inv->paid_amount ?? 0)),
                'status'         => $inv->status ?? 'unpaid',
                'due_date'       => optional($inv->due_date)->toDateString(),
                'items'          => $inv->items->map(fn ($it) => [
                    'id'          => $it->id,
                    'name'        => $it->description ?? optional($it->feeType)->name ?? 'Fee Item',
                    'amount'      => (float) ($it->total ?? $it->unit_price ?? 0),
                ])->values()->all(),
            ])->values()->all();
        } catch (\Throwable) {}

        // ── 17. Expenses (V5) ─────────────────────────────────────────────
        $expenses = [];
        try {
            $expenses = \App\Models\Expense::latest('expense_date')->limit(200)->get()->map(fn ($e) => [
                'id'             => $e->id,
                'title'          => $e->title,
                'category'       => $e->category ?? 'General',
                'amount'         => (float) $e->amount,
                'expense_date'   => optional($e->expense_date)->toDateString() ?? now()->toDateString(),
                'payment_method' => $e->payment_method ?? 'cash',
                'reference'      => $e->reference,
                'notes'          => $e->notes,
            ])->values()->all();
        } catch (\Throwable) {}

        // ── 18. Income Records (V5) ───────────────────────────────────────
        $income = [];
        try {
            $income = \App\Models\IncomeRecord::latest('income_date')->limit(200)->get()->map(fn ($inc) => [
                'id'             => $inc->id,
                'title'          => $inc->title,
                'category'       => $inc->category ?? 'General',
                'amount'         => (float) $inc->amount,
                'income_date'    => optional($inc->income_date)->toDateString() ?? now()->toDateString(),
                'payment_method' => $inc->payment_method ?? 'cash',
                'reference'      => $inc->reference,
                'notes'          => $inc->notes,
            ])->values()->all();
        } catch (\Throwable) {}

        // ── 19. Payments & Receipts (V5) ──────────────────────────────────
        $payments = [];
        try {
            $payments = \App\Models\Payment::with(['invoice.student'])->latest('payment_date')->limit(200)->get()->map(fn ($p) => [
                'id'             => $p->id,
                'invoice_id'     => $p->invoice_id,
                'invoice_number' => $p->invoice?->invoice_number,
                'student_name'   => $p->invoice?->student ? ($p->invoice->student->first_name . ' ' . $p->invoice->student->last_name) : 'Student',
                'amount'         => (float) $p->amount,
                'payment_date'   => optional($p->payment_date)->toDateString() ?? now()->toDateString(),
                'payment_method' => $p->payment_method ?? 'cash',
                'receipt_number' => $p->receipt_number ?? ('REC-' . $p->id),
                'notes'          => $p->notes,
            ])->values()->all();
        } catch (\Throwable) {}

        // ── 20. Visitors Registry ─────────────────────────────────────────
        $visitors = [];
        try {
            $visitors = Visitor::whereDate('check_in_time', today())->latest()->limit(50)->get()
                ->map(fn ($v) => [
                    'id'             => $v->id,
                    'name'           => $v->name,
                    'phone'          => $v->phone,
                    'badge_number'   => $v->badge_number,
                    'person_to_meet' => $v->person_to_meet,
                    'purpose'        => $v->purpose,
                    'check_in_time'  => optional($v->check_in_time)->toTimeString(),
                    'status'         => $v->status ?? 'in_premises',
                ])->values()->all();
        } catch (\Throwable) {}

        // ── 21. Leave Applications ────────────────────────────────────────
        $leaveApplications = [];
        try {
            $leaveQuery = LeaveApplication::with('leaveType:id,name')->latest()->limit(50);
            if ($user) {
                $leaveQuery->where('user_id', $user->id);
            }
            $leaveApplications = $leaveQuery->get()->map(fn ($l) => [
                'id'         => $l->id,
                'user_id'    => $l->user_id,
                'leave_type' => $l->leaveType?->name ?? 'General',
                'from_date'  => optional($l->from_date)->toDateString(),
                'to_date'    => optional($l->to_date)->toDateString(),
                'total_days' => (int) ($l->total_days ?? 1),
                'reason'     => $l->reason ?? '',
                'status'     => $l->status ?? 'pending',
            ])->values()->all();
        } catch (\Throwable) {}

        // ── 22. Feedbacks & Grievances ────────────────────────────────────
        $feedbacks = [];
        try {
            $fbQuery = Feedback::latest()->limit(30);
            if ($user && method_exists($user, 'hasRole') && !$user->hasRole(['admin', 'principal'])) {
                $fbQuery->where('user_id', $user->id);
            }
            $feedbacks = $fbQuery->get()->map(fn ($f) => [
                'id'          => $f->id,
                'ticket_code' => $f->ticket_code,
                'title'       => $f->title,
                'category'    => $f->category,
                'priority'    => $f->priority,
                'status'      => $f->status,
                'created_at'  => optional($f->created_at)->toDateString(),
            ])->values()->all();
        } catch (\Throwable) {}

        // ── 23. Academic Calendar Events ──────────────────────────────
        $events = [];
        try {
            $events = Event::where('session_id', $sessionId)->orderBy('start')->get()
                ->map(fn ($ev) => [
                    'id'            => $ev->id,
                    'title'         => $ev->title,
                    'category'      => $ev->category ?? 'academic',
                    'is_holiday'    => (bool) $ev->is_holiday,
                    'start'         => optional($ev->start)->toDateTimeString(),
                    'end'           => optional($ev->end)->toDateTimeString(),
                    'start_bs'      => $ev->start_bs,
                    'end_bs'        => $ev->end_bs,
                    'audience'      => $ev->audience ?? 'all',
                    'academic_term' => $ev->academic_term,
                    'venue'         => $ev->venue,
                    'color'         => $ev->color,
                    'description'   => $ev->description,
                ])->values()->all();
        } catch (\Throwable) {}

        // ── 24. Reference KV ──────────────────────────────────────────────
        $reference = [
            ['key' => 'academic_sessions',  'value' => $this->listAcademicSessions()],
            ['key' => 'current_session_id', 'value' => $sessionId],
            ['key' => 'school_id',          'value' => $schoolId],
            ['key' => 'seed_schema_version','value' => 5],
            ['key' => 'nepali_months',      'value' => [
                'Baisakh','Jestha','Asar','Shrawan','Bhadra','Ashoj',
                'Kartik','Mangsir','Poush','Magh','Falgun','Chaitra',
            ]],
            ['key' => 'today_day_book',     'value' => [
                'date'           => now()->toDateString(),
                'today_income'   => (float) (\App\Models\Payment::whereDate('payment_date', today())->sum('amount') + \App\Models\IncomeRecord::whereDate('income_date', today())->sum('amount')),
                'today_expenses' => (float) \App\Models\Expense::whereDate('expense_date', today())->sum('amount'),
            ]],
        ];

        return response()->json([
            'schema_version' => 5,
            'session_id'     => $sessionId,
            'school_id'      => $schoolId,
            'seeded_at'      => now()->toIso8601String(),

            // ── Reference data (V1 + V2) ──
            'classes'        => $classes,
            'students'       => $students,
            'staff'          => $staff,
            'fee_structures' => $feeStructures,
            'fee_types'      => $feeTypes,
            'subjects'       => $subjects,
            'hostels'        => $hostels,
            'transport'      => $transport,
            'reference'      => $reference,

            // ── Live, Calendar & Finance data (V3 + V4 + V5) ──
            'syllabi'        => $syllabi,
            'assignments'    => $assignments,
            'marks'          => $marks,
            'notices'        => $notices,
            'exams'          => $exams,
            'events'         => $events,
            'homework'       => $homework,
            'routines'       => $routines,
            'invoices'       => $invoices,
            'expenses'       => $expenses,
            'income'         => $income,
            'payments'       => $payments,
            'visitors'       => $visitors,
            'leave'          => $leaveApplications,
            'feedback'       => $feedbacks,

            // Sizes for progress bar in offline-seed.js
            'sizes' => [
                'classes'        => count($classes),
                'students'       => count($students),
                'staff'          => count($staff),
                'fee_structures' => count($feeStructures),
                'fee_types'      => count($feeTypes),
                'subjects'       => count($subjects),
                'hostels'        => count($hostels),
                'transport'      => count($transport),
                'syllabi'        => count($syllabi),
                'assignments'    => count($assignments),
                'marks'          => count($marks),
                'notices'        => count($notices),
                'exams'          => count($exams),
                'homework'       => count($homework),
                'routines'       => count($routines),
                'invoices'       => count($invoices),
                'expenses'       => count($expenses),
                'income'         => count($income),
                'payments'       => count($payments),
                'visitors'       => count($visitors),
                'leave'          => count($leaveApplications),
                'feedback'       => count($feedbacks),
            ],
        ]);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function currentSessionId(): int
    {
        try {
            return (int) (new SchoolSessionRepository())->getCurrentSession()?->id ?? 0;
        } catch (\Throwable) {
            return 0;
        }
    }

    private function listAcademicSessions(): array
    {
        try {
            $class = '\App\Models\AcademicSession';
            if (class_exists($class)) {
                return $class::query()
                    ->limit(20)->orderByDesc('id')
                    ->get(['id', 'name', 'start_date', 'end_date', 'is_default'])
                    ->toArray();
            }
            // Fallback — try SchoolSession
            return \App\Models\SchoolSession::query()
                ->limit(20)->orderByDesc('id')
                ->get(['id', 'session_name as name', 'start_year', 'end_year'])
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
    }
}
