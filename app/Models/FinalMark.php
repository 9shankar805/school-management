<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class FinalMark extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'calculated_marks', 'final_marks', 'note',
        'student_id', 'class_id', 'section_id',
        'course_id', 'semester_id', 'session_id',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    // ── GPA helpers ───────────────────────────────────────────────────────────

    /**
     * Look up this record's grade point and grade letter from a set of GradeRule objects.
     * Returns ['point' => float, 'grade' => string, 'passed' => bool].
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $gradeRules
     */
    public function resolveGrade($gradeRules, ?float $fullMarks = null, ?float $passMarks = null): array
    {
        // Grade rules use a percentage scale (0–100).
        // Normalise final_marks to a percentage before matching.
        $fm    = (float) $this->final_marks;
        $total = $fullMarks ?? (float)($this->full_marks ?? 0);

        if ($total > 0 && $total != 100) {
            // Guard: if marks were entered out of 100 but total was recorded lower than obtained
            if ($fm > $total && $fm <= 100) {
                $score = $fm;
            } else {
                $score = round(($fm / $total) * 100, 4);
            }
        } else {
            $score = $fm;
        }
        // Normalize score between 0 and 100 for rule matching
        $score = min(100.0, max(0.0, $score));

        foreach ($gradeRules as $rule) {
            if ($score >= $rule->start_at && $score <= $rule->end_at) {
                $isPassingGrade = !in_array(strtoupper(trim($rule->grade)), ['NG', 'F', 'FAIL']) && (float)$rule->point > 0;

                // Pass/fail MUST be decided by comparing the student's obtained marks
                // ($fm) against the configured minimum pass mark for THIS subject — NOT
                // by whether the grade letter happens to be a "passing grade".
                //
                // The grade is awarded on a percentage band (Nepal CDC: 35–39.99% → "D",
                // point 1.6). A subject may have a pass mark of 16/40 (40%); a student
                // scoring 15/40 = 37.5% earns a "D" grade — a passing grade — yet 15 is
                // below the real pass mark of 16, so the student must be FAIL. Relying on
                // the grade band alone causes exactly that incorrect PASS.
                //
                // $passMarks is resolved per subject/class/exam by the caller
                // (ResultRepository) and passed in explicitly. FinalMark has no exam_id /
                // examRule relation (final_marks stores only course_id), so the caller-
                // provided pass mark is authoritative. The grade-based $isPassingGrade is
                // kept only as a last-resort fallback for legacy callers that supply none.
                if ($passMarks !== null && $passMarks > 0) {
                    $passed = $fm >= $passMarks;
                } elseif (isset($this->examRule) && $this->examRule && $this->examRule->pass_marks > 0) {
                    $passed = $fm >= $this->examRule->pass_marks;
                } else {
                    $passed = $isPassingGrade;
                }

                // A student who scored below the subject's configured pass mark has
                // FAILED. Their grade must be "NG" (No Grade) with 0.0 grade point —
                // NOT the percentage-band letter their score happened to fall into.
                //
                // Without this, a student below pass marks but landing in the 35–
                // 39.99% band receives "D" (a passing-grade letter) even though they
                // actually failed, because the band lookup and the pass/fail check
                // are independent. Forcing NG here makes the grade reflect the real
                // outcome and keeps GPA/CGPA correct (a failed subject contributes 0
                // points, matching the missing-subject path).
                if (! $passed) {
                    return [
                        'point'  => 0.0,
                        'grade'  => 'NG',
                        'passed' => false,
                    ];
                }

                return [
                    'point'  => (float) $rule->point,
                    'grade'  => (string) $rule->grade,
                    'passed' => true,
                ];
            }
        }
        return ['point' => 0.0, 'grade' => 'NG', 'passed' => false];
    }

    // ── Static GPA/CGPA utilities ─────────────────────────────────────────────

    /**
     * Calculate GPA for a student in one semester.
     *
     * @param  int    $studentId
     * @param  int    $semesterId
     * @param  int    $sessionId
     * @param  \Illuminate\Database\Eloquent\Collection  $gradeRules
     * @return array  ['gpa' => float, 'courses' => int, 'passed' => int, 'failed' => int]
     */
    public static function calculateGpa(
        int $studentId,
        int $semesterId,
        int $sessionId,
        $gradeRules
    ): array {
        $marks = static::with('course')
            ->where('student_id', $studentId)
            ->where('semester_id', $semesterId)
            ->where('session_id',  $sessionId)
            ->get();

        if ($marks->isEmpty()) {
            return ['gpa' => 0.0, 'courses' => 0, 'passed' => 0, 'failed' => 0];
        }

        // Resolve per-course full/pass marks from exam rules so pass/fail uses the
        // configured subject-specific pass mark, not just the grade band.
        $classId           = (int) ($marks->first()->class_id ?? 0);
        $examRulesByCourse = $classId > 0
            ? self::examRulesByCourse($sessionId, $semesterId, $classId)
            : [];

        $totalPoints = 0.0;
        $passed      = 0;
        $failed      = 0;

        foreach ($marks as $mark) {
            $fullMarks = (float)($examRulesByCourse[$mark->course_id]?->total_marks ?? 100);
            $passMarks = (float)($examRulesByCourse[$mark->course_id]?->pass_marks ?? round($fullMarks * 0.4, 0));
            $resolved    = $mark->resolveGrade($gradeRules, $fullMarks, $passMarks);
            $totalPoints += $resolved['point'];
            $resolved['passed'] ? $passed++ : $failed++;
        }

        $count = $marks->count();
        $gpa   = $count > 0 ? round($totalPoints / $count, 2) : 0.0;

        return compact('gpa', 'passed', 'failed') + ['courses' => $count];
    }

    /**
     * Calculate CGPA across all semesters in a session.
     *
     * @param  int    $studentId
     * @param  int    $sessionId
     * @param  \Illuminate\Database\Eloquent\Collection  $gradeRules
     * @return float
     */
    public static function calculateCgpa(
        int $studentId,
        int $sessionId,
        $gradeRules
    ): float {
        $marks = static::where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->get();

        if ($marks->isEmpty()) return 0.0;

        // Resolve per-course full/pass marks from exam rules so the grade (and any
        // pass/fail derived from it) honours the subject-specific pass mark.
        $classId           = (int) ($marks->first()->class_id ?? 0);
        $examRulesByCourse = $classId > 0
            ? self::examRulesByCourse($sessionId, null, $classId)
            : [];

        $totalPoints = 0.0;
        foreach ($marks as $mark) {
            $fullMarks = (float)($examRulesByCourse[$mark->course_id]?->total_marks ?? 100);
            $passMarks = (float)($examRulesByCourse[$mark->course_id]?->pass_marks ?? round($fullMarks * 0.4, 0));
            $resolved     = $mark->resolveGrade($gradeRules, $fullMarks, $passMarks);
            $totalPoints += $resolved['point'];
        }

        return round($totalPoints / $marks->count(), 2);
    }

    /**
     * Build an exam-rules lookup keyed by course_id for a given class + session.
     * Mirrors ResultRepository::buildExamRulesByCourse but kept self-contained on
     * the model so the static GPA/CGPA helpers can resolve correct full/pass marks
     * without a circular dependency on the repository.
     *
     * Two resolution passes (per course_id, then per course_name) ensure the
     * school's configured full/pass marks win over the auto-created 100/40
     * shadow rule even when the real configuration lives under a sibling course
     * row (e.g. a First-Term course row) while the marks/FinalMark use a
     * Second-Term course row.
     *
     * @return array<int, object>  course_id → stdClass{exam_id, total_marks, pass_marks}
     */
    private static function examRulesByCourse(int $sessionId, ?int $semesterId, int $classId): array
    {
        $examRows = \App\Models\Exam::where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->when($semesterId !== null, fn($q) => $q->where('semester_id', $semesterId))
            ->get(['id', 'course_id']);

        if ($examRows->isEmpty()) {
            return [];
        }

        $examIdToCourseId = $examRows->pluck('course_id', 'id')->all();
        $courseIds = $examRows->pluck('course_id')->unique()->filter()->values()->all();
        $courseIdToName = \App\Models\Course::whereIn('id', $courseIds)
            ->pluck('course_name', 'id')->all();

        $isDefault = function ($tm, $pm) {
            return (float) $tm === 100.0 && (float) $pm === 40.0;
        };

        $result = [];

        // Pass 1 — per course_id: prefer a custom rule over the default.
        foreach (\App\Models\ExamRule::whereIn('exam_id', $examRows->pluck('id'))->get(['exam_id', 'total_marks', 'pass_marks']) as $r) {
            $cId = $examIdToCourseId[$r->exam_id] ?? null;
            if ($cId === null) {
                continue;
            }
            if (!isset($result[$cId])
                || ($isDefault($result[$cId]->total_marks, $result[$cId]->pass_marks)
                    && !$isDefault($r->total_marks, $r->pass_marks))) {
                $result[$cId] = $r;
            }
        }

        // Pass 2 — per course_name: promote a custom FM/PM found under any course
        // row to every sibling course row sharing the same subject name.
        $byName = [];
        foreach ($result as $cId => $r) {
            $name = $courseIdToName[$cId] ?? null;
            if ($name === null) continue;
            if (!isset($byName[$name])
                || ($isDefault($byName[$name]->total_marks, $byName[$name]->pass_marks)
                    && !$isDefault($r->total_marks, $r->pass_marks))) {
                $byName[$name] = $r;
            }
        }
        foreach ($result as $cId => $r) {
            $name = $courseIdToName[$cId] ?? null;
            if ($name !== null && isset($byName[$name])
                && $isDefault($r->total_marks, $r->pass_marks)
                && !$isDefault($byName[$name]->total_marks, $byName[$name]->pass_marks)) {
                $result[$cId] = $byName[$name];
            }
        }

        return $result;
    }
}
