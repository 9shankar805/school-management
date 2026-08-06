<?php

namespace App\Http\Controllers\Api\TeacherApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    /**
     * GET /api/teacher-app/exams
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status'); // upcoming or past

        $exams = [
            ['id' => 1, 'title' => 'Midterm Math', 'status' => 'upcoming', 'date' => '2026-08-15', 'class_id' => 1],
            ['id' => 2, 'title' => 'Final Science', 'status' => 'past', 'date' => '2026-06-10', 'class_id' => 2],
        ];

        if ($status) {
            $exams = array_filter($exams, function($e) use ($status) {
                return $e['status'] === $status;
            });
            $exams = array_values($exams);
        }

        return response()->json(['exams' => $exams]);
    }

    /**
     * POST /api/teacher-app/exams
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'duration_minutes' => 'required|integer',
            'class_id' => 'required|integer',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Exam scheduled successfully.',
            'exam' => array_merge(['id' => rand(10, 100), 'status' => 'upcoming'], $validated)
        ]);
    }

    /**
     * GET /api/teacher-app/exams/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        return response()->json([
            'exam' => [
                'id' => $id,
                'title' => 'Midterm Math',
                'date' => '2026-08-15',
                'duration_minutes' => 120,
                'class_id' => 1,
                'status' => 'upcoming'
            ]
        ]);
    }

    /**
     * PUT /api/teacher-app/exams/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'date' => 'sometimes|date',
            'duration_minutes' => 'sometimes|integer',
            'class_id' => 'sometimes|integer',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Exam updated successfully.'
        ]);
    }

    /**
     * DELETE /api/teacher-app/exams/{id}
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Exam deleted successfully.'
        ]);
    }

    /**
     * GET /api/teacher-app/exams/{id}/results
     */
    public function results(Request $request, $id): JsonResponse
    {
        return response()->json([
            'exam_id' => $id,
            'results' => [
                ['student_id' => 1, 'name' => 'John Doe', 'score' => 88],
                ['student_id' => 2, 'name' => 'Jane Smith', 'score' => 92],
            ]
        ]);
    }

    /**
     * PUT /api/teacher-app/exams/{id}/marks
     * Used for bulk mark entry for a specific exam.
     */
    public function submitMarks(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'marks' => 'required|array',
            'marks.*.student_id' => 'required|integer',
            'marks.*.score' => 'required|numeric|min:0|max:100',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Exam marks entered successfully.'
        ]);
    }
}
