<?php

namespace App\Http\Controllers;

use App\Models\QuestionBank;
use App\Models\QuestionDownloadLog;
use App\Models\QuestionPaper;
use App\Models\QuestionPaperTemplate;
use App\Models\QuestionPrintLog;
use App\Repositories\QuestionBankRepository;
use App\Repositories\QuestionPaperRepository;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SemesterInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QuestionPaperController extends Controller
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

    // ── Paper list ────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $this->authorize('view exams');

        $sessionId = $this->getSchoolCurrentSession();
        $repo      = new QuestionPaperRepository();

        $userId = auth()->user()->hasAnyRole(['teacher', 'class-teacher'])
            ? auth()->id()
            : null;

        $papers   = $repo->getAll($sessionId, $userId);
        $statuses = QuestionPaper::STATUSES;

        return view('question-papers.index', compact('papers', 'statuses', 'sessionId'));
    }

    // ── Create / Edit paper ───────────────────────────────────────────────────

    public function create()
    {
        $this->authorize('create exams');

        $sessionId  = $this->getSchoolCurrentSession();
        $templates  = QuestionPaperTemplate::where('is_active', true)->orderBy('name')->get();
        $classes    = $this->schoolClassRepository->getAllBySession($sessionId);
        $semesters  = $this->semesterRepository->getAll($sessionId);
        $exams      = \App\Models\Exam::where('session_id', $sessionId)->with('course')->get();
        $courses    = \App\Models\Course::where('session_id', $sessionId)->orderBy('course_name')->get();

        return view('question-papers.create', compact(
            'templates', 'classes', 'semesters', 'exams', 'courses', 'sessionId'
        ));
    }

    public function store(Request $request)
    {
        $this->authorize('create exams');

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'template_id' => 'nullable|integer|exists:question_paper_templates,id',
            'exam_id'     => 'nullable|integer|exists:exams,id',
            'class_id'    => 'nullable|integer|exists:school_classes,id',
            'section_id'  => 'nullable|integer',
            'course_id'   => 'nullable|integer|exists:courses,id',
            'exam_name'   => 'nullable|string|max:255',
            'subject'     => 'nullable|string|max:100',
            'class_label' => 'nullable|string|max:100',
            'duration'    => 'nullable|string|max:50',
            'full_marks'  => 'nullable|numeric|min:0',
            'pass_marks'  => 'nullable|numeric|min:0',
            'exam_date'   => 'nullable|date',
            'paper_size'  => 'required|in:A4,Letter',
            'orientation' => 'required|in:portrait,landscape',
        ]);

        $sessionId = $this->getSchoolCurrentSession();

        $repo  = new QuestionPaperRepository();
        $paper = $repo->create(array_merge($data, [
            'session_id' => $sessionId,
            'created_by' => auth()->id(),
            'status'     => 'draft',
        ]));

        return redirect()->route('question-papers.edit', $paper->id)
            ->with('status', 'Paper created. Add sections and questions below.');
    }

    public function edit(int $id)
    {
        $this->authorize('create exams');

        $repo      = new QuestionPaperRepository();
        $bankRepo  = new QuestionBankRepository();
        $paper     = $repo->findById($id);

        if ($paper->is_locked) {
            return redirect()->route('question-papers.show', $id)
                ->withError('This paper is locked and cannot be edited.');
        }

        $subjects  = $bankRepo->getSubjects();
        $bankTypes = QuestionBank::QUESTION_TYPES;
        $bankDiffs = QuestionBank::DIFFICULTIES;
        $blooms    = QuestionBank::BLOOM_LEVELS;

        return view('question-papers.editor', compact(
            'paper', 'subjects', 'bankTypes', 'bankDiffs', 'blooms'
        ));
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('create exams');

        $paper = QuestionPaper::findOrFail($id);

        if ($paper->is_locked) {
            return back()->withError('This paper is locked and cannot be edited.');
        }

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'exam_name'   => 'nullable|string|max:255',
            'subject'     => 'nullable|string|max:100',
            'class_label' => 'nullable|string|max:100',
            'duration'    => 'nullable|string|max:50',
            'full_marks'  => 'nullable|numeric|min:0',
            'pass_marks'  => 'nullable|numeric|min:0',
            'exam_date'   => 'nullable|date',
            'paper_size'  => 'required|in:A4,Letter',
            'orientation' => 'required|in:portrait,landscape',
        ]);

        (new QuestionPaperRepository())->update($id, $data);

        return back()->with('status', 'Paper details saved.');
    }

    public function destroy(int $id)
    {
        $this->authorize('create exams');

        $paper = QuestionPaper::findOrFail($id);
        if ($paper->is_locked) {
            return back()->withError('Locked papers cannot be deleted.');
        }

        (new QuestionPaperRepository())->delete($id);
        return redirect()->route('question-papers.index')->with('status', 'Paper deleted.');
    }

    public function show(int $id)
    {
        $this->authorize('view exams');

        $repo  = new QuestionPaperRepository();
        $paper = $repo->findById($id);

        return view('question-papers.show', compact('paper'));
    }

    // ── Section / Question AJAX endpoints ─────────────────────────────────────

    public function addSection(Request $request, int $paperId)
    {
        $this->authorize('create exams');

        $data = $request->validate([
            'title'        => 'required|string|max:100',
            'instructions' => 'nullable|string|max:500',
        ]);

        $section = (new QuestionPaperRepository())->addSection($paperId, $data);
        return response()->json(['section' => $section, 'success' => true]);
    }

    public function updateSection(Request $request, int $sectionId)
    {
        $this->authorize('create exams');

        $data = $request->validate([
            'title'        => 'required|string|max:100',
            'instructions' => 'nullable|string|max:500',
        ]);

        $section = (new QuestionPaperRepository())->updateSection($sectionId, $data);
        return response()->json(['section' => $section, 'success' => true]);
    }

    public function deleteSection(int $sectionId)
    {
        $this->authorize('create exams');
        (new QuestionPaperRepository())->deleteSection($sectionId);
        return response()->json(['success' => true]);
    }

    public function addQuestion(Request $request, int $sectionId)
    {
        $this->authorize('create exams');

        $data = $request->validate([
            'question_type'   => 'required|in:' . implode(',', array_keys(QuestionBank::QUESTION_TYPES)),
            'question_text'   => 'required|string',
            'answer_text'     => 'nullable|string',
            'options'         => 'nullable|array',
            'allocated_marks' => 'required|numeric|min:0.25',
            'difficulty'      => 'required|in:easy,medium,hard',
            'chapter'         => 'nullable|string|max:100',
            'bloom_taxonomy'  => 'nullable|string',
            'bank_id'         => 'nullable|integer|exists:question_bank,id',
        ]);

        $question = (new QuestionPaperRepository())->addQuestion($sectionId, $data);
        (new QuestionPaperRepository())->renumberQuestions($question->section->paper_id);

        return response()->json(['question' => $question->load('images'), 'success' => true]);
    }

    public function updateQuestion(Request $request, int $questionId)
    {
        $this->authorize('create exams');

        $data = $request->validate([
            'question_text'   => 'required|string',
            'answer_text'     => 'nullable|string',
            'options'         => 'nullable|array',
            'allocated_marks' => 'required|numeric|min:0.25',
            'difficulty'      => 'required|in:easy,medium,hard',
            'chapter'         => 'nullable|string|max:100',
        ]);

        $q = (new QuestionPaperRepository())->updateQuestion($questionId, $data);
        return response()->json(['question' => $q, 'success' => true]);
    }

    public function deleteQuestion(int $questionId)
    {
        $this->authorize('create exams');
        (new QuestionPaperRepository())->deleteQuestion($questionId);
        return response()->json(['success' => true]);
    }

    public function reorderSections(Request $request, int $paperId)
    {
        $this->authorize('create exams');
        $request->validate(['order' => 'required|array']);
        (new QuestionPaperRepository())->reorderSections($paperId, $request->order);
        return response()->json(['success' => true]);
    }

    public function reorderQuestions(Request $request, int $sectionId)
    {
        $this->authorize('create exams');
        $request->validate(['order' => 'required|array']);
        (new QuestionPaperRepository())->reorderQuestions($sectionId, $request->order);
        (new QuestionPaperRepository())->renumberQuestions(
            \App\Models\QuestionSection::find($sectionId)?->paper_id ?? 0
        );
        return response()->json(['success' => true]);
    }

    /** GET /question-papers/{paperId}/bank-search — AJAX search for inserting bank questions */
    public function bankSearch(Request $request, int $paperId)
    {
        $repo      = new QuestionBankRepository();
        $questions = $repo->search($request->only([
            'subject', 'chapter', 'question_type', 'difficulty', 'search'
        ]), 20);

        return response()->json($questions);
    }

    // ── PDF & DOCX export ─────────────────────────────────────────────────────

    public function exportPdf(int $id)
    {
        $this->authorize('view exams');

        $repo  = new QuestionPaperRepository();
        $paper = $repo->findById($id);

        // Log the download
        QuestionDownloadLog::create([
            'paper_id'      => $id,
            'downloaded_by' => auth()->id(),
            'format'        => 'pdf',
            'ip_address'    => request()->ip(),
        ]);

        // Mark as printed if locked
        if ($paper->is_locked) {
            $paper->update(['status' => 'printed']);
            QuestionPrintLog::create([
                'paper_id'   => $id,
                'printed_by' => auth()->id(),
                'copies'     => 1,
                'ip_address' => request()->ip(),
            ]);
        }

        $template = $paper->template;
        $orientation = $paper->orientation ?? 'portrait';
        $paperSize   = $paper->paper_size  ?? 'A4';

        $pdf = Pdf::loadView('question-papers.pdf.paper', compact('paper', 'template'))
            ->setPaper(strtolower($paperSize), $orientation)
            ->setOptions([
                'defaultFont'     => 'sans-serif',
                'dpi'             => 120,
                'isRemoteEnabled' => true,
            ]);

        return $pdf->stream("question-paper-{$paper->id}.pdf");
    }

    // ── Version history ───────────────────────────────────────────────────────

    public function versions(int $id)
    {
        $this->authorize('view exams');

        $repo     = new QuestionPaperRepository();
        $paper    = QuestionPaper::findOrFail($id);
        $versions = $repo->getVersions($id);

        return view('question-papers.versions', compact('paper', 'versions'));
    }

    public function restoreVersion(int $versionId)
    {
        $this->authorize('create exams');

        $paper = (new QuestionPaperRepository())->restoreVersion($versionId);

        return redirect()->route('question-papers.edit', $paper->id)
            ->with('status', 'Version restored successfully.');
    }

    /** POST — manual auto-save from editor (AJAX) */
    public function autoSave(Request $request, int $id)
    {
        $paper = QuestionPaper::findOrFail($id);

        if ($paper->created_by !== auth()->id() && ! auth()->user()->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (! $paper->is_locked) {
            $paper->fresh()->saveVersion('Auto-save');
        }

        return response()->json(['success' => true, 'saved_at' => now()->toTimeString()]);
    }
}
