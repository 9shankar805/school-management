<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionDownloadLog extends Model
{
    use HasFactory;

    protected $fillable = ['paper_id', 'downloaded_by', 'format', 'ip_address'];

    public function paper()      { return $this->belongsTo(QuestionPaper::class, 'paper_id'); }
    public function downloader() { return $this->belongsTo(User::class, 'downloaded_by'); }
}
