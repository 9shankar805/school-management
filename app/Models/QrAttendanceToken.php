<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QrAttendanceToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'class_id',
        'section_id',
        'course_id',
        'session_id',
        'date',
        'token',
        'valid_minutes',
        'school_start',
        'is_active',
    ];

    protected $casts = [
        'date'       => 'date',
        'is_active'  => 'boolean',
    ];

    // ──────────────────────────────────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────────────────────────────────

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Business logic helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Generate a cryptographically-safe unique token string.
     */
    public static function generateToken(): string
    {
        return Str::random(48) . '-' . now()->timestamp;
    }

    /**
     * Is this token still within its validity window?
     */
    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        // Date must be today
        if (! $this->date->isToday()) {
            return false;
        }

        // If valid_minutes == 0, the token never expires (teacher controls it manually)
        if ($this->valid_minutes === 0) {
            return true;
        }

        return $this->created_at->addMinutes($this->valid_minutes)->isFuture();
    }

    /**
     * Calculate how many minutes late a student is relative to school_start.
     * Returns 0 if the student checks in on time or early.
     */
    public function calcLateMinutes(): int
    {
        $start = Carbon::today()->setTimeFromTimeString($this->school_start);
        $now   = Carbon::now();

        return $now->gt($start) ? (int) $start->diffInMinutes($now) : 0;
    }

    /**
     * The full URL a student visits after scanning the QR code.
     */
    public function getScanUrlAttribute(): string
    {
        return route('attendance.qr.scan', ['token' => $this->token]);
    }

    /**
     * URL-safe QR image (via api.qrserver.com — consistent with the rest of the app).
     */
    public function getQrImageUrlAttribute(): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($this->scan_url);
    }
}
