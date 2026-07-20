<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FeeDiscount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'type', 'value', 'fee_category_id', 'student_id',
        'fee_structure_id', 'valid_from', 'valid_until', 'is_active', 'reason', 'created_by',
    ];

    protected $casts = [
        'value'       => 'decimal:2',
        'valid_from'  => 'date',
        'valid_until' => 'date',
        'is_active'   => 'boolean',
    ];

    const TYPES = [
        'percentage' => 'Percentage (%)',
        'fixed'      => 'Fixed Amount',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────
    public function feeCategory()
    {
        return $this->belongsTo(FeeCategory::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    /** Compute the actual discount amount given a base amount. */
    public function computeDiscount(float $baseAmount): float
    {
        if ($this->type === 'percentage') {
            return round($baseAmount * $this->value / 100, 2);
        }

        return min((float) $this->value, $baseAmount);
    }

    public function getStatusBadgeAttribute(): string
    {
        if (! $this->is_active) {
            return 'bg-slate-100 text-slate-500';
        }
        if ($this->valid_until && $this->valid_until->isPast()) {
            return 'bg-rose-100 text-rose-700';
        }
        return 'bg-emerald-100 text-emerald-700';
    }
}
