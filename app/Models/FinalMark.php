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
    public function resolveGrade($gradeRules, ?float $fullMarks = null): array
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
                $passed = isset($this->examRule) && $this->examRule && $this->examRule->pass_marks > 0
                    ? $fm >= $this->examRule->pass_marks
                    : $isPassingGrade;

                return [
                    'point'  => (float) $rule->point,
                    'grade'  => (string) $rule->grade,
                    'passed' => (bool) $passed,
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

        $totalPoints = 0.0;
        $passed      = 0;
        $failed      = 0;

        foreach ($marks as $mark) {
            $resolved    = $mark->resolveGrade($gradeRules);
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

        $totalPoints = 0.0;
        foreach ($marks as $mark) {
            $resolved     = $mark->resolveGrade($gradeRules);
            $totalPoints += $resolved['point'];
        }

        return round($totalPoints / $marks->count(), 2);
    }
}
