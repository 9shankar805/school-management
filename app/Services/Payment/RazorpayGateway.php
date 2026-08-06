<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Razorpay payment gateway adapter.
 *
 * Configure in .env:
 *   RAZORPAY_KEY_ID=rzp_live_…
 *   RAZORPAY_KEY_SECRET=…
 *   RAZORPAY_WEBHOOK_SECRET=…
 */
class RazorpayGateway implements PaymentGatewayInterface
{
    private string $keyId;
    private string $keySecret;
    private string $webhookSecret;
    private string $baseUrl = 'https://api.razorpay.com/v1';

    public function __construct()
    {
        $this->keyId         = config('services.razorpay.key_id', '');
        $this->keySecret     = config('services.razorpay.key_secret', '');
        $this->webhookSecret = config('services.razorpay.webhook_secret', '');
    }

    public function getGatewayName(): string
    {
        return 'razorpay';
    }

    public function createPayment(float $amount, string $currency, array $metadata = []): array
    {
        // Razorpay amounts are in smallest unit (paise for INR)
        $amountUnit = (int) round($amount * 100);

        $response = Http::withBasicAuth($this->keyId, $this->keySecret)
            ->post("{$this->baseUrl}/orders", [
                'amount'          => $amountUnit,
                'currency'        => strtoupper($currency),
                'notes'           => $metadata,
                'receipt'         => 'rcpt_' . uniqid(),
            ]);

        if ($response->failed()) {
            Log::error('Razorpay createPayment failed', $response->json());
            throw new \RuntimeException($response->json('error.description') ?? 'Razorpay error');
        }

        $data = $response->json();
        return [
            'id'           => $data['id'],
            'redirect_url' => '', // Razorpay uses a JS checkout widget
            'status'       => $data['status'],
            'key_id'       => $this->keyId, // needed by front-end
        ];
    }

    public function verifyPayment(array $payload): array
    {
        // Verify HMAC SHA256 signature
        $orderId   = $payload['razorpay_order_id']   ?? '';
        $paymentId = $payload['razorpay_payment_id'] ?? '';
        $signature = $payload['razorpay_signature']  ?? '';

        $expectedSig = hash_hmac('sha256', "{$orderId}|{$paymentId}", $this->keySecret);

        if (! hash_equals($expectedSig, $signature)) {
            return ['success' => false, 'transaction_id' => '', 'amount' => 0, 'metadata' => []];
        }

        // Fetch payment details
        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->get("{$this->baseUrl}/payments/{$paymentId}");

            $data = $response->json();
            return [
                'success'        => ($data['status'] ?? '') === 'captured',
                'transaction_id' => $paymentId,
                'amount'         => ($data['amount'] ?? 0) / 100,
                'metadata'       => $data['notes'] ?? [],
            ];
        } catch (\Throwable $e) {
            Log::error('Razorpay verifyPayment exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'transaction_id' => '', 'amount' => 0, 'metadata' => []];
        }
    }

    public function refund(string $transactionId, float $amount): array
    {
        $amountUnit = (int) round($amount * 100);

        $response = Http::withBasicAuth($this->keyId, $this->keySecret)
            ->post("{$this->baseUrl}/payments/{$transactionId}/refund", [
                'amount' => $amountUnit,
            ]);

        return [
            'success'   => $response->ok(),
            'refund_id' => $response->json('id') ?? '',
        ];
    }
}
