<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Master list of salary component heads.
 *
 * System components (is_system = true):
 *   BASIC  — Basic Salary (earning)
 *   TDS    — Tax Deducted at Source (deduction)
 *   PF     — Provident Fund (deduction)
 *
 * Custom components added by school admin:
 *   HRA    — House Rent Allowance (earning)
 *   MED    — Medical Allowance (earning)
 *   TRP    — Transport Allowance (earning)
 *   CIT    — Citizen Investment Trust (deduction)
 *   SSF    — Social Security Fund (deduction)
 *   LOAN   — Loan Deduction (deduction)
 *   ADV    — Advance Recovery (deduction)
 */
class SalaryComponent extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $table = 'salary_components';

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'type',
        'calculation',
        'default_value',
        'formula',
        'is_taxable',
        'is_active',
        'is_system',
        'sort_order',
    ];

    protected $casts = [
        'is_taxable'    => 'boolean',
        'is_active'     => 'boolean',
        'is_system'     => 'boolean',
        'default_value' => 'decimal:2',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function staffStructures()
    {
        return $this->hasMany(StaffSalaryStructure::class, 'component_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeEarnings($query)
    {
        return $query->where('type', 'earning');
    }

    public function scopeDeductions($query)
    {
        return $query->where('type', 'deduction');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Compute this component's value for a given basic salary and present days.
     *
     * @param float $basicSalary
     * @param int   $presentDays
     * @param int   $workingDays
     * @param float $overrideAmount  — value from staff_salary_structures if set
     */
    public function compute(
        float $basicSalary,
        int   $presentDays  = 0,
        int   $workingDays  = 30,
        float $overrideAmount = 0
    ): float {
        $base = $overrideAmount > 0 ? $overrideAmount : $this->default_value;

        return match ($this->calculation) {
            'fixed'      => (float) $base,
            'percentage' => round($basicSalary * ($base / 100), 2),
            'attendance' => $workingDays > 0
                ? round(($basicSalary / $workingDays) * max(0, $workingDays - $presentDays), 2)
                : 0.0,
            'formula'    => $this->evaluateFormula($basicSalary, $presentDays, $workingDays),
            default      => 0.0,
        };
    }

    private function evaluateFormula(float $basic, int $present, int $working): float
    {
        // Safe formula eval: replace known tokens, then eval in sandboxed context
        // Tokens: basic, present_days, working_days
        $expr = str_replace(
            ['basic', 'present_days', 'working_days'],
            [(string) $basic, (string) $present, (string) $working],
            $this->formula ?? '0'
        );
        // Only allow safe arithmetic
        if (! preg_match('/^[\d\s\.\+\-\*\/\(\)]+$/', $expr)) {
            return 0.0;
        }
        try {
            return (float) eval("return ({$expr});");
        } catch (\Throwable) {
            return 0.0;
        }
    }
}
