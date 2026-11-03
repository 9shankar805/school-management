<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaasInvoiceItem extends Model
{
    use HasFactory;

    protected $table = 'saas_invoice_items';

    protected $fillable = [
        'saas_invoice_id',
        'service_id',
        'title',
        'description',
        'quantity',
        'unit_price',
        'total_amount',
    ];

    protected $casts = [
        'quantity'     => 'decimal:2',
        'unit_price'   => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SaasInvoice::class, 'saas_invoice_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(SaasService::class, 'service_id');
    }
}

