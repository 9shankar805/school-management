<?php

namespace App\Repositories;

use App\Models\FinalMark;
use App\Models\GradingSystem;
use App\Models\GradeRule;
use App\Models\Semester;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Promotion;
use App\Models\Mark;
use App\Models\Exam;
use App\Models\ExamRule;
use App\Models\Course;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * ResultRepository
 * ─────────────────────────────────────────────────────────────────────────────
 * Central hub for all result-related queries:
 *  - Per-student result sheet (all courses in a semester)
 *  - Class-level result sheet (all students in a class)
 *  - Class ranking / merit list
 *  - GPA per student per semester
 *  - CGPA per student across all semesters in a session
 *  - Performance analytics (average per subject, per class)
 */
class ResultRepository
{
    // ── Grade rule helpers ────────────────────────────────────────────────────

    /**
     * Load grade rules for a grading system bound to a class+semester+session.
     * Falls back to class-level, session-level, or Nepal CDC 8-point grading scale.
     */
    public function getGradeRules(int $sessionId, int $semesterId, int $classId): Collection
    {
        // 1. Exact match: session + semester + class
        $system = GradingSystem::where('session_id',  $sessionId)
            ->where('semester_id', $semesterId)
            ->where('class_id',    $classId)
            ->first();

        // 2. Fallback: session + class (any semester)
        if (! $system) {
            $system = GradingSystem::where('session_id', $sessionId)
                ->where('class_id', $classId)
                ->first();
        }

        // 3. Fallback: session (any grading system in this session)
        if (! $system) {
            $system = GradingSystem::where('session_id', $sessionId)->first();
        }

        if ($system) {
            $rules = GradeRule::where('grading_system_id', $system->id)
                ->where('session_id', $sessionId)
                ->orderByDesc('start_at')
                ->get();

            if ($rules->isNotEmpty()) {
                return $rules;
            }
        }

        // 4. Fallback: Official Nepal Government CDC 8-Point Grading System
        return $this->getDefaultCdcGradeRules();
    }

    /**
     * Standard Government of Nepal CDC 8-Point Grading System scale.
     */
    public function getDefaultCdcGradeRules(): Collection
    {
        return collect([
            (object)['start_at' => 90.0, 'end_at' => 100.0, 'grade' => 'A+', 'point' => 4.0],
            (object)['start_at' => 80.0, 'end_at' => 89.99, 'grade' => 'A',  'point' => 3.6],
            (object)['start_at' => 70.0, 'end_at' => 79.99, 'grade' => 'B+', 'point' => 3.2],
            (object)['start_at' => 60.0, 'end_at' => 69.99, 'grade' => 'B',  'point' => 2.8],
            (object)['start_at' => 50.0, 'end_at' => 59.99, 'grade' => 'C+', 'point' => 2.4],
            (object)['start_at' => 40.0, 'end_at' => 49.99, 'grade' => 'C',  'point' => 2.0],
            (object)['start_at' => 35.0, 'end_at' => 39.99, 'grade' => 'D',  'point' => 1.6],
            (object)['start_at' => 0.0,  'end_at' => 34.99, 'grade' => 'NG', 'point' => 0.0],
        ]);
    }

    // ── Student result sheet ──────────────────────────────────────────────────

