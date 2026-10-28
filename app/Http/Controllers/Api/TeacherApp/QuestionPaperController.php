<?php

namespace App\Http\Controllers\Api\TeacherApp;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Exam;
use App\Models\QuestionBank;
use App\Models\QuestionDownloadLog;
use App\Models\QuestionPaper;
use App\Models\QuestionPaperTemplate;
use App\Models\QuestionPrintLog;
use App\Models\QuestionQuestion;
use App\Models\QuestionSection;
use App\Models\SchoolClass;
use App\Services\AiQuestionExtractorService;
use App\Traits\SchoolSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuestionPaperController extends Controller
{
    use SchoolSession;

    /**
     * GET /api/teacher-app/question-papers
     * List question papers created by or accessible to teacher.
     */
    public function index(Request $request): JsonResponse
    {
        $sessionId = $this->getSchoolCurrentSession();
        $user = $request->user();

        $query = QuestionPaper::with(['template', 'sections.questions'])
            ->when($sessionId, fn($q) => $q->where('session_id', $sessionId))
            ->orderByDesc('id');

        // If not super admin or school admin, filter to papers created by teacher or subject
        if ($user && !$user->hasRole(['super-admin', 'admin', 'principal'])) {
            $query->where(function($q) use ($user) {
                $q->where('created_by', $user->id);
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($subject = $request->query('subject')) {
            $query->where('subject', 'like', "%{$subject}%");
        }

        if ($search = $request->query('search')) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('exam_name', 'like', "%{$search}%")
                  ->orWhere('class_label', 'like', "%{$search}%");
            });
        }

        $perPage = (int) ($request->query('per_page', 15));
        $papers = $query->paginate($perPage);

        $data = $papers->getCollection()->map(function ($p) {
            $totalQuestions = $p->sections->sum(fn($s) => $s->questions->count());
            $calculatedMarks = $p->sections->sum('total_marks');
            return [
                'id'              => $p->id,
                'title'           => $p->title,
                'exam_name'       => $p->exam_name,
                'subject'         => $p->subject,
                'class_label'     => $p->class_label,
                'duration'        => $p->duration,
                'full_marks'      => $p->full_marks ?: $calculatedMarks,
                'pass_marks'      => $p->pass_marks,
                'exam_date'       => $p->exam_date ? $p->exam_date->format('Y-m-d') : null,
                'status'          => $p->status,
                'status_badge'    => $p->status_badge,
                'is_locked'       => $p->is_locked,
                'is_editable'     => $p->is_editable,
                'sections_count'  => $p->sections->count(),
                'questions_count' => $totalQuestions,
                'calculated_marks'=> $calculatedMarks,
                'template_name'   => $p->template?->name,
                'created_at'      => $p->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $data,
            'meta'   => [
                'current_page' => $papers->currentPage(),
                'last_page'    => $papers->lastPage(),
                'total'        => $papers->total(),
                'per_page'     => $papers->perPage(),
            ],
        ]);
    }

    /**
     * GET /api/teacher-app/question-papers/meta
     * Metadata for dropdowns, forms, and creation wizards.
     */
    public function meta(Request $request): JsonResponse
    {
        $sessionId = $this->getSchoolCurrentSession();

        $templates = QuestionPaperTemplate::where('is_active', true)
            ->select('id', 'name', 'school_name', 'instructions_html')
            ->orderBy('name')
            ->get();

        $classes = SchoolClass::where('session_id', $sessionId)
            ->with(['sections' => fn($q) => $q->select('id', 'class_id', 'name')])
            ->select('id', 'name')
            ->orderBy('id')
            ->get();

        $courses = Course::uniqueByName()
            ->where('session_id', $sessionId)
            ->select('id', 'course_name', 'course_code')
            ->orderBy('course_name')
            ->get();

        $exams = Exam::where('session_id', $sessionId)
            ->select('id', 'name', 'start_date', 'end_date')
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'templates'      => $templates,
                'classes'        => $classes,
                'courses'        => $courses,
                'exams'          => $exams,
                'question_types' => QuestionBank::QUESTION_TYPES,
                'difficulties'   => QuestionBank::DIFFICULTIES,
                'bloom_levels'   => QuestionBank::BLOOM_LEVELS,
                'statuses'       => QuestionPaper::STATUSES,
            ],
        ]);
    }

    /**
     * POST /api/teacher-app/question-papers
     * Create manual question paper draft.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
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
            'paper_size'  => 'nullable|in:A4,Letter',
            'orientation' => 'nullable|in:portrait,landscape',
        ]);

        $sessionId = $this->getSchoolCurrentSession();
        $templateId = $validated['template_id'] ?? QuestionPaperTemplate::where('is_active', true)->value('id');

        $paper = QuestionPaper::create(array_merge($validated, [
            'template_id' => $templateId,
            'paper_size'  => $validated['paper_size'] ?? 'A4',
            'orientation' => $validated['orientation'] ?? 'portrait',
            'session_id'  => $sessionId,
            'created_by'  => $request->user()?->id,
            'status'      => 'draft',
        ]));

        return response()->json([
            'status'  => 'success',
            'message' => 'Question paper created successfully.',
            'data'    => $paper->load('template'),
        ], 201);
    }

    /**
     * POST /api/teacher-app/question-papers/auto-create
     * 1-Click Auto-Create full question paper directly from topic/prompt or syllabus file upload!
     */
    public function autoCreate(Request $request, AiQuestionExtractorService $aiExtractor): JsonResponse
    {
        $request->validate([
            'title'                    => 'required|string|max:255',
            'subject'                  => 'required|string|max:100',
            'class_label'              => 'nullable|string|max:100',
            'exam_name'                => 'nullable|string|max:255',
            'full_marks'               => 'nullable|numeric|min:1',
            'pass_marks'               => 'nullable|numeric|min:0',
            'duration'                 => 'nullable|string|max:50',
            'template_id'              => 'nullable|integer|exists:question_paper_templates,id',
            'difficulty'               => 'nullable|in:mixed,easy,medium,hard',
            'prompt_text'              => 'nullable|string|max:10000',
            'document_file'            => 'nullable|file|mimes:txt,pdf,docx,doc,jpg,jpeg,png,webp|max:10240',
        ]);

        $promptText = trim($request->input('prompt_text', ''));
        $hasFile    = $request->hasFile('document_file');

        if (!$hasFile && empty($promptText)) {
            $promptText = "Generate a standard comprehensive exam paper for {$request->input('subject')} Class {$request->input('class_label', '10')} covering core syllabus topics with multiple sections (Multiple Choice, Short Answer, and Long Analytical Questions).";
        }

        $sessionId  = $this->getSchoolCurrentSession();
        $templateId = $request->input('template_id') ?: QuestionPaperTemplate::where('is_active', true)->value('id');
        $difficulty = $request->input('difficulty', 'mixed');
        $classLabel = $request->input('class_label', 'Grade 10');

        DB::beginTransaction();
        try {
            // 1. Create Paper Header
            $paper = QuestionPaper::create([
                'title'       => $request->input('title'),
                'subject'     => $request->input('subject'),
                'class_label' => $classLabel,
                'exam_name'   => $request->input('exam_name', 'Terminal Examination'),
                'full_marks'  => $request->input('full_marks', 100),
                'pass_marks'  => $request->input('pass_marks', 40),
                'duration'    => $request->input('duration', '3 Hours'),
                'template_id' => $templateId,
                'paper_size'  => 'A4',
                'orientation' => 'portrait',
                'session_id'  => $sessionId,
                'created_by'  => $request->user()?->id,
                'status'      => 'draft',
            ]);

            // 2. Extract sections & questions via AI
            $sectionsData = $aiExtractor->generateSectionsAndQuestions(
                $request->file('document_file'),
                $promptText,
                $difficulty,
                $classLabel
            );

            if (empty($sectionsData)) {
                throw new \Exception('AI could not extract structured questions. Please verify your prompt or document.');
            }

            $createdSectionsCount  = 0;
            $createdQuestionsCount = 0;
            $sortOrder = 0;

            foreach ($sectionsData as $secItem) {
                if (empty($secItem['questions']) || !is_array($secItem['questions'])) {
                    continue;
                }

                $sortOrder++;
                $section = QuestionSection::create([
                    'school_id'    => $paper->school_id,
                    'paper_id'     => $paper->id,
                    'title'        => $secItem['title'] ?? ('Section ' . chr(64 + $sortOrder)),
                    'instructions' => $secItem['instructions'] ?? null,
                    'sort_order'   => $sortOrder,
                ]);
                $createdSectionsCount++;

                $qSortOrder = 0;
                foreach ($secItem['questions'] as $qItem) {
                    $qSortOrder++;
                    $qType = strtolower($qItem['question_type'] ?? 'short_answer');
                    if (!array_key_exists($qType, QuestionBank::QUESTION_TYPES)) {
                        $qType = 'short_answer';
                    }

                    QuestionQuestion::create([
                        'school_id'       => $paper->school_id,
                        'section_id'      => $section->id,
                        'question_type'   => $qType,
                        'question_text'   => $qItem['question_text'] ?? 'Question text',
                        'answer_text'     => $qItem['answer_text'] ?? null,
                        'options'         => !empty($qItem['options']) && is_array($qItem['options']) ? $qItem['options'] : null,
                        'correct_answer'  => $qItem['correct_answer'] ?? null,
                        'allocated_marks' => !empty($qItem['allocated_marks']) ? (float)$qItem['allocated_marks'] : 1.0,
                        'difficulty'      => in_array($qItem['difficulty'] ?? '', ['easy', 'medium', 'hard']) ? $qItem['difficulty'] : 'medium',
                        'bloom_taxonomy'  => $qItem['bloom_taxonomy'] ?? null,
                        'sort_order'      => $qSortOrder,
                    ]);
                    $createdQuestionsCount++;
                }

                $section->recalcMarks();
            }

            $paper->saveVersion("Auto-created with {$createdQuestionsCount} questions in {$createdSectionsCount} sections.");
            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "Successfully auto-created question paper with {$createdQuestionsCount} questions across {$createdSectionsCount} sections!",
                'data'    => $this->formatPaperDetail($paper->fresh(['template', 'sections.questions'])),
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Auto-create Question Paper error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to auto-create question paper: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/teacher-app/question-papers/{id}
     * Full question paper details with sections, questions, and formatting.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $paper = QuestionPaper::with(['template', 'sections.questions'])->find($id);

        if (!$paper) {
            return response()->json(['status' => 'error', 'message' => 'Question paper not found.'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $this->formatPaperDetail($paper),
        ]);
    }

    /**
     * PUT /api/teacher-app/question-papers/{id}
     * Update paper header metadata.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $paper = QuestionPaper::find($id);
        if (!$paper) {
            return response()->json(['status' => 'error', 'message' => 'Question paper not found.'], 404);
        }

        if ($paper->is_locked) {
            return response()->json(['status' => 'error', 'message' => 'Locked paper cannot be modified.'], 422);
        }

        $validated = $request->validate([
            'title'       => 'sometimes|string|max:255',
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
            'paper_size'  => 'nullable|in:A4,Letter',
            'orientation' => 'nullable|in:portrait,landscape',
            'status'      => 'nullable|in:draft,submitted,reviewed,approved,locked',
        ]);

        $paper->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Question paper updated successfully.',
            'data'    => $this->formatPaperDetail($paper->fresh(['template', 'sections.questions'])),
        ]);
    }

    /**
     * DELETE /api/teacher-app/question-papers/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $paper = QuestionPaper::find($id);
        if (!$paper) {
            return response()->json(['status' => 'error', 'message' => 'Question paper not found.'], 404);
        }

        if ($paper->is_locked) {
            return response()->json(['status' => 'error', 'message' => 'Locked paper cannot be deleted.'], 422);
        }

        $paper->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Question paper deleted successfully.',
        ]);
    }

    /**
     * POST /api/teacher-app/question-papers/{id}/ai-generate
     * Append or replace AI generated questions into an existing paper.
     */
    public function aiGenerate(Request $request, int $id, AiQuestionExtractorService $aiExtractor): JsonResponse
    {
        $paper = QuestionPaper::with('sections.questions')->find($id);
        if (!$paper) {
            return response()->json(['status' => 'error', 'message' => 'Question paper not found.'], 404);
        }

        if ($paper->is_locked) {
            return response()->json(['status' => 'error', 'message' => 'This paper is locked and cannot be modified.'], 422);
        }

        $request->validate([
            'document_file'            => 'nullable|file|mimes:txt,pdf,docx,doc,jpg,jpeg,png,webp|max:10240',
            'prompt_text'              => 'nullable|string|max:10000',
            'difficulty'               => 'nullable|in:mixed,easy,medium,hard',
            'mode'                     => 'nullable|in:append,replace',
            'section_id'               => 'nullable|integer',
            'new_section_title'        => 'nullable|string|max:150',
            'new_section_instructions' => 'nullable|string|max:500',
        ]);

        $difficulty = $request->input('difficulty', 'mixed');
        $mode       = $request->input('mode', 'append');
        $sectionId  = $request->input('section_id');
        $newTitle   = trim($request->input('new_section_title', ''));
        $newInst    = trim($request->input('new_section_instructions', ''));
        $promptText = trim($request->input('prompt_text', ''));
        $hasFile    = $request->hasFile('document_file');

        if (!$hasFile && empty($promptText)) {
            return response()->json(['status' => 'error', 'message' => 'Please upload a reference file or paste question instructions/text.'], 422);
        }

        try {
            $targetSection = $sectionId ? QuestionSection::where('paper_id', $paper->id)->find($sectionId) : null;
            $targetTitle   = $targetSection ? $targetSection->title : ($newTitle ?: null);

            $sectionsData = $aiExtractor->generateSectionsAndQuestions(
                $request->file('document_file'),
                $promptText,
                $difficulty,
                $paper->class_label ?: 'Secondary',
                $targetTitle
            );

            if (empty($sectionsData)) {
                return response()->json(['status' => 'error', 'message' => 'AI could not extract questions from the provided input.'], 422);
            }

            // Case A: Append to target section or new section
            if ($targetSection || !empty($newTitle)) {
                $section = $targetSection;
                if (!$section) {
                    $sortOrder = ($paper->sections()->max('sort_order') ?? 0) + 1;
                    $section = QuestionSection::create([
                        'school_id'    => $paper->school_id,
                        'paper_id'     => $paper->id,
                        'title'        => $newTitle,
                        'instructions' => $newInst ?: null,
                        'sort_order'   => $sortOrder,
                    ]);
                }

                $allQuestions = [];
                foreach ($sectionsData as $secItem) {
                    if (!empty($secItem['questions']) && is_array($secItem['questions'])) {
                        foreach ($secItem['questions'] as $qItem) {
                            $allQuestions[] = $qItem;
                        }
                    }
                }

                $qSortOrder = $section->questions()->max('sort_order') ?? 0;
                $createdCount = 0;

                foreach ($allQuestions as $qItem) {
                    $qSortOrder++;
                    $qType = strtolower($qItem['question_type'] ?? 'short_answer');
                    if (!array_key_exists($qType, QuestionBank::QUESTION_TYPES)) {
                        $qType = 'short_answer';
                    }

                    QuestionQuestion::create([
                        'school_id'       => $paper->school_id,
                        'section_id'      => $section->id,
                        'question_type'   => $qType,
                        'question_text'   => $qItem['question_text'] ?? 'Question',
                        'answer_text'     => $qItem['answer_text'] ?? null,
                        'options'         => !empty($qItem['options']) && is_array($qItem['options']) ? $qItem['options'] : null,
                        'correct_answer'  => $qItem['correct_answer'] ?? null,
                        'allocated_marks' => !empty($qItem['allocated_marks']) ? (float)$qItem['allocated_marks'] : 1.0,
                        'difficulty'      => in_array($qItem['difficulty'] ?? '', ['easy', 'medium', 'hard']) ? $qItem['difficulty'] : 'medium',
                        'bloom_taxonomy'  => $qItem['bloom_taxonomy'] ?? null,
                        'sort_order'      => $qSortOrder,
                    ]);
                    $createdCount++;
                }

                $section->recalcMarks();
                $paper->fresh()->saveVersion("AI added {$createdCount} questions to {$section->title}");

                return response()->json([
                    'status'  => 'success',
                    'message' => "Successfully added {$createdCount} question(s) to {$section->title}!",
                    'data'    => $this->formatPaperDetail($paper->fresh(['template', 'sections.questions'])),
                ]);
            }

            // Case B: Global paper replace or append
            if ($mode === 'replace') {
                foreach ($paper->sections as $sec) {
                    $sec->questions()->delete();
                    $sec->delete();
                }
            }

            $createdSectionsCount  = 0;
            $createdQuestionsCount = 0;
            $sortOrder = $paper->sections()->max('sort_order') ?? 0;

            foreach ($sectionsData as $secItem) {
                if (empty($secItem['questions']) || !is_array($secItem['questions'])) {
                    continue;
                }

                $sortOrder++;
                $section = QuestionSection::create([
                    'school_id'    => $paper->school_id,
                    'paper_id'     => $paper->id,
                    'title'        => $secItem['title'] ?? ('Section ' . chr(64 + $sortOrder)),
                    'instructions' => $secItem['instructions'] ?? null,
                    'sort_order'   => $sortOrder,
                ]);
                $createdSectionsCount++;

                $qSortOrder = 0;
                foreach ($secItem['questions'] as $qItem) {
                    $qSortOrder++;
                    $qType = strtolower($qItem['question_type'] ?? 'short_answer');
                    if (!array_key_exists($qType, QuestionBank::QUESTION_TYPES)) {
                        $qType = 'short_answer';
                    }

                    QuestionQuestion::create([
                        'school_id'       => $paper->school_id,
                        'section_id'      => $section->id,
                        'question_type'   => $qType,
                        'question_text'   => $qItem['question_text'] ?? 'Question',
                        'answer_text'     => $qItem['answer_text'] ?? null,
                        'options'         => !empty($qItem['options']) && is_array($qItem['options']) ? $qItem['options'] : null,
                        'correct_answer'  => $qItem['correct_answer'] ?? null,
                        'allocated_marks' => !empty($qItem['allocated_marks']) ? (float)$qItem['allocated_marks'] : 1.0,
                        'difficulty'      => in_array($qItem['difficulty'] ?? '', ['easy', 'medium', 'hard']) ? $qItem['difficulty'] : 'medium',
                        'bloom_taxonomy'  => $qItem['bloom_taxonomy'] ?? null,
                        'sort_order'      => $qSortOrder,
                    ]);
                    $createdQuestionsCount++;
                }

                $section->recalcMarks();
            }

            $paper->fresh()->saveVersion("AI generated {$createdQuestionsCount} questions in {$createdSectionsCount} sections.");

            return response()->json([
                'status'  => 'success',
                'message' => "Successfully generated {$createdQuestionsCount} question(s) across {$createdSectionsCount} section(s).",
                'data'    => $this->formatPaperDetail($paper->fresh(['template', 'sections.questions'])),
            ]);

        } catch (\Throwable $e) {
            Log::error('AI Paper Generation error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'AI Generation Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/teacher-app/question-papers/{id}/sections
     */
    public function addSection(Request $request, int $id): JsonResponse
    {
        $paper = QuestionPaper::find($id);
        if (!$paper) {
            return response()->json(['status' => 'error', 'message' => 'Question paper not found.'], 404);
        }

        $validated = $request->validate([
            'title'        => 'required|string|max:150',
            'instructions' => 'nullable|string|max:500',
        ]);

        $sortOrder = ($paper->sections()->max('sort_order') ?? 0) + 1;
        $section = QuestionSection::create([
            'school_id'    => $paper->school_id,
            'paper_id'     => $paper->id,
            'title'        => $validated['title'],
            'instructions' => $validated['instructions'] ?? null,
            'sort_order'   => $sortOrder,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Section created successfully.',
            'data'    => $section->load('questions'),
        ], 201);
    }

    /**
     * PUT /api/teacher-app/question-papers/sections/{sectionId}
     */
    public function updateSection(Request $request, int $sectionId): JsonResponse
    {
        $section = QuestionSection::find($sectionId);
        if (!$section) {
            return response()->json(['status' => 'error', 'message' => 'Section not found.'], 404);
        }

        $validated = $request->validate([
            'title'        => 'required|string|max:150',
            'instructions' => 'nullable|string|max:500',
        ]);

        $section->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Section updated successfully.',
            'data'    => $section->load('questions'),
        ]);
    }

    /**
     * DELETE /api/teacher-app/question-papers/sections/{sectionId}
     */
    public function deleteSection(Request $request, int $sectionId): JsonResponse
    {
        $section = QuestionSection::find($sectionId);
        if (!$section) {
            return response()->json(['status' => 'error', 'message' => 'Section not found.'], 404);
        }

        $section->questions()->delete();
        $section->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Section deleted successfully.',
        ]);
    }

    /**
     * POST /api/teacher-app/question-papers/sections/{sectionId}/questions
     */
    public function addQuestion(Request $request, int $sectionId): JsonResponse
    {
        $section = QuestionSection::find($sectionId);
        if (!$section) {
            return response()->json(['status' => 'error', 'message' => 'Section not found.'], 404);
        }

        $validated = $request->validate([
            'question_type'   => 'required|in:' . implode(',', array_keys(QuestionBank::QUESTION_TYPES)),
            'question_text'   => 'required|string',
            'answer_text'     => 'nullable|string',
            'options'         => 'nullable|array',
            'correct_answer'  => 'nullable|string',
            'allocated_marks' => 'required|numeric|min:0.25',
            'difficulty'      => 'required|in:easy,medium,hard',
            'bloom_taxonomy'  => 'nullable|string',
            'bank_id'         => 'nullable|integer|exists:question_bank,id',
        ]);

        $qSortOrder = ($section->questions()->max('sort_order') ?? 0) + 1;

        $question = QuestionQuestion::create([
            'school_id'       => $section->school_id,
            'section_id'      => $section->id,
            'question_type'   => $validated['question_type'],
            'question_text'   => $validated['question_text'],
            'answer_text'     => $validated['answer_text'] ?? null,
            'options'         => $validated['options'] ?? null,
            'correct_answer'  => $validated['correct_answer'] ?? null,
            'allocated_marks' => $validated['allocated_marks'],
            'difficulty'      => $validated['difficulty'],
            'bloom_taxonomy'  => $validated['bloom_taxonomy'] ?? null,
            'bank_id'         => $validated['bank_id'] ?? null,
            'sort_order'      => $qSortOrder,
        ]);

        $section->recalcMarks();

        return response()->json([
            'status'  => 'success',
            'message' => 'Question added successfully.',
            'data'    => $question,
        ], 201);
    }

    /**
     * PUT /api/teacher-app/question-papers/questions/{questionId}
     */
    public function updateQuestion(Request $request, int $questionId): JsonResponse
    {
        $question = QuestionQuestion::find($questionId);
        if (!$question) {
            return response()->json(['status' => 'error', 'message' => 'Question not found.'], 404);
        }

        $validated = $request->validate([
            'question_type'   => 'sometimes|in:' . implode(',', array_keys(QuestionBank::QUESTION_TYPES)),
            'question_text'   => 'sometimes|string',
            'answer_text'     => 'nullable|string',
            'options'         => 'nullable|array',
            'correct_answer'  => 'nullable|string',
            'allocated_marks' => 'sometimes|numeric|min:0.25',
            'difficulty'      => 'sometimes|in:easy,medium,hard',
            'bloom_taxonomy'  => 'nullable|string',
        ]);

        $question->update($validated);
        $question->section?->recalcMarks();

        return response()->json([
            'status'  => 'success',
            'message' => 'Question updated successfully.',
            'data'    => $question,
        ]);
    }

    /**
     * DELETE /api/teacher-app/question-papers/questions/{questionId}
     */
    public function deleteQuestion(Request $request, int $questionId): JsonResponse
    {
        $question = QuestionQuestion::find($questionId);
        if (!$question) {
            return response()->json(['status' => 'error', 'message' => 'Question not found.'], 404);
        }

        $section = $question->section;
        $question->delete();
        $section?->recalcMarks();

        return response()->json([
            'status'  => 'success',
            'message' => 'Question deleted successfully.',
        ]);
    }

    /**
     * GET /api/teacher-app/question-papers/{id}/bank-search
     * Search question bank to import into the paper.
     */
    public function bankSearch(Request $request, int $id): JsonResponse
    {
        $paper = QuestionPaper::find($id);
        if (!$paper) {
            return response()->json(['status' => 'error', 'message' => 'Question paper not found.'], 404);
        }

        $query = QuestionBank::query();

        if ($subject = $request->query('subject', $paper->subject)) {
            $query->where('subject', 'like', "%{$subject}%");
        }
        if ($type = $request->query('type')) {
            $query->where('question_type', $type);
        }
        if ($diff = $request->query('difficulty')) {
            $query->where('difficulty', $diff);
        }
        if ($q = $request->query('q')) {
            $query->where('question_text', 'like', "%{$q}%");
        }

        $items = $query->limit(50)->get();

        return response()->json([
            'status' => 'success',
            'data'   => $items,
        ]);
    }

    /**
     * POST /api/teacher-app/question-papers/sections/{sectionId}/import-bank
     * Import multiple questions from Question Bank into section.
     */
    public function importFromBank(Request $request, int $sectionId): JsonResponse
    {
        $section = QuestionSection::find($sectionId);
        if (!$section) {
            return response()->json(['status' => 'error', 'message' => 'Section not found.'], 404);
        }

        $validated = $request->validate([
            'bank_ids'   => 'required|array|min:1',
            'bank_ids.*' => 'integer|exists:question_bank,id',
        ]);

        $bankQuestions = QuestionBank::whereIn('id', $validated['bank_ids'])->get();
        $qSortOrder = $section->questions()->max('sort_order') ?? 0;
        $importedCount = 0;

        foreach ($bankQuestions as $bq) {
            $qSortOrder++;
            QuestionQuestion::create([
                'school_id'       => $section->school_id,
                'section_id'      => $section->id,
                'question_type'   => $bq->question_type,
                'question_text'   => $bq->question_text,
                'answer_text'     => $bq->answer_text,
                'options'         => $bq->options,
                'correct_answer'  => $bq->correct_answer,
                'allocated_marks' => $bq->default_marks ?: 1.0,
                'difficulty'      => $bq->difficulty ?: 'medium',
                'bloom_taxonomy'  => $bq->bloom_taxonomy,
                'bank_id'         => $bq->id,
                'sort_order'      => $qSortOrder,
            ]);
            $importedCount++;
        }

        $section->recalcMarks();

        return response()->json([
            'status'  => 'success',
            'message' => "Successfully imported {$importedCount} questions from Question Bank.",
            'data'    => $section->load('questions'),
        ]);
    }

    /**
     * Helper to format complete question paper response for mobile app.
     */
    private function formatPaperDetail(QuestionPaper $paper): array
    {
        return [
            'id'              => $paper->id,
            'title'           => $paper->title,
            'exam_name'       => $paper->exam_name,
            'subject'         => $paper->subject,
            'class_label'     => $paper->class_label,
            'duration'        => $paper->duration,
            'full_marks'      => (float) ($paper->full_marks ?: $paper->total_marks),
            'pass_marks'      => (float) $paper->pass_marks,
            'exam_date'       => $paper->exam_date ? $paper->exam_date->format('Y-m-d') : null,
            'paper_size'      => $paper->paper_size ?: 'A4',
            'orientation'     => $paper->orientation ?: 'portrait',
            'status'          => $paper->status,
            'status_badge'    => $paper->status_badge,
            'is_locked'       => $paper->is_locked,
            'is_editable'     => $paper->is_editable,
            'template'        => $paper->template ? [
                'id'                => $paper->template->id,
                'name'              => $paper->template->name,
                'school_name'       => $paper->template->school_name,
                'instructions_html' => $paper->template->instructions_html,
            ] : null,
            'sections'        => $paper->sections->map(function ($sec) {
                return [
                    'id'           => $sec->id,
                    'title'        => $sec->title,
                    'instructions' => $sec->instructions,
                    'total_marks'  => (float) $sec->total_marks,
                    'sort_order'   => $sec->sort_order,
                    'questions'    => $sec->questions->map(function ($q) {
                        return [
                            'id'              => $q->id,
                            'numbering'       => $q->numbering,
                            'question_type'   => $q->question_type,
                            'question_text'   => $q->question_text,
                            'answer_text'     => $q->answer_text,
                            'options'         => $q->options,
                            'correct_answer'  => $q->correct_answer,
                            'allocated_marks' => (float) $q->allocated_marks,
                            'difficulty'      => $q->difficulty,
                            'bloom_taxonomy'  => $q->bloom_taxonomy,
                            'sort_order'      => $qSortOrder = $q->sort_order,
                        ];
                    }),
                ];
            }),
            'calculated_total_marks' => (float) $paper->sections->sum('total_marks'),
            'total_questions_count'  => (int) $paper->sections->sum(fn($s) => $s->questions->count()),
            'created_at'             => $paper->created_at?->toIso8601String(),
            'updated_at'             => $paper->updated_at?->toIso8601String(),
        ];
    }
}
