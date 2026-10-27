<?php

namespace App\Http\Controllers\Api\StudentApp;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    /**
     * GET /student-app/fees
     * All fee invoices for the authenticated student.
     */
    public function index(Request $request): JsonResponse
    {
        $invoices = Invoice::with('payments')
            ->where('student_id', $request->user()->id)
            ->orderBy('due_date', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $invoices,
        ]);
    }

    /**
     * GET /student-app/fees/summary
     * Aggregated fee summary for the student.
     */
    public function summary(Request $request): JsonResponse
    {
        $invoices = Invoice::where('student_id', $request->user()->id)->get();

        $totalPending = $invoices->where('status', 'unpaid')->sum('total_amount');
        $totalPaid    = $invoices->where('status', 'paid')->sum('total_amount');
        $totalOverdue = $invoices->filter(
            fn($inv) => $inv->status === 'unpaid' && Carbon::parse($inv->due_date)->isPast()
        )->sum('total_amount');

        return response()->json([
            'status' => 'success',
            'data'   => [
                'total_pending' => (float) $totalPending,
                'total_paid'    => (float) $totalPaid,
                'total_overdue' => (float) $totalOverdue,
            ],
        ]);
    }

    /**
     * GET /student-app/fees/{id}
     * Single invoice detail.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $invoice = Invoice::with('payments')
            ->where('student_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $invoice,
        ]);
    }

    /**
     * GET /student-app/fees/status/{status}
     * Filter invoices by status: paid | unpaid | overdue.
     */
    public function byStatus(Request $request, string $status): JsonResponse
    {
        $query = Invoice::with('payments')
            ->where('student_id', $request->user()->id);

        if ($status === 'overdue') {
            $query->where('status', 'unpaid')->where('due_date', '<', now());
        } else {
            $query->where('status', $status);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $query->orderBy('due_date')->get(),
        ]);
    }

    /**
     * GET /student-app/fees/{id}/receipt
     * Payment receipt for a given payment ID.
     */
    public function receipt(Request $request, int $id): JsonResponse
    {
        $payment = Payment::with(['invoice'])
            ->whereHas('invoice', fn($q) => $q->where('student_id', $request->user()->id))
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $payment,
        ]);
    }
}
