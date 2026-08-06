<?php

namespace App\Http\Controllers\Api\TeacherApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LessonPlanController extends Controller
{
    /**
     * GET /api/teacher-app/lesson-plans
     */
    public function index(Request $request): JsonResponse
    {
        $plans = [
            ['id' => 1, 'date' => '2026-08-10', 'subject' => 'Math', 'title' => 'Intro to Algebra'],
            ['id' => 2, 'date' => '2026-08-11', 'subject' => 'Science', 'title' => 'Cell Structure'],
        ];

        return response()->json(['lesson_plans' => $plans]);
    }

    /**
     * GET /api/teacher-app/lesson-plans/{date}
     */
    public function byDate(Request $request, $date): JsonResponse
    {
        // Dummy fetch by date
        return response()->json([
            'date' => $date,
            'lesson_plans' => [
                ['id' => 1, 'subject' => 'Math', 'title' => 'Intro to Algebra', 'content' => 'Review basic variables.'],
            ]
        ]);
    }

    /**
     * POST /api/teacher-app/lesson-plans
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'subject' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Lesson plan created successfully.',
            'lesson_plan' => array_merge(['id' => rand(10, 100)], $validated)
        ]);
    }

    /**
     * GET /api/teacher-app/lesson-plans/detail/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        return response()->json([
            'lesson_plan' => [
                'id' => $id,
                'date' => '2026-08-10',
                'subject' => 'Math',
                'title' => 'Intro to Algebra',
                'content' => 'Review basic variables and equations.',
            ]
        ]);
    }

    /**
     * PUT /api/teacher-app/lesson-plans/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'sometimes|date',
            'subject' => 'sometimes|string|max:100',
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Lesson plan updated successfully.'
        ]);
    }

    /**
     * DELETE /api/teacher-app/lesson-plans/{id}
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Lesson plan deleted successfully.'
        ]);
    }
}
