<?php

namespace App\Http\Controllers\Api\TeacherApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeatingChartController extends Controller
{
    /**
     * GET /api/teacher-app/seating/{classId}
     */
    public function show(Request $request, $classId): JsonResponse
    {
        // Dummy seating chart data
        return response()->json([
            'class_id' => $classId,
            'seating_chart' => [
                'rows' => 5,
                'columns' => 6,
                'seats' => [
                    ['row' => 1, 'col' => 1, 'student_id' => 1, 'name' => 'Alice Smith'],
                    ['row' => 1, 'col' => 2, 'student_id' => 2, 'name' => 'Bob Johnson'],
                    // ... other seats
                ]
            ]
        ]);
    }

    /**
     * POST /api/teacher-app/seating/{classId}
     */
    public function store(Request $request, $classId): JsonResponse
    {
        $validated = $request->validate([
            'rows' => 'required|integer',
            'columns' => 'required|integer',
            'seats' => 'required|array',
            'seats.*.row' => 'required|integer',
            'seats.*.col' => 'required|integer',
            'seats.*.student_id' => 'nullable|integer',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Seating chart saved successfully.'
        ]);
    }

    /**
     * POST /api/teacher-app/seating/{classId}/randomize
     */
    public function randomize(Request $request, $classId): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Seating chart randomized successfully.',
            // Return new randomized layout here in reality
        ]);
    }

    /**
     * GET /api/teacher-app/seating/{classId}/template
     */
    public function template(Request $request, $classId): JsonResponse
    {
        return response()->json([
            'template' => [
                'rows' => 5,
                'columns' => 6,
                'desk_layout' => 'traditional' // e.g., groups, u-shape, traditional
            ]
        ]);
    }
}
