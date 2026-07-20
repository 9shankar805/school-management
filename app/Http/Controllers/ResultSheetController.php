<?php

namespace App\Http\Controllers;

use App\Repositories\ResultRepository;
use App\Repositories\GradingSystemRepository;
use App\Repositories\GradeRuleRepository;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Section;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SemesterInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ResultSheetController extends Controller
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
        $this->middleware(['auth', 'can:view marks']);
    }

    // ── Selector form ─────────────────────────────────────────────────────────

    /**
     * GET /results
     * Form to pick class + semester + section.
     */
    public function index(Request $request)
    {
        $sessionId = $this->getSchoolCurrentSession();
        $classes   = $this->schoolClassRepository->getAllBySession($sessionId);
        $semesters = $this->semesterRepository->getAll($sessionId);

        return view('results.index', compact('classes', 'semesters', 'sessionId'));
    }

    // ── Class result sheet (web) ──────────────────────────────────────────────

    /**
     * GET /results/class
     * Full class result table with GPA + rankings.
     */
    public function classResult(Request $request)
    {
        $request->validate([
            'class_id'    => 'required|integer',
            'section_id'  => 'required|integer',
            'semester_id' => 'required|integer',
        ]);

        $sessionId  = $this->getSchoolCurrentSession();
        $classId    = (int) $request->class_id;
        $sectionId  = (int) $request->section_id;
        $semesterId = (int) $request->semester_id;

        $repo      = new ResultRepository();
        $results   = $repo->getClassResults($semesterId, $classId, $sectionId, $sessionId);
        $class     = SchoolClass::find($classId);
        $section   = Section::find($sectionId);
        $semester  = Semester::find($semesterId);

        // Course names for the table header (from first student's courses)
        $courses   = collect($results->first()['courses'] ?? [])->pluck('course');

        return view('results.class', compact(
            'results', 'class', 'section', 'semester',
            'courses', 'sessionId'
        ));
    }

    // ── Single student result ─────────────────────────────────────────────────

    /**
     * GET /results/student/{studentId}
     */
    public function studentResult(Request $request, int $studentId)
    {
        $request->validate([
            'class_id'    => 'required|integer',
            'section_id'  => 'required|integer',
            'semester_id' => 'required|integer',
        ]);

        // Students can only view their own results
        if (auth()->user()->hasRole('student') && auth()->id() !== $studentId) {
            abort(403);
        }

        $sessionId  = $this->getSchoolCurrentSession();
        $classId    = (int) $request->class_id;
        $sectionId  = (int) $request->section_id;
        $semesterId = (int) $request->semester_id;

        $repo   = new ResultRepository();
        $result = $repo->getStudentResult($studentId, $semesterId, $classId, $sectionId, $sessionId);

        return view('results.student', compact('result', 'sessionId'));
    }

    // ── Merit list ────────────────────────────────────────────────────────────

    /**
     * GET /results/merit
     */
    public function meritList(Request $request)
    {
        $request->validate([
            'class_id'    => 'required|integer',
            'section_id'  => 'required|integer',
            'semester_id' => 'required|integer',
        ]);

        $sessionId  = $this->getSchoolCurrentSession();
        $classId    = (int) $request->class_id;
        $sectionId  = (int) $request->section_id;
        $semesterId = (int) $request->semester_id;

        $repo     = new ResultRepository();
        $results  = $repo->getClassResults($semesterId, $classId, $sectionId, $sessionId);
        $class    = SchoolClass::find($classId);
        $section  = Section::find($sectionId);
        $semester = Semester::find($semesterId);

        return view('results.merit-list', compact(
            'results', 'class', 'section', 'semester', 'sessionId'
        ));
    }

    // ── PDF exports ───────────────────────────────────────────────────────────

    /**
     * GET /results/class/pdf
     * Class result sheet as PDF (landscape A4).
     */
    public function classResultPdf(Request $request)
    {
        $request->validate([
            'class_id'    => 'required|integer',
            'section_id'  => 'required|integer',
            'semester_id' => 'required|integer',
        ]);

        $sessionId  = $this->getSchoolCurrentSession();
        $classId    = (int) $request->class_id;
        $sectionId  = (int) $request->section_id;
        $semesterId = (int) $request->semester_id;

        $repo     = new ResultRepository();
        $results  = $repo->getClassResults($semesterId, $classId, $sectionId, $sessionId);
        $class    = SchoolClass::find($classId);
        $section  = Section::find($sectionId);
        $semester = Semester::find($semesterId);
        $courses  = collect($results->first()['courses'] ?? [])->pluck('course');

        $pdf = Pdf::loadView('results.pdf.class-sheet', compact(
            'results', 'class', 'section', 'semester', 'courses'
        ))
        ->setPaper('A4', 'landscape')
        ->setOptions(['defaultFont' => 'sans-serif', 'dpi' => 96]);

        return $pdf->stream("result-sheet-{$class->class_name}-{$semester->semester_name}.pdf");
    }

    /**
     * GET /results/student/{studentId}/report-card
     * Individual report card PDF.
     */
    public function reportCard(Request $request, int $studentId)
    {
        $request->validate([
            'class_id'    => 'required|integer',
            'section_id'  => 'required|integer',
            'semester_id' => 'required|integer',
        ]);

        if (auth()->user()->hasRole('student') && auth()->id() !== $studentId) {
            abort(403);
        }

        $sessionId  = $this->getSchoolCurrentSession();
        $repo       = new ResultRepository();
        $result     = $repo->getStudentResult(
            $studentId,
            (int) $request->semester_id,
            (int) $request->class_id,
            (int) $request->section_id,
            $sessionId
        );

        $pdf = Pdf::loadView('results.pdf.report-card', compact('result'))
            ->setPaper('A4', 'portrait')
            ->setOptions(['defaultFont' => 'sans-serif', 'dpi' => 120, 'isRemoteEnabled' => true]);

        return $pdf->stream("report-card-{$result['student']->full_name}.pdf");
    }

    /**
     * GET /results/class/excel
     */
    public function classResultExcel(Request $request)
    {
        $request->validate([
            'class_id'    => 'required|integer',
            'section_id'  => 'required|integer',
            'semester_id' => 'required|integer',
        ]);

        $sessionId  = $this->getSchoolCurrentSession();
        $classId    = (int) $request->class_id;
        $sectionId  = (int) $request->section_id;
        $semesterId = (int) $request->semester_id;

        $repo     = new ResultRepository();
        $results  = $repo->getClassResults($semesterId, $classId, $sectionId, $sessionId);
        $class    = SchoolClass::findOrFail($classId);
        $semester = Semester::findOrFail($semesterId);

        return Excel::download(
            new \App\Exports\MarksReportExport($results->toArray(), $class, $semester),
            "results-{$class->class_name}-{$semester->semester_name}.xlsx"
        );
    }

    // ── Performance analytics ─────────────────────────────────────────────────

    /**
     * GET /results/analytics
     */
    public function analytics(Request $request)
    {
        $sessionId  = $this->getSchoolCurrentSession();
        $classes    = $this->schoolClassRepository->getAllBySession($sessionId);
        $semesters  = $this->semesterRepository->getAll($sessionId);

        $classId    = (int) $request->query('class_id',    $classes->first()?->id ?? 0);
        $semesterId = (int) $request->query('semester_id', $semesters->first()?->id ?? 0);

        $repo               = new ResultRepository();
        $subjectAverages    = $repo->getSubjectAverages($sessionId, $semesterId, $classId);
        $gradeDistribution  = $repo->getGradeDistribution($sessionId, $semesterId, $classId);
        $classResults       = $repo->getClassResults($semesterId, $classId, 0, $sessionId);

        $topStudents        = $classResults->take(5);
        $avgGpa             = $classResults->isNotEmpty()
            ? round($classResults->avg('gpa'), 2)
            : 0;

        return view('results.analytics', compact(
            'classes', 'semesters', 'classId', 'semesterId',
            'subjectAverages', 'gradeDistribution',
            'topStudents', 'avgGpa', 'sessionId'
        ));
    }
}
