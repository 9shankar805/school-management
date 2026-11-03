<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaasInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'saas_invoices';

    protected $fillable = [
        'invoice_number',
        'school_id',
        'agreement_id',
        'school_name',
        'school_pan',
        'school_address',
        'school_email',
        'school_phone',
        'invoice_date',
        'due_date',
        'invoice_date_bs',
        'due_date_bs',
        'subtotal',
        'discount_amount',
        'tax_percent',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'due_amount',
        'status',
        'notes',
        'terms',
        'created_by',
    ];

    protected $casts = [
        'invoice_date'    => 'date',
        'due_date'        => 'date',
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percent'     => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'total_amount'    => 'decimal:2',
        'paid_amount'     => 'decimal:2',
        'due_amount'      => 'decimal:2',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(SaasAgreement::class, 'agreement_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaasInvoiceItem::class, 'saas_invoice_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SaasPayment::class, 'saas_invoice_id');
    }

    public static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $count = static::withTrashed()->whereYear('created_at', $year)->count() + 1;
        return sprintf('TPC-%s-%04d', $year, $count);
    }

    /**
     * Get Company PAN digits array for the 9-box print layout
     */
    public static function getPanDigits(?string $pan = null): array
    {
        $clean = preg_replace('/[^0-9]/', '', (string)($pan ?: '623608272'));
        $digits = str_split($clean);
        while (count($digits) < 9) {
            array_unshift($digits, '0');
        }
        return array_slice($digits, 0, 9);
    }

    /**
     * Convert numeric currency amount to English words (South Asian Lakh / Crore format)
     */
    public static function numberToWords($number): string
    {
        $number = (float)$number;
        if ($number <= 0) {
            return 'Zero Rupees Only';
        }

        $ones = [
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen'
        ];
        $tens = [
            2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
            6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'
        ];

        $convertGroup = function ($n) use (&$convertGroup, $ones, $tens) {
            $output = '';
            if ($n >= 100) {
                $output .= $ones[intval($n / 100)] . ' Hundred ';
                $n %= 100;
            }
            if ($n >= 20) {
                $output .= $tens[intval($n / 10)] . ' ';
                $n %= 10;
            }
            if ($n > 0) {
                $output .= $ones[$n] . ' ';
            }
            return trim($output);
        };

        $crore    = intval($number / 10000000);
        $number   %= 10000000;
        $lakh     = intval($number / 100000);
        $number   %= 100000;
        $thousand = intval($number / 1000);
        $number   %= 1000;
        $hundreds = intval($number);

        $result = '';
        if ($crore > 0) {
            $result .= $convertGroup($crore) . ' Crore ';
        }
        if ($lakh > 0) {
            $result .= $convertGroup($lakh) . ' Lakh ';
        }
        if ($thousand > 0) {
            $result .= $convertGroup($thousand) . ' Thousand ';
        }
        if ($hundreds > 0) {
            $result .= $convertGroup($hundreds) . ' ';
        }

        return trim($result) . ' Rupees Only';
    }

    /**
     * Recalculate totals and status
     */
    public function recalculate(): void
    {
        $subtotal = $this->items()->sum('total_amount');
        $discount = (float)$this->discount_amount;
        $afterDiscount = max(0, $subtotal - $discount);
        $tax = round($afterDiscount * ((float)$this->tax_percent / 100), 2);
        $total = $afterDiscount + $tax;
        $paid = $this->payments()->sum('amount');
        $due = max(0, $total - $paid);

        $status = 'unpaid';
        if ($paid >= $total && $total > 0) {
            $status = 'paid';
        } elseif ($paid > 0 && $paid < $total) {
            $status = 'partial';
        } elseif ($this->due_date && $this->due_date->isPast() && $paid < $total) {
            $status = 'overdue';
        }

        $this->update([
            'subtotal'     => $subtotal,
            'tax_amount'   => $tax,
            'total_amount' => $total,
            'paid_amount'  => $paid,
            'due_amount'   => $due,
            'status'       => $status,
        ]);
    }
}

