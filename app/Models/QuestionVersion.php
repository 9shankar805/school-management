<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionVersion extends Model
{
    use HasFactory;

    protected $fillable = ['paper_id', 'version_number', 'snapshot', 'change_summary', 'saved_by'];
    protected $casts    = ['snapshot' => 'array'];

    public function paper()  { return $this->belongsTo(QuestionPaper::class, 'paper_id'); }
    public function saver()  { return $this->belongsTo(User::class, 'saved_by'); }
}
