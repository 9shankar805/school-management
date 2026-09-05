<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentProof;
use App\Models\School;
use App\Notifications\GeneralNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * PaymentProofController
 *
 * Parent-side:
 *   GET  /payment-proof/{invoice}          show QR + submission form
 *   POST /payment-proof/{invoice}          submit proof (screenshot + txn ID)
 *
 * Admin/Accountant-side:
 *   GET  /payment-proofs                   pending proof list
 *   POST /payment-proofs/{proof}/approve   approve → auto-record payment
 *   POST /payment-proofs/{proof}/reject    reject with reason
 */
class PaymentProofController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ── PARENT: Show QR + submission form ────────────────────────────────────

    public function showForm(int $invoiceId)
    {
        $invoice = Invoice::with('student', 'items.feeType', 'payments')->findOrFail($invoiceId);
        $user    = auth()->user();

        // Parents can only pay their own child's invoice; students their own
        if ($user->hasRole('student') && $invoice->student_id !== $user->id) {
            abort(403);
        }
        if ($user->hasRole('parent')) {
            $childIds = $user->children()->pluck('users.id');
            if (! $childIds->contains($invoice->student_id)) {
                abort(403);
            }
        }

        if ($invoice->status === 'paid') {
            return back()->with('status', 'This invoice is already fully paid.');
        }

        // Pending proof already submitted
        $pendingProof = PaymentProof::where('invoice_id', $invoiceId)
            ->where('status', 'pending')
            ->latest()
            ->first();

        // Resolve school QR
        $paySchool = School::find(
            $invoice->school_id
            ?? $user->school_id
            ?? optional($invoice->student)->school_id
        );

        $amountDue = (float) $invoice->amount_due;

        return view('payments.payment-proof', compact(
            'invoice', 'paySchool', 'amountDue', 'pendingProof'
        ));
    }

    // ── PARENT: Submit proof ─────────────────────────────────────────────────

    public function submit(Request $request, int $invoiceId)
    {
        $invoice = Invoice::with('student')->findOrFail($invoiceId);
        $user    = auth()->user();

        if ($user->hasRole('student') && $invoice->student_id !== $user->id) abort(403);
        if ($user->hasRole('parent')) {
            $childIds = $user->children()->pluck('users.id');
            if (! $childIds->contains($invoice->student_id)) abort(403);
        }

        if ($invoice->status === 'paid') {
            return back()->with('status', 'Invoice already paid.');
        }

        $data = $request->validate([
            'gateway'        => 'required|in:esewa,khalti,bank,fonepay,other',
            'transaction_id' => 'nullable|string|max:200',
            'amount'         => 'required|numeric|min:1',
            'screenshot'     => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'notes'          => 'nullable|string|max:500',
        ]);

        // Store screenshot
        $path = $request->file('screenshot')->store('payment-proofs', 'public');

        $proof = PaymentProof::create([
            'invoice_id'     => $invoice->id,
            'student_id'     => $invoice->student_id,
            'submitted_by'   => $user->id,
            'gateway'        => $data['gateway'],
            'transaction_id' => $data['transaction_id'] ?? null,
            'amount'         => $data['amount'],
            'screenshot'     => $path,
            'notes'          => $data['notes'] ?? null,
            'status'         => 'pending',
            'school_id'      => $invoice->school_id ?? $user->school_id,
        ]);

        // Notify admins/accountants
        try {
            $admins = \App\Models\User::role(['admin', 'principal', 'accountant'])
                ->where(function($q) use ($invoice, $user) {
                    $sid = $invoice->school_id ?? $user->school_id;
                    if ($sid) $q->where('school_id', $sid);
                })
                ->take(5)->get();

            $studentName = $invoice->student?->full_name ?? 'A student';
            $gatewayLabel = match($data['gateway']) {
                'esewa' => 'eSewa', 'khalti' => 'Khalti',
                'bank'  => 'Bank',  'fonepay' => 'FonePay', default => 'Online'
            };

            foreach ($admins as $admin) {
                $admin->notify(new GeneralNotification(
                    title:     "💰 Fee Payment Proof Submitted",
                    message:   "{$studentName} submitted {$gatewayLabel} payment proof for invoice \"{$invoice->title}\" — NPR " . number_format($proof->amount, 2) . ". Please review and approve.",
                    actionUrl: route('payment-proofs.index'),
                    actionText: 'Review Now',
                    type:      'fee',
                ));
            }
        } catch (\Throwable $e) {
            Log::warning('PaymentProof notify failed: ' . $e->getMessage());
        }

        return redirect()->route('payments.index')
            ->with('status', '✅ Payment proof submitted successfully! The school admin will review and confirm within 24 hours.');
    }

    // ── ADMIN: Pending proofs list ────────────────────────────────────────────

    public function index(Request $request)
    {
        $this->authorize('manage finance');

        $status = $request->query('status', 'pending');
        $user   = auth()->user();

        $query = PaymentProof::with(['invoice.student', 'submittedBy', 'reviewedBy'])
            ->when($user->school_id, fn($q) => $q->where('school_id', $user->school_id))
            ->latest();

        if (in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        $proofs    = $query->paginate(20)->withQueryString();
        $pendingCount = PaymentProof::where('status', 'pending')
            ->when($user->school_id, fn($q) => $q->where('school_id', $user->school_id))
            ->count();

        return view('payments.proof-index', compact('proofs', 'status', 'pendingCount'));
    }

    // ── ADMIN: Approve proof → auto-record payment ────────────────────────────

    public function approve(Request $request, int $proofId)
    {
        $this->authorize('manage finance');

        $proof   = PaymentProof::with('invoice')->findOrFail($proofId);
        $invoice = $proof->invoice;

        if ($proof->status !== 'pending') {
            return back()->with('error', 'This proof has already been reviewed.');
        }

        DB::transaction(function () use ($proof, $invoice, $request) {
            // Record the payment
            Payment::create([
                'invoice_id'             => $invoice->id,
                'amount'                 => $proof->amount,
                'payment_date'           => today()->toDateString(),
                'payment_method'         => $proof->gateway,
                'gateway'                => $proof->gateway,
                'gateway_transaction_id' => $proof->transaction_id,
                'notes'                  => 'QR payment approved. Ref: ' . ($proof->transaction_id ?? 'N/A'),
                'recorded_by'            => auth()->id(),
                'school_id'              => $invoice->school_id,
            ]);

            // Mark proof approved
            $proof->update([
                'status'      => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'notes'       => $request->input('admin_notes', $proof->notes),
            ]);
        });

        // Notify the parent who submitted
        try {
            $submitter = $proof->submittedBy;
            if ($submitter) {
                $submitter->notify(new GeneralNotification(
                    title:     '✅ Payment Confirmed!',
                    message:   "Your " . $proof->gateway_label . " payment of NPR " . number_format($proof->amount, 2) . " for \"{$invoice->title}\" has been approved. Your invoice is now marked as paid.",
                    actionUrl: route('payments.index'),
                    actionText: 'View Invoice',
                    type:      'fee',
                ));
            }
        } catch (\Throwable $e) {
            Log::warning('PaymentProof approval notify failed: ' . $e->getMessage());
        }

        return back()->with('status', '✅ Payment approved and invoice updated.');
    }

    // ── ADMIN: Reject proof ───────────────────────────────────────────────────

    public function reject(Request $request, int $proofId)
    {
        $this->authorize('manage finance');

        $proof = PaymentProof::with('invoice')->findOrFail($proofId);

        if ($proof->status !== 'pending') {
            return back()->with('error', 'This proof has already been reviewed.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $proof->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
        ]);

        // Notify the parent
        try {
            $submitter = $proof->submittedBy;
            if ($submitter) {
                $submitter->notify(new GeneralNotification(
                    title:     '❌ Payment Proof Rejected',
                    message:   "Your payment proof for \"{$proof->invoice->title}\" was not accepted. Reason: {$request->rejection_reason}. Please resubmit with correct details.",
                    actionUrl: route('payment-proof.show', $proof->invoice_id),
                    actionText: 'Resubmit',
                    type:      'alert',
                ));
            }
        } catch (\Throwable $e) {
            Log::warning('PaymentProof rejection notify failed: ' . $e->getMessage());
        }

        return back()->with('status', 'Proof rejected and parent notified.');
    }
}
