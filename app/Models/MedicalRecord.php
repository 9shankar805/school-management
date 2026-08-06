<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Sensitive PHI model — all identifiable medical fields are encrypted
 * at rest using Laravel's AES-256-CBC 'encrypted' cast.
 * Decryption is transparent via Eloquent accessors.
 */
class MedicalRecord extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'student_id', 'allergies', 'chronic_conditions', 'medications',
        'vaccination_history', 'blood_type', 'height_cm', 'weight_kg',
        'eye_condition', 'hearing_condition', 'special_needs',
        'emergency_medical_notes', 'doctor_name', 'doctor_phone',
    ];

    /**
     * Sensitive PHI columns encrypted at rest via Laravel's built-in
     * AES-256-CBC encryption (APP_KEY is the encryption key).
     * Values are automatically encrypted on write and decrypted on read.
     */
    protected $casts = [
        'allergies'               => 'encrypted',
        'chronic_conditions'      => 'encrypted',
        'medications'             => 'encrypted',
        'vaccination_history'     => 'encrypted',
        'special_needs'           => 'encrypted',
        'emergency_medical_notes' => 'encrypted',
        'doctor_name'             => 'encrypted',
        'doctor_phone'            => 'encrypted',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function getBmiAttribute(): ?float
    {
        if ($this->height_cm && $this->weight_kg && $this->height_cm > 0) {
            $heightM = $this->height_cm / 100;
            return round($this->weight_kg / ($heightM * $heightM), 1);
        }
        return null;
    }
}
