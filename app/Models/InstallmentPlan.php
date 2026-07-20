<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InstallmentPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'fee_structure_id', 'student_id', 'invoice_id',
        'num_installments', 'total_amount', 'late_fee', 'is_active',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'late_fee'     => 'decimal:2',
        'is_active'    => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────
    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function items()
    {
        return $this->hasMany(InstallmentPlanItem::class)->orderBy('installment_no');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    public function getPaidAmountAttribute(): float
    {
        return (float) $this->items->where('status', 'paid')->sum('amount');
    }

    public function getPendingAmountAttribute(): float
    {
        return (float) $this->items->whereIn('status', ['pending', 'overdue'])->sum('amount');
    }

    public function getProgressPercentAttribute(): int
    {
        if (! $this->total_amount) {
            return 0;
        }
        return (int) min(100, round($this->paid_amount / $this->total_amount * 100));
    }
}
