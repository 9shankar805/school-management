<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class TeacherPayroll extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'teacher_id',
        'month',
        'year',
        'basic_salary',
        'allowances',
        'overtime',
        'bonus',
        'gross_salary',
        'tax_deduction',
        'tds_payable',
        'other_deductions',
        'cit_deduction',
        'ssf_deduction',
        'late_fine',
        'loan_deduction',
        'advance_deduction',
        'insurance_deduction',
        'net_salary',
        'working_days',
        'present_days',
        'absent_days',
        'leave_days',
        'notes',
        'status',
        'payment_method',
        'payment_date',
        'processed_by',
        'is_approved',
        'approved_by',
    ];

    protected $casts = [
        'payment_date'        => 'date',
        'is_approved'         => 'boolean',
        'basic_salary'        => 'float',
        'allowances'          => 'float',
        'overtime'            => 'float',
        'bonus'               => 'float',
        'gross_salary'        => 'float',
        'tax_deduction'       => 'float',
        'tds_payable'         => 'float',
        'cit_deduction'       => 'float',
        'ssf_deduction'       => 'float',
        'late_fine'           => 'float',
        'loan_deduction'      => 'float',
        'advance_deduction'   => 'float',
        'insurance_deduction' => 'float',
        'other_deductions'    => 'float',
        'net_salary'          => 'float',
    ];

    public function teacher()   { return $this->belongsTo(User::class, 'teacher_id'); }
    public function processor() { return $this->belongsTo(User::class, 'processed_by'); }
    public function approver()  { return $this->belongsTo(User::class, 'approved_by'); }

    public function getMonthNameAttribute(): string {
        return Carbon::create()->month($this->month)->format('F');
    }

    public function getNepaliMonthNameAttribute(): string {
        $nepaliMonths = [
            1 => 'Baisakh', 2 => 'Jestha', 3 => 'Ashadh', 4 => 'Shrawan',
            5 => 'Bhadra', 6 => 'Ashwin', 7 => 'Kartik', 8 => 'Mangsir',
            9 => 'Poush', 10 => 'Magh', 11 => 'Falgun', 12 => 'Chaitra',
        ];
        return $nepaliMonths[$this->month] ?? $this->month_name;
    }

    public function getTotalDeductionsAttribute(): float {
        return (float) (
            ($this->tax_deduction ?: $this->tds_payable) +
            $this->cit_deduction +
            $this->ssf_deduction +
            $this->late_fine +
            $this->loan_deduction +
            $this->advance_deduction +
            $this->insurance_deduction +
            $this->other_deductions
        );
    }

    public function getStatusBadgeAttribute(): string {
        return match($this->status) {
            'paid'      => 'bg-emerald-100 text-emerald-800 border border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800',
            'draft'     => $this->is_approved
                            ? 'bg-blue-100 text-blue-800 border border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800'
                            : 'bg-amber-100 text-amber-800 border border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800',
            'cancelled' => 'bg-rose-100 text-rose-800 border border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800',
            default     => 'bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
        };
    }
}
