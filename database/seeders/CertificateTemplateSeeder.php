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

        // ── 1. ROYAL GOLD / CLASSIC ───────────────────────────────────────────
        $query()->updateOrCreate(
            ['name' => 'Royal Gold', 'school_id' => $schoolId],
            [
                'type'               => 'character',
                'language'           => 'en',
                'header_text'        => 'CERTIFICATE OF GOOD CONDUCT',
                'body_text'          => 'has successfully demonstrated outstanding academic performance and exemplary conduct throughout the academic year, and is hereby recognized and awarded this certificate in testament of their dedication and merit.',
                'body_text_ne'       => null,
                'footer_text'        => null,
                'signature_name'     => 'Principal',
                'signature_title'    => 'Head of Institution',
                'logo_path'          => null,
                'signature_image_path'  => null,
                'background_image_path' => null,
                'paper_size'         => 'A4',
                'orientation'        => 'landscape',
                'border_style'       => 'ornate',
                'primary_color'      => '#1a3a6b',
                'secondary_color'    => '#b8953e',
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

        // ── 5. OFFICIAL TRANSFER CERTIFICATE (TC) ────────────────────────────
        $query()->updateOrCreate(
            ['name' => 'Official Transfer Certificate', 'school_id' => $schoolId],
            [
                'type'               => 'transfer',
                'language'           => 'en',
                'header_text'        => 'Transfer Certificate (TC)',
                'body_text'          => 'This is to certify that {{student_name}}, Roll No. {{roll_no}}, Admission No. {{admission_no}}, was a student of Class {{class}}, Section {{section}} at {{school_name}}. All school dues have been cleared up to date. The student has paid all fees and is granted permission to seek admission to any recognized institution.',
                'body_text_ne'       => 'यो स्थानान्तरण प्रमाण-पत्र (Transfer Certificate) द्वारा प्रमाणित गरिन्छ कि {{student_name}}, कक्षा {{class}}, भर्ना नं. {{admission_no}}, यस विद्यालयका विद्यार्थी हुन्। निजले विद्यालयका सम्पूर्ण शुल्कहरू भुक्तानी गरीसकेका छन्।',
                'footer_text'        => 'Dues Cleared & Verified by Accounts & Administration.',
                'signature_name'     => 'School Registrar',
                'signature_title'    => 'Principal / School Authority',
                'logo_path'          => null,
                'signature_image_path'  => null,
                'background_image_path' => null,
                'paper_size'         => 'A4',
                'orientation'        => 'portrait',
                'border_style'       => 'classic',
                'primary_color'      => '#1e3a8a',
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

        // ── 6. BONAFIDE STUDENT CERTIFICATE ───────────────────────────────────
        $query()->updateOrCreate(
            ['name' => 'Bonafide Student Certificate', 'school_id' => $schoolId],
            [
                'type'               => 'bonafide',
                'language'           => 'en',
                'header_text'        => 'Bonafide Certificate',
                'body_text'          => 'This is to certify that {{student_name}}, Admission No. {{admission_no}}, is a genuine bonafide student of {{school_name}} studying in Class {{class}}, Section {{section}} for the academic year {{academic_year}}. To the best of our knowledge, the student bears good moral character.',
                'body_text_ne'       => null,
                'footer_text'        => 'Issued for official verification / passport / bank account / visa application.',
                'signature_name'     => 'Administrative Officer',
                'signature_title'    => 'Principal',
                'logo_path'          => null,
                'signature_image_path'  => null,
                'background_image_path' => null,
                'paper_size'         => 'A4',
                'orientation'        => 'landscape',
                'border_style'       => 'modern',
                'primary_color'      => '#0369a1',
                'secondary_color'    => '#0284c7',
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

        // ── 7. SPORTS CHAMPIONSHIP AWARD ─────────────────────────────────────
        $query()->updateOrCreate(
            ['name' => 'Sports Championship Award', 'school_id' => $schoolId],
            [
                'type'               => 'sports',
                'language'           => 'en',
                'header_text'        => 'Certificate of Athletic Excellence',
                'body_text'          => 'This Certificate of Sports Excellence is proudly awarded to {{student_name}} of Class {{class}} for securing 1st Position / Outstanding Achievement in the Annual Sports Meet {{academic_year}} at {{school_name}}. In recognition of extraordinary sportsmanship, dedication, and athletic performance.',
                'body_text_ne'       => null,
                'footer_text'        => 'Awarded at the Annual Sports & Physical Education Day.',
                'signature_name'     => 'Sports Director',
                'signature_title'    => 'Principal',
                'logo_path'          => null,
                'signature_image_path'  => null,
                'background_image_path' => null,
                'paper_size'         => 'A4',
                'orientation'        => 'landscape',
                'border_style'       => 'ornate',
                'primary_color'      => '#065f46',
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

        // ── 8. APPRECIATION & HONOR AWARD ────────────────────────────────────
        $query()->updateOrCreate(
            ['name' => 'Appreciation & Honor Award', 'school_id' => $schoolId],
            [
                'type'               => 'appreciation',
                'language'           => 'en',
                'header_text'        => 'Certificate of Appreciation',
                'body_text'          => 'This Certificate of Appreciation is awarded to {{student_name}} of Class {{class}} in sincere gratitude for outstanding leadership, exemplary volunteer service, and invaluable contributions to {{school_name}} during the academic year {{academic_year}}.',
                'body_text_ne'       => null,
                'footer_text'        => 'With highest honors and warm congratulations.',
                'signature_name'     => 'Event Coordinator',
                'signature_title'    => 'Headmaster / Principal',
                'logo_path'          => null,
                'signature_image_path'  => null,
                'background_image_path' => null,
                'paper_size'         => 'A4',
                'orientation'        => 'landscape',
                'border_style'       => 'ornate',
                'primary_color'      => '#881337',
                'secondary_color'    => '#f59e0b',
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

        // ── 9. GRADUATION DIPLOMA ─────────────────────────────────────────────
        $query()->updateOrCreate(
            ['name' => 'Graduation Diploma', 'school_id' => $schoolId],
            [
                'type'               => 'graduation',
                'language'           => 'en',
                'header_text'        => 'Diploma of Graduation',
                'body_text'          => 'The Board of Trustees and Faculty of {{school_name}} hereby confer upon {{student_name}} this Diploma of Graduation, certifying completion of all prescribed secondary academic courses for Class {{class}} in the academic year {{academic_year}}, with all rights, honors, and privileges appertaining thereto.',
                'body_text_ne'       => null,
                'footer_text'        => 'Given under the seal of the institution.',
                'signature_name'     => 'Chairman of Board',
                'signature_title'    => 'Principal',
                'logo_path'          => null,
                'signature_image_path'  => null,
                'background_image_path' => null,
                'paper_size'         => 'A4',
                'orientation'        => 'landscape',
                'border_style'       => 'ornate',
                'primary_color'      => '#0f172a',
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

        // ── 10. COMPUTER & DIGITAL LITERACY ──────────────────────────────────
        $query()->updateOrCreate(
            ['name' => 'Computer & Digital Literacy Certificate', 'school_id' => $schoolId],
            [
                'type'               => 'computer_course',
                'language'           => 'en',
                'header_text'        => 'Certificate of Digital Literacy',
                'body_text'          => 'This is to certify that {{student_name}} of Class {{class}} has successfully completed the Computer Application & Digital Literacy Course conducted by the ICT Department of {{school_name}} during {{academic_year}}.',
                'body_text_ne'       => null,
                'footer_text'        => 'Certified ICT & Digital Skills Training.',
                'signature_name'     => 'Head of Computer Science',
                'signature_title'    => 'Principal',
                'logo_path'          => null,
                'signature_image_path'  => null,
                'background_image_path' => null,
                'paper_size'         => 'A4',
                'orientation'        => 'landscape',
                'border_style'       => 'modern',
                'primary_color'      => '#581c87',
                'secondary_color'    => '#a855f7',
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
    }
}
