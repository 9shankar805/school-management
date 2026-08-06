<?php

namespace App\Http\Controllers\Api\ParentsApp;

use App\Http\Controllers\Controller;
use App\Models\AssignedTeacher;
use App\Models\StudentStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    /**
     * GET /api/teachers
     */
    public function index(Request $request): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $classIds = StudentStatus::whereIn('student_id', $childIds)
            ->where('is_current', true)
            ->pluck('class_id');

        $assignedTeachers = AssignedTeacher::with(['teacher', 'course'])
            ->whereIn('class_id', $classIds)
            ->get()
            ->groupBy('teacher_id')
            ->map(function ($assignments) {
                $teacher = $assignments->first()->teacher;
                return collect($teacher)->merge([
                    'subjects_taught' => $assignments->pluck('course.course_name')->unique()->values()
                ]);
            })
            ->values();

        return response()->json([
            'status' => 'success',
            'data'   => $assignedTeachers,
        ]);
    }

    /**
     * GET /api/teachers/available
     */
    public function available(Request $request): JsonResponse
    {
        // Mock available teachers for meetings
        $childIds = $request->user()->children()->pluck('users.id');
        
        $classIds = StudentStatus::whereIn('student_id', $childIds)
            ->where('is_current', true)
            ->pluck('class_id');

        $assignedTeachers = AssignedTeacher::with(['teacher', 'course'])
            ->whereIn('class_id', $classIds)
            ->get()
            ->groupBy('teacher_id')
            ->map(function ($assignments) {
                $teacher = $assignments->first()->teacher;
                return [
                    'id'              => $teacher->id,
                    'name'            => $teacher->first_name . ' ' . $teacher->last_name,
                    'photo'           => $teacher->photo,
                    'subjects_taught' => $assignments->pluck('course.course_name')->unique()->values(),
                    'next_available'  => now()->addDays(rand(1, 5))->format('Y-m-d H:i:s'),
                ];
            })
            ->values();

        return response()->json([
            'status' => 'success',
            'data'   => $assignedTeachers,
        ]);
    }

    /**
     * GET /api/teachers/search
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->query('q', '');
        
        $childIds = $request->user()->children()->pluck('users.id');
        
        $classIds = StudentStatus::whereIn('student_id', $childIds)
            ->where('is_current', true)
            ->pluck('class_id');

        $assignedTeachers = AssignedTeacher::with(['teacher', 'course'])
            ->whereIn('class_id', $classIds)
            ->whereHas('teacher', function($q) use ($query) {
                $q->where('first_name', 'LIKE', "%{$query}%")
                  ->orWhere('last_name', 'LIKE', "%{$query}%");
            })
            ->get()
            ->groupBy('teacher_id')
            ->map(function ($assignments) {
                $teacher = $assignments->first()->teacher;
                return collect($teacher)->merge([
                    'subjects_taught' => $assignments->pluck('course.course_name')->unique()->values()
                ]);
            })
            ->values();

        return response()->json([
            'status' => 'success',
            'data'   => $assignedTeachers,
        ]);
    }

    /**
     * GET /api/teachers/subject/{subjectId}
     */
    public function bySubject(Request $request, $subjectId): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $classIds = StudentStatus::whereIn('student_id', $childIds)
            ->where('is_current', true)
            ->pluck('class_id');

        $assignedTeachers = AssignedTeacher::with(['teacher', 'course'])
            ->whereIn('class_id', $classIds)
            ->where('course_id', $subjectId)
            ->get()
            ->groupBy('teacher_id')
            ->map(function ($assignments) {
                $teacher = $assignments->first()->teacher;
                return collect($teacher)->merge([
                    'subjects_taught' => $assignments->pluck('course.course_name')->unique()->values()
                ]);
            })
            ->values();

        return response()->json([
            'status' => 'success',
            'data'   => $assignedTeachers,
        ]);
    }

    /**
     * GET /api/teachers/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $classIds = StudentStatus::whereIn('student_id', $childIds)
            ->where('is_current', true)
            ->pluck('class_id');

        $assignments = AssignedTeacher::with(['teacher', 'course'])
            ->whereIn('class_id', $classIds)
            ->where('teacher_id', $id)
            ->get();

        if ($assignments->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Teacher not found or not assigned to your child.'], 404);
        }

        $teacher = $assignments->first()->teacher;

        return response()->json([
            'status' => 'success',
            'data'   => collect($teacher)->merge([
                'subjects_taught' => $assignments->pluck('course.course_name')->unique()->values()
            ]),
        ]);
    }
}
