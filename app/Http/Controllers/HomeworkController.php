<?php

namespace App\Http\Controllers;

use App\Models\Homework;
use App\Models\HomeworkSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\SchoolClassInterface;

class HomeworkController extends Controller
{
    use SchoolSession;
    protected $schoolSessionRepository;
    protected $schoolClassRepository;

    public function __construct(
        SchoolSessionInterface $schoolSessionRepository,
        SchoolClassInterface   $schoolClassRepository
    ) {
        $this->middleware('auth');
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository   = $schoolClassRepository;
    }

    /** Teacher: list homework they created / Student: list homework for their class */
    public function index(Request $request)
    {
        $session_id = $this->getSchoolCurrentSession();
        $user       = auth()->user();

        if ($user->hasRole(['teacher', 'class-teacher'])) {
            $homeworks = Homework::with(['course', 'schoolClass', 'section'])
                ->withCount('submissions')
                ->where('teacher_id', $user->id)
                ->where('session_id', $session_id)
                ->latest()
                ->paginate(20);

            return view('homework.teacher-index', compact('homeworks', 'session_id'));
        }

        // Student view — find their class/section via latest promotion
        $promotion = \App\Models\Promotion::where('student_id', $user->id)
            ->where('session_id', $session_id)
            ->latest()->first();

        $homeworks = collect();
        if ($promotion) {
            $homeworks = Homework::with(['course', 'mySubmission'])
                ->where('class_id', $promotion->class_id)
                ->where('section_id', $promotion->section_id)
                ->where('session_id', $session_id)
                ->where('status', 'active')
                ->latest()
                ->paginate(20);
        }

        return view('homework.student-index', compact('homeworks', 'session_id'));
    }

    public function create()
    {
        $this->authorize('create homework');

        $session_id = $this->getSchoolCurrentSession();
        $classes    = $this->schoolClassRepository->getAllBySession($session_id);

        return view('homework.create', compact('classes', 'session_id'));
    }

    public function store(Request $request)
    {
        $this->authorize('create homework');

        $session_id = $this->getSchoolCurrentSession();

        $data = $request->validate([
            'title'       => 'required|string|max:200',
            'description' => 'nullable|string',
            'due_date'    => 'required|date|after_or_equal:today',
            'total_marks' => 'required|integer|min:1|max:100',
            'course_id'   => 'required|integer',
            'class_id'    => 'required|integer',
            'section_id'  => 'required|integer',
            'file'        => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png',
        ]);

        $file_path = null;
        if ($request->hasFile('file')) {
            $file_path = $request->file('file')->store('homeworks', 'public');
        }

        Homework::create([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'file_path'   => $file_path,
            'due_date'    => $data['due_date'],
            'total_marks' => $data['total_marks'],
            'teacher_id'  => auth()->id(),
            'course_id'   => $data['course_id'],
            'class_id'    => $data['class_id'],
            'section_id'  => $data['section_id'],
            'session_id'  => $session_id,
        ]);

        return redirect()->route('homework.index')->with('status', 'Homework assigned.');
    }

    public function show(int $id)
    {
        $homework = Homework::with([
            'course', 'schoolClass', 'section', 'teacher',
            'submissions.student',
        ])->findOrFail($id);

        $user = auth()->user();

        // Students see their own submission only
        if ($user->hasRole('student')) {
            $mySubmission = $homework->mySubmission;
            return view('homework.student-show', compact('homework', 'mySubmission'));
        }

        return view('homework.teacher-show', compact('homework'));
    }

    public function destroy(int $id)
    {
        $this->authorize('create homework');

        $homework = Homework::where('teacher_id', auth()->id())->findOrFail($id);
        if ($homework->file_path) Storage::disk('public')->delete($homework->file_path);
        $homework->delete();

        return back()->with('status', 'Homework deleted.');
    }

    // ── Student Submission ────────────────────────────────────────────────

    public function submit(Request $request, int $id)
    {
        $homework = Homework::findOrFail($id);

        abort_if($homework->status === 'closed', 403, 'This homework is closed for submission.');

        $data = $request->validate([
            'remarks' => 'nullable|string|max:1000',
            'file'    => 'required|file|max:20480|mimes:pdf,doc,docx,jpg,jpeg,png,zip',
        ]);

        $file_path = $request->file('file')->store('homework-submissions', 'public');

        HomeworkSubmission::updateOrCreate(
            ['homework_id' => $id, 'student_id' => auth()->id()],
            [
                'file_path'    => $file_path,
                'remarks'      => $data['remarks'] ?? null,
                'status'       => 'submitted',
                'submitted_at' => now(),
            ]
        );

        return back()->with('status', 'Homework submitted successfully.');
    }

    // ── Teacher: grade a submission ───────────────────────────────────────

    public function grade(Request $request, int $submissionId)
    {
        $this->authorize('create homework');

        $submission = HomeworkSubmission::with('homework')->findOrFail($submissionId);

        abort_if(
            $submission->homework->teacher_id !== auth()->id(),
            403, 'You can only grade your own homework.'
        );

        $data = $request->validate([
            'marks_obtained'   => 'required|integer|min:0|max:' . $submission->homework->total_marks,
            'teacher_feedback' => 'nullable|string|max:1000',
        ]);

        $submission->update(array_merge($data, ['status' => 'graded']));

        return back()->with('status', 'Submission graded.');
    }

    // ── Teacher: toggle open / closed ─────────────────────────────────────

    public function toggleStatus(int $id)
    {
        $this->authorize('create homework');

        $homework = Homework::where('teacher_id', auth()->id())->findOrFail($id);
        $homework->update(['status' => $homework->status === 'active' ? 'closed' : 'active']);

        return back()->with('status', 'Homework status updated.');
    }
}
