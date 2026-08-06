<?php

namespace App\Http\Controllers\Api\TeacherApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    /**
     * GET /api/teacher-app/assignments
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status'); // pending or completed

        // Dummy data for assignments
        $assignments = [
            ['id' => 1, 'title' => 'Algebra Chapter 1', 'status' => 'pending', 'due_date' => '2026-08-10', 'class_id' => 1],
            ['id' => 2, 'title' => 'Photosynthesis Essay', 'status' => 'completed', 'due_date' => '2026-07-20', 'class_id' => 2],
        ];

        if ($status) {
            $assignments = array_filter($assignments, function($a) use ($status) {
                return $a['status'] === $status;
            });
            $assignments = array_values($assignments);
        }

        return response()->json(['assignments' => $assignments]);
    }

    /**
     * POST /api/teacher-app/assignments
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_date' => 'required|date',
            'class_id' => 'required|integer',
            'total_marks' => 'required|integer',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Assignment created successfully.',
            'assignment' => array_merge(['id' => rand(10, 100), 'status' => 'pending'], $validated)
        ]);
    }

    /**
     * GET /api/teacher-app/assignments/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        return response()->json([
            'assignment' => [
                'id' => $id,
                'title' => 'Algebra Chapter 1',
                'description' => 'Complete odd problems on page 42.',
                'due_date' => '2026-08-10',
                'class_id' => 1,
                'total_marks' => 100,
                'status' => 'pending'
            ]
        ]);
    }

    /**
     * PUT /api/teacher-app/assignments/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'due_date' => 'sometimes|date',
            'class_id' => 'sometimes|integer',
            'total_marks' => 'sometimes|integer',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Assignment updated successfully.'
        ]);
    }

    /**
     * DELETE /api/teacher-app/assignments/{id}
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Assignment deleted successfully.'
        ]);
    }

    /**
     * GET /api/teacher-app/assignments/{id}/submissions
     */
    public function submissions(Request $request, $id): JsonResponse
    {
        // Dummy submissions data
        $submissions = [
            ['student_id' => 1, 'student_name' => 'John Doe', 'submitted_at' => '2026-08-09 10:00:00', 'grade' => null, 'file_url' => 'https://example.com/file1.pdf'],
            ['student_id' => 2, 'student_name' => 'Jane Smith', 'submitted_at' => '2026-08-09 11:30:00', 'grade' => 95, 'file_url' => 'https://example.com/file2.pdf'],
        ];

        return response()->json(['submissions' => $submissions]);
    }
}
