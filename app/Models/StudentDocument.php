<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class StudentDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id', 'document_type', 'title',
        'file_path', 'file_name', 'mime_type', 'file_size',
        'notes', 'uploaded_by', 'is_verified',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    const TYPES = [
        'birth_certificate'  => 'Birth Certificate',
        'national_id'        => 'National ID',
        'passport'           => 'Passport',
        'previous_marksheet' => 'Previous Marksheet',
        'transfer_certificate'=> 'Transfer Certificate',
        'medical_certificate'=> 'Medical Certificate',
        'photo'              => 'Photo',
        'other'              => 'Other',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->file_size ?? 0;
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 0) . ' KB';
        return $bytes . ' B';
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function isPdf(): bool
    {
        return ($this->mime_type ?? '') === 'application/pdf';
    }
}
