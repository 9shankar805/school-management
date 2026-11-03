<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaasService extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'saas_services';

    protected $fillable = [
        'name',
        'name_np',
        'code',
        'category',
        'default_price',
        'billing_basis',
        'unit_name',
        'description',
        'specifications',
        'is_active',
        'is_featured',
        'created_by',
    ];

    protected $casts = [
        'default_price' => 'decimal:2',
        'is_active'     => 'boolean',
        'is_featured'   => 'boolean',
    ];

    public function agreementItems(): HasMany
    {
        return $this->hasMany(SaasAgreementItem::class, 'service_id');
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(SaasInvoiceItem::class, 'service_id');
    }

    /**
     * Categories helper
     */
    public static function categories(): array
    {
        return [
            'hardware' => 'हार्डवेयर तथा उपकरण (Hardware & Devices)',
            'software' => 'सफ्टवेयर तथा डिजिटल प्रणाली (Software & Digital)',
            'service'  => 'सेवा तथा प्राविधिक सहयोग (Service & Support)',
            'setup'    => 'स्थापना तथा जडान (Setup & Installation)',
            'package'  => 'कम्बो प्याकेज (Combo Package)',
            'other'    => 'अन्य (Other)',
        ];
    }

    /**
     * Billing basis helper
     */
    public static function billingBases(): array
    {
        return [
            'one_time' => 'एकपटक (One-Time)',
            'annual'   => 'प्रतिवर्ष (Annual / Yearly)',
            'monthly'  => 'प्रतिमहिना (Monthly)',
            'per_unit' => 'प्रति थान/प्रति विद्यार्थी (Per Unit)',
        ];
    }
}

