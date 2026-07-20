<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FeeStructure extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'session_id', 'class_id', 'program_id', 'term_id',
        'total_amount', 'is_active', 'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'is_active'    => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────
    public function session()
    {
        return $this->belongsTo(SchoolSession::class, 'session_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function items()
    {
        return $this->hasMany(FeeStructureItem::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function discounts()
    {
        return $this->hasMany(FeeDiscount::class);
    }

    public function installmentPlans()
    {
        return $this->hasMany(InstallmentPlan::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    /** Recalculate total_amount from items and persist. */
    public function syncTotal(): void
    {
        $this->update(['total_amount' => $this->items()->sum('amount')]);
    }
}
