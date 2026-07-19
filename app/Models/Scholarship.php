<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Scholarship extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id', 'name', 'type', 'amount', 'percentage',
        'awarded_date', 'expiry_date', 'status', 'criteria', 'notes', 'awarded_by',
    ];

    protected $casts = [
        'awarded_date' => 'date',
        'expiry_date'  => 'date',
        'amount'       => 'decimal:2',
    ];

    const TYPES = [
        'merit'      => 'Merit',
        'need_based' => 'Need-Based',
        'sports'     => 'Sports',
        'arts'       => 'Arts',
        'other'      => 'Other',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function awardedBy()
    {
        return $this->belongsTo(User::class, 'awarded_by');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'active'  => 'bg-emerald-100 text-emerald-700',
            'expired' => 'bg-slate-100 text-slate-500',
            'revoked' => 'bg-rose-100 text-rose-700',
            default   => 'bg-slate-100 text-slate-600',
        };
    }
}
