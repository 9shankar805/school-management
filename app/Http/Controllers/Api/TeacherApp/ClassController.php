<?php

namespace App\Http\Controllers\Api\TeacherApp;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    /**
     * GET /api/teacher-app/classes/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        $class = SchoolClass::with('sections')->find($id);

        if (!$class) {
            return response()->json(['error' => 'Class not found'], 404);
        }

        return response()->json([
            'class' => $class
        ]);
    }

    /**
     * GET /api/teacher-app/classes/{id}/top-students
     */
    public function topStudents(Request $request, $id): JsonResponse
    {
        // Dummy data for now. In reality, join with marks/results.
        $topStudents = [
            ['id' => 1, 'name' => 'Alice Smith', 'gpa' => 4.0, 'photo' => null],
            ['id' => 2, 'name' => 'Bob Johnson', 'gpa' => 3.9, 'photo' => null],
            ['id' => 3, 'name' => 'Charlie Brown', 'gpa' => 3.8, 'photo' => null],
        ];

        return response()->json([
            'top_students' => $topStudents
        ]);
    }

    /**
     * GET /api/teacher-app/classes/summary
     */
    public function summary(Request $request): JsonResponse
    {
        // Dummy overall summary
        return response()->json([
            'summary' => [
                'total_classes'  => 5,
                'total_students' => 120,
                'average_gpa'    => 3.2,
                'average_attendance' => '92%'
            ]
        ]);
    }
}
