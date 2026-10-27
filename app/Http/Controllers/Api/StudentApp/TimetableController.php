<?php

namespace App\Http\Controllers\Api\StudentApp;

use App\Http\Controllers\Controller;
use App\Models\Routine;
use App\Models\StudentStatus;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    /**
     * GET /student-app/timetable
     * Full weekly timetable for the student's class.
     */
    public function index(Request $request): JsonResponse
    {
        $classId = $this->currentClassId($request);

        $routines = $classId
            ? Routine::where('class_id', $classId)
                ->with(['course', 'schoolClass', 'section'])
                ->orderBy('weekday')
                ->orderBy('start')
                ->get()
            : collect();

        // Group by weekday number (1=Mon … 7=Sun)
        $weekdays = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];

        $grouped = $routines->groupBy('weekday')->map(function ($slots, $weekday) use ($weekdays) {
            return [
                'weekday'    => (int) $weekday,
                'day_name'   => $weekdays[$weekday] ?? 'Day ' . $weekday,
                'slots'      => $slots->map(fn($r) => $this->formatSlot($r)),
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data'   => $grouped,
        ]);
    }

    /**
     * GET /student-app/timetable/today
     * Today's schedule only.
     */
    public function today(Request $request): JsonResponse
    {
        $classId = $this->currentClassId($request);
        $todayWeekday = Carbon::now()->isoWeekday();

        $slots = $classId
            ? Routine::where('class_id', $classId)
                ->where('weekday', $todayWeekday)
                ->with('course')
                ->orderBy('start')
                ->get()
                ->map(fn($r) => $this->formatSlot($r))
            : collect();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'day'   => Carbon::now()->format('l'),
                'date'  => Carbon::now()->toDateString(),
                'slots' => $slots,
            ],
        ]);
    }

    /**
     * GET /student-app/timetable/day/{weekday}
     * Schedule for a specific weekday (1=Mon … 7=Sun).
     */
    public function byDay(Request $request, int $weekday): JsonResponse
    {
        if ($weekday < 1 || $weekday > 7) {
            return response()->json(['status' => 'error', 'message' => 'Invalid weekday. Use 1 (Monday) to 7 (Sunday).'], 422);
        }

        $classId = $this->currentClassId($request);

        $slots = $classId
            ? Routine::where('class_id', $classId)
                ->where('weekday', $weekday)
                ->with('course')
                ->orderBy('start')
                ->get()
                ->map(fn($r) => $this->formatSlot($r))
            : collect();

        $dayNames = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];

        return response()->json([
            'status' => 'success',
            'data'   => [
                'weekday'  => $weekday,
                'day_name' => $dayNames[$weekday],
                'slots'    => $slots,
            ],
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function currentClassId(Request $request): ?int
    {
        return StudentStatus::where('student_id', $request->user()->id)
            ->where('is_current', true)
            ->value('class_id');
    }

    private function formatSlot(Routine $r): array
    {
        return [
            'id'          => $r->id,
            'subject'     => $r->course?->course_name,
            'subject_code'=> $r->course?->course_code,
            'start'       => $r->start,
            'end'         => $r->end,
            'room'        => $r->room ?? null,
        ];
    }
}
