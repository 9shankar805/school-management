<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionPrintLog extends Model
{
    use HasFactory;

    protected $fillable = ['paper_id', 'printed_by', 'copies', 'ip_address'];

    public function paper()   { return $this->belongsTo(QuestionPaper::class, 'paper_id'); }
    public function printer() { return $this->belongsTo(User::class, 'printed_by'); }
}
