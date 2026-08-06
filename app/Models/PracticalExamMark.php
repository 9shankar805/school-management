<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PracticalExamMark extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'practical_exam_id', 'student_id', 'marks_obtained', 'remarks', 'recorded_by',
    ];

    public function practicalExam() { return $this->belongsTo(PracticalExam::class); }
    public function student()       { return $this->belongsTo(User::class, 'student_id'); }
    public function recordedBy()    { return $this->belongsTo(User::class, 'recorded_by'); }

    public function getPassedAttribute(): bool
    {
        return $this->marks_obtained >= ($this->practicalExam->pass_marks ?? 0);
    }
}
