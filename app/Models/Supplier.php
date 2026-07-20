<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name','code','contact_person','email','phone',
        'address','website','tax_number','status','notes',
    ];

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        return $this->status === 'active'
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-secondary">Inactive</span>';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
