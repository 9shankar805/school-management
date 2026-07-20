<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class LibraryMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'member_type',
        'card_number',
        'membership_start',
        'membership_end',
        'status',
        'max_books',
        'loan_days',
        'outstanding_fine',
    ];

    protected $casts = [
        'membership_start'  => 'date',
        'membership_end'    => 'date',
        'outstanding_fine'  => 'decimal:2',
    ];

    // Auto-generate card number on creation
    protected static function booted(): void
    {
        static::creating(function (self $member) {
            if (empty($member->card_number)) {
                $member->card_number = 'LIB-' . strtoupper(Str::random(8));
            }
            if (empty($member->membership_start)) {
                $member->membership_start = now()->toDateString();
            }
        });
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function issues()
    {
        return $this->hasMany(BookIssue::class, 'member_id');
    }

    public function activeIssues()
    {
        return $this->hasMany(BookIssue::class, 'member_id')
                    ->whereIn('status', ['issued', 'overdue']);
    }

    public function overdueIssues()
    {
        return $this->hasMany(BookIssue::class, 'member_id')
                    ->where('status', 'overdue');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** How many more books this member can borrow */
    public function getRemainingQuotaAttribute(): int
    {
        $active = $this->activeIssues()->count();
        return max(0, $this->max_books - $active);
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active'
            && ($this->membership_end === null || $this->membership_end->isFuture());
    }

    /** Recalculate and persist outstanding fine from pending book-issue fines */
    public function recalculateFine(): void
    {
        $total = $this->issues()
                      ->where('fine_status', 'pending')
                      ->sum('fine_amount');
        $this->outstanding_fine = $total;
        $this->saveQuietly();
    }
}
