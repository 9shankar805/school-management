<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class BookIssue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'book_id',
        'member_id',
        'issued_by',
        'returned_to',
        'issue_date',
        'due_date',
        'return_date',
        'status',
        'overdue_days',
        'fine_per_day',
        'fine_amount',
        'fine_status',
        'notes',
    ];

    protected $casts = [
        'issue_date'   => 'date',
        'due_date'     => 'date',
        'return_date'  => 'date',
        'fine_per_day' => 'decimal:2',
        'fine_amount'  => 'decimal:2',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function member()
    {
        return $this->belongsTo(LibraryMember::class, 'member_id');
    }

    public function issuedByUser()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function returnedToUser()
    {
        return $this->belongsTo(User::class, 'returned_to');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['issued', 'overdue']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopePendingFine($query)
    {
        return $query->where('fine_status', 'pending');
    }

    // ── Accessors / Helpers ───────────────────────────────────────────────────

    public function getIsOverdueAttribute(): bool
    {
        return $this->return_date === null
            && $this->due_date !== null
            && $this->due_date->isPast();
    }

    public function getCalculatedOverdueDaysAttribute(): int
    {
        if (!$this->is_overdue && $this->status !== 'overdue') {
            return 0;
        }
        $base = $this->return_date ?? Carbon::today();
        return max(0, $this->due_date->diffInDays($base));
    }

    public function getCalculatedFineAttribute(): float
    {
        return round($this->calculated_overdue_days * $this->fine_per_day, 2);
    }

    /**
     * Mark this issue as returned and calculate fine.
     * Also updates book available_qty and member outstanding_fine.
     */
    public function processReturn(int $returnedToUserId, ?string $notes = null): void
    {
        $returnDate    = Carbon::today();
        $overdueDays   = max(0, $this->due_date->diffInDays($returnDate, false) * -1);
        // diffInDays with false: positive means returnDate > dueDate (overdue)
        $overdueDays   = $returnDate->gt($this->due_date)
            ? (int) $this->due_date->diffInDays($returnDate)
            : 0;
        $fineAmount    = round($overdueDays * $this->fine_per_day, 2);

        $this->update([
            'return_date'  => $returnDate->toDateString(),
            'returned_to'  => $returnedToUserId,
            'status'       => 'returned',
            'overdue_days' => $overdueDays,
            'fine_amount'  => $fineAmount,
            'fine_status'  => $fineAmount > 0 ? 'pending' : 'none',
            'notes'        => $notes ?? $this->notes,
        ]);

        // Restore book stock
        $this->book->increment('available_qty');

        // Refresh member's outstanding fine
        $this->member->recalculateFine();
    }
}
