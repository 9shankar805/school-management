<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaasAgreementItem extends Model
{
    use HasFactory;

    protected $table = 'saas_agreement_items';

    protected $fillable = [
        'agreement_id',
        'service_id',
        'item_name',
        'unit_price',
        'quantity',
        'total_price',
        'basis',
        'remarks',
    ];

    protected $casts = [
        'unit_price'  => 'decimal:2',
        'total_price' => 'decimal:2',
        'quantity'    => 'integer',
    ];

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(SaasAgreement::class, 'agreement_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(SaasService::class, 'service_id');
    }
}

