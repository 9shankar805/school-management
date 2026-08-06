<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * eSewa v2 Nepal Payment Gateway adapter.
 *
 * Uses eSewa ePay v2 API directly (no SDK dependency).
 * Configure in .env:
 *   ESEWA_SECRET_KEY=your_merchant_secret_key
 *   ESEWA_SUCCESS_URL=https://your-app.com/payment/esewa/success
 *   ESEWA_FAILURE_URL=https://your-app.com/payment/esewa/failure
 */
class ESewaGateway implements PaymentGatewayInterface
{
    private string $secretKey;
    private string $successUrl;
    private string $failureUrl;
    private string $initiateUrl = 'https://epay.esewa.com.np/api/epay/main/v2/form';
    private string $refundUrl   = 'https://epay.esewa.com.np/api/epay/transaction/refund';

    public function __construct()
    {
        $this->secretKey  = config('services.esewa.secret_key', '');
        $this->successUrl = config('services.esewa.success_url', '');
        $this->failureUrl = config('services.esewa.failure_url', '');
    }

    public function getGatewayName(): string
    {
        return 'esewa';
    }

    public function createPayment(float $amount, string $currency, array $metadata = []): array
    {
        $transactionUuid  = (string) Str::uuid();
        $productCode      = $metadata['invoice_id'] ?? $metadata['order_id'] ?? 'FEE';
        $totalAmount      = number_format($amount, 2, '.', '');
        $deliveryCharge   = 0;
        $serviceCharge    = 0;

        $signedFieldNames = 'total_amount,transaction_uuid,product_code';
        $message          = "total_amount={$totalAmount},transaction_uuid={$transactionUuid},product_code={$productCode}";
        $signature        = base64_encode(hash_hmac('sha256', $message, $this->secretKey, true));

        try {
            $params = [
                'amount'                   => $totalAmount,
                'total_amount'             => $totalAmount,
                'transaction_uuid'         => $transactionUuid,
                'product_code'             => (string) $productCode,
                'product_delivery_charge'  => $deliveryCharge,
                'product_service_charge'   => $serviceCharge,
                'success_url'              => $this->successUrl,
                'failure_url'              => $this->failureUrl,
                'signed_field_names'       => $signedFieldNames,
                'signature'                => $signature,
            ];

            $redirectUrl = $this->initiateUrl . '?' . http_build_query($params);

            return [
                'id'           => $transactionUuid,
                'redirect_url' => $redirectUrl,
                'status'       => 'initiated',
            ];
        } catch (\Throwable $e) {
            Log::error('ESewa createPayment exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    public function verifyPayment(array $payload): array
    {
        $encodedData = $payload['data'] ?? '';

        if (empty($encodedData)) {
            return ['success' => false, 'transaction_id' => '', 'amount' => 0, 'metadata' => []];
        }

        try {
            $decodedJson = base64_decode($encodedData, true);

            if ($decodedJson === false) {
                Log::error('ESewa verifyPayment: failed to base64 decode data');
                return ['success' => false, 'transaction_id' => '', 'amount' => 0, 'metadata' => []];
            }

            $data = json_decode($decodedJson, true);

            if (! is_array($data)) {
                Log::error('ESewa verifyPayment: decoded data is not valid JSON');
                return ['success' => false, 'transaction_id' => '', 'amount' => 0, 'metadata' => []];
            }

            $success = ($data['status'] ?? '') === 'COMPLETE';

            return [
                'success'        => $success,
                'transaction_id' => $data['reference_id'] ?? $data['transaction_code'] ?? '',
                'amount'         => (float) ($data['total_amount'] ?? $data['amount'] ?? 0),
                'metadata'       => [
                    'transaction_uuid' => $data['transaction_uuid'] ?? '',
                    'product_code'     => $data['product_code'] ?? '',
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('ESewa verifyPayment exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'transaction_id' => '', 'amount' => 0, 'metadata' => []];
        }
    }

    public function refund(string $transactionId, float $amount): array
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->refundUrl, [
                'reference_id' => $transactionId,
                'amount'       => $amount,
                'secret_key'   => $this->secretKey,
            ]);

            $data = $response->json();

            if ($response->failed()) {
                Log::error('ESewa refund failed', $data);
            }

            return [
                'success'   => $response->ok(),
                'refund_id' => $data['refund_id'] ?? $data['transaction_code'] ?? '',
            ];
        } catch (\Throwable $e) {
            Log::error('ESewa refund exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'refund_id' => ''];
        }
    }
}
