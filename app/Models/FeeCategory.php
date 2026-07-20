<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class FeeCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'description', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Default categories seeded on first use ────────────────────────────────
    const DEFAULTS = [
        'Tuition',
        'Transport',
        'Hostel',
        'Library',
        'Laboratory',
        'Examination',
        'Sports',
        'Uniform',
        'Canteen',
        'Activity',
        'Miscellaneous',
    ];

    // ── Auto-generate slug ────────────────────────────────────────────────────
    protected static function booted(): void
    {
        static::creating(function (self $cat) {
            if (empty($cat->slug)) {
                $cat->slug = Str::slug($cat->name);
            }
        });
    }

    // ── Relationships ─────────────────────────────────────────────────────────
    public function feeStructureItems()
    {
        return $this->hasMany(FeeStructureItem::class);
    }

    public function discounts()
    {
        return $this->hasMany(FeeDiscount::class);
    }
}
