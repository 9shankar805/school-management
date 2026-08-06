<?php

namespace App\Http\Controllers\Api\ParentsApp;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\StudentStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    /**
     * GET /api/exams
     */
    public function index(Request $request): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $classIds = StudentStatus::whereIn('student_id', $childIds)
            ->where('is_current', true)
            ->pluck('class_id');

        $exams = Exam::with(['course', 'semester'])
            ->whereIn('class_id', $classIds)
            ->orderBy('start_date', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $exams,
        ]);
    }

    /**
     * GET /api/exams/upcoming
     */
    public function upcoming(Request $request): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $classIds = StudentStatus::whereIn('student_id', $childIds)
            ->where('is_current', true)
            ->pluck('class_id');

        $exams = Exam::with(['course', 'semester'])
            ->whereIn('class_id', $classIds)
            ->where('start_date', '>=', now())
            ->orderBy('start_date', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $exams,
        ]);
    }

    /**
     * GET /api/exams/past
     */
    public function past(Request $request): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $classIds = StudentStatus::whereIn('student_id', $childIds)
            ->where('is_current', true)
            ->pluck('class_id');

        $exams = Exam::with(['course', 'semester'])
            ->whereIn('class_id', $classIds)
            ->where('end_date', '<', now())
            ->orderBy('start_date', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $exams,
        ]);
    }

    /**
     * GET /api/exams/child/{childId}
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

        $exams = Exam::with(['course', 'semester'])
            ->where('class_id', $status->class_id)
            ->orderBy('start_date', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $exams,
        ]);
    }

    /**
     * GET /api/exams/child/{childId}/schedule
     */
    public function scheduleByChild(Request $request, $childId): JsonResponse
    {
        $request->user()->children()->findOrFail($childId);

        $status = StudentStatus::where('student_id', $childId)
            ->where('is_current', true)
            ->first();

        if (!$status) {
            return response()->json(['status' => 'success', 'data' => []]);
        }

        // Get exams with schedules eager loaded
        $exams = Exam::with(['course', 'schedules.hall'])
            ->where('class_id', $status->class_id)
            ->where('start_date', '>=', now())
            ->orderBy('start_date', 'asc')
            ->get();

        // Flatten schedules for easier frontend rendering
        $schedules = $exams->flatMap(function($exam) {
            return $exam->schedules->map(function($schedule) use ($exam) {
                return [
                    'exam_id'    => $exam->id,
                    'exam_name'  => $exam->exam_name,
                    'course'     => $exam->course ? $exam->course->course_name : null,
                    'date'       => $schedule->exam_date->format('Y-m-d'),
                    'start_time' => $schedule->start_time,
                    'end_time'   => $schedule->end_time,
                    'duration'   => $schedule->duration_label,
                    'hall'       => $schedule->hall ? $schedule->hall->name : null,
                    'is_today'   => $schedule->is_today,
                ];
            });
        })->sortBy('date')->values();

        return response()->json([
            'status' => 'success',
            'data'   => $schedules,
        ]);
    }

    /**
     * GET /api/exams/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $classIds = StudentStatus::whereIn('student_id', $childIds)
            ->where('is_current', true)
            ->pluck('class_id');

        $exam = Exam::with(['course', 'semester', 'schedules.hall'])
            ->whereIn('class_id', $classIds)
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => collect($exam)->merge([
                'status_label' => $exam->status_label,
                'status_badge' => $exam->status_badge,
            ])
        ]);
    }
}
