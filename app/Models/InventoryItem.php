<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name','sku','category_id','supplier_id','unit',
        'quantity_in_stock','reorder_level','unit_price',
        'location','description','is_active',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'is_active'  => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function transactions()
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity_in_stock <= $this->reorder_level;
    }

    public function getStockBadgeAttribute(): string
    {
        if ($this->quantity_in_stock <= 0) {
            return '<span class="badge bg-danger">Out of Stock</span>';
        }
        if ($this->is_low_stock) {
            return '<span class="badge bg-warning text-dark">Low (' . $this->quantity_in_stock . ')</span>';
        }
        return '<span class="badge bg-success">' . $this->quantity_in_stock . ' ' . $this->unit . '</span>';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity_in_stock', '<=', 'reorder_level');
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('sku', 'like', "%{$term}%");
        });
    }
}
