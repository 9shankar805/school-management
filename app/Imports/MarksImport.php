<?php

namespace App\Imports;

use App\Models\Exam;
use App\Models\ExamRule;
use App\Models\Mark;
use App\Models\Promotion;
use App\Repositories\MarkRepository;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithValidation;

class MarksImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public int    $classId;
    public int    $sectionId;
    public int    $semesterId;
    public int    $sessionId;
    public string $courseId;          // 'all' or numeric string
    public int    $schoolId;

    /** Rows that had validation warnings — ['row' => n, 'msg' => '...'] */
    public array  $warnings = [];
    /** Count of marks successfully imported */
    public int    $imported  = 0;

    // Map of heading column name → [course_id|exam_id, fm]
    // Populated in collection() once we know the headings.
    private array $subjectColMap = [];

    public function __construct(
        int    $classId,
        int    $sectionId,
        int    $semesterId,
        int    $sessionId,
        string $courseId,
        int    $schoolId
    ) {
        $this->classId    = $classId;
        $this->sectionId  = $sectionId;
        $this->semesterId = $semesterId;
        $this->sessionId  = $sessionId;
        $this->courseId   = $courseId;
        $this->schoolId   = $schoolId;
    }

    /**
     * WithHeadingRow normalises headings by lowercasing + replacing spaces/special
     * chars with underscores. We normalise our expected keys the same way.
     */
    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            $this->warnings[] = ['row' => 0, 'msg' => 'The uploaded file has no data rows.'];
            return;
        }

        // ── Build subject column map from the first row keys ──────────────
        // The template headings look like: "Nepali (FM:75)" → normalised to
        // "nepali_fm75" by Maatwebsite. We find them by excluding the fixed cols.
        $fixedCols = ['student_id', 'roll_no', 'student_name'];
        $firstRow  = $rows->first();

        // Fetch all exams for this class/semester so we can map course→exam
        $exams = Exam::where('session_id', $this->sessionId)
            ->where('class_id', $this->classId)
            ->when($this->semesterId > 0, fn($q) => $q->where('semester_id', $this->semesterId))
            ->with('examRule')
            ->get();

        // exam_id → total_marks
        $fmByExamId = $exams->mapWithKeys(fn($e) => [
            $e->id => (float)($e->examRule?->total_marks ?? 100),
        ])->all();

        // course_id → exam (pick latest)
        $examByCourse = $exams->keyBy('course_id');

        // Also keep the original (un-normalised) heading text so we can parse FM/PM values
        // that the teacher may have changed in the file (e.g. "Nepali (FM:50,PM:20)").
        // Maatwebsite normalises headings, so we need the raw heading row separately.
        // We read row 4 of the raw file via the Collection — it is the first row Maatwebsite
        // gives us after stripping the heading row itself, so we extract it from the original
        // heading keys using the WithHeadingRow contract (keys are already normalised).
        // Strategy: parse FM from the normalised key itself — e.g. "nepali_fm_50_" → FM=50.

        foreach (array_keys($firstRow->toArray()) as $col) {
            if (in_array($col, $fixedCols)) continue;

            // ── Parse FM value from the heading key if present ────────────
            // Template heading: "Nepali (FM:75)" → normalised: "nepali__fm_75_"
            // We look for the pattern  fm_(\d+)  anywhere in the key.
            $fmFromHeading = null;
            $pmFromHeading = null;
            if (preg_match('/fm[_:\s]*(\d+(?:\.\d+)?)/i', $col, $fmMatch)) {
                $fmFromHeading = (float) $fmMatch[1];
            }
            if (preg_match('/pm[_:\s]*(\d+(?:\.\d+)?)/i', $col, $pmMatch)) {
                $pmFromHeading = (float) $pmMatch[1];
            }

            // ── Match column to exam by course name ───────────────────────
            foreach ($exams as $exam) {
                $normalised = strtolower(preg_replace('/[^a-z0-9]/i', '_', $exam->course?->course_name ?? ''));
                if (str_starts_with($col, $normalised) || str_starts_with($col, substr($normalised, 0, 6))) {
                    $dbFm = (float)($exam->examRule?->total_marks ?? 100);
                    $dbPm = (float)($exam->examRule?->pass_marks ?? 40);

                    // If teacher changed FM/PM in the heading, update exam_rules
                    $effectiveFm = $fmFromHeading ?? $dbFm;
                    $effectivePm = $pmFromHeading ?? $dbPm;

                    if ($fmFromHeading !== null && $fmFromHeading !== $dbFm
                        || $pmFromHeading !== null && $pmFromHeading !== $dbPm) {
                        ExamRule::updateOrCreate(
                            ['exam_id' => $exam->id, 'session_id' => $this->sessionId],
                            [
                                'school_id'    => $this->schoolId ?: null,
                                'total_marks'  => $effectiveFm,
                                'pass_marks'   => $effectivePm,
                            ]
                        );
                        // Clamp existing marks to the new full marks so none exceed it.
                        ExamRule::clampMarksToFullMarks($exam->id, $effectiveFm);
                        $this->warnings[] = [
                            'row' => 0,
                            'msg' => "FM/PM updated for \"{$exam->course?->course_name}\": FM={$effectiveFm}, PM={$effectivePm}",
                        ];
                    }

                    $this->subjectColMap[$col] = [
                        'course_id' => $exam->course_id,
                        'exam_id'   => $exam->id,
                        'fm'        => $effectiveFm,
                        'pm'        => $effectivePm,
                    ];
                    break;
                }
            }
        }

        // Fallback: if course-name matching fails (e.g. name mismatch), map by column
        // position — D onwards corresponds to the exam order
        if (empty($this->subjectColMap)) {
            $subCols = array_values(array_diff(array_keys($firstRow->toArray()), $fixedCols));
            $examList = $exams->values();
            foreach ($subCols as $i => $col) {
                if (isset($examList[$i])) {
                    $exam = $examList[$i];
                    $fmFromHeading = null;
                    $pmFromHeading = null;
                    if (preg_match('/fm[_:\s]*(\d+(?:\.\d+)?)/i', $col, $fmMatch)) {
                        $fmFromHeading = (float) $fmMatch[1];
                    }
                    if (preg_match('/pm[_:\s]*(\d+(?:\.\d+)?)/i', $col, $pmMatch)) {
                        $pmFromHeading = (float) $pmMatch[1];
                    }
                    $dbFm = (float)($exam->examRule?->total_marks ?? 100);
                    $dbPm = (float)($exam->examRule?->pass_marks ?? 40);
                    $effectiveFm = $fmFromHeading ?? $dbFm;
                    $effectivePm = $pmFromHeading ?? $dbPm;

                    if (($fmFromHeading !== null && $fmFromHeading !== $dbFm)
                        || ($pmFromHeading !== null && $pmFromHeading !== $dbPm)) {
                        ExamRule::updateOrCreate(
                            ['exam_id' => $exam->id, 'session_id' => $this->sessionId],
                            ['school_id' => $this->schoolId ?: null, 'total_marks' => $effectiveFm, 'pass_marks' => $effectivePm]
                        );
                        // Clamp existing marks to the new full marks so none exceed it.
                        ExamRule::clampMarksToFullMarks($exam->id, $effectiveFm);
                    }

                    $this->subjectColMap[$col] = [
                        'course_id' => $exam->course_id,
                        'exam_id'   => $exam->id,
                        'fm'        => $effectiveFm,
                        'pm'        => $effectivePm,
                    ];
                }
            }
        }

        if (empty($this->subjectColMap)) {
            $this->warnings[] = ['row' => 0, 'msg' => 'Could not map any subject columns. Make sure you downloaded the official template for this class/semester.'];
            return;
        }

        // ── Build section map for students ────────────────────────────────
        $studentSectionMap = Promotion::where('session_id', $this->sessionId)
            ->where('class_id', $this->classId)
            ->pluck('section_id', 'student_id')
            ->all();

        // ── Process rows ──────────────────────────────────────────────────
        $markRows = [];
        $rowNum   = 4; // headings are row 4 in the template (3 title rows + heading)

        foreach ($rows as $row) {
            $rowNum++;
            $rowArr = $row->toArray();

            $studentId = (int)($rowArr['student_id'] ?? 0);
            if ($studentId <= 0) {
                $this->warnings[] = ['row' => $rowNum, 'msg' => 'Missing or invalid student_id — row skipped.'];
                continue;
            }

            $actualSection = $this->sectionId > 0
                ? $this->sectionId
                : (int)($studentSectionMap[$studentId] ?? 0);

            foreach ($this->subjectColMap as $col => $subInfo) {
                $rawVal = $rowArr[$col] ?? null;

                // Skip truly blank cells (absent)
                if ($rawVal === null || $rawVal === '') continue;

                $markVal = (float)$rawVal;
                if ($markVal < 0) {
                    $this->warnings[] = ['row' => $rowNum, 'msg' => "Negative mark ({$rawVal}) in column [{$col}] for student #{$studentId} — skipped."];
                    continue;
                }

                // Cap to FM
                if ($markVal > $subInfo['fm']) {
                    $this->warnings[] = ['row' => $rowNum, 'msg' => "Mark {$markVal} exceeds FM {$subInfo['fm']} for [{$col}] / student #{$studentId} — capped."];
                    $markVal = $subInfo['fm'];
                }

                $markRows[] = [
                    'school_id'  => $this->schoolId,
                    'class_id'   => $this->classId,
                    'section_id' => $actualSection,
                    'student_id' => $studentId,
                    'course_id'  => $subInfo['course_id'],
                    'exam_id'    => $subInfo['exam_id'],
                    'marks'      => $markVal,
                    'session_id' => $this->sessionId,
                ];
            }
        }

        if (empty($markRows)) {
            $this->warnings[] = ['row' => 0, 'msg' => 'No mark values found in the file. Cells may all be blank.'];
            return;
        }

        $repo = new MarkRepository();
        $repo->create($markRows);
        $this->imported = count($markRows);
    }
}
