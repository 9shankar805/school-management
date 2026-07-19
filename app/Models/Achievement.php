<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Achievement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id', 'category', 'title', 'award_type',
        'description', 'issuing_body', 'level',
        'awarded_date', 'attachment_path', 'recorded_by',
    ];

    protected $casts = [
        'awarded_date' => 'date',
    ];

    const CATEGORIES = [
        'academic'     => 'Academic',
        'sports'       => 'Sports',
        'arts'         => 'Arts',
        'science'      => 'Science',
        'community'    => 'Community Service',
        'leadership'   => 'Leadership',
        'competition'  => 'Competition',
        'other'        => 'Other',
    ];

    const LEVELS = [
        'school'        => 'School',
        'district'      => 'District',
        'state'         => 'State',
        'national'      => 'National',
        'international' => 'International',
    ];

    const LEVEL_BADGES = [
        'school'        => 'bg-slate-100 text-slate-600',
        'district'      => 'bg-blue-100 text-blue-700',
        'state'         => 'bg-indigo-100 text-indigo-700',
        'national'      => 'bg-violet-100 text-violet-700',
        'international' => 'bg-amber-100 text-amber-700',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        return $this->attachment_path
            ? Storage::url($this->attachment_path)
            : null;
    }

    public function getLevelBadgeAttribute(): string
    {
        return self::LEVEL_BADGES[$this->level] ?? 'bg-slate-100 text-slate-600';
    }
}