    /**
     * Complete result sheet for one student in one semester.
     *
     * Returns array:
     * [
     *   'student'     => User,
     *   'semester'    => Semester,
     *   'class'       => SchoolClass,
     *   'section'     => Section,
     *   'promotion'   => Promotion,
     *   'courses'     => [['course', 'final_marks', 'calculated_marks', 'note', 'grade', 'point', 'passed'], ...],
     *   'gpa'         => float,
     *   'total_marks' => float,
     *   'passed'      => int,
     *   'failed'      => int,
     *   'rank'        => int|null,   (within the class+section)
     * ]
     */
    public function getStudentResult(
        int $studentId,
        int $semesterId,
        int $classId,
        int $sectionId,
        int $sessionId
    ): array {
        $student   = User::findOrFail($studentId);
        $semester  = Semester::find($semesterId);
        $class     = SchoolClass::find($classId);
        $section   = $sectionId > 0 ? Section::find($sectionId) : null;
        $rules     = $this->getGradeRules($sessionId, $semesterId, $classId);

        $marksQuery = FinalMark::with('course')
            ->where('student_id',  $studentId)
            ->where('semester_id', $semesterId)
            ->where('class_id',    $classId)
            ->where('session_id',  $sessionId)
            ->orderBy(
                Course::select('position')
                    ->whereColumn('courses.id', 'final_marks.course_id')
                    ->limit(1)
            )
            ->orderBy('course_id'); // stable secondary sort

        if ($sectionId > 0) {
            $marksQuery->where('section_id', $sectionId);
        }

        $marks = $marksQuery->get();

        // Pre-load ExamRule total_marks per course for correct full_marks display.
        // Uses the shared helper which prefers custom (non-default) FM/PM over 100/40.
        // This is used to resolve full_marks for DB-backed FinalMark records (primary
        // path) which have no full_marks column, AND as a fallback for the Mark-table path.
        $examRulesByCourse = $this->buildExamRulesByCourse($sessionId, $semesterId, $classId);

        // ── Per-subject merge: FinalMark + live Mark-table data ───────────────
        // final_marks holds the finalized/published result per subject, while the
        // Mark table holds the live entered marks per exam. The old all-or-nothing
        // `isEmpty()` gate meant finalising a SINGLE subject hid every other
        // subject's entered marks from the report card (e.g. only Nepali finalised
        // → English, Math, Science … all vanished). Now we merge per subject: keep
        // subjects present in final_marks, and supplement any subject NOT present
        // there from the live Mark table. This is a strict generalisation:
        //   • final_marks empty     → every subject comes from the Mark table (as before)
        //   • final_marks complete  → nothing is supplemented (as before)
        //   • final_marks partial    → missing subjects are filled from the Mark table (FIX)
        $fmSubjectNames = $marks->map(fn($m) => $m->course?->course_name)->filter()->unique()->all();
        $missingFromFm  = array_diff($this->getExpectedCourseNames($classId, $sessionId), $fmSubjectNames);

        if ($marks->isEmpty() || !empty($missingFromFm)) {
            $examIds = Exam::where('semester_id', $semesterId)
                ->where('session_id', $sessionId)
                ->where('class_id',   $classId)      // ← must scope to student's class only
                ->pluck('id');

            if ($examIds->isNotEmpty()) {
                $rawMarks = Mark::with(['course', 'exam.examRule'])
                    ->where('student_id', $studentId)
                    ->where('class_id',    $classId)
                    ->where('session_id',  $sessionId)
                    ->whereIn('exam_id',   $examIds)
                    ->get();

                if ($rawMarks->isNotEmpty()) {
                    // Group by course_NAME — deduplicate subjects that span multiple course rows
                    // (e.g. same subject registered under different semester course IDs).
                    $byCourse = $rawMarks->groupBy(fn($m) => $m->course?->course_name ?? $m->course_id);
                    // Keep only subjects NOT already covered by final_marks, so a finalised
                    // subject (e.g. Nepali) is shown from final_marks, not duplicated from marks.
                    $supplemental = $byCourse->reject(fn($group, $name) => in_array($name, $fmSubjectNames, true))->map(function ($cMarks) use ($studentId, $classId, $sectionId, $semesterId, $sessionId) {
                        // Pick the best candidate exam for this subject. Each terminal has its
                        // own course row, and a teacher may attach a Second-Term exam to a
                        // First-Term course row (or vice-versa). To stay robust we rank by:
                        //   Priority 1 — exam with a CUSTOM (non-default) total_marks, i.e. ≠ 100/40.
                        //                 A default 100/40 ExamRule is auto-created whenever marks
                        //                 are entered without a configured rule, so a duplicate
                        //                 "shadow" exam with FM=100/40 commonly coexists with the
                        //                 real custom-FM exam. The custom one must win, otherwise a
                        //                 40-mark (or 10-mark practical) exam is graded as if it were
                        //                 100 marks and every passing score collapses to NG.
                        //   Priority 2 — the exam whose COURSE belongs to the requested semester
                        //                 (term isolation between terminal examinations).
                        //   Priority 3 — lowest exam_id (earliest created) as a tiebreaker.
                        $best = null;
                        foreach ($cMarks->sortBy('exam_id') as $candidate) {
                            if ($best === null) {
                                $best = $candidate;
                                continue;
                            }
                            $bestFm         = (float) ($best->exam?->examRule?->total_marks ?? 100);
                            $candidateFm    = (float) ($candidate->exam?->examRule?->total_marks ?? 100);
                            $bestPm         = (float) ($best->exam?->examRule?->pass_marks ?? round($bestFm * 0.4, 0));
                            $candidatePm    = (float) ($candidate->exam?->examRule?->pass_marks ?? round($candidateFm * 0.4, 0));
                            $bestIsDefault      = ($bestFm === 100.0 && $bestPm === 40.0);
                            $candidateIsDefault = ($candidateFm === 100.0 && $candidatePm === 40.0);
                            // Priority 1: custom FM beats default FM.
                            if ($candidateIsDefault && !$bestIsDefault) {
                                continue; // keep $best
                            }
                            if (!$candidateIsDefault && $bestIsDefault) {
                                $best = $candidate;
                                continue;
                            }
                            // Priority 2: same custom/default status → prefer semester match.
                            $bestSemMatch      = (int) ($best->course?->semester_id ?? 0) === $semesterId;
                            $candidateSemMatch = (int) ($candidate->course?->semester_id ?? 0) === $semesterId;
                            if ($candidateSemMatch && !$bestSemMatch) {
                                $best = $candidate;
                                continue;
                            }
                            if (!$candidateSemMatch && $bestSemMatch) {
                                continue;
                            }
                            // Priority 3: both custom & both semester-matched (or neither) → keep earlier exam_id
                        }
                        // Among marks with the same exam_id as $best, take the latest save
                        $best = $cMarks->where('exam_id', $best->exam_id)->sortByDesc('updated_at')->first();

                        $fm = new FinalMark();
                        $fm->student_id       = $studentId;
                        $fm->class_id         = $classId;
                        $fm->section_id       = $sectionId > 0 ? $sectionId : ($best->section_id ?? 0);
                        $fm->course_id        = $best->course_id;
                        $fm->course           = $best->course;
                        $fm->semester_id      = $semesterId;
                        $fm->session_id       = $sessionId;
                        $fm->final_marks      = (float) $best->marks;
                        $fm->calculated_marks = $fm->final_marks;
                        $fm->note             = 'Recorded Exam Marks';
                        $fm->full_marks       = (float) ($best->exam?->examRule?->total_marks ?? 100);
                        $fm->pass_marks       = (float) ($best->exam?->examRule?->pass_marks ?? round($fm->full_marks * 0.4, 0));
                        return $fm;
                    });

                    if ($supplemental->isNotEmpty()) {
                        // Merge finalised subjects (final_marks) with the supplemented
                        // live-mark subjects, ordered by the configured course position.
                        $marks = $marks->concat($supplemental)
                            ->sortBy(fn($fm) => [
                                ($fm->course?->position ?? 9999),
                                ($fm->course_id ?? 9999),
                            ])
                            ->values();
                    }
                }
            }
        }

        // Auto-resolve Section if sectionId was 0
        if (! $section && $marks->isNotEmpty()) {
            $detectedSecId = $marks->first()->section_id;
            if ($detectedSecId > 0) {
                $section = Section::find($detectedSecId);
            }
        }
        if (! $section && $student->academic_info?->section_id) {
            $section = Section::find($student->academic_info->section_id);
        }

        $promotion = Promotion::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->first();

        $courses     = [];
        $totalPoints = 0.0;
        $totalMarks  = 0.0;
        $passed      = 0;
        $failed      = 0;

        foreach ($marks as $mark) {
            // Determine full_marks and pass_marks with correct priority:
            // 1. Transient $mark->full_marks / $mark->pass_marks — set from the actual exam
            //    rule during fallback-path construction. Always correct, even when
            //    mark.course_id ≠ exam.course_id (e.g. GT cross-exam assignment).
            // 2. examRulesByCourse keyed by course_id — correct for DB-backed FinalMark
            //    records (primary path) where the transient property is null because the
            //    final_marks table has no full_marks / pass_marks columns.
            // 3. If the transient is the auto-created default (100/40) but the lookup
            //    resolved a custom FM for this subject name, the custom one is authoritative.
            // 4. Hard-coded 100 / 40% as a last resort.
            [$fullMarks, $passMarks] = self::resolveFmPm(
                $mark->full_marks ?? null,
                $mark->pass_marks ?? null,
                $examRulesByCourse[$mark->course_id] ?? null
            );

            // Always pass the resolved full_marks AND pass_marks into resolveGrade so
            // the percentage normalisation uses the correct denominator (e.g. 40 for a
            // 40-mark exam) and — critically — the pass/fail flag is decided by comparing
            // obtained marks against THIS subject's configured pass mark, not the grade band.
            $resolved = $mark->resolveGrade($rules, $fullMarks, $passMarks);
            $totalPoints += $resolved['point'];
            $totalMarks  += $mark->final_marks;
            $resolved['passed'] ? $passed++ : $failed++;

            $courses[] = [
                'course'            => $mark->course,
                'final_marks'       => $mark->final_marks,
                'calculated_marks'  => $mark->calculated_marks,
                'full_marks'        => $fullMarks,
                'pass_marks'        => $passMarks,
                'note'              => $mark->note,
                'grade'             => $resolved['grade'],
                'point'             => $resolved['point'],
                'passed'            => $resolved['passed'],
            ];
        }

        // ── Add missing subjects as 0-mark entries ─────────────────────────────
        // Missing subjects (no marks entered) must still contribute to totals, GPA,
        // and pass/fail — they count as 0 obtained marks with the configured FM/PM.
        // In a real exam, an unentered subject means the student scored 0, not that
        // the subject doesn't exist.
        $expectedNames  = $this->getExpectedCourseNames($classId, $sessionId);
        $enteredNames   = collect($courses)->map(fn($c) => $c['course']?->course_name ?? '')->filter()->toArray();
        $missingNames    = array_values(array_diff($expectedNames, $enteredNames));
        $missingCount    = count($missingNames);

        foreach ($missingNames as $mName) {
            // Find a Course model and its exam rule for this missing subject
            $missingCourse = Course::where('class_id', $classId)
                ->where('course_name', $mName)
                ->where(fn($q) => $q->where('session_id', $sessionId)->orWhereNull('session_id'))
                ->first();

            if ($missingCourse) {
                $missingFullMarks = (float)($examRulesByCourse[$missingCourse->id]?->total_marks
                    ?? $this->buildExamRulesByCourse($sessionId, $semesterId, $classId)[$missingCourse->id]?->total_marks
                    ?? 100);
                $missingPassMarks = (float)($examRulesByCourse[$missingCourse->id]?->pass_marks
                    ?? round($missingFullMarks * 0.4, 0));
            } else {
                $missingFullMarks = 100;
                $missingPassMarks = 40;
            }

            // Missing subject = 0 marks, NG grade, 0 point, failed
            $totalMarks  += 0; // 0 obtained
            $totalPoints += 0; // 0 grade point
            $failed++;
            $courses[]   = [
                'course'            => $missingCourse,
                'final_marks'       => 0,
                'calculated_marks'  => 0,
                'full_marks'        => $missingFullMarks,
                'pass_marks'        => $missingPassMarks,
                'note'              => 'Not Entered',
                'grade'             => 'NG',
                'point'             => 0.0,
                'passed'            => false,
            ];
        }

        $count = count($courses);
        $gpa   = $count > 0 ? round($totalPoints / $count, 2) : 0.0;

        // Result status: NG if missing, FAIL if failed, PASS if all passed
        $resultStatus = $missingCount > 0 ? 'NG' : ($failed > 0 ? 'FAIL' : 'PASS');

        // Rank within class (+section if specified) for this semester
        $rank = $this->getStudentRank($studentId, $semesterId, $classId, $sectionId, $sessionId);

        return compact(
            'student', 'semester', 'class', 'section', 'promotion',
            'courses', 'gpa', 'totalMarks', 'passed', 'failed', 'rank',
            'missingCount', 'missingNames', 'resultStatus'
        );
    }

