<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TeacherResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view teachers');

        $teachers = User::role('teacher')
            ->with(['assignedCourses.course', 'assignedCourses.section', 'assignedCourses.schoolClass',
                    'qualifications', 'departments'])
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name',  'like', "%{$request->search}%")
                  ->orWhere('email',       'like', "%{$request->search}%");
            }))
            ->when($request->department_id, fn($q) => $q->whereHas('departments',
                fn($dq) => $dq->where('departments.id', $request->department_id)
            ))
            ->paginate($request->integer('per_page', 20));

        return TeacherResource::collection($teachers);
    }

    public function show(int $id): TeacherResource
    {
        $this->authorize('view teachers');

        $teacher = User::role('teacher')
            ->with(['assignedCourses.course', 'assignedCourses.section', 'assignedCourses.schoolClass',
                    'qualifications', 'departments', 'contracts'])
            ->findOrFail($id);

        return new TeacherResource($teacher);
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'message' => 'Teacher creation via API is not yet supported. Use the web portal.',
        ], 501);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'message' => 'Teacher update via API is not yet supported. Use the web portal.',
        ], 501);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete users');
        User::role('teacher')->findOrFail($id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Teacher deleted.']);
    }
}
