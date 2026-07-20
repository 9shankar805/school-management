<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FeeStructureItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_structure_id', 'fee_category_id', 'amount', 'is_mandatory', 'notes',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'is_mandatory' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────
    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function feeCategory()
    {
        return $this->belongsTo(FeeCategory::class);
    }

    // ── Auto-sync parent total when item changes ──────────────────────────────
    protected static function booted(): void
    {
        $sync = fn (self $item) => optional($item->feeStructure)->syncTotal();

        static::saved($sync);
        static::deleted($sync);
    }
}
