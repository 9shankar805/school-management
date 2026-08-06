<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A single line item on an invoice.
 *
 * Invoice → InvoiceItems (one per fee type)
 *
 * total = (unit_price * quantity - discount_amount) + tax_amount
 */
class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'fee_type_id',
        'fee_structure_id',
        'description',
        'unit_price',
        'quantity',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'total',
    ];

    protected $casts = [
        'unit_price'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate'        => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'total'           => 'decimal:2',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function feeType()
    {
        return $this->belongsTo(FeeType::class, 'fee_type_id');
    }

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class, 'fee_structure_id');
    }

    // ── Computed helpers ──────────────────────────────────────────────────────

    /**
     * (Re)calculate totals from components and save.
     * Call after changing unit_price, quantity, discount_amount, or tax_rate.
     */
    public function recalculate(): static
    {
        $subtotal = $this->unit_price * $this->quantity;
        $afterDiscount = $subtotal - $this->discount_amount;
        $this->tax_amount = round($afterDiscount * ($this->tax_rate / 100), 2);
        $this->total = round($afterDiscount + $this->tax_amount, 2);
        return $this;
    }
}
