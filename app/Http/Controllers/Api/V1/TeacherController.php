<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $teachers = User::role('teacher')
            ->with('assignedCourses')
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%");
            }))
            ->paginate($request->integer('per_page', 20));

        return response()->json(['status' => 'success', 'data' => $teachers]);
    }

    public function show(int $id): JsonResponse
    {
        $teacher = User::role('teacher')->findOrFail($id);

        return response()->json(['status' => 'success', 'data' => $teacher]);
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json(['status' => 'success', 'message' => 'Use web route for full teacher creation.'], 501);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return response()->json(['status' => 'success', 'message' => 'Use web route for full teacher update.'], 501);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete users');
        User::findOrFail($id)->delete();

        return response()->json(['status' => 'success', 'message' => 'Teacher deleted.']);
    }
}
