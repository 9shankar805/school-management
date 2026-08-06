<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class QuestionQuestion extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $table = 'question_questions';

    protected $fillable = [
        'school_id',
        'section_id', 'bank_id', 'question_type', 'question_text',
        'answer_text', 'options', 'correct_answer', 'allocated_marks',
        'difficulty', 'chapter', 'bloom_taxonomy', 'numbering', 'sort_order',
    ];

    protected $casts = ['options' => 'array'];

    public function section()  { return $this->belongsTo(QuestionSection::class, 'section_id'); }
    public function bankItem() { return $this->belongsTo(QuestionBank::class, 'bank_id'); }
    public function images()   { return $this->hasMany(QuestionImage::class, 'question_id'); }
}
