<?php

namespace App\Http\Controllers;

use App\Models\Curriculum;
use App\Models\CurriculumTopic;
use App\Models\Program;
use App\Models\Term;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\CourseInterface;

class CurriculumController extends Controller
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
        $this->authorize('view academic settings');

        $session_id = $this->getSchoolCurrentSession();
        $class_id   = $request->query('class_id');
        $course_id  = $request->query('course_id');

        $query = Curriculum::with(['program', 'schoolClass', 'course'])
            ->withCount('topics')
            ->where('session_id', $session_id);

        if ($class_id)  $query->where('class_id', $class_id);
        if ($course_id) $query->where('course_id', $course_id);

        $curriculums = $query->latest()->get();
        $classes     = $this->schoolClassRepository->getAllBySession($session_id);
        $programs    = Program::where('is_active', true)->orderBy('name')->get();

        return view('curriculums.index', compact('curriculums', 'classes', 'programs', 'session_id', 'class_id', 'course_id'));
    }

    public function create()
    {
        $this->authorize('view academic settings');

        $session_id = $this->getSchoolCurrentSession();
        $classes    = $this->schoolClassRepository->getAllBySession($session_id);
        $programs   = Program::where('is_active', true)->orderBy('name')->get();
        $terms      = Term::where('session_id', $session_id)->orderBy('start_date')->get();

        return view('curriculums.create', compact('classes', 'programs', 'terms', 'session_id'));
    }

    public function store(Request $request)
    {
        $this->authorize('view academic settings');

        $session_id = $this->getSchoolCurrentSession();

        $data = $request->validate([
            'title'            => 'required|string|max:200',
            'description'      => 'nullable|string',
            'program_id'       => 'nullable|exists:programs,id',
            'class_id'         => 'required|integer',
            'course_id'        => 'required|integer',
            'status'           => 'required|in:draft,published,archived',
            'objectives'       => 'nullable|string',
            'learning_outcomes'=> 'nullable|string',
        ]);

        $curriculum = Curriculum::create(array_merge($data, ['session_id' => $session_id]));

        // Store topics if provided
        if ($request->has('topics')) {
            foreach ($request->topics as $index => $topic) {
                if (!empty($topic['title'])) {
                    CurriculumTopic::create([
                        'curriculum_id'    => $curriculum->id,
                        'title'            => $topic['title'],
                        'description'      => $topic['description'] ?? null,
                        'term_id'          => $topic['term_id'] ?? null,
                        'order'            => $index + 1,
                        'estimated_hours'  => $topic['estimated_hours'] ?? 1,
                    ]);
                }
            }
        }

        return redirect()->route('curriculums.show', $curriculum->id)
            ->with('status', "Curriculum '{$curriculum->title}' created.");
    }

    public function show(int $id)
    {
        $this->authorize('view academic settings');

        $curriculum = Curriculum::with(['program', 'schoolClass', 'course', 'topics.term'])
            ->findOrFail($id);

        $session_id = $this->getSchoolCurrentSession();
        $terms      = Term::where('session_id', $session_id)->orderBy('start_date')->get();

        return view('curriculums.show', compact('curriculum', 'terms'));
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('view academic settings');

        $curriculum = Curriculum::findOrFail($id);

        $data = $request->validate([
            'title'             => 'required|string|max:200',
            'description'       => 'nullable|string',
            'program_id'        => 'nullable|exists:programs,id',
            'status'            => 'required|in:draft,published,archived',
            'objectives'        => 'nullable|string',
            'learning_outcomes' => 'nullable|string',
        ]);

        $curriculum->update($data);

        return back()->with('status', 'Curriculum updated.');
    }

    public function destroy(int $id)
    {
        $this->authorize('view academic settings');

        $curriculum = Curriculum::findOrFail($id);
        $curriculum->delete();

        return redirect()->route('curriculums.index')->with('status', 'Curriculum deleted.');
    }

    // ── Topics ────────────────────────────────────────────────────────────

    public function storeTopic(Request $request, int $curriculumId)
    {
        $this->authorize('view academic settings');

        $curriculum = Curriculum::findOrFail($curriculumId);

        $data = $request->validate([
            'title'           => 'required|string|max:200',
            'description'     => 'nullable|string',
            'term_id'         => 'nullable|exists:terms,id',
            'estimated_hours' => 'required|integer|min:1',
        ]);

        $maxOrder = $curriculum->topics()->max('order') ?? 0;

        CurriculumTopic::create(array_merge($data, [
            'curriculum_id' => $curriculumId,
            'order'         => $maxOrder + 1,
        ]));

        return back()->with('status', 'Topic added.');
    }

    public function destroyTopic(int $topicId)
    {
        $this->authorize('view academic settings');

        CurriculumTopic::findOrFail($topicId)->delete();

        return back()->with('status', 'Topic removed.');
    }
}
