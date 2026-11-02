<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ExamRule extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'total_marks',
        'pass_marks',
        'marks_distribution_note',
        'exam_id',
        'session_id'
    ];

    /**
     * Clamp existing marks stored for an exam down to its configured full marks
     * so no stored value can exceed the maximum.
     *
     * This MUST run whenever an exam rule's total_marks is changed (especially
     * reduced). Marks entered while the full marks were higher (e.g. 100) become
     * mathematically impossible values (e.g. 60 out of 50) once the full marks are
     * lowered. The system already caps marks to full marks at entry time; this
     * applies the same cap retroactively whenever the ceiling moves.
     *
     * @param  int    $examId
     * @param  float  $totalMarks  The new (current) full marks.
     * @return int    Number of mark rows that were clamped.
     */
    public static function clampMarksToFullMarks(int $examId, float $totalMarks): int
    {
        if ($examId <= 0 || $totalMarks <= 0) {
            return 0;
        }

        return \Illuminate\Support\Facades\DB::table('marks')
            ->where('exam_id', $examId)
            ->where('marks', '>', $totalMarks)
            ->update(['marks' => $totalMarks]);
    }
}
