<?php

namespace App\Http\Controllers\Api\ParentsApp;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    /**
     * GET /api/fees
     */
    public function index(Request $request): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $invoices = Invoice::with(['student', 'payments'])
            ->whereIn('student_id', $childIds)
            ->orderBy('due_date', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $invoices,
        ]);
    }

    /**
     * GET /api/fees/summary
     */
    public function summary(Request $request): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $invoices = Invoice::whereIn('student_id', $childIds)->get();

        $totalPending = $invoices->where('status', 'unpaid')->sum('amount');
        $totalPaid    = $invoices->where('status', 'paid')->sum('amount');
        
        // Assume anything past due date and unpaid is overdue
        $totalOverdue = $invoices->filter(function($invoice) {
            return $invoice->status === 'unpaid' && \Carbon\Carbon::parse($invoice->due_date)->isPast();
        })->sum('amount');

        return response()->json([
            'status' => 'success',
            'data'   => [
                'total_pending' => $totalPending,
                'total_paid'    => $totalPaid,
                'total_overdue' => $totalOverdue,
            ]
        ]);
    }

    /**
     * GET /api/fees/status/{status}
     */
    public function byStatus(Request $request, $status): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');

        $query = Invoice::with(['student', 'payments'])
            ->whereIn('student_id', $childIds);

        if ($status === 'overdue') {
            $query->where('status', 'unpaid')
                  ->where('due_date', '<', now());
        } else {
            $query->where('status', $status);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $query->orderBy('due_date', 'asc')->get(),
        ]);
    }

    /**
     * GET /api/fees/child/{childId}
     */
    public function byChild(Request $request, $childId): JsonResponse
    {
        // Verify child belongs to parent
        $request->user()->children()->findOrFail($childId);

        $invoices = Invoice::with(['payments'])
            ->where('student_id', $childId)
            ->orderBy('due_date', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $invoices,
        ]);
    }

    /**
     * GET /api/fees/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $invoice = Invoice::with(['student', 'payments'])
            ->whereIn('student_id', $childIds)
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $invoice,
        ]);
    }

    /**
     * POST /api/fees/{id}/pay
     */
    public function pay(Request $request, $id): JsonResponse
    {
        $childIds = $request->user()->children()->pluck('users.id');
        
        $invoice = Invoice::whereIn('student_id', $childIds)->findOrFail($id);

        if ($invoice->status === 'paid') {
            return response()->json(['status' => 'error', 'message' => 'Invoice is already paid'], 400);
        }

        $validated = $request->validate([
            'payment_method' => 'required|string', // e.g., 'credit_card', 'paypal', 'bank_transfer'
        ]);

        // MOCK PAYMENT PROCESS
        // Create a payment record for the full amount
        $payment = Payment::create([
            'school_id'      => $invoice->school_id,
            'invoice_id'     => $invoice->id,
            'amount_paid'    => $invoice->amount,
            'payment_date'   => now(),
            'payment_method' => $validated['payment_method'],
        ]);

        $invoice->update(['status' => 'paid']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Payment processed successfully (mocked)',
            'data'    => [
                'invoice' => $invoice,
                'payment' => $payment,
            ],
        ]);
    }

    /**
     * GET /api/fees/{id}/receipt
     */
    public function receipt(Request $request, $id): JsonResponse
    {
        // Here ID is the payment ID
        $childIds = $request->user()->children()->pluck('users.id');
        
        $payment = Payment::with(['invoice.student'])
            ->whereHas('invoice', function($q) use ($childIds) {
                $q->whereIn('student_id', $childIds);
            })
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $payment,
        ]);
    }
}
