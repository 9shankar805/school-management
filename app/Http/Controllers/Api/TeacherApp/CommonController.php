<?php

namespace App\Http\Controllers\Api\TeacherApp;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    /**
     * GET /api/teacher/classes
     */
    public function classes(Request $request): JsonResponse
    {
        // TODO: In a real scenario, filter by classes assigned to this teacher
        $classes = SchoolClass::with('sections')->get();

        return response()->json([
            'classes' => $classes
        ]);
    }

    /**
     * GET /api/teacher/students
     */
    public function students(Request $request): JsonResponse
    {
        // TODO: Filter students by those taught by this teacher
        $students = User::role('student')
            ->select('id', 'first_name', 'last_name', 'email', 'photo')
            ->get();

        return response()->json([
            'students' => $students
        ]);
    }

    /**
     * GET /api/config/dropdowns
     */
    public function dropdowns(Request $request): JsonResponse
    {
        $classes = SchoolClass::with('sections')->get();
        // Placeholder for subjects
        $subjects = [
            ['id' => 1, 'name' => 'Mathematics'],
            ['id' => 2, 'name' => 'Science'],
            ['id' => 3, 'name' => 'English'],
        ];

        return response()->json([
            'classes'  => $classes,
            'subjects' => $subjects,
        ]);
    }

    /**
     * POST /api/upload
     */
    public function upload(Request $request): JsonResponse
    {
        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'No file provided.'], 400);
        }

        $file = $request->file('file');
        $path = $file->store('teacher_uploads', 'public');

        return response()->json([
            'status' => 'success',
            'url'    => asset('storage/' . $path),
            'path'   => $path
        ]);
    }

    /**
     * GET /api/search
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('query');
        if (!$query) {
            return response()->json(['results' => []]);
        }

        // Basic student search implementation
        $students = User::role('student')
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->select('id', 'first_name', 'last_name', 'email', 'photo')
            ->limit(10)
            ->get();

        return response()->json([
            'results' => [
                'students' => $students,
                // Add more searchable entities here (classes, resources, etc.)
            ]
        ]);
    }
}
