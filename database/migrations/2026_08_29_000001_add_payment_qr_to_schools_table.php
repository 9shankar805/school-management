<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add payment QR code image columns to schools table.
 *
 * Principals upload a QR image (eSewa / Khalti / bank / FonePay) via
 * School Profile → Payment QR section. When parents view an invoice
 * they see the QR to scan and pay directly on their phone — no gateway
 * integration or merchant credentials required.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->string('payment_qr_esewa',  500)->nullable()->after('youtube_url')
                  ->comment('eSewa QR image path (storage/public)');
            $table->string('payment_qr_khalti', 500)->nullable()->after('payment_qr_esewa')
                  ->comment('Khalti QR image path');
            $table->string('payment_qr_bank',   500)->nullable()->after('payment_qr_khalti')
                  ->comment('Bank / Connect IPS QR image path');
            $table->string('payment_qr_fonepay',500)->nullable()->after('payment_qr_bank')
                  ->comment('FonePay QR image path');
            $table->text('payment_instructions')->nullable()->after('payment_qr_fonepay')
                  ->comment('Optional instruction text shown below QR on invoice');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'payment_qr_esewa',
                'payment_qr_khalti',
                'payment_qr_bank',
                'payment_qr_fonepay',
                'payment_instructions',
            ]);
        });
    }
};