    // ── Class result sheet & merit list ───────────────────────────────────────

    /**
     * All students' results for a class+section in a semester, sorted by GPA desc.
     * Used for class result sheet, merit list, and ranking.
     *
     * Returns Collection of arrays (same shape as getStudentResult, plus 'rank').
     */
    public function getClassResults(
        int $semesterId,
        int $classId,
        int $sectionId,
        int $sessionId
    ): Collection {
        $rules = $this->getGradeRules($sessionId, $semesterId, $classId);

        // Pre-load ExamRule FM/PM keyed by course_id for the report cards.
        // Uses the shared helper which prefers custom (non-default) FM/PM over 100/40.
        $examRulesByCourseClass = $this->buildExamRulesByCourse($sessionId, $semesterId, $classId);

        // Get all final marks for the class (+ section if specified) + semester
        $marksQuery = FinalMark::with(['student', 'course'])
            ->where('semester_id', $semesterId)
            ->where('class_id',    $classId)
            ->where('session_id',  $sessionId)
            ->orderBy(
                Course::select('position')
                    ->whereColumn('courses.id', 'final_marks.course_id')
                    ->limit(1)
            )
            ->orderBy('course_id');

        if ($sectionId > 0) {
            $marksQuery->where('section_id', $sectionId);
        }

        $allMarks = $marksQuery->get()->groupBy('student_id');

        // ── Per-subject merge: supplement subjects missing from each student's
        // final_marks with live Mark-table data. (See getStudentResult for the full
        // rationale.) The old `isEmpty()` gate hid entered marks whenever even one
        // subject was finalised; now we keep finalised subjects and fill the rest.
        $expectedNamesForCheck = $this->getExpectedCourseNames($classId, $sessionId);
        $needSupplement = $allMarks->isEmpty()
            || $allMarks->contains(function ($stuMarks) use ($expectedNamesForCheck) {
                $names = $stuMarks->map(fn($m) => $m->course?->course_name)->filter()->unique()->all();
                return !empty(array_diff($expectedNamesForCheck, $names));
            });

        if ($needSupplement) {
            $examIds = Exam::where('semester_id', $semesterId)
                ->where('session_id', $sessionId)
                ->where('class_id',   $classId)      // ← scope to this class only
                ->pluck('id');

            if ($examIds->isNotEmpty()) {
                $rawQuery = Mark::with(['student', 'course', 'exam.examRule'])
                    ->where('session_id', $sessionId)
                    ->where('class_id',    $classId)
                    ->whereIn('exam_id',   $examIds);

                if ($sectionId > 0) {
                    $rawQuery->where('section_id', $sectionId);
                }

                $rawMarks = $rawQuery->get();
                if ($rawMarks->isNotEmpty()) {
                    // Build a single FinalMark for one subject (a group of marks sharing a
                    // course_name). Priority: custom FM (non-default) first, then semester match,
                    // then earliest exam_id. The eager-loaded exam.examRule is keyed to the actual
                    // exam_id, so it stays correct even when mark.course_id differs from exam.course_id.
                    $buildSubjectFm = function ($cMarks) use ($examRulesByCourseClass, $semesterId) {
                        $best = null;
                        foreach ($cMarks->sortBy('exam_id') as $candidate) {
                            if ($best === null) {
                                $best = $candidate;
                                continue;
                            }
                            $bestFm         = (float) ($best->exam?->examRule?->total_marks ?? 100);
                            $candidateFm    = (float) ($candidate->exam?->examRule?->total_marks ?? 100);
                            $bestPm         = (float) ($best->exam?->examRule?->pass_marks ?? round($bestFm * 0.4, 0));
                            $candidatePm    = (float) ($candidate->exam?->examRule?->pass_marks ?? round($candidateFm * 0.4, 0));
                            $bestIsDefault      = ($bestFm === 100.0 && $bestPm === 40.0);
                            $candidateIsDefault = ($candidateFm === 100.0 && $candidatePm === 40.0);
                            // Priority 1: custom FM/PM beats default FM/PM.
                            if ($candidateIsDefault && !$bestIsDefault) {
                                continue; // keep $best
                            }
                            if (!$candidateIsDefault && $bestIsDefault) {
                                $best = $candidate;
                                continue;
                            }
                            // Priority 2: same default/custom status → prefer semester match.
                            $bestSemMatch      = (int) ($best->course?->semester_id ?? 0) === $semesterId;
                            $candidateSemMatch = (int) ($candidate->course?->semester_id ?? 0) === $semesterId;
                            if ($candidateSemMatch && !$bestSemMatch) {
                                $best = $candidate;
                                continue;
                            }
                            if (!$candidateSemMatch && $bestSemMatch) {
                                continue;
                            }
                            // Priority 3: both custom & both semester-matched (or neither) → keep earlier exam_id
                        }
                        // Among marks with the same exam_id, take the latest save
                        $best = $cMarks->where('exam_id', $best->exam_id)->sortByDesc('updated_at')->first();

                        $fm = new FinalMark();
                        $fm->student_id       = $best->student_id;
                        $fm->student          = $best->student;
                        $fm->class_id         = $best->class_id;
                        $fm->section_id       = $best->section_id;
                        $fm->course_id        = $best->course_id;
                        $fm->course           = $best->course;
                        $fm->final_marks      = (float) $best->marks;
                        $fm->calculated_marks = $fm->final_marks;
                        // Use the eager-loaded exam rule directly (correct for this exam_id)
                        // rather than the course-keyed pre-load which may map to a different
                        // exam for the same course_id.
                        $fm->full_marks       = (float) ($best->exam?->examRule?->total_marks
                            ?? $examRulesByCourseClass[$best->course_id]?->total_marks
                            ?? 100);
                        $fm->pass_marks       = (float) ($best->exam?->examRule?->pass_marks
                            ?? $examRulesByCourseClass[$best->course_id]?->pass_marks
                            ?? round($fm->full_marks * 0.4, 0));
                        return $fm;
                    };

                    // Per student: keep finalised subjects (final_marks) and supplement
                    // any subject whose name is NOT already in final_marks from the live
                    // marks. Students with no final_marks at all get all their live marks
                    // (identical to the previous empty-final_marks fallback).
                    foreach ($rawMarks->groupBy('student_id') as $studentId => $stuMarks) {
                        $fmNames = $allMarks->has($studentId)
                            ? $allMarks->get($studentId)->map(fn($m) => $m->course?->course_name)->filter()->unique()->all()
                            : [];

                        $supplemental = $stuMarks
                            ->groupBy(fn($m) => $m->course?->course_name ?? $m->course_id)
                            ->reject(fn($group, $name) => in_array($name, $fmNames, true))
                            ->map($buildSubjectFm)
                            ->sortBy(fn($fm) => [
                                ($fm->course?->position ?? 9999),
                                ($fm->course_id ?? 9999),
                            ]);

                        if ($supplemental->isNotEmpty()) {
                            $existing = $allMarks->get($studentId, collect());
                            $allMarks->put($studentId, $existing->concat($supplemental)
                                ->sortBy(fn($fm) => [
                                    ($fm->course?->position ?? 9999),
                                    ($fm->course_id ?? 9999),
                                ]));
                        }
                    }
                }
            }
        }

        // Load the expected subject names for this class so we can detect
        // missing marks per student and set NG (Not Graded) status.
        $expectedNames = $this->getExpectedCourseNames($classId, $sessionId);

        $results = $allMarks->map(function (Collection $marks) use ($rules, $examRulesByCourseClass, $expectedNames, $classId, $sessionId, $semesterId) {
            $student     = $marks->first()->student;
            $totalPoints = 0.0;
            $totalMarks  = 0.0;
            $totalMax    = 0.0;
            $passed      = 0;
            $failed      = 0;
            $aPlusCount  = 0;
            $courses     = [];

            foreach ($marks as $mark) {
                // Determine full_marks / pass_marks with correct priority:
                // Transient $mark->full_marks (set from the actual exam rule in the fallback
                // construction above) is correct even when mark.course_id ≠ exam.course_id.
                // If the transient is the auto-created default (100/40) but the lookup resolved
                // a custom FM for this subject name, the custom one is authoritative.
                [$fullMarks, $passMarks] = self::resolveFmPm(
                    $mark->full_marks ?? null,
                    $mark->pass_marks ?? null,
                    $examRulesByCourseClass[$mark->course_id] ?? null
                );

                // Pass resolved full_marks AND pass_marks so resolveGrade normalises the
                // percentage correctly AND decides pass/fail against the subject's own
                // configured pass mark (e.g. 16/40) rather than the generic grade band.
                $resolved    = $mark->resolveGrade($rules, $fullMarks, $passMarks);
                $totalPoints += $resolved['point'];
                $totalMarks  += $mark->final_marks;
                $totalMax    += $fullMarks;
                $resolved['passed'] ? $passed++ : $failed++;
                if ($resolved['grade'] === 'A+') $aPlusCount++;

                $courses[] = [
                    'course'            => $mark->course,
                    'final_marks'       => $mark->final_marks,
                    'calculated_marks'  => $mark->calculated_marks,
                    'full_marks'        => $fullMarks,
                    'pass_marks'        => $passMarks,
                    'grade'             => $resolved['grade'],
                    'point'             => $resolved['point'],
                    'passed'            => $resolved['passed'],
                ];
            }

            // ── Add missing subjects as 0-mark entries ──────────────────────────
            // Missing subjects must contribute to totals, GPA, and pass/fail.
            // An unentered subject = 0 obtained marks with the configured FM/PM.
            $enteredNames   = collect($courses)->map(fn($c) => $c['course']?->course_name ?? '')->filter()->toArray();
            $missingNames    = array_values(array_diff($expectedNames, $enteredNames));
            $missingCount    = count($missingNames);

            foreach ($missingNames as $mName) {
                $missingCourse = Course::where('class_id', $classId)
                    ->where('course_name', $mName)
                    ->where(fn($q) => $q->where('session_id', $sessionId)->orWhereNull('session_id'))
                    ->first();

                if ($missingCourse) {
                    $missingFullMarks = (float)($examRulesByCourseClass[$missingCourse->id]?->total_marks ?? 100);
                    $missingPassMarks = (float)($examRulesByCourseClass[$missingCourse->id]?->pass_marks ?? round($missingFullMarks * 0.4, 0));
                } else {
                    $missingFullMarks = 100;
                    $missingPassMarks = 40;
                }

                $totalMarks  += 0;
                $totalMax    += $missingFullMarks;
                $totalPoints += 0;
                $failed++;
                $courses[]   = [
                    'course'            => $missingCourse,
                    'final_marks'       => 0,
                    'calculated_marks'  => 0,
                    'full_marks'        => $missingFullMarks,
                    'pass_marks'        => $missingPassMarks,
                    'grade'             => 'NG',
                    'point'             => 0.0,
                    'passed'            => false,
                ];
            }

            $count      = count($courses);
            $gpa        = $count > 0 ? round($totalPoints / $count, 2) : 0.0;
            $percentage = $totalMax > 0 ? round(($totalMarks / $totalMax) * 100, 4) : 0.0;
            $resultStatus = $missingCount > 0 ? 'NG' : ($failed > 0 ? 'FAIL' : 'PASS');

            return compact('student', 'courses', 'gpa', 'totalMarks', 'totalMax', 'percentage', 'passed', 'failed', 'aPlusCount', 'missingCount', 'missingNames', 'resultStatus');
        });

        // Nepal CDC ranking — usort for reliable multi-key sort
        $sorted = $results->values()->toArray();
        usort($sorted, function ($a, $b) {
            if ($b['totalMarks'] != $a['totalMarks']) return $b['totalMarks'] <=> $a['totalMarks'];
            if ($b['gpa']        != $a['gpa'])        return $b['gpa']        <=> $a['gpa'];
            if ($b['aPlusCount'] != $a['aPlusCount']) return $b['aPlusCount'] <=> $a['aPlusCount'];
            return $a['failed'] <=> $b['failed'];
        });

        // Standard competition ranking: ties share rank, next skips
        // e.g. 3 students tied at #1 → all get #1, next student gets #4
        $rank = 1; $prevKey = null; $tieCount = 0;
        $ranked = [];
        foreach ($sorted as $row) {
            $key = $row['totalMarks'] . '_' . $row['gpa'] . '_' . $row['aPlusCount'] . '_' . $row['failed'];
            if ($prevKey !== null && $key === $prevKey) {
                $tieCount++;
            } else {
                if ($prevKey !== null) $rank += $tieCount + 1;
                $tieCount = 0;
            }
            $row['rank'] = $rank;
            $prevKey = $key;
            $ranked[] = $row;
        }
        return collect($ranked);
    }

