<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffContract extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'user_id', 'contract_type', 'start_date', 'end_date',
        'salary', 'allowances', 'position', 'department',
        'status', 'document_path', 'notes',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'salary'      => 'decimal:2',
        'allowances'  => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getGrossSalaryAttribute(): float
    {
        return (float) $this->salary + (float) $this->allowances;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->end_date
            && $this->end_date->isFuture()
            && $this->end_date->diffInDays(now()) <= 30;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active'     => '<span class="badge bg-success">Active</span>',
            'expired'    => '<span class="badge bg-danger">Expired</span>',
            'terminated' => '<span class="badge bg-secondary">Terminated</span>',
            default      => '<span class="badge bg-light text-dark">' . ucfirst($this->status) . '</span>',
        };
    }
}
