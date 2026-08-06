<?php

namespace App\Http\Controllers\Api\ParentsApp;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * GET /api/attendance
     */
    public function index(Request $request): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $attendance = Attendance::with(['course', 'student'])
            ->whereIn('student_id', $childIds)
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $attendance,
        ]);
    }

    /**
     * GET /api/attendance/child/{childId}
     */
    public function byChild(Request $request, $childId): JsonResponse
    {
        // Verify child belongs to parent
        $request->user()->children()->findOrFail($childId);

        $attendance = Attendance::with(['course'])
            ->where('student_id', $childId)
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $attendance,
        ]);
    }

    /**
     * GET /api/attendance/subject/{subjectId}
     */
    public function bySubject(Request $request, $subjectId): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $attendance = Attendance::with(['student'])
            ->whereIn('student_id', $childIds)
            ->where('course_id', $subjectId)
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $attendance,
        ]);
    }

    /**
     * GET /api/attendance/child/{childId}/summary
     */
    public function summaryByChild(Request $request, $childId): JsonResponse
    {
        $request->user()->children()->findOrFail($childId);
        
        // We assume the active session is passed or we default to the latest session.
        // For simplicity, we just calculate raw aggregates across all records if sessionId is missing.
        $records = Attendance::where('student_id', $childId)->get();
        
        $total   = $records->count();
        $present = $records->where('status', 'on')->count();
        $absent  = $total - $present;
        $late    = $records->where('status', 'on')->where('late_minutes', '>', 0)->count();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'child_id'   => $childId,
                'total'      => $total,
                'present'    => $present,
                'absent'     => $absent,
                'late'       => $late,
                'percentage' => $total > 0 ? round($present / $total * 100, 1) : 0,
            ]
        ]);
    }

    /**
     * GET /api/attendance/child/{childId}/calendar/{month}
     * Month format expected: YYYY-MM
     */
    public function calendarByChild(Request $request, $childId, $month): JsonResponse
    {
        $request->user()->children()->findOrFail($childId);
        
        try {
            $parsedDate = Carbon::createFromFormat('Y-m', $month);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Invalid month format. Use YYYY-MM'], 400);
        }

        $attendance = Attendance::with('course')
            ->where('student_id', $childId)
            ->inMonth($parsedDate->year, $parsedDate->month)
            ->get();
            
        // Map to calendar friendly format
        $calendar = $attendance->map(function($record) {
            return [
                'date'         => $record->date->format('Y-m-d'),
                'status'       => $record->status_label,
                'is_present'   => $record->is_present,
                'is_late'      => $record->is_late,
                'late_minutes' => $record->late_minutes,
                'course'       => $record->course ? $record->course->course_name : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $calendar,
        ]);
    }

    /**
     * GET /api/attendance/child/{childId}/subject/{subjectId}
     */
    public function subjectByChild(Request $request, $childId, $subjectId): JsonResponse
    {
        $request->user()->children()->findOrFail($childId);

        $attendance = Attendance::where('student_id', $childId)
            ->where('course_id', $subjectId)
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $attendance,
        ]);
    }
}
