<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class CertificateTemplate extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name', 'type', 'language',
        'header_text', 'body_text', 'body_text_ne', 'footer_text',
        'signature_name', 'signature_title',
        'logo_path', 'signature_image_path', 'background_image_path',
        'builder_mode', 'builder_layout', 'editable_fields',
        'paper_size', 'orientation',
        'show_student_photo', 'show_qr_code', 'show_serial_number',
        'border_style', 'primary_color', 'secondary_color',
        'verification_url',
        'is_active',
    ];

    protected $casts = [
        'is_active'           => 'boolean',
        'show_student_photo'  => 'boolean',
        'show_qr_code'        => 'boolean',
        'show_serial_number'  => 'boolean',
        'builder_mode'        => 'boolean',
        'builder_layout'      => 'array',
        'editable_fields'     => 'array',
    ];

    // ── All 35+ certificate types ─────────────────────────────────────────
    const TYPES = [
        // Administrative / Admission
        'admission'             => 'Admission Certificate',
        'bonafide'              => 'Bonafide Certificate',
        'character'             => 'Character Certificate',
        'school_leaving'        => 'School Leaving Certificate (SLC)',
        'transfer'              => 'Transfer Certificate (TC)',
        'migration'             => 'Migration Certificate',

        // Conduct & Attendance
        'conduct'               => 'Conduct Certificate',
        'attendance'            => 'Attendance Certificate',

        // Study / Enrollment
        'study'                 => 'Study Certificate',
        'enrollment'            => 'Enrollment Certificate',
        'identity'              => 'Identity Certificate',
        'birth_date'            => 'Birth Date Certificate',
        'promotion'             => 'Promotion Certificate',

        // Completion / Academic
        'completion'            => 'Completion Certificate',
        'graduation'            => 'Graduation Certificate',
        'merit'                 => 'Merit Certificate',
        'excellence'            => 'Excellence Certificate',

        // Achievements / Extra-curricular
        'sports'                => 'Sports Achievement Certificate',
        'cultural'              => 'Cultural Participation Certificate',
        'volunteer'             => 'Volunteer Certificate',
        'appreciation'          => 'Appreciation Certificate',
        'participation'         => 'Participation Certificate',
        'scholarship'           => 'Scholarship Certificate',

        // Professional / Senior
        'internship'            => 'Internship Certificate',
        'recommendation'        => 'Recommendation Certificate',

        // Financial / Clearance
        'fee_clearance'         => 'Fee Clearance Certificate',
        'no_due'                => 'No Due Certificate',

        // Medical / Special
        'medical_leave'         => 'Medical Leave Certificate',

        // Training / Courses
        'training'              => 'Training Certificate',
        'workshop'              => 'Workshop Certificate',
        'club_membership'       => 'Club Membership Certificate',
        'computer_course'       => 'Computer Course Certificate',
        'language_course'       => 'Language Course Certificate',
        'digital_literacy'      => 'Digital Literacy Certificate',

        // Leadership / Community
        'prefect_leadership'    => 'Prefect / Leadership Certificate',
        'community_service'     => 'Community Service Certificate',

        // Custom
        'custom'                => 'Custom Certificate',
    ];

    // Type groupings for UI organisation
    const TYPE_GROUPS = [
        'Administrative'    => ['admission','bonafide','character','school_leaving','transfer','migration'],
        'Conduct & Attendance' => ['conduct','attendance'],
        'Study & Enrollment'   => ['study','enrollment','identity','birth_date','promotion'],
        'Academic'             => ['completion','graduation','merit','excellence'],
        'Achievement'          => ['sports','cultural','volunteer','appreciation','participation','scholarship'],
        'Professional'         => ['internship','recommendation'],
        'Finance & Clearance'  => ['fee_clearance','no_due'],
        'Medical'              => ['medical_leave'],
        'Training & Courses'   => ['training','workshop','club_membership','computer_course','language_course','digital_literacy'],
        'Leadership'           => ['prefect_leadership','community_service'],
        'Custom'               => ['custom'],
    ];

    // Border styles available for design
    const BORDER_STYLES = [
        'classic'  => 'Classic (Double Border)',
        'modern'   => 'Modern (Accent Line)',
        'minimal'  => 'Minimal (No Border)',
        'ornate'   => 'Ornate (Decorative)',
    ];

    // ── Relationships ────────────────────────────────────────────────────
    public function issuedCertificates(): HasMany
    {
        return $this->hasMany(IssuedCertificate::class, 'template_id');
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    /** Absolute URL for the school logo stored on disk */
    public function logoUrl(): ?string
    {
        return $this->logo_path
            ? Storage::disk('public')->url($this->logo_path)
            : null;
    }

    /** Absolute URL for the principal/authority signature image */
    public function signatureImageUrl(): ?string
    {
        return $this->signature_image_path
            ? Storage::disk('public')->url($this->signature_image_path)
            : null;
    }

    /** Absolute URL for the certificate background image */
    public function backgroundImageUrl(): ?string
    {
        return $this->background_image_path
            ? Storage::disk('public')->url($this->background_image_path)
            : null;
    }

    /**
     * Replace template tokens with real student data.
     *
     * @param  User        $student
     * @param  array       $extra    Additional token overrides
     * @param  string|null $lang     'en' | 'ne' — uses body_text_ne when 'ne' and available
     * @return string
     */
    public function render(User $student, array $extra = [], ?string $lang = null): string
    {
        $lang      = $lang ?? $this->language ?? 'en';
        $promotion = $student->promotions()->with('schoolClass', 'section')->latest('id')->first();
        $academic  = $student->academic_info;
        $parent    = $student->parent_info;

        $tokens = array_merge([
            '{{student_name}}'       => $student->full_name,
            '{{first_name}}'         => $student->first_name,
            '{{last_name}}'          => $student->last_name,
            '{{gender}}'             => $student->gender ?? '—',
            '{{birthday}}'           => $student->birthday?->format('F j, Y') ?? '—',
            '{{age}}'                => $student->birthday ? (string) $student->birthday->age : '—',
            '{{nationality}}'        => $student->nationality ?? '—',
            '{{religion}}'           => $student->religion ?? '—',
            '{{blood_type}}'         => $student->blood_type ?? '—',
            '{{address}}'            => trim(($student->address ?? '') . ' ' . ($student->city ?? '')),
            '{{phone}}'              => $student->phone ?? '—',
            '{{class}}'              => $promotion?->schoolClass?->class_name ?? '—',
            '{{section}}'            => $promotion?->section?->section_name ?? '—',
            '{{roll_no}}'            => $promotion?->roll_no ?? $promotion?->id_card_number ?? '—',
            '{{admission_no}}'       => $academic?->admission_no ?? $promotion?->id_card_number ?? '—',
            '{{board_reg_no}}'       => $academic?->board_reg_no ?? '—',
            '{{academic_year}}'      => $promotion?->session?->session_name ?? (string) now()->year,
            '{{father_name}}'        => $parent?->father_name ?? '—',
            '{{mother_name}}'        => $parent?->mother_name ?? '—',
            '{{date}}'               => now()->format('F j, Y'),
            '{{date_np}}'            => now()->format('Y-m-d'),  // extend with BS conversion if needed
            '{{school_name}}'        => config('app.name'),
            '{{issued_by}}'          => auth()->user()?->full_name ?? '—',
            '{{extra_notes}}'        => '',
            '{{purpose}}'            => '',
        ], $extra);

        $body = ($lang === 'ne' && !empty($this->body_text_ne))
            ? $this->body_text_ne
            : $this->body_text;

        return str_replace(array_keys($tokens), array_values($tokens), $body);
    }

    /** Human-readable label for this template's type */
    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucwords(str_replace('_', ' ', $this->type));
    }

    /** DomPDF paper array for the configured size + orientation */
    public function paperConfig(): array
    {
        $sizes = [
            'A4'     => [0, 0, 595.28, 841.89],
            'Letter' => [0, 0, 612, 792],
        ];
        return $sizes[$this->paper_size] ?? $sizes['A4'];
    }

    /** Paper width in mm for the builder canvas */
    public function paperWidthMm(): float
    {
        return $this->orientation === 'landscape'
            ? ($this->paper_size === 'Letter' ? 279.4 : 297)
            : ($this->paper_size === 'Letter' ? 215.9 : 210);
    }

    /** Paper height in mm for the builder canvas */
    public function paperHeightMm(): float
    {
        return $this->orientation === 'landscape'
            ? ($this->paper_size === 'Letter' ? 215.9 : 210)
            : ($this->paper_size === 'Letter' ? 279.4 : 297);
    }

    /**
     * Field tokens available as "editable/dynamic fields" in the builder.
     * Key = token, Value = human label.
     * When builder_layout has an element with `field: <key>`, the renderer resolves it via render().
     */
    public static function DYNAMIC_FIELDS(): array
    {
        return [
            'student_name'   => 'Student Full Name',
            'first_name'     => 'First Name',
            'last_name'      => 'Last Name',
            'class'          => 'Class',
            'section'        => 'Section',
            'roll_no'        => 'Roll No.',
            'admission_no'   => 'Admission No.',
            'board_reg_no'   => 'Board Reg. No.',
            'academic_year'  => 'Academic Year',
            'birthday'       => 'Birthday',
            'age'            => 'Age',
            'gender'         => 'Gender',
            'father_name'    => "Father's Name",
            'mother_name'    => "Mother's Name",
            'address'        => 'Address',
            'date'           => 'Issue Date',
            'school_name'    => 'School Name',
            'issued_by'      => 'Issued By',
            'cert_no'        => 'Certificate Number',
            'purpose'        => 'Purpose',
            'extra_notes'    => 'Extra Notes',
            'custom_1'       => 'Custom Text 1 (editable per issue)',
            'custom_2'       => 'Custom Text 2 (editable per issue)',
            'custom_3'       => 'Custom Text 3 (editable per issue)',
        ];
    }
}
