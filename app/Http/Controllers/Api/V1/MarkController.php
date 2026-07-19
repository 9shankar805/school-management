<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Mark;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarkController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('view marks');

        $marks = Mark::with('exam', 'course')
            ->when($request->student_id, fn($q) => $q->where('student_id', $request->student_id))
            ->when($request->exam_id, fn($q) => $q->where('exam_id', $request->exam_id))
            ->paginate($request->integer('per_page', 50));

        return response()->json(['status' => 'success', 'data' => $marks]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('save marks');

        $request->validate([
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'exam_id'    => ['required', 'integer', 'exists:exams,id'],
            'course_id'  => ['required', 'integer', 'exists:courses,id'],
            'mark'       => ['required', 'numeric', 'min:0'],
        ]);

        $mark = Mark::updateOrCreate(
            $request->only('student_id', 'exam_id', 'course_id'),
            ['mark' => $request->mark]
        );

        return response()->json(['status' => 'success', 'data' => $mark], 201);
    }

    public function results(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => ['required', 'integer'],
        ]);

        $marks = Mark::where('student_id', $request->student_id)
            ->with('exam', 'course', 'gradeRule')
            ->get();

        $total       = $marks->sum('mark');
        $maxPossible = $marks->count() * 100; // placeholder
        $percentage  = $maxPossible > 0 ? round(($total / $maxPossible) * 100, 2) : 0;

        return response()->json([
            'status' => 'success',
            'data'   => [
                'marks'      => $marks,
                'total'      => $total,
                'percentage' => $percentage,
            ],
        ]);
    }
}
