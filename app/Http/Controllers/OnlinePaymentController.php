<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Payment\PaymentGatewayManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * OnlinePaymentController
 *
 * Handles the initiate → redirect → callback flow for online payment gateways.
 *
 * Flow:
 *  1. POST /finance/initiate  — creates payment intent, redirects to gateway
 *  2. Gateway redirects back to success/failure/callback URL
 *  3. Controller verifies, records payment, updates invoice
 *
 * Supports: eSewa, Khalti, Stripe (card form redirect), Razorpay, PayPal
 */
class OnlinePaymentController extends Controller
{
    public function __construct(private PaymentGatewayManager $gateways)
    {
        $this->middleware('auth');
    }

    // ── Step 1: Initiate payment (redirect to gateway) ────────────────────────

    public function initiate(Request $request)
    {
        $data = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'gateway'    => 'required|in:esewa,khalti,stripe,razorpay,paypal,flutterwave',
        ]);

        $invoice = Invoice::with('student')->findOrFail($data['invoice_id']);

        // Students can only pay their own invoices
        if (auth()->user()->hasRole('student') && $invoice->student_id !== auth()->id()) {
            abort(403);
        }

        if ($invoice->status === 'paid') {
            return back()->with('error', 'This invoice is already fully paid.');
        }

        $amountDue = (float) $invoice->amount_due;
        if ($amountDue <= 0) {
            return back()->with('error', 'No outstanding amount on this invoice.');
        }

        try {
            $gateway = $this->gateways->driver($data['gateway']);
            $result  = $gateway->createPayment($amountDue, 'NPR', [
                'invoice_id'   => $invoice->id,
                'invoice_name' => $invoice->title,
                'student_id'   => $invoice->student_id,
            ]);

            // Store pidx/intent ID in session for callback verification
            session(["payment_intent_{$invoice->id}" => $result['id']]);

            if (! empty($result['redirect_url'])) {
                return redirect()->away($result['redirect_url']);
            }

            // For Stripe Elements (no redirect), return client_secret via JSON
            return response()->json([
                'client_secret' => $result['client_secret'] ?? null,
                'gateway'       => $data['gateway'],
            ]);

        } catch (\Throwable $e) {
            Log::error("OnlinePayment initiate failed for invoice #{$invoice->id}: " . $e->getMessage());
            return back()->with('error', 'Payment initiation failed: ' . $e->getMessage());
        }
    }

    // ── Step 2a: eSewa success callback ──────────────────────────────────────

    public function esewaSuccess(Request $request)
    {
        try {
            $gateway = $this->gateways->driver('esewa');
            $result  = $gateway->verifyPayment($request->all());

            if (! $result['success']) {
                return redirect()->route('payments.index')
                    ->with('error', 'eSewa payment could not be verified. Please contact support.');
            }

            return $this->finalisePayment(
                invoiceId:     $result['metadata']['product_code'] ?? null,
                amount:        $result['amount'],
                gateway:       'esewa',
                transactionId: $result['transaction_id'],
            );
        } catch (\Throwable $e) {
            Log::error('eSewa success callback error: ' . $e->getMessage());
            return redirect()->route('payments.index')->with('error', 'eSewa callback error.');
        }
    }

    public function esewaFailure(Request $request)
    {
        Log::warning('eSewa payment failure', $request->all());
        return redirect()->route('payments.index')
            ->with('error', 'eSewa payment was not completed. Please try again.');
    }

    // ── Step 2b: Khalti callback ──────────────────────────────────────────────

    public function khaltiCallback(Request $request)
    {
        // Khalti sends pidx in the callback URL
        if ($request->input('status') === 'Completed') {
            try {
                $gateway = $this->gateways->driver('khalti');
                $result  = $gateway->verifyPayment($request->all());

                if (! $result['success']) {
                    return redirect()->route('payments.index')
                        ->with('error', 'Khalti payment verification failed.');
                }

                $invoiceId = $result['metadata']['purchase_order_id'] ?? null;

                return $this->finalisePayment(
                    invoiceId:     $invoiceId,
                    amount:        $result['amount'],
                    gateway:       'khalti',
                    transactionId: $result['transaction_id'],
                );
            } catch (\Throwable $e) {
                Log::error('Khalti callback error: ' . $e->getMessage());
                return redirect()->route('payments.index')->with('error', 'Khalti callback error.');
            }
        }

        return redirect()->route('payments.index')
            ->with('error', 'Khalti payment was cancelled or failed.');
    }

    // ── Shared: record payment and redirect ───────────────────────────────────

    private function finalisePayment(
        mixed  $invoiceId,
        float  $amount,
        string $gateway,
        string $transactionId,
    ) {
        if (! $invoiceId) {
            Log::error("finalisePayment: no invoice_id in callback");
            return redirect()->route('payments.index')->with('error', 'Could not identify invoice. Contact support.');
        }

        $invoice = Invoice::find($invoiceId);
        if (! $invoice) {
            Log::error("finalisePayment: invoice #{$invoiceId} not found");
            return redirect()->route('payments.index')->with('error', 'Invoice not found.');
        }

        // Idempotency: don't double-record the same transaction
        if (Payment::where('gateway_transaction_id', $transactionId)->exists()) {
            return redirect()->route('payments.show', $invoice->id)
                ->with('status', 'Payment already recorded.');
        }

        DB::transaction(function () use ($invoice, $amount, $gateway, $transactionId) {
            Payment::create([
                'invoice_id'             => $invoice->id,
                'amount'                 => $amount,
                'payment_date'           => today()->toDateString(),
                'payment_method'         => $gateway,
                'gateway'                => $gateway,
                'gateway_transaction_id' => $transactionId,
                'notes'                  => ucfirst($gateway) . ' online payment',
                'recorded_by'            => auth()->id(),
            ]);
        });

        return redirect()->route('payments.show', $invoice->id)
            ->with('status', 'Payment of Rs. ' . number_format($amount, 2) . ' received via ' . ucfirst($gateway) . '.');
    }
}
