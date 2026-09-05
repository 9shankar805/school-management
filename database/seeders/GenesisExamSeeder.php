<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\ExamRule;
use App\Models\ExamSchedule;
use App\Models\FinalMark;
use App\Models\GradingSystem;
use App\Models\GradeRule;
use App\Models\Mark;
use App\Models\Promotion;
use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\Semester;
use App\Models\School;
use App\Models\Section;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * GenesisExamSeeder
 * ─────────────────────────────────────────────────────────────────────────────
 * Seeds complete 1st Terminal and 2nd Terminal exam data for Genesis Global
 * School (school_id = 3, session 2026-2027).
 *
 * HOW THE EXAM SYSTEM WORKS — EXPLAINED
 * ──────────────────────────────────────
 *
 * 1. TERMINAL EXAMS (Nepali school calendar)
 *    The Nepali/Indian school year is split into two terminals:
 *      • 1st Terminal (First Term)  → April–September 2026
 *      • 2nd Terminal (Second Term) → October 2026–March 2027
 *    Each terminal has its own exam sitting for every subject per class.
 *
 * 2. EXAM MODEL STRUCTURE
 *    Each `Exam` record represents ONE subject's exam in ONE class for ONE
 *    terminal. So "1st Terminal Mathematics for Grade 6" is one row.
 *    The `ExamRule` linked to it sets full_marks (100) and pass_marks (40).
 *
 * 3. RAW MARKS (marks table)
 *    When a teacher enters marks, one `Mark` row is saved per student per
 *    exam. This is the raw, per-sitting score.
 *
 * 4. FINAL MARKS (final_marks table)
 *    After all exam sittings are complete, the teacher publishes final marks
 *    to the `final_marks` table (one row per student per course per semester).
 *    This is what drives the report card.
 *
 *    In a two-terminal system like Nepal:
 *      • 1st terminal final mark  = the 1st Terminal exam mark directly
 *      • 2nd terminal final mark  = the 2nd Terminal exam mark directly
 *      • Annual/combined result   = weighted average: (T1 × 0.4) + (T2 × 0.6)
 *        (The 60% weighting on T2 is standard in Nepal as it covers more syllabus)
 *
 * 5. GRADING SCALE (GPA system)
 *    The app uses a GPA-based letter-grade system, NOT percentage divisions.
 *    For each FinalMark, the grade is resolved from GradeRule thresholds:
 *
 *      A+ → 4.0 GPA  (90–100 marks)   Distinction / Outstanding
 *      A  → 3.7 GPA  (80–89 marks)    1st Division equivalent
 *      B  → 3.0 GPA  (70–79 marks)    2nd Division equivalent
 *      C  → 2.0 GPA  (60–69 marks)    3rd Division equivalent
 *      D  → 1.0 GPA  (50–59 marks)    Pass (lower boundary)
 *      F  → 0.0 GPA  (0–49 marks)     Fail / Needs improvement
 *
 * 6. DIVISION ASSIGNMENT (traditional Nepali system mapping)
 *    Division is not a stored field — it is computed from the student's
 *    overall percentage or GPA across all subjects in a semester:
 *
 *      1st Division → GPA ≥ 3.6 (or percentage ≥ 80%)  [A / A+]
 *      2nd Division → GPA ≥ 2.8 (or percentage ≥ 60%)  [B / C+]
 *      3rd Division → GPA ≥ 1.0 (or percentage ≥ 45%)  [D / C-]
 *      Fail         → GPA < 1.0 or any subject with F grade
 *
 *    In this seeder we target a realistic distribution:
 *      ~25% of students score 1st Division (GPA ≥ 3.6)
 *      ~35% score 2nd Division (GPA 2.8–3.59)
 *      ~30% score 3rd Division (GPA 1.0–2.79)
 *      ~10% fail one or more subjects
 *
 * 7. WHAT IS SEEDED
 *    For ALL 14 classes × 8 subjects × 2 terminals:
 *      ✓ GradingSystem + GradeRule rows (A+/A/B/C/D/F scale)
 *      ✓ Exam records (1st Terminal + 2nd Terminal per course per class)
 *      ✓ ExamRule rows (full_marks=100, pass_marks=40)
 *      ✓ ExamSchedule rows (dates in exam period blocks)
 *      ✓ Mark rows (raw per-exam marks for all 601 students)
 *      ✓ FinalMark rows (terminal final marks for report cards)
 */
