<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Staff Salary Slip
 * Covers all non-teacher staff.  Teachers use TeacherPayroll.
 */
class SalarySlip extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'staff_id',
        'month',
        'year',
        'basic_salary',
        'allowances',
        'overtime',
        'bonus',
        'gross_salary',
        'tax_deduction',
        'provident_fund',
        'other_deductions',
        'net_salary',
        'working_days',
        'present_days',
        'absent_days',
        'leave_days',
        'notes',
        'status',        // draft | approved | paid
        'payment_date',
        'payment_method', // cash | bank_transfer | cheque
        'processed_by',
    ];

    protected $casts = [
        'payment_date'     => 'date',
        'basic_salary'     => 'decimal:2',
        'allowances'       => 'decimal:2',
        'overtime'         => 'decimal:2',
        'bonus'            => 'decimal:2',
        'gross_salary'     => 'decimal:2',
        'tax_deduction'    => 'decimal:2',
        'provident_fund'   => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'net_salary'       => 'decimal:2',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function getMonthNameAttribute(): string
    {
        return Carbon::create()->month($this->month)->format('F');
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'paid'     => 'bg-success',
            'approved' => 'bg-primary',
            'draft'    => 'bg-warning text-dark',
            default    => 'bg-secondary',
        };
    }
}
