<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmergencyContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'name', 'relationship', 'phone', 'phone_alt',
        'email', 'address', 'is_primary', 'is_authorized_pickup',
    ];

    protected $casts = [
        'is_primary'            => 'boolean',
        'is_authorized_pickup'  => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
