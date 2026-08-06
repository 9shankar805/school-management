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
}
