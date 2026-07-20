<?php

namespace App\Http\Controllers;

use App\Models\FinalMark;
use App\Models\ReExamApplication;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SemesterInterface;
use App\Repositories\CourseRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReExamController extends Controller
{
    use SchoolSession;

    protected $schoolSessionRepository;
    protected $schoolClassRepository;
    protected $semesterRepository;

    public function __construct(
        SchoolSessionInterface $schoolSessionRepository,
        SchoolClassInterface   $schoolClassRepository,
        SemesterInterface      $semesterRepository
    ) {
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository   = $schoolClassRepository;
        $this->semesterRepository      = $semesterRepository;
        $this->middleware(['auth']);
    }

    /**
     * GET /re-exam
     * Admin/teacher: list all applications.
     * Student: list own applications.
     */
    public function index(Request $request)
    {
        $sessionId = $this->getSchoolCurrentSession();
        $user      = auth()->user();

        $query = ReExamApplication::with(['student', 'course', 'schoolClass', 'semester', 'reviewer'])
            ->where('session_id', $sessionId);

        if ($user->hasRole('student')) {
            $query->where('student_id', $user->id);
        }

        $status = $request->query('status');
        if ($status) $query->where('status', $status);

        $applications = $query->orderByDesc('created_at')->paginate(20);
        $statuses     = ReExamApplication::STATUSES;

        return view('exams.re-exam.index', compact('applications', 'statuses', 'sessionId'));
    }

    /**
     * GET /re-exam/apply
     * Student: application form.
     */
    public function create(Request $request)
    {
        $sessionId  = $this->getSchoolCurrentSession();
        $student    = auth()->user();

        // Load student's failed courses for the current session
        $failedCourses = FinalMark::with(['course', 'semester'])
            ->where('student_id', $student->id)
            ->where('session_id', $sessionId)
            ->get()
            ->filter(fn($m) => $m->final_marks < 40); // simple threshold; override via grade rules as needed

        $semesters = $this->semesterRepository->getAll($sessionId);

        return view('exams.re-exam.create', compact('failedCourses', 'semesters', 'sessionId'));
    }

    /**
     * POST /re-exam/apply
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id'   => 'required|integer|exists:courses,id',
            'class_id'    => 'required|integer|exists:school_classes,id',
            'section_id'  => 'nullable|integer',
            'semester_id' => 'required|integer|exists:semesters,id',
            'reason'      => 'required|string|max:1000',
        ]);

        $sessionId = $this->getSchoolCurrentSession();
        $studentId = auth()->id();

        // Prevent duplicate applications
        $existing = ReExamApplication::where('student_id',  $studentId)
            ->where('course_id',   $validated['course_id'])
            ->where('semester_id', $validated['semester_id'])
            ->where('session_id',  $sessionId)
            ->whereNotIn('status', ['rejected'])
            ->first();

        if ($existing) {
            return back()->withError('You already have an active re-exam application for this course.');
        }

        // Get original marks
        $originalMarks = FinalMark::where('student_id',  $studentId)
            ->where('course_id',   $validated['course_id'])
            ->where('semester_id', $validated['semester_id'])
            ->where('session_id',  $sessionId)
            ->value('final_marks') ?? 0;

        ReExamApplication::create(array_merge($validated, [
            'student_id'     => $studentId,
            'session_id'     => $sessionId,
            'section_id'     => $validated['section_id'] ?? 0,
            'original_marks' => $originalMarks,
            'status'         => 'pending',
        ]));

        return redirect()->route('re-exam.index')
            ->with('status', 'Re-exam application submitted. Awaiting review.');
    }

    /**
     * POST /re-exam/{id}/review
     * Admin/exam-controller: approve or reject.
     */
    public function review(Request $request, int $id)
    {
        $this->authorize('create exams');

        $request->validate([
            'action'      => 'required|in:approved,rejected',
            'admin_notes' => 'nullable|string|max:500',
            're_exam_date'=> 'nullable|date|after:today',
        ]);

        $app = ReExamApplication::findOrFail($id);

        if (! $app->isPending) {
            return back()->withError('Only pending applications can be reviewed.');
        }

        $newStatus = $request->action === 'approved' ? 'approved' : 'rejected';

        $app->update([
            'status'       => $newStatus,
            'admin_notes'  => $request->admin_notes,
            're_exam_date' => $request->re_exam_date,
            'reviewed_by'  => auth()->id(),
            'reviewed_at'  => now(),
        ]);

        return back()->with('status', "Application {$newStatus}.");
    }

    /**
     * POST /re-exam/{id}/result
     * Enter re-exam result marks.
     */
    public function enterResult(Request $request, int $id)
    {
        $this->authorize('save marks');

        $request->validate([
            're_exam_marks' => 'required|numeric|min:0|max:100',
        ]);

        $app = ReExamApplication::findOrFail($id);

        if (! in_array($app->status, ['approved', 'scheduled'])) {
            return back()->withError('Re-exam must be approved or scheduled before entering results.');
        }

        $app->update([
            're_exam_marks' => $request->re_exam_marks,
            'status'        => 'result_entered',
        ]);

        // Update FinalMark record with the better of original vs re-exam marks
        $bestMark = max($app->original_marks, $app->re_exam_marks);
        FinalMark::where('student_id',  $app->student_id)
            ->where('course_id',   $app->course_id)
            ->where('semester_id', $app->semester_id)
            ->where('session_id',  $app->session_id)
            ->update(['final_marks' => $bestMark, 'note' => 'Re-exam result applied.']);

        return back()->with('status', "Re-exam marks entered. Final mark updated to {$bestMark}.");
    }

    /**
     * POST /re-exam/{id}/complete
     */
    public function complete(int $id)
    {
        $this->authorize('create exams');

        $app = ReExamApplication::findOrFail($id);

        if ($app->status !== 'result_entered') {
            return back()->withError('Result must be entered before marking as completed.');
        }

        $app->update(['status' => 'completed']);
        return back()->with('status', 'Re-exam application marked as completed.');
    }
}
