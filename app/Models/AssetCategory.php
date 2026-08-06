<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = ['school_id', 'name', 'type', 'description'];

    public function assets()
    {
        return $this->hasMany(Asset::class, 'category_id');
    }

    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class, 'category_id');
    }

    public function getTypeBadgeAttribute(): string
    {
        return $this->type === 'asset'
            ? '<span class="badge bg-primary">Asset</span>'
            : '<span class="badge bg-info text-dark">Consumable</span>';
    }
}
