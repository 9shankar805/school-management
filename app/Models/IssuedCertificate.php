<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class IssuedCertificate extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'template_id',
        'student_id',
        'issued_by',
        'certificate_number',
        'type',
        'issued_date',
        'valid_until',
        'extra_notes',
        'field_overrides',
        'purpose',
        'status',
        'language',
        'paper_size',
        'verification_token',
    ];

    protected $casts = [
        'issued_date'     => 'date',
        'valid_until'     => 'date',
        'field_overrides' => 'array',
    ];

    const STATUSES = [
        'issued'  => 'Issued',
        'revoked' => 'Revoked',
        'expired' => 'Expired',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    public function template(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class, 'template_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'issued');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Generate a unique, human-readable certificate number.
     * Format: CERT-{TYPE_PREFIX}-{YEAR}-{6-digit sequence}
     * e.g.  CERT-BON-2026-000042
     */
    public static function generateNumber(string $type): string
    {
        $prefix   = strtoupper(substr($type, 0, 3));
        $year     = now()->year;
        $base     = sprintf('CERT-%s-%d-', $prefix, $year);

        $latest = static::withoutGlobalScopes()
            ->withTrashed()
            ->where('certificate_number', 'like', $base . '%')
            ->orderByDesc('certificate_number')
            ->first();

        $next = 1;
        if ($latest && preg_match('/-(\d+)$/', $latest->certificate_number, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        do {
            $candidate = $base . str_pad($next, 6, '0', STR_PAD_LEFT);
            $exists = static::withoutGlobalScopes()->withTrashed()->where('certificate_number', $candidate)->exists();
            if ($exists) {
                $next++;
            }
        } while ($exists);

        return $candidate;
    }

    /**
     * Generate a cryptographically secure verification token.
     */
    public static function generateToken(): string
    {
        return Str::random(64);
    }

    /**
     * Public URL for QR code verification.
     */
    public function verificationUrl(): string
    {
        return route('certificates.verify', $this->verification_token);
    }

    /**
     * QR code image URL (via QR server API, safe for DomPDF isRemoteEnabled).
     */
    public function qrCodeUrl(): string
    {
        $data = $this->verificationUrl();
        return 'https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=' . urlencode($data);
    }

    public function typeLabel(): string
    {
        return CertificateTemplate::TYPES[$this->type] ?? ucwords(str_replace('_', ' ', $this->type));
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'issued'  => 'bg-emerald-100 text-emerald-700',
            'revoked' => 'bg-rose-100 text-rose-700',
            'expired' => 'bg-amber-100 text-amber-700',
            default   => 'bg-slate-100 text-slate-500',
        };
    }
}
