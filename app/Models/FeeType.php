<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Master lookup of all fee types a school can charge.
 *
 * FeeType  →  FeeStructure (amounts per class)
 *         →  InvoiceItem (line items on invoices)
 *
 * Trigger values:
 *   manual       — only via bulk invoice generation
 *   admission    — auto-generated when student is enrolled
 *   monthly      — auto-generated on 1st of each month
 *   exam_publish — auto-generated when an exam is published
 *   annual       — auto-generated once per academic year
 */
class FeeType extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    /** Nepal's 11 legally recognised fee heads (IRD / local govt audits) */
    public const LEGAL_HEADS = [
        'Tuition Fee',
        'Annual Fee',
        'Admission Fee',
        'Refundable Deposit',
        'Exam Fee',
        'Computer Fee',
        'Special Training Fee',
        'Hostel Fee',
        'Meal Fee',
        'Transport Fee',
        'Educational Tour Fee',
    ];

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'category_id',
        'description',
        'frequency',
        'trigger',
        'is_taxable',
        'tax_rate',
        'is_active',
    ];

    protected $casts = [
        'is_taxable' => 'boolean',
        'is_active'  => 'boolean',
        'tax_rate'   => 'decimal:2',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function category()
    {
        return $this->belongsTo(FeeCategory::class, 'category_id');
    }

    public function feeStructures()
    {
        return $this->hasMany(FeeStructure::class, 'fee_type_id');
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class, 'fee_type_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByTrigger($query, string $trigger)
    {
        return $query->where('trigger', $trigger);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Calculate the tax amount for a given base amount.
     */
    public function calculateTax(float $amount): float
    {
        if (! $this->is_taxable || $this->tax_rate <= 0) {
            return 0.0;
        }
        return round($amount * ($this->tax_rate / 100), 2);
    }

    public function getFrequencyLabelAttribute(): string
    {
        return match ($this->frequency) {
            'once'        => 'One-time',
            'monthly'     => 'Monthly',
            'quarterly'   => 'Quarterly',
            'half_yearly' => 'Half-Yearly',
            'yearly'      => 'Yearly',
            default       => ucfirst($this->frequency),
        };
    }

    public function getTriggerLabelAttribute(): string
    {
        return match ($this->trigger) {
            'manual'       => 'Manual',
            'admission'    => 'On Admission',
            'monthly'      => 'Monthly (Auto)',
            'exam_publish' => 'On Exam Publish',
            'annual'       => 'Annual (Auto)',
            default        => ucfirst($this->trigger),
        };
    }
}
