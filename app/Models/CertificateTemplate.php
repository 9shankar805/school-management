<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CertificateTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'body_text', 'header_text', 'footer_text',
        'signature_name', 'signature_title', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    const TYPES = [
        'completion'   => 'Course Completion',
        'merit'        => 'Merit / Excellence',
        'participation'=> 'Participation',
        'graduation'   => 'Graduation',
        'transfer'     => 'Transfer Certificate',
        'custom'       => 'Custom',
    ];

    /**
     * Replace template tokens with real student data.
     */
    public function render(User $student, array $extra = []): string
    {
        $promotion = $student->promotions()->latest()->first();
        $tokens = array_merge([
            '{{student_name}}'   => $student->full_name,
            '{{first_name}}'     => $student->first_name,
            '{{last_name}}'      => $student->last_name,
            '{{class}}'          => $promotion?->schoolClass?->class_name ?? '—',
            '{{section}}'        => $promotion?->section?->section_name ?? '—',
            '{{roll_no}}'        => $promotion?->id_card_number ?? '—',
            '{{date}}'           => now()->format('F j, Y'),
            '{{school_name}}'    => config('app.name'),
            '{{academic_year}}'  => now()->year,
        ], $extra);

        return str_replace(array_keys($tokens), array_values($tokens), $this->body_text);
    }
}
