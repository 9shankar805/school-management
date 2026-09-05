<?php

namespace App\Models;
use App\Traits\HasTenant;

use App\Models\GradeRule;
use App\Models\Semester;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GradingSystem extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'system_name',
        'class_id',
        'semester_id',
        'session_id'
    ];

    public function semester() {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function schoolClass() {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function gradeRules() {
        return $this->hasMany(GradeRule::class, 'grading_system_id');
    }
}
