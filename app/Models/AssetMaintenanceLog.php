<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssetMaintenanceLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_id', 'type', 'maintenance_date', 'next_due_date',
        'cost', 'vendor', 'status', 'description', 'findings',
        'performed_by', 'created_by',
    ];

    protected $casts = [
        'cost'             => 'decimal:2',
        'maintenance_date' => 'date',
        'next_due_date'    => 'date',
    ];

    const TYPES = [
        'preventive' => 'Preventive',
        'corrective' => 'Corrective',
        'inspection' => 'Inspection',
        'upgrade'    => 'Upgrade',
        'disposal'   => 'Disposal',
    ];

    const STATUSES = [
        'scheduled'   => 'Scheduled',
        'in_progress' => 'In Progress',
        'completed'   => 'Completed',
        'cancelled'   => 'Cancelled',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'scheduled'   => 'bg-info text-dark',
            'in_progress' => 'bg-warning text-dark',
            'completed'   => 'bg-success',
            'cancelled'   => 'bg-secondary',
            default       => 'bg-light text-dark',
        };
    }
}
