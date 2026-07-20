<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionSection extends Model
{
    use HasFactory;

    protected $fillable = ['paper_id', 'title', 'instructions', 'total_marks', 'sort_order'];

    public function paper()     { return $this->belongsTo(QuestionPaper::class, 'paper_id'); }
    public function questions() { return $this->hasMany(QuestionQuestion::class, 'section_id')->orderBy('sort_order'); }

    /** Recalculate total_marks from children and save. */
    public function recalcMarks(): void
    {
        $this->update(['total_marks' => $this->questions()->sum('allocated_marks')]);
    }
}
