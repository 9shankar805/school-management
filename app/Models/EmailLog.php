<?php

namespace App\Models;
use App\Traits\HasTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailLog extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'sender_id',
        'recipient_email',
        'recipient_id',
        'mailer',
        'message_id',
        'type',
        'subject',
        'body',
        'attachments',
        'status',
        'error_message',
        'sent_at',
        'notifiable_type',
        'notifiable_id',
    ];

    protected $casts = [
        'sent_at'     => 'datetime',
        'attachments' => 'array',
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

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeForSchool($query, int $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }
}
