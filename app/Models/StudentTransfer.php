<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentTransfer extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'school_id',
        'student_id',
        'transfer_type',          // inter_class | inter_section | inter_school | incoming
        // From
        'from_session_id',
        'from_class_id',
        'from_section_id',
        'from_school',            // free-text name (scenario 2/3)
        'from_school_id',         // FK to schools (scenario 1 — source school)
        // To
        'to_session_id',
        'to_class_id',
        'to_section_id',
        'to_school',              // free-text name (scenario 2)
        'to_school_id',           // FK to schools (scenario 1 — destination school)
        // Meta
        'transfer_date',
        'reason',
        'status',                 // pending | approved | rejected
        'approved_by',
        'approved_at',
        'notes',
        // TC fields
        'tc_token',
        'tc_issued_at',
        'tc_number',
        'accepted_at',
        'accepted_by',
        // Document carry flags
        'carry_marksheet',
        'carry_character',
        'carry_birth_cert',
        'carry_medical',
        // Incoming-specific
        'previous_tc_number',
        'previous_school_name',
        'joining_date',
    ];

    protected $casts = [
        'transfer_date'  => 'date',
        'joining_date'   => 'date',
        'approved_at'    => 'datetime',
        'tc_issued_at'   => 'datetime',
        'accepted_at'    => 'datetime',
        'carry_marksheet' => 'boolean',
        'carry_character' => 'boolean',
        'carry_birth_cert'=> 'boolean',
        'carry_medical'   => 'boolean',
    ];

    // ── Scenario labels ───────────────────────────────────────────────────────
    public const TYPE_LABELS = [
        'inter_class'   => 'Class Transfer',
        'inter_section' => 'Section Transfer',
        'inter_school'  => 'School Transfer (Outgoing)',
        'incoming'      => 'Incoming Transfer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function fromSchool(): BelongsTo
    {
        return $this->belongsTo(School::class, 'from_school_id');
    }

    public function toSchool(): BelongsTo
    {
        return $this->belongsTo(School::class, 'to_school_id');
    }

    public function fromClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'from_class_id');
    }

    public function toClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'to_class_id');
    }

    public function fromSection(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'from_section_id');
    }

    public function toSection(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'to_section_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    // ── Computed helpers ──────────────────────────────────────────────────────

    /** Human-readable scenario label */
    public function getScenarioLabelAttribute(): string
    {
        if ($this->transfer_type === 'inter_school') {
            return $this->to_school_id
                ? 'Same-Platform Transfer (Scenario 1)'
                : 'External School Transfer (Scenario 2)';
        }
        return self::TYPE_LABELS[$this->transfer_type] ?? $this->transfer_type;
    }

    /** Scenario number (1 / 2 / 3 / null for intra-school) */
    public function getScenarioAttribute(): ?int
    {
        if ($this->transfer_type === 'inter_school') {
            return $this->to_school_id ? 1 : 2;
        }
        if ($this->transfer_type === 'incoming') {
            return 3;
        }
        return null;
    }

    /** Is TC already issued? */
    public function isTcIssued(): bool
    {
        return !empty($this->tc_token) && $this->tc_issued_at !== null;
    }

    /** Is this transfer accepted by the destination school (Scenario 1)? */
    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    /** Destination school name (either from FK or free-text) */
    public function getDestinationSchoolNameAttribute(): string
    {
        if ($this->toSchool) {
            return $this->toSchool->name;
        }
        return $this->to_school ?? 'External School';
    }

    /** Source school name (either from FK or free-text) */
    public function getSourceSchoolNameAttribute(): string
    {
        if ($this->fromSchool) {
            return $this->fromSchool->name;
        }
        return $this->from_school ?? $this->previous_school_name ?? 'Unknown School';
    }

    /** Tailwind badge class for status */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'bg-amber-100 text-amber-700',
            'approved' => 'bg-emerald-100 text-emerald-700',
            'rejected' => 'bg-rose-100 text-rose-700',
            default    => 'bg-slate-100 text-slate-600',
        };
    }
}
