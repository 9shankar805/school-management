<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeStructure extends Model
{
    use HasTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name', 'session_id', 'class_id', 'category_id',
        'amount', 'frequency', 'due_date', 'is_active', 'description',
        'fee_type_id', 'is_taxable', 'tax_rate', 'applies_to',
    ];

    protected $casts = [
        'amount'     => 'decimal:2',
        'tax_rate'   => 'decimal:2',
        'due_date'   => 'date',
        'is_active'  => 'boolean',
        'is_taxable' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(FeeCategory::class, 'category_id');
    }
    public function feeType()
    {
        return $this->belongsTo(FeeType::class, 'fee_type_id');
    }

    public function session()
    {
        return $this->belongsTo(SchoolSession::class, 'session_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function getFrequencyLabelAttribute(): string
    {
        return match ($this->frequency) {
            'once'        => 'One-time',
            'monthly'     => 'Monthly',
            'quarterly'   => 'Quarterly',
            'half_yearly' => 'Half-Yearly',
            'yearly'      => 'Yearly',
            default       => ucfirst($this->frequency),
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
