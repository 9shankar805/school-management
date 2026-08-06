<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class HostelVisitor extends Model
{
    use HasTenant, SoftDeletes;
    protected $fillable = [
        'school_id',
        'hostel_id', 'student_id', 'visitor_name', 'relation', 'date', 'in_time', 'out_time', 'purpose'
    ];

    public function hostel()
    {
        return $this->belongsTo(Hostel::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
