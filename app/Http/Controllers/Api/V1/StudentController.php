<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('view users');

        $students = User::role('student')
            ->with('academic_info', 'parent_info')
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            }))
            ->paginate($request->integer('per_page', 20));

        return response()->json(['status' => 'success', 'data' => $students]);
    }

    public function show(int $id): JsonResponse
    {
        $student = User::role('student')
            ->with('academic_info', 'parent_info', 'marks')
            ->findOrFail($id);

        return response()->json(['status' => 'success', 'data' => $student]);
    }

    public function store(Request $request): JsonResponse
    {
        // Delegates to UserController logic — stub for API layer
        return response()->json(['status' => 'success', 'message' => 'Use web route for full student creation.'], 501);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return response()->json(['status' => 'success', 'message' => 'Use web route for full student update.'], 501);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete users');
        User::findOrFail($id)->delete();

        return response()->json(['status' => 'success', 'message' => 'Student deleted.']);
    }

    public function attendance(Request $request, int $id): JsonResponse
    {
        $attendance = Attendance::where('student_id', $id)
            ->when($request->from, fn($q) => $q->where('date', '>=', $request->from))
            ->when($request->to, fn($q) => $q->where('date', '<=', $request->to))
            ->orderBy('date', 'desc')
            ->get();

        return response()->json(['status' => 'success', 'data' => $attendance]);
    }

    public function results(int $id): JsonResponse
    {
        $marks = User::findOrFail($id)->marks()->with('exam', 'course')->get();

        return response()->json(['status' => 'success', 'data' => $marks]);
    }
}
