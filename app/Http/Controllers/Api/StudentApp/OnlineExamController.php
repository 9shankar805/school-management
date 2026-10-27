<?php

namespace App\Http\Controllers\Api\StudentApp;

use App\Http\Controllers\Controller;
use App\Models\OnlineExam;
use App\Models\OnlineExamAttempt;
use App\Models\OnlineExamAnswer;
use App\Models\OnlineExamQuestion;
use App\Models\StudentStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OnlineExamController extends Controller
{
    /**
     * GET /student-app/online-exams
     * Available online exams for the student's class.
     */
    public function index(Request $request): JsonResponse
    {
        $classId   = $this->currentClassId($request);
        $studentId = $request->user()->id;

        $exams = OnlineExam::where('class_id', $classId)
            ->where('status', 'published')
            ->with('course')
            ->orderByDesc('available_from')    // actual column: available_from
            ->get()
            ->map(fn($exam) => $this->formatExam($exam, $studentId));

        return response()->json([
            'status' => 'success',
            'data'   => $exams,
        ]);
    }

    /**
     * GET /student-app/online-exams/{id}
     * Exam details (without questions — shown only when starting).
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $classId   = $this->currentClassId($request);
        $studentId = $request->user()->id;

        $exam = OnlineExam::where('class_id', $classId)
            ->with('course')
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $this->formatExam($exam, $studentId),
        ]);
    }

    /**
     * POST /student-app/online-exams/{id}/start
     * Start an exam attempt — returns questions.
     */
    public function start(Request $request, int $id): JsonResponse
    {
        $classId   = $this->currentClassId($request);
        $studentId = $request->user()->id;

        $exam = OnlineExam::where('class_id', $classId)
            ->where('status', 'published')
            ->findOrFail($id);

        // Enforce start/end window if set
        if ($exam->available_from && now()->lt($exam->available_from)) {
            return response()->json(['status' => 'error', 'message' => 'Exam has not started yet.'], 403);
        }
        if ($exam->available_to && now()->gt($exam->available_to)) {
            return response()->json(['status' => 'error', 'message' => 'Exam window has closed.'], 403);
        }

        // Check for existing attempt
        $attempt = OnlineExamAttempt::where('online_exam_id', $id)
            ->where('student_id', $studentId)
            ->first();

        if ($attempt && $attempt->submitted_at) {
            return response()->json(['status' => 'error', 'message' => 'You have already submitted this exam.'], 409);
        }

        // Resume or create attempt
        if (! $attempt) {
            $attempt = OnlineExamAttempt::create([
                'online_exam_id' => $id,
                'student_id'     => $studentId,
                'school_id'      => $exam->school_id,
                'started_at'     => now(),
            ]);
        }

        // Load questions — shuffle if configured
        $questions = OnlineExamQuestion::where('online_exam_id', $id)
            ->when($exam->shuffle_questions ?? false, fn($q) => $q->inRandomOrder())
            ->get()
            ->map(fn($q) => [
                'id'      => $q->id,
                'text'    => $q->question_text,   // actual column
                'type'    => $q->question_type,   // actual column
                'marks'   => $q->marks,
                // Only send options for MCQ — not correct answer
                'options' => ($q->question_type === 'mcq' || $q->question_type === 'multiple_choice')
                    ? json_decode($q->options ?? '[]', true) : null,
            ]);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'attempt_id'   => $attempt->id,
                'exam_name'    => $exam->title,           // actual column: title
                'duration_min' => $exam->duration_minutes,
                'started_at'   => $attempt->started_at,
                'questions'    => $questions,
            ],
        ]);
    }

    /**
     * POST /student-app/online-exams/{id}/submit
     * Submit answers and auto-grade MCQ questions.
     */
    public function submit(Request $request, int $id): JsonResponse
    {
        $studentId = $request->user()->id;

        $attempt = OnlineExamAttempt::where('online_exam_id', $id)
            ->where('student_id', $studentId)
            ->whereNull('submitted_at')
            ->firstOrFail();

        $validated = $request->validate([
            'answers'                  => ['required', 'array'],
            'answers.*.question_id'    => ['required', 'integer'],
            'answers.*.answer'         => ['nullable', 'string', 'max:5000'],
        ]);

        DB::beginTransaction();
        try {
            $totalMarks   = 0;
            $obtainedMarks = 0;

            foreach ($validated['answers'] as $row) {
                $question = OnlineExamQuestion::find($row['question_id']);
                if (! $question) continue;

                $isCorrect   = null;
                $marksAwarded = 0;

                if ($question->question_type === 'mcq' || $question->question_type === 'multiple_choice'
                    || $question->question_type === 'true_false') {
                    $isCorrect    = strtolower(trim($row['answer'] ?? '')) === strtolower(trim($question->correct_answer ?? ''));
                    $marksAwarded = $isCorrect ? ($question->marks ?? 1) : 0;
                }
                // Short/long answers: marked by teacher (marksAwarded stays 0 until reviewed)

                OnlineExamAnswer::updateOrCreate(
                    ['attempt_id' => $attempt->id, 'question_id' => $row['question_id']],
                    [
                        'answer_text'  => $row['answer'] ?? '',   // actual column: answer_text
                        'is_correct'   => $isCorrect,
                        'marks_awarded'=> $marksAwarded,
                        'school_id'    => $attempt->school_id,
                    ]
                );

                $totalMarks    += $question->marks ?? 1;
                $obtainedMarks += $marksAwarded;
            }

            $attempt->update([
                'submitted_at'   => now(),
                'status'         => 'submitted',
                'score'          => $obtainedMarks,       // actual column: score
                'percentage'     => $totalMarks > 0 ? round($obtainedMarks / $totalMarks * 100, 2) : 0,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Failed to submit exam. Please try again.'], 500);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Exam submitted successfully.',
            'data'    => [
                'score'          => $attempt->score,
                'percentage'     => $attempt->percentage,
                'total_marks'    => $attempt->onlineExam?->total_marks,   // relation: onlineExam()
            ],
        ]);
    }

    /**
     * GET /student-app/online-exams/{id}/result
     * Result for a completed attempt.
     */
    public function result(Request $request, int $id): JsonResponse
    {
        $studentId = $request->user()->id;

        $attempt = OnlineExamAttempt::with(['onlineExam', 'answers.question'])   // relation: onlineExam()
            ->where('online_exam_id', $id)
            ->where('student_id', $studentId)
            ->whereNotNull('submitted_at')
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'exam_name'   => $attempt->onlineExam?->title,    // relation: onlineExam()
                'submitted_at'  => $attempt->submitted_at,
                'score'         => $attempt->score,
                'total_marks'   => $attempt->onlineExam?->total_marks,
                'percentage'    => $attempt->percentage,
                'answers'        => $attempt->answers->map(fn($a) => [
                    'question' => $a->question?->question_text,  // actual column: question_text
                    'answer'   => $a->answer_text,               // actual column: answer_text
                    'correct'  => $a->is_correct,
                    'marks'    => $a->marks_awarded,
                ]),
            ],
        ]);
    }

    // ── Helper ────────────────────────────────────────────────────────────

    private function currentClassId(Request $request): ?int
    {
        return StudentStatus::where('student_id', $request->user()->id)
            ->where('is_current', true)
            ->value('class_id');
    }

    private function formatExam(OnlineExam $exam, int $studentId): array
    {
        $attempt = OnlineExamAttempt::where('online_exam_id', $exam->id)
            ->where('student_id', $studentId)
            ->first();

        return [
            'id'           => $exam->id,
            'name'         => $exam->title,            // actual column: title
            'course'       => $exam->course?->course_name,
            'duration_min' => $exam->duration_minutes,
            'total_marks'  => $exam->total_marks,
            'start_time'   => $exam->available_from,   // actual column: available_from
            'end_time'     => $exam->available_to,     // actual column: available_to
            'status'       => $exam->status,
            'attempt'      => $attempt ? [
                'started_at'   => $attempt->started_at,
                'submitted_at' => $attempt->submitted_at,
                'score'        => $attempt->score,     // actual column: score
                'percentage'   => $attempt->percentage,
            ] : null,
        ];
    }
}
