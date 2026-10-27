<?php

namespace App\Http\Controllers\Api\StudentApp;

use App\Http\Controllers\Controller;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    /**
     * GET /student-app/leaves
     * All leave applications for the authenticated student.
     */
    public function index(Request $request): JsonResponse
    {
        $leaves = LeaveApplication::with('leaveType')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('from_date')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $leaves,
        ]);
    }

    /**
     * GET /student-app/leaves/types
     * Available leave types to display in the apply form.
     */
    public function types(Request $request): JsonResponse
    {
        $types = LeaveType::where('school_id', $request->user()->school_id)
            ->where('is_active', true)
            ->get(['id', 'name', 'days_allowed', 'code', 'is_paid']);

        return response()->json([
            'status' => 'success',
            'data'   => $types,
        ]);
    }

    /**
     * POST /student-app/leaves
     * Apply for leave.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            'from_date'     => ['required', 'date', 'after_or_equal:today'],
            'to_date'       => ['required', 'date', 'after_or_equal:from_date'],
            'reason'        => ['required', 'string', 'max:1000'],
        ]);

        $application = LeaveApplication::create([
            'user_id'       => $request->user()->id,
            'school_id'     => $request->user()->school_id,
            'leave_type_id' => $validated['leave_type_id'],
            'from_date'     => $validated['from_date'],
            'to_date'       => $validated['to_date'],
            'reason'        => $validated['reason'],
            'status'        => 'pending',
            'submitted_by'  => $request->user()->id,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Leave application submitted successfully.',
            'data'    => $application->load('leaveType'),
        ], 201);
    }

    /**
     * GET /student-app/leaves/{id}
     * Single leave application detail.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $leave = LeaveApplication::with('leaveType')
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $leave,
        ]);
    }

    /**
     * PUT /student-app/leaves/{id}/cancel
     * Cancel a pending leave application.
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $leave = LeaveApplication::where('user_id', $request->user()->id)
            ->findOrFail($id);

        if ($leave->status !== 'pending') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Only pending leave applications can be cancelled.',
            ], 422);
        }

        $leave->update(['status' => 'cancelled']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Leave application cancelled.',
        ]);
    }
}
