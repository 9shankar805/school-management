<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_code', 'name', 'category', 'description', 'brand', 'model',
        'serial_number', 'purchase_price', 'purchase_date', 'warranty_expiry',
        'current_value', 'condition', 'status', 'location', 'assigned_to',
        'warehouse_id', 'supplier_id', 'image_path', 'created_by',
    ];

    protected $casts = [
        'purchase_price'  => 'decimal:2',
        'current_value'   => 'decimal:2',
        'purchase_date'   => 'date',
        'warranty_expiry' => 'date',
    ];

    const CATEGORIES = [
        'furniture'   => 'Furniture',
        'electronics' => 'Electronics',
        'vehicle'     => 'Vehicle',
        'lab'         => 'Lab Equipment',
        'sports'      => 'Sports Equipment',
        'it'          => 'IT Equipment',
        'library'     => 'Library Equipment',
        'kitchen'     => 'Kitchen Equipment',
        'medical'     => 'Medical Equipment',
        'other'       => 'Other',
    ];

    const CONDITIONS = [
        'new'      => 'New',
        'good'     => 'Good',
        'fair'     => 'Fair',
        'poor'     => 'Poor',
        'damaged'  => 'Damaged',
        'disposed' => 'Disposed',
    ];

    const STATUSES = [
        'available'         => 'Available',
        'in_use'            => 'In Use',
        'under_maintenance' => 'Under Maintenance',
        'disposed'          => 'Disposed',
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

    public function maintenanceLogs()
    {
        return $this->hasMany(AssetMaintenanceLog::class)->latest('maintenance_date');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    public function getConditionLabelAttribute(): string
    {
        return self::CONDITIONS[$this->condition] ?? ucfirst($this->condition);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'available'         => 'bg-success',
            'in_use'            => 'bg-primary',
            'under_maintenance' => 'bg-warning text-dark',
            'disposed'          => 'bg-secondary',
            default             => 'bg-light text-dark',
        };
    }

    public function getConditionBadgeAttribute(): string
    {
        return match ($this->condition) {
            'new'      => 'bg-success',
            'good'     => 'bg-primary',
            'fair'     => 'bg-info text-dark',
            'poor'     => 'bg-warning text-dark',
            'damaged'  => 'bg-danger',
            'disposed' => 'bg-secondary',
            default    => 'bg-light text-dark',
        };
    }

    /** Generate the next sequential asset code. */
    public static function nextCode(): string
    {
        $year  = now()->year;
        $count = static::withTrashed()->whereYear('created_at', $year)->count() + 1;
        return 'AST-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
