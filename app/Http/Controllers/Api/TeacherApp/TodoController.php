<?php

namespace App\Http\Controllers\Api\TeacherApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    /**
     * GET /api/teacher-app/todos
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status'); // pending or completed

        // Dummy data for now. Can be replaced with actual DB model e.g., TeacherTodo
        $todos = [
            ['id' => 1, 'title' => 'Grade Math exams', 'status' => 'pending', 'due_date' => '2026-08-05'],
            ['id' => 2, 'title' => 'Prepare Science lab', 'status' => 'pending', 'due_date' => '2026-08-06'],
            ['id' => 3, 'title' => 'Submit weekly report', 'status' => 'completed', 'due_date' => '2026-08-01'],
        ];

        if ($status) {
            $todos = array_filter($todos, function($todo) use ($status) {
                return $todo['status'] === $status;
            });
            $todos = array_values($todos); // reindex
        }

        return response()->json([
            'todos' => $todos
        ]);
    }

    /**
     * POST /api/teacher-app/todos
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'due_date' => 'nullable|date',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Task created successfully.',
            'todo' => [
                'id' => rand(10, 100),
                'title' => $validated['title'],
                'status' => 'pending',
                'due_date' => $validated['due_date'] ?? null,
            ]
        ]);
    }

    /**
     * PUT /api/teacher-app/todos/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'due_date' => 'sometimes|date|nullable',
            'status' => 'sometimes|in:pending,completed',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Task updated successfully.'
        ]);
    }

    /**
     * DELETE /api/teacher-app/todos/{id}
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Task deleted successfully.'
        ]);
    }

    /**
     * PUT /api/teacher-app/todos/{id}/toggle
     */
    public function toggle(Request $request, $id): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Task completion toggled successfully.',
            'new_status' => 'completed' // dynamic in reality
        ]);
    }
}
