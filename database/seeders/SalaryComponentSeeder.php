<?php

namespace Database\Seeders;

use App\Models\SalaryComponent;
use Illuminate\Database\Seeder;

/**
 * Seeds the default Nepal-standard salary components.
 * Idempotent — uses firstOrCreate so safe to run multiple times.
 *
 * Run: php artisan db:seed --class=SalaryComponentSeeder
 */
class SalaryComponentSeeder extends Seeder
{
    public function run(): void
    {
        $components = [
            // ── Earnings ─────────────────────────────────────────────────────
            [
                'name'          => 'Basic Salary',
                'code'          => 'BASIC',
                'type'          => 'earning',
                'calculation'   => 'fixed',
                'default_value' => 0,
                'is_taxable'    => true,
                'is_active'     => true,
                'is_system'     => true,
                'sort_order'    => 1,
            ],
            [
                'name'          => 'House Rent Allowance',
                'code'          => 'HRA',
                'type'          => 'earning',
                'calculation'   => 'fixed',
                'default_value' => 0,
                'is_taxable'    => true,
                'is_active'     => true,
                'is_system'     => false,
                'sort_order'    => 2,
            ],
            [
                'name'          => 'Medical Allowance',
                'code'          => 'MED',
                'type'          => 'earning',
                'calculation'   => 'fixed',
                'default_value' => 0,
                'is_taxable'    => false,   // Medical allowance typically exempt
                'is_active'     => true,
                'is_system'     => false,
                'sort_order'    => 3,
            ],
            [
                'name'          => 'Transport Allowance',
                'code'          => 'TRP',
                'type'          => 'earning',
                'calculation'   => 'fixed',
                'default_value' => 0,
                'is_taxable'    => false,
                'is_active'     => true,
                'is_system'     => false,
                'sort_order'    => 4,
            ],
            [
                'name'          => 'Dearness Allowance',
                'code'          => 'DA',
                'type'          => 'earning',
                'calculation'   => 'percentage',
                'default_value' => 0,
                'is_taxable'    => true,
                'is_active'     => false,   // Enable if school uses it
                'is_system'     => false,
                'sort_order'    => 5,
            ],
            [
                'name'          => 'Overtime',
                'code'          => 'OT',
                'type'          => 'earning',
                'calculation'   => 'fixed',
                'default_value' => 0,
                'is_taxable'    => true,
                'is_active'     => true,
                'is_system'     => false,
                'sort_order'    => 6,
            ],
            [
                'name'          => 'Bonus',
                'code'          => 'BONUS',
                'type'          => 'earning',
                'calculation'   => 'fixed',
                'default_value' => 0,
                'is_taxable'    => true,
                'is_active'     => true,
                'is_system'     => false,
                'sort_order'    => 7,
            ],

            // ── Deductions ────────────────────────────────────────────────────
            [
                'name'          => 'Income Tax (TDS)',
                'code'          => 'TDS',
                'type'          => 'deduction',
                'calculation'   => 'formula',   // computed by PayrollService::computeProgressiveTax()
                'default_value' => 0,
                'formula'       => '0',         // PayrollService overrides this
                'is_taxable'    => false,
                'is_active'     => true,
                'is_system'     => true,
                'sort_order'    => 10,
            ],
            [
                'name'          => 'Provident Fund (PF)',
                'code'          => 'PF',
                'type'          => 'deduction',
                'calculation'   => 'percentage',
                'default_value' => 10,          // 10% of basic salary
                'is_taxable'    => false,
                'is_active'     => true,
                'is_system'     => true,
                'sort_order'    => 11,
            ],
            [
                'name'          => 'Citizen Investment Trust (CIT)',
                'code'          => 'CIT',
                'type'          => 'deduction',
                'calculation'   => 'percentage',
                'default_value' => 5,           // 5% of basic (configurable)
                'is_taxable'    => false,
                'is_active'     => false,        // Enable if school participates in CIT
                'is_system'     => false,
                'sort_order'    => 12,
            ],
            [
                'name'          => 'Social Security Fund (SSF)',
                'code'          => 'SSF',
                'type'          => 'deduction',
                'calculation'   => 'percentage',
                'default_value' => 11,           // Employee contribution 11% of basic
                'is_taxable'    => false,
                'is_active'     => false,         // Enable when SSF applicable
                'is_system'     => false,
                'sort_order'    => 13,
            ],
            [
                'name'          => 'Late Fine',
                'code'          => 'LATE',
                'type'          => 'deduction',
                'calculation'   => 'attendance',  // per late day × amount
                'default_value' => 100,           // Rs. 100 per late day (configurable)
                'is_taxable'    => false,
                'is_active'     => true,
                'is_system'     => false,
                'sort_order'    => 14,
            ],
            [
                'name'          => 'Loan Deduction',
                'code'          => 'LOAN',
                'type'          => 'deduction',
                'calculation'   => 'fixed',
                'default_value' => 0,
                'is_taxable'    => false,
                'is_active'     => true,
                'is_system'     => false,
                'sort_order'    => 15,
            ],
            [
                'name'          => 'Salary Advance Recovery',
                'code'          => 'ADV',
                'type'          => 'deduction',
                'calculation'   => 'fixed',
                'default_value' => 0,
                'is_taxable'    => false,
                'is_active'     => true,
                'is_system'     => false,
                'sort_order'    => 16,
            ],
            [
                'name'          => 'Insurance Premium',
                'code'          => 'INS',
                'type'          => 'deduction',
                'calculation'   => 'fixed',
                'default_value' => 0,
                'is_taxable'    => false,
                'is_active'     => false,
                'is_system'     => false,
                'sort_order'    => 17,
            ],
        ];

        foreach ($components as $data) {
            SalaryComponent::firstOrCreate(
                ['code' => $data['code']],
                $data
            );
        }

        $this->command->info('✓ Salary components seeded (' . count($components) . ' components).');
    }
}
