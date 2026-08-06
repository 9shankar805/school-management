<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Khalti v2 Digital Payment Nepal Gateway adapter.
 *
 * Uses Khalti ePayment API directly (no SDK dependency).
 * Configure in .env:
 *   KHALTI_SECRET_KEY=live_secret_key_…
 *   KHALTI_RETURN_URL=https://your-app.com/payment/khalti/callback
 *   KHALTI_WEBSITE_URL=https://your-app.com
 */
class KhaltiGateway implements PaymentGatewayInterface
{
    private string $secretKey;
    private string $returnUrl;
    private string $websiteUrl;
    private string $baseUrl = 'https://khalti.com/api/v2/epayment';

    public function __construct()
    {
        $this->secretKey  = config('services.khalti.secret_key', '');
        $this->returnUrl  = config('services.khalti.return_url', '');
        $this->websiteUrl = config('services.khalti.website_url', '');
    }

    public function getGatewayName(): string
    {
        return 'khalti';
    }

    public function createPayment(float $amount, string $currency, array $metadata = []): array
    {
        if (strtoupper($currency) !== 'NPR') {
            throw new \InvalidArgumentException('Khalti gateway only supports NPR currency.');
        }

        $amountPaise = (int) round($amount * 100);
        $purchaseOrderId   = $metadata['invoice_id'] ?? $metadata['order_id'] ?? (string) Str::uuid();
        $purchaseOrderName = $metadata['invoice_name'] ?? $metadata['description'] ?? 'Payment';

        try {
            $response = Http::withHeaders([
                'Authorization' => "Key {$this->secretKey}",
                'Content-Type'  => 'application/json',
            ])->post("{$this->baseUrl}/initiate", [
                'return_url'         => $this->returnUrl,
                'website_url'        => $this->websiteUrl,
                'amount'             => $amountPaise,
                'purchase_order_id'  => (string) $purchaseOrderId,
                'purchase_order_name'=> $purchaseOrderName,
            ]);

            $data = $response->json();

            if ($response->failed()) {
                Log::error('Khalti createPayment failed', $data);
                throw new \RuntimeException($data['detail'] ?? $data['error_message'] ?? 'Khalti error');
            }

            $pidx = $data['pidx'];

            return [
                'id'           => $pidx,
                'redirect_url' => "https://khalti.com/epayment/#/pay/{$pidx}",
                'status'       => 'initiated',
            ];
        } catch (\Throwable $e) {
            Log::error('Khalti createPayment exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    public function verifyPayment(array $payload): array
    {
        $pidx = $payload['pidx'] ?? '';

        if (empty($pidx)) {
            return ['success' => false, 'transaction_id' => '', 'amount' => 0, 'metadata' => []];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Key {$this->secretKey}",
                'Content-Type'  => 'application/json',
            ])->post("{$this->baseUrl}/lookup/", [
                'pidx' => $pidx,
            ]);

            $data = $response->json();

            if ($response->failed()) {
                Log::error('Khalti verifyPayment failed', $data);
                return ['success' => false, 'transaction_id' => '', 'amount' => 0, 'metadata' => []];
            }

            $success = ($data['status'] ?? '') === 'Completed';

            return [
                'success'        => $success,
                'transaction_id' => $data['txn_id'] ?? '',
                'amount'         => ($data['amount'] ?? 0) / 100,
                'metadata'       => [
                    'pidx'                => $data['pidx'] ?? '',
                    'purchase_order_id'   => $data['purchase_order_id'] ?? '',
                    'purchase_order_name' => $data['purchase_order_name'] ?? '',
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('Khalti verifyPayment exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'transaction_id' => '', 'amount' => 0, 'metadata' => []];
        }
    }

    public function refund(string $transactionId, float $amount): array
    {
        $amountPaise = (int) round($amount * 100);

        try {
            $response = Http::withHeaders([
                'Authorization' => "Key {$this->secretKey}",
                'Content-Type'  => 'application/json',
            ])->post("{$this->baseUrl}/refund/", [
                'transaction_id' => $transactionId,
                'amount'         => $amountPaise,
            ]);

            $data = $response->json();

            if ($response->failed()) {
                Log::error('Khalti refund failed', $data);
            }

            return [
                'success'   => $response->ok(),
                'refund_id' => $data['refund_id'] ?? $data['idx'] ?? '',
            ];
        } catch (\Throwable $e) {
            Log::error('Khalti refund exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'refund_id' => ''];
        }
    }
}
