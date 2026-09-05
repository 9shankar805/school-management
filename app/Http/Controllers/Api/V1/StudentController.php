<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AttendanceResource;
use App\Http\Resources\Api\V1\StudentResource;
use App\Models\Attendance;
use App\Models\User;
use App\Traits\ScopesStudentData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    use ScopesStudentData;

    public function index(Request $request)
    {
        $this->authorize('view users');

        $query = User::role('student')
            ->with(['academic_info', 'parent_info'])
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name',  'like', "%{$request->search}%")
                  ->orWhere('email',       'like', "%{$request->search}%");
            }))
            ->when($request->class_id, fn($q) => $q->whereHas('academic_info',
                fn($aq) => $aq->where('class_id', $request->class_id)
            ));

        // Row-level security: students/parents only see their own record
        $this->applyStudentScope($query, $request, 'id');

        return StudentResource::collection($query->paginate($request->integer('per_page', 20)));
    }

    public function show(int $id, Request $request)
    {
        if ($denied = $this->denyIfNotOwned($request, $id)) {
            return $denied;
        }

        $student = User::role('student')
            ->with(['academic_info', 'parent_info', 'marks.exam', 'marks.course'])
            ->findOrFail($id);

        return new StudentResource($student);
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'message' => 'Student creation via API is not yet supported. Use the web portal.',
        ], 501);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'message' => 'Student update via API is not yet supported. Use the web portal.',
        ], 501);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete users');
        User::role('student')->findOrFail($id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Student deleted.']);
    }

    public function attendance(Request $request, int $id)
    {
        if ($denied = $this->denyIfNotOwned($request, $id)) {
            return $denied;
        }

        $records = Attendance::with(['course'])
            ->where('student_id', $id)
            ->when($request->from, fn($q) => $q->where('date', '>=', $request->from))
            ->when($request->to,   fn($q) => $q->where('date', '<=', $request->to))
            ->when($request->session_id, fn($q) => $q->where('session_id', $request->session_id))
            ->orderByDesc('date')
            ->get();

        return AttendanceResource::collection($records);
    }

    public function results(int $id, Request $request): JsonResponse
    {
        if ($denied = $this->denyIfNotOwned($request, $id)) {
            return $denied;
        }

        $marks = User::findOrFail($id)->marks()
            ->with(['exam', 'course'])
            ->when($request->session_id, fn($q) => $q->where('session_id', $request->session_id))
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $marks->map(fn($m) => [
                'exam'        => $m->exam?->exam_name,
                'course'      => $m->course?->course_name,
                'marks'       => (float) $m->marks,
                'session_id'  => $m->session_id,
            ]),
        ]);
    }
}
