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
            ->where('session_id',  $sessionId);

        if ($sectionId > 0) {
            $marksQuery->where('section_id', $sectionId);
        }

        $marks = $marksQuery->get();

        // Pre-load ExamRule total_marks per course for correct full_marks display
        $examIds = Exam::where('semester_id', $semesterId)
            ->where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->get(['id', 'course_id']);
        $examRulesByCourse = ExamRule::whereIn('exam_id', $examIds->pluck('id'))
            ->get(['exam_id', 'total_marks', 'pass_marks'])
            ->keyBy(fn($r) => $examIds->firstWhere('id', $r->exam_id)?->course_id ?? 0);

        // Fallback: If FinalMark is not finalized yet for this term, aggregate from Mark table
        if ($marks->isEmpty()) {
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
                    // Group by course_id then take the LATEST mark per course (not sum across all exams)
                    $byCourse = $rawMarks->groupBy('course_id');
                    $marks = $byCourse->map(function ($cMarks) use ($studentId, $classId, $sectionId, $semesterId, $sessionId) {
                        // Use only the most recent mark entry for this course in this semester
                        $latest = $cMarks->sortByDesc('id')->first();
                        $fm = new FinalMark();
                        $fm->student_id       = $studentId;
                        $fm->class_id         = $classId;
                        $fm->section_id       = $sectionId > 0 ? $sectionId : ($cMarks->first()->section_id ?? 0);
                        $fm->course_id        = $latest->course_id;
                        $fm->course           = $latest->course;
                        $fm->semester_id      = $semesterId;
                        $fm->session_id       = $sessionId;
                        $fm->final_marks      = (float) $latest->marks;
                        $fm->calculated_marks = $fm->final_marks;
                        $fm->note             = 'Recorded Exam Marks';
                        $fm->full_marks       = (float) ($latest->exam?->examRule?->total_marks ?? 100);
                        return $fm;
                    })->values();
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
            $resolved = $mark->resolveGrade($rules);
            $totalPoints += $resolved['point'];
            $totalMarks  += $mark->final_marks;
            $resolved['passed'] ? $passed++ : $failed++;

            $courses[] = [
                'course'            => $mark->course,
                'final_marks'       => $mark->final_marks,
                'calculated_marks'  => $mark->calculated_marks,
                'full_marks'        => (float)($examRulesByCourse[$mark->course_id]?->total_marks ?? $mark->full_marks ?? 100),
                'note'              => $mark->note,
                'grade'             => $resolved['grade'],
                'point'             => $resolved['point'],
                'passed'            => $resolved['passed'],
            ];
        }

        $count = count($courses);
        $gpa   = $count > 0 ? round($totalPoints / $count, 2) : 0.0;

        // Rank within class (+section if specified) for this semester
        $rank = $this->getStudentRank($studentId, $semesterId, $classId, $sectionId, $sessionId);

        return compact(
            'student', 'semester', 'class', 'section', 'promotion',
            'courses', 'gpa', 'totalMarks', 'passed', 'failed', 'rank'
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

        // Get all final marks for the class (+ section if specified) + semester
        $marksQuery = FinalMark::with(['student', 'course'])
            ->where('semester_id', $semesterId)
            ->where('class_id',    $classId)
            ->where('session_id',  $sessionId);

        if ($sectionId > 0) {
            $marksQuery->where('section_id', $sectionId);
        }

        $allMarks = $marksQuery->get()->groupBy('student_id');

        // Fallback: If FinalMark is empty, compute from Mark table
        if ($allMarks->isEmpty()) {
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
                    $allMarks = $rawMarks->groupBy('student_id')->map(function ($stuMarks) {
                        return $stuMarks->groupBy('course_id')->map(function ($cMarks) {
                            $fm = new FinalMark();
                            $fm->student_id       = $cMarks->first()->student_id;
                            $fm->student          = $cMarks->first()->student;
                            $fm->class_id         = $cMarks->first()->class_id;
                            $fm->section_id       = $cMarks->first()->section_id;
                            $fm->course_id        = $cMarks->first()->course_id;
                            $fm->course           = $cMarks->first()->course;
                            // Take LATEST mark only — do NOT sum across semesters
                            $latest = $cMarks->sortByDesc('id')->first();
                            $fm->student_id       = $latest->student_id;
                            $fm->student          = $latest->student;
                            $fm->class_id         = $latest->class_id;
                            $fm->section_id       = $latest->section_id;
                            $fm->course_id        = $latest->course_id;
                            $fm->course           = $latest->course;
                            $fm->final_marks      = (float) $latest->marks;
                            $fm->calculated_marks = $fm->final_marks;
                            $fm->full_marks       = (float) ($latest->exam?->examRule?->total_marks ?? 100);
                            return $fm;
                        });
                    });
                }
            }
        }

        $results = $allMarks->map(function (Collection $marks) use ($rules) {
            $student     = $marks->first()->student;
            $totalPoints = 0.0;
            $totalMarks  = 0.0;
            $totalMax    = 0.0;
            $passed      = 0;
            $failed      = 0;
            $aPlusCount  = 0;
            $courses     = [];

            foreach ($marks as $mark) {
                $resolved    = $mark->resolveGrade($rules);
                $totalPoints += $resolved['point'];
                $totalMarks  += $mark->final_marks;
                $totalMax    += ($mark->full_marks ?? 100);
                $resolved['passed'] ? $passed++ : $failed++;
                if ($resolved['grade'] === 'A+') $aPlusCount++;

                $courses[] = [
                    'course'            => $mark->course,
                    'final_marks'       => $mark->final_marks,
                    'calculated_marks'  => $mark->calculated_marks,
                    'grade'             => $resolved['grade'],
                    'point'             => $resolved['point'],
                    'passed'            => $resolved['passed'],
                ];
            }

            $count      = count($courses);
            $gpa        = $count > 0 ? round($totalPoints / $count, 2) : 0.0;
            $percentage = $totalMax > 0 ? round(($totalMarks / $totalMax) * 100, 4) : 0.0;

            return compact('student', 'courses', 'gpa', 'totalMarks', 'totalMax', 'percentage', 'passed', 'failed', 'aPlusCount');
        })
        // Nepal CDC ranking: primary = total marks desc, secondary = GPA desc,
        // tertiary = A+ count desc, quaternary = failed count asc
        ->sortBy([
            fn($a, $b) => $b['totalMarks']  <=> $a['totalMarks'],
            fn($a, $b) => $b['gpa']          <=> $a['gpa'],
            fn($a, $b) => $b['aPlusCount']   <=> $a['aPlusCount'],
            fn($a, $b) => $a['failed']        <=> $b['failed'],
        ])
        ->values();

        // Standard competition ranking (Nepal practice):
        // Tied students share the SAME rank; next rank skips by tie size
        // e.g. two students at #2 → next is #4 (not #3)
        $rank      = 1;
        $prevKey   = null;
        $sameCount = 0;

        return $results->map(function ($row) use (&$rank, &$prevKey, &$sameCount) {
            // Tie key = totalMarks + GPA + aPlusCount + failed
            $key = $row['totalMarks'] . '_' . $row['gpa'] . '_' . $row['aPlusCount'] . '_' . $row['failed'];

            if ($prevKey !== null && $key === $prevKey) {
                $sameCount++;
            } else {
                $rank     += $sameCount;
                $sameCount = 0;
            }
            $row['rank'] = $rank;
            $prevKey     = $key;
            return $row;
        });
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

        // Group by semester for per-semester GPA breakdown
        $bySemester = $marks->groupBy('semester_id');
        $semesterGpas = [];
        $allPoints   = 0.0;
        $allCount    = 0;

        foreach ($bySemester as $semesterId => $semMarks) {
            $rules = $this->getGradeRules($sessionId, $semesterId, $classId);

            $points = 0.0;
            foreach ($semMarks as $m) {
                $resolved = $m->resolveGrade($rules);
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

        $dist = [];
        foreach ($marks as $mark) {
            $resolved = $mark->resolveGrade($rules);
            $grade    = $resolved['grade'];
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
        return DB::table('final_marks')
            ->join('courses', 'final_marks.course_id', '=', 'courses.id')
            ->join('exam_rules', function ($join) use ($sessionId) {
                $join->on('exam_rules.exam_id', '=', 'courses.id') // approximation
                     ->where('exam_rules.session_id', $sessionId);
            })
            ->select(
                'courses.course_name',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN final_marks.final_marks >= exam_rules.pass_marks THEN 1 ELSE 0 END) as passed'),
                DB::raw('SUM(CASE WHEN final_marks.final_marks < exam_rules.pass_marks THEN 1 ELSE 0 END) as failed')
            )
            ->where('final_marks.session_id',  $sessionId)
            ->where('final_marks.semester_id', $semesterId)
            ->where('final_marks.class_id',    $classId)
            ->when(config('tenant.school_id'), function ($q) {
                $schoolId = config('tenant.school_id');
                return $q->where('final_marks.school_id', $schoolId)
                         ->where('courses.school_id', $schoolId)
                         ->where('exam_rules.school_id', $schoolId);
            })
            ->groupBy('courses.course_name')
            ->get()
            ->toArray();
    }
}
