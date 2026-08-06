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
        'amount',           // net amount = total_amount - discount_amount + tax_amount
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
        $latest = static::whereYear('created_at', $year)
                        ->latest('id')
                        ->first();
        $next = $latest ? ((int) substr($latest->invoice_number, -6)) + 1 : 1;
        return 'INV-' . $year . '-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
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
        return (float) $this->payments()->sum('amount');
    }

    public function getAmountDueAttribute(): float
    {
        return max(0, (float) $this->amount - $this->amount_paid);
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
