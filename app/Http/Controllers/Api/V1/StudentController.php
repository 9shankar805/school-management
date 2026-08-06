<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Traits\ScopesStudentData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    use ScopesStudentData;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('view users');

        $query = User::role('student')
            ->with('academic_info', 'parent_info')
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            }));

        // Row-level security: students/parents only see their own records
        $this->applyStudentScope($query, $request, 'id');

        return response()->json(['status' => 'success', 'data' => $query->paginate($request->integer('per_page', 20))]);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        // Row-level: deny student/parent trying to view someone else's record
        if ($denied = $this->denyIfNotOwned($request, $id)) {
            return $denied;
        }

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
        // Row-level: students/parents may only pull their own attendance
        if ($denied = $this->denyIfNotOwned($request, $id)) {
            return $denied;
        }

        $attendance = Attendance::where('student_id', $id)
            ->when($request->from, fn($q) => $q->where('date', '>=', $request->from))
            ->when($request->to, fn($q) => $q->where('date', '<=', $request->to))
            ->orderBy('date', 'desc')
            ->get();

        return response()->json(['status' => 'success', 'data' => $attendance]);
    }

    public function results(int $id, Request $request): JsonResponse
    {
        // Row-level: students/parents may only pull their own marks
        if ($denied = $this->denyIfNotOwned($request, $id)) {
            return $denied;
        }

        $marks = User::findOrFail($id)->marks()->with('exam', 'course')->get();

        return response()->json(['status' => 'success', 'data' => $marks]);
    }
}
