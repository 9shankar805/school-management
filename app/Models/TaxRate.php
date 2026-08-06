<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

/**
 * Tax brackets / rates for payroll deduction.
 */
class TaxRate extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'code',
        'name',          // e.g. "Income Tax Slab A", "VAT 15%"
        'type',          // income_tax | vat | provident_fund | other
        'rate',          // percentage (0-100)
        'min_salary',    // applicable from this gross salary
        'max_salary',    // applicable up to this gross salary (null = unlimited)
        'is_active',
        'description',
    ];

    protected $casts = [
        'rate'       => 'decimal:4',
        'min_salary' => 'decimal:2',
        'max_salary' => 'decimal:2',
        'is_active'  => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeGlobal($query)
    {
        return $query->whereNull('school_id');
    }

    /**
     * Calculate deduction for a given gross salary.
     */
    public function calculateDeduction(float $grossSalary): float
    {
        if ($grossSalary < $this->min_salary) {
            return 0.0;
        }
        if ($this->max_salary && $grossSalary > $this->max_salary) {
            $taxable = $this->max_salary - $this->min_salary;
        } else {
            $taxable = $grossSalary - $this->min_salary;
        }
        return round($taxable * ($this->rate / 100), 2);
    }
}
