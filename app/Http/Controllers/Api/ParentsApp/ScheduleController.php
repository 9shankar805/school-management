<?php

namespace App\Http\Controllers\Api\ParentsApp;

use App\Http\Controllers\Controller;
use App\Models\Routine;
use App\Models\StudentStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * GET /api/schedule
     */
    public function index(Request $request): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $classIds = StudentStatus::whereIn('student_id', $childIds)
            ->where('is_current', true)
            ->pluck('class_id');

        $routines = Routine::with(['course', 'teacher', 'schoolClass'])
            ->whereIn('class_id', $classIds)
            ->orderBy('weekday')
            ->orderBy('start')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $routines,
        ]);
    }

    /**
     * GET /api/schedule/week
     */
    public function week(Request $request): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $classIds = StudentStatus::whereIn('student_id', $childIds)
            ->where('is_current', true)
            ->pluck('class_id');

        // Group routines by weekday (e.g. 1 = Monday, 7 = Sunday)
        $routines = Routine::with(['course', 'teacher'])
            ->whereIn('class_id', $classIds)
            ->orderBy('weekday')
            ->orderBy('start')
            ->get()
            ->groupBy('weekday');

        return response()->json([
            'status' => 'success',
            'data'   => $routines,
        ]);
    }

    /**
     * GET /api/schedule/child/{childId}
     */
    public function byChild(Request $request, $childId): JsonResponse
    {
        $request->user()->children()->findOrFail($childId);

        $status = StudentStatus::where('student_id', $childId)
            ->where('is_current', true)
            ->first();

        if (!$status) {
            return response()->json(['status' => 'success', 'data' => []]);
        }

        $routines = Routine::with(['course', 'teacher'])
            ->where('class_id', $status->class_id)
            ->orderBy('weekday')
            ->orderBy('start')
            ->get()
            ->groupBy('weekday');

        return response()->json([
            'status' => 'success',
            'data'   => $routines,
        ]);
    }

    /**
     * GET /api/schedule/day/{day}
     */
    public function byDay(Request $request, $day): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $classIds = StudentStatus::whereIn('student_id', $childIds)
            ->where('is_current', true)
            ->pluck('class_id');

        $routines = Routine::with(['course', 'teacher'])
            ->whereIn('class_id', $classIds)
            ->where('weekday', $day)
            ->orderBy('start')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $routines,
        ]);
    }

    /**
     * GET /api/schedule/subject/{subjectId}
     */
    public function bySubject(Request $request, $subjectId): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $classIds = StudentStatus::whereIn('student_id', $childIds)
            ->where('is_current', true)
            ->pluck('class_id');

        $routines = Routine::with(['course', 'teacher'])
            ->whereIn('class_id', $classIds)
            ->where('course_id', $subjectId)
            ->orderBy('weekday')
            ->orderBy('start')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $routines,
        ]);
    }
}
