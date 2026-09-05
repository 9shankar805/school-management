<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'invoice_id',
        'amount',               // was amount_paid — renamed for consistency
        'payment_date',
        'payment_method',
        'gateway',
        'gateway_transaction_id',
        'receipt_number',
        'notes',
        'recorded_by',
        'verified_by',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'date',
    ];

    // ── Boot: auto-generate receipt_number ───────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {
            if (empty($payment->receipt_number)) {
                $payment->receipt_number = static::generateReceiptNumber();
            }
        });

        // After payment saved → update invoice status + post credit to student ledger
        static::created(function (Payment $payment) {
            $payment->updateInvoiceStatus();
            $payment->postLedgerCredit();
        });
    }

    public static function generateReceiptNumber(): string
    {
        $year   = now()->year;
        $latest = static::whereYear('created_at', $year)->latest('id')->first();
        $next   = $latest ? ((int) substr($latest->receipt_number, -6)) + 1 : 1;
        return 'RCP-' . $year . '-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    /**
     * After a payment is recorded, recompute the parent invoice status.
     */
    public function updateInvoiceStatus(): void
    {
        $invoice = $this->invoice;
        if (! $invoice) return;

        $paid = (float) $invoice->payments()->sum('amount');
        $due  = (float) $invoice->amount;

        if ($paid <= 0) {
            $status = 'unpaid';
        } elseif ($paid >= $due) {
            $status = 'paid';
        } else {
            $status = 'partial';
        }

        $invoice->update(['status' => $status]);
    }

    /**
     * Post a credit entry to the student's ledger when a payment is recorded.
     * This reduces the student's outstanding balance.
     */
    public function postLedgerCredit(): void
    {
        try {
            $invoice = $this->invoice;
            if (! $invoice || ! $invoice->student_id) return;

            \App\Models\StudentLedger::credit(
                studentId:   $invoice->student_id,
                amount:      (float) $this->amount,
                description: 'Payment received — ' . $this->receipt_number
                             . ' (' . ucfirst($this->payment_method ?? 'cash') . ')',
                type:        'payment',
                sessionId:   $invoice->session_id,
                reference:   $this,
                date:        $this->payment_date
                             ? \Carbon\Carbon::parse($this->payment_date)->toDateString()
                             : today()->toDateString(),
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Payment::postLedgerCredit failed: ' . $e->getMessage());
        }
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function ledgerEntries()
    {
        return $this->morphMany(StudentLedger::class, 'reference');
    }
}
