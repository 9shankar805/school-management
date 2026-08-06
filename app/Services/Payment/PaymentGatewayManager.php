<?php

namespace App\Services\Payment;

/**
 * Resolves the correct gateway adapter based on config or runtime selection.
 *
 * Usage (NepalSchoolHub default amounts are in NPR / Rs.):
 *   $gateway = app(PaymentGatewayManager::class)->driver('stripe');
 *   $result  = $gateway->createPayment(15000.00, 'NPR', ['invoice_id' => 42]);
 */
class PaymentGatewayManager
{
    private array $drivers = [
        'stripe'       => StripeGateway::class,
        'paypal'       => PayPalGateway::class,
        'razorpay'     => RazorpayGateway::class,
        'flutterwave'  => FlutterwaveGateway::class,
        'khalti'       => KhaltiGateway::class,
        'esewa'        => ESewaGateway::class,
    ];

    public function driver(?string $name = null): PaymentGatewayInterface
    {
        $name = $name ?? config('services.payment_gateway.default', 'stripe');

        if (! isset($this->drivers[$name])) {
            throw new \InvalidArgumentException("Unknown payment gateway: {$name}");
        }

        return app($this->drivers[$name]);
    }

    /** List all registered gateway slugs */
    public function available(): array
    {
        return array_keys($this->drivers);
    }
}
