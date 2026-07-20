<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionBank extends Model
{
    use HasFactory;

    protected $table = 'question_bank';

    protected $fillable = [
        'question_type', 'question_text', 'answer_text',
        'options', 'correct_answer', 'allocated_marks',
        'difficulty', 'subject', 'chapter',
        'bloom_taxonomy', 'learning_outcome',
        'category_id', 'created_by', 'is_active',
    ];

    protected $casts = [
        'options'   => 'array',
        'is_active' => 'boolean',
    ];

    const QUESTION_TYPES = [
        'essay'        => 'Essay',
        'mcq'          => 'MCQ',
        'fill_blank'   => 'Fill in the Blank',
        'true_false'   => 'True / False',
        'match'        => 'Match the Following',
        'short_answer' => 'Short Answer',
        'long_answer'  => 'Long Answer',
        'diagram'      => 'Diagram Based',
        'case_study'   => 'Case Study',
        'programming'  => 'Programming',
        'practical'    => 'Practical',
        'numerical'    => 'Numerical',
    ];

    const DIFFICULTIES = ['easy' => 'Easy', 'medium' => 'Medium', 'hard' => 'Hard'];

    const BLOOM_LEVELS = [
        'remember'   => 'Remember',
        'understand' => 'Understand',
        'apply'      => 'Apply',
        'analyse'    => 'Analyse',
        'evaluate'   => 'Evaluate',
        'create'     => 'Create',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function category()  { return $this->belongsTo(QuestionCategory::class, 'category_id'); }
    public function creator()   { return $this->belongsTo(User::class, 'created_by'); }
    public function tags()      { return $this->belongsToMany(QuestionTag::class, 'question_bank_tag', 'question_bank_id', 'question_tag_id'); }
    public function images()    { return $this->hasMany(QuestionImage::class, 'bank_id'); }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getDifficultyBadgeAttribute(): string
    {
        return match ($this->difficulty) {
            'easy'   => 'bg-emerald-100 text-emerald-700',
            'medium' => 'bg-amber-100 text-amber-700',
            'hard'   => 'bg-rose-100 text-rose-700',
            default  => 'bg-slate-100 text-slate-500',
        };
    }
}
