<?php

namespace App\Http\Controllers\Api\TeacherApp;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AssignedTeacher;
use App\Models\Promotion;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\Section;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * TeacherApp — Attendance API
 *
 * All attendance is stored with status = 'on' (present) or 'off' (absent).
 * This matches the canonical values written by AttendanceController (web).
 *
 * Routes (all under /api/teacher-app/attendance, auth:sanctum protected):
 *   GET  /classes                        — classes assigned to teacher
 *   GET  /{classId}/students             — students in class (with today's status)
 *   GET  /{classId}/date/{date}          — attendance for a specific date
 *   POST /{classId}                      — save / upsert attendance for a date
 *   GET  /{studentId}/history            — per-student attendance history
 */
class AttendanceController extends Controller
{
    /**
     * GET /classes
     * Returns classes (+ sections) assigned to the authenticated teacher.
    /**
     * GET /classes
     * Returns classes (+ sections) where the authenticated teacher is designated as Class In-Charge.
     * Administrators / principals receive all active classes.
     */
    public function classes(Request $request): JsonResponse
    {
        $teacher   = auth()->user();
        $sessionId = $this->currentSessionId();
        $isAdmin   = $teacher && $teacher->hasAnyRole(['super-admin', 'admin', 'principal', 'vice-principal', 'academic-coordinator', 'attendance-officer']);

        if ($isAdmin) {
            $classes = SchoolClass::with('sections')
                ->when($teacher->school_id, fn($q) => $q->where('school_id', $teacher->school_id))
                ->orderBy('class_name')
                ->get()
                ->map(fn($c) => [
                    'id'              => $c->id,
                    'name'            => $c->class_name,
                    'is_class_teacher'=> true,
                    'sections'        => $c->sections->map(fn($s) => [
                        'id'              => $s->id,
                        'name'            => $s->section_name,
                        'is_class_teacher'=> true,
                    ]),
                ]);

            return response()->json(['classes' => $classes]);
        }

        // Classes & sections assigned to this teacher as Class In-Charge (is_class_teacher = true)
        $inChargeAssignments = AssignedTeacher::where('teacher_id', $teacher->id)
            ->where('is_class_teacher', true)
            ->when($sessionId, fn($q) => $q->where('session_id', $sessionId))
            ->get();

        $classIds   = $inChargeAssignments->pluck('class_id')->unique()->values();
        $sectionIds = $inChargeAssignments->pluck('section_id')->unique()->values();

        $classes = SchoolClass::with(['sections' => fn($q) => $q->whereIn('id', $sectionIds)])
            ->whereIn('id', $classIds)
            ->when($teacher->school_id, fn($q) => $q->where('school_id', $teacher->school_id))
            ->orderBy('class_name')
            ->get()
            ->map(fn($c) => [
                'id'              => $c->id,
                'name'            => $c->class_name,
                'is_class_teacher'=> true,
                'sections'        => $c->sections->map(fn($s) => [
                    'id'              => $s->id,
                    'name'            => $s->section_name,
                    'is_class_teacher'=> true,
                ]),
            ]);

        return response()->json(['classes' => $classes]);
    }

    /**
     * GET /{classId}/students
     * Returns all students in the class, including today's attendance status
     * so the teacher app can pre-populate the mark form.
     */
    public function students(Request $request, int $classId): JsonResponse
    {
        $teacher   = auth()->user();
        $sessionId = $this->currentSessionId();
        $sectionId = (int) $request->query('section_id', 0);
        $today     = Carbon::today()->toDateString();
        $isAdmin   = $teacher && $teacher->hasAnyRole(['super-admin', 'admin', 'principal', 'vice-principal', 'academic-coordinator', 'attendance-officer']);

        // Enforce Class In-Charge restriction
        if (!$isAdmin) {
            $inChargeSectionIds = AssignedTeacher::where('teacher_id', $teacher->id)
                ->where('class_id', $classId)
                ->when($sessionId, fn($q) => $q->where('session_id', $sessionId))
                ->where('is_class_teacher', true)
                ->pluck('section_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            $classHasSections = Section::where('class_id', $classId)->exists();

            if ($classHasSections) {
                if (empty($inChargeSectionIds)) {
                    return response()->json([
                        'error' => 'Unauthorized: You are not assigned as Class In-Charge for this class.'
                    ], 403);
                }

                if ($sectionId === 0 && count($inChargeSectionIds) === 1) {
                    $sectionId = (int) $inChargeSectionIds[0];
                }

                if (!in_array($sectionId, $inChargeSectionIds, true)) {
                    return response()->json([
                        'error' => 'Unauthorized: You are only the Class In-Charge for your designated section. You cannot take attendance for other sections.'
                    ], 403);
                }
            }
        }

        // Fetch students promoted to this class/section in the current session
        $query = Promotion::where('class_id', $classId)
            ->where('session_id', $sessionId)
            ->with(['student' => fn($q) => $q->select('id', 'first_name', 'last_name', 'photo', 'gender', 'roll_no')])
            ->when($sectionId, fn($q) => $q->where('section_id', $sectionId));

        $promotions = $query->get();

        // Fetch today's attendance in bulk with submission info
        $todayAttendance = Attendance::with('recordedBy')
            ->where('class_id', $classId)
            ->where('session_id', $sessionId)
            ->whereDate('date', $today)
            ->when($sectionId, fn($q) => $q->where('section_id', $sectionId))
            ->get()
            ->keyBy('student_id');

        $students = $promotions->map(function ($promo) use ($todayAttendance) {
            $s = $promo->student;
            if (! $s) return null;

            $att         = $todayAttendance->get($s->id);
            $dbStatus    = $att?->status;
            $displayStatus = match ($dbStatus) {
                'on'    => 'present',
                'off'   => 'absent',
                default => 'not_marked',
            };

            return [
                'id'            => $s->id,
                'name'          => $s->full_name,
                'roll_no'       => $promo->roll_no ?? $s->roll_no,
                'photo'         => $s->avatar,
                'gender'        => $s->gender,
                'section_id'    => $promo->section_id,
                'status'        => $displayStatus,
                'already_saved' => $dbStatus !== null,
                'taken_time'    => $att?->taken_time ?? ($att?->submitted_at?->format('h:i A')),
                'recorded_by'   => $att?->recordedBy?->name,
            ];
        })->filter()->values();

        $firstAtt = $todayAttendance->first();

        return response()->json([
            'students'     => $students,
            'date'         => $today,
            'class_id'     => $classId,
            'section_id'   => $sectionId ?: null,
            'taken_time'   => $firstAtt?->taken_time ?? ($firstAtt?->submitted_at?->format('h:i A')),
            'recorded_by'  => $firstAtt?->recordedBy?->name,
            'submitted_at' => $firstAtt?->submitted_at?->toIso8601String(),
        ]);
    }

    /**
     * GET /{classId}/date/{date}
     * Returns attendance for a given date (or today).
     * Used for editing a past day's records.
     */
    public function attendanceByDate(Request $request, int $classId, string $date): JsonResponse
    {
        try {
            $parsedDate = Carbon::parse($date)->toDateString();
        } catch (\Throwable) {
            return response()->json(['error' => 'Invalid date format. Use YYYY-MM-DD.'], 422);
        }

        $teacher   = auth()->user();
        $sessionId = $this->currentSessionId();
        $sectionId = (int) $request->query('section_id', 0);
        $isAdmin   = $teacher && $teacher->hasAnyRole(['super-admin', 'admin', 'principal', 'vice-principal', 'academic-coordinator', 'attendance-officer']);

        if (!$isAdmin) {
            $inChargeSectionIds = AssignedTeacher::where('teacher_id', $teacher->id)
                ->where('class_id', $classId)
                ->when($sessionId, fn($q) => $q->where('session_id', $sessionId))
                ->where('is_class_teacher', true)
                ->pluck('section_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            $classHasSections = Section::where('class_id', $classId)->exists();

            if ($classHasSections) {
                if ($sectionId === 0 && count($inChargeSectionIds) === 1) {
                    $sectionId = (int) $inChargeSectionIds[0];
                }

                if (!in_array($sectionId, $inChargeSectionIds, true)) {
                    return response()->json([
                        'error' => 'Unauthorized: Only the designated Class In-Charge (Class Teacher) can view attendance for this section.'
                    ], 403);
                }
            }
        }

        $records = Attendance::with('recordedBy')
            ->where('class_id', $classId)
            ->where('session_id', $sessionId)
            ->whereDate('date', $parsedDate)
            ->when($sectionId, fn($q) => $q->where('section_id', $sectionId))
            ->get();

        $firstRecord = $records->first();

        return response()->json([
            'attendance'   => $records->map(fn($a) => [
                'student_id'   => $a->student_id,
                'status'       => $a->status === 'on' ? 'present' : 'absent',
                'late_minutes' => $a->late_minutes ?? 0,
                'remarks'      => $a->notes ?? '',
                'taken_time'   => $a->taken_time ?? ($a->submitted_at?->format('h:i A')),
            ]),
            'date'         => $parsedDate,
            'class_id'     => $classId,
            'taken_time'   => $firstRecord?->taken_time ?? ($firstRecord?->submitted_at?->format('h:i A')),
            'recorded_by'  => $firstRecord?->recordedBy?->name,
            'submitted_at' => $firstRecord?->submitted_at?->toIso8601String(),
        ]);
    }

    /**
     * POST /{classId}
     * Save (upsert) attendance for a class on a given date.
     * Only the designated Class In-Charge (Class Teacher) can submit attendance.
     */
    public function store(Request $request, int $classId): JsonResponse
    {
        $validated = $request->validate([
            'date'                         => 'required|date',
            'section_id'                   => 'nullable|integer|exists:sections,id',
            'course_id'                    => 'nullable|integer|exists:courses,id',
            'attendance'                   => 'required|array|min:1',
            'attendance.*.student_id'      => 'required|integer|exists:users,id',
            'attendance.*.status'          => 'required|in:present,absent,late,excused',
            'attendance.*.late_minutes'    => 'nullable|integer|min:0',
            'attendance.*.remarks'         => 'nullable|string|max:500',
        ]);

        $sessionId = $this->currentSessionId();
        $teacher   = auth()->user();
        $sectionId = (int) ($validated['section_id'] ?? 0);
        $courseId  = $validated['course_id']  ?? null;
        $isAdmin   = $teacher && $teacher->hasAnyRole(['super-admin', 'admin', 'principal', 'vice-principal', 'academic-coordinator', 'attendance-officer']);

        // Enforce Class In-Charge restriction
        if (!$isAdmin) {
            $inChargeSectionIds = AssignedTeacher::where('teacher_id', $teacher->id)
                ->where('class_id', $classId)
                ->when($sessionId, fn($q) => $q->where('session_id', $sessionId))
                ->where('is_class_teacher', true)
                ->pluck('section_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            $classHasSections = Section::where('class_id', $classId)->exists();

            if ($classHasSections) {
                if (empty($inChargeSectionIds)) {
                    return response()->json([
                        'error' => 'Unauthorized: You are not assigned as Class In-Charge for this class.'
                    ], 403);
                }

                if ($sectionId === 0 && count($inChargeSectionIds) === 1) {
                    $sectionId = (int) $inChargeSectionIds[0];
                }

                if (!in_array($sectionId, $inChargeSectionIds, true)) {
                    return response()->json([
                        'error' => 'Unauthorized: Only the designated Class In-Charge for this section can submit attendance.'
                    ], 403);
                }
            }
        }

        $date      = Carbon::parse($validated['date'])->toDateString();
        $schoolId  = $teacher->school_id ?? config('tenant.school_id');
        $now       = now()->toDateTimeString();
        $takenTime = Carbon::now()->format('h:i A');

        $saved   = 0;
        $errors  = [];

        foreach ($validated['attendance'] as $row) {
            try {
                $dbStatus    = in_array($row['status'], ['present', 'late']) ? 'on' : 'off';
                $lateMinutes = ($row['status'] === 'late') ? max(1, (int)($row['late_minutes'] ?? 5)) : 0;

                Attendance::updateOrCreate(
                    [
                        'student_id' => $row['student_id'],
                        'class_id'   => $classId,
                        'session_id' => $sessionId,
                        'date'       => $date,
                        ...($courseId ? ['course_id' => $courseId] : []),
                        ...($sectionId ? ['section_id' => $sectionId] : []),
                    ],
                    [
                        'status'       => $dbStatus,
                        'late_minutes' => $lateMinutes,
                        'recorded_by'  => $teacher->id,
                        'submitted_at' => $now,
                        'taken_time'   => $takenTime,
                        'school_id'    => $schoolId,
                        'section_id'   => $sectionId,
                        'course_id'    => $courseId,
                        'updated_at'   => $now,
                        'created_at'   => $now,
                    ]
                );

                $saved++;
            } catch (\Throwable $e) {
                Log::error('TeacherApp attendance store error', [
                    'student_id' => $row['student_id'],
                    'date'       => $date,
                    'error'      => $e->getMessage(),
                ]);
                $errors[] = ['student_id' => $row['student_id'], 'error' => $e->getMessage()];
            }
        }

        return response()->json([
            'status'       => $errors ? 'partial' : 'success',
            'message'      => "Attendance recorded at {$takenTime} by {$teacher->name} ({$saved} students synced).",
            'saved'        => $saved,
            'errors'       => $errors,
            'date'         => $date,
            'taken_time'   => $takenTime,
            'recorded_by'  => $teacher->name,
            'submitted_at' => $now,
        ], $errors && $saved === 0 ? 422 : 200);
    }

    /**
     * GET /{studentId}/history
     * Returns real attendance history for a student across the current session.
     */
    public function history(Request $request, int $studentId): JsonResponse
    {
        $sessionId = $this->currentSessionId();

        $records = Attendance::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->orderByDesc('date')
            ->get();

        $total   = $records->count();
        $present = $records->filter(fn($a) => $a->status === 'on')->count();
        $absent  = $records->filter(fn($a) => $a->status === 'off')->count();
        $late    = $records->filter(fn($a) => $a->status === 'on' && ($a->late_minutes ?? 0) > 0)->count();
        $pct     = $total > 0 ? round($present / $total * 100, 1) : 0;

        return response()->json([
            'summary' => [
                'total_days' => $total,
                'present'    => $present,
                'absent'     => $absent,
                'late'       => $late,
                'percentage' => $pct,
                'shortage'   => max(0, (int) ceil($total * 0.75 - $present)),
            ],
            'records' => $records->map(fn($a) => [
                'date'         => $a->date,
                'status'       => $a->status === 'on' ? 'present' : 'absent',
                'late_minutes' => $a->late_minutes ?? 0,
            ]),
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function currentSessionId(): int
    {
        return (int) (
            SchoolSession::current()?->id
            ?? SchoolSession::latest('id')->value('id')
            ?? 0
        );
    }
}
