<?php

namespace Database\Seeders;

use App\Models\CertificateTemplate;
use App\Scopes\TenantScope;
use Illuminate\Database\Seeder;

/**
 * Seeds four additional certificate designs — each visually distinct from the
 * first batch (Royal Gold, Modern Gradient, Minimalist Clean, Classic Nepali).
 *
 *  1. Teal Ribbon        — modern border, teal/cyan, landscape, sports & achievements
 *  2. Deep Navy          — classic border, navy/gold, landscape, graduation & formal
 *  3. Rose Petal         — minimal border, rose/pink, portrait, appreciation & cultural
 *  4. Scholar's Scroll   — ornate border, deep-purple/amber, portrait, bonafide & academic
 */
class CertificateTemplateSeeder2 extends Seeder
{
    public function run(): void
    {
        $schoolId = config('tenant.school_id')
            ?: optional(\App\Models\School::first())->id;

        if (!$schoolId) {
            $this->command?->warn('CertificateTemplateSeeder2: no school found — skipping.');
            return;
        }

        $query = fn () => CertificateTemplate::withoutGlobalScope(TenantScope::class);

        // ── 1. TEAL RIBBON ────────────────────────────────────────────────────
        $query()->updateOrCreate(
            ['name' => 'Teal Ribbon', 'school_id' => $schoolId],
            [
                'type'               => 'sports',
                'language'           => 'en',
                'header_text'        => 'Certificate of Achievement',
                'body_text'          => 'This certificate is proudly awarded to {{student_name}} of Class {{class}}, Section {{section}}, in recognition of exceptional achievement and outstanding performance during the academic year {{academic_year}}. Your dedication, hard work, and sportsmanship have been an inspiration to the entire school community. Keep striving for excellence in all your endeavours.',
                'body_text_ne'       => null,
                'footer_text'        => 'Awarded with honour on {{date}}.',
                'signature_name'     => 'Sports Director',
                'signature_title'    => 'Physical Education Department',
                'logo_path'          => null,
                'signature_image_path'  => null,
                'background_image_path' => null,
                'paper_size'         => 'A4',
                'orientation'        => 'landscape',
                'border_style'       => 'modern',
                'primary_color'      => '#0d9488',
                'secondary_color'    => '#06b6d4',
                'show_student_photo' => true,
                'show_qr_code'       => false,
                'show_serial_number' => true,
                'verification_url'   => null,
                'is_active'          => true,
                'builder_mode'       => false,
                'builder_layout'     => null,
                'editable_fields'    => null,
            ]
        );

        // ── 2. DEEP NAVY ──────────────────────────────────────────────────────
        $query()->updateOrCreate(
            ['name' => 'Deep Navy', 'school_id' => $schoolId],
            [
                'type'               => 'graduation',
                'language'           => 'en',
                'header_text'        => 'Graduation Certificate',
                'body_text'          => 'This is to certify that {{student_name}}, son/daughter of {{father_name}} and {{mother_name}}, bearing Board Registration No. {{board_reg_no}} and Admission No. {{admission_no}}, has successfully completed the prescribed course of study at this institution. The student was enrolled in Class {{class}}, Section {{section}} during the academic year {{academic_year}} and has fulfilled all academic requirements with distinction. This certificate is awarded in recognition of the student\'s successful completion of studies.',
                'body_text_ne'       => null,
                'footer_text'        => 'Issued under the seal and authority of the institution on {{date}}.',
                'signature_name'     => 'Principal',
                'signature_title'    => 'Head of Institution',
                'logo_path'          => null,
                'signature_image_path'  => null,
                'background_image_path' => null,
                'paper_size'         => 'A4',
                'orientation'        => 'landscape',
                'border_style'       => 'classic',
                'primary_color'      => '#1e3a5f',
                'secondary_color'    => '#b45309',
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

        // ── 3. ROSE PETAL ─────────────────────────────────────────────────────
        $query()->updateOrCreate(
            ['name' => 'Rose Petal', 'school_id' => $schoolId],
            [
                'type'               => 'appreciation',
                'language'           => 'en',
                'header_text'        => 'Certificate of Appreciation',
                'body_text'          => 'With sincere gratitude and appreciation, this certificate is presented to {{student_name}} of Class {{class}} for valuable contributions, enthusiastic participation, and a spirit of service during the academic year {{academic_year}}. Your positive attitude, creativity, and willingness to contribute have enriched our school community and served as an example to your peers. We are proud of your dedication and commitment.',
                'body_text_ne'       => null,
                'footer_text'        => 'With heartfelt appreciation from the school family — {{date}}.',
                'signature_name'     => 'Class Teacher',
                'signature_title'    => 'School Administration',
                'logo_path'          => null,
                'signature_image_path'  => null,
                'background_image_path' => null,
                'paper_size'         => 'A4',
                'orientation'        => 'portrait',
                'border_style'       => 'minimal',
                'primary_color'      => '#be185d',
                'secondary_color'    => '#db2777',
                'show_student_photo' => false,
                'show_qr_code'       => false,
                'show_serial_number' => false,
                'verification_url'   => null,
                'is_active'          => true,
                'builder_mode'       => false,
                'builder_layout'     => null,
                'editable_fields'    => null,
            ]
        );

        // ── 4. SCHOLAR'S SCROLL ───────────────────────────────────────────────
        $query()->updateOrCreate(
            ['name' => "Scholar's Scroll", 'school_id' => $schoolId],
            [
                'type'               => 'bonafide',
                'language'           => 'en',
                'header_text'        => 'Bonafide Certificate',
                'body_text'          => 'This is to certify that {{student_name}}, Admission No. {{admission_no}}, Roll No. {{roll_no}}, is a bonafide student of this institution. The student is currently studying in Class {{class}}, Section {{section}} during the academic year {{academic_year}}. Date of Birth: {{birthday}}. Gender: {{gender}}. Father\'s Name: {{father_name}}. Mother\'s Name: {{mother_name}}. Permanent Address: {{address}}. This certificate is issued on the request of the student for official purposes only.',
                'body_text_ne'       => 'यो प्रमाणित गरिन्छ कि {{student_name}}, भर्ना नं. {{admission_no}}, रोल नं. {{roll_no}}, यस संस्थाका एक नियमित विद्यार्थी हुन्। उक्त विद्यार्थी शैक्षिक वर्ष {{academic_year}} मा कक्षा {{class}}, शाखा {{section}} मा अध्ययनरत छन्। जन्म मिति: {{birthday}}। लिङ्ग: {{gender}}। बुबाको नाम: {{father_name}}। आमाको नाम: {{mother_name}}। स्थायी ठेगाना: {{address}}। यो प्रमाणपत्र विद्यार्थीको आग्रहमा आधिकारिक प्रयोजनका लागि जारी गरिएको हो।',
                'footer_text'        => 'This certificate is valid for official use only. Issued on {{date}}.',
                'signature_name'     => 'Principal',
                'signature_title'    => 'Office Seal & Signature',
                'logo_path'          => null,
                'signature_image_path'  => null,
                'background_image_path' => null,
                'paper_size'         => 'A4',
                'orientation'        => 'portrait',
                'border_style'       => 'ornate',
                'primary_color'      => '#6d28d9',
                'secondary_color'    => '#d97706',
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
