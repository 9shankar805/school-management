<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'status', 'session_id', 'class_id',
        'effective_date', 'reason', 'notes',
        'graduation_certificate_no', 'alumni_batch',
        'destination_school', 'is_current', 'processed_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'is_current'     => 'boolean',
    ];

    const STATUSES = [
        'active'      => 'Active',
        'graduated'   => 'Graduated',
        'dropped_out' => 'Dropped Out',
        'withdrawn'   => 'Withdrawn',
        'alumni'      => 'Alumni',
        'suspended'   => 'Suspended',
        'transferred' => 'Transferred',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function session()
    {
        return $this->belongsTo(SchoolSession::class, 'session_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active'      => 'bg-emerald-100 text-emerald-700',
            'graduated'   => 'bg-indigo-100 text-indigo-700',
            'alumni'      => 'bg-blue-100 text-blue-700',
            'dropped_out' => 'bg-rose-100 text-rose-700',
            'withdrawn'   => 'bg-orange-100 text-orange-700',
            'suspended'   => 'bg-amber-100 text-amber-700',
            'transferred' => 'bg-slate-100 text-slate-600',
            default       => 'bg-slate-100 text-slate-600',
        };
    }
}
