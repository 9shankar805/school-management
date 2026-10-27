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
        // Homepage popup notice (Principal urgent announcement / admission flyer)
        'popup_active', 'popup_title', 'popup_subtitle', 'popup_image',
        'popup_btn_text', 'popup_btn_url', 'popup_expires_at', 'popup_show_once',
        // Fullscreen hero slider banners
        'slider_banners',
    ];

    protected $casts = [
        'approved_at'         => 'datetime',
        'trial_ends_at'       => 'date',
        'plan_expires_at'     => 'date',
        'popup_expires_at'    => 'date',
        'popup_active'        => 'boolean',
        'popup_show_once'     => 'boolean',
        'ai_features_enabled' => 'boolean',
        'settings'            => 'array',
        'gallery_images'      => 'array',
        'slider_banners'      => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (School $school) {
            if (empty($school->slug) && !empty($school->name)) {
                $school->slug = static::makeSlug($school->name);
            }
            if (empty($school->website) && !empty($school->slug)) {
                $cleanSub = str_replace('-', '', strtolower($school->slug));
                $baseDomain = config('app.domain', 'nshlms.com');
                $school->website = "https://{$cleanSub}.{$baseDomain}";
            }
        });
    }

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

    /** School official published notices */
    public function notices(): HasMany
    {
        return $this->hasMany(Notice::class, 'school_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($q)      { return $q->where('status', 'active'); }
    public function scopePending($q)     { return $q->where('status', 'pending'); }
    public function scopeSuspended($q)   { return $q->where('status', 'suspended'); }

    // ── Accessors ────────────────────────────────────────────────────────────

    public function getLogoUrlAttribute(): string
    {
        if ($this->logo) {
            if (str_starts_with($this->logo, 'http://') || str_starts_with($this->logo, 'https://')) {
                return $this->logo;
            }
            if (str_starts_with($this->logo, 'imgs/')) {
                return asset($this->logo);
            }
            $clean = ltrim(str_replace('storage/', '', $this->logo), '/');
            return asset('storage/' . $clean);
        }
        // Auto-generated school avatar with school initials and branded color
        $initials = urlencode(strtoupper(substr($this->name ?? 'SC', 0, 2)));
        $bg = ltrim($this->primary_color ?: '4f46e5', '#');
        return "https://ui-avatars.com/api/?name={$initials}&background={$bg}&color=fff&size=128&bold=true";
    }

    public function getPopupImageUrlAttribute(): ?string
    {
        if ($this->popup_image) {
            return asset('storage/' . $this->popup_image);
        }
        return null;
    }

    /**
     * Get the list of hero slider banners, with professional fallbacks if none uploaded yet.
     */
    public function getSliderBannersListAttribute(): array
    {
        if (!empty($this->slider_banners) && is_array($this->slider_banners) && count($this->slider_banners) > 0) {
            return array_values($this->slider_banners);
        }

        // Professional academic defaults so the school website always looks stunning
        return [
            [
                'image'              => 'imgs/hero/hero_1.webp',
                'is_asset'           => true,
                'tag'                => 'Admissions Open 2026-2027',
                'title'              => 'Excellence in Education, Rooted in Values',
                'subtitle'           => 'Inspiring intellectual curiosity, moral courage, and compassionate leadership in the heart of Nepal.',
                'btn_text'           => 'Apply for Admission',
                'btn_url'            => url('admissions/apply?school=' . $this->slug),
                'secondary_btn_text' => 'Explore Campus',
                'secondary_btn_url'  => '#about',
            ],
            [
                'image'              => 'imgs/hero/hero_2.webp',
                'is_asset'           => true,
                'tag'                => 'Modern Infrastructure',
                'title'              => 'Advanced STEM Laboratories & Digital Library',
                'subtitle'           => 'Empowering learners with experiential science, high-speed robotics labs, and comprehensive research tools for future-ready innovators.',
                'btn_text'           => 'Discover Facilities',
                'btn_url'            => '#features',
                'secondary_btn_text' => 'View Programs',
                'secondary_btn_url'  => '#academics',
            ],
            [
                'image'              => 'imgs/hero/hero_3.webp',
                'is_asset'           => true,
                'tag'                => 'Holistic Development',
                'title'              => 'Nurturing Champions in Academics, Sports & Arts',
                'subtitle'           => 'Comprehensive athletic grounds, arts studios, and student council programs cultivating well-rounded leaders with global perspective.',
                'btn_text'           => 'Academic Programs',
                'btn_url'            => '#academics',
                'secondary_btn_text' => 'Explore Campus Life',
                'secondary_btn_url'  => '#facilities',
            ],
        ];
    }

    public function hasActivePopup(): bool
    {
        if (! $this->popup_active) {
            return false;
        }
        if ($this->popup_expires_at && $this->popup_expires_at->isPast()) {
            return false;
        }
        return ! empty($this->popup_image) || ! empty($this->popup_title);
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

    /** Clean lowercase alphanumeric subdomain without dashes, e.g. 'genesisglobalschool' */
    public function getSubdomainAttribute(): string
    {
        return str_replace('-', '', strtolower($this->slug ?? ''));
    }

    /** Full canonical subdomain URL, e.g. 'https://genesisglobalschool.nshlms.com' */
    public function getSubdomainUrlAttribute(): string
    {
        $baseDomain = config('app.domain', 'nshlms.com');
        return "https://{$this->subdomain}.{$baseDomain}";
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
        'royal_blue'   => ['label' => 'Oxford Navy & Gold',   'primary' => '#1a365d', 'secondary' => '#c59b27'],
        'forest_green' => ['label' => 'Dartmouth Emerald',    'primary' => '#064e3b', 'secondary' => '#10b981'],
        'classic_red'  => ['label' => 'Harvard Crimson',      'primary' => '#881337', 'secondary' => '#d97706'],
        'orange'       => ['label' => 'Amber & Deep Slate',   'primary' => '#ea580c', 'secondary' => '#0f172a'],
        'violet'       => ['label' => 'Imperial Purple',      'primary' => '#4c1d95', 'secondary' => '#f59e0b'],
        'teal'         => ['label' => 'Maritime Deep Teal',   'primary' => '#0f766e', 'secondary' => '#06b6d4'],
        'slate'        => ['label' => 'Executive Slate Dark', 'primary' => '#0f172a', 'secondary' => '#3b82f6'],
        'sapphire'     => ['label' => 'Royal Sapphire Blue',  'primary' => '#1d4ed8', 'secondary' => '#f59e0b'],
    ];

    /** Get principal photo URL with intelligent fallbacks */
    public function getPrincipalPhotoUrlAttribute(): string
    {
        if (!empty($this->settings['principal_image'])) {
            return asset('storage/' . $this->settings['principal_image']);
        }
        $principal = $this->users()->where('role', 'principal')->whereNotNull('photo')->first();
        if ($principal && $principal->photo) {
            return asset('storage/' . $principal->photo);
        }
        if ($this->owner && $this->owner->photo) {
            return asset('storage/' . $this->owner->photo);
        }
        if (file_exists(public_path('storage/school-staff/principal.jpg'))) {
            return asset('storage/school-staff/principal.jpg');
        }
        return asset('imgs/profile.webp');
    }

    /** Get principal name with intelligent fallbacks */
    public function getPrincipalNameResolvedAttribute(): string
    {
        if (!empty($this->settings['principal_name'])) {
            return $this->settings['principal_name'];
        }
        if (!empty($this->contact_person)) {
            return $this->contact_person;
        }
        $principal = $this->users()->where('role', 'principal')->first();
        if ($principal) {
            return $principal->name;
        }
        return 'Prof. Dr. Principal';
    }

    /** School Operational Hours & Schedule Accessors */
    public function getSchoolHoursWeekdaysAttribute(): string
    {
        return $this->settings['school_hours_weekdays'] ?? '09:00 AM – 04:00 PM (Sun – Thu)';
    }

    public function getSchoolHoursFridayAttribute(): string
    {
        return $this->settings['school_hours_friday'] ?? '09:00 AM – 01:30 PM (Friday)';
    }

    public function getSchoolHoursWeekendAttribute(): string
    {
        return $this->settings['school_hours_weekend'] ?? 'Closed (Saturday & Public Holidays)';
    }

    public function getOfficeHoursAttribute(): string
    {
        return $this->settings['office_hours'] ?? '08:30 AM – 05:00 PM';
    }

    /** Realtime School Open/Close status checker */
    public function getIsOpenNowAttribute(): bool
    {
        $now = now()->setTimezone(config('app.timezone', 'Asia/Kathmandu'));
        $dayOfWeek = $now->dayOfWeek; // 0 = Sunday, 5 = Friday, 6 = Saturday
        $timeStr = $now->format('H:i');

        if ($dayOfWeek === 6) {
            // Saturday in Nepal is weekend holiday
            return false;
        }

        if ($dayOfWeek === 5) {
            // Friday half day
            return ($timeStr >= '09:00' && $timeStr <= '13:30');
        }

        // Sunday through Thursday
        return ($timeStr >= '08:30' && $timeStr <= '16:30');
    }

    public function getOpenStatusBadgeAttribute(): array
    {
        $now = now()->setTimezone(config('app.timezone', 'Asia/Kathmandu'));
        $dayOfWeek = $now->dayOfWeek; // 0 = Sun, 5 = Fri, 6 = Sat
        $isOpen = $this->is_open_now;

        if ($dayOfWeek === 6) {
            return [
                'is_open' => false,
                'badge' => 'CLOSED (Saturday Weekend)',
                'class' => 'bg-rose-500/10 text-rose-600 border-rose-200',
                'subtext' => 'Opens Sunday at 09:00 AM',
            ];
        }

        if ($isOpen) {
            $closeTime = ($dayOfWeek === 5) ? '01:30 PM' : '04:00 PM';
            return [
                'is_open' => true,
                'badge' => 'OPEN NOW',
                'class' => 'bg-emerald-500/10 text-emerald-600 border-emerald-200',
                'subtext' => "Academic & Office active until {$closeTime}",
            ];
        } else {
            return [
                'is_open' => false,
                'badge' => 'CLOSED NOW',
                'class' => 'bg-amber-500/10 text-amber-600 border-amber-200',
                'subtext' => 'Opens at 09:00 AM on next working day',
            ];
        }
    }

    /** Upcoming Exam Routine Accessors */
    public function getExamTitleAttribute(): ?string
    {
        return $this->settings['exam_title'] ?? null;
    }

    public function getExamStartDateAttribute(): ?string
    {
        return $this->settings['exam_start_date'] ?? null;
    }

    public function getExamEndDateAttribute(): ?string
    {
        return $this->settings['exam_end_date'] ?? null;
    }

    public function getExamRoutineFileUrlAttribute(): ?string
    {
        if (!empty($this->settings['exam_routine_file'])) {
            return asset('storage/' . $this->settings['exam_routine_file']);
        }
        return null;
    }

    public function getExamInstructionsAttribute(): ?string
    {
        return $this->settings['exam_instructions'] ?? null;
    }

    public function getExamShiftDetailsAttribute(): ?string
    {
        return $this->settings['exam_shift_details'] ?? null;
    }

    public function hasUpcomingExamRoutine(): bool
    {
        return !empty($this->settings['exam_title']) || !empty($this->settings['exam_routine_file']);
    }

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
