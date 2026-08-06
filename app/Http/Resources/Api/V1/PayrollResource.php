<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Used for both TeacherPayroll and SalarySlip models.
 */
class PayrollResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $staffKey = $this->resource instanceof \App\Models\TeacherPayroll
            ? 'teacher'
            : 'staff';

        $staffId = $this->teacher_id ?? $this->staff_id;

        return [
            'id'               => $this->id,
            'type'             => $staffKey,
            'staff_id'         => $staffId,
            'staff_name'       => $this->teacher?->full_name ?? $this->staff?->full_name,
            'month'            => $this->month,
            'year'             => $this->year,
            'month_label'      => $this->month_name . ' ' . $this->year,
            'basic_salary'     => (float) $this->basic_salary,
            'allowances'       => (float) $this->allowances,
            'overtime'         => (float) $this->overtime,
            'bonus'            => (float) $this->bonus,
            'gross_salary'     => (float) $this->gross_salary,
            'deductions'       => [
                'tax_tds'      => (float) ($this->tds_payable ?? $this->tax_deduction ?? 0),
                'provident_fund'=> (float) ($this->provident_fund ?? 0),
                'cit'          => (float) ($this->cit_deduction ?? 0),
                'ssf'          => (float) ($this->ssf_deduction ?? 0),
                'late_fine'    => (float) ($this->late_fine ?? 0),
                'other'        => (float) ($this->other_deductions ?? 0),
            ],
            'net_salary'       => (float) $this->net_salary,
            'attendance'       => [
                'working_days' => $this->working_days,
                'present_days' => $this->present_days,
                'absent_days'  => $this->absent_days,
                'leave_days'   => $this->leave_days,
            ],
            'status'           => $this->status,
            'is_approved'      => (bool) $this->is_approved,
            'payment_date'     => $this->payment_date?->format('Y-m-d'),
            'payment_method'   => $this->payment_method,
            'created_at'       => $this->created_at?->toIso8601String(),
        ];
    }
}
