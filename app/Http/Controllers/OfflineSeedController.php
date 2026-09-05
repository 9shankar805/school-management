<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignedTeacher;
use App\Models\Exam;
use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\HostelRoom;
use App\Models\Mark;
use App\Models\Notice;
use App\Models\Promotion;
use App\Models\SchoolClass;
use App\Models\Staff;
use App\Models\Subject;
use App\Models\Syllabus;
use App\Models\TransportRoute;
use App\Repositories\SchoolSessionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * OfflineSeedController (v3)
 *
 * Provides the full offline-first data payload for the PWA layer.
 * Called by public/js/offline-seed.js on login / page load (same-origin cookie auth).
 *
 * V1: classes, students
 * V2: + staff, fee_structures, fee_types, subjects, hostels, transport, reference
 * V3: + syllabi, assignments, marks, notices, exams
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

        // ── 14. Reference KV ──────────────────────────────────────────────
        $reference = [
            ['key' => 'academic_sessions',  'value' => $this->listAcademicSessions()],
            ['key' => 'current_session_id', 'value' => $sessionId],
            ['key' => 'school_id',          'value' => $schoolId],
            ['key' => 'seed_schema_version','value' => 3],
            ['key' => 'nepali_months',      'value' => [
                'Baisakh','Jestha','Asar','Shrawan','Bhadra','Ashoj',
                'Kartik','Mangsir','Poush','Magh','Falgun','Chaitra',
            ]],
        ];

        return response()->json([
            'schema_version' => 3,
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

            // ── Live data (V3) ──
            'syllabi'        => $syllabi,
            'assignments'    => $assignments,
            'marks'          => $marks,
            'notices'        => $notices,
            'exams'          => $exams,

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
