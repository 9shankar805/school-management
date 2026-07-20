<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\AccountingLedger;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'receipt_number', 'invoice_id', 'amount_paid', 'payment_date',
        'payment_method', 'transaction_reference', 'bank_name', 'cheque_number',
        'notes', 'received_by',
    ];

    protected $casts = [
        'amount_paid'  => 'decimal:2',
        'payment_date' => 'date',
    ];

    const PAYMENT_METHODS = [
        'cash'          => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'cheque'        => 'Cheque',
        'card'          => 'Card / POS',
        'online'        => 'Online Payment',
    ];

    // ── Boot: auto-generate receipt number + ledger entry ────────────────────
    protected static function booted(): void
    {
        static::creating(function (self $payment) {
            if (empty($payment->receipt_number)) {
                $payment->receipt_number = 'RCP-' . strtoupper(uniqid());
            }
        });

        static::created(function (self $payment) {
            // Record credit entry in accounting ledger
            $invoice = $payment->invoice;
            AccountingLedger::record(
                type:          'credit',
                amount:        (float) $payment->amount_paid,
                description:   'Fee payment — ' . ($invoice?->invoice_number ?? 'Invoice #' . $payment->invoice_id),
                date:          $payment->payment_date->toDateString(),
                referenceType: 'Payment',
                referenceId:   $payment->id,
                category:      'fees',
                createdBy:     $payment->received_by,
            );
        });
    }

    // ── Relationships ─────────────────────────────────────────────────────────
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function installmentItem()
    {
        return $this->hasOne(InstallmentPlanItem::class);
    }
}
