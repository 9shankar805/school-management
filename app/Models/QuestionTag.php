<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionTag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'color'];

    public function questions()
    {
        return $this->belongsToMany(QuestionBank::class, 'question_bank_tag', 'question_tag_id', 'question_bank_id');
    }
}
