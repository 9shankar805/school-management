<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Stripe Payment Gateway adapter.
 *
 * Uses Stripe's REST API directly (no SDK dependency).
 * Configure in .env:
 *   STRIPE_KEY=pk_live_…
 *   STRIPE_SECRET=sk_live_…
 *   STRIPE_WEBHOOK_SECRET=whsec_…
 */
class StripeGateway implements PaymentGatewayInterface
{
    private string $secretKey;
    private string $webhookSecret;
    private string $baseUrl = 'https://api.stripe.com/v1';

    public function __construct()
    {
        $this->secretKey     = config('services.stripe.secret', '');
        $this->webhookSecret = config('services.stripe.webhook_secret', '');
    }

    public function getGatewayName(): string
    {
        return 'stripe';
    }

    public function createPayment(float $amount, string $currency, array $metadata = []): array
    {
        // Stripe amounts are in smallest currency unit (cents)
        $amountCents = (int) round($amount * 100);

        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->asForm()
                ->post("{$this->baseUrl}/payment_intents", [
                    'amount'               => $amountCents,
                    'currency'             => strtolower($currency),
                    'metadata'             => $metadata,
                    'automatic_payment_methods[enabled]' => 'true',
                ]);

            $data = $response->json();

            if ($response->failed()) {
                Log::error('Stripe createPayment failed', $data);
                throw new \RuntimeException($data['error']['message'] ?? 'Stripe error');
            }

            return [
                'id'           => $data['id'],
                'client_secret'=> $data['client_secret'],
                'redirect_url' => '', // Stripe uses Elements — no redirect URL needed
                'status'       => $data['status'],
            ];
        } catch (\Throwable $e) {
            Log::error('Stripe createPayment exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    public function verifyPayment(array $payload): array
    {
        // Validate Stripe webhook signature
        $sig     = request()->header('Stripe-Signature', '');
        $rawBody = request()->getContent();

        try {
            // Manual HMAC verification without Stripe SDK
            $parts     = [];
            foreach (explode(',', $sig) as $part) {
                [$k, $v] = explode('=', $part, 2);
                $parts[$k][] = $v;
            }
            $timestamp = $parts['t'][0] ?? 0;
            $expectedSig = hash_hmac('sha256', "{$timestamp}.{$rawBody}", $this->webhookSecret);

            if (! in_array($expectedSig, $parts['v1'] ?? [], true)) {
                return ['success' => false, 'transaction_id' => '', 'amount' => 0, 'metadata' => []];
            }
        } catch (\Throwable) {
            return ['success' => false, 'transaction_id' => '', 'amount' => 0, 'metadata' => []];
        }

        $intent = $payload['data']['object'] ?? [];
        $success = ($payload['type'] ?? '') === 'payment_intent.succeeded';

        return [
            'success'        => $success,
            'transaction_id' => $intent['id'] ?? '',
            'amount'         => ($intent['amount'] ?? 0) / 100,
            'metadata'       => $intent['metadata'] ?? [],
        ];
    }

    public function refund(string $transactionId, float $amount): array
    {
        $amountCents = (int) round($amount * 100);

        $response = Http::withBasicAuth($this->secretKey, '')
            ->asForm()
            ->post("{$this->baseUrl}/refunds", [
                'payment_intent' => $transactionId,
                'amount'         => $amountCents,
            ]);

        $data = $response->json();

        return [
            'success'   => $response->ok(),
            'refund_id' => $data['id'] ?? '',
        ];
    }
}
