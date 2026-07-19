<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'transfer_type',
        'from_session_id', 'from_class_id', 'from_section_id', 'from_school',
        'to_session_id', 'to_class_id', 'to_section_id', 'to_school',
        'transfer_date', 'reason', 'status', 'approved_by', 'approved_at', 'notes',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'approved_at'   => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function fromClass()
    {
        return $this->belongsTo(SchoolClass::class, 'from_class_id');
    }

    public function toClass()
    {
        return $this->belongsTo(SchoolClass::class, 'to_class_id');
    }

    public function fromSection()
    {
        return $this->belongsTo(Section::class, 'from_section_id');
    }

    public function toSection()
    {
        return $this->belongsTo(Section::class, 'to_section_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending'  => 'bg-amber-100 text-amber-700',
            'approved' => 'bg-emerald-100 text-emerald-700',
            'rejected' => 'bg-rose-100 text-rose-700',
            default    => 'bg-slate-100 text-slate-600',
        };
    }
}
