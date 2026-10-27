<?php

namespace App\Http\Controllers\Api\StudentApp;

use App\Http\Controllers\Controller;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Assignment;
use App\Models\StudentStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    /**
     * GET /student-app/assignments
     * All homework/assignments for the student's class.
     */
    public function index(Request $request): JsonResponse
    {
        $classId   = $this->currentClassId($request);
        $studentId = $request->user()->id;

        $homework = $classId
            ? Homework::where('class_id', $classId)
                ->with(['course', 'submissions' => fn($q) => $q->where('student_id', $studentId)])
                ->orderByDesc('due_date')
                ->get()
                ->map(fn($hw) => $this->formatHomework($hw, $studentId))
            : collect();

        return response()->json([
            'status' => 'success',
            'data'   => $homework,
        ]);
    }

    /**
     * GET /student-app/assignments/pending
     * Homework not yet submitted.
     */
    public function pending(Request $request): JsonResponse
    {
        $classId   = $this->currentClassId($request);
        $studentId = $request->user()->id;

        $homework = $classId
            ? Homework::where('class_id', $classId)
                ->where('due_date', '>=', now()->toDateString())
                ->whereDoesntHave('submissions', fn($q) => $q->where('student_id', $studentId))
                ->with('course')
                ->orderBy('due_date')
                ->get()
                ->map(fn($hw) => $this->formatHomework($hw, $studentId))
            : collect();

        return response()->json([
            'status' => 'success',
            'data'   => $homework,
        ]);
    }

    /**
     * GET /student-app/assignments/completed
     * Homework already submitted.
     */
    public function completed(Request $request): JsonResponse
    {
        $studentId = $request->user()->id;

        $submissions = HomeworkSubmission::with(['homework.course'])
            ->where('student_id', $studentId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($sub) => [
                'submission_id'    => $sub->id,
                'homework_id'      => $sub->homework_id,
                'homework_title'   => $sub->homework?->title,
                'course'           => $sub->homework?->course?->course_name,
                'submitted_at'     => $sub->submitted_at ?? $sub->created_at,
                'status'           => $sub->status ?? 'submitted',
                'teacher_feedback' => $sub->teacher_feedback,  // actual column
                'marks_obtained'   => $sub->marks_obtained,
                'file_url'         => $sub->file_path ? Storage::url($sub->file_path) : null,
            ]);

        return response()->json([
            'status' => 'success',
            'data'   => $submissions,
        ]);
    }

    /**
     * GET /student-app/assignments/{id}
     * Single homework detail.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $classId   = $this->currentClassId($request);
        $studentId = $request->user()->id;

        $homework = Homework::where('class_id', $classId)
            ->with(['course', 'submissions' => fn($q) => $q->where('student_id', $studentId)])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $this->formatHomework($homework, $studentId),
        ]);
    }

    /**
     * POST /student-app/assignments/{id}/submit
     * Submit homework (text + optional file attachment).
     */
    public function submit(Request $request, int $id): JsonResponse
    {
        $classId   = $this->currentClassId($request);
        $studentId = $request->user()->id;

        $homework = Homework::where('class_id', $classId)->findOrFail($id);

        // Prevent duplicate submissions
        $existing = HomeworkSubmission::where('homework_id', $id)
            ->where('student_id', $studentId)
            ->first();

        if ($existing) {
            return response()->json([
                'status'  => 'error',
                'message' => 'You have already submitted this homework.',
            ], 409);
        }

        $validated = $request->validate([
            'remarks' => ['nullable', 'string', 'max:5000'],
            'file'    => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,jpg,jpeg,png,zip'],
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store("homework_submissions/{$homework->school_id}", 'public');
        }

        $submission = HomeworkSubmission::create([
            'homework_id'  => $id,
            'student_id'   => $studentId,
            'school_id'    => $homework->school_id,
            'remarks'      => $validated['remarks'] ?? null,  // actual fillable column
            'file_path'    => $filePath,
            'status'       => 'submitted',
            'submitted_at' => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Homework submitted successfully.',
            'data'    => $submission,
        ], 201);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function currentClassId(Request $request): ?int
    {
        return StudentStatus::where('student_id', $request->user()->id)
            ->where('is_current', true)
            ->value('class_id');
    }

    private function formatHomework(Homework $hw, int $studentId): array
    {
        $submission = $hw->submissions->first();

        return [
            'id'               => $hw->id,
            'title'            => $hw->title,
            'description'      => $hw->description,
            'course'           => $hw->course?->course_name,
            'course_id'        => $hw->course_id,
            'due_date'         => $hw->due_date,
            'total_marks'      => $hw->total_marks,
            'is_submitted'     => $submission !== null,
            'submission'       => $submission ? [
                'id'               => $submission->id,
                'status'           => $submission->status,
                'submitted_at'     => $submission->submitted_at ?? $submission->created_at,
                'teacher_feedback' => $submission->teacher_feedback,  // actual column
                'marks_obtained'   => $submission->marks_obtained,
                'file_url'         => $submission->file_path ? Storage::url($submission->file_path) : null,
            ] : null,
        ];
    }
}
