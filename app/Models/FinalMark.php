<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinalMark extends Model
{
    use HasFactory;

    protected $fillable = [
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
    public function resolveGrade($gradeRules): array
    {
        foreach ($gradeRules as $rule) {
            if ($this->final_marks >= $rule->start_at && $this->final_marks <= $rule->end_at) {
                return [
                    'point'  => (float) $rule->point,
                    'grade'  => (string) $rule->grade,
                    'passed' => $this->final_marks >= ($this->examRule?->pass_marks ?? 0),
                ];
            }
        }
        return ['point' => 0.0, 'grade' => 'F', 'passed' => false];
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
