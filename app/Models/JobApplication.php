<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'job_posting_id', 'applicant_name', 'email', 'phone',
        'resume', 'cover_letter', 'interview_date', 'interview_time',
        'interview_location', 'status', 'notes',
    ];

    protected $casts = [
        'interview_date' => 'date',
    ];

    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'applied'      => '<span class="badge bg-secondary">Applied</span>',
            'shortlisted'  => '<span class="badge bg-info text-dark">Shortlisted</span>',
            'interviewed'  => '<span class="badge bg-primary">Interviewed</span>',
            'selected'     => '<span class="badge bg-success">Selected</span>',
            'rejected'     => '<span class="badge bg-danger">Rejected</span>',
            default        => '<span class="badge bg-light text-dark">' . ucfirst($this->status) . '</span>',
        };
    }
}