    /**
     * Get a single student's rank in a class+section for a semester.
     * Returns 1-based integer or null if no data.
     */
    public function getStudentRank(
        int $studentId,
        int $semesterId,
        int $classId,
        int $sectionId,
        int $sessionId
    ): ?int {
        $results = $this->getClassResults($semesterId, $classId, $sectionId, $sessionId);

        $entry = $results->firstWhere('student.id', $studentId);
        return $entry ? $entry['rank'] : null;
    }

    // ── CGPA (across all semesters) ───────────────────────────────────────────

    /**
     * Calculate CGPA for a student across all semesters in a session.
     * Uses a unified grade rule lookup (first grading system found for the class).
     */
    public function getCgpa(int $studentId, int $classId, int $sessionId): array
    {
        $marks = FinalMark::with('course')
            ->where('student_id', $studentId)
            ->where('class_id',   $classId)
            ->where('session_id', $sessionId)
            ->get();

        if ($marks->isEmpty()) {
            return ['cgpa' => 0.0, 'total_courses' => 0, 'semesters' => []];
        }

        // Pre-load exam rules keyed by course_id so resolveGrade() uses the correct FM.
        // The final_marks table has no full_marks column, so without this the raw
        // final_marks value would be used directly as a percentage (wrong for non-100 FM).
        $examRulesForCgpa = $this->buildExamRulesByCourse(
            $sessionId, null, $classId
        );

        // Group by semester for per-semester GPA breakdown
        $bySemester = $marks->groupBy('semester_id');
        $semesterGpas = [];
        $allPoints   = 0.0;
        $allCount    = 0;

        foreach ($bySemester as $semesterId => $semMarks) {
            $rules = $this->getGradeRules($sessionId, $semesterId, $classId);

            $points = 0.0;
            foreach ($semMarks as $m) {
                [$fullMarks, $passMarks] = self::resolveFmPm(
                    $m->full_marks ?? null,
                    $m->pass_marks ?? null,
                    $examRulesForCgpa[$m->course_id] ?? null
                );
                $resolved = $m->resolveGrade($rules, $fullMarks, $passMarks);
                $points  += $resolved['point'];
            }

            $count   = $semMarks->count();
            $semGpa  = $count > 0 ? round($points / $count, 2) : 0.0;
            $allPoints += $points;
            $allCount  += $count;

            $semesterGpas[$semesterId] = [
                'semester_id' => $semesterId,
                'gpa'         => $semGpa,
                'courses'     => $count,
            ];
        }

        $cgpa = $allCount > 0 ? round($allPoints / $allCount, 2) : 0.0;

        return [
            'cgpa'          => $cgpa,
            'total_courses' => $allCount,
            'semesters'     => array_values($semesterGpas),
        ];
    }

