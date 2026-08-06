<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'school_name',
        'subject',
        'message',
        'status',
        'admin_notes',
        'ip_address',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────
    public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // ── Helpers ───────────────────────────────────────────────────────────
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'unread'   => '<span class="badge bg-rose-100 text-rose-700 text-xs px-2.5 py-1 rounded-full font-semibold"><i class="bi bi-envelope-fill me-1"></i>Unread</span>',
            'read'     => '<span class="badge bg-slate-100 text-slate-600 text-xs px-2.5 py-1 rounded-full font-semibold"><i class="bi bi-envelope-open me-1"></i>Read</span>',
            'replied'  => '<span class="badge bg-emerald-100 text-emerald-700 text-xs px-2.5 py-1 rounded-full font-semibold"><i class="bi bi-reply-fill me-1"></i>Replied</span>',
            'archived' => '<span class="badge bg-amber-100 text-amber-700 text-xs px-2.5 py-1 rounded-full font-semibold"><i class="bi bi-archive me-1"></i>Archived</span>',
            default    => '<span class="badge bg-slate-100 text-slate-600 text-xs px-2.5 py-1 rounded-full font-semibold">' . ucfirst($this->status) . '</span>',
        };
    }
}
