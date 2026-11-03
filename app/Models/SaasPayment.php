<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaasPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'saas_payments';

    protected $fillable = [
        'saas_invoice_id',
        'payment_number',
        'payment_date',
        'payment_date_bs',
        'amount',
        'payment_method',
        'transaction_reference',
        'bank_name',
        'note',
        'received_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SaasInvoice::class, 'saas_invoice_id');
    }

    public static function generatePaymentNumber(): string
    {
        $year = date('Y');
        $count = static::withTrashed()->whereYear('created_at', $year)->count() + 1;
        return sprintf('NSH-PAY-%s-%03d', $year, $count);
    }
}

