<?php

namespace App\Services\Payment;

/**
 * Contract every payment gateway adapter must implement.
 */
interface PaymentGatewayInterface
{
    /**
     * Create a payment intent / session.
     *
     * @param  float  $amount     Amount in the currency's base unit (Nepal default: NPR rupees)
     * @param  string $currency   ISO 4217 currency code (default "NPR" for NepalSchoolHub)
     * @param  array  $metadata   Extra data to attach (student_id, invoice_id, etc.)
     * @return array{id: string, redirect_url: string, status: string}
     */
    public function createPayment(float $amount, string $currency, array $metadata = []): array;

    /**
     * Verify a callback / webhook payload and return normalised result.
     *
     * @param  array  $payload  Raw POST payload or decoded JSON
     * @return array{success: bool, transaction_id: string, amount: float, metadata: array}
     */
    public function verifyPayment(array $payload): array;

    /**
     * Process a refund for an existing transaction.
     *
     * @param  string $transactionId  Gateway transaction / payment-intent ID
     * @param  float  $amount         Amount to refund (partial refunds supported)
     * @return array{success: bool, refund_id: string}
     */
    public function refund(string $transactionId, float $amount): array;

    /** Gateway identifier slug (e.g. "stripe", "paypal", "razorpay") */
    public function getGatewayName(): string;
}
