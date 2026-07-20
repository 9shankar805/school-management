<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HostelAttendance extends Model
{
    protected $fillable = [
        'hostel_id', 'student_id', 'date', 'status', 'remarks'
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
