<?php

namespace Database\Seeders;

use App\Models\CertificateTemplate;
use App\Scopes\TenantScope;
use Illuminate\Database\Seeder;

/**
 * Seeds four production-ready certificate designs.
 *
 *  1. Royal Gold         — ornate double-border, gold/amber palette, landscape, formal
 *  2. Modern Gradient    — modern accent-bar border, indigo/violet, landscape, clean
 *  3. Minimalist Clean   — no border, slate/emerald, portrait, simple
 *  4. Classic Nepali     — classic double-border, red/blue national colours, landscape, bilingual
 *
 * All use {{placeholders}} resolved by CertificateTemplate::render().
 * Uses updateOrCreate so re-seeding is safe.
 */
class CertificateTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // Resolve the current school_id from tenant config (set by TenantMiddleware).
        // When running via artisan db:seed directly (e.g. fresh install) the config
        // may be empty, so we fall back to the first school in the database.
        $schoolId = config('tenant.school_id')
            ?: optional(\App\Models\School::first())->id;

        if (!$schoolId) {
            $this->command?->warn('CertificateTemplateSeeder: no school found — skipping.');
            return;
        }

        $query = fn () => CertificateTemplate::withoutGlobalScope(TenantScope::class);

        // ── 1. ROYAL GOLD ─────────────────────────────────────────────────────
        $query()->updateOrCreate(
            ['name' => 'Royal Gold', 'school_id' => $schoolId],
            [
                'type'               => 'merit',
                'language'           => 'en',
                'header_text'        => 'Certificate of Merit',
                'body_text'          => 'This is to certify that {{student_name}}, a student of {{class}}, Section {{section}}, has demonstrated outstanding academic excellence and commendable conduct during the academic year {{academic_year}}. This certificate is awarded in recognition of exceptional performance and dedication to learning.',
                'body_text_ne'       => null,
                'footer_text'        => 'This certificate is issued with pride and recognition of the student\'s outstanding achievement.',
                'signature_name'     => 'Principal',
                'signature_title'    => 'Head of Institution',
                'logo_path'          => null,
                'signature_image_path'  => null,
                'background_image_path' => null,
                'paper_size'         => 'A4',
                'orientation'        => 'landscape',
                'border_style'       => 'ornate',
                'primary_color'      => '#b45309',
                'secondary_color'    => '#d97706',
                'show_student_photo' => false,
                'show_qr_code'       => true,
                'show_serial_number' => true,
                'verification_url'   => null,
                'is_active'          => true,
                'builder_mode'       => false,
                'builder_layout'     => null,
                'editable_fields'    => null,
            ]
        );

        // ── 2. MODERN GRADIENT ────────────────────────────────────────────────
        $query()->updateOrCreate(
            ['name' => 'Modern Gradient', 'school_id' => $schoolId],
            [
                'type'               => 'completion',
                'language'           => 'en',
                'header_text'        => 'Certificate of Completion',
                'body_text'          => 'This is to certify that {{student_name}}, Admission No. {{admission_no}}, of Class {{class}}, Section {{section}}, has successfully completed the academic requirements for the year {{academic_year}}. The student has fulfilled all prescribed coursework and demonstrated satisfactory progress throughout the year.',
                'body_text_ne'       => null,
                'footer_text'        => 'Issued on {{date}} by the Academic Office.',
                'signature_name'     => 'Exam Controller',
                'signature_title'    => 'Academic Department',
                'logo_path'          => null,
                'signature_image_path'  => null,
                'background_image_path' => null,
                'paper_size'         => 'A4',
                'orientation'        => 'landscape',
                'border_style'       => 'modern',
                'primary_color'      => '#4f46e5',
                'secondary_color'    => '#7c3aed',
                'show_student_photo' => true,
                'show_qr_code'       => true,
                'show_serial_number' => true,
                'verification_url'   => null,
                'is_active'          => true,
                'builder_mode'       => false,
                'builder_layout'     => null,
                'editable_fields'    => null,
            ]
        );

        // ── 3. MINIMALIST CLEAN ───────────────────────────────────────────────
        $query()->updateOrCreate(
            ['name' => 'Minimalist Clean', 'school_id' => $schoolId],
            [
                'type'               => 'participation',
                'language'           => 'en',
                'header_text'        => 'Certificate of Participation',
                'body_text'          => 'This certificate is proudly presented to {{student_name}} of Class {{class}} for active participation in school activities and programmes during the academic year {{academic_year}}. Your enthusiasm and involvement have made a positive contribution to our school community.',
                'body_text_ne'       => null,
                'footer_text'        => 'We appreciate your participation and encourage continued engagement.',
                'signature_name'     => 'Class Teacher',
                'signature_title'    => 'School Administration',
                'logo_path'          => null,
                'signature_image_path'  => null,
                'background_image_path' => null,
                'paper_size'         => 'A4',
                'orientation'        => 'portrait',
                'border_style'       => 'minimal',
                'primary_color'      => '#059669',
                'secondary_color'    => '#0d9488',
                'show_student_photo' => false,
                'show_qr_code'       => false,
                'show_serial_number' => true,
                'verification_url'   => null,
                'is_active'          => true,
                'builder_mode'       => false,
                'builder_layout'     => null,
                'editable_fields'    => null,
            ]
        );

        // ── 4. CLASSIC NEPALI ─────────────────────────────────────────────────
        $query()->updateOrCreate(
            ['name' => 'Classic Nepali', 'school_id' => $schoolId],
            [
                'type'               => 'character',
                'language'           => 'en',
                'header_text'        => 'Character Certificate',
                'body_text'          => 'This is to certify that {{student_name}}, son/daughter of {{father_name}} and {{mother_name}}, bearing Admission No. {{admission_no}}, was a bonafide student of this institution. The student studied in Class {{class}}, Section {{section}} during the academic year {{academic_year}}. During the period of study, the conduct and character of the student were found to be good. We wish {{student_name}} success in all future endeavours.',
                'body_text_ne'       => 'यो प्रमाणित गरिन्छ कि {{student_name}}, {{father_name}} र {{mother_name}} का पुत्र/पुत्री, भर्ना नं. {{admission_no}}, यस विद्यालयका एक नियमित विद्यार्थी थिए। उक्त विद्यार्थीले शैक्षिक वर्ष {{academic_year}} मा कक्षा {{class}}, शाखा {{section}} मा अध्ययन गरेका थिए। अध्ययन अवधिमा उक्त विद्यार्थीको आचरण तथा चरित्र उत्तम पाइयो। उनको भविष्य उज्ज्वल होस् भन्ने शुभकामना व्यक्त गर्दछौं।',
                'footer_text'        => 'This certificate is issued on the request of the student for official purposes.',
                'signature_name'     => 'Principal / प्रधानाध्यापक',
                'signature_title'    => 'School Seal & Signature',
                'logo_path'          => null,
                'signature_image_path'  => null,
                'background_image_path' => null,
                'paper_size'         => 'A4',
                'orientation'        => 'portrait',
                'border_style'       => 'classic',
                'primary_color'      => '#b91c1c',
                'secondary_color'    => '#1d4ed8',
                'show_student_photo' => true,
                'show_qr_code'       => true,
                'show_serial_number' => true,
                'verification_url'   => null,
                'is_active'          => true,
                'builder_mode'       => false,
                'builder_layout'     => null,
                'editable_fields'    => null,
            ]
        );
    }
}
