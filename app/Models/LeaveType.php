<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaveType extends Model
{
    use HasFactory;
    protected $fillable = ['name','code','days_allowed','is_paid','carry_forward','is_active'];
    protected $casts = ['is_paid'=>'boolean','carry_forward'=>'boolean','is_active'=>'boolean'];

    public function applications() { return $this->hasMany(LeaveApplication::class); }
    public function balances()     { return $this->hasMany(LeaveBalance::class); }
}