class GenesisExamSeeder extends Seeder
{
    // ── Seeder configuration ─────────────────────────────────────────────────

    /** Exam marks distribution by percentage band — controls division spread */
    private const MARK_BANDS = [
        // [probability, min, max]  — probability is cumulative weight
        ['weight' => 10, 'min' => 90, 'max' => 100],  // A+ — outstanding (~10%)
        ['weight' => 15, 'min' => 80, 'max' => 89],   // A  — 1st division (~15%)
        ['weight' => 25, 'min' => 70, 'max' => 79],   // B  — 2nd division (~25%)
        ['weight' => 25, 'min' => 60, 'max' => 69],   // C  — 3rd division (~25%)
        ['weight' => 15, 'min' => 50, 'max' => 59],   // D  — pass         (~15%)
        ['weight' => 10, 'min' => 20, 'max' => 39],   // F  — fail         (~10%)
    ];

    /** Grade rules applied to every grading system */
    private const GRADE_RULES = [
        ['grade' => 'A+', 'point' => 4.0, 'start_at' => 90,    'end_at' => 100],
        ['grade' => 'A',  'point' => 3.7, 'start_at' => 80,    'end_at' => 89.99],
        ['grade' => 'B',  'point' => 3.0, 'start_at' => 70,    'end_at' => 79.99],
        ['grade' => 'C',  'point' => 2.0, 'start_at' => 60,    'end_at' => 69.99],
        ['grade' => 'D',  'point' => 1.0, 'start_at' => 50,    'end_at' => 59.99],
        ['grade' => 'F',  'point' => 0.0, 'start_at' => 0,     'end_at' => 49.99],
    ];

