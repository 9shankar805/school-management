<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class OnlineExamAnswer extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'attempt_id', 'question_id', 'answer_text',
        'marks_awarded', 'is_correct', 'feedback',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function attempt()  { return $this->belongsTo(OnlineExamAttempt::class, 'attempt_id'); }
    public function question() { return $this->belongsTo(OnlineExamQuestion::class, 'question_id'); }
}
