<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'code', 'location', 'manager_name', 'phone',
        'type', 'status', 'description', 'created_by',
    ];

    const TYPES = [
        'main'      => 'Main Store',
        'branch'    => 'Branch Store',
        'classroom' => 'Classroom',
        'lab'       => 'Laboratory',
        'other'     => 'Other',
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

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function getStatusBadgeAttribute(): string
    {
        return $this->status === 'active' ? 'bg-success' : 'bg-secondary';
    }
}
