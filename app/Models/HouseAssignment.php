<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HouseAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'session_id', 'house_name', 'house_color', 'captain_name', 'notes',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function session()
    {
        return $this->belongsTo(SchoolSession::class, 'session_id');
    }
}
