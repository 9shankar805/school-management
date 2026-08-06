<?php

namespace App\Observers;

use App\Models\LedgerEntry;
use App\Models\SalarySlip;
use App\Models\TeacherPayroll;

/**
 * SalaryObserver
 *
 * When a salary slip or teacher payroll is marked as PAID,
 * auto-post the double-entry journal entries:
 *
 *   Dr: Salary Expense         (gross salary)
 *   Cr: TDS Payable            (tax withheld)
 *   Cr: Provident Fund Payable (PF withheld)
 *   Cr: CIT Payable            (if applicable)
 *   Cr: SSF Payable            (if applicable)
 *   Cr: Bank / Cash            (net salary paid out)
 *
 * When school later deposits TDS to government:
 *   Dr: TDS Payable  /  Cr: Bank   (handled manually via expense entry)
 */
class SalaryObserver
{
    public function updated(SalarySlip|TeacherPayroll $model): void
    {
        // Only fire when status just changed to 'paid'
        if (! $model->wasChanged('status') || $model->status !== 'paid') {
            return;
        }

        $payDate  = $model->payment_date?->toDateString() ?? today()->toDateString();
        $staffId  = $model instanceof SalarySlip ? $model->staff_id : $model->teacher_id;
        $type     = $model instanceof SalarySlip ? 'staff_salary' : 'teacher_salary';
        $refDesc  = ($model instanceof SalarySlip ? 'Staff Salary Slip' : 'Teacher Payroll')
                    . " #{$model->id} — " . $model->month_name . ' ' . $model->year;

        $gross      = (float) $model->gross_salary;
        $net        = (float) $model->net_salary;
        $tds        = (float) ($model->tds_payable ?? $model->tax_deduction ?? 0);
        $pf         = (float) ($model->provident_fund ?? 0);
        $cit        = (float) ($model->cit_deduction ?? 0);
        $ssf        = (float) ($model->ssf_deduction ?? 0);

        $refType = get_class($model);
        $refId   = $model->id;
        $createdBy = auth()->id();

        // Dr: Salary Expense (gross)
        LedgerEntry::create([
            'entry_date'     => $payDate,
            'type'           => $type,
            'account'        => 'Staff Salaries',
            'debit'          => $gross,
            'credit'         => 0,
            'balance'        => 0,
            'reference_type' => $refType,
            'reference_id'   => $refId,
            'description'    => $refDesc,
            'created_by'     => $createdBy,
        ]);

        // Cr: TDS Payable
        if ($tds > 0) {
            LedgerEntry::create([
                'entry_date'     => $payDate,
                'type'           => $type,
                'account'        => 'TDS Payable',
                'debit'          => 0,
                'credit'         => $tds,
                'balance'        => 0,
                'reference_type' => $refType,
                'reference_id'   => $refId,
                'description'    => "TDS withheld — {$refDesc}",
                'created_by'     => $createdBy,
            ]);
        }

        // Cr: Provident Fund Payable
        if ($pf > 0) {
            LedgerEntry::create([
                'entry_date'     => $payDate,
                'type'           => $type,
                'account'        => 'Provident Fund Payable',
                'debit'          => 0,
                'credit'         => $pf,
                'balance'        => 0,
                'reference_type' => $refType,
                'reference_id'   => $refId,
                'description'    => "PF withheld — {$refDesc}",
                'created_by'     => $createdBy,
            ]);
        }

        // Cr: CIT Payable
        if ($cit > 0) {
            LedgerEntry::create([
                'entry_date'     => $payDate,
                'type'           => $type,
                'account'        => 'CIT Payable',
                'debit'          => 0,
                'credit'         => $cit,
                'balance'        => 0,
                'reference_type' => $refType,
                'reference_id'   => $refId,
                'description'    => "CIT withheld — {$refDesc}",
                'created_by'     => $createdBy,
            ]);
        }

        // Cr: SSF Payable
        if ($ssf > 0) {
            LedgerEntry::create([
                'entry_date'     => $payDate,
                'type'           => $type,
                'account'        => 'SSF Payable',
                'debit'          => 0,
                'credit'         => $ssf,
                'balance'        => 0,
                'reference_type' => $refType,
                'reference_id'   => $refId,
                'description'    => "SSF withheld — {$refDesc}",
                'created_by'     => $createdBy,
            ]);
        }

        // Cr: Bank / Cash (net paid out)
        if ($net > 0) {
            LedgerEntry::create([
                'entry_date'     => $payDate,
                'type'           => $type,
                'account'        => 'Bank Account',
                'debit'          => 0,
                'credit'         => $net,
                'balance'        => 0,
                'reference_type' => $refType,
                'reference_id'   => $refId,
                'description'    => "Net salary paid — {$refDesc}",
                'created_by'     => $createdBy,
            ]);
        }
    }
}
