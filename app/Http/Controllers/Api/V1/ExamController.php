<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $exams = Exam::with('examRule')
            ->paginate($request->integer('per_page', 20));

        return response()->json(['status' => 'success', 'data' => $exams]);
    }

    public function show(int $id): JsonResponse
    {
        $exam = Exam::with('examRule')->findOrFail($id);

        return response()->json(['status' => 'success', 'data' => $exam]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create exams');

        $request->validate([
            'name'         => ['required', 'string', 'max:100'],
            'exam_rule_id' => ['nullable', 'integer', 'exists:exam_rules,id'],
        ]);

        $exam = Exam::create($request->only('name', 'exam_rule_id'));

        return response()->json(['status' => 'success', 'data' => $exam], 201);
    }
}
