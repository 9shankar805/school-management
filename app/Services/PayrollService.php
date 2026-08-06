<?php

namespace App\Services;

use App\Models\SalaryComponent;
use App\Models\SalarySlip;
use App\Models\StaffAttendance;
use App\Models\StaffSalaryStructure;
use App\Models\TeacherAttendance;
use App\Models\TeacherPayroll;
use App\Models\TaxRate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * PayrollService — compute salary from configurable components.
 *
 * Replaces the manual "enter basic + lump allowances" approach.
 * Reads each staff member's salary structure, applies attendance deductions,
 * applies tax slabs, and returns a ready-to-save data array.
 */
class PayrollService
{
    /**
     * Compute full payroll data for a staff member for a given month/year.
     * Works for both teachers (TeacherAttendance) and non-teaching staff (StaffAttendance).
     *
     * @return array Ready to pass to SalarySlip::updateOrCreate() or TeacherPayroll::updateOrCreate()
     */
    public function computeForStaff(User $staff, int $month, int $year): array
    {
        $workingDays = Carbon::create($year, $month)->daysInMonth;

        // Pull attendance
        $attendance = $this->getAttendance($staff, $month, $year);
        $presentDays = $attendance['present'];
        $absentDays  = $attendance['absent'];
        $leaveDays   = $attendance['leave'];
        $lateDays    = $attendance['late'];

        // Load salary structure (current components for this staff)
        $structures = StaffSalaryStructure::with('component')
            ->where('user_id', $staff->id)
            ->current()
            ->get()
            ->keyBy('component_id');

        // Load all active components in sort order
        $components = SalaryComponent::active()
            ->orderBy('sort_order')
            ->get();

        // If no structure defined, fall back to user.basic_salary
        $basicSalary = 0.0;
        if ($structures->isEmpty() && $staff->basic_salary > 0) {
            $basicSalary = (float) $staff->basic_salary;
        }

        $earnings   = [];
        $deductions = [];
        $grossSalary = 0.0;

        // Pass 1: calculate all earnings first (needed for % deductions)
        foreach ($components->where('type', 'earning') as $component) {
            $override = $structures->get($component->id)?->amount ?? 0;
            $isBasic  = $component->code === 'BASIC';

            // For basic, use override OR user.basic_salary
            if ($isBasic && $override == 0) {
                $override = $staff->basic_salary ?? 0;
            }

            $amount = $component->compute(
                basicSalary:    $isBasic ? (float) $override : $basicSalary,
                presentDays:    $presentDays,
                workingDays:    $workingDays,
                overrideAmount: (float) $override,
            );

            if ($isBasic) {
                $basicSalary = $amount;
            }

            $earnings[$component->code] = [
                'component'   => $component,
                'amount'      => $amount,
                'is_taxable'  => $component->is_taxable,
            ];

            $grossSalary += $amount;
        }

        // Pass 2: calculate all deductions (can reference basicSalary / grossSalary)
        $totalDeductions = 0.0;
        $tdsPayable      = 0.0;
        $pfAmount        = 0.0;
        $citAmount       = 0.0;
        $ssfAmount       = 0.0;
        $lateFine        = 0.0;
        $otherDeductions = 0.0;

        foreach ($components->where('type', 'deduction') as $component) {
            $override = $structures->get($component->id)?->amount ?? 0;

            // Attendance-based late fine
            if ($component->code === 'LATE') {
                $amount = $lateDays * (float) ($override ?: $component->default_value);
            } else {
                $amount = $component->compute(
                    basicSalary:    $basicSalary,
                    presentDays:    $presentDays,
                    workingDays:    $workingDays,
                    overrideAmount: (float) $override,
                );
            }

            // Special deduction tracking
            match ($component->code) {
                'TDS'   => $tdsPayable = $amount,
                'PF'    => $pfAmount   = $amount,
                'CIT'   => $citAmount  = $amount,
                'SSF'   => $ssfAmount  = $amount,
                'LATE'  => $lateFine   = $amount,
                default => $otherDeductions += $amount,
            };

            $deductions[$component->code] = [
                'component' => $component,
                'amount'    => $amount,
            ];

            $totalDeductions += $amount;
        }

        // If no components defined, fall back to Nepal progressive TDS on gross
        if ($components->where('type', 'deduction')->count() === 0) {
            $tdsPayable      = $this->computeProgressiveTax($grossSalary);
            $totalDeductions = $tdsPayable;
        }

        // Absent day deduction (prorated from basic salary)
        $absentDeduction = $workingDays > 0
            ? round(($basicSalary / $workingDays) * $absentDays, 2)
            : 0.0;

        $totalDeductions += $absentDeduction;

        $netSalary = max(0, $grossSalary - $totalDeductions);

        return [
            // Core payroll fields (compatible with both SalarySlip + TeacherPayroll)
            'basic_salary'     => round($basicSalary, 2),
            'allowances'       => round($grossSalary - $basicSalary, 2),
            'overtime'         => 0,    // Manual entry
            'bonus'            => 0,    // Manual entry
            'gross_salary'     => round($grossSalary, 2),
            'tax_deduction'    => round($tdsPayable, 2),
            'tds_payable'      => round($tdsPayable, 2),
            'provident_fund'   => round($pfAmount, 2),
            'cit_deduction'    => round($citAmount, 2),
            'ssf_deduction'    => round($ssfAmount, 2),
            'late_fine'        => round($lateFine + $absentDeduction, 2),
            'other_deductions' => round($otherDeductions, 2),
            'net_salary'       => round($netSalary, 2),
            // Attendance
            'working_days'     => $workingDays,
            'present_days'     => $presentDays,
            'absent_days'      => $absentDays,
            'leave_days'       => $leaveDays,
            // Breakdown arrays for payslip rendering
            '_earnings'        => $earnings,
            '_deductions'      => $deductions,
        ];
    }

