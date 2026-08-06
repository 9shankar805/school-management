<?php
namespace App\Http\Controllers;

use App\Models\{Attendance, Exam, Homework, HomeworkSubmission, Mark, Project, ProjectSubmission, Promotion, SchoolClass, Section, User};
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradebookController extends Controller
{
    use SchoolSession;
    protected $schoolSessionRepository;

    public function __construct(SchoolSessionInterface $schoolSessionRepository)
    {
        $this->middleware(['auth']);
        $this->schoolSessionRepository = $schoolSessionRepository;
    }

    /**
     * Gradebook — per-class marks grid across all exams/homework/projects.
     * GET /gradebook
     */
    public function index(Request $request)
    {
        $sessionId = $this->getSchoolCurrentSession();
        $classId   = (int) $request->query('class_id', 0);
        $sectionId = (int) $request->query('section_id', 0);

        $classes = SchoolClass::where('session_id', $sessionId)->with('sections')->get();

        if (!$classId || !$sectionId) {
            return view('gradebook.index', compact('classes', 'sessionId', 'classId', 'sectionId'));
        }

        $class   = SchoolClass::find($classId);
        $section = Section::find($sectionId);

        // Get all students in this class/section via promotions
        $studentIds = Promotion::where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->pluck('student_id');

        $students = User::whereIn('id', $studentIds)->orderBy('first_name')->get();

        // Get all exams for this class
        $exams = Exam::where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->orderBy('start_date')
            ->get();

        // Get marks
        $marks = Mark::where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->get()
            ->groupBy('student_id')
            ->map(fn($m) => $m->keyBy('exam_id'));

        // Compute averages
        $studentAverages = $students->mapWithKeys(function ($student) use ($marks, $exams) {
            $studentMarks = $marks->get($student->id, collect());
            $total = $studentMarks->sum('marks');
            $count = $studentMarks->count();
            return [$student->id => $count > 0 ? round($total / $count, 1) : 0];
        });

        return view('gradebook.index', compact(
            'classes', 'sessionId', 'classId', 'sectionId',
            'class', 'section', 'students', 'exams', 'marks', 'studentAverages'
        ));
    }

    /**
     * Student Progress Tracker — per-student academic summary.
     * GET /student-progress/{studentId}
     */
    public function studentProgress(Request $request, int $studentId)
    {
        $sessionId = $this->getSchoolCurrentSession();
        $student   = User::findOrFail($studentId);

        // Class/section
        $promotion = Promotion::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->latest()
            ->first();

        // Marks
        $marks = Mark::with('exam', 'course')
            ->where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->orderBy('created_at')
            ->get();

        $avgMarks = $marks->avg('marks') ?? 0;

        // Attendance
        $attendanceStats = Attendance::statsFor($studentId, $sessionId);

        // Homework submissions
        $homeworkSubmissions = HomeworkSubmission::with('homework')
            ->where('student_id', $studentId)
            ->whereHas('homework', fn($q) => $q->where('session_id', $sessionId))
            ->latest()
            ->get();

        // Project submissions
        $projectSubmissions = ProjectSubmission::with('project')
            ->where('student_id', $studentId)
            ->whereHas('project', fn($q) => $q->where('session_id', $sessionId))
            ->latest()
            ->get();

        return view('gradebook.student-progress', compact(
            'student', 'promotion', 'sessionId',
            'marks', 'avgMarks',
            'attendanceStats',
            'homeworkSubmissions',
            'projectSubmissions'
        ));
    }
}
