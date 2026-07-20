<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\SchoolClassInterface;

class ProjectController extends Controller
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

    public function index(Request $request)
    {
        $session_id = $this->getSchoolCurrentSession();
        $user       = auth()->user();

        if ($user->hasRole(['teacher', 'class-teacher'])) {
            $projects = Project::with(['course', 'schoolClass', 'section'])
                ->withCount('submissions')
                ->where('teacher_id', $user->id)
                ->where('session_id', $session_id)
                ->latest()
                ->paginate(20);

            return view('projects.teacher-index', compact('projects', 'session_id'));
        }

        // Student view
        $promotion = \App\Models\Promotion::where('student_id', $user->id)
            ->where('session_id', $session_id)
            ->latest()->first();

        $projects = collect();
        if ($promotion) {
            $projects = Project::with(['course', 'mySubmission'])
                ->where('class_id', $promotion->class_id)
                ->where('section_id', $promotion->section_id)
                ->where('session_id', $session_id)
                ->latest()
                ->paginate(20);
        }

        return view('projects.student-index', compact('projects', 'session_id'));
    }

    public function create()
    {
        $this->authorize('create homework');

        $session_id = $this->getSchoolCurrentSession();
        $classes    = $this->schoolClassRepository->getAllBySession($session_id);

        return view('projects.create', compact('classes', 'session_id'));
    }

    public function store(Request $request)
    {
        $this->authorize('create homework');

        $session_id = $this->getSchoolCurrentSession();

        $data = $request->validate([
            'title'       => 'required|string|max:200',
            'description' => 'nullable|string',
            'start_date'  => 'required|date',
            'due_date'    => 'required|date|after_or_equal:start_date',
            'total_marks' => 'required|integer|min:1|max:100',
            'type'        => 'required|in:individual,group',
            'course_id'   => 'required|integer',
            'class_id'    => 'required|integer',
            'section_id'  => 'required|integer',
            'file'        => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png',
        ]);

        $file_path = null;
        if ($request->hasFile('file')) {
            $file_path = $request->file('file')->store('projects', 'public');
        }

        Project::create([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'file_path'   => $file_path,
            'start_date'  => $data['start_date'],
            'due_date'    => $data['due_date'],
            'total_marks' => $data['total_marks'],
            'type'        => $data['type'],
            'teacher_id'  => auth()->id(),
            'course_id'   => $data['course_id'],
            'class_id'    => $data['class_id'],
            'section_id'  => $data['section_id'],
            'session_id'  => $session_id,
        ]);

        return redirect()->route('projects.index')->with('status', 'Project created.');
    }

    public function show(int $id)
    {
        $project = Project::with([
            'course', 'schoolClass', 'section', 'teacher',
            'submissions.student',
        ])->findOrFail($id);

        $user = auth()->user();
        if ($user->hasRole('student')) {
            $mySubmission = $project->mySubmission;
            return view('projects.student-show', compact('project', 'mySubmission'));
        }

        return view('projects.teacher-show', compact('project'));
    }

    public function destroy(int $id)
    {
        $this->authorize('create homework');

        $project = Project::where('teacher_id', auth()->id())->findOrFail($id);
        if ($project->file_path) Storage::disk('public')->delete($project->file_path);
        $project->delete();

        return back()->with('status', 'Project deleted.');
    }

    // ── Student Submit ────────────────────────────────────────────────────

    public function submit(Request $request, int $id)
    {
        $project = Project::findOrFail($id);
        abort_if($project->status === 'closed', 403, 'Submissions are closed.');

        $data = $request->validate([
            'description' => 'nullable|string|max:2000',
            'file'        => 'required|file|max:51200|mimes:pdf,doc,docx,zip,jpg,jpeg,png',
        ]);

        $file_path = $request->file('file')->store('project-submissions', 'public');

        ProjectSubmission::updateOrCreate(
            ['project_id' => $id, 'student_id' => auth()->id()],
            [
                'file_path'    => $file_path,
                'description'  => $data['description'] ?? null,
                'status'       => 'submitted',
                'submitted_at' => now(),
            ]
        );

        return back()->with('status', 'Project submitted successfully.');
    }

    // ── Teacher: grade ────────────────────────────────────────────────────

    public function grade(Request $request, int $submissionId)
    {
        $this->authorize('create homework');

        $submission = ProjectSubmission::with('project')->findOrFail($submissionId);

        abort_if(
            $submission->project->teacher_id !== auth()->id(),
            403, 'You can only grade your own projects.'
        );

        $data = $request->validate([
            'marks_obtained'   => 'required|integer|min:0|max:' . $submission->project->total_marks,
            'teacher_feedback' => 'nullable|string|max:1000',
        ]);

        $submission->update(array_merge($data, ['status' => 'graded']));

        return back()->with('status', 'Submission graded.');
    }
}