    // ── Progressive TDS computation (Nepal IRD slabs) ─────────────────────────

    /**
     * Compute income tax using active TaxRate slabs from the DB.
     * Falls back to Nepal 2083 approximate slabs if no DB rules exist.
     */
    public function computeProgressiveTax(float $grossMonthly): float
    {
        // Annualise
        $annual = $grossMonthly * 12;

        // Load active income_tax slabs
        $slabs = TaxRate::where('type', 'income_tax')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', today());
            })
            ->where(function ($q) {
                $q->whereNull('effective_from')->orWhere('effective_from', '<=', today());
            })
            ->orderBy('min_salary')
            ->get();

        $tax = 0.0;

        if ($slabs->isNotEmpty()) {
            foreach ($slabs as $slab) {
                $tax += $slab->calculateDeduction($annual);
            }
        } else {
            // Hardcoded Nepal approximate slabs (FY 2083-84)
            // ≤ 5,00,000  → 1%
            // 5,00,001 – 7,00,000  → 10%
            // 7,00,001 – 10,00,000 → 20%
            // > 10,00,000          → 30%
            $tax = match (true) {
                $annual <= 500000  => $annual * 0.01,
                $annual <= 700000  => 5000  + ($annual - 500000) * 0.10,
                $annual <= 1000000 => 25000 + ($annual - 700000) * 0.20,
                default            => 85000 + ($annual - 1000000) * 0.30,
            };
        }

        // Return monthly portion
        return round($tax / 12, 2);
    }

    // ── Attendance helpers ────────────────────────────────────────────────────

    private function getAttendance(User $staff, int $month, int $year): array
    {
        $isTeacher = $staff->hasAnyRole(['teacher', 'class-teacher']);

        if ($isTeacher) {
            $rows = TeacherAttendance::where('teacher_id', $staff->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->get();
        } else {
            $rows = StaffAttendance::where('staff_id', $staff->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->get();
        }

        return [
            'present' => $rows->whereIn('status', ['present', 'half_day'])->count(),
            'absent'  => $rows->where('status', 'absent')->count(),
            'leave'   => $rows->where('status', 'on_leave')->count(),
            'late'    => $rows->where('status', 'late')->count(),
        ];
    }
}
