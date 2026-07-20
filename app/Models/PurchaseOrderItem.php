<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id', 'item_name', 'item_type',
        'inventory_item_id', 'quantity', 'unit_price', 'notes',
    ];

    protected $casts = [
        'unit_price'  => 'decimal:2',
        'total_price' => 'decimal:2',
        'quantity'    => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Computed total even before stored-as column resolves. */
    public function getLineTotalAttribute(): float
    {
        return (float) ($this->quantity * $this->unit_price);
    }
}
