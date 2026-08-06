<?php

namespace App\Http\Controllers\Api\TeacherApp;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * GET /api/teacher-app/students
     * Optional ?class={classId} filter
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::role('student')
                     ->select('id', 'first_name', 'last_name', 'email', 'photo');

        if ($request->has('class')) {
            // Assuming academic_info relation links student to a class
            $query->whereHas('academic_info', function($q) use ($request) {
                $q->where('class_id', $request->class);
            });
        }

        return response()->json([
            'students' => $query->get()
        ]);
    }

    /**
     * GET /api/teacher-app/students/search
     * Search students via ?query={query}
     */
    public function search(Request $request): JsonResponse
    {
        $term = $request->query('query');

        if (!$term) {
            return response()->json(['students' => []]);
        }

        $students = User::role('student')
            ->where(function ($q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                  ->orWhere('last_name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%");
            })
            ->select('id', 'first_name', 'last_name', 'email', 'photo')
            ->limit(20)
            ->get();

        return response()->json([
            'students' => $students
        ]);
    }

    /**
     * GET /api/teacher-app/students/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        $student = User::role('student')
            ->with(['academic_info', 'parent_info'])
            ->find($id);

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        return response()->json([
            'student' => $student
        ]);
    }

    /**
     * GET /api/teacher-app/students/{id}/performance
     */
    public function performance(Request $request, $id): JsonResponse
    {
        // Dummy performance data
        return response()->json([
            'performance' => [
                'gpa' => 3.8,
                'attendance_rate' => '95%',
                'behavior_points' => 120,
                'recent_grades' => [
                    ['subject' => 'Math', 'grade' => 'A'],
                    ['subject' => 'Science', 'grade' => 'B+'],
                ]
            ]
        ]);
    }

    /**
     * GET /api/teacher-app/students/{id}/assignments
     */
    public function assignments(Request $request, $id): JsonResponse
    {
        // Dummy assignment data
        return response()->json([
            'assignments' => [
                [
                    'id' => 1,
                    'title' => 'Algebra Worksheet 1',
                    'status' => 'completed',
                    'grade' => 'A',
                    'submitted_at' => '2026-08-01'
                ],
                [
                    'id' => 2,
                    'title' => 'Science Lab Report',
                    'status' => 'pending',
                    'grade' => null,
                    'submitted_at' => null
                ]
            ]
        ]);
    }
}
