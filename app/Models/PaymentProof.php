<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class PaymentProof extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id', 'invoice_id', 'student_id', 'submitted_by', 'reviewed_by',
        'gateway', 'transaction_id', 'amount', 'screenshot',
        'status', 'rejection_reason', 'notes', 'reviewed_at',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ── Computed ──────────────────────────────────────────────────────────────

    public function getScreenshotUrlAttribute(): ?string
    {
        return $this->screenshot
            ? Storage::disk('public')->url($this->screenshot)
            : null;
    }

    public function getGatewayLabelAttribute(): string
    {
        return match($this->gateway) {
            'esewa'   => 'eSewa',
            'khalti'  => 'Khalti',
            'bank'    => 'Bank / Connect IPS',
            'fonepay' => 'FonePay',
            default   => ucfirst($this->gateway),
        };
    }

    public function getGatewayColorAttribute(): string
    {
        return match($this->gateway) {
            'esewa'   => '#60bb46',
            'khalti'  => '#5c2d91',
            'bank'    => '#1d4ed8',
            'fonepay' => '#dc2626',
            default   => '#6366f1',
        };
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
