<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id', 'purchase_order_id', 'processed_by',
        'type', 'quantity', 'quantity_before', 'quantity_after',
        'unit_price', 'reference', 'notes', 'transacted_at',
    ];

    protected $casts = [
        'unit_price'     => 'decimal:2',
        'transacted_at'  => 'datetime',
    ];

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function processedByUser()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function getTypeBadgeAttribute(): string
    {
        return match ($this->type) {
            'in'         => '<span class="badge bg-success">Stock In</span>',
            'out'        => '<span class="badge bg-danger">Stock Out</span>',
            'adjustment' => '<span class="badge bg-warning text-dark">Adjustment</span>',
            'return'     => '<span class="badge bg-info text-dark">Return</span>',
            default      => '<span class="badge bg-secondary">' . ucfirst($this->type) . '</span>',
        };
    }
}
