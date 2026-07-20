<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'contact_person', 'email', 'phone', 'address',
        'tax_number', 'bank_account', 'status', 'notes', 'created_by',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

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

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getStatusBadgeAttribute(): string
    {
        return $this->status === 'active'
            ? 'bg-success'
            : 'bg-secondary';
    }
}
