<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobPosting extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'title', 'department', 'description', 'requirements',
        'vacancies', 'salary_min', 'salary_max',
        'application_deadline', 'status', 'posted_by',
    ];

    protected $casts = [
        'salary_min'           => 'decimal:2',
        'salary_max'           => 'decimal:2',
        'application_deadline' => 'date',
    ];

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'published' => '<span class="badge bg-success">Published</span>',
            'draft'     => '<span class="badge bg-secondary">Draft</span>',
            'closed'    => '<span class="badge bg-danger">Closed</span>',
            default     => '<span class="badge bg-light text-dark">' . ucfirst($this->status) . '</span>',
        };
    }

    public function getIsOpenAttribute(): bool
    {
        return $this->status === 'published'
            && ($this->application_deadline === null || $this->application_deadline->isFuture());
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
