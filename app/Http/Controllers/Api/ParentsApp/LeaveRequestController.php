<?php

namespace App\Http\Controllers\Api\ParentsApp;

use App\Http\Controllers\Controller;
use App\Models\LeaveApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    /**
     * GET /api/leave-requests
     */
    public function index(Request $request): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $leaves = LeaveApplication::with(['user', 'leaveType'])
            ->whereIn('user_id', $childIds)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $leaves,
        ]);
    }

    /**
     * GET /api/leave-requests/status/{status}
     */
    public function byStatus(Request $request, $status): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $leaves = LeaveApplication::with(['user', 'leaveType'])
            ->whereIn('user_id', $childIds)
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $leaves,
        ]);
    }

    /**
     * GET /api/leave-requests/child/{childId}
     */
    public function byChild(Request $request, $childId): JsonResponse
    {
        $request->user()->children()->findOrFail($childId);

        $leaves = LeaveApplication::with(['leaveType'])
            ->where('user_id', $childId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $leaves,
        ]);
    }

    /**
     * GET /api/leave-requests/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $leave = LeaveApplication::with(['user', 'leaveType', 'reviewer'])
            ->whereIn('user_id', $childIds)
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => collect($leave)->merge(['status_badge' => $leave->status_badge]),
        ]);
    }

    /**
     * POST /api/leave-requests
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'child_id'      => 'required|integer',
            'leave_type_id' => 'nullable|integer',
            'from_date'     => 'required|date',
            'to_date'       => 'required|date|after_or_equal:from_date',
            'reason'        => 'required|string',
        ]);

        $childId = $validated['child_id'];
        
        // Verify child
        $request->user()->children()->findOrFail($childId);

        // Calculate total days (inclusive)
        $start = \Carbon\Carbon::parse($validated['from_date']);
        $end = \Carbon\Carbon::parse($validated['to_date']);
        $totalDays = $start->diffInDays($end) + 1;

        $leave = LeaveApplication::create([
            'school_id'     => $request->user()->school_id ?? 1,
            'user_id'       => $childId,
            'leave_type_id' => $validated['leave_type_id'] ?? null,
            'from_date'     => $validated['from_date'],
            'to_date'       => $validated['to_date'],
            'total_days'    => $totalDays,
            'reason'        => $validated['reason'],
            'status'        => 'pending',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Leave request submitted successfully.',
            'data'    => $leave,
        ], 201);
    }

    /**
     * PUT /api/leave-requests/{id}/cancel
     */
    public function cancel(Request $request, $id): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $leave = LeaveApplication::whereIn('user_id', $childIds)->findOrFail($id);

        if ($leave->status !== 'pending') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Only pending requests can be cancelled.'
            ], 400);
        }

        $leave->update(['status' => 'cancelled']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Leave request cancelled.',
            'data'    => $leave,
        ]);
    }

    /**
     * PUT /api/leave-requests/{id}/approve
     */
    public function approve(Request $request, $id): JsonResponse
    {
        return response()->json([
            'status'  => 'error', 
            'message' => 'Parents cannot approve leave requests.'
        ], 403);
    }

    /**
     * PUT /api/leave-requests/{id}/reject
     */
    public function reject(Request $request, $id): JsonResponse
    {
        return response()->json([
            'status'  => 'error', 
            'message' => 'Parents cannot reject leave requests.'
        ], 403);
    }
}
