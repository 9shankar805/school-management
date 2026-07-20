<?php

namespace App\Http\Controllers;

use App\Models\SchoolSession;
use App\Models\User;
use App\Repositories\ResultRepository;
use App\Traits\SchoolSession as SchoolSessionTrait;
use App\Interfaces\SchoolSessionInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class TranscriptController extends Controller
{
    use SchoolSessionTrait;

    protected $schoolSessionRepository;

    public function __construct(SchoolSessionInterface $schoolSessionRepository)
    {
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->middleware(['auth', 'can:view marks']);
    }

    /**
     * GET /results/transcript/{studentId}
     * Full academic transcript for a student — all semesters in the current session.
     */
    public function show(Request $request, int $studentId)
    {
        // Students can only view their own transcript
        if (auth()->user()->hasRole('student') && auth()->id() !== $studentId) {
            abort(403);
        }

        $sessionId = $this->getSchoolCurrentSession();
        $student   = User::with('academic_info', 'promotions.schoolClass', 'promotions.section')
            ->findOrFail($studentId);

        $repo = new ResultRepository();

        // Get all semesters that have final marks for this student in this session
        $semesterIds = \App\Models\FinalMark::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->distinct()
            ->pluck('semester_id');

        $semesterResults = [];
        foreach ($semesterIds as $semId) {
            $classId   = \App\Models\FinalMark::where('student_id', $studentId)
                ->where('session_id', $sessionId)
                ->where('semester_id', $semId)
                ->value('class_id') ?? 0;

            $sectionId = \App\Models\FinalMark::where('student_id', $studentId)
                ->where('session_id', $sessionId)
                ->where('semester_id', $semId)
                ->value('section_id') ?? 0;

            $result = $repo->getStudentResult($studentId, $semId, $classId, $sectionId, $sessionId);
            $semesterResults[] = $result;
        }

        $cgpaData  = $repo->getCgpa($studentId, $student->academic_info?->class_id ?? 0, $sessionId);
        $session   = SchoolSession::find($sessionId);

        return view('results.transcript', compact(
            'student', 'semesterResults', 'cgpaData', 'session', 'sessionId'
        ));
    }

    /**
     * GET /results/transcript/{studentId}/pdf
     * Official transcript PDF.
     */
    public function pdf(Request $request, int $studentId)
    {
        if (auth()->user()->hasRole('student') && auth()->id() !== $studentId) {
            abort(403);
        }

        $sessionId = $this->getSchoolCurrentSession();
        $student   = User::with('academic_info', 'promotions.schoolClass', 'promotions.section')
            ->findOrFail($studentId);

        $repo        = new ResultRepository();
        $semesterIds = \App\Models\FinalMark::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->distinct()->pluck('semester_id');

        $semesterResults = [];
        foreach ($semesterIds as $semId) {
            $classId   = \App\Models\FinalMark::where('student_id', $studentId)
                ->where('session_id', $sessionId)
                ->where('semester_id', $semId)
                ->value('class_id') ?? 0;
            $sectionId = \App\Models\FinalMark::where('student_id', $studentId)
                ->where('session_id', $sessionId)
                ->where('semester_id', $semId)
                ->value('section_id') ?? 0;
            $semesterResults[] = $repo->getStudentResult($studentId, $semId, $classId, $sectionId, $sessionId);
        }

        $cgpaData = $repo->getCgpa($studentId, $student->academic_info?->class_id ?? 0, $sessionId);
        $session  = SchoolSession::find($sessionId);

        $pdf = Pdf::loadView('results.pdf.transcript', compact(
            'student', 'semesterResults', 'cgpaData', 'session'
        ))
        ->setPaper('A4', 'portrait')
        ->setOptions(['defaultFont' => 'sans-serif', 'dpi' => 120, 'isRemoteEnabled' => true]);

        return $pdf->stream("transcript-{$student->full_name}.pdf");
    }
}
