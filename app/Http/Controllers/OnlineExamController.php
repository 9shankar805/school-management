<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Exam;
use App\Models\OnlineExam;
use App\Models\OnlineExamAnswer;
use App\Models\OnlineExamAttempt;
use App\Models\OnlineExamQuestion;
use App\Models\SchoolClass;
use App\Traits\SchoolSession;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OnlineExamController extends Controller
{
    use SchoolSession;

    protected $schoolSessionRepository;

    public function __construct(\App\Interfaces\SchoolSessionInterface $schoolSessionRepository)
    {
        $this->middleware('auth');
        $this->schoolSessionRepository = $schoolSessionRepository;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ADMIN / TEACHER — Exam management
    // ─────────────────────────────────────────────────────────────────────────

    /** List all online exams (admin/teacher) */
    public function index(Request $request)
    {
        $this->authorize('view exams');
        $sessionId = $this->getSchoolCurrentSession();

        $query = OnlineExam::with(['course', 'schoolClass', 'createdBy'])
            ->where('session_id', $sessionId);

        if ($classId = $request->input('class_id')) {
            $query->where('class_id', $classId);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $onlineExams = $query->latest()->paginate(20)->withQueryString();
        $classes     = SchoolClass::orderBy('class_name')->get();

        return view('online-exams.index', compact('onlineExams', 'classes'));
    }

    /** Create form */
    public function create()
    {
        $this->authorize('create exams');
        $sessionId = $this->getSchoolCurrentSession();
        $classes   = SchoolClass::orderBy('class_name')->get();
        $courses   = Course::where('session_id', $sessionId)->orderBy('course_name')->get();
        $exams     = Exam::where('session_id', $sessionId)->orderBy('exam_name')->get();

        return view('online-exams.create', compact('classes', 'courses', 'exams', 'sessionId'));
    }

    /** Store new online exam */
    public function store(Request $request)
    {
        $this->authorize('create exams');
        $sessionId = $this->getSchoolCurrentSession();

        $data = $request->validate([
            'title'                    => 'required|string|max:200',
            'instructions'             => 'nullable|string',
            'exam_id'                  => 'nullable|exists:exams,id',
            'course_id'                => 'nullable|exists:courses,id',
            'class_id'                 => 'nullable|exists:school_classes,id',
            'duration_minutes'         => 'required|integer|min:5|max:300',
            'total_marks'              => 'required|integer|min:1',
            'pass_marks'               => 'required|integer|min:0',
            'max_attempts'             => 'required|integer|min:1|max:10',
            'is_practice'              => 'boolean',
            'shuffle_questions'        => 'boolean',
            'show_result_immediately'  => 'boolean',
            'available_from'           => 'nullable|date',
            'available_to'             => 'nullable|date|after_or_equal:available_from',
        ]);

        $data['session_id']  = $sessionId;
        $data['created_by']  = auth()->id();
        $data['status']      = 'draft';
        $data['shuffle_questions']       = $request->boolean('shuffle_questions');
        $data['is_practice']             = $request->boolean('is_practice');
        if ($data['is_practice']) { $data['max_attempts'] = 99; $data['show_result_immediately'] = true; }
        $data['show_result_immediately'] = $request->boolean('show_result_immediately');

        $exam = OnlineExam::create($data);

        return redirect()->route('online-exams.questions', $exam->id)
            ->with('status', 'Online exam created. Now add questions.');
    }

    /** Show exam detail + manage questions */
    public function show(int $id)
    {
        $this->authorize('view exams');
        $exam = OnlineExam::with(['questions', 'course', 'schoolClass', 'createdBy'])->findOrFail($id);
        return view('online-exams.show', compact('exam'));
    }

    /** Edit exam settings */
    public function edit(int $id)
    {
        $this->authorize('create exams');
        $exam      = OnlineExam::findOrFail($id);
        $sessionId = $this->getSchoolCurrentSession();
        $classes   = SchoolClass::orderBy('class_name')->get();
        $courses   = Course::where('session_id', $sessionId)->orderBy('course_name')->get();
        $exams     = Exam::where('session_id', $sessionId)->orderBy('exam_name')->get();
        return view('online-exams.edit', compact('exam', 'classes', 'courses', 'exams'));
    }

    /** Update exam */
    public function update(Request $request, int $id)
    {
        $this->authorize('create exams');
        $exam = OnlineExam::findOrFail($id);

        $data = $request->validate([
            'title'                    => 'required|string|max:200',
            'instructions'             => 'nullable|string',
            'duration_minutes'         => 'required|integer|min:5|max:300',
            'total_marks'              => 'required|integer|min:1',
            'pass_marks'               => 'required|integer|min:0',
            'max_attempts'             => 'required|integer|min:1|max:10',
            'is_practice'              => 'boolean',
            'shuffle_questions'        => 'boolean',
            'show_result_immediately'  => 'boolean',
            'available_from'           => 'nullable|date',
            'available_to'             => 'nullable|date|after_or_equal:available_from',
        ]);

        $data['shuffle_questions']       = $request->boolean('shuffle_questions');
        $data['show_result_immediately'] = $request->boolean('show_result_immediately');
        $exam->update($data);

        return back()->with('status', 'Exam updated.');
    }

    /** Publish / unpublish */
    public function togglePublish(int $id)
    {
        $this->authorize('create exams');
        $exam = OnlineExam::findOrFail($id);
        if ($exam->status === 'published') {
            $exam->update(['status' => 'draft', 'is_published' => false]);
            $msg = 'Exam unpublished.';
        } else {
            if ($exam->questions()->count() === 0) {
                return back()->with('error', 'Cannot publish an exam with no questions.');
            }
            $exam->update(['status' => 'published', 'is_published' => true]);
            $msg = 'Exam published — students can now take it.';
        }
        return back()->with('status', $msg);
    }

    /** Close exam (no more submissions) */
    public function close(int $id)
    {
        $this->authorize('create exams');
        OnlineExam::findOrFail($id)->update(['status' => 'closed']);
        return back()->with('status', 'Exam closed.');
    }

    /** Delete */
    public function destroy(int $id)
    {
        $this->authorize('create exams');
        OnlineExam::findOrFail($id)->delete();
        return redirect()->route('online-exams.index')->with('status', 'Exam deleted.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Question management
    // ─────────────────────────────────────────────────────────────────────────

    /** Show question manager for an exam */
    public function questions(int $id)
    {
        $this->authorize('create exams');
        $exam      = OnlineExam::with('questions')->findOrFail($id);
        $totalMark = $exam->questions->sum('marks');
        return view('online-exams.questions', compact('exam', 'totalMark'));
    }

    /** Add a question */
    public function storeQuestion(Request $request, int $id)
    {
        $this->authorize('create exams');
        OnlineExam::findOrFail($id); // ensure exam exists

        $data = $request->validate([
            'question_text'  => 'required|string',
            'question_type'  => 'required|in:mcq,true_false,short_answer,essay',
            'marks'          => 'required|integer|min:1',
            'correct_answer' => 'nullable|string|max:500',
            'explanation'    => 'nullable|string|max:1000',
            // MCQ options come as options[0], options[1], …
            'options'        => 'nullable|array|min:2',
            'options.*'      => 'string|max:500',
        ]);

        $data['online_exam_id'] = $id;
        $data['sort_order']     = OnlineExamQuestion::where('online_exam_id', $id)->max('sort_order') + 1;

        OnlineExamQuestion::create($data);
        return back()->with('status', 'Question added.');
    }

    /** Update a question */
    public function updateQuestion(Request $request, int $questionId)
    {
        $this->authorize('create exams');
        $q = OnlineExamQuestion::findOrFail($questionId);

        $data = $request->validate([
            'question_text'  => 'required|string',
            'marks'          => 'required|integer|min:1',
            'correct_answer' => 'nullable|string|max:500',
            'explanation'    => 'nullable|string|max:1000',
            'options'        => 'nullable|array',
            'options.*'      => 'string|max:500',
        ]);

        $q->update($data);
        return back()->with('status', 'Question updated.');
    }

    /** Delete a question */
    public function destroyQuestion(int $questionId)
    {
        $this->authorize('create exams');
        OnlineExamQuestion::findOrFail($questionId)->delete();
        return back()->with('status', 'Question deleted.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Attempt grading (teacher / admin)
    // ─────────────────────────────────────────────────────────────────────────

    /** View all attempts for an exam */
    public function attempts(int $id)
    {
        $this->authorize('view exams');
        $exam     = OnlineExam::findOrFail($id);
        $attempts = OnlineExamAttempt::with('student')
            ->where('online_exam_id', $id)
            ->orderByDesc('started_at')
            ->paginate(30);
        return view('online-exams.attempts', compact('exam', 'attempts'));
    }

    /** Grade a single attempt (for essay questions) */
    public function grade(Request $request, int $attemptId)
    {
        $this->authorize('create exams');
        $attempt = OnlineExamAttempt::with(['answers.question', 'onlineExam'])->findOrFail($attemptId);

        $request->validate([
            'marks'          => 'required|array',
            'marks.*'        => 'integer|min:0',
            'teacher_feedback' => 'nullable|string|max:1000',
        ]);

        $totalScore = 0;
        foreach ($request->marks as $answerId => $marks) {
            $answer = OnlineExamAnswer::findOrFail($answerId);
            $maxMarks = $answer->question->marks;
            $awardedMarks = min((int)$marks, $maxMarks);
            $answer->update([
                'marks_awarded' => $awardedMarks,
                'is_correct'    => $awardedMarks >= $maxMarks,
            ]);
            $totalScore += $awardedMarks;
        }

        $exam       = $attempt->onlineExam;
        $percentage = $exam->total_marks > 0 ? round(($totalScore / $exam->total_marks) * 100, 2) : 0;
        $passed     = $totalScore >= $exam->pass_marks;

        $attempt->update([
            'status'           => 'graded',
            'score'            => $totalScore,
            'percentage'       => $percentage,
            'passed'           => $passed,
            'graded_by'        => auth()->id(),
            'graded_at'        => now(),
            'teacher_feedback' => $request->teacher_feedback,
        ]);

        return back()->with('status', "Attempt graded. Score: {$totalScore}/{$exam->total_marks}.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STUDENT — take exam
    // ─────────────────────────────────────────────────────────────────────────

    /** Student: list available exams */
    public function studentIndex()
    {
        $user       = auth()->user();
        $sessionId  = $this->getSchoolCurrentSession();
        $classId    = $user->academic_info?->class_id;

        $exams = OnlineExam::with('course')
            ->where('status', 'published')
            ->where('session_id', $sessionId)
            ->where(fn($q) => $q->whereNull('class_id')->orWhere('class_id', $classId))
            ->where(fn($q) => $q
                ->whereNull('available_from')
                ->orWhere('available_from', '<=', now())
            )
            ->where(fn($q) => $q
                ->whereNull('available_to')
                ->orWhere('available_to', '>=', now())
            )
            ->get();

        // Attach student's attempt status to each exam
        $exams->each(function ($exam) use ($user) {
            $exam->student_attempts = $exam->attemptsFor($user->id)->get();
        });

        return view('online-exams.student.index', compact('exams'));
    }

    /** Student: start or resume an attempt */
    public function startAttempt(int $id)
    {
        $user  = auth()->user();
        $exam  = OnlineExam::with('questions')->findOrFail($id);

        if (! $exam->is_available) {
            return redirect()->route('online-exams.student.index')
                ->with('error', 'This exam is not currently available.');
        }

        $attemptCount = $exam->attemptsFor($user->id)->count();

        if ($attemptCount >= $exam->max_attempts) {
            return redirect()->route('online-exams.student.index')
                ->with('error', 'You have used all your attempts for this exam.');
        }

        // Check for in-progress attempt
        $attempt = $exam->attemptsFor($user->id)->where('status', 'in_progress')->first();

        if (! $attempt) {
            $attempt = OnlineExamAttempt::create([
                'online_exam_id'  => $exam->id,
                'student_id'      => $user->id,
                'attempt_number'  => $attemptCount + 1,
                'status'          => 'in_progress',
                'started_at'      => now(),
            ]);
        }

        // Auto-timeout check
        $remaining = $attempt->remainingSeconds();
        if ($remaining === 0) {
            $this->autoSubmit($attempt);
            return redirect()->route('online-exams.student.result', $attempt->id)
                ->with('error', 'Your time has expired. The exam was automatically submitted.');
        }

        $questions = $exam->shuffle_questions
            ? $exam->questions->shuffle()
            : $exam->questions;

        $savedAnswers = $attempt->answers()->pluck('answer_text', 'question_id');

        return view('online-exams.student.take', compact('exam', 'attempt', 'questions', 'savedAnswers', 'remaining'));
    }

    /** Student: auto-save a single answer (AJAX) */
    public function saveAnswer(Request $request, int $attemptId)
    {
        $attempt = OnlineExamAttempt::where('id', $attemptId)
            ->where('student_id', auth()->id())
            ->where('status', 'in_progress')
            ->firstOrFail();

        $request->validate([
            'question_id'  => 'required|exists:online_exam_questions,id',
            'answer_text'  => 'nullable|string|max:5000',
        ]);

        OnlineExamAnswer::updateOrCreate(
            ['attempt_id' => $attempt->id, 'question_id' => $request->question_id],
            ['answer_text' => $request->answer_text]
        );

        return response()->json(['saved' => true]);
    }

    /** Student: submit exam */
    public function submitAttempt(Request $request, int $attemptId)
    {
        $attempt = OnlineExamAttempt::where('id', $attemptId)
            ->where('student_id', auth()->id())
            ->where('status', 'in_progress')
            ->firstOrFail();

        // Save all submitted answers
        $answers = $request->input('answers', []);
        foreach ($answers as $questionId => $answerText) {
            OnlineExamAnswer::updateOrCreate(
                ['attempt_id' => $attempt->id, 'question_id' => $questionId],
                ['answer_text' => $answerText]
            );
        }

        $this->autoGrade($attempt);

        return redirect()->route('online-exams.student.result', $attempt->id)
            ->with('status', 'Exam submitted successfully!');
    }

    /** Student: view attempt result */
    public function result(int $attemptId)
    {
        $attempt = OnlineExamAttempt::with(['onlineExam.questions', 'answers.question'])
            ->where('id', $attemptId)
            ->where('student_id', auth()->id())
            ->firstOrFail();

        return view('online-exams.student.result', compact('attempt'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function autoSubmit(OnlineExamAttempt $attempt): void
    {
        $this->autoGrade($attempt, 'timed_out');
    }

    private function autoGrade(OnlineExamAttempt $attempt, string $finalStatus = 'submitted'): void
    {
        $exam      = $attempt->onlineExam;
        $totalScore = 0;
        $hasEssay  = false;

        foreach ($attempt->answers as $answer) {
            $question = $answer->question;
            if ($question->isAutoGradable()) {
                $correct = $question->checkAnswer($answer->answer_text ?? '');
                $marks   = $correct ? $question->marks : 0;
                $answer->update(['is_correct' => $correct, 'marks_awarded' => $marks]);
                $totalScore += $marks;
            } else {
                $hasEssay = true; // needs manual grading
            }
        }

        $needsGrading = $hasEssay ? 'submitted' : $finalStatus;
        $percentage   = $exam->total_marks > 0
            ? round(($totalScore / $exam->total_marks) * 100, 2)
            : 0;

        $attempt->update([
            'status'       => $needsGrading,
            'submitted_at' => now(),
            'score'        => $totalScore,
            'percentage'   => $percentage,
            'passed'       => $totalScore >= $exam->pass_marks,
        ]);
    }
}
