<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaasAgreement extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'saas_agreements';

    protected $fillable = [
        'agreement_number',
        'school_id',
        'status',
        'agreement_date_bs',
        'bs_year',
        'bs_month',
        'bs_day',
        'effective_date',
        'expiry_date',
        'duration_years',
        // Provider
        'provider_name',
        'provider_reg_no',
        'provider_pan_vat',
        'provider_address',
        'provider_phone',
        'provider_email',
        'provider_website',
        'provider_representative',
        'provider_designation',
        // School
        'school_name',
        'school_address',
        'school_reg_no',
        'school_pan_no',
        'school_representative',
        'school_designation',
        'school_phone',
        'school_email',
        // Standard Fees
        'setup_fee',
        'setup_fee_basis',
        'renewal_fee',
        'renewal_fee_basis',
        'software_inclusion_text',
        // Totals
        'additional_services_total',
        'grand_total',
        'notes',
        // Witnesses
        'witness_1_name',
        'witness_1_date',
        'witness_2_name',
        'witness_2_date',
        'created_by',
    ];

    protected $casts = [
        'effective_date'            => 'date',
        'expiry_date'               => 'date',
        'setup_fee'                 => 'decimal:2',
        'renewal_fee'               => 'decimal:2',
        'additional_services_total' => 'decimal:2',
        'grand_total'               => 'decimal:2',
        'duration_years'            => 'integer',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaasAgreementItem::class, 'agreement_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SaasInvoice::class, 'agreement_id');
    }

    /**
     * Generate unique agreement number
     */
    public static function generateAgreementNumber(): string
    {
        $year = date('Y');
        $count = static::withTrashed()->whereYear('created_at', $year)->count() + 1;
        return sprintf('NSH-AGR-%s-%03d', $year, $count);
    }

    /**
     * Convert English digits to Nepali digits
     */
    public static function toNepaliDigits($number): string
    {
        $en = ['0','1','2','3','4','5','6','7','8','9'];
        $np = ['०','१','२','३','४','५','६','७','८','९'];
        return str_replace($en, $np, (string)$number);
    }

    /**
     * Format amount in Nepali currency style with Nepali digits: रु. १५,०००/-
     */
    public static function formatNepaliCurrency($amount): string
    {
        $formatted = number_format((float)$amount, 0);
        return 'रु. ' . self::toNepaliDigits($formatted) . '/-';
    }
}

