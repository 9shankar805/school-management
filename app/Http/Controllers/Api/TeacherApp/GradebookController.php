<?php

namespace App\Http\Controllers\Api\TeacherApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GradebookController extends Controller
{
    /**
     * GET /api/teacher-app/gradebook/{classId}
     */
    public function show(Request $request, $classId): JsonResponse
    {
        // Dummy gradebook data
        return response()->json([
            'class_id' => $classId,
            'gradebook' => [
                'assignments' => [
                    ['id' => 1, 'title' => 'Algebra 1'],
                    ['id' => 2, 'title' => 'Algebra 2'],
                ],
                'students' => [
                    [
                        'student_id' => 1,
                        'name' => 'John Doe',
                        'grades' => [
                            ['assignment_id' => 1, 'grade' => 95],
                            ['assignment_id' => 2, 'grade' => 88],
                        ]
                    ]
                ]
            ]
        ]);
    }

    /**
     * GET /api/teacher-app/gradebook/{classId}/distribution
     */
    public function distribution(Request $request, $classId): JsonResponse
    {
        return response()->json([
            'class_id' => $classId,
            'distribution' => [
                'A' => 15,
                'B' => 10,
                'C' => 3,
                'D' => 1,
                'F' => 0
            ]
        ]);
    }

    /**
     * GET /api/teacher-app/gradebook/{classId}/export
     */
    public function export(Request $request, $classId): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'download_url' => url('/storage/exports/gradebook_' . $classId . '.csv')
        ]);
    }

    /**
     * PUT /api/teacher-app/gradebook/{studentId}/{assignmentId}
     */
    public function updateGrade(Request $request, $studentId, $assignmentId): JsonResponse
    {
        $validated = $request->validate([
            'grade' => 'required|numeric|min:0|max:100',
            'comments' => 'nullable|string'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Grade updated successfully.'
        ]);
    }

    /**
     * GET /api/teacher-app/gradebook/{studentId}/history
     */
    public function history(Request $request, $studentId): JsonResponse
    {
        return response()->json([
            'student_id' => $studentId,
            'history' => [
                ['term' => 'Term 1', 'gpa' => 3.5],
                ['term' => 'Term 2', 'gpa' => 3.8],
            ]
        ]);
    }
}