    // ── Performance analytics ─────────────────────────────────────────────────

    /**
     * Average final_marks per course for a class+semester (for bar chart).
     * Returns [['course_name', 'avg_marks', 'course_id'], ...]
     */
    public function getSubjectAverages(
        int $sessionId,
        int $semesterId,
        int $classId
    ): array {
        return DB::table('final_marks')
            ->join('courses', 'final_marks.course_id', '=', 'courses.id')
            ->select(
                'courses.id as course_id',
                'courses.course_name',
                DB::raw('ROUND(AVG(final_marks.final_marks), 1) as avg_marks'),
                DB::raw('COUNT(final_marks.student_id) as student_count'),
                DB::raw('ROUND(MIN(final_marks.final_marks), 1) as min_marks'),
                DB::raw('ROUND(MAX(final_marks.final_marks), 1) as max_marks')
            )
            ->where('final_marks.session_id',  $sessionId)
            ->where('final_marks.semester_id', $semesterId)
            ->where('final_marks.class_id',    $classId)
            ->when(config('tenant.school_id'), function ($q) {
                $schoolId = config('tenant.school_id');
                return $q->where('final_marks.school_id', $schoolId)
                         ->where('courses.school_id', $schoolId);
            })
            ->groupBy('courses.id', 'courses.course_name')
            ->orderByDesc('avg_marks')
            ->get()
            ->toArray();
    }

