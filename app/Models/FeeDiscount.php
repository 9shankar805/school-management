<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class FeeDiscount extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = ['school_id', 'name', 'type', 'value', 'description', 'is_active'];

    protected $casts = [
        'value'     => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function studentDiscounts()
    {
        return $this->hasMany(StudentFeeDiscount::class, 'discount_id');
    }

    public function getDisplayValueAttribute(): string
    {
        return $this->type === 'percentage'
            ? $this->value . '%'
            : '$' . number_format($this->value, 2);
    }

    /** Calculate discount amount on a given fee amount */
    public function calculate(float $amount): float
    {
        if ($this->type === 'percentage') {
            return round($amount * $this->value / 100, 2);
        }
        return min((float) $this->value, $amount);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
