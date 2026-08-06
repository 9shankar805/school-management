<?php

namespace Database\Seeders;

use App\Models\TaxRate;
use Illuminate\Database\Seeder;

class NepalTaxRateSeeder extends Seeder
{
    public function run()
    {
        TaxRate::updateOrCreate(
            ['code' => 'EEF', 'school_id' => null],
            [
                'name'        => 'Education Equity Fee (शिक्षा समता शुल्क) 3%',
                'type'        => 'equity_fee',
                'rate'        => 3.00,
                'min_salary'  => 0,
                'max_salary'  => null,
                'is_active'   => true,
                'description' => 'MoEST FY 2083/84 mandatory 3% levy on all tuition fee invoices',
            ]
        );

        TaxRate::updateOrCreate(
            ['code' => 'VAT13', 'school_id' => null],
            [
                'name'        => 'VAT 13% (Service Tax)',
                'type'        => 'vat',
                'rate'        => 13.00,
                'min_salary'  => 0,
                'max_salary'  => null,
                'is_active'   => true,
            ]
        );

        TaxRate::updateOrCreate(
            ['code' => 'TDS', 'school_id' => null],
            [
                'name'        => 'TDS Withholding 1.5% on services',
                'type'        => 'tds',
                'rate'        => 1.50,
                'min_salary'  => 0,
                'max_salary'  => null,
                'is_active'   => true,
            ]
        );

        TaxRate::updateOrCreate(
            ['code' => 'EDUCE0', 'school_id' => null],
            [
                'name'        => 'Basic Education (Grades 1-8) No Admission Fee',
                'type'        => 'fee_waiver',
                'rate'        => 100.00,
                'min_salary'  => 0,
                'max_salary'  => null,
                'is_active'   => true,
                'description' => 'Constitution Art. 31 + Free & Compulsory Edu Act 2075 §3 prohibits enrollment fees Grades 1-8',
            ]
        );
    }
}
