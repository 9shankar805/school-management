<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number', 'student_id', 'fee_structure_id', 'session_id',
        'title', 'amount', 'discount_amount', 'tax_amount', 'net_amount',
        'status', 'payment_method', 'reference_no',
        'due_date', 'description', 'created_by',
    ];

    protected $casts = [
        'amount'          => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'net_amount'      => 'decimal:2',
        'due_date'        => 'date',
    ];

    // ── Boot: auto-generate invoice number ────────────────────────────────────
    protected static function booted(): void
    {
        static::creating(function (self $invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = 'INV-' . strtoupper(uniqid());
            }
            // net_amount = amount - discount + tax
            if (! $invoice->net_amount) {
                $invoice->net_amount = $invoice->amount
                    - ($invoice->discount_amount ?? 0)
                    + ($invoice->tax_amount ?? 0);
            }
        });
    }

    // ── Relationships ─────────────────────────────────────────────────────────
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function session()
    {
        return $this->belongsTo(SchoolSession::class, 'session_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function installmentPlans()
    {
        return $this->hasMany(InstallmentPlan::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Computed helpers ──────────────────────────────────────────────────────
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments->sum('amount_paid');
    }

    public function getBalanceDueAttribute(): float
    {
        return max(0, (float) $this->net_amount - $this->total_paid);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'paid' && $this->due_date && $this->due_date->isPast();
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'paid'    => 'bg-emerald-100 text-emerald-700',
            'partial' => 'bg-amber-100 text-amber-700',
            default   => 'bg-rose-100 text-rose-700',
        };
    }
}
