<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class StaffDocument extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'user_id', 'title', 'document_type', 'file_path', 'file_name',
        'is_verified', 'verified_by', 'verified_at', 'expiry_date', 'notes',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'expiry_date' => 'date',
    ];

    const TYPES = [
        'national_id'   => 'National ID',
        'degree'        => 'Degree/Diploma',
        'contract'      => 'Contract',
        'experience'    => 'Experience Letter',
        'certificate'   => 'Certificate',
        'other'         => 'Other',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedByUser()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->document_type] ?? ucfirst($this->document_type);
    }

    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }
}
