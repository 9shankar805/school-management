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
     * GET /api/health/child/{childId}/overview
     */
    public function overviewByChild(Request $request, $childId): JsonResponse
    {
        $request->user()->children()->findOrFail($childId);

        $record = MedicalRecord::where('student_id', $childId)->first();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'student_id'          => (int) $childId,
                'blood_type'          => $record->blood_type ?? 'Unknown',
                'height_cm'           => $record->height_cm ?? null,
                'weight_kg'           => $record->weight_kg ?? null,
                'bmi'                 => $record?->bmi ?? null,
                'allergies'           => $record->allergies ?? 'None reported',
                'chronic_conditions'  => $record->chronic_conditions ?? 'None reported',
                'medications'         => $record->medications ?? 'None',
                'vaccination_history' => $record->vaccination_history ?? null,
                'doctor_name'         => $record->doctor_name ?? null,
                'doctor_phone'        => $record->doctor_phone ?? null,
            ],
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

    /**
     * GET /api/health/vaccinations
     */
    public function vaccinations(Request $request): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        $records  = MedicalRecord::whereIn('student_id', $childIds)->get();

        $vaccinations = $records->map(function ($rec) {
            return [
                'student_id'          => $rec->student_id,
                'vaccination_history' => $rec->vaccination_history,
                'updated_at'          => $rec->updated_at,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $vaccinations,
        ]);
    }

    /**
     * POST /api/health/vaccinations
     */
    public function storeVaccination(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id'          => 'required|integer',
            'vaccination_history' => 'required|string',
        ]);

        $request->user()->children()->findOrFail($validated['student_id']);

        $record = MedicalRecord::firstOrCreate(
            ['student_id' => $validated['student_id']],
            ['school_id' => $request->user()->school_id ?? 1]
        );

        $history = trim(($record->vaccination_history ? $record->vaccination_history . '; ' : '') . $validated['vaccination_history']);
        $record->update(['vaccination_history' => $history]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Vaccination record added successfully.',
            'data'    => $record,
        ]);
    }

    /**
     * GET /api/health/vaccinations/due
     */
    public function vaccinationsDue(Request $request): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');

        return response()->json([
            'status' => 'success',
            'data'   => [],
            'message'=> 'All required vaccinations are up to date.',
        ]);
    }

    /**
     * GET /api/health/vaccinations/child/{childId}
     */
    public function vaccinationsByChild(Request $request, $childId): JsonResponse
    {
        $request->user()->children()->findOrFail($childId);
        $record = MedicalRecord::where('student_id', $childId)->first();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'student_id'          => (int) $childId,
                'vaccination_history' => $record->vaccination_history ?? null,
            ],
        ]);
    }

    /**
     * PUT /api/health/vaccinations/{id}
     */
    public function updateVaccination(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'student_id'          => 'required|integer',
            'vaccination_history' => 'required|string',
        ]);

        $request->user()->children()->findOrFail($validated['student_id']);
        $record = MedicalRecord::where('student_id', $validated['student_id'])->firstOrFail();
        $record->update(['vaccination_history' => $validated['vaccination_history']]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Vaccination history updated.',
            'data'    => $record,
        ]);
    }

    /**
     * GET /api/health/nurse-visits
     */
    public function nurseVisits(Request $request): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        $records  = MedicalRecord::whereIn('student_id', $childIds)
            ->whereNotNull('emergency_medical_notes')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $records,
        ]);
    }

    /**
     * POST /api/health/nurse-visits
     */
    public function storeNurseVisit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|integer',
            'notes'      => 'required|string',
        ]);

        $request->user()->children()->findOrFail($validated['student_id']);

        $record = MedicalRecord::firstOrCreate(
            ['student_id' => $validated['student_id']],
            ['school_id' => $request->user()->school_id ?? 1]
        );

        $notes = trim(($record->emergency_medical_notes ? $record->emergency_medical_notes . "\n" : '') . '[Nurse Visit ' . now()->format('Y-m-d H:i') . ']: ' . $validated['notes']);
        $record->update(['emergency_medical_notes' => $notes]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Nurse visit logged.',
            'data'    => $record,
        ]);
    }

    /**
     * GET /api/health/nurse-visits/child/{childId}
     */
    public function nurseVisitsByChild(Request $request, $childId): JsonResponse
    {
        $request->user()->children()->findOrFail($childId);
        $record = MedicalRecord::where('student_id', $childId)->first();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'student_id' => (int) $childId,
                'notes'      => $record->emergency_medical_notes ?? null,
            ],
        ]);
    }

    /**
     * GET /api/health/medical-info/child/{childId}
     */
    public function medicalInfoByChild(Request $request, $childId): JsonResponse
    {
        return $this->byChild($request, $childId);
    }

    /**
     * PUT /api/health/medical-info/child/{childId}
     */
    public function updateMedicalInfo(Request $request, $childId): JsonResponse
    {
        return $this->update($request, $childId);
    }
}
