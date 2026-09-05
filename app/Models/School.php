<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class School extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'iemis_code', 'email', 'phone',
        'address', 'city', 'district', 'province', 'country',
        'logo', 'website', 'description',
        'about_us', 'mission', 'vision', 'history',
        'gallery_images', 'admission_info', 'result_info',
        'notice_board', 'principal_message',
        // Theme & branding
        'primary_color', 'secondary_color', 'preset_theme',
        // Social links
        'facebook_url', 'twitter_url', 'instagram_url', 'youtube_url',
        'plan', 'max_students', 'max_teachers', 'max_staff', 'whatsapp_quota',
        'ai_features_enabled', 'trial_ends_at', 'plan_expires_at',
        'status', 'approved_at', 'approved_by', 'owner_id',
        'contact_person', 'contact_email', 'settings',
        // Payment QR codes (principal uploads; shown to parents on invoices)
        'payment_qr_esewa', 'payment_qr_khalti', 'payment_qr_bank',
        'payment_qr_fonepay', 'payment_instructions',
    ];

    protected $casts = [
        'approved_at'         => 'datetime',
        'trial_ends_at'       => 'date',
        'plan_expires_at'     => 'date',
        'ai_features_enabled' => 'boolean',
        'settings'            => 'array',
        'gallery_images'      => 'array',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    /** All users belonging to this school */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** The owner / primary admin user */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** Super-admin who approved this school */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** School Management Committee (SMC) members. */
    public function committeeMembers(): HasMany
    {
        return $this->hasMany(SchoolCommitteeMember::class, 'school_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($q)      { return $q->where('status', 'active'); }
    public function scopePending($q)     { return $q->where('status', 'pending'); }
    public function scopeSuspended($q)   { return $q->where('status', 'suspended'); }

    // ── Accessors ────────────────────────────────────────────────────────────

    public function getLogoUrlAttribute(): string
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }
        // Auto-generated school avatar
        $initials = strtoupper(substr($this->name, 0, 2));
        return "https://ui-avatars.com/api/?name={$initials}&background=6366f1&color=fff&size=128&bold=true";
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getIsTrialAttribute(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function getPlanLabelAttribute(): string
    {
        return match($this->plan) {
            'free'       => 'Free',
            'yearly'     => 'Yearly Premium',
            'starter'    => 'Starter',
            'growth'     => 'Growth',
            'enterprise' => 'Enterprise',
            default      => ucfirst($this->plan),
        };
    }

    public function getPlanColorAttribute(): string
    {
        return match($this->plan) {
            'free'       => 'slate',
            'yearly'     => 'indigo',
            'starter'    => 'blue',
            'growth'     => 'indigo',
            'enterprise' => 'violet',
            default      => 'slate',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active'    => 'emerald',
            'pending'   => 'amber',
            'suspended' => 'rose',
            'cancelled' => 'slate',
            default     => 'slate',
        };
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Resolved primary color — falls back to the theme default, then the
     * hard-coded default so the landing page always has a valid colour.
     */
    public function getPrimaryColorResolvedAttribute(): string
    {
        if ($this->primary_color) return $this->primary_color;
        return self::PRESET_THEMES[$this->preset_theme ?? 'royal_blue']['primary'] ?? '#1e4080';
    }

    public function getSecondaryColorResolvedAttribute(): string
    {
        if ($this->secondary_color) return $this->secondary_color;
        return self::PRESET_THEMES[$this->preset_theme ?? 'royal_blue']['secondary'] ?? '#e6b800';
    }

    /** All built-in preset themes shown in the colour picker. */
    const PRESET_THEMES = [
        'royal_blue'   => ['label' => 'Royal Blue',   'primary' => '#1e4080', 'secondary' => '#e6b800'],
        'forest_green' => ['label' => 'Forest Green', 'primary' => '#166534', 'secondary' => '#16a34a'],
        'classic_red'  => ['label' => 'Classic Red',  'primary' => '#991b1b', 'secondary' => '#1d4ed8'],
        'violet'       => ['label' => 'Violet',        'primary' => '#5b21b6', 'secondary' => '#7c3aed'],
        'teal'         => ['label' => 'Teal',          'primary' => '#0f766e', 'secondary' => '#06b6d4'],
        'orange'       => ['label' => 'Orange',        'primary' => '#c2410c', 'secondary' => '#ea580c'],
        'slate'        => ['label' => 'Slate',         'primary' => '#1e293b', 'secondary' => '#475569'],
    ];

    /** Generate a unique slug from the school name */
    public static function makeSlug(string $name): string
    {
        $base = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
        $base = trim($base, '-');
        $slug = $base;
        $i    = 2;
        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }
        return $slug;
    }

    public static function planLimits(): array
    {
        return [
            'free'       => ['students' => 100,  'teachers' => 20,  'staff' => 10,  'ai' => false, 'whatsapp' => 0,    'price_npr' => 0],
            'yearly'     => ['students' => 9999, 'teachers' => 999, 'staff' => 500, 'ai' => true,  'whatsapp' => 5000, 'price_npr' => 15000],
            // Kept for backward compatibility if already in DB
            'starter'    => ['students' => 500,  'teachers' => 50,  'staff' => 30,  'ai' => false, 'whatsapp' => 500,  'price_npr' => 2999],
            'growth'     => ['students' => 2000, 'teachers' => 150, 'staff' => 80,  'ai' => true,  'whatsapp' => 2000, 'price_npr' => 7999],
            'enterprise' => ['students' => 9999, 'teachers' => 999, 'staff' => 500, 'ai' => true,  'whatsapp' => 9999, 'price_npr' => 19999],
        ];
    }
}
