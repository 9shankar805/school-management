<?php

namespace App\Http\Controllers;

use App\Models\LessonPlan;
use App\Models\Term;
use App\Models\CurriculumTopic;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\CourseInterface;
use App\Interfaces\SectionInterface;

class LessonPlanController extends Controller
{
    use SchoolSession;
    protected $schoolSessionRepository;
    protected $schoolClassRepository;
    protected $courseRepository;
    protected $sectionRepository;

    public function __construct(
        SchoolSessionInterface $schoolSessionRepository,
        SchoolClassInterface   $schoolClassRepository,
        CourseInterface        $courseRepository,
        SectionInterface       $sectionRepository
    ) {
        $this->middleware('auth');
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository   = $schoolClassRepository;
        $this->courseRepository        = $courseRepository;
        $this->sectionRepository       = $sectionRepository;
    }

    public function index(Request $request)
    {
        $this->authorize('view lesson plans');

        $session_id = $this->getSchoolCurrentSession();
        $teacher_id = auth()->user()->hasRole(['teacher','class-teacher'])
            ? auth()->id()
            : $request->query('teacher_id');

        $query = LessonPlan::with(['course', 'schoolClass', 'section', 'term', 'teacher'])
            ->where('session_id', $session_id);

        if ($teacher_id)              $query->where('teacher_id', $teacher_id);
        if ($request->course_id)      $query->where('course_id', $request->course_id);
        if ($request->class_id)       $query->where('class_id', $request->class_id);
        if ($request->status)         $query->where('status', $request->status);

        $lessonPlans = $query->orderByDesc('planned_date')->paginate(20)->withQueryString();

        $classes     = $this->schoolClassRepository->getAllBySession($session_id);
        $terms       = Term::where('session_id', $session_id)->orderBy('start_date')->get();

        return view('lesson-plans.index', compact('lessonPlans', 'classes', 'terms', 'session_id'));
    }

    public function create()
    {
        $this->authorize('create lesson plans');

        $session_id = $this->getSchoolCurrentSession();
        $classes    = $this->schoolClassRepository->getAllBySession($session_id);
        $terms      = Term::where('session_id', $session_id)->orderBy('start_date')->get();

        return view('lesson-plans.create', compact('classes', 'terms', 'session_id'));
    }

    public function store(Request $request)
    {
        $this->authorize('create lesson plans');

        $session_id = $this->getSchoolCurrentSession();

        $data = $request->validate([
            'title'                => 'required|string|max:200',
            'objectives'           => 'nullable|string',
            'content'              => 'nullable|string',
            'teaching_methods'     => 'nullable|string',
            'resources'            => 'nullable|string',
            'homework_description' => 'nullable|string',
            'notes'                => 'nullable|string',
            'planned_date'         => 'required|date',
            'duration_minutes'     => 'required|integer|min:1|max:480',
            'status'               => 'required|in:draft,approved,completed',
            'course_id'            => 'required|integer',
            'class_id'             => 'required|integer',
            'section_id'           => 'nullable|integer',
            'term_id'              => 'nullable|exists:terms,id',
            'curriculum_topic_id'  => 'nullable|exists:curriculum_topics,id',
        ]);

        $lessonPlan = LessonPlan::create(array_merge($data, [
            'teacher_id' => auth()->id(),
            'session_id' => $session_id,
        ]));

        return redirect()->route('lesson-plans.show', $lessonPlan->id)
            ->with('status', 'Lesson plan created.');
    }

    public function show(int $id)
    {
        $this->authorize('view lesson plans');

        $lessonPlan = LessonPlan::with([
            'teacher', 'course', 'schoolClass', 'section', 'term', 'curriculumTopic'
        ])->findOrFail($id);

        return view('lesson-plans.show', compact('lessonPlan'));
    }

    public function edit(int $id)
    {
        $this->authorize('create lesson plans');

        $lessonPlan = LessonPlan::findOrFail($id);

        // Only the owner or admin may edit
        if (auth()->id() !== $lessonPlan->teacher_id) {
            $this->authorize('view academic settings');
        }

        $session_id = $this->getSchoolCurrentSession();
        $classes    = $this->schoolClassRepository->getAllBySession($session_id);
        $terms      = Term::where('session_id', $session_id)->orderBy('start_date')->get();
        $topics     = CurriculumTopic::whereHas('curriculum', fn($q) =>
            $q->where('course_id', $lessonPlan->course_id)
        )->orderBy('order')->get();

        return view('lesson-plans.edit', compact('lessonPlan', 'classes', 'terms', 'topics', 'session_id'));
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('create lesson plans');

        $lessonPlan = LessonPlan::findOrFail($id);

        if (auth()->id() !== $lessonPlan->teacher_id) {
            $this->authorize('view academic settings');
        }

        $data = $request->validate([
            'title'                => 'required|string|max:200',
            'objectives'           => 'nullable|string',
            'content'              => 'nullable|string',
            'teaching_methods'     => 'nullable|string',
            'resources'            => 'nullable|string',
            'homework_description' => 'nullable|string',
            'notes'                => 'nullable|string',
            'planned_date'         => 'required|date',
            'duration_minutes'     => 'required|integer|min:1|max:480',
            'status'               => 'required|in:draft,approved,completed',
            'term_id'              => 'nullable|exists:terms,id',
            'curriculum_topic_id'  => 'nullable|exists:curriculum_topics,id',
        ]);

        $lessonPlan->update($data);

        return back()->with('status', 'Lesson plan updated.');
    }

    public function destroy(int $id)
    {
        $this->authorize('create lesson plans');

        $lessonPlan = LessonPlan::findOrFail($id);

        if (auth()->id() !== $lessonPlan->teacher_id) {
            $this->authorize('view academic settings');
        }

        $lessonPlan->delete();

        return redirect()->route('lesson-plans.index')->with('status', 'Lesson plan deleted.');
    }
}
