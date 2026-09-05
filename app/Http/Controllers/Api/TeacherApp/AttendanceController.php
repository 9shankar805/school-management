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
     * Falls back to all active classes if no assignments found.
     */
    public function classes(Request $request): JsonResponse
    {
        $teacher   = auth()->user();
        $sessionId = $this->currentSessionId();

        // Classes assigned to this teacher via assigned_teachers table
        $classIds = AssignedTeacher::where('teacher_id', $teacher->id)
            ->when($sessionId, fn($q) => $q->where('session_id', $sessionId))
            ->pluck('class_id')
            ->unique()
            ->values();

        $classes = SchoolClass::with('sections')
            ->when($classIds->isNotEmpty(), fn($q) => $q->whereIn('id', $classIds))
            ->when($teacher->school_id, fn($q) => $q->where('school_id', $teacher->school_id))
            ->orderBy('class_name')
            ->get()
            ->map(fn($c) => [
                'id'         => $c->id,
                'name'       => $c->class_name,
                'sections'   => $c->sections->map(fn($s) => [
                    'id'   => $s->id,
                    'name' => $s->section_name,
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
        $sectionId = (int) $request->query('section_id', 0);
        $sessionId = $this->currentSessionId();
        $today     = Carbon::today()->toDateString();

        // Fetch students promoted to this class/section in the current session
        $query = Promotion::where('class_id', $classId)
            ->where('session_id', $sessionId)
            ->with(['student' => fn($q) => $q->select('id', 'first_name', 'last_name', 'photo', 'gender', 'roll_no')])
            ->when($sectionId, fn($q) => $q->where('section_id', $sectionId));

        $promotions = $query->get();

        // Fetch today's attendance in bulk (one query)
        $todayAttendance = Attendance::where('class_id', $classId)
            ->where('session_id', $sessionId)
            ->whereDate('date', $today)
            ->when($sectionId, fn($q) => $q->where('section_id', $sectionId))
            ->pluck('status', 'student_id');  // keyed by student_id

        $students = $promotions->map(function ($promo) use ($todayAttendance) {
            $s = $promo->student;
            if (! $s) return null;

            $dbStatus    = $todayAttendance->get($s->id);
            // Normalise stored 'on'/'off' to friendly 'present'/'absent' for the app UI
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
            ];
        })->filter()->values();

        return response()->json([
            'students'    => $students,
            'date'        => $today,
            'class_id'    => $classId,
            'section_id'  => $sectionId ?: null,
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

        $sessionId = $this->currentSessionId();
        $sectionId = (int) $request->query('section_id', 0);

        $records = Attendance::where('class_id', $classId)
            ->where('session_id', $sessionId)
            ->whereDate('date', $parsedDate)
            ->when($sectionId, fn($q) => $q->where('section_id', $sectionId))
            ->get()
            ->map(fn($a) => [
                'student_id'   => $a->student_id,
                // Return friendly names to the app UI
                'status'       => $a->status === 'on' ? 'present' : 'absent',
                'late_minutes' => $a->late_minutes ?? 0,
                'remarks'      => $a->notes ?? '',
            ]);

        return response()->json([
            'attendance' => $records,
            'date'       => $parsedDate,
            'class_id'   => $classId,
        ]);
    }

    /**
     * POST /{classId}
     * Save (upsert) attendance for a class on a given date.
     *
     * The app sends status as 'present'/'absent'/'late'/'excused'.
     * We normalise to 'on'/'off' before writing — matching the canonical DB values.
     *
     * Request body:
     * {
     *   "date": "2026-08-29",
     *   "section_id": 3,          // optional
     *   "course_id": 5,           // optional — for course-based attendance
     *   "attendance": [
     *     { "student_id": 1, "status": "present", "late_minutes": 0, "remarks": "" },
     *     { "student_id": 2, "status": "absent",  "late_minutes": 0, "remarks": "Sick" }
     *   ]
     * }
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
        $date      = Carbon::parse($validated['date'])->toDateString();
        $sectionId = $validated['section_id'] ?? null;
        $courseId  = $validated['course_id']  ?? null;
        $schoolId  = $teacher->school_id ?? config('tenant.school_id');
        $now       = now()->toDateTimeString();

        $saved   = 0;
        $errors  = [];

        foreach ($validated['attendance'] as $row) {
            try {
                // Normalise app-friendly status → canonical DB values
                // 'late' is stored as 'on' with late_minutes > 0
                // 'excused' is stored as 'off' (absent but excused — notes carry the reason)
                $dbStatus    = in_array($row['status'], ['present', 'late']) ? 'on' : 'off';
                $lateMinutes = ($row['status'] === 'late') ? max(1, (int)($row['late_minutes'] ?? 5)) : 0;

                Attendance::updateOrCreate(
                    [
                        'student_id' => $row['student_id'],
                        'class_id'   => $classId,
                        'session_id' => $sessionId,
                        'date'       => $date,
                        // Include course_id in the unique key only when present
                        // (course-based attendance mode)
                        ...($courseId ? ['course_id' => $courseId] : []),
                        ...($sectionId ? ['section_id' => $sectionId] : []),
                    ],
                    [
                        'status'       => $dbStatus,
                        'late_minutes' => $lateMinutes,
                        'school_id'    => $schoolId,
                        'section_id'   => $sectionId,
                        'course_id'    => $courseId,
                        'updated_at'   => $now,
                        'created_at'   => $now,   // ignored on update by updateOrCreate
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
            'status'  => $errors ? 'partial' : 'success',
            'message' => "Attendance saved: {$saved} records" . ($errors ? ' (' . count($errors) . ' failed)' : '.'),
            'saved'   => $saved,
            'errors'  => $errors,
            'date'    => $date,
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
            SchoolSession::where('is_current', true)
                ->when(config('tenant.school_id'), fn($q) => $q->where('school_id', config('tenant.school_id')))
                ->value('id')
            ?? SchoolSession::latest('id')->value('id')
            ?? 0
        );
    }
}