    /**
     * Grade distribution (how many students got each grade) for a class+semester.
     * Returns [['grade', 'count'], ...]
     */
    public function getGradeDistribution(
        int $sessionId,
        int $semesterId,
        int $classId
    ): array {
        $rules  = $this->getGradeRules($sessionId, $semesterId, $classId);
        $marks  = FinalMark::where('session_id',  $sessionId)
            ->where('semester_id', $semesterId)
            ->where('class_id',    $classId)
            ->get();

        // Load exam rules so resolveGrade normalises marks correctly for non-100 FM subjects.
        $examRulesForDist = $this->buildExamRulesByCourse($sessionId, $semesterId, $classId);

        $dist = [];
        foreach ($marks as $mark) {
            [$fullMarks, $passMarks] = self::resolveFmPm(
                $mark->full_marks ?? null,
                $mark->pass_marks ?? null,
                $examRulesForDist[$mark->course_id] ?? null
            );
            $resolved  = $mark->resolveGrade($rules, $fullMarks, $passMarks);
            $grade     = $resolved['grade'];
            isset($dist[$grade]) ? $dist[$grade]++ : $dist[$grade] = 1;
        }

        arsort($dist);

        return array_map(
            fn($grade, $count) => compact('grade', 'count'),
            array_keys($dist),
            array_values($dist)
        );
    }

