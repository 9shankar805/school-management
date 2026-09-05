<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Finance hardening — Nepal compliance:
 *
 * 1. Seed the Education Equity Fee (शिक्षा समता शुल्क) 3% TaxRate entry
 *    required by MoF directive FY 2083/84.
 *
 * 2. Add `is_closed` + `closed_at` + `closed_by` columns to `ledger_entries`
 *    so LedgerController::sync() can refuse to overwrite a closed period.
 *
 * 3. Seed Nepal mandatory payroll TaxRate rows (EPF 10%, SSF 20% school,
 *    SSF 11% employee) if they don't exist, so PayrollService has DB-driven
 *    rates to fall back to.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Period-close columns on ledger_entries ─────────────────────────
        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->boolean('is_closed')->default(false)->after('balance');
            $table->timestamp('closed_at')->nullable()->after('is_closed');
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete()->after('closed_at');
        });

        // ── 2. Seed Education Equity Fee 3% ───────────────────────────────────
        // Global (school_id = NULL) so it applies to all tenants.
        DB::table('tax_rates')->insertOrIgnore([
            'code'        => 'EEF',
            'name'        => 'Education Equity Fee (शिक्षा समता शुल्क) — 3%',
            'type'        => 'other',
            'rate'        => 3.0000,
            'min_salary'  => 0,
            'max_salary'  => null,
            'is_active'   => true,
            'description' => 'MoF directive FY 2083/84: 3% Education Equity Fee (शिक्षा समता शुल्क) on tuition fee. Auto-applied as invoice line item.',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // ── 3. Seed Nepal mandatory payroll deduction rates ───────────────────
        // Employees Provident Fund — employee contribution 10%
        DB::table('tax_rates')->insertOrIgnore([
            'code'        => 'EPF_EMP',
            'name'        => 'Employees Provident Fund — Employee 10% (कर्मचारी सञ्चयकोष)',
            'type'        => 'provident_fund',
            'rate'        => 10.0000,
            'min_salary'  => 0,
            'max_salary'  => null,
            'is_active'   => true,
            'description' => 'Employee contribution to EPF — 10% of basic salary. Mandatory under EPF Act 2019.',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Employees Provident Fund — employer contribution 10%
        DB::table('tax_rates')->insertOrIgnore([
            'code'        => 'EPF_EMP_SCHOOL',
            'name'        => 'Employees Provident Fund — Employer 10% (विद्यालय अंश)',
            'type'        => 'provident_fund',
            'rate'        => 10.0000,
            'min_salary'  => 0,
            'max_salary'  => null,
            'is_active'   => true,
            'description' => "School's matching 10% EPF contribution. Mandatory.",
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Social Security Fund — employee contribution 11%
        DB::table('tax_rates')->insertOrIgnore([
            'code'        => 'SSF_EMP',
            'name'        => 'Social Security Fund — Employee 11% (सामाजिक सुरक्षा कोष)',
            'type'        => 'provident_fund',
            'rate'        => 11.0000,
            'min_salary'  => 0,
            'max_salary'  => null,
            'is_active'   => true,
            'description' => 'Employee SSF contribution 11% of basic salary. SSF Act 2074.',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Social Security Fund — employer contribution 20%
        DB::table('tax_rates')->insertOrIgnore([
            'code'        => 'SSF_SCHOOL',
            'name'        => 'Social Security Fund — Employer 20% (विद्यालय अंश)',
            'type'        => 'provident_fund',
            'rate'        => 20.0000,
            'min_salary'  => 0,
            'max_salary'  => null,
            'is_active'   => true,
            'description' => "School's SSF contribution 20% of basic salary. SSF Act 2074.",
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->dropForeign(['closed_by']);
            $table->dropColumn(['is_closed', 'closed_at', 'closed_by']);
        });

        DB::table('tax_rates')
            ->whereIn('code', ['EEF', 'EPF_EMP', 'EPF_EMP_SCHOOL', 'SSF_EMP', 'SSF_SCHOOL'])
            ->delete();
    }
};
