<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * PayPal REST API gateway adapter.
 *
 * ─── NepalSchoolHub (NPR) Notes ───────────────────────────────────────────
 * PayPal does not officially support the Nepali Rupee (NPR) as a settlement
 * currency. All UI displays and database amounts use NPR / Rs. / रू,
 * but PayPal API calls here submit in USD (PayPal's ISO-4217 requirement)
 * for international/credit-card cross-border flows.
 * For domestic Nepal collections prefer eSewa / Khalti / FonePay / IME Pay /
 * ConnectIPS gateways or direct bank transfers instead.
 *
 * Configure in .env:
 *   PAYPAL_CLIENT_ID=…
 *   PAYPAL_CLIENT_SECRET=…
 *   PAYPAL_MODE=sandbox   # or "live"
 */
class PayPalGateway implements PaymentGatewayInterface
{
    private string $clientId;
    private string $clientSecret;
    private string $baseUrl;

    public function __construct()
    {
        $this->clientId     = config('services.paypal.client_id', '');
        $this->clientSecret = config('services.paypal.client_secret', '');
        $mode               = config('services.paypal.mode', 'sandbox');
        $this->baseUrl      = $mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    public function getGatewayName(): string
    {
        return 'paypal';
    }

    /** Fetch (or cache) an OAuth2 access token. */
    private function getAccessToken(): string
    {
        return Cache::remember('paypal_access_token', 3000, function () {
            $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
                ->asForm()
                ->post("{$this->baseUrl}/v1/oauth2/token", ['grant_type' => 'client_credentials']);

            if ($response->failed()) {
                throw new \RuntimeException('PayPal authentication failed');
            }
            return $response->json('access_token');
        });
    }

    public function createPayment(float $amount, string $currency, array $metadata = []): array
    {
        $token = $this->getAccessToken();
        $returnUrl = route('finance.paypal.return');
        $cancelUrl = route('finance.paypal.cancel');

        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/v2/checkout/orders", [
                'intent'         => 'CAPTURE',
                'purchase_units' => [[
                    'amount'      => ['currency_code' => strtoupper($currency), 'value' => number_format($amount, 2, '.', '')],
                    'custom_id'   => json_encode($metadata),
                ]],
                'application_context' => [
                    'return_url' => $returnUrl,
                    'cancel_url' => $cancelUrl,
                ],
            ]);

        if ($response->failed()) {
            Log::error('PayPal createPayment failed', $response->json());
            throw new \RuntimeException($response->json('message') ?? 'PayPal error');
        }

        $data        = $response->json();
        $approveLink = collect($data['links'] ?? [])->firstWhere('rel', 'approve');

        return [
            'id'           => $data['id'],
            'redirect_url' => $approveLink['href'] ?? '',
            'status'       => $data['status'],
        ];
    }

    public function verifyPayment(array $payload): array
    {
        $orderId = $payload['token'] ?? $payload['orderID'] ?? '';
        if (! $orderId) {
            return ['success' => false, 'transaction_id' => '', 'amount' => 0, 'metadata' => []];
        }

        try {
            $token    = $this->getAccessToken();
            $response = Http::withToken($token)
                ->post("{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture");

            $data = $response->json();
            $capture = $data['purchase_units'][0]['payments']['captures'][0] ?? [];

            return [
                'success'        => ($data['status'] ?? '') === 'COMPLETED',
                'transaction_id' => $capture['id'] ?? $orderId,
                'amount'         => (float) ($capture['amount']['value'] ?? 0),
                'metadata'       => json_decode($data['purchase_units'][0]['custom_id'] ?? '{}', true) ?? [],
            ];
        } catch (\Throwable $e) {
            Log::error('PayPal verifyPayment exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'transaction_id' => '', 'amount' => 0, 'metadata' => []];
        }
    }

    public function refund(string $transactionId, float $amount): array
    {
        $token    = $this->getAccessToken();
        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/v2/payments/captures/{$transactionId}/refund", [
                'amount' => ['value' => number_format($amount, 2, '.', ''), 'currency_code' => 'USD'],
            ]);

        return [
            'success'   => $response->ok(),
            'refund_id' => $response->json('id') ?? '',
        ];
    }
}
