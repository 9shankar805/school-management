<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class TeacherContract extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['teacher_id','contract_type','start_date','end_date','basic_salary','position','terms','attachment_path','status','created_by'];
    protected $casts = ['start_date'=>'date','end_date'=>'date','basic_salary'=>'decimal:2'];

    const TYPES = ['permanent'=>'Permanent','temporary'=>'Temporary','part_time'=>'Part-Time','visiting'=>'Visiting','probation'=>'Probation'];

    public function teacher() { return $this->belongsTo(User::class, 'teacher_id'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    public function getIsExpiringAttribute(): bool {
        return $this->end_date && $this->end_date->diffInDays(now()) <= 30 && $this->end_date >= now() && $this->status === 'active';
    }
    public function getIsExpiredAttribute(): bool {
        return $this->end_date && $this->end_date < now() && $this->status === 'active';
    }
    public function getStatusBadgeAttribute(): string {
        return match($this->status) {
            'active'     => 'bg-emerald-100 text-emerald-700',
            'expired'    => 'bg-rose-100 text-rose-700',
            'terminated' => 'bg-slate-100 text-slate-500',
            'renewed'    => 'bg-blue-100 text-blue-700',
            default      => 'bg-slate-100 text-slate-600',
        };
    }
}