    public function run(): void
    {
        $school  = School::findOrFail(3);
        $session = SchoolSession::where('school_id', $school->id)
            ->where('session_name', '2026-2027')
            ->firstOrFail();

        $term1 = Semester::where('school_id', $school->id)
            ->where('session_id', $session->id)
            ->where('semester_name', 'First Term 2026')
            ->firstOrFail();

        $term2 = Semester::where('school_id', $school->id)
            ->where('session_id', $session->id)
            ->where('semester_name', 'Second Term 2026')
            ->firstOrFail();

        $classes = SchoolClass::where('school_id', $school->id)
            ->where('session_id', $session->id)
            ->with(['sections'])
            ->orderBy('id')
            ->get();

        $this->command->info("Genesis Global School — Seeding exam data");
        $this->command->info("  Session:  {$session->session_name} (id={$session->id})");
        $this->command->info("  Term 1:   {$term1->semester_name} (id={$term1->id})");
        $this->command->info("  Term 2:   {$term2->semester_name} (id={$term2->id})");
        $this->command->info("  Classes:  " . $classes->count());

        DB::transaction(function () use ($school, $session, $term1, $term2, $classes) {

            // ── Step 1: Remove stale exam data for this school so we start clean ──
            $this->command->info("\n[1/5] Clearing old exam data for school_id={$school->id}…");
            $this->clearOldData($school->id, $session->id);

            // ── Step 2: Grading systems + grade rules ─────────────────────────────
            $this->command->info("[2/5] Creating grading systems and grade rules…");
            foreach ($classes as $cls) {
                foreach ([$term1, $term2] as $term) {
                    $gs = GradingSystem::updateOrCreate(
                        [
                            'class_id'    => $cls->id,
                            'semester_id' => $term->id,
                            'session_id'  => $session->id,
                            'school_id'   => $school->id,
                        ],
                        ['system_name' => "GPA Scale — {$cls->class_name} {$term->semester_name}"]
                    );

                    foreach (self::GRADE_RULES as $rule) {
                        GradeRule::updateOrCreate(
                            [
                                'grade'             => $rule['grade'],
                                'grading_system_id' => $gs->id,
                                'session_id'        => $session->id,
                            ],
                            [
                                'point'    => $rule['point'],
                                'start_at' => $rule['start_at'],
                                'end_at'   => $rule['end_at'],
                                'school_id'=> $school->id,
                            ]
                        );
                    }
                }
            }

            // ── Step 3: Exams, ExamRules, ExamSchedules ───────────────────────────
            $this->command->info("[3/5] Creating exams, exam rules, and schedules…");

            /**
             * Exam schedule blocks:
             *  1st Terminal: held 15–30 September 2026
             *  2nd Terminal: held 1–15 March 2027
             */
            $termConfig = [
                $term1->id => [
                    'label'      => '1st Terminal Examination',
                    'start_date' => '2026-09-15',
                    'end_date'   => '2026-09-30',
                    'sched_base' => '2026-09-15', // schedule dates start here
                    'note'       => 'Covers Syllabus: April–September 2026 (50% of annual curriculum). '
                                  . 'Marks distribution: Written 80 + Internal Assessment 20.',
                ],
                $term2->id => [
                    'label'      => '2nd Terminal Examination',
                    'start_date' => '2027-03-01',
                    'end_date'   => '2027-03-15',
                    'sched_base' => '2027-03-01',
                    'note'       => 'Covers Syllabus: October 2026–March 2027 (remaining 50%). '
                                  . 'Marks distribution: Written 80 + Internal Assessment 20. '
                                  . 'Annual result = (T1 × 40%) + (T2 × 60%).',
                ],
            ];

            // exam_id[class_id][course_id][term_id] = Exam
            $examMap = [];

            foreach ($classes as $cls) {
                $courses = Course::where('class_id', $cls->id)
                    ->where('session_id', $session->id)
                    ->get();

                foreach ([$term1, $term2] as $termIdx => $term) {
                    $cfg        = $termConfig[$term->id];
                    $schedDate  = $cfg['sched_base'];
                    $dayOffset  = 0;

                    foreach ($courses as $crsIdx => $course) {
                        // One exam per subject per terminal per class
                        $exam = Exam::updateOrCreate(
                            [
                                'exam_name'  => "{$cfg['label']} — {$course->course_name}",
                                'class_id'   => $cls->id,
                                'course_id'  => $course->id,
                                'semester_id'=> $term->id,
                                'session_id' => $session->id,
                                'school_id'  => $school->id,
                            ],
                            [
                                'start_date' => $cfg['start_date'],
                                'end_date'   => $cfg['end_date'],
                            ]
                        );

                        // ExamRule: FM=100, PM=40, marks breakdown
                        ExamRule::updateOrCreate(
                            ['exam_id' => $exam->id, 'session_id' => $session->id],
                            [
                                'total_marks'              => 100,
                                'pass_marks'               => 40,
                                'marks_distribution_note'  => $cfg['note'],
                                'school_id'                => $school->id,
                            ]
                        );

                        // ExamSchedule: each subject gets its own date (Monday–Friday)
                        // Skip weekends
                        $schedDay  = \Carbon\Carbon::parse($schedDate)->addDays($dayOffset);
                        while ($schedDay->isWeekend()) { $schedDay->addDay(); }

                        ExamSchedule::updateOrCreate(
                            ['exam_id' => $exam->id, 'session_id' => $session->id],
                            [
                                'exam_date'  => $schedDay->toDateString(),
                                'start_time' => '10:00:00',
                                'end_time'   => '13:00:00',
                                'notes'      => $cfg['note'],
                                'school_id'  => $school->id,
                            ]
                        );

                        $dayOffset++;
                        $examMap[$cls->id][$course->id][$term->id] = $exam;
                    }
                }
            }

            // ── Step 4: Seed raw marks (marks table) ──────────────────────────────
            $this->command->info("[4/5] Seeding raw marks for all students…");
            $this->seedMarks($school, $session, $term1, $term2, $classes, $examMap);

            // ── Step 5: Calculate and store final marks ───────────────────────────
            $this->command->info("[5/5] Computing and storing final marks (report card data)…");
            $this->seedFinalMarks($school, $session, $term1, $term2, $classes, $examMap);
        });

        $this->printSummary($school, $session, $term1, $term2);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  MARK SEEDING
    // ─────────────────────────────────────────────────────────────────────────

    private function seedMarks(
        School $school,
        SchoolSession $session,
        Semester $term1,
        Semester $term2,
        $classes,
        array $examMap
    ): void {
        // Each student gets a consistent "ability profile" so their marks are
        // correlated across subjects and terms (good students stay good).
        $markRows      = [];
        $batchSize     = 500;
        $totalInserted = 0;

        foreach ($classes as $cls) {
            $courses    = Course::where('class_id', $cls->id)->where('session_id', $session->id)->get();
            $promotions = Promotion::where('class_id', $cls->id)->where('session_id', $session->id)
                ->with('student')
                ->get();

            foreach ($promotions as $promo) {
                // Assign a stable "ability band" per student (seeded by student_id)
                $abilityBand = $this->pickBand($promo->student_id);

                foreach ([$term1, $term2] as $term) {
                    foreach ($courses as $course) {
                        $exam = $examMap[$cls->id][$course->id][$term->id] ?? null;
                        if (! $exam) continue;

                        // Vary marks slightly per subject/term (±8 marks around band midpoint)
                        $baseMark = $this->markForBand($abilityBand, $promo->student_id + $course->id + $term->id);

                        $markRows[] = [
                            'school_id'   => $school->id,
                            'exam_id'     => $exam->id,
                            'student_id'  => $promo->student_id,
                            'class_id'    => $cls->id,
                            'section_id'  => $promo->section_id ?? 0,
                            'course_id'   => $course->id,
                            'session_id'  => $session->id,
                            'marks'       => $baseMark,
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ];

                        if (count($markRows) >= $batchSize) {
                            DB::table('marks')->upsert(
                                $markRows,
                                ['exam_id', 'student_id', 'course_id', 'session_id'],
                                ['marks', 'updated_at']
                            );
                            $totalInserted += count($markRows);
                            $markRows = [];
                        }
                    }
                }
            }
        }

        // Flush remaining
        if (count($markRows) > 0) {
            DB::table('marks')->upsert(
                $markRows,
                ['exam_id', 'student_id', 'course_id', 'session_id'],
                ['marks', 'updated_at']
            );
            $totalInserted += count($markRows);
        }

        $this->command->info("     → {$totalInserted} mark rows inserted/updated");
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  FINAL MARK SEEDING
    // ─────────────────────────────────────────────────────────────────────────

    private function seedFinalMarks(
        School $school,
        SchoolSession $session,
        Semester $term1,
        Semester $term2,
        $classes,
        array $examMap
    ): void {
        $finalRows     = [];
        $batchSize     = 500;
        $totalInserted = 0;

        foreach ($classes as $cls) {
            $courses    = Course::where('class_id', $cls->id)->where('session_id', $session->id)->get();
            $promotions = Promotion::where('class_id', $cls->id)->where('session_id', $session->id)->get();

            foreach ($promotions as $promo) {
                $abilityBand = $this->pickBand($promo->student_id);

                foreach ([$term1, $term2] as $term) {
                    foreach ($courses as $course) {
                        $exam = $examMap[$cls->id][$course->id][$term->id] ?? null;
                        if (! $exam) continue;

                        // Get the raw mark we seeded for this student/exam
                        $rawMark = $this->markForBand($abilityBand, $promo->student_id + $course->id + $term->id);

                        /**
                         * Final mark breakdown:
                         *   Written exam  = 80% of raw mark  (max 80)
                         *   Internal/CA   = 20% of raw mark  (max 20)
                         *   calculated    = written + internal
                         *   final_marks   = rounded to 1 decimal
                         */
                        $written         = round($rawMark * 0.80, 1);
                        $internal        = round($rawMark * 0.20, 1);
                        $calculatedMarks = round($written + $internal, 1); // = rawMark
                        $finalMarks      = $calculatedMarks;

                        $note = "Written: {$written}/80  |  Internal: {$internal}/20";

                        $finalRows[] = [
                            'school_id'        => $school->id,
                            'student_id'       => $promo->student_id,
                            'class_id'         => $cls->id,
                            'section_id'       => $promo->section_id ?? 0,
                            'course_id'        => $course->id,
                            'semester_id'      => $term->id,
                            'session_id'       => $session->id,
                            'calculated_marks' => $calculatedMarks,
                            'final_marks'      => $finalMarks,
                            'note'             => $note,
                            'created_at'       => now(),
                            'updated_at'       => now(),
                        ];

                        if (count($finalRows) >= $batchSize) {
                            DB::table('final_marks')->upsert(
                                $finalRows,
                                ['student_id', 'course_id', 'semester_id', 'session_id'],
                                ['calculated_marks', 'final_marks', 'note', 'updated_at']
                            );
                            $totalInserted += count($finalRows);
                            $finalRows = [];
                        }
                    }
                }
            }
        }

        if (count($finalRows) > 0) {
            DB::table('final_marks')->upsert(
                $finalRows,
                ['student_id', 'course_id', 'semester_id', 'session_id'],
                ['calculated_marks', 'final_marks', 'note', 'updated_at']
            );
            $totalInserted += count($finalRows);
        }

        $this->command->info("     → {$totalInserted} final_mark rows inserted/updated");
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Clear all exam-related data for this school/session so the seeder is
     * safe to re-run without constraint violations.
     */
    private function clearOldData(int $schoolId, int $sessionId): void
    {
        // Get all exam IDs for this school+session first
        $examIds = DB::table('exams')
            ->where('school_id', $schoolId)
            ->where('session_id', $sessionId)
            ->pluck('id');

        if ($examIds->isNotEmpty()) {
            DB::table('exam_schedules')->whereIn('exam_id', $examIds)->delete();
            DB::table('exam_rules')->whereIn('exam_id', $examIds)->delete();
            DB::table('marks')->where('school_id', $schoolId)->where('session_id', $sessionId)->delete();
        }

        DB::table('final_marks')->where('school_id', $schoolId)->where('session_id', $sessionId)->delete();

        // Grade rules
        $gsIds = DB::table('grading_systems')
            ->where('school_id', $schoolId)
            ->where('session_id', $sessionId)
            ->pluck('id');
        if ($gsIds->isNotEmpty()) {
            DB::table('grade_rules')->whereIn('grading_system_id', $gsIds)->delete();
        }
        DB::table('grading_systems')->where('school_id', $schoolId)->where('session_id', $sessionId)->delete();

        // Hard-delete the old single exam that was seeded by GenesisGlobalSchoolSeeder
        DB::table('exams')->where('school_id', $schoolId)->where('session_id', $sessionId)->delete();
    }

    /**
     * Assign a stable "ability band" index (0–5) to a student based on their ID.
     * Lower index = higher ability, consistent across runs.
     *
     * Distribution matches MARK_BANDS weights (10/15/25/25/15/10 = 100).
     */
    private function pickBand(int $studentId): int
    {
        // Use the last 2 digits of student_id mod 100 to distribute into bands
        $r = $studentId % 100;
        if ($r < 10) return 0;   // A+ band (10%)
        if ($r < 25) return 1;   // A  band (15%)
        if ($r < 50) return 2;   // B  band (25%)
        if ($r < 75) return 3;   // C  band (25%)
        if ($r < 90) return 4;   // D  band (15%)
        return 5;                 // F  band (10%)
    }

    /**
     * Generate a mark within the given ability band's range.
     * The $seed ensures variation per subject/term while staying in the band.
     */
    private function markForBand(int $bandIndex, int $seed): float
    {
        $band = self::MARK_BANDS[$bandIndex];
        $range = $band['max'] - $band['min'];

        // Deterministic but varied: use a simple hash to pick within range
        $offset = abs($seed * 2654435761 & 0xFFFFFF) % ($range + 1);
        $mark   = $band['min'] + $offset;

        // Clamp to valid range
        return (float) max($band['min'], min($band['max'], $mark));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  SUMMARY REPORT
    // ─────────────────────────────────────────────────────────────────────────

    private function printSummary(School $school, SchoolSession $session, Semester $term1, Semester $term2): void
    {
        $examCount   = DB::table('exams')->where('school_id', $school->id)->where('session_id', $session->id)->count();
        $ruleCount   = DB::table('exam_rules')
            ->join('exams', 'exam_rules.exam_id', '=', 'exams.id')
            ->where('exams.school_id', $school->id)
            ->count();
        $markCount   = DB::table('marks')->where('school_id', $school->id)->where('session_id', $session->id)->count();
        $finalCount  = DB::table('final_marks')->where('school_id', $school->id)->where('session_id', $session->id)->count();
        $gsCount     = DB::table('grading_systems')->where('school_id', $school->id)->where('session_id', $session->id)->count();
        $grCount     = DB::table('grade_rules')->where('school_id', $school->id)->count();

        // Division distribution from final_marks
        $dist = DB::table('final_marks')
            ->where('school_id', $school->id)
            ->where('session_id', $session->id)
            ->selectRaw('
                SUM(CASE WHEN final_marks >= 80 THEN 1 ELSE 0 END) as first_div,
                SUM(CASE WHEN final_marks >= 60 AND final_marks < 80 THEN 1 ELSE 0 END) as second_div,
                SUM(CASE WHEN final_marks >= 45 AND final_marks < 60 THEN 1 ELSE 0 END) as third_div,
                SUM(CASE WHEN final_marks < 40 THEN 1 ELSE 0 END) as failed
            ')
            ->first();

        $total = $markCount ?: 1;
        $this->command->info("\n╔══════════════════════════════════════════════════════════╗");
        $this->command->info("║         GENESIS GLOBAL SCHOOL — EXAM SEED COMPLETE        ║");
        $this->command->info("╠══════════════════════════════════════════════════════════╣");
        $this->command->info("║  School         : {$school->name} (id={$school->id})");
        $this->command->info("║  Session        : {$session->session_name}");
        $this->command->info("║  Exams created  : {$examCount}");
        $this->command->info("║  Exam rules     : {$ruleCount}");
        $this->command->info("║  Grading systems: {$gsCount}  Grade rules: {$grCount}");
        $this->command->info("║  Raw marks      : {$markCount}");
        $this->command->info("║  Final marks    : {$finalCount}");
        $this->command->info("╠══════════════════════════════════════════════════════════╣");
        $this->command->info("║  DIVISION / GRADE DISTRIBUTION (per subject mark)");
        $this->command->info("║  1st Division (≥80)  : " . number_format($dist->first_div));
        $this->command->info("║  2nd Division (60-79): " . number_format($dist->second_div));
        $this->command->info("║  3rd Division (45-59): " . number_format($dist->third_div));
        $this->command->info("║  Failed (<40)        : " . number_format($dist->failed));
        $this->command->info("╚══════════════════════════════════════════════════════════╝");
    }
}
