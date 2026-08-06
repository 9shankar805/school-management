<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PracticalExam extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'title', 'description', 'course_id', 'class_id', 'session_id',
        'exam_date', 'total_marks', 'pass_marks', 'venue',
        'equipment_required', 'examiner_id',
    ];

    protected $casts = ['exam_date' => 'date'];

    public function course()    { return $this->belongsTo(Course::class); }
    public function schoolClass(){ return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function session()   { return $this->belongsTo(SchoolSession::class, 'session_id'); }
    public function examiner()  { return $this->belongsTo(User::class, 'examiner_id'); }
    public function marks()     { return $this->hasMany(PracticalExamMark::class); }
}
