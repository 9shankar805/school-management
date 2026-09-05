<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchoolCommitteeMember extends Model
{
    use HasTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'full_name',
        'position',
        'phone',
        'email',
        'no_political_party_member',
        'declaration_signed_at',
        'citizenship_no',
        'voter_id_no',
        'appointed_at',
        'term_ends_at',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'no_political_party_member' => 'boolean',
        'declaration_signed_at'     => 'datetime',
        'appointed_at'              => 'date',
        'term_ends_at'              => 'date',
    ];

    const POSITIONS = [
        'chairperson'             => 'Chairperson (अध्यक्ष)',
        'vice_chairperson'        => 'Vice-Chairperson (उपाध्यक्ष)',
        'secretary'               => 'Secretary (सचिव)',
        'treasurer'               => 'Treasurer (कोषाध्यक्ष)',
        'member'                  => 'Member (सदस्य)',
        'teacher_representative'  => 'Teacher Representative (शिक्षक प्रतिनिधि)',
        'parent_representative'   => 'Parent Representative (अभिभावक प्रतिनिधि)',
        'community_representative'=> 'Community Representative (समुदाय प्रतिनिधि)',
        'student_representative'  => 'Student Representative (विद्यार्थी प्रतिनिधि)',
        'other'                   => 'Other (अन्य)',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(MediaFile::class, 'model')
                    ->whereIn('collection', ['smc_id_proof', 'smc_declaration']);
    }

    public function idProofs(): MorphMany
    {
        return $this->morphMany(MediaFile::class, 'model')
                    ->where('collection', 'smc_id_proof');
    }

    public function declarations(): MorphMany
    {
        return $this->morphMany(MediaFile::class, 'model')
                    ->where('collection', 'smc_declaration');
    }

    public function getPositionLabelAttribute(): string
    {
        return self::POSITIONS[$this->position] ?? ucfirst(str_replace('_', ' ', $this->position));
    }

    public function getComplianceBadgeClassAttribute(): string
    {
        return $this->no_political_party_member
            ? 'bg-emerald-100 text-emerald-700'
            : 'bg-rose-100 text-rose-700';
    }

    public function getComplianceLabelAttribute(): string
    {
        return $this->no_political_party_member
            ? 'No Political Affiliation ✓'
            : '⚠ Declaration Pending';
    }
}
