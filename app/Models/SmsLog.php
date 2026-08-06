<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmsLog extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'sender_id',
        'recipient_phone',
        'recipient_id',
        'gateway',
        'gateway_message_id',
        'type',
        'message',
        'status',
        'error_message',
        'sent_at',
        'delivered_at',
        'cost',
        'notifiable_type',
        'notifiable_id',
    ];

    protected $casts = [
        'sent_at'      => 'datetime',
        'delivered_at' => 'datetime',
        'cost'         => 'decimal:4',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /** Polymorphic notifiable (Student / Teacher / etc.) */
    public function notifiable()
    {
        return $this->morphTo();
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeForSchool($query, int $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }
}
