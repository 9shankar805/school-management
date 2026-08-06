<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Per-student append-only financial ledger.
 *
 * DO NOT add SoftDeletes — this table is immutable for audit integrity.
 * Corrections are made via 'adjustment' entries, never by editing/deleting.
 */
class StudentLedger extends Model
{
    use HasFactory, HasTenant;

    protected $table = 'student_ledger';

    protected $fillable = [
        'school_id',
        'student_id',
        'session_id',
        'entry_date',
        'type',
        'description',
        'debit',
        'credit',
        'balance',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'debit'      => 'decimal:2',
        'credit'     => 'decimal:2',
        'balance'    => 'decimal:2',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function session()
    {
        return $this->belongsTo(SchoolSession::class, 'session_id');
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Static factory helpers ────────────────────────────────────────────────

    /**
     * Get the current running balance for a student (in a session).
     */
    public static function currentBalance(int $studentId, ?int $sessionId = null): float
    {
        $query = static::where('student_id', $studentId);
        if ($sessionId) {
            $query->where('session_id', $sessionId);
        }
        $latest = $query->latest('id')->first();
        return $latest ? (float) $latest->balance : 0.0;
    }

    /**
     * Append a debit entry (fee charged) and return the new row.
     */
    public static function debit(
        int    $studentId,
        float  $amount,
        string $description,
        string $type = 'invoice',
        ?int   $sessionId = null,
        mixed  $reference = null,
        string $date = null,
    ): static {
        $prevBalance = static::currentBalance($studentId, $sessionId);
        $newBalance  = $prevBalance + $amount;

        return static::create([
            'student_id'     => $studentId,
            'session_id'     => $sessionId,
            'entry_date'     => $date ?? today()->toDateString(),
            'type'           => $type,
            'description'    => $description,
            'debit'          => $amount,
            'credit'         => 0,
            'balance'        => $newBalance,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id'   => $reference?->id,
            'created_by'     => auth()->id(),
        ]);
    }

    /**
     * Append a credit entry (payment received / discount) and return the new row.
     */
    public static function credit(
        int    $studentId,
        float  $amount,
        string $description,
        string $type = 'payment',
        ?int   $sessionId = null,
        mixed  $reference = null,
        string $date = null,
    ): static {
        $prevBalance = static::currentBalance($studentId, $sessionId);
        $newBalance  = $prevBalance - $amount;

        return static::create([
            'student_id'     => $studentId,
            'session_id'     => $sessionId,
            'entry_date'     => $date ?? today()->toDateString(),
            'type'           => $type,
            'description'    => $description,
            'debit'          => 0,
            'credit'         => $amount,
            'balance'        => $newBalance,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id'   => $reference?->id,
            'created_by'     => auth()->id(),
        ]);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForSession($query, int $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeDebits($query)
    {
        return $query->where('debit', '>', 0);
    }

    public function scopeCredits($query)
    {
        return $query->where('credit', '>', 0);
    }
}
