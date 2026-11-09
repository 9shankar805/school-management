<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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
        $host = request()->getHost();
        if (in_array($host, ['localhost', '127.0.0.1']) || str_contains(config('app.url', ''), 'localhost')) {
            return url('/school/' . $this->slug);
        }
        return "https://{$this->subdomain}.{$baseDomain}";
    }

    /** Direct public landing page URL */
    public function getLandingUrlAttribute(): string
    {
        return url('/school/' . $this->slug);
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
        'rato_bangala' => ['label' => 'Rato Bangala Heritage Crimson', 'primary' => '#8b151b', 'secondary' => '#c29d38'],
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

    /** Get founder/director photo URL with intelligent fallbacks */
    public function getFounderPhotoUrlAttribute(): ?string
    {
        if (!empty($this->settings['founder_image'])) {
            return asset('storage/' . $this->settings['founder_image']);
        }
        if ($this->owner && $this->owner->photo) {
            return asset('storage/' . $this->owner->photo);
        }
        return null;
    }

    /** Get founder name */
    public function getFounderNameResolvedAttribute(): ?string
    {
        return $this->settings['founder_name'] ?? null;
    }

    /** Get founder title */
    public function getFounderTitleResolvedAttribute(): string
    {
        return $this->settings['founder_title'] ?? 'Founder & Managing Director';
    }

    /** Get founder message */
    public function getFounderMessageResolvedAttribute(): ?string
    {
        return $this->settings['founder_message'] ?? null;
    }

    /** Convert YouTube / Vimeo or local video URL to clean responsive embed URL */
    public function getCampusVideoEmbedUrlAttribute(): ?string
    {
        $url = $this->settings['campus_video_url'] ?? null;
        if (empty($url)) return null;

        // YouTube watch URL: https://www.youtube.com/watch?v=XYZ
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $url, $match)) {
            return 'https://www.youtube.com/embed/' . $match[1] . '?rel=0&modestbranding=1';
        }

        // Vimeo: https://vimeo.com/123456789
        if (preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)/i', $url, $match)) {
            return 'https://player.vimeo.com/video/' . $match[3];
        }

        return $url;
    }

    /** Check if campus video is MP4 / direct file */
    public function getIsCampusVideoDirectFileAttribute(): bool
    {
        $url = $this->settings['campus_video_url'] ?? '';
        return (bool) preg_match('/\.(mp4|webm|ogg|mov)$/i', $url);
    }

    /** Admission announcement banner accessors */
    public function getAdmissionBannerActiveAttribute(): bool
    {
        return !empty($this->settings['admission_banner_active']);
    }

    public function getAdmissionBannerTextAttribute(): string
    {
        return $this->settings['admission_banner_text'] ?? 'PRE-KINDERGARTEN & GRADE XI NOW ACCEPTING APPLICATIONS';
    }

    public function getAdmissionBannerBtnTextAttribute(): string
    {
        return $this->settings['admission_banner_btn_text'] ?? 'CLICK HERE TO APPLY NOW';
    }

    public function getAdmissionBannerBtnUrlAttribute(): string
    {
        return $this->settings['admission_banner_btn_url'] ?? url('admissions/apply?school=' . $this->slug);
    }

    /** College counseling banner accessors (Rato Bangala style) */
    public function getCollegeCounselingActiveAttribute(): bool
    {
        return !isset($this->settings['college_counseling_active']) || !empty($this->settings['college_counseling_active']);
    }

    public function getCollegeCounselingTextAttribute(): string
    {
        return $this->settings['college_counseling_text'] ?? 'College Counseling & Global Placements: Mentoring students for Ivy League, leading universities abroad, SAT/AP guidance & competitive scholarships.';
    }

    public function getCollegeCounselingBtnTextAttribute(): string
    {
        return $this->settings['college_counseling_btn_text'] ?? 'Explore Placements & Apply';
    }

    public function getCollegeCounselingBtnUrlAttribute(): string
    {
        return $this->settings['college_counseling_btn_url'] ?? url('admissions/apply?school=' . $this->slug);
    }

    /** Newsletter subscription accessors */
    public function getNewsletterActiveAttribute(): bool
    {
        return !isset($this->settings['newsletter_active']) || !empty($this->settings['newsletter_active']);
    }

    public function getNewsletterTitleAttribute(): string
    {
        return $this->settings['newsletter_title'] ?? 'Join our Newsletter';
    }

    public function getNewsletterDescAttribute(): string
    {
        return $this->settings['newsletter_desc'] ?? 'Subscribe to our official newsletter to receive latest academic circulars, event dispatches, and student achievements directly in your inbox.';
    }

    /** RBPOP / Outreach & History accessors (Rato Bangala style) */
    public function getOutreachActiveAttribute(): bool
    {
        return !isset($this->settings['outreach_active']) || !empty($this->settings['outreach_active']);
    }

    public function getOutreachTagAttribute(): string
    {
        return $this->settings['outreach_tag'] ?? 'RBPOP';
    }

    public function getOutreachTitleAttribute(): string
    {
        return $this->settings['outreach_title'] ?? 'RATO BANGALA PARTNERSHIP IN OUTREACH PROGRAM';
    }

    public function getOutreachDescAttribute(): string
    {
        if (!empty($this->settings['outreach_desc'])) {
            return $this->settings['outreach_desc'];
        }
        if (!empty($this->history)) {
            return $this->history;
        }
        return 'The Rato Bangala Partnership in Outreach Programme (RBPOP) is a UNESCO awarded public-private partnership programme of Rato Bangala School that works to uplift the education of Nepali children beyond the school itself. It was started with inputs from RBS parents, teachers and students in 2003.';
    }

    public function getOutreachBtnTextAttribute(): string
    {
        return $this->settings['outreach_btn_text'] ?? 'More On RBPOP';
    }

    public function getOutreachBtnUrlAttribute(): string
    {
        return $this->settings['outreach_btn_url'] ?? url('school/' . $this->slug . '/about#history');
    }

    /**
     * Get section image URL with auto-fallback to default asset.
     * When uploaded by school admin, it is stored as an optimized .webp file.
     */
    public function sectionImage(string $key, string $defaultAsset): string
    {
        $custom = $this->settings['section_images'][$key] ?? null;
        if ($custom && Storage::disk('public')->exists($custom)) {
            return asset('storage/' . $custom);
        }
        return asset($defaultAsset);
    }

    /**
     * Check if a custom section image has been uploaded
     */
    public function hasCustomSectionImage(string $key): bool
    {
        $custom = $this->settings['section_images'][$key] ?? null;
        return !empty($custom) && Storage::disk('public')->exists($custom);
    }

    public function getHeritageTaglineAttribute(): string
    {
        return $this->settings['heritage_tagline'] ?? ('CENTER OF EXCELLENCE SINCE ' . ($this->settings['established_year'] ?? $this->established_year ?? '1992'));
    }

    public function getEstablishedYearResolvedAttribute(): string
    {
        return (string) ($this->settings['established_year'] ?? $this->established_year ?? '1992');
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

    public function googleConfig(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SchoolGoogleConfig::class);
    }

    /**
     * Get structured publications, blogs, news articles, and student stories for landing pages.
     */
    public function getLandingArticles(?string $type = null, ?string $category = null): array
    {
        $schoolName = $this->name;
        $city = ucwords(strtolower($this->city ?? ($this->district ?? 'Nepal')));
        $principalName = $this->principal_name_resolved;
        $principalPhoto = $this->principal_photo_url;
        $estdYear = $this->established_year ?? '1992';

        $defaults = [
            // ── 1. SCHOOL BLOGS & ACADEMIC ESSAYS ─────────────────────────
            [
                'slug'           => 'pedagogical-inquiry-modern-classroom',
                'type'           => 'blog',
                'type_label'     => 'School Blog',
                'category'       => 'Pedagogy & Learning',
                'title'          => 'Fostering Critical Inquiry: Why Questions Matter More Than Answers',
                'excerpt'        => "In an era of rapid technological disruption, mere memorization is obsolete. At {$schoolName}, progressive education centers on student inquiry, scientific reasoning, and conceptual mastery.",
                'author_name'    => $principalName,
                'author_role'    => 'Principal & Academic Lead',
                'author_avatar'  => $principalPhoto,
                'published_date' => 'Nov 04, 2026',
                'read_time'      => '5 min read',
                'featured'       => true,
                'image'          => $this->sectionImage('pub_blogs_image', 'imgs/hero/hero_1.webp'),
                'tags'           => ['Pedagogy', 'Inquiry-Based Learning', 'Critical Thinking', 'Future Skills'],
                'content'        => "
                    <p class=\"lead text-lg font-medium text-slate-700 leading-relaxed mb-6\">
                        When visitors step onto the grounds of {$schoolName}, one of the first things they observe in our classrooms is not silence, but thoughtful dialogue. The 21st-century world does not reward those who memorize predictable answers; it elevates those who ask brave, insightful, and boundary-pushing questions.
                    </p>
                    <h3 class=\"text-2xl font-extrabold text-slate-900 mt-8 mb-4\">The Shift from Rote Learning to Intellectual Discovery</h3>
                    <p class=\"text-slate-600 leading-relaxed mb-4\">
                        Traditional pedagogical models in South Asia were built for an industrial paradigm where compliance was valued over curiosity. At {$schoolName}, we invert this philosophy. Whether exploring gravitational kinematics in our physics laboratory or analyzing ancient South Asian historiography, students are challenged to construct hypotheses, critique assumptions, and defend their conclusions using empirical evidence.
                    </p>
                    <blockquote class=\"my-8 p-6 bg-slate-50 border-l-4 border-[#8b151b] rounded-r-2xl italic text-slate-800 text-lg font-serif\">
                        “Education is not the learning of facts, but the training of the mind to think.” — We believe that every child who passes through our doors possesses an innate spark of intellectual courage.
                    </blockquote>
                    <h3 class=\"text-2xl font-extrabold text-slate-900 mt-8 mb-4\">Building Lifelong Metacognitive Skills</h3>
                    <p class=\"text-slate-600 leading-relaxed mb-4\">
                        Our faculty view themselves not as repositories of knowledge, but as mentors and co-investigators. By integrating seminar-style Socratic dialogue, interdisciplinary STEAM projects, and reflective learning journals across grades 1 through 12, scholars learn how to learn. They develop what educational theorists call metacognition—the capacity to evaluate one's own intellectual biases, refine strategies, and persist through challenging problems.
                    </p>
                    <p class=\"text-slate-600 leading-relaxed\">
                        As we look toward the decades ahead, our commitment remains steadfast: to cultivate graduates who are not only intellectually formidable, but deeply compassionate ethical stewards of their communities.
                    </p>
                ",
            ],
            [
                'slug'           => 'bridging-humanities-and-artificial-intelligence',
                'type'           => 'blog',
                'type_label'     => 'School Blog',
                'category'       => 'STEAM & Technology',
                'title'          => 'Code Meets Conscience: Integrating Ethics into STEAM & Robotics',
                'excerpt'        => "How our modern computing and robotics labs encourage scholars not merely to build algorithmic systems, but to interrogate their social, ethical, and human impact.",
                'author_name'    => 'Faculty of Computing & Innovation',
                'author_role'    => 'STEM Department Lead',
                'author_avatar'  => asset('imgs/hero/hero_2.webp'),
                'published_date' => 'Oct 29, 2026',
                'read_time'      => '4 min read',
                'featured'       => false,
                'image'          => $this->sectionImage('facility_computer_image', 'imgs/hero/hero_3.webp'),
                'tags'           => ['AI & Robotics', 'Digital Literacy', 'Tech Ethics', 'STEAM'],
                'content'        => "
                    <p class=\"lead text-lg font-medium text-slate-700 leading-relaxed mb-6\">
                        At {$schoolName}, our coding curriculum begins far beyond syntax. While our students write Python routines, program microcontrollers, and design machine learning models, the foundational question remains: <em>To what human end?</em>
                    </p>
                    <h3 class=\"text-2xl font-extrabold text-slate-900 mt-8 mb-4\">Hands-On Technological Fluency</h3>
                    <p class=\"text-slate-600 leading-relaxed mb-4\">
                        Technological literacy in the modern era requires both mechanical competence and humanistic discernment. In our high-speed AI laboratory, students study automated decision algorithms alongside case studies in algorithmic fairness, data privacy, and digital civic responsibility.
                    </p>
                    <p class=\"text-slate-600 leading-relaxed\">
                        By uniting engineering rigour with ethical philosophy, {$schoolName} ensures that our future software developers, bioengineers, and entrepreneurs wield technology as an instrument of equity and community uplift.
                    </p>
                ",
            ],
            [
                'slug'           => 'the-golden-window-early-childhood-brain-development',
                'type'           => 'blog',
                'type_label'     => 'School Blog',
                'category'       => 'Early Childhood',
                'title'          => 'The Golden Window: Why Play-Based Sensorial Inquiry Shapes Lifelong Learners',
                'excerpt'        => "Neuroscience reveals that 90% of brain architecture develops before age six. A look inside our Early Childhood Centre's child-centered sensorial environment.",
                'author_name'    => 'Early Childhood Faculty',
                'author_role'    => 'Early Childhood Coordinator',
                'author_avatar'  => asset('imgs/rato-bangala/ecc.jpg'),
                'published_date' => 'Oct 18, 2026',
                'read_time'      => '6 min read',
                'featured'       => false,
                'image'          => $this->sectionImage('program_ecc_image', 'imgs/rato-bangala/ecc.jpg'),
                'tags'           => ['Early Childhood', 'Montessori', 'Sensorial Play', 'Parenting'],
                'content'        => "
                    <p class=\"lead text-lg font-medium text-slate-700 leading-relaxed mb-6\">
                        The early years are not merely a prelude to academic schooling; they are the fertile ground where curiosity, motor neural mapping, and emotional security are permanently anchored.
                    </p>
                    <h3 class=\"text-2xl font-extrabold text-slate-900 mt-8 mb-4\">Natural Wonder as a Pedagogical Foundation</h3>
                    <p class=\"text-slate-600 leading-relaxed mb-4\">
                        In our dedicated Early Childhood Centre, learning is structured through immersive tactile discovery. Children pour sand, manipulate Montessori beads, listen to bilingual folklore, and plant seeds in our campus garden. Every activity builds spatial reasoning, emotional vocabulary, and cooperative socialization.
                    </p>
                    <p class=\"text-slate-600 leading-relaxed\">
                        When children experience joy and agency in their earliest encounters with learning, they internalize a lifelong conviction that the universe is an exciting place to be explored.
                    </p>
                ",
            ],
            [
                'slug'           => 'cultivating-deep-reading-in-a-distracted-world',
                'type'           => 'blog',
                'type_label'     => 'School Blog',
                'category'       => 'Literary & Humanities',
                'title'          => 'The Lost Art of Deep Reading: Cultivating Lifelong Book Lovers',
                'excerpt'        => "From quiet library alcoves to analytical seminar circles, how our reading culture nurtures focus, empathy, and intellectual resilience in a fragmented digital landscape.",
                'author_name'    => 'Department of English & Humanities',
                'author_role'    => 'Senior Literature Faculty',
                'author_avatar'  => asset('imgs/hero/hero_1.webp'),
                'published_date' => 'Sep 25, 2026',
                'read_time'      => '4 min read',
                'featured'       => false,
                'image'          => $this->sectionImage('facility_library_image', 'imgs/hero/hero_1.webp'),
                'tags'           => ['Reading Culture', 'Library', 'Humanities', 'Empathy'],
                'content'        => "
                    <p class=\"lead text-lg font-medium text-slate-700 leading-relaxed mb-6\">
                        In a world dominated by fifteen-second algorithmic videos, sustained deep reading has become a quiet act of intellectual rebellion.
                    </p>
                    <h3 class=\"text-2xl font-extrabold text-slate-900 mt-8 mb-4\">The Sanctuary of the Printed Page</h3>
                    <p class=\"text-slate-600 leading-relaxed mb-4\">
                        Our Central Library houses over 15,000 curated volumes spanning world literature, Nepali poetry, scientific treatises, and historical archives. Weekly library periods are sacred times of sustained silent reading, followed by book club circles where scholars dissect character motivations, historical subtext, and thematic symbolism.
                    </p>
                    <p class=\"text-slate-600 leading-relaxed\">
                        By immersing young minds in rich, complex prose, we teach them how to inhabit the lives of others—a vital prerequisite for moral empathy in a divided world.
                    </p>
                ",
            ],

            // ── 2. CAMPUS NEWS & PRESS RELEASES ───────────────────────────
            [
                'slug'           => 'stem-innovation-fair-2026-triumphant-conclusion',
                'type'           => 'news',
                'type_label'     => 'Campus News',
                'category'       => 'Campus Milestone',
                'title'          => 'Annual Science & Robotics Fair 2026 Concludes with 48 Innovative Student Inventions',
                'excerpt'        => "Over 600 visiting parents, educators, and university jury members witnessed autonomous solar rovers, eco-filtering prototypes, and smart IoT campus systems designed by our scholars.",
                'author_name'    => 'Campus News Desk',
                'author_role'    => 'Communications Office',
                'author_avatar'  => asset('imgs/hero/hero_2.webp'),
                'published_date' => 'Nov 02, 2026',
                'read_time'      => '3 min read',
                'featured'       => true,
                'image'          => $this->sectionImage('pathway_steam_image', 'imgs/kathmandu.webp'),
                'tags'           => ['Science Fair', 'Robotics', 'Innovation', 'Student Awards'],
                'content'        => "
                    <p class=\"lead text-lg font-medium text-slate-700 leading-relaxed mb-6\">
                        The annual {$schoolName} STEM & Robotics Fair celebrated its largest edition yet, turning the campus auditorium and sports grounds into a thriving hub of scientific experimentation.
                    </p>
                    <h3 class=\"text-2xl font-extrabold text-slate-900 mt-8 mb-4\">Jury Commendations and Grand Prizes</h3>
                    <p class=\"text-slate-600 leading-relaxed mb-4\">
                        Projects were judged by visiting researchers from national universities and technology incubators. Grand prize honours were awarded to a Grade 10 team for their low-cost bio-sand filtration monitor utilizing IoT sensors to test drinking water purity across Himalayan watersheds.
                    </p>
                    <p class=\"text-slate-600 leading-relaxed\">
                        The principal presented awards to all top contenders, reiterating {$schoolName}'s commitment to funding ongoing prototyping through our dedicated student research grant.
                    </p>
                ",
            ],
            [
                'slug'           => 'academic-board-excellence-see-neb-honors',
                'type'           => 'news',
                'type_label'     => 'Campus News',
                'category'       => 'Academic Honors',
                'title'          => 'Celebrating 100% Board Results: Graduating Cohort Sets Exceptional Benchmark',
                'excerpt'        => "Our graduating class achieved record-setting distinction marks across national SEE and NEB +2 examinations, with numerous scholars ranking among the highest percentiles in the nation.",
                'author_name'    => 'Academic Council',
                'author_role'    => 'Examination Board',
                'author_avatar'  => $principalPhoto,
                'published_date' => 'Oct 20, 2026',
                'read_time'      => '3 min read',
                'featured'       => false,
                'image'          => $this->sectionImage('pathway_secondary_image', file_exists(public_path('imgs/hero/school_hero_graduates.jpg')) ? 'imgs/hero/school_hero_graduates.jpg' : 'imgs/hero/hero_2.webp'),
                'tags'           => ['Board Exam', 'SEE Distinction', 'NEB Honors', 'Academic Excellence'],
                'content'        => "
                    <p class=\"lead text-lg font-medium text-slate-700 leading-relaxed mb-6\">
                        The National Examination Board results have once again reaffirmed the rigor, resilience, and intellectual discipline cultivated at {$schoolName}.
                    </p>
                    <h3 class=\"text-2xl font-extrabold text-slate-900 mt-8 mb-4\">Exceptional GPA Distributions</h3>
                    <p class=\"text-slate-600 leading-relaxed mb-4\">
                        100% of our candidate cohort passed in the First Division and Distinction tiers. Special congratulations to our science and management toppers who secured straight-A GPA distributions, alongside exceptional practical laboratory scores.
                    </p>
                    <p class=\"text-slate-600 leading-relaxed\">
                        We express our deepest gratitude to our faculty and families whose unwavering support formed the bedrock of this triumph.
                    </p>
                ",
            ],
            [
                'slug'           => 'championship-victory-inter-school-athletics-arena',
                'type'           => 'news',
                'type_label'     => 'Campus News',
                'category'       => 'Sports & Athletics',
                'title'          => 'Trophy Victorious: School Athletics Squad Sweeps Valley Championship',
                'excerpt'        => "Demonstrating exceptional stamina, teamwork, and sportsmanship, our student-athletes brought home 14 gold medals and the prestigious Inter-School Championship Cup.",
                'author_name'    => 'Department of Physical Education',
                'author_role'    => 'Athletics Department',
                'author_avatar'  => asset('imgs/schools.webp'),
                'published_date' => 'Oct 12, 2026',
                'read_time'      => '2 min read',
                'featured'       => false,
                'image'          => $this->sectionImage('facility_sports_image', 'imgs/schools.webp'),
                'tags'           => ['Athletics', 'Football', 'Sports Cup', 'Championship'],
                'content'        => "
                    <p class=\"lead text-lg font-medium text-slate-700 leading-relaxed mb-6\">
                        Over three days of intense competition against sixteen prestigious institutions, {$schoolName}'s track and field and football teams claimed the overall championship title.
                    </p>
                    <p class=\"text-slate-600 leading-relaxed mb-4\">
                        Key highlights included record-breaking 400m sprint times, a dramatic penalty-shootout victory in the Senior Boys Football final, and an undefeated tournament run by our Girls Basketball team.
                    </p>
                    <p class=\"text-slate-600 leading-relaxed\">
                        Athletics at our school is not an extracurricular diversion, but a cornerstone of character, resilience, and collaborative spirit.
                    </p>
                ",
            ],
            [
                'slug'           => 'unesco-recognized-outreach-partnership-expands',
                'type'           => 'news',
                'type_label'     => 'Campus News',
                'category'       => 'Outreach & Community',
                'title'          => 'Outreach Initiative Expands: Educational Partnership Reaches 700+ Rural Schools',
                'excerpt'        => "Building on over two decades of educational outreach, our flagship program extends progressive teacher training and experiential classroom resources across 9 districts.",
                'author_name'    => 'Outreach Steering Committee',
                'author_role'    => 'Community Outreach',
                'author_avatar'  => asset('imgs/rato-bangala/rcpop_visual.png'),
                'published_date' => 'Sep 15, 2026',
                'read_time'      => '4 min read',
                'featured'       => false,
                'image'          => $this->sectionImage('outreach_visual_image', 'imgs/rato-bangala/rcpop_visual.png'),
                'tags'           => ['Outreach', 'UNESCO', 'Teacher Training', 'Community Service'],
                'content'        => "
                    <p class=\"lead text-lg font-medium text-slate-700 leading-relaxed mb-6\">
                        True institutional excellence is measured not merely by what happens within our campus gates, but by our contribution to the broader educational ecosystem of Nepal.
                    </p>
                    <p class=\"text-slate-600 leading-relaxed mb-4\">
                        The UNESCO-recognized Outreach Programme commenced its 2026 cycle with multi-day pedagogical workshops focusing on hands-on math teaching, low-cost science experiments, and child-centered classroom management in rural community schools.
                    </p>
                    <p class=\"text-slate-600 leading-relaxed\">
                        We applaud our teachers and student volunteers who continue to embody the principle that every child in Nepal deserves world-class quality instruction.
                    </p>
                ",
            ],

            // ── 3. STUDENT STORIES & ALUMNI VOICES ────────────────────────
            [
                'slug'           => 'from-campus-to-world-stage-a-scholars-journey',
                'type'           => 'story',
                'type_label'     => 'Student Story',
                'category'       => 'Alumni Spotlight',
                'title'          => 'From Campus Corridors to Global Ambition: An Alumnus Reflects on Foundation',
                'excerpt'        => "Graduating scholar credits our culture of fearless inquiry, debating society, and laboratory mentorship for securing a fully-funded international fellowship.",
                'author_name'    => 'Prerana K. (Alumni Class of 2022)',
                'author_role'    => 'Rhodes / International Scholar',
                'author_avatar'  => asset('imgs/hero/school_hero_graduates.jpg'),
                'published_date' => 'Nov 01, 2026',
                'read_time'      => '5 min read',
                'featured'       => true,
                'image'          => $this->sectionImage('pub_circle_image', file_exists(public_path('imgs/hero/school_hero_graduates.jpg')) ? 'imgs/hero/school_hero_graduates.jpg' : 'imgs/schools.webp'),
                'tags'           => ['Alumni Story', 'Global Fellowship', 'Inspiration', 'College Counseling'],
                'content'        => "
                    <p class=\"lead text-lg font-medium text-slate-700 leading-relaxed mb-6\">
                        Looking back across my years at {$schoolName}, the most enduring lesson was never found on a blackboard. It was the unwavering encouragement from my teachers to challenge conventional wisdom without fear of failure.
                    </p>
                    <h3 class=\"text-2xl font-extrabold text-slate-900 mt-8 mb-4\">Mentorship that Transcended Syllabi</h3>
                    <p class=\"text-slate-600 leading-relaxed mb-4\">
                        When I decided to launch our first environmental debate council in Grade 11, my teachers stayed after school to help draft constitution resolutions and debate rules. That small initiative laid the foundation for my international university research in environmental economics.
                    </p>
                    <p class=\"text-slate-600 leading-relaxed\">
                        To current students walking these halls: embrace the laboratories, write for the magazines, and speak up in class. You are gaining far more than diplomas; you are cultivating an identity.
                    </p>
                ",
            ],
            [
                'slug'           => 'unfiltered-voices-what-mun-taught-me-about-diplomacy',
                'type'           => 'story',
                'type_label'     => 'Student Story',
                'category'       => 'Student Voices',
                'title'          => 'Stepping Up to the Podium: How Model United Nations Changed My Worldview',
                'excerpt'        => "A Grade 11 delegate recounts representing Nepal on the UN Human Rights Council and learning the delicate craft of consensus, active listening, and passionate rhetoric.",
                'author_name'    => 'Aarav S. (Grade 11 Scholar)',
                'author_role'    => 'Secretary General, MUN Society',
                'author_avatar'  => asset('imgs/hero/hero_2.webp'),
                'published_date' => 'Oct 14, 2026',
                'read_time'      => '4 min read',
                'featured'       => false,
                'image'          => $this->sectionImage('admissions_slide3_image', 'imgs/hero/hero_2.webp'),
                'tags'           => ['Student Voice', 'MUN', 'Public Speaking', 'Leadership'],
                'content'        => "
                    <p class=\"lead text-lg font-medium text-slate-700 leading-relaxed mb-6\">
                        The gavel tapped twice, signaling the commencement of unmoderated caucus. My heart raced, but as I stepped onto the floor, six months of school research and rhetoric coaching took over.
                    </p>
                    <p class=\"text-slate-600 leading-relaxed mb-4\">
                        Model United Nations at {$schoolName} is not a simulation of politicians; it is an exercise in radical empathy. To draft a resolution, you must understand the historical trauma, economic constraints, and strategic imperatives of a country ten thousand miles away.
                    </p>
                    <p class=\"text-slate-600 leading-relaxed\">
                        Through debate, I learned that eloquence without substance is hollow, but knowledge articulated with empathy has the power to bridge any divide.
                    </p>
                ",
            ],
            [
                'slug'           => 'ten-days-in-the-hills-lessons-from-our-service-camp',
                'type'           => 'story',
                'type_label'     => 'Student Story',
                'category'       => 'Experiential Learning',
                'title'          => 'Beyond the Horizon: What Ten Days in a Rural Village Taught Our Cohort',
                'excerpt'        => "Twelve students and two faculty mentors share transformative moments renovating school libraries, teaching basic digital literacy, and learning from Himalayan communities.",
                'author_name'    => 'Grade 10 Social Service Cohort',
                'author_role'    => 'Student Outreach Delegation',
                'author_avatar'  => asset('imgs/rato-bangala/admissions_kids.jpg'),
                'published_date' => 'Sep 28, 2026',
                'read_time'      => '5 min read',
                'featured'       => false,
                'image'          => $this->sectionImage('admissions_slide1_image', 'imgs/rato-bangala/admissions_kids.jpg'),
                'tags'           => ['Community Service', 'Experiential Learning', 'Nepal', 'Character'],
                'content'        => "
                    <p class=\"lead text-lg font-medium text-slate-700 leading-relaxed mb-6\">
                        No textbook can prepare you for the warmth of a community that welcomes you with fresh tea and open arms after a seven-hour mountain trail hike.
                    </p>
                    <p class=\"text-slate-600 leading-relaxed mb-4\">
                        Our annual Grade 10 Community Service Camp took us to a rural secondary school in Sindhupalchok. We carried 300 books donated by our campus book drive, installed solar reading lamps, and taught foundational scratch programming on laptops. But what we received in return—lessons in gratitude, communal solidarity, and resilience—was immeasurably greater.
                    </p>
                    <p class=\"text-slate-600 leading-relaxed\">
                        {$schoolName} taught us that privilege is not an entitlement; it is a profound responsibility to serve.
                    </p>
                ",
            ],
            [
                'slug'           => 'painting-our-identity-the-studio-arts-collective',
                'type'           => 'story',
                'type_label'     => 'Student Story',
                'category'       => 'Creative Arts',
                'title'          => 'Colors of Heritage: Why the Visual Arts Studio is My Second Home',
                'excerpt'        => "From traditional Mithila and Paubha motifs to contemporary oil canvases, student artists explore identity, culture, and expressive voice in the campus studio.",
                'author_name'    => 'Student Fine Arts Guild',
                'author_role'    => 'Arts Department',
                'author_avatar'  => asset('imgs/hero/hero_3.webp'),
                'published_date' => 'Sep 08, 2026',
                'read_time'      => '3 min read',
                'featured'       => false,
                'image'          => $this->sectionImage('pub_cornice_image', 'imgs/hero/hero_3.webp'),
                'tags'           => ['Fine Arts', 'Nepali Heritage', 'Creativity', 'Cornice'],
                'content'        => "
                    <p class=\"lead text-lg font-medium text-slate-700 leading-relaxed mb-6\">
                        Natural light floods the fourth-floor art studio every afternoon, illuminating rows of wooden easels, ceramic wheels, and the intoxicating scent of oil paints and clay.
                    </p>
                    <p class=\"text-slate-600 leading-relaxed mb-4\">
                        Our visual arts mentor taught us that artistic technique is merely grammar; what you say with it is your soul. Here, scholars synthesize ancient Nepali geometric traditions with modern conceptual surrealism, preparing works for the annual Cornice literary and fine art publication.
                    </p>
                    <p class=\"text-slate-600 leading-relaxed\">
                        In creating art, we do not merely paint canvases; we discover who we are.
                    </p>
                ",
            ],

            // ── 4. OFFICIAL MAGAZINES & LITERARY PUBLICATIONS ─────────────
            [
                'slug'           => 'cornice-annual-student-anthology',
                'type'           => 'magazine',
                'type_label'     => 'Official Magazine',
                'category'       => 'Annual Literary Journal',
                'title'          => 'Cornice: Annual Student Anthology of Creative Prose, Poetry & Fine Artwork',
                'excerpt'        => "Published continuously each academic year, Cornice curates the finest student essays, original poetry in Nepali and English, and fine artwork across all grades.",
                'author_name'    => 'Student Editorial Board',
                'author_role'    => 'Editors of Cornice',
                'author_avatar'  => asset('imgs/hero/hero_3.webp'),
                'published_date' => 'Oct 2026 Issue',
                'read_time'      => '12 min browse',
                'featured'       => true,
                'image'          => $this->sectionImage('pub_cornice_image', 'imgs/hero/hero_3.webp'),
                'tags'           => ['Cornice', 'Poetry', 'Fine Artwork', 'Student Anthology'],
                'content'        => "
                    <p class=\"lead text-lg font-medium text-slate-700 leading-relaxed mb-6\">
                        Cornice stands as our hallmark annual anthology celebrating student creativity, literary courage, and artistic expression.
                    </p>
                    <p class=\"text-slate-600 leading-relaxed mb-4\">
                        Produced under the mentorship of our literature faculty, Cornice brings together original sonnets, short fiction, personal memoirs, and exquisite studio photography crafted by scholars from primary grades through +2 graduation.
                    </p>
                    <p class=\"text-slate-600 leading-relaxed\">
                        Digital editions are available online, with bound commemorative print editions distributed to the school archives, partner libraries, and parents.
                    </p>
                ",
            ],
            [
                'slug'           => 'the-circle-flagship-campus-magazine',
                'type'           => 'magazine',
                'type_label'     => 'Official Magazine',
                'category'       => 'Editorial Review',
                'title'          => 'The Circle: Official Campus Journalism, Interviews & Features Magazine',
                'excerpt'        => "Produced entirely by our Student Editorial Council, covering sports triumphs, faculty features, investigative campus reports, and cultural commentary.",
                'author_name'    => 'Student Editorial Council',
                'author_role'    => 'Journalism Guild',
                'author_avatar'  => asset('imgs/hero/school_hero_graduates.jpg'),
                'published_date' => 'Sep 2026 Issue',
                'read_time'      => '10 min browse',
                'featured'       => true,
                'image'          => $this->sectionImage('pub_circle_image', file_exists(public_path('imgs/hero/school_hero_graduates.jpg')) ? 'imgs/hero/school_hero_graduates.jpg' : 'imgs/schools.webp'),
                'tags'           => ['The Circle', 'Campus Journalism', 'Features', 'Editorial'],
                'content'        => "
                    <p class=\"lead text-lg font-medium text-slate-700 leading-relaxed mb-6\">
                        The Circle is the authentic voice of student life, inquiry, and journalism at {$schoolName}.
                    </p>
                    <p class=\"text-slate-600 leading-relaxed mb-4\">
                        Featuring hard-hitting campus reporting, exclusive long-form faculty interviews, photographic retrospectives of sports championships, and thought-provoking editorial columns on contemporary global topics.
                    </p>
                    <p class=\"text-slate-600 leading-relaxed\">
                        The Student Editorial Council manages layout, peer reviews, typography design, and interview curation, providing scholars real-world publishing experience.
                    </p>
                ",
            ],
            [
                'slug'           => 'quarterly-administrative-dispatch',
                'type'           => 'magazine',
                'type_label'     => 'Official Magazine',
                'category'       => 'Quarterly Dispatch',
                'title'          => 'Quarterly Newsletter: Campus Dispatches & Administrative Milestones',
                'excerpt'        => "Comprehensive seasonal report updating parents, alumni, and patrons on curriculum enhancements, campus renovations, examination routines, and student accolades.",
                'author_name'    => 'Office of Communications',
                'author_role'    => 'Institutional Administration',
                'author_avatar'  => $principalPhoto,
                'published_date' => 'Fall 2026 Edition',
                'read_time'      => '6 min browse',
                'featured'       => false,
                'image'          => $this->sectionImage('pub_newsletter_image', 'imgs/hero/hero_2.webp'),
                'tags'           => ['Newsletter', 'Quarterly Dispatch', 'Administrative Report'],
                'content'        => "
                    <p class=\"lead text-lg font-medium text-slate-700 leading-relaxed mb-6\">
                        Our quarterly newsletter keeps our extended community seamlessly informed of school governance, curriculum innovations, and student achievements.
                    </p>
                    <p class=\"text-slate-600 leading-relaxed mb-4\">
                        Includes reports from the Principal, infrastructural updates regarding new science lab equipment, parent-teacher association schedules, and key calendar dates for upcoming terminal examinations.
                    </p>
                ",
            ],
        ];

        // Merge custom articles from settings if any
        $custom = $this->settings['custom_articles'] ?? [];
        if (!empty($custom) && is_array($custom)) {
            $defaults = array_merge($custom, $defaults);
        }

        // Filter by type if requested
        if ($type) {
            $defaults = array_values(array_filter($defaults, function ($item) use ($type) {
                return $item['type'] === $type;
            }));
        }

        // Filter by category if requested
        if ($category) {
            $defaults = array_values(array_filter($defaults, function ($item) use ($category) {
                return strtolower($item['category']) === strtolower($category);
            }));
        }

        return $defaults;
    }

    /**
     * Resolve a single landing article by slug.
     */
    public function getLandingArticleBySlug(string $slug): ?array
    {
        $articles = $this->getLandingArticles();
        foreach ($articles as $article) {
            if ($article['slug'] === $slug) {
                return $article;
            }
        }
        return null;
    }

    /**
     * Helper to resolve section name for a student in a class.
     */
    public function getStudentSectionName($studentId, $classId): string
    {
        try {
            $promo = \App\Models\Promotion::where('student_id', $studentId)
                ->where('class_id', $classId)
                ->with('section')
                ->first();
            if ($promo && $promo->section && !empty($promo->section->section_name)) {
                return $promo->section->section_name;
            }
        } catch (\Throwable $e) {}

        return 'Section A';
    }

    /**
     * Format a single academic ranker object.
     */
    public function formatRankerRecord(
        int $rank,
        string $name,
        ?string $photo,
        string $className,
        $classId,
        string $sectionName,
        $sectionId,
        $gpa,
        $percentage,
        $totalMarks,
        string $termName,
        ?string $honors = null
    ): array {
        $rankLabels = [1 => '1st Rank', 2 => '2nd Rank', 3 => '3rd Rank'];
        $medalTitles = [
            1 => 'Gold Medalist · Academic Topper',
            2 => 'Silver Medalist · Distinction',
            3 => 'Bronze Medalist · Honors'
        ];
        $defaultHonors = [
            1 => 'Outstanding Academic Distinction · Campus Scholar',
            2 => 'First Division with Distinction',
            3 => 'Academic Merit & Excellence Award'
        ];

        $cleanGpa = ($gpa !== null && $gpa !== '') ? number_format((float)$gpa, 2) : '3.96';
        $cleanPct = ($percentage !== null && $percentage !== '') ? number_format((float)$percentage, 1) . '%' : '95.4%';
        
        $cleanSection = trim($sectionName);
        if ($cleanSection === '' || $cleanSection === '0') {
            $cleanSection = 'Section A';
        } elseif (!str_starts_with(strtolower($cleanSection), 'sec') && !str_contains(strtolower($cleanSection), 'section')) {
            $cleanSection = 'Section ' . $cleanSection;
        }

        if (empty($photo)) {
            $colors = [1 => 'b45309', 2 => '475569', 3 => '92400e'];
            $bg = $colors[$rank] ?? '0b417e';
            $photo = "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background={$bg}&color=fff&size=160&bold=true";
        }

        return [
            'rank'          => $rank,
            'rank_label'    => $rankLabels[$rank] ?? "{$rank}th Rank",
            'medal_title'   => $medalTitles[$rank] ?? 'Merit Achiever',
            'student_name'  => $name,
            'student_photo' => $photo,
            'class_id'      => $classId,
            'class_name'    => $className ?: 'General',
            'section_id'    => $sectionId,
            'section_name'  => $cleanSection,
            'gpa'           => $cleanGpa,
            'percentage'    => $cleanPct,
            'total_marks'   => $totalMarks ? (string)$totalMarks : null,
            'term_name'     => $termName ?: 'Terminal Examination',
            'honors'        => $honors ?: ($defaultHonors[$rank] ?? 'Academic Merit Award'),
        ];
    }

    /**
     * Automatically retrieve and format top 1st, 2nd, and 3rd rank achievers with class and section.
     */
    public function getTopRankersData(?int $filterClassId = null): array
    {
        $settings = $this->settings ?? [];
        $isActive = (bool) ($settings['topper_ranks_active'] ?? true);
        $mode = $settings['topper_ranks_mode'] ?? 'auto';
        $customTitle = $settings['topper_ranks_title'] ?? 'Academic Hall of Fame · Top Rankers';
        $customSubtitle = $settings['topper_ranks_subtitle'] ?? 'Celebrating academic dedication, discipline, and intellectual brilliance across all classes & sections.';
        $examTerm = $settings['topper_ranks_exam_term'] ?? null;

        // 1. Manual Spotlight Mode
        if ($mode === 'manual' && !empty($settings['topper_ranks_custom'])) {
            $customData = $this->formatCustomRankers($settings['topper_ranks_custom'], $customTitle, $customSubtitle, $examTerm, $isActive);
            if (!empty($customData['overall'])) {
                return $customData;
            }
        }

        // 2. Auto Mode: Live TerminalRollNumber query
        try {
            $terminalQuery = \App\Models\TerminalRollNumber::where('school_id', $this->id)
                ->whereNotNull('rank')
                ->where('rank', '>', 0);

            if ((clone $terminalQuery)->exists()) {
                // Find latest semester that has ranks
                $latestSem = (clone $terminalQuery)->orderBy('id', 'desc')->first()?->semester;
                $resolvedTermName = $examTerm ?: ($latestSem?->semester_name ?? 'Latest Terminal Examination');

                $allRecords = (clone $terminalQuery)
                    ->with(['student', 'schoolClass', 'section', 'semester'])
                    ->orderBy('rank', 'asc')
                    ->get();

                $byClass = [];
                $classesAvailable = [];

                foreach ($allRecords->groupBy('class_id') as $cId => $records) {
                    $classObj = $records->first()->schoolClass;
                    $cName = $classObj?->class_name ?? "Grade {$cId}";

                    $topThree = $records->sortBy('rank')->take(3)->values();
                    $formattedClassRankers = [];

                    foreach ($topThree as $idx => $rec) {
                        $rankNum = $rec->rank > 0 && $rec->rank <= 3 ? (int)$rec->rank : ($idx + 1);
                        $studentName = $rec->student ? trim($rec->student->first_name . ' ' . $rec->student->last_name) : 'Student Scholar';
                        $photoUrl = $rec->student?->avatar ?? null;
                        
                        $secName = $rec->section?->section_name;
                        if (!$secName || $rec->section_id <= 0) {
                            $secName = $this->getStudentSectionName($rec->student_id, $cId);
                        }

                        $formattedClassRankers[] = $this->formatRankerRecord(
                            $rankNum,
                            $studentName,
                            $photoUrl,
                            $cName,
                            $cId,
                            $secName,
                            $rec->section_id,
                            $rec->gpa,
                            $rec->percentage,
                            $rec->total_marks,
                            $rec->semester?->semester_name ?? $resolvedTermName
                        );
                    }

                    if (!empty($formattedClassRankers)) {
                        $byClass[$cId] = [
                            'class_id'   => $cId,
                            'class_name' => $cName,
                            'rankers'    => $formattedClassRankers,
                        ];
                        $classesAvailable[] = [
                            'id'   => (string)$cId,
                            'name' => $cName,
                        ];
                    }
                }

                // Overall Top 3 across school: sorted by rank, then gpa desc, then percentage desc
                $sortedOverall = $allRecords->sortBy([
                    ['rank', 'asc'],
                    ['gpa', 'desc'],
                    ['percentage', 'desc'],
                    ['total_marks', 'desc'],
                ])->take(3)->values();

                $overallRankers = [];
                foreach ($sortedOverall as $idx => $rec) {
                    $displayRank = $idx + 1;
                    $cName = $rec->schoolClass?->class_name ?? "Grade {$rec->class_id}";
                    $studentName = $rec->student ? trim($rec->student->first_name . ' ' . $rec->student->last_name) : 'Student Scholar';
                    $secName = $rec->section?->section_name ?: $this->getStudentSectionName($rec->student_id, $rec->class_id);

                    $overallRankers[] = $this->formatRankerRecord(
                        $displayRank,
                        $studentName,
                        $rec->student?->avatar ?? null,
                        $cName,
                        $rec->class_id,
                        $secName,
                        $rec->section_id,
                        $rec->gpa,
                        $rec->percentage,
                        $rec->total_marks,
                        $rec->semester?->semester_name ?? $resolvedTermName
                    );
                }

                if (!empty($overallRankers)) {
                    array_unshift($classesAvailable, [
                        'id'   => 'overall',
                        'name' => 'Overall Campus',
                    ]);

                    return [
                        'is_active'    => $isActive,
                        'mode'         => 'auto',
                        'title'        => $customTitle,
                        'subtitle'     => $customSubtitle,
                        'exam_term'    => $resolvedTermName,
                        'is_live_data' => true,
                        'overall'      => $overallRankers,
                        'by_class'     => $byClass,
                        'classes'      => $classesAvailable,
                    ];
                }
            }
        } catch (\Throwable $e) {}

        // 3. Auto Mode: Live FinalMark query fallback
        try {
            if (\App\Models\FinalMark::where('school_id', $this->id)->exists()) {
                $computed = $this->computeRankersFromFinalMarks($customTitle, $customSubtitle, $examTerm, $isActive);
                if (!empty($computed['overall'])) {
                    return $computed;
                }
            }
        } catch (\Throwable $e) {}

        // 4. Smart Fallback with School's Defined Classes & Sections
        return $this->getDefaultRankersData($isActive, $customTitle, $customSubtitle, $examTerm);
    }

    /**
     * Compute ranks from FinalMark table if TerminalRollNumber has not been generated yet.
     */
    public function computeRankersFromFinalMarks(string $title, string $subtitle, ?string $examTerm, bool $isActive): array
    {
        $latestSem = \App\Models\FinalMark::where('school_id', $this->id)
            ->orderBy('id', 'desc')
            ->first()?->semester;

        $resolvedTermName = $examTerm ?: ($latestSem?->semester_name ?? 'Terminal Examination');

        $query = \App\Models\FinalMark::where('school_id', $this->id)
            ->with(['student', 'schoolClass', 'section']);

        if ($latestSem) {
            $query->where('semester_id', $latestSem->id);
        }

        $marks = $query->get();
        if ($marks->isEmpty()) {
            return [];
        }

        $byClass = [];
        $classesAvailable = [];
        $allStudentSummaries = [];

        foreach ($marks->groupBy('class_id') as $cId => $classMarks) {
            $classObj = $classMarks->first()->schoolClass;
            $cName = $classObj?->class_name ?? "Grade {$cId}";

            $studentSummaries = [];
            foreach ($classMarks->groupBy('student_id') as $sId => $sMarks) {
                $student = $sMarks->first()->student;
                $totMarks = $sMarks->sum('final_marks');
                $count = $sMarks->count();
                $avg = $count > 0 ? round($totMarks / $count, 2) : 0;
                $gpa = round(min(4.0, max(1.0, ($avg / 100) * 4.0)), 2);

                $sec = $sMarks->first()->section;
                $secName = $sec?->section_name ?: $this->getStudentSectionName($sId, $cId);

                $summary = [
                    'student_id'   => $sId,
                    'student_name' => $student ? trim($student->first_name . ' ' . $student->last_name) : 'Scholar',
                    'photo'        => $student?->avatar ?? null,
                    'class_id'     => $cId,
                    'class_name'   => $cName,
                    'section_name' => $secName,
                    'section_id'   => $sec?->id ?? 0,
                    'total_marks'  => $totMarks,
                    'gpa'          => $gpa,
                    'percentage'   => $avg,
                ];

                $studentSummaries[] = $summary;
                $allStudentSummaries[] = $summary;
            }

            usort($studentSummaries, function ($a, $b) {
                if ($b['gpa'] != $a['gpa']) return $b['gpa'] <=> $a['gpa'];
                return $b['total_marks'] <=> $a['total_marks'];
            });

            $topThree = array_slice($studentSummaries, 0, 3);
            $formattedRankers = [];
            foreach ($topThree as $i => $item) {
                $formattedRankers[] = $this->formatRankerRecord(
                    $i + 1,
                    $item['student_name'],
                    $item['photo'],
                    $item['class_name'],
                    $item['class_id'],
                    $item['section_name'],
                    $item['section_id'],
                    $item['gpa'],
                    $item['percentage'],
                    $item['total_marks'],
                    $resolvedTermName
                );
            }

            if (!empty($formattedRankers)) {
                $byClass[$cId] = [
                    'class_id'   => $cId,
                    'class_name' => $cName,
                    'rankers'    => $formattedRankers,
                ];
                $classesAvailable[] = [
                    'id'   => (string)$cId,
                    'name' => $cName,
                ];
            }
        }

        usort($allStudentSummaries, function ($a, $b) {
            if ($b['gpa'] != $a['gpa']) return $b['gpa'] <=> $a['gpa'];
            return $b['total_marks'] <=> $a['total_marks'];
        });

        $overallRankers = [];
        foreach (array_slice($allStudentSummaries, 0, 3) as $i => $item) {
            $overallRankers[] = $this->formatRankerRecord(
                $i + 1,
                $item['student_name'],
                $item['photo'],
                $item['class_name'],
                $item['class_id'],
                $item['section_name'],
                $item['section_id'],
                $item['gpa'],
                $item['percentage'],
                $item['total_marks'],
                $resolvedTermName
            );
        }

        if (!empty($overallRankers)) {
            array_unshift($classesAvailable, [
                'id'   => 'overall',
                'name' => 'Overall Campus',
            ]);

            return [
                'is_active'    => $isActive,
                'mode'         => 'auto',
                'title'        => $title,
                'subtitle'     => $subtitle,
                'exam_term'    => $resolvedTermName,
                'is_live_data' => true,
                'overall'      => $overallRankers,
                'by_class'     => $byClass,
                'classes'      => $classesAvailable,
            ];
        }

        return [];
    }

    /**
     * Format manual custom rankers from settings.
     */
    public function formatCustomRankers(array $custom, string $title, string $subtitle, ?string $examTerm, bool $isActive): array
    {
        $termName = $examTerm ?: ($custom['exam_term'] ?? 'Annual Terminal Examination 2081/82');
        $overall = [];

        for ($rank = 1; $rank <= 3; $rank++) {
            $prefix = "topper_{$rank}_";
            $name = $custom[$prefix . 'name'] ?? null;
            if (!empty($name)) {
                $photoPath = $custom[$prefix . 'photo'] ?? null;
                $photoUrl = $photoPath ? asset('storage/' . $photoPath) : null;
                $cName = $custom[$prefix . 'class'] ?? 'Grade 10';
                $secName = $custom[$prefix . 'section'] ?? 'Section A';
                $gpa = $custom[$prefix . 'gpa'] ?? ($rank === 1 ? '3.98' : ($rank === 2 ? '3.94' : '3.90'));
                $pct = $custom[$prefix . 'percentage'] ?? ($rank === 1 ? '96.5' : ($rank === 2 ? '94.8' : '92.6'));
                $honors = $custom[$prefix . 'honors'] ?? null;

                $overall[] = $this->formatRankerRecord(
                    $rank,
                    $name,
                    $photoUrl,
                    $cName,
                    $rank,
                    $secName,
                    $rank,
                    $gpa,
                    $pct,
                    null,
                    $termName,
                    $honors
                );
            }
        }

        if (empty($overall)) {
            return [];
        }

        return [
            'is_active'    => $isActive,
            'mode'         => 'manual',
            'title'        => $title,
            'subtitle'     => $subtitle,
            'exam_term'    => $termName,
            'is_live_data' => true,
            'overall'      => $overall,
            'by_class'     => ['overall' => ['class_id' => 'overall', 'class_name' => 'Campus Toppers', 'rankers' => $overall]],
            'classes'      => [['id' => 'overall', 'name' => 'Campus Toppers']],
        ];
    }

    /**
     * Fallback smart data using the school's configured classes & sections.
     */
    public function getDefaultRankersData(bool $isActive, string $title, string $subtitle, ?string $examTerm): array
    {
        $termName = $examTerm ?: 'Annual Terminal Examination 2081/82';

        // Check if school has defined SchoolClass records
        $classes = [];
        try {
            $classes = \App\Models\SchoolClass::where('school_id', $this->id)->with('sections')->take(5)->get();
        } catch (\Throwable $e) {}

        $byClass = [];
        $classesAvailable = [
            ['id' => 'overall', 'name' => 'Overall Campus']
        ];

        // Sample student names reflecting Nepal's academic community
        $namesPool = [
            ['Aarav Sharma', 'Prerana Shrestha', 'Rohan Adhikari'],
            ['Samyak Bajracharya', 'Aayusha Karki', 'Bipin Poudel'],
            ['Kritika Thapa', 'Siddhartha Joshi', 'Ananya Dahal'],
        ];

        if ($classes && $classes->isNotEmpty()) {
            foreach ($classes as $idx => $classObj) {
                $cId = (string) $classObj->id;
                $cName = $classObj->class_name ?: "Grade " . ($idx + 9);
                $secName = $classObj->sections->first()?->section_name ?? ($idx % 2 === 0 ? 'Section A' : 'Section B');
                $names = $namesPool[$idx % count($namesPool)];

                $classRankers = [
                    $this->formatRankerRecord(1, $names[0], null, $cName, $cId, $secName, 1, '3.98', '96.5', 772, $termName),
                    $this->formatRankerRecord(2, $names[1], null, $cName, $cId, $classObj->sections->skip(1)->first()?->section_name ?? 'Section B', 2, '3.94', '94.8', 758, $termName),
                    $this->formatRankerRecord(3, $names[2], null, $cName, $cId, $secName, 3, '3.90', '92.6', 741, $termName),
                ];

                $byClass[$cId] = [
                    'class_id'   => $cId,
                    'class_name' => $cName,
                    'rankers'    => $classRankers,
                ];
                $classesAvailable[] = [
                    'id'   => $cId,
                    'name' => $cName,
                ];
            }
        } else {
            // Standard Nepalese academic curricula
            $defaultClasses = [
                ['id' => '10', 'name' => 'Grade 10 (SEE)', 'sec' => 'Section A'],
                ['id' => '12', 'name' => 'Grade 12 (+2 Science)', 'sec' => 'Newton Section'],
                ['id' => '9',  'name' => 'Grade 9 (Basic Level)', 'sec' => 'Section A'],
            ];

            foreach ($defaultClasses as $idx => $cDef) {
                $cId = $cDef['id'];
                $cName = $cDef['name'];
                $names = $namesPool[$idx];

                $classRankers = [
                    $this->formatRankerRecord(1, $names[0], null, $cName, $cId, $cDef['sec'], 1, '3.98', '96.5', 772, $termName),
                    $this->formatRankerRecord(2, $names[1], null, $cName, $cId, 'Section B', 2, '3.94', '94.8', 758, $termName),
                    $this->formatRankerRecord(3, $names[2], null, $cName, $cId, $cDef['sec'], 3, '3.90', '92.6', 741, $termName),
                ];

                $byClass[$cId] = [
                    'class_id'   => $cId,
                    'class_name' => $cName,
                    'rankers'    => $classRankers,
                ];
                $classesAvailable[] = [
                    'id'   => $cId,
                    'name' => $cName,
                ];
            }
        }

        // Overall campus top 3
        $overall = [
            $this->formatRankerRecord(1, 'Aarav Sharma', null, 'Grade 10 (SEE)', '10', 'Section A', 1, '3.98', '96.5', 772, $termName, 'Highest Overall Campus Aggregate · Distinction'),
            $this->formatRankerRecord(2, 'Samyak Bajracharya', null, 'Grade 12 (+2 Science)', '12', 'Section Newton', 2, '3.96', '95.8', 766, $termName, 'First Division with Distinction'),
            $this->formatRankerRecord(3, 'Prerana Shrestha', null, 'Grade 10 (SEE)', '10', 'Section B', 3, '3.94', '94.8', 758, $termName, 'Academic Merit & Excellence Award'),
        ];

        return [
            'is_active'    => $isActive,
            'mode'         => 'auto',
            'title'        => $title,
            'subtitle'     => $subtitle,
            'exam_term'    => $termName,
            'is_live_data' => false,
            'overall'      => $overall,
            'by_class'     => $byClass,
            'classes'      => $classesAvailable,
        ];
    }
}
