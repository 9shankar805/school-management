<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionQuestion extends Model
{
    use HasFactory;

    protected $table = 'question_questions';

    protected $fillable = [
        'section_id', 'bank_id', 'question_type', 'question_text',
        'answer_text', 'options', 'correct_answer', 'allocated_marks',
        'difficulty', 'chapter', 'bloom_taxonomy', 'numbering', 'sort_order',
    ];

    protected $casts = ['options' => 'array'];

    public function section()  { return $this->belongsTo(QuestionSection::class, 'section_id'); }
    public function bankItem() { return $this->belongsTo(QuestionBank::class, 'bank_id'); }
    public function images()   { return $this->hasMany(QuestionImage::class, 'question_id'); }
}
