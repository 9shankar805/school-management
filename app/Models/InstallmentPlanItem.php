<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InstallmentPlanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'installment_plan_id', 'installment_no', 'amount',
        'due_date', 'status', 'late_fee_charged', 'paid_date', 'payment_id',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'late_fee_charged' => 'decimal:2',
        'due_date'         => 'date',
        'paid_date'        => 'date',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────
    public function installmentPlan()
    {
        return $this->belongsTo(InstallmentPlan::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'paid'    => 'bg-emerald-100 text-emerald-700',
            'overdue' => 'bg-rose-100 text-rose-700',
            'waived'  => 'bg-slate-100 text-slate-500',
            default   => 'bg-amber-100 text-amber-700',
        };
    }

    /** Mark overdue if past due_date and still pending. */
    public function checkOverdue(): void
    {
        if ($this->status === 'pending' && $this->due_date->isPast()) {
            $this->update(['status' => 'overdue']);
        }
    }
}
