<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    public function __construct()
    {
        $this->middleware(['can:view students']);
    }

    public function upsert(Request $request, int $studentId)
    {
        $this->authorize('create students');

        $data = $request->validate([
            'allergies'               => 'nullable|string|max:2000',
            'chronic_conditions'      => 'nullable|string|max:2000',
            'medications'             => 'nullable|string|max:2000',
            'vaccination_history'     => 'nullable|string|max:2000',
            'blood_type'              => 'nullable|string|max:10',
            'height_cm'               => 'nullable|numeric|min:0|max:300',
            'weight_kg'               => 'nullable|numeric|min:0|max:500',
            'eye_condition'           => 'nullable|string|max:255',
            'hearing_condition'       => 'nullable|string|max:255',
            'special_needs'           => 'nullable|string|max:2000',
            'emergency_medical_notes' => 'nullable|string|max:2000',
            'doctor_name'             => 'nullable|string|max:255',
            'doctor_phone'            => 'nullable|string|max:30',
        ]);

        MedicalRecord::updateOrCreate(
            ['student_id' => $studentId],
            $data
        );

        return back()->with('status', 'Medical record saved.');
    }
}
