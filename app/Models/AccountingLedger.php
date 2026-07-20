<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountingLedger extends Model
{
    use HasFactory;

    protected $table = 'accounting_ledger';

    protected $fillable = [
        'transaction_date', 'description', 'type', 'amount', 'balance',
        'reference_type', 'reference_id', 'category', 'created_by',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'balance'          => 'decimal:2',
        'transaction_date' => 'date',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Static helpers ────────────────────────────────────────────────────────
    /**
     * Append a ledger entry, computing the running balance automatically.
     */
    public static function record(
        string  $type,           // 'debit' | 'credit'
        float   $amount,
        string  $description,
        string  $date,
        ?string $referenceType = null,
        ?int    $referenceId   = null,
        ?string $category      = null,
        ?int    $createdBy     = null,
    ): self {
        $last    = self::latest('id')->first();
        $current = (float) ($last?->balance ?? 0);
        $balance = $type === 'credit'
            ? $current + $amount
            : $current - $amount;

        return self::create([
            'transaction_date' => $date,
            'description'      => $description,
            'type'             => $type,
            'amount'           => $amount,
            'balance'          => $balance,
            'reference_type'   => $referenceType,
            'reference_id'     => $referenceId,
            'category'         => $category,
            'created_by'       => $createdBy,
        ]);
    }

    public function getTypeBadgeAttribute(): string
    {
        return $this->type === 'credit'
            ? 'bg-emerald-100 text-emerald-700'
            : 'bg-rose-100 text-rose-700';
    }
}
