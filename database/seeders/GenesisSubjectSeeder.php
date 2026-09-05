<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamRule;
use App\Models\ExamSchedule;
use App\Models\FinalMark;
use App\Models\GradeRule;
use App\Models\GradingSystem;
use App\Models\Mark;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\School;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * GenesisSubjectSeeder
 * ─────────────────────────────────────────────────────────────────────────────
 * Seeds proper subjects (courses) for all 14 classes at Genesis Global School
 * following Nepal's national curriculum (CDC Nepal syllabus), then re-runs the
 * exam seeding logic so all exams, marks, and final marks align with the new
 * correct subjects.
 *
 * CURRICULUM STRUCTURE USED
 * ──────────────────────────
 * Grade 1–3  : Primary (ECC/lower primary) — core foundational subjects
 * Grade 4–5  : Upper primary — same core + Hindi added
 * Grade 6–8  : Lower secondary — subjects split into distinct disciplines
 * Grade 9–10 : Secondary (SEE level) — full subject load including Optional Math
 * Grade 11–12 Science    : NEB +2 Science stream
 * Grade 11–12 Management : NEB +2 Management stream
 *
 * Full marks per subject follow NEB/CDC standard:
 *   Theory      FM 75 / PM 30
 *   Practical   FM 25 / PM 10   (for Science, Computer, PE)
 *   Combined    FM 100 / PM 40
 */
