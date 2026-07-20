<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CurriculumTopic extends Model
{
    use HasFactory;

    protected $fillable = [
        'curriculum_id', 'title', 'description',
        'term_id', 'order', 'estimated_hours',
    ];

    public function curriculum()
    {
        return $this->belongsTo(Curriculum::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function lessonPlans()
    {
        return $this->hasMany(LessonPlan::class, 'curriculum_topic_id');
    }
}
