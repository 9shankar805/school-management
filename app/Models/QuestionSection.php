<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class QuestionSection extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = ['school_id', 'paper_id', 'title', 'instructions', 'total_marks', 'sort_order'];

    public function paper()     { return $this->belongsTo(QuestionPaper::class, 'paper_id'); }
    public function questions() { return $this->hasMany(QuestionQuestion::class, 'section_id')->orderBy('sort_order'); }

    /** Recalculate total_marks from children and save. */
    public function recalcMarks(): void
    {
        $this->update(['total_marks' => $this->questions()->sum('allocated_marks')]);
    }
}
