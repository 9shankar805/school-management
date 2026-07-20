<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name','asset_tag','category_id','supplier_id','brand',
        'model_number','serial_number','purchase_date','purchase_price',
        'warranty_expiry','location','assigned_to','condition',
        'status','description',
    ];

    protected $casts = [
        'purchase_date'   => 'date',
        'warranty_expiry' => 'date',
        'purchase_price'  => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'available'   => '<span class="badge bg-success">Available</span>',
            'assigned'    => '<span class="badge bg-primary">Assigned</span>',
            'maintenance' => '<span class="badge bg-warning text-dark">Maintenance</span>',
            'disposed'    => '<span class="badge bg-danger">Disposed</span>',
            default       => '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>',
        };
    }

    public function getConditionBadgeAttribute(): string
    {
        return match ($this->condition) {
            'new'      => '<span class="badge bg-success">New</span>',
            'good'     => '<span class="badge bg-info text-dark">Good</span>',
            'fair'     => '<span class="badge bg-warning text-dark">Fair</span>',
            'poor'     => '<span class="badge bg-danger">Poor</span>',
            'disposed' => '<span class="badge bg-secondary">Disposed</span>',
            default    => '<span class="badge bg-light text-dark">' . ucfirst($this->condition) . '</span>',
        };
    }

    public function getIsWarrantyExpiredAttribute(): bool
    {
        return $this->warranty_expiry && $this->warranty_expiry->isPast();
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('asset_tag', 'like', "%{$term}%")
              ->orWhere('serial_number', 'like', "%{$term}%")
              ->orWhere('brand', 'like', "%{$term}%");
        });
    }
}
