<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class QuestionTag extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = ['school_id', 'name', 'color'];

    public function questions()
    {
        return $this->belongsToMany(QuestionBank::class, 'question_bank_tag', 'question_tag_id', 'question_bank_id');
    }
}
