<?php

namespace App\Http\Controllers\Api\ParentsApp;

use App\Http\Controllers\Controller;
use App\Models\DisciplinaryRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BehaviorController extends Controller
{
    /**
     * GET /api/behavior/child/{childId}
     */
    public function byChild(Request $request, $childId): JsonResponse
    {
        $request->user()->children()->findOrFail($childId);

        $records = DisciplinaryRecord::with('reporter')
            ->where('student_id', $childId)
            ->orderBy('incident_date', 'desc')
            ->get();

        $active = $records->where('resolved', false)->values();
        $history = $records->where('resolved', true)->values();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'active'  => $active,
                'history' => $history,
            ]
        ]);
    }

    /**
     * GET /api/behavior/summary/child/{childId}
     */
    public function summaryByChild(Request $request, $childId): JsonResponse
    {
        $request->user()->children()->findOrFail($childId);

        $records = DisciplinaryRecord::where('student_id', $childId)->get();

        $totalIncidents    = $records->count();
        $activeIncidents   = $records->where('resolved', false)->count();
        $resolvedIncidents = $records->where('resolved', true)->count();
        $totalPoints       = $records->sum('demerit_points');

        return response()->json([
            'status' => 'success',
            'data'   => [
                'student_id'         => (int) $childId,
                'total_incidents'    => $totalIncidents,
                'active_incidents'   => $activeIncidents,
                'resolved_incidents' => $resolvedIncidents,
                'total_demerit_points' => $totalPoints,
            ],
        ]);
    }

    /**
     * GET /api/behavior/commendations
     */
    public function commendations(Request $request): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');

        $commendations = DisciplinaryRecord::with('reporter')
            ->whereIn('student_id', $childIds)
            ->where('incident_type', 'commendation')
            ->orderBy('incident_date', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $commendations,
        ]);
    }

    /**
     * POST /api/behavior/commendations
     */
    public function storeCommendation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id'    => 'required|integer',
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'incident_date' => 'required|date',
        ]);

        $request->user()->children()->findOrFail($validated['student_id']);

        $record = DisciplinaryRecord::create([
            'school_id'     => $request->user()->school_id ?? 1,
            'student_id'    => $validated['student_id'],
            'reported_by'   => $request->user()->id,
            'incident_type' => 'commendation',
            'title'         => $validated['title'],
            'description'   => $validated['description'] ?? null,
            'incident_date' => $validated['incident_date'],
            'resolved'      => true,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Commendation recorded.',
            'data'    => $record,
        ], 201);
    }

    /**
     * GET /api/behavior/commendations/child/{childId}
     */
    public function commendationsByChild(Request $request, $childId): JsonResponse
    {
        $request->user()->children()->findOrFail($childId);

        $commendations = DisciplinaryRecord::with('reporter')
            ->where('student_id', $childId)
            ->where('incident_type', 'commendation')
            ->orderBy('incident_date', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $commendations,
        ]);
    }

    /**
     * GET /api/behavior/warnings
     */
    public function warnings(Request $request): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');

        $warnings = DisciplinaryRecord::with('reporter')
            ->whereIn('student_id', $childIds)
            ->whereIn('incident_type', ['warning', 'infraction', 'misconduct'])
            ->orderBy('incident_date', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $warnings,
        ]);
    }

    /**
     * POST /api/behavior/warnings
     */
    public function storeWarning(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id'    => 'required|integer',
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'incident_date' => 'required|date',
            'demerit_points'=> 'nullable|integer',
        ]);

        $request->user()->children()->findOrFail($validated['student_id']);

        $record = DisciplinaryRecord::create([
            'school_id'     => $request->user()->school_id ?? 1,
            'student_id'    => $validated['student_id'],
            'reported_by'   => $request->user()->id,
            'incident_type' => 'warning',
            'title'         => $validated['title'],
            'description'   => $validated['description'] ?? null,
            'incident_date' => $validated['incident_date'],
            'demerit_points'=> $validated['demerit_points'] ?? 0,
            'resolved'      => false,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Warning recorded.',
            'data'    => $record,
        ], 201);
    }

    /**
     * GET /api/behavior/warnings/child/{childId}
     */
    public function warningsByChild(Request $request, $childId): JsonResponse
    {
        $request->user()->children()->findOrFail($childId);

        $warnings = DisciplinaryRecord::with('reporter')
            ->where('student_id', $childId)
            ->whereIn('incident_type', ['warning', 'infraction', 'misconduct'])
            ->orderBy('incident_date', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $warnings,
        ]);
    }
}
