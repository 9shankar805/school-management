<?php
namespace App\Models;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveBalance extends Model
{
    use HasFactory, HasTenant, SoftDeletes;
    protected $fillable = ['school_id', 'user_id','leave_type_id','year','total_days','used_days','remaining_days'];

    public function user()      { return $this->belongsTo(User::class); }
    public function leaveType() { return $this->belongsTo(LeaveType::class); }
}
