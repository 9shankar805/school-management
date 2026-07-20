<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class QuestionImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id', 'bank_id', 'file_path',
        'original_name', 'file_size', 'mime_type',
        'caption', 'uploaded_by',
    ];

    public function question()   { return $this->belongsTo(QuestionQuestion::class, 'question_id'); }
    public function bankItem()   { return $this->belongsTo(QuestionBank::class, 'bank_id'); }
    public function uploader()   { return $this->belongsTo(User::class, 'uploaded_by'); }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }
}
