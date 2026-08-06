<?php

namespace App\Http\Controllers\Api\ParentsApp;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    /**
     * GET /api/health/child/{childId}
     */
    public function byChild(Request $request, $childId): JsonResponse
    {
        $request->user()->children()->findOrFail($childId);

        // Fetch medical record
        $record = MedicalRecord::where('student_id', $childId)->first();

        if (!$record) {
            return response()->json([
                'status' => 'success',
                'data'   => null,
                'message'=> 'No medical record found for this child.'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data'   => collect($record)->merge([
                'bmi' => $record->bmi
            ]),
        ]);
    }

    /**
     * PUT /api/health/child/{childId}
     */
    public function update(Request $request, $childId): JsonResponse
    {
        $request->user()->children()->findOrFail($childId);

        $validated = $request->validate([
            'allergies'               => 'nullable|string',
            'chronic_conditions'      => 'nullable|string',
            'medications'             => 'nullable|string',
            'vaccination_history'     => 'nullable|string',
            'blood_type'              => 'nullable|string|max:10',
            'height_cm'               => 'nullable|numeric',
            'weight_kg'               => 'nullable|numeric',
            'emergency_medical_notes' => 'nullable|string',
            'doctor_name'             => 'nullable|string|max:255',
            'doctor_phone'            => 'nullable|string|max:50',
        ]);

        $record = MedicalRecord::firstOrCreate(
            ['student_id' => $childId],
            ['school_id' => $request->user()->school_id ?? 1]
        );

        $record->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Medical record updated securely.',
            'data'    => $record,
        ]);
    }
}
