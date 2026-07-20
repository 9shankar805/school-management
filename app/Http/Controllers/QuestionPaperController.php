<?php

namespace App\Http\Controllers;

use App\Interfaces\QuestionBankInterface;
use App\Interfaces\QuestionPaperInterface;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\SemesterInterface;
use App\Models\QuestionBank;
use App\Models\QuestionDownloadLog;
use App\Models\QuestionPaper;
use App\Models\QuestionPaperTemplate;
use App\Models\QuestionPrintLog;
use App\Models\QuestionSection;
use App\Traits\SchoolSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class QuestionPaperController extends Controller
{
    use SchoolSession;

    public function __construct(
        protected SchoolSessionInterface $schoolSessionRepository,
        protected SchoolClassInterface   $schoolClassRepository,
        protected SemesterInterface      $semesterRepository,
        protected QuestionPaperInterface $paperRepo,
        protected QuestionBankInterface  $bankRepo,
    ) {
        $this->middleware(['auth']);
    }

    // ── Paper list ────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $this->authorize('viewAny', QuestionPaper::class);

        $sessionId = $this->getSchoolCurrentSession();

        $userId = auth()->user()->hasAnyRole(['teacher', 'class-teacher'])
            ? auth()->id()
            : null;

        $papers   = $this->paperRepo->getAll($sessionId, $userId);
        $statuses = QuestionPaper::STATUSES;

        return view('question-papers.index', compact('papers', 'statuses', 'sessionId'));
    }

    // ── Create / Edit paper ───────────────────────────────────────────────────

    public function create()
    {
        $this->authorize('create', QuestionPaper::class);

        $sessionId = $this->getSchoolCurrentSession();
        $templates = QuestionPaperTemplate::where('is_active', true)->orderBy('name')->get();
        $classes   = $this->schoolClassRepository->getAllBySession($sessionId);
        $semesters = $this->semesterRepository->getAll($sessionId);
        $exams     = \App\Models\Exam::where('session_id', $sessionId)->with('course')->get();
        $courses   = \App\Models\Course::where('session_id', $sessionId)->orderBy('course_name')->get();

        return view('question-papers.create', compact(
            'templates', 'classes', 'semesters', 'exams', 'courses', 'sessionId'
        ));
    }

    public function store(Request $request)
    {
        $this->authorize('create', QuestionPaper::class);

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

        $paper = $this->paperRepo->create(array_merge($data, [
            'session_id' => $sessionId,
            'created_by' => auth()->id(),
            'status'     => 'draft',
        ]));

        return redirect()->route('question-papers.edit', $paper->id)
            ->with('status', 'Paper created. Add sections and questions below.');
    }

    public function show(int $id)
    {
        $paper = $this->paperRepo->findById($id);
        $this->authorize('view', $paper);

        return view('question-papers.show', compact('paper'));
    }

    public function edit(int $id)
    {
        $paper = $this->paperRepo->findById($id);
        $this->authorize('update', $paper);

        if ($paper->is_locked) {
            return redirect()->route('question-papers.show', $id)
                ->withErrors('This paper is locked and cannot be edited.');
        }

        $subjects  = $this->bankRepo->getSubjects();
        $bankTypes = QuestionBank::QUESTION_TYPES;
        $bankDiffs = QuestionBank::DIFFICULTIES;
        $blooms    = QuestionBank::BLOOM_LEVELS;

        return view('question-papers.editor', compact(
            'paper', 'subjects', 'bankTypes', 'bankDiffs', 'blooms'
        ));
    }

    public function update(Request $request, int $id)
    {
        $paper = QuestionPaper::findOrFail($id);
        $this->authorize('update', $paper);

        if ($paper->is_locked) {
            return back()->withErrors('This paper is locked and cannot be edited.');
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

        $this->paperRepo->update($id, $data);

        return back()->with('status', 'Paper details saved.');
    }

    public function destroy(int $id)
    {
        $paper = QuestionPaper::findOrFail($id);
        $this->authorize('delete', $paper);

        if ($paper->is_locked) {
            return back()->withErrors('Locked papers cannot be deleted.');
        }

        $this->paperRepo->delete($id);
        return redirect()->route('question-papers.index')->with('status', 'Paper deleted.');
    }

    // ── Section / Question AJAX endpoints ─────────────────────────────────────

    public function addSection(Request $request, int $paperId)
    {
        $this->authorize('create', QuestionPaper::class);

        $data    = $request->validate([
            'title'        => 'required|string|max:100',
            'instructions' => 'nullable|string|max:500',
        ]);
        $section = $this->paperRepo->addSection($paperId, $data);

        return response()->json(['section' => $section, 'success' => true]);
    }

    public function updateSection(Request $request, int $sectionId)
    {
        $this->authorize('create', QuestionPaper::class);

        $data    = $request->validate([
            'title'        => 'required|string|max:100',
            'instructions' => 'nullable|string|max:500',
        ]);
        $section = $this->paperRepo->updateSection($sectionId, $data);

        return response()->json(['section' => $section, 'success' => true]);
    }

    public function deleteSection(int $sectionId)
    {
        $this->authorize('create', QuestionPaper::class);
        $this->paperRepo->deleteSection($sectionId);
        return response()->json(['success' => true]);
    }

    public function addQuestion(Request $request, int $sectionId)
    {
        $this->authorize('create', QuestionPaper::class);

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

        $question = $this->paperRepo->addQuestion($sectionId, $data);
        $this->paperRepo->renumberQuestions($question->section->paper_id);

        return response()->json(['question' => $question->load('images'), 'success' => true]);
    }

    public function updateQuestion(Request $request, int $questionId)
    {
        $this->authorize('create', QuestionPaper::class);

        $data = $request->validate([
            'question_text'   => 'required|string',
            'answer_text'     => 'nullable|string',
            'options'         => 'nullable|array',
            'allocated_marks' => 'required|numeric|min:0.25',
            'difficulty'      => 'required|in:easy,medium,hard',
            'chapter'         => 'nullable|string|max:100',
        ]);

        $q = $this->paperRepo->updateQuestion($questionId, $data);
        return response()->json(['question' => $q, 'success' => true]);
    }

    public function deleteQuestion(int $questionId)
    {
        $this->authorize('create', QuestionPaper::class);
        $this->paperRepo->deleteQuestion($questionId);
        return response()->json(['success' => true]);
    }

    public function reorderSections(Request $request, int $paperId)
    {
        $this->authorize('create', QuestionPaper::class);
        $request->validate(['order' => 'required|array']);
        $this->paperRepo->reorderSections($paperId, $request->order);
        return response()->json(['success' => true]);
    }

    public function reorderQuestions(Request $request, int $sectionId)
    {
        $this->authorize('create', QuestionPaper::class);
        $request->validate(['order' => 'required|array']);
        $this->paperRepo->reorderQuestions($sectionId, $request->order);
        $this->paperRepo->renumberQuestions(
            QuestionSection::find($sectionId)?->paper_id ?? 0
        );
        return response()->json(['success' => true]);
    }

    /** GET /question-papers/{paperId}/bank-search — AJAX question-bank picker */
    public function bankSearch(Request $request, int $paperId)
    {
        $questions = $this->bankRepo->search($request->only([
            'subject', 'chapter', 'question_type', 'difficulty', 'search',
        ]), 20);

        return response()->json($questions);
    }

    // ── PDF export ────────────────────────────────────────────────────────────

    public function exportPdf(int $id)
    {
        $paper = $this->paperRepo->findById($id);
        $this->authorize('view', $paper);

        QuestionDownloadLog::create([
            'paper_id'      => $id,
            'downloaded_by' => auth()->id(),
            'format'        => 'pdf',
            'ip_address'    => request()->ip(),
        ]);

        if ($paper->is_locked) {
            $paper->update(['status' => 'printed']);
            QuestionPrintLog::create([
                'paper_id'   => $id,
                'printed_by' => auth()->id(),
                'copies'     => 1,
                'ip_address' => request()->ip(),
            ]);
        }

        $template    = $paper->template;
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

    // ── DOCX export ───────────────────────────────────────────────────────────

    public function exportDocx(int $id)
    {
        $paper = $this->paperRepo->findById($id);
        $this->authorize('view', $paper);

        QuestionDownloadLog::create([
            'paper_id'      => $id,
            'downloaded_by' => auth()->id(),
            'format'        => 'docx',
            'ip_address'    => request()->ip(),
        ]);

        $phpWord = new \PhpOffice\PhpWord\PhpWord();

        // ── Page setup ───────────────────────────────────────────────────────
        $isLandscape = ($paper->orientation ?? 'portrait') === 'landscape';
        $section = $phpWord->addSection([
            'orientation'  => $isLandscape ? 'landscape' : 'portrait',
            'paperSize'    => strtoupper($paper->paper_size ?? 'A4'),
            'marginTop'    => 720,   // ~1.25 cm in twips
            'marginBottom' => 720,
            'marginLeft'   => 1000,
            'marginRight'  => 1000,
        ]);

        // ── Font + paragraph styles ──────────────────────────────────────────
        $titleStyle    = ['name' => 'Arial', 'size' => 14, 'bold' => true, 'color' => '000000'];
        $headingStyle  = ['name' => 'Arial', 'size' => 12, 'bold' => true];
        $labelStyle    = ['name' => 'Arial', 'size' => 10, 'bold' => true];
        $normalStyle   = ['name' => 'Arial', 'size' => 10];
        $centerPara    = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER];
        $boldUnderline = ['name' => 'Arial', 'size' => 10, 'bold' => true, 'underline' => 'single'];

        // ── Header block ─────────────────────────────────────────────────────
        $template = $paper->template;
        $schoolName = $template?->school_name ?? config('app.name');

        $section->addText($schoolName, $titleStyle, $centerPara);
        $section->addText($paper->exam_name ?? 'Examination', $headingStyle, $centerPara);

        // Meta row: Subject | Class | Date | Time | Full Marks | Pass Marks
        $metaTable = $section->addTable(['borderSize' => 0, 'cellMargin' => 60]);
        $metaTable->addRow();
        $metaTable->addCell(3000)->addText('Subject: ' . ($paper->subject ?? '—'), $normalStyle);
        $metaTable->addCell(3000)->addText('Class: '   . ($paper->class_label ?? '—'), $normalStyle);
        $metaTable->addCell(3000)->addText('Date: '    . ($paper->exam_date?->format('d M Y') ?? '—'), $normalStyle);
        $metaTable->addRow();
        $metaTable->addCell(3000)->addText('Time: '       . ($paper->duration   ?? '—'), $normalStyle);
        $metaTable->addCell(3000)->addText('Full Marks: ' . ($paper->full_marks  ?? '—'), $normalStyle);
        $metaTable->addCell(3000)->addText('Pass Marks: ' . ($paper->pass_marks  ?? '—'), $normalStyle);

        $section->addTextBreak(1);

        // Instructions
        if ($template?->instructions_html) {
            $section->addText('Instructions:', $boldUnderline);
            // Strip HTML tags for plain-text DOCX output
            $section->addText(strip_tags($template->instructions_html), $normalStyle);
            $section->addTextBreak(1);
        }

        $section->addLine(['weight' => 1, 'color' => '000000', 'width' => 400, 'height' => 0]);
        $section->addTextBreak(1);

        // ── Sections & Questions ─────────────────────────────────────────────
        foreach ($paper->sections as $qSection) {
            $section->addText(
                strtoupper($qSection->title) . '   [Total: ' . $qSection->total_marks . ' marks]',
                $headingStyle
            );

            if ($qSection->instructions) {
                $section->addText($qSection->instructions, ['name' => 'Arial', 'size' => 9, 'italic' => true]);
            }

            $section->addTextBreak(1);

            foreach ($qSection->questions as $q) {
                $prefix   = $q->numbering ? $q->numbering . '. ' : '';
                $markText = '  [' . $q->allocated_marks . ' mark' . ($q->allocated_marks != 1 ? 's' : '') . ']';

                // Question text (strip HTML)
                $section->addText(
                    $prefix . strip_tags($q->question_text) . $markText,
                    $normalStyle
                );

                // MCQ options
                if ($q->question_type === 'mcq' && ! empty($q->options)) {
                    $optionLetters = ['a', 'b', 'c', 'd', 'e', 'f'];
                    foreach (array_values($q->options) as $i => $opt) {
                        $letter = $optionLetters[$i] ?? ($i + 1);
                        $section->addText(
                            '    (' . $letter . ')  ' . strip_tags(is_array($opt) ? ($opt['text'] ?? $opt) : $opt),
                            $normalStyle
                        );
                    }
                }

                // True/False
                if ($q->question_type === 'true_false') {
                    $section->addText('    (a) True     (b) False', $normalStyle);
                }

                $section->addTextBreak(1);
            }

            $section->addTextBreak(1);
        }

        // ── Footer / Signature ────────────────────────────────────────────────
        if ($template?->signature_name) {
            $section->addTextBreak(2);
            $section->addText($template->signature_name,  $labelStyle);
            $section->addText($template->signature_title ?? '', $normalStyle);
        }

        // ── Watermark (text box in header) ───────────────────────────────────
        if ($template?->show_watermark && $template->watermark_text) {
            $header = $section->addHeader();
            $header->addText(
                strtoupper($template->watermark_text),
                ['name' => 'Arial', 'size' => 60, 'bold' => true, 'color' => 'E0E0E0'],
                $centerPara
            );
        }

        // ── Stream response ───────────────────────────────────────────────────
        $filename  = 'question-paper-' . $paper->id . '.docx';
        $writer    = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $tmpFile   = tempnam(sys_get_temp_dir(), 'qp_');
        $writer->save($tmpFile);

        return response()->download($tmpFile, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ])->deleteFileAfterSend(true);
    }

    // ── Version history ───────────────────────────────────────────────────────

    public function versions(int $id)
    {
        $paper = QuestionPaper::findOrFail($id);
        $this->authorize('view', $paper);

        $versions = $this->paperRepo->getVersions($id);

        return view('question-papers.versions', compact('paper', 'versions'));
    }

    public function restoreVersion(int $versionId)
    {
        $this->authorize('create', QuestionPaper::class);

        $paper = $this->paperRepo->restoreVersion($versionId);

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
