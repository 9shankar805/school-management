<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdmissionApplication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'application_number', 'first_name', 'last_name', 'email', 'phone',
        'birthday', 'gender', 'nationality', 'religion', 'blood_type', 'address',
        'session_id', 'class_id',
        'guardian_name', 'guardian_phone', 'guardian_email', 'guardian_relation',
        'previous_school', 'previous_class',
        'status', 'reviewer_notes', 'reviewed_by', 'reviewed_at', 'student_id',
    ];

    protected $casts = [
        'birthday'    => 'date',
        'reviewed_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING      = 'pending';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_APPROVED     = 'approved';
    const STATUS_REJECTED     = 'rejected';
    const STATUS_ENROLLED     = 'enrolled';

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function session()
    {
        return $this->belongsTo(SchoolSession::class, 'session_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function getApplicantNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending'      => 'bg-amber-100 text-amber-700',
            'under_review' => 'bg-blue-100 text-blue-700',
            'approved'     => 'bg-emerald-100 text-emerald-700',
            'rejected'     => 'bg-rose-100 text-rose-700',
            'enrolled'     => 'bg-indigo-100 text-indigo-700',
            default        => 'bg-slate-100 text-slate-600',
        };
    }

    /** Generate a unique application number */
    public static function generateNumber(): string
    {
        return 'APP-' . strtoupper(uniqid());
    }
}
