<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Flutterwave payment gateway adapter.
 *
 * Configure in .env:
 *   FLUTTERWAVE_PUBLIC_KEY=FLWPUBK_TEST-…
 *   FLUTTERWAVE_SECRET_KEY=FLWSECK_TEST-…
 *   FLUTTERWAVE_ENCRYPTION_KEY=…
 *   FLUTTERWAVE_WEBHOOK_SECRET=…
 */
class FlutterwaveGateway implements PaymentGatewayInterface
{
    private string $secretKey;
    private string $publicKey;
    private string $webhookSecret;
    private string $baseUrl = 'https://api.flutterwave.com/v3';

    public function __construct()
    {
        $this->secretKey     = config('services.flutterwave.secret_key', '');
        $this->publicKey     = config('services.flutterwave.public_key', '');
        $this->webhookSecret = config('services.flutterwave.webhook_secret', '');
    }

    public function getGatewayName(): string
    {
        return 'flutterwave';
    }

    public function createPayment(float $amount, string $currency, array $metadata = []): array
    {
        $txRef = 'SCH-' . uniqid();

        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/payments", [
                'tx_ref'       => $txRef,
                'amount'       => $amount,
                'currency'     => strtoupper($currency),
                'redirect_url' => route('finance.flutterwave.callback'),
                'meta'         => $metadata,
                'customer'     => [
                    'email' => $metadata['email'] ?? 'student@school.edu',
                    'name'  => $metadata['name']  ?? 'Student',
                ],
                'customizations' => [
                    'title'       => 'School Fee Payment',
                    'description' => 'Invoice #' . ($metadata['invoice_id'] ?? ''),
                ],
            ]);

        if ($response->failed()) {
            Log::error('Flutterwave createPayment failed', $response->json());
            throw new \RuntimeException($response->json('message') ?? 'Flutterwave error');
        }

        $data = $response->json();
        return [
            'id'           => $txRef,
            'redirect_url' => $data['data']['link'] ?? '',
            'status'       => $data['status'],
        ];
    }

    public function verifyPayment(array $payload): array
    {
        // Validate webhook hash
        $hashHeader = request()->header('verif-hash', '');
        if ($this->webhookSecret && $hashHeader !== $this->webhookSecret) {
            return ['success' => false, 'transaction_id' => '', 'amount' => 0, 'metadata' => []];
        }

        $transactionId = $payload['data']['id'] ?? $payload['transaction_id'] ?? null;
        if (! $transactionId) {
            return ['success' => false, 'transaction_id' => '', 'amount' => 0, 'metadata' => []];
        }

        try {
            $response = Http::withToken($this->secretKey)
                ->get("{$this->baseUrl}/transactions/{$transactionId}/verify");

            $data = $response->json('data') ?? [];
            return [
                'success'        => ($data['status'] ?? '') === 'successful',
                'transaction_id' => (string) ($data['id'] ?? ''),
                'amount'         => (float) ($data['amount'] ?? 0),
                'metadata'       => $data['meta'] ?? [],
            ];
        } catch (\Throwable $e) {
            Log::error('Flutterwave verifyPayment exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'transaction_id' => '', 'amount' => 0, 'metadata' => []];
        }
    }

    public function refund(string $transactionId, float $amount): array
    {
        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/transactions/{$transactionId}/refund", [
                'amount' => $amount,
            ]);

        return [
            'success'   => $response->ok(),
            'refund_id' => $response->json('data.id') ?? '',
        ];
    }
}
