<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeacherAttendance extends Model
{
    use HasFactory;
    protected $fillable = ['teacher_id','date','status','check_in','check_out','notes','marked_by'];
    protected $casts = ['date'=>'date'];

    const STATUSES = ['present'=>'Present','absent'=>'Absent','late'=>'Late','half_day'=>'Half Day','on_leave'=>'On Leave'];

    public function teacher()  { return $this->belongsTo(User::class, 'teacher_id'); }
    public function markedBy() { return $this->belongsTo(User::class, 'marked_by'); }

    public function getStatusBadgeAttribute(): string {
        return match($this->status) {
            'present'   => 'bg-emerald-100 text-emerald-700',
            'absent'    => 'bg-rose-100 text-rose-700',
            'late'      => 'bg-amber-100 text-amber-700',
            'half_day'  => 'bg-blue-100 text-blue-700',
            'on_leave'  => 'bg-violet-100 text-violet-700',
            default     => 'bg-slate-100 text-slate-600',
        };
    }
}