class GenesisSubjectSeeder extends Seeder
{
    /** Subject map: class name → [subject_name, full_marks, pass_marks, type] */
    private const CURRICULUM = [

        // ── GRADE 1 ──────────────────────────────────────────────────────────
        'Grade 1' => [
            ['English',             75,  30, 'theory'],
            ['Nepali',              75,  30, 'theory'],
            ['Mathematics',         75,  30, 'theory'],
            ['Environmental Studies (EVS)', 50, 20, 'theory'],
            ['Creative Arts & Craft', 25, 10, 'practical'],
            ['Physical Education (PE)', 25, 10, 'practical'],
        ],

        // ── GRADE 2 ──────────────────────────────────────────────────────────
        'Grade 2' => [
            ['English',             75,  30, 'theory'],
            ['Nepali',              75,  30, 'theory'],
            ['Mathematics',         75,  30, 'theory'],
            ['Environmental Studies (EVS)', 50, 20, 'theory'],
            ['Creative Arts & Craft', 25, 10, 'practical'],
            ['Physical Education (PE)', 25, 10, 'practical'],
        ],

        // ── GRADE 3 ──────────────────────────────────────────────────────────
        'Grade 3' => [
            ['English',             75,  30, 'theory'],
            ['Nepali',              75,  30, 'theory'],
            ['Mathematics',         75,  30, 'theory'],
            ['Environmental Studies (EVS)', 50, 20, 'theory'],
            ['Social Studies & Life Skills', 50, 20, 'theory'],
            ['Creative Arts',       25,  10, 'practical'],
            ['Physical Education (PE)', 25, 10, 'practical'],
        ],

        // ── GRADE 4 ──────────────────────────────────────────────────────────
        'Grade 4' => [
            ['English',             100, 40, 'theory'],
            ['Nepali',              100, 40, 'theory'],
            ['Mathematics',         100, 40, 'theory'],
            ['Science & Technology', 75, 30, 'theory'],
            ['Social Studies & Life Skills', 75, 30, 'theory'],
            ['Hindi',               50,  20, 'theory'],
            ['Health, Population & Environment (HPE)', 50, 20, 'theory'],
        ],

        // ── GRADE 5 ──────────────────────────────────────────────────────────
        'Grade 5' => [
            ['English',             100, 40, 'theory'],
            ['Nepali',              100, 40, 'theory'],
            ['Mathematics',         100, 40, 'theory'],
            ['Science & Technology', 75, 30, 'theory'],
            ['Social Studies & Life Skills', 75, 30, 'theory'],
            ['Hindi',               50,  20, 'theory'],
            ['Health, Population & Environment (HPE)', 50, 20, 'theory'],
        ],

        // ── GRADE 6 ──────────────────────────────────────────────────────────
        'Grade 6' => [
            ['English',             100, 40, 'theory'],
            ['Nepali',              100, 40, 'theory'],
            ['Mathematics',         100, 40, 'theory'],
            ['Science',             75,  30, 'theory'],
            ['Social Studies',      75,  30, 'theory'],
            ['Hindi / Sanskrit',    50,  20, 'theory'],
            ['Computer Science',    50,  20, 'theory'],
            ['Health & Physical Education', 50, 20, 'practical'],
        ],

        // ── GRADE 7 ──────────────────────────────────────────────────────────
        'Grade 7' => [
            ['English',             100, 40, 'theory'],
            ['Nepali',              100, 40, 'theory'],
            ['Mathematics',         100, 40, 'theory'],
            ['Science',             75,  30, 'theory'],
            ['Social Studies',      75,  30, 'theory'],
            ['Hindi / Sanskrit',    50,  20, 'theory'],
            ['Computer Science',    50,  20, 'theory'],
            ['Health & Physical Education', 50, 20, 'practical'],
        ],

        // ── GRADE 8 ──────────────────────────────────────────────────────────
        'Grade 8' => [
            ['English',             100, 40, 'theory'],
            ['Nepali',              100, 40, 'theory'],
            ['Mathematics',         100, 40, 'theory'],
            ['Science',             75,  30, 'theory'],
            ['Social Studies',      75,  30, 'theory'],
            ['Hindi / Sanskrit',    50,  20, 'theory'],
            ['Computer Science',    50,  20, 'theory'],
            ['Health & Physical Education', 50, 20, 'practical'],
        ],

        // ── GRADE 9 (SEE prep) ───────────────────────────────────────────────
        'Grade 9' => [
            ['Compulsory English',  100, 40, 'theory'],
            ['Compulsory Nepali',   100, 40, 'theory'],
            ['Mathematics',         100, 40, 'theory'],
            ['Science (Physics, Chemistry & Biology)', 75, 30, 'theory'],
            ['Social Studies & Life Skills', 75, 30, 'theory'],
            ['Optional Mathematics', 100, 40, 'theory'],
            ['Computer Science',    75,  30, 'theory'],
            ['Health, Population & Environment (HPE)', 50, 20, 'theory'],
        ],

        // ── GRADE 10 (SEE year) ──────────────────────────────────────────────
        'Grade 10' => [
            ['Compulsory English',  100, 40, 'theory'],
            ['Compulsory Nepali',   100, 40, 'theory'],
            ['Mathematics',         100, 40, 'theory'],
            ['Science (Physics, Chemistry & Biology)', 75, 30, 'theory'],
            ['Social Studies & Life Skills', 75, 30, 'theory'],
            ['Optional Mathematics', 100, 40, 'theory'],
            ['Computer Science',    75,  30, 'theory'],
            ['Health, Population & Environment (HPE)', 50, 20, 'theory'],
        ],

        // ── GRADE 11 (Science) ───────────────────────────────────────────────
        'Grade 11 (Science)' => [
            ['English',             100, 40, 'theory'],
            ['Nepali',              100, 40, 'theory'],
            ['Physics',             75,  27, 'theory'],     // 75 T + 25 P = 100
            ['Physics Practical',   25,  10, 'practical'],
            ['Chemistry',           75,  27, 'theory'],
            ['Chemistry Practical', 25,  10, 'practical'],
            ['Mathematics',         100, 40, 'theory'],
            ['Biology',             75,  27, 'theory'],
            ['Biology Practical',   25,  10, 'practical'],
        ],

        // ── GRADE 12 (Science) ───────────────────────────────────────────────
        'Grade 12 (Science)' => [
            ['English',             100, 40, 'theory'],
            ['Nepali',              100, 40, 'theory'],
            ['Physics',             75,  27, 'theory'],
            ['Physics Practical',   25,  10, 'practical'],
            ['Chemistry',           75,  27, 'theory'],
            ['Chemistry Practical', 25,  10, 'practical'],
            ['Mathematics',         100, 40, 'theory'],
            ['Biology',             75,  27, 'theory'],
            ['Biology Practical',   25,  10, 'practical'],
        ],

        // ── GRADE 11 (Management) ────────────────────────────────────────────
        'Grade 11 (Management)' => [
            ['English',             100, 40, 'theory'],
            ['Nepali',              100, 40, 'theory'],
            ['Accountancy',         100, 40, 'theory'],
            ['Business Studies',    100, 40, 'theory'],
            ['Economics',           100, 40, 'theory'],
            ['Mathematics (Business Math)', 100, 40, 'theory'],
        ],

        // ── GRADE 12 (Management) ────────────────────────────────────────────
        'Grade 12 (Management)' => [
            ['English',             100, 40, 'theory'],
            ['Nepali',              100, 40, 'theory'],
            ['Accountancy',         100, 40, 'theory'],
            ['Business Studies',    100, 40, 'theory'],
            ['Economics',           100, 40, 'theory'],
            ['Mathematics (Business Math)', 100, 40, 'theory'],
        ],
    ];

