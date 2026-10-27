<?php

namespace App\Http\Controllers\Api\StudentApp;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * GET /student-app/attendance
     * All attendance records for the authenticated student.
     */
    public function index(Request $request): JsonResponse
    {
        $attendance = Attendance::with('course')
            ->where('student_id', $request->user()->id)
            ->orderByDesc('date')
            ->get()
            ->map(fn($a) => $this->formatRecord($a));

        return response()->json([
            'status' => 'success',
            'data'   => $attendance,
        ]);
    }

    /**
     * GET /student-app/attendance/summary
     * Aggregated summary for the authenticated student.
     */
    public function summary(Request $request): JsonResponse
    {
        $records = Attendance::where('student_id', $request->user()->id)->get();

        $total    = $records->count();
        $present  = $records->where('status', 'on')->count();
        $absent   = $total - $present;
        $late     = $records->where('status', 'on')->where('late_minutes', '>', 0)->count();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'total'      => $total,
                'present'    => $present,
                'absent'     => $absent,
                'late'       => $late,
                'percentage' => $total > 0 ? round($present / $total * 100, 1) : 0,
                'shortage'   => max(0, (int) ceil($total * 0.75 - $present)),
            ],
        ]);
    }

    /**
     * GET /student-app/attendance/subject/{subjectId}
     * Attendance filtered by a specific subject/course.
     */
    public function bySubject(Request $request, int $subjectId): JsonResponse
    {
        $attendance = Attendance::with('course')
            ->where('student_id', $request->user()->id)
            ->where('course_id', $subjectId)
            ->orderByDesc('date')
            ->get()
            ->map(fn($a) => $this->formatRecord($a));

        $total   = $attendance->count();
        $present = $attendance->where('is_present', true)->count();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'records'    => $attendance,
                'summary'    => [
                    'total'      => $total,
                    'present'    => $present,
                    'absent'     => $total - $present,
                    'percentage' => $total > 0 ? round($present / $total * 100, 1) : 0,
                ],
            ],
        ]);
    }

    /**
     * GET /student-app/attendance/calendar/{month}
     * Monthly calendar view. Month format: YYYY-MM.
     */
    public function calendar(Request $request, string $month): JsonResponse
    {
        try {
            $parsedDate = Carbon::createFromFormat('Y-m', $month);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid month format. Use YYYY-MM.',
            ], 422);
        }

        $attendance = Attendance::with('course')
            ->where('student_id', $request->user()->id)
            ->whereYear('date', $parsedDate->year)
            ->whereMonth('date', $parsedDate->month)
            ->orderBy('date')
            ->get()
            ->map(fn($a) => $this->formatRecord($a));

        return response()->json([
            'status' => 'success',
            'data'   => $attendance,
        ]);
    }

    // ── Helper ─────────────────────────────────────────────────────────────

    private function formatRecord(Attendance $a): array
    {
        return [
            'date'         => optional($a->date)->format('Y-m-d'),
            'status'       => $a->status === 'on' ? 'present' : 'absent',
            'is_present'   => $a->status === 'on',
            'is_late'      => $a->status === 'on' && ($a->late_minutes ?? 0) > 0,
            'late_minutes' => $a->late_minutes ?? 0,
            'course'       => $a->course?->course_name,
            'course_id'    => $a->course_id,
        ];
    }
}
