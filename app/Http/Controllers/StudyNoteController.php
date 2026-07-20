<?php

namespace App\Http\Controllers;

use App\Models\StudyNote;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\CourseInterface;

class StudyNoteController extends Controller
{
    use SchoolSession;
    protected $schoolSessionRepository;
    protected $schoolClassRepository;
    protected $courseRepository;

    public function __construct(
        SchoolSessionInterface $schoolSessionRepository,
        SchoolClassInterface   $schoolClassRepository,
        CourseInterface        $courseRepository
    ) {
        $this->middleware('auth');
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository   = $schoolClassRepository;
        $this->courseRepository        = $courseRepository;
    }

    public function index(Request $request)
    {
        $session_id = $this->getSchoolCurrentSession();
        $user       = auth()->user();

        $query = StudyNote::with(['uploader', 'course', 'schoolClass', 'term'])
            ->where('session_id', $session_id);

        // Teachers see all their own notes; students see only published
        if ($user->hasRole('student')) {
            $query->where('is_published', true);

            // Scope to student's class
            $promotion = \App\Models\Promotion::where('student_id', $user->id)
                ->where('session_id', $session_id)
                ->latest()->first();

            if ($promotion) {
                $query->where('class_id', $promotion->class_id);
            }
        } elseif ($user->hasRole(['teacher', 'class-teacher'])) {
            $query->where('uploaded_by', $user->id);
        }

        if ($request->course_id) $query->where('course_id', $request->course_id);
        if ($request->type)      $query->where('type', $request->type);

        $notes   = $query->latest()->paginate(25)->withQueryString();
        $classes = $this->schoolClassRepository->getAllBySession($session_id);
        $terms   = Term::where('session_id', $session_id)->orderBy('start_date')->get();

        return view('study-notes.index', compact('notes', 'classes', 'terms', 'session_id'));
    }

    public function create()
    {
        $this->authorize('create study notes');

        $session_id = $this->getSchoolCurrentSession();
        $classes    = $this->schoolClassRepository->getAllBySession($session_id);
        $terms      = Term::where('session_id', $session_id)->orderBy('start_date')->get();

        return view('study-notes.create', compact('classes', 'terms', 'session_id'));
    }

    public function store(Request $request)
    {
        $this->authorize('create study notes');

        $session_id = $this->getSchoolCurrentSession();

        $data = $request->validate([
            'title'        => 'required|string|max:200',
            'description'  => 'nullable|string',
            'type'         => 'required|in:note,handout,reference,video_link',
            'external_url' => 'nullable|url|max:500',
            'course_id'    => 'required|integer',
            'class_id'     => 'required|integer',
            'term_id'      => 'nullable|exists:terms,id',
            'is_published' => 'boolean',
            'file'         => 'nullable|file|max:20480|mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png,mp4',
        ]);

        $file_path = null;
        if ($request->hasFile('file')) {
            $file_path = $request->file('file')->store('study-notes', 'public');
        }

        StudyNote::create([
            'title'        => $data['title'],
            'description'  => $data['description'] ?? null,
            'file_path'    => $file_path,
            'type'         => $data['type'],
            'external_url' => $data['external_url'] ?? null,
            'uploaded_by'  => auth()->id(),
            'course_id'    => $data['course_id'],
            'class_id'     => $data['class_id'],
            'session_id'   => $session_id,
            'term_id'      => $data['term_id'] ?? null,
            'is_published' => $request->boolean('is_published', true),
        ]);

        return redirect()->route('study-notes.index')->with('status', 'Study material uploaded.');
    }

    public function destroy(int $id)
    {
        $this->authorize('create study notes');

        $note = StudyNote::findOrFail($id);

        // Only uploader or admin may delete
        if (auth()->id() !== $note->uploaded_by) {
            $this->authorize('view academic settings');
        }

        if ($note->file_path) Storage::disk('public')->delete($note->file_path);
        $note->delete();

        return back()->with('status', 'Study material deleted.');
    }

    public function togglePublish(int $id)
    {
        $this->authorize('create study notes');

        $note = StudyNote::findOrFail($id);

        if (auth()->id() !== $note->uploaded_by) {
            $this->authorize('view academic settings');
        }

        $note->update(['is_published' => !$note->is_published]);

        return back()->with('status', 'Visibility updated.');
    }
}