    // Grade rules (same GPA scale for all classes)
    private const GRADE_RULES = [
        ['grade' => 'A+', 'point' => 4.0, 'start_at' => 90,   'end_at' => 100],
        ['grade' => 'A',  'point' => 3.7, 'start_at' => 80,   'end_at' => 89.99],
        ['grade' => 'B+', 'point' => 3.3, 'start_at' => 70,   'end_at' => 79.99],
        ['grade' => 'B',  'point' => 3.0, 'start_at' => 60,   'end_at' => 69.99],
        ['grade' => 'C+', 'point' => 2.3, 'start_at' => 50,   'end_at' => 59.99],
        ['grade' => 'C',  'point' => 2.0, 'start_at' => 40,   'end_at' => 49.99],
        ['grade' => 'D',  'point' => 1.0, 'start_at' => 35,   'end_at' => 39.99],
        ['grade' => 'NG', 'point' => 0.0, 'start_at' => 0,    'end_at' => 34.99],
    ];

    /** Exam config per terminal */
    private const TERMS = [
        'First Term 2026' => [
            'label'      => '1st Terminal Examination',
            'start_date' => '2026-09-15',
            'end_date'   => '2026-09-30',
            'sched_base' => '2026-09-15',
            'note'       => '1st Terminal | Syllabus: April–September 2026 (50%) | Written 80% + Internal Assessment 20%',
        ],
        'Second Term 2026' => [
            'label'      => '2nd Terminal Examination',
            'start_date' => '2027-03-01',
            'end_date'   => '2027-03-15',
            'sched_base' => '2027-03-01',
            'note'       => '2nd Terminal | Syllabus: October 2026–March 2027 (50%) | Written 80% + Internal 20% | Annual = T1×40% + T2×60%',
        ],
    ];

    // Mark distribution weights per ability band (must sum to 100)
    private const BANDS = [
        ['w' => 8,  'min' => 90, 'max' => 100],  // A+
        ['w' => 12, 'min' => 80, 'max' => 89],   // A
        ['w' => 20, 'min' => 70, 'max' => 79],   // B+
        ['w' => 22, 'min' => 60, 'max' => 69],   // B
        ['w' => 20, 'min' => 50, 'max' => 59],   // C+
        ['w' => 10, 'min' => 40, 'max' => 49],   // C
        ['w' => 5,  'min' => 35, 'max' => 39],   // D (marginal pass)
        ['w' => 3,  'min' => 10, 'max' => 34],   // NG (fail)
    ];

