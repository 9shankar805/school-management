<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveApplication extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','leave_type_id','from_date','to_date','total_days','reason','status','reviewer_notes','reviewed_by','reviewed_at','attachment_path'];
    protected $casts = ['from_date'=>'date','to_date'=>'date','reviewed_at'=>'datetime'];

    public function user()       { return $this->belongsTo(User::class); }
    public function leaveType()  { return $this->belongsTo(LeaveType::class); }
    public function reviewer()   { return $this->belongsTo(User::class, 'reviewed_by'); }

    public function getStatusBadgeAttribute(): string {
        return match($this->status) {
            'pending'   => 'bg-amber-100 text-amber-700',
            'approved'  => 'bg-emerald-100 text-emerald-700',
            'rejected'  => 'bg-rose-100 text-rose-700',
            'cancelled' => 'bg-slate-100 text-slate-500',
            default     => 'bg-slate-100 text-slate-600',
        };
    }
}
