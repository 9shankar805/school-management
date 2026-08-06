<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class QuestionDownloadLog extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = ['school_id', 'paper_id', 'downloaded_by', 'format', 'ip_address'];

    public function paper()      { return $this->belongsTo(QuestionPaper::class, 'paper_id'); }
    public function downloader() { return $this->belongsTo(User::class, 'downloaded_by'); }
}