    /**
     * Pass/fail ratio per course for a class+semester.
     */
    public function getPassFailByCourse(
        int $sessionId,
        int $semesterId,
        int $classId
    ): array {
        // Join via exams → exam_rules to correctly resolve pass_marks per course.
        // Previously this joined exam_rules directly on courses.id which is wrong.
        return DB::table('final_marks')
            ->join('courses', 'final_marks.course_id', '=', 'courses.id')
            ->join('exams', function ($join) use ($sessionId, $semesterId, $classId) {
                $join->on('exams.course_id', '=', 'final_marks.course_id')
                     ->where('exams.session_id',  $sessionId)
                     ->where('exams.semester_id', $semesterId)
                     ->where('exams.class_id',    $classId);
            })
            ->join('exam_rules', 'exam_rules.exam_id', '=', 'exams.id')
            ->select(
                'courses.course_name',
                DB::raw('COUNT(DISTINCT final_marks.student_id) as total'),
                DB::raw('SUM(CASE WHEN (final_marks.final_marks / NULLIF(exam_rules.total_marks, 0)) * 100 >= (exam_rules.pass_marks / NULLIF(exam_rules.total_marks, 0)) * 100 THEN 1 ELSE 0 END) as passed'),
                DB::raw('SUM(CASE WHEN (final_marks.final_marks / NULLIF(exam_rules.total_marks, 0)) * 100 <  (exam_rules.pass_marks / NULLIF(exam_rules.total_marks, 0)) * 100 THEN 1 ELSE 0 END) as failed')
            )
            ->where('final_marks.session_id',  $sessionId)
            ->where('final_marks.semester_id', $semesterId)
            ->where('final_marks.class_id',    $classId)
            ->when(config('tenant.school_id'), function ($q) {
                $schoolId = config('tenant.school_id');
                return $q->where('final_marks.school_id', $schoolId)
                         ->where('courses.school_id', $schoolId);
            })
            ->groupBy('courses.id', 'courses.course_name')
            ->get()
            ->toArray();
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * True when a (total_marks, pass_marks) pair is the auto-created default that
     * MarkController writes whenever marks are entered without a configured rule
     * (FM=100, PM=40). These "shadow" rules coexist with the real custom-FM exam
     * and must never win over a custom configuration.
     */
    public static function isDefaultRule($totalMarks, $passMarks): bool
    {
        return (float) $totalMarks === 100.0 && (float) $passMarks === 40.0;
    }

    /**
     * Resolve the correct full_marks / pass_marks for a subject, choosing between
     * a per-record transient value (set from the selected exam's rule during the
     * supplemental build) and the course-keyed lookup from buildExamRulesByCourse().
     *
     * The auto-created default (100/40) is only ever a fallback. If the transient
     * is that default but the lookup resolved a custom FM for the same subject
     * name, the custom one is authoritative — the default was a shadow created at
     * marks-entry time and must not override the school's configuration.
     *
     * @return array{0:float,1:float}  [full_marks, pass_marks]
     */
    public static function resolveFmPm(?float $transientFm, ?float $transientPm, ?object $rule): array
    {
        $tFm = (float) ($transientFm ?? 0);
        $tPm = (float) ($transientPm ?? 0);
        $lFm = (float) ($rule?->total_marks ?? 0);
        $lPm = (float) ($rule?->pass_marks ?? 0);

        if (self::isDefaultRule($tFm, $tPm) && $lFm > 0 && !self::isDefaultRule($lFm, $lPm)) {
            return [$lFm, $lPm];
        }

        $fullMarks = $tFm > 0 ? $tFm : ($lFm > 0 ? $lFm : 100.0);
        $passMarks = $tPm > 0 ? $tPm : ($lPm > 0 ? $lPm : round($fullMarks * 0.4, 0));
        return [(float) $fullMarks, (float) $passMarks];
    }

    /**
     * Build an exam-rules lookup keyed by course_id for a given class + session.
     * When $semesterId is null, all semesters for the class are included (used by CGPA).
     *
     * Two resolution passes ensure the school's configured full/pass marks win:
     *  1. Per course_id — when multiple exam rows map to the same course_id, the
     *     custom (non-default) FM/PM is preferred over the auto-created 100/40.
     *  2. Per course_name — when the same subject is registered under multiple
     *     course rows (one per term, e.g. Nepali@sem12 and Nepali@sem13), a
     *     custom FM found under any of those rows is promoted to ALL of them.
     *     This is the fix for the reported Class 3 / Class 7 error: marks entered
     *     under a First-Term course row with FM=40 were graded as 100 because the
     *     Second-Term course row only had the auto-created 100/40 shadow rule.
     *
     * @return array<int, object>  course_id → stdClass{total_marks, pass_marks}
     */
    public function buildExamRulesByCourse(int $sessionId, ?int $semesterId, int $classId): array
    {
        $examRows = Exam::where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->when($semesterId !== null, fn($q) => $q->where('semester_id', $semesterId))
            ->get(['id', 'course_id']);

        if ($examRows->isEmpty()) {
            return [];
        }

        // course_id → course_name (via the exam's course relation, eagerly loaded)
        $courseIds = $examRows->pluck('course_id')->unique()->filter()->values()->all();
        $courseIdToName = Course::whereIn('id', $courseIds)
            ->pluck('course_name', 'id')->all();
        $examIdToCourseId = $examRows->pluck('course_id', 'id')->all();

        $result = [];

        // Pass 1 — per course_id: prefer a custom rule over the default.
        foreach (ExamRule::whereIn('exam_id', $examRows->pluck('id'))->get(['exam_id', 'total_marks', 'pass_marks']) as $r) {
            $cId = $examIdToCourseId[$r->exam_id] ?? null;
            if ($cId === null) continue;
            if (!isset($result[$cId])
                || (self::isDefaultRule($result[$cId]->total_marks, $result[$cId]->pass_marks)
                    && !self::isDefaultRule($r->total_marks, $r->pass_marks))) {
                $result[$cId] = $r;
            }
        }

        // Pass 2 — per course_name: promote a custom FM/PM found under any course row
        // to every course row sharing the same subject name. The 100/40 shadow rule
        // auto-created at marks-entry time must not override a real configuration that
        // lives under a sibling course row.
        $byName = [];
        foreach ($result as $cId => $r) {
            $name = $courseIdToName[$cId] ?? null;
            if ($name === null) continue;
            if (!isset($byName[$name])
                || (self::isDefaultRule($byName[$name]->total_marks, $byName[$name]->pass_marks)
                    && !self::isDefaultRule($r->total_marks, $r->pass_marks))) {
                $byName[$name] = $r;
            }
        }
        foreach ($result as $cId => $r) {
            $name = $courseIdToName[$cId] ?? null;
            if ($name !== null && isset($byName[$name])
                && self::isDefaultRule($r->total_marks, $r->pass_marks)
                && !self::isDefaultRule($byName[$name]->total_marks, $byName[$name]->pass_marks)) {
                $result[$cId] = $byName[$name];
            }
        }

        return $result;
    }

    /**
     * Get the unique set of course names configured for a class in a session.
     * These are the "required subjects" — if a student's result is missing any
     * of these names, their overall status is NG (Not Graded).
     *
     * Each semester has its own course row for the same subject, so we
     * deduplicate by course_name to get the canonical subject list.
     */
    private function getExpectedCourseNames(int $classId, int $sessionId): array
    {
        return Course::where('class_id', $classId)
            ->where(function ($q) use ($sessionId) {
                $q->where('session_id', $sessionId)->orWhereNull('session_id');
            })
            ->distinct()
            ->pluck('course_name')
            ->sort()
            ->values()
            ->toArray();
    }
}
