<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class RfidScan extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'device_id', 'user_id', 'card_uid', 'biometric_template_id',
        'scanned_at', 'direction', 'attendance_marked', 'attendance_id', 'raw_payload',
    ];

    protected $casts = [
        'scanned_at'        => 'datetime',
        'attendance_marked' => 'boolean',
    ];

    public function device()     { return $this->belongsTo(BiometricDevice::class, 'device_id'); }
    public function user()       { return $this->belongsTo(User::class); }
    public function attendance() { return $this->belongsTo(Attendance::class); }
}
