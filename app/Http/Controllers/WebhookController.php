<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Payment\PaymentGatewayManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Handles incoming payment gateway webhooks.
 * These routes are excluded from CSRF (add to VerifyCsrfToken $except).
 */
class WebhookController extends Controller
{
    public function __construct(private PaymentGatewayManager $gateways) {}

    public function stripe(Request $request): Response
    {
        return $this->process('stripe', $request->all());
    }

    public function paypal(Request $request): Response
    {
        return $this->process('paypal', $request->all());
    }

    public function razorpay(Request $request): Response
    {
        return $this->process('razorpay', $request->all());
    }

    public function flutterwave(Request $request): Response
    {
        return $this->process('flutterwave', $request->all());
    }

    public function esewa(Request $request): Response
    {
        return $this->process('esewa', $request->all());
    }

    public function khalti(Request $request): Response
    {
        return $this->process('khalti', $request->all());
    }

    private function process(string $driver, array $payload): Response
    {
        try {
            $gateway = $this->gateways->driver($driver);
            $result  = $gateway->verifyPayment($payload);

            if (! $result['success']) {
                Log::warning("{$driver} webhook: verification failed", $payload);
                return response('Verification failed', 400);
            }

            $meta      = $result['metadata'];
            $invoiceId = $meta['invoice_id'] ?? null;

            if ($invoiceId) {
                $invoice = Invoice::find($invoiceId);
                if ($invoice && $invoice->status !== 'paid') {
                    // Record payment
                    Payment::create([
                        'invoice_id'     => $invoice->id,
                        'amount'         => $result['amount'],
                        'payment_date'   => today()->toDateString(),
                        'payment_method' => $driver,
                        'gateway'        => $driver,
                    ]);

                    // Mark invoice paid if fully settled
                    $totalPaid = $invoice->payments()->sum('amount');
                    if ($totalPaid >= $invoice->amount) {
                        $invoice->update(['status' => 'paid']);
                    } else {
                        $invoice->update(['status' => 'partial']);
                    }
                }
            }

            Log::info("{$driver} webhook processed", $result);
            return response('OK', 200);
        } catch (\Throwable $e) {
            Log::error("{$driver} webhook exception", ['message' => $e->getMessage()]);
            return response('Error', 500);
        }
    }
}