    public function run(): void
    {
        $school  = School::findOrFail(3);
        $session = SchoolSession::where('school_id', $school->id)
            ->where('session_name', '2026-2027')
            ->firstOrFail();

        $semesters = \App\Models\Semester::where('school_id', $school->id)
            ->where('session_id', $session->id)
            ->get()
            ->keyBy('semester_name');

        $term1 = $semesters['First Term 2026'];
        $term2 = $semesters['Second Term 2026'];

        $classes = SchoolClass::where('school_id', $school->id)
            ->where('session_id', $session->id)
            ->orderBy('id')
            ->get();

        $this->command->info("Genesis Global School — Seeding proper subjects + exam data");
        $this->command->info("  Classes: " . $classes->count());

        DB::transaction(function () use ($school, $session, $term1, $term2, $classes) {

            // ── Step 1: Wipe old subject-dependent data ───────────────────────
            $this->command->info("\n[1/5] Clearing old courses, exams, marks, final_marks, grading data…");
            $this->clearAll($school->id, $session->id);

            // ── Step 2: Seed correct subjects ─────────────────────────────────
            $this->command->info("[2/5] Seeding correct subjects per class…");
            $courseMap = $this->seedCourses($school, $session, $classes, $term1);

            // ── Step 3: Grading systems + rules ───────────────────────────────
            $this->command->info("[3/5] Seeding grading systems and grade rules…");
            $this->seedGrading($school, $session, $classes, $term1, $term2);

            // ── Step 4: Exams + ExamRules + Schedules ─────────────────────────
            $this->command->info("[4/5] Creating exams, rules, and schedules…");
            $examMap = $this->seedExams($school, $session, $term1, $term2, $classes, $courseMap);

            // ── Step 5: Marks + FinalMarks ────────────────────────────────────
            $this->command->info("[5/5] Seeding marks and final marks for all students…");
            $this->seedMarks($school, $session, $term1, $term2, $classes, $courseMap, $examMap);
        });

        $this->printSummary($school, $session);
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function clearAll(int $schoolId, int $sessionId): void
    {
        $examIds = DB::table('exams')
            ->where('school_id', $schoolId)
            ->where('session_id', $sessionId)
            ->pluck('id');

        if ($examIds->isNotEmpty()) {
            DB::table('exam_schedules')->whereIn('exam_id', $examIds)->delete();
            DB::table('exam_rules')->whereIn('exam_id', $examIds)->delete();
            DB::table('marks')->where('school_id', $schoolId)->where('session_id', $sessionId)->delete();
        }

        DB::table('exams')->where('school_id', $schoolId)->where('session_id', $sessionId)->delete();
        DB::table('final_marks')->where('school_id', $schoolId)->where('session_id', $sessionId)->delete();

        $gsIds = DB::table('grading_systems')
            ->where('school_id', $schoolId)
            ->where('session_id', $sessionId)
            ->pluck('id');
        if ($gsIds->isNotEmpty()) {
            DB::table('grade_rules')->whereIn('grading_system_id', $gsIds)->delete();
        }
        DB::table('grading_systems')->where('school_id', $schoolId)->where('session_id', $sessionId)->delete();

        // Remove old courses for this school+session
        DB::table('courses')
            ->where('school_id', $schoolId)
            ->where('session_id', $sessionId)
            ->delete();
    }

    // ── Seed courses ──────────────────────────────────────────────────────────

    /**
     * @return array<int, array<int, Course>>   courseMap[class_id][subject_name] = Course
     */
    private function seedCourses(School $school, SchoolSession $session, $classes, $term1): array
    {
        $courseMap = [];

        foreach ($classes as $cls) {
            $subjects = self::CURRICULUM[$cls->class_name] ?? null;

            if (! $subjects) {
                $this->command->warn("  No curriculum defined for '{$cls->class_name}' — skipping");
                continue;
            }

            $this->command->line("  {$cls->class_name}: " . count($subjects) . " subjects");

            foreach ($subjects as [$name, $fm, $pm, $type]) {
                $course = Course::create([
                    'school_id'   => $school->id,
                    'course_name' => $name,
                    'course_type' => $type,
                    'class_id'    => $cls->id,
                    'session_id'  => $session->id,
                    'semester_id' => $term1->id,  // FK required; courses span both terms
                ]);

                $courseMap[$cls->id][$name] = $course;
            }
        }

        return $courseMap;
    }

    // ── Grading systems ───────────────────────────────────────────────────────

    private function seedGrading(School $school, SchoolSession $session, $classes, $term1, $term2): void
    {
        foreach ($classes as $cls) {
            foreach ([$term1, $term2] as $term) {
                $gs = GradingSystem::create([
                    'school_id'   => $school->id,
                    'system_name' => "GPA Scale — {$cls->class_name} {$term->semester_name}",
                    'class_id'    => $cls->id,
                    'semester_id' => $term->id,
                    'session_id'  => $session->id,
                ]);

                foreach (self::GRADE_RULES as $rule) {
                    GradeRule::create([
                        'school_id'         => $school->id,
                        'grading_system_id' => $gs->id,
                        'session_id'        => $session->id,
                        'grade'             => $rule['grade'],
                        'point'             => $rule['point'],
                        'start_at'          => $rule['start_at'],
                        'end_at'            => $rule['end_at'],
                    ]);
                }
            }
        }
    }

    // ── Exams ─────────────────────────────────────────────────────────────────

    private function seedExams(
        School $school,
        SchoolSession $session,
        $term1,
        $term2,
        $classes,
        array $courseMap
    ): array {
        $examMap = [];

        foreach ($classes as $cls) {
            $classCourses = $courseMap[$cls->id] ?? [];
            if (empty($classCourses)) continue;

            foreach ([$term1, $term2] as $term) {
                $cfg       = self::TERMS[$term->semester_name];
                $dayOffset = 0;

                foreach ($classCourses as $courseName => $course) {
                    // Compute per-subject full_marks and pass_marks from curriculum
                    $subCfg = $this->subjectConfig($cls->class_name, $courseName);
                    $fm = $subCfg[1];
                    $pm = $subCfg[2];

                    $exam = Exam::create([
                        'school_id'   => $school->id,
                        'exam_name'   => "{$cfg['label']} — {$courseName}",
                        'class_id'    => $cls->id,
                        'course_id'   => $course->id,
                        'semester_id' => $term->id,
                        'session_id'  => $session->id,
                        'start_date'  => $cfg['start_date'],
                        'end_date'    => $cfg['end_date'],
                    ]);

                    ExamRule::create([
                        'school_id'               => $school->id,
                        'exam_id'                 => $exam->id,
                        'session_id'              => $session->id,
                        'total_marks'             => $fm,
                        'pass_marks'              => $pm,
                        'marks_distribution_note' => $cfg['note'],
                    ]);

                    // Schedule: each subject on a weekday, one per day
                    $day = \Carbon\Carbon::parse($cfg['sched_base'])->addDays($dayOffset);
                    while ($day->isWeekend()) { $day->addDay(); }

                    ExamSchedule::create([
                        'school_id'  => $school->id,
                        'exam_id'    => $exam->id,
                        'session_id' => $session->id,
                        'exam_date'  => $day->toDateString(),
                        'start_time' => '10:00:00',
                        'end_time'   => '13:00:00',
                        'notes'      => $cfg['note'],
                    ]);

                    $dayOffset++;
                    $examMap[$cls->id][$course->id][$term->id] = [
                        'exam'       => $exam,
                        'full_marks' => $fm,
                        'pass_marks' => $pm,
                    ];
                }
            }
        }

        return $examMap;
    }

    // ── Marks + FinalMarks ────────────────────────────────────────────────────

    private function seedMarks(
        School $school,
        SchoolSession $session,
        $term1,
        $term2,
        $classes,
        array $courseMap,
        array $examMap
    ): void {
        $markRows  = [];
        $finalRows = [];
        $batch     = 500;
        $mTotal    = 0;
        $fTotal    = 0;

        foreach ($classes as $cls) {
            $classCourses = $courseMap[$cls->id] ?? [];
            if (empty($classCourses)) continue;

            $promotions = \App\Models\Promotion::where('class_id', $cls->id)
                ->where('session_id', $session->id)
                ->get();

            foreach ($promotions as $promo) {
                $band = $this->pickBand($promo->student_id);

                foreach ([$term1, $term2] as $term) {
                    foreach ($classCourses as $courseName => $course) {
                        $entry = $examMap[$cls->id][$course->id][$term->id] ?? null;
                        if (! $entry) continue;

                        $exam    = $entry['exam'];
                        $fm      = $entry['full_marks'];
                        $pm      = $entry['pass_marks'];

                        // Raw mark scaled to this subject's full marks
                        $rawPct  = $this->pctForBand($band, $promo->student_id + $course->id + $term->id);
                        $rawMark = round($rawPct / 100 * $fm, 1);

                        // Mark row
                        $markRows[] = [
                            'school_id'  => $school->id,
                            'exam_id'    => $exam->id,
                            'student_id' => $promo->student_id,
                            'class_id'   => $cls->id,
                            'section_id' => $promo->section_id ?? 0,
                            'course_id'  => $course->id,
                            'session_id' => $session->id,
                            'marks'      => $rawMark,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        // FinalMark: Written 80% + Internal 20% of scaled raw
                        $written  = round($rawMark * 0.80, 1);
                        $internal = round($rawMark * 0.20, 1);
                        $final    = round($written + $internal, 1);

                        $finalRows[] = [
                            'school_id'        => $school->id,
                            'student_id'       => $promo->student_id,
                            'class_id'         => $cls->id,
                            'section_id'       => $promo->section_id ?? 0,
                            'course_id'        => $course->id,
                            'semester_id'      => $term->id,
                            'session_id'       => $session->id,
                            'calculated_marks' => $final,
                            'final_marks'      => $final,
                            'note'             => "Written: {$written}/{$fm} (80%) | Internal: {$internal} (20%) | FM: {$fm} PM: {$pm}",
                            'created_at'       => now(),
                            'updated_at'       => now(),
                        ];

                        if (count($markRows) >= $batch) {
                            DB::table('marks')->insert($markRows);
                            $mTotal += count($markRows);
                            $markRows = [];
                        }

                        if (count($finalRows) >= $batch) {
                            DB::table('final_marks')->insert($finalRows);
                            $fTotal += count($finalRows);
                            $finalRows = [];
                        }
                    }
                }
            }
        }

        if ($markRows)  { DB::table('marks')->insert($markRows);       $mTotal += count($markRows);  }
        if ($finalRows) { DB::table('final_marks')->insert($finalRows); $fTotal += count($finalRows); }

        $this->command->info("     → {$mTotal} mark rows");
        $this->command->info("     → {$fTotal} final_mark rows");
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /** Return [name, full_marks, pass_marks] for a subject in a class */
    private function subjectConfig(string $className, string $subjectName): array
    {
        foreach (self::CURRICULUM[$className] ?? [] as $row) {
            if ($row[0] === $subjectName) return $row;
        }
        return [$subjectName, 100, 40, 'theory'];
    }

    /** Assign a stable ability band index (0–7) keyed by student_id */
    private function pickBand(int $studentId): int
    {
        $r     = $studentId % 100;
        $cumul = 0;
        foreach (self::BANDS as $i => $band) {
            $cumul += $band['w'];
            if ($r < $cumul) return $i;
        }
        return count(self::BANDS) - 1;
    }

    /** Return a percentage (0–100) within the band, varied by seed */
    private function pctForBand(int $bandIndex, int $seed): float
    {
        $band   = self::BANDS[$bandIndex];
        $range  = $band['max'] - $band['min'];
        $offset = abs(($seed * 2654435761) & 0xFFFFFF) % ($range + 1);
        return (float) ($band['min'] + $offset);
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function printSummary(School $school, SchoolSession $session): void
    {
        $sid = $session->id;
        $schId = $school->id;

        $courses   = DB::table('courses')->where('school_id', $schId)->where('session_id', $sid)->count();
        $exams     = DB::table('exams')->where('school_id', $schId)->where('session_id', $sid)->count();
        $rules     = DB::table('exam_rules')->join('exams','exam_rules.exam_id','=','exams.id')->where('exams.school_id',$schId)->count();
        $marks     = DB::table('marks')->where('school_id', $schId)->where('session_id', $sid)->count();
        $finals    = DB::table('final_marks')->where('school_id', $schId)->where('session_id', $sid)->count();
        $gs        = DB::table('grading_systems')->where('school_id', $schId)->where('session_id', $sid)->count();

        $dist = DB::table('final_marks')
            ->where('school_id', $schId)->where('session_id', $sid)
            ->selectRaw("
                SUM(CASE WHEN final_marks >= 80 THEN 1 ELSE 0 END) as first_div,
                SUM(CASE WHEN final_marks >= 60 AND final_marks < 80 THEN 1 ELSE 0 END) as second_div,
                SUM(CASE WHEN final_marks >= 40 AND final_marks < 60 THEN 1 ELSE 0 END) as third_div,
                SUM(CASE WHEN final_marks < 40 THEN 1 ELSE 0 END) as failed
            ")->first();

        // Per-class subject listing
        $classes = SchoolClass::where('school_id', $schId)->where('session_id', $sid)->orderBy('id')->get();

        $this->command->info("\n╔══════════════════════════════════════════════════════════╗");
        $this->command->info("║       GENESIS GLOBAL SCHOOL — SUBJECTS + EXAM SEED        ║");
        $this->command->info("╠══════════════════════════════════════════════════════════╣");
        $this->command->info("║  Courses seeded    : {$courses}");
        $this->command->info("║  Exams created     : {$exams}  (ExamRules: {$rules})");
        $this->command->info("║  Grading systems   : {$gs}");
        $this->command->info("║  Raw marks         : {$marks}");
        $this->command->info("║  Final marks       : {$finals}");
        $this->command->info("╠══════════════════════════════════════════════════════════╣");
        $this->command->info("║  GRADE DISTRIBUTION (per subject final mark)");
        $this->command->info("║  1st Division (A/A+ ≥80)  : " . number_format($dist->first_div));
        $this->command->info("║  2nd Division (B/B+ 60-79): " . number_format($dist->second_div));
        $this->command->info("║  3rd Division (C/C+ 40-59): " . number_format($dist->third_div));
        $this->command->info("║  Fail / NG (< 40)         : " . number_format($dist->failed));
        $this->command->info("╠══════════════════════════════════════════════════════════╣");
        $this->command->info("║  SUBJECTS BY CLASS:");
        foreach ($classes as $cls) {
            $subs = DB::table('courses')
                ->where('school_id', $schId)->where('session_id', $sid)->where('class_id', $cls->id)
                ->pluck('course_name')->toArray();
            $this->command->info("║  {$cls->class_name}: " . implode(' | ', $subs));
        }
        $this->command->info("╚══════════════════════════════════════════════════════════╝");
    }
}
