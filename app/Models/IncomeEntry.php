<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IncomeEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'category', 'amount', 'income_date', 'payment_method',
        'reference_no', 'source', 'description', 'invoice_id', 'created_by',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'income_date' => 'date',
    ];

    const CATEGORIES = [
        'fees'      => 'Student Fees',
        'donations' => 'Donations',
        'grants'    => 'Grants & Subsidies',
        'events'    => 'Events & Activities',
        'canteen'   => 'Canteen',
        'rentals'   => 'Facility Rentals',
        'other'     => 'Other',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }
}
