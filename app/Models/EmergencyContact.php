<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmergencyContact extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
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
