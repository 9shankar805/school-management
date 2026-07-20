<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'item_code', 'name', 'category', 'unit', 'quantity_in_stock',
        'reorder_level', 'unit_price', 'description',
        'warehouse_id', 'supplier_id', 'status', 'created_by',
    ];

    protected $casts = [
        'unit_price'       => 'decimal:2',
        'quantity_in_stock' => 'integer',
        'reorder_level'    => 'integer',
    ];

    const CATEGORIES = [
        'stationery' => 'Stationery & Office',
        'cleaning'   => 'Cleaning Supplies',
        'lab'        => 'Lab Consumables',
        'sports'     => 'Sports Supplies',
        'medical'    => 'Medical Supplies',
        'it'         => 'IT Consumables',
        'kitchen'    => 'Kitchen Supplies',
        'printing'   => 'Printing & Ink',
        'other'      => 'Other',
    ];

    const UNITS = [
        'pcs'   => 'Pieces',
        'box'   => 'Box',
        'ream'  => 'Ream',
        'kg'    => 'Kilogram',
        'litre' => 'Litre',
        'roll'  => 'Roll',
        'pack'  => 'Pack',
        'set'   => 'Set',
        'pair'  => 'Pair',
        'unit'  => 'Unit',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    public function getUnitLabelAttribute(): string
    {
        return self::UNITS[$this->unit] ?? ucfirst($this->unit);
    }

    /** True when current stock is at or below the reorder level. */
    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity_in_stock <= $this->reorder_level;
    }

    public function getStockBadgeAttribute(): string
    {
        if ($this->quantity_in_stock === 0) {
            return 'bg-danger';
        }
        if ($this->is_low_stock) {
            return 'bg-warning text-dark';
        }
        return 'bg-success';
    }

    public function getStockLabelAttribute(): string
    {
        if ($this->quantity_in_stock === 0) return 'Out of Stock';
        if ($this->is_low_stock)           return 'Low Stock';
        return 'In Stock';
    }

    /** Generate the next sequential item code. */
    public static function nextCode(): string
    {
        $count = static::withTrashed()->count() + 1;
        return 'ITM-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
