<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'category', 'amount', 'expense_date', 'payment_method',
        'reference_no', 'vendor', 'description', 'receipt_path',
        'status', 'created_by', 'approved_by',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'expense_date' => 'date',
    ];

    const CATEGORIES = [
        'salaries'    => 'Salaries & Wages',
        'utilities'   => 'Utilities',
        'maintenance' => 'Maintenance & Repairs',
        'supplies'    => 'Office Supplies',
        'transport'   => 'Transport & Fuel',
        'marketing'   => 'Marketing & Events',
        'it'          => 'IT & Technology',
        'other'       => 'Other',
    ];

    const PAYMENT_METHODS = [
        'cash'          => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'cheque'        => 'Cheque',
        'card'          => 'Card',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'bg-emerald-100 text-emerald-700',
            'rejected' => 'bg-rose-100 text-rose-700',
            default    => 'bg-amber-100 text-amber-700',
        };
    }
}
