<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class OnlineExamQuestion extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'online_exam_id', 'question_text', 'question_type',
        'marks', 'sort_order', 'options', 'correct_answer', 'explanation',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function onlineExam() { return $this->belongsTo(OnlineExam::class); }
    public function answers()    { return $this->hasMany(OnlineExamAnswer::class, 'question_id'); }

    public function isAutoGradable(): bool
    {
        return in_array($this->question_type, ['mcq', 'true_false'], true);
    }

    public function checkAnswer(string $studentAnswer): bool
    {
        return trim(strtolower($studentAnswer)) === trim(strtolower($this->correct_answer ?? ''));
    }
}
