<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'po_number', 'supplier_id', 'order_date', 'expected_delivery',
        'delivered_date', 'total_amount', 'status', 'payment_method',
        'reference_no', 'notes', 'created_by', 'approved_by',
    ];

    protected $casts = [
        'total_amount'      => 'decimal:2',
        'order_date'        => 'date',
        'expected_delivery' => 'date',
        'delivered_date'    => 'date',
    ];

    const STATUSES = [
        'draft'     => 'Draft',
        'submitted' => 'Submitted',
        'approved'  => 'Approved',
        'received'  => 'Received',
        'cancelled' => 'Cancelled',
    ];

    const PAYMENT_METHODS = [
        'cash'          => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'cheque'        => 'Cheque',
        'credit'        => 'Credit / Net Terms',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'bg-secondary',
            'submitted' => 'bg-info text-dark',
            'approved'  => 'bg-primary',
            'received'  => 'bg-success',
            'cancelled' => 'bg-danger',
            default     => 'bg-light text-dark',
        };
    }

    /** Recalculate total_amount from line items and persist. */
    public function recalculateTotal(): void
    {
        $this->update([
            'total_amount' => $this->items()->sum('total_price'),
        ]);
    }

    /** Generate the next sequential PO number. */
    public static function nextPoNumber(): string
    {
        $year  = now()->year;
        $count = static::withTrashed()->whereYear('created_at', $year)->count() + 1;
        return 'PO-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
