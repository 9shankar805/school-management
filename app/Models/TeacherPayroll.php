<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class TeacherPayroll extends Model
{
    use HasFactory;
    protected $fillable = [
        'teacher_id','month','year','basic_salary','allowances','overtime','gross_salary',
        'tax_deduction','other_deductions','net_salary','working_days','present_days',
        'absent_days','leave_days','notes','status','payment_date','processed_by',
    ];
    protected $casts = ['payment_date'=>'date'];

    public function teacher()   { return $this->belongsTo(User::class, 'teacher_id'); }
    public function processor() { return $this->belongsTo(User::class, 'processed_by'); }

    public function getMonthNameAttribute(): string {
        return Carbon::create()->month($this->month)->format('F');
    }
    public function getStatusBadgeAttribute(): string {
        return match($this->status) {
            'paid'      => 'bg-emerald-100 text-emerald-700',
            'draft'     => 'bg-amber-100 text-amber-700',
            'cancelled' => 'bg-rose-100 text-rose-700',
            default     => 'bg-slate-100 text-slate-600',
        };
    }
}
