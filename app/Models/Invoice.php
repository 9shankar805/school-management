<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'invoice_number',
        'student_id',
        'session_id',
        'class_id',
        'section_id',
        'fee_type_id',
        'title',
        'description',
        'total_amount',
        'discount_amount',
        'tax_amount',
        'amount',           // current net amount = total_amount - discount_amount + tax_amount
        'previous_due',     // unpaid balance carried forward from previous months (arrears/बक्यौता)
        'fine_amount',      // late payment fine/penalty
        'paid_amount',      // cached payments total
        'status',           // unpaid | partial | paid
        'due_date',
        'billing_month',
        'billing_year',
        'is_auto_generated',
    ];

    protected $casts = [
        'total_amount'      => 'decimal:2',
        'discount_amount'   => 'decimal:2',
        'tax_amount'        => 'decimal:2',
        'amount'            => 'decimal:2',
        'previous_due'      => 'decimal:2',
        'fine_amount'       => 'decimal:2',
        'paid_amount'       => 'decimal:2',
        'due_date'          => 'date',
        'is_auto_generated' => 'boolean',
    ];

    // ── Boot: auto-generate invoice_number ───────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
        });
    }

    public static function generateInvoiceNumber(): string
    {
        $year   = now()->year;
        $prefix = 'INV-' . $year . '-';

        $latest = static::withoutGlobalScopes()
            ->withTrashed()
            ->where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('invoice_number')
            ->first();

        $next = 1;
        if ($latest && preg_match('/-(\d+)$/', $latest->invoice_number, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        do {
            $candidate = $prefix . str_pad($next, 6, '0', STR_PAD_LEFT);
            $exists = static::withoutGlobalScopes()->withTrashed()->where('invoice_number', $candidate)->exists();
            if ($exists) {
                $next++;
            }
        } while ($exists);

        return $candidate;
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function session()
    {
        return $this->belongsTo(SchoolSession::class, 'session_id');
    }

    public function feeType()
    {
        return $this->belongsTo(FeeType::class, 'fee_type_id');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function ledgerEntries()
    {
        return $this->morphMany(StudentLedger::class, 'reference');
    }

    // ── Computed helpers ──────────────────────────────────────────────────────

    public function getAmountPaidAttribute(): float
    {
        if (isset($this->attributes['paid_amount']) && (float) $this->attributes['paid_amount'] > 0) {
            return (float) $this->attributes['paid_amount'];
        }
        return (float) $this->payments()->sum('amount');
    }

    /**
     * Grand total payable for this billing statement:
     * Current Net Fee + Previous Unpaid Arrears (बक्यौता) + Late Fines
     */
    public function getTotalPayableAttribute(): float
    {
        return round((float) $this->amount + (float) ($this->previous_due ?? 0) + (float) ($this->fine_amount ?? 0), 2);
    }

    /**
     * Total remaining amount due to clear this cumulative statement:
     * Total Payable minus payments made.
     */
    public function getAmountDueAttribute(): float
    {
        return max(0, round($this->total_payable - $this->amount_paid, 2));
    }

    /**
     * Net fee for this month only (excluding arrears and fines)
     */
    public function getCurrentNetDueAttribute(): float
    {
        return max(0, round((float) $this->amount - $this->amount_paid, 2));
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'paid' && $this->due_date && $this->due_date->isPast();
    }

    /**
     * Recalculate invoice totals from its line items and save.
     */
    public function recalculateFromItems(): static
    {
        $this->total_amount    = $this->items()->sum(\DB::raw('unit_price * quantity'));
        $this->discount_amount = $this->items()->sum('discount_amount');
        $this->tax_amount      = $this->items()->sum('tax_amount');
        $this->amount          = $this->total_amount - $this->discount_amount + $this->tax_amount;
        $this->save();
        return $this;
    }
}
