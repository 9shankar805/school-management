<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MedicalRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'allergies', 'chronic_conditions', 'medications',
        'vaccination_history', 'blood_type', 'height_cm', 'weight_kg',
        'eye_condition', 'hearing_condition', 'special_needs',
        'emergency_medical_notes', 'doctor_name', 'doctor_phone',
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
