<?php

namespace App\Repositories;

use App\Models\FinalMark;
use App\Models\GradingSystem;
use App\Models\GradeRule;
use App\Models\Semester;
use App\Models\User;
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
     * Returns empty collection if no grading system configured.
     */
    public function getGradeRules(int $sessionId, int $semesterId, int $classId): Collection
    {
        $system = GradingSystem::where('session_id',  $sessionId)
            ->where('semester_id', $semesterId)
            ->where('class_id',    $classId)
            ->first();

        if (! $system) return collect();

        return GradeRule::where('grading_system_id', $system->id)
            ->where('session_id', $sessionId)
            ->orderByDesc('start_at')
            ->get();
    }

    // ── Student result sheet ──────────────────────────────────────────────────

    /**
     * Complete result sheet for one student in one semester.
     *
     * Returns array:
     * [
     *   'student'     => User,
     *   'semester'    => Semester,
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
        $student  = User::findOrFail($studentId);
        $semester = Semester::find($semesterId);
        $rules    = $this->getGradeRules($sessionId, $semesterId, $classId);

        $marks = FinalMark::with('course')
            ->where('student_id',  $studentId)
            ->where('semester_id', $semesterId)
            ->where('class_id',    $classId)
            ->where('section_id',  $sectionId)
            ->where('session_id',  $sessionId)
            ->get();

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
                'note'              => $mark->note,
                'grade'             => $resolved['grade'],
                'point'             => $resolved['point'],
                'passed'            => $resolved['passed'],
            ];
        }

        $count = count($courses);
        $gpa   = $count > 0 ? round($totalPoints / $count, 2) : 0.0;

        // Rank within class+section for this semester
        $rank = $this->getStudentRank($studentId, $semesterId, $classId, $sectionId, $sessionId);

        return compact('student', 'semester', 'courses', 'gpa', 'totalMarks', 'passed', 'failed', 'rank');
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

        // Get all final marks for the class+section+semester
        $allMarks = FinalMark::with(['student', 'course'])
            ->where('semester_id', $semesterId)
            ->where('class_id',    $classId)
            ->where('section_id',  $sectionId)
            ->where('session_id',  $sessionId)
            ->get()
            ->groupBy('student_id');

        $results = $allMarks->map(function (Collection $marks) use ($rules) {
            $student     = $marks->first()->student;
            $totalPoints = 0.0;
            $totalMarks  = 0.0;
            $passed      = 0;
            $failed      = 0;
            $courses     = [];

            foreach ($marks as $mark) {
                $resolved    = $mark->resolveGrade($rules);
                $totalPoints += $resolved['point'];
                $totalMarks  += $mark->final_marks;
                $resolved['passed'] ? $passed++ : $failed++;

                $courses[] = [
                    'course'            => $mark->course,
                    'final_marks'       => $mark->final_marks,
                    'calculated_marks'  => $mark->calculated_marks,
                    'grade'             => $resolved['grade'],
                    'point'             => $resolved['point'],
                    'passed'            => $resolved['passed'],
                ];
            }

            $count = count($courses);
            $gpa   = $count > 0 ? round($totalPoints / $count, 2) : 0.0;

            return compact('student', 'courses', 'gpa', 'totalMarks', 'passed', 'failed');
        })
        ->sortByDesc('gpa')
        ->values();

        // Assign ranks (ties share the same rank)
        $rank    = 1;
        $prev    = null;
        $sameCount = 0;

        return $results->map(function ($row) use (&$rank, &$prev, &$sameCount) {
            if ($prev !== null && $row['gpa'] === $prev) {
                $sameCount++;
            } else {
                $rank += $sameCount;
                $sameCount = 0;
            }
            $row['rank'] = $rank;
            $prev        = $row['gpa'];
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
            ->groupBy('courses.course_name')
            ->get()
            ->toArray();
    }
}
