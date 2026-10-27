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
        $teacher = $request->user();
        $sessionId = \App\Models\SchoolSession::current()?->id ?? \App\Models\SchoolSession::latest('id')->value('id') ?? 1;

        $assignedClassIds = \App\Models\AssignedTeacher::where('teacher_id', $teacher->id)
            ->where('session_id', $sessionId)
            ->pluck('class_id')
            ->unique();

        $classes = SchoolClass::whereIn('id', $assignedClassIds)
            ->with(['sections' => function($q) use ($teacher, $sessionId) {
                $q->whereHas('assignedTeachers', fn($aq) => $aq->where('teacher_id', $teacher->id)->where('session_id', $sessionId));
            }])
            ->get();

        if ($classes->isEmpty()) {
            $classes = SchoolClass::with('sections')->get();
        }

        return response()->json([
            'status'  => 'success',
            'classes' => $classes
        ]);
    }

    /**
     * GET /api/teacher/students
     */
    public function students(Request $request): JsonResponse
    {
        $teacher = $request->user();
        $sessionId = \App\Models\SchoolSession::current()?->id ?? \App\Models\SchoolSession::latest('id')->value('id') ?? 1;

        $assignedClassIds = \App\Models\AssignedTeacher::where('teacher_id', $teacher->id)
            ->where('session_id', $sessionId)
            ->pluck('class_id')
            ->unique();

        $students = \App\Models\Promotion::whereIn('class_id', $assignedClassIds)
            ->where('session_id', $sessionId)
            ->with(['student', 'schoolClass', 'section'])
            ->get()
            ->map(function($p) {
                return [
                    'id'         => $p->student?->id ?? $p->id,
                    'name'       => trim(($p->student?->first_name ?? '') . ' ' . ($p->student?->last_name ?? '')),
                    'first_name' => $p->student?->first_name ?? '',
                    'last_name'  => $p->student?->last_name ?? '',
                    'email'      => $p->student?->email ?? '',
                    'photo'      => $p->student?->photo,
                    'roll_no'    => $p->roll_no ?? '01',
                    'class_name' => $p->schoolClass?->class_name ?? '',
                    'section_name'=> $p->section?->section_name ?? '',
                    'grade'      => ($p->schoolClass?->class_name ?? '') . ' - ' . ($p->section?->section_name ?? ''),
                    'gpa'        => 3.6,
                    'attendance' => 0.95,
                    'rank'       => 1,
                ];
            });

        return response()->json([
            'status'   => 'success',
            'students' => $students
        ]);
    }

    /**
     * GET /api/config/dropdowns
     */
    public function dropdowns(Request $request): JsonResponse
    {
        $teacher = $request->user();
        $sessionId = \App\Models\SchoolSession::current()?->id ?? \App\Models\SchoolSession::latest('id')->value('id') ?? 1;

        $classes = SchoolClass::with('sections')->get();
        
        $subjects = \App\Models\AssignedTeacher::where('teacher_id', $teacher->id)
            ->where('session_id', $sessionId)
            ->with('course')
            ->get()
            ->pluck('course')
            ->filter()
            ->unique('id')
            ->values()
            ->map(fn($c) => ['id' => $c->id, 'name' => $c->course_name]);

        return response()->json([
            'status'   => 'success',
            'classes'  => $classes,
            'subjects' => $subjects->isNotEmpty() ? $subjects : \App\Models\Course::limit(10)->get(['id', 'course_name as name']),
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
