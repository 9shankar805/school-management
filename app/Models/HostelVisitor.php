<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HostelVisitor extends Model
{
    protected $fillable = [
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
