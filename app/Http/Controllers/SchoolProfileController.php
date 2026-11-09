<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class SchoolProfileController extends Controller
{
    public function edit(): View
    {
        $school = auth()->user()->school;
        if (!$school) abort(404, 'School not found');

        $presetThemes = School::PRESET_THEMES;

        $steps = [
            ['icon' => 'bi-building',       'label' => 'Basic Info'],
            ['icon' => 'bi-palette',         'label' => 'Branding'],
            ['icon' => 'bi-sliders2',       'label' => 'Hero Slider'],
            ['icon' => 'bi-file-text',       'label' => 'Content'],
            ['icon' => 'bi-images',          'label' => 'Gallery'],
            ['icon' => 'bi-qr-code',         'label' => 'Payment QR'],
            ['icon' => 'bi-megaphone',       'label' => 'Popup Notice'],
        ];

        $activeStep = (int) request('step', 1);
        $activeStep = max(1, min(count($steps), $activeStep));

        return view('school-profile.edit', compact('school', 'presetThemes', 'steps', 'activeStep'));
    }

    // ── Dedicated Payment QR page ──────────────────────────────────────────

    public function paymentQr(): View
    {
        $school = auth()->user()->school;
        if (! $school) abort(404, 'School not found');

        return view('school-profile.payment-qr', compact('school'));
    }

    public function updatePaymentQr(Request $request): RedirectResponse
    {
        $school = auth()->user()->school;
        if (! $school) abort(404, 'School not found');

        $request->validate([
            'payment_qr_esewa'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'payment_qr_khalti'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'payment_qr_bank'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'payment_qr_fonepay'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'payment_instructions' => 'nullable|string|max:1000',
        ]);

        foreach (['payment_qr_esewa', 'payment_qr_khalti', 'payment_qr_bank', 'payment_qr_fonepay'] as $field) {
            if ($request->hasFile($field)) {
                if ($school->$field) {
                    Storage::disk('public')->delete($school->$field);
                }
                $school->$field = \App\Services\ImageWebpService::storeAsWebp(
                    $request->file($field), 'payment-qr', 'public'
                );
            } elseif ($request->input("remove_{$field}") == '1' && $school->$field) {
                Storage::disk('public')->delete($school->$field);
                $school->$field = null;
            }
        }

        $school->payment_instructions = $request->input('payment_instructions', $school->payment_instructions);
        $school->save();

        return back()->with('success', 'Payment QR codes saved successfully.');
    }

    public function update(Request $request): RedirectResponse
    {
        $school = auth()->user()->school;
        if (!$school) abort(404, 'School not found');

        $data = $request->validate([
            // Basic
            'name'              => 'required|string|max:255',
            'iemis_code'         => 'nullable|string|max:20',
            'email'             => 'required|email|max:255',
            'phone'             => 'nullable|string|max:20',
            'website'           => 'nullable|url|max:255',
            'contact_person'    => 'nullable|string|max:255',
            'contact_email'     => 'nullable|email|max:255',
            // Address
            'address'           => 'nullable|string|max:500',
            'city'              => 'nullable|string|max:100',
            'district'          => 'nullable|string|max:100',
            'province'          => 'nullable|string|max:100',
            'country'           => 'nullable|string|max:100',
            // Branding & theme
            'preset_theme'      => 'nullable|string|max:30',
            'primary_color'     => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'secondary_color'   => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            // Social
            'facebook_url'      => 'nullable|url|max:255',
            'twitter_url'       => 'nullable|url|max:255',
            'instagram_url'     => 'nullable|url|max:255',
            'youtube_url'       => 'nullable|url|max:255',
            // Content
            'description'       => 'nullable|string|max:1000',
            'about_us'          => 'nullable|string|max:5000',
            'mission'           => 'nullable|string|max:2000',
            'vision'            => 'nullable|string|max:2000',
            'history'           => 'nullable|string|max:5000',
            'principal_message' => 'nullable|string|max:3000',
            'admission_info'    => 'nullable|string|max:3000',
            'result_info'       => 'nullable|string|max:3000',
            'notice_board'      => 'nullable|string|max:3000',
            // Logo & gallery
            'logo'                       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'new_gallery_images.*'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'replace_gallery_image.*'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            // Payment QR codes
            'payment_qr_esewa'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:1024',
            'payment_qr_khalti'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:1024',
            'payment_qr_bank'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:1024',
            'payment_qr_fonepay'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:1024',
            'payment_instructions'       => 'nullable|string|max:1000',
            // Popup notice fields
            'popup_active'               => 'nullable',
            'popup_show_once'            => 'nullable',
            'popup_title'                => 'nullable|string|max:255',
            'popup_subtitle'             => 'nullable|string|max:1000',
            'popup_btn_text'             => 'nullable|string|max:100',
            'popup_btn_url'              => 'nullable|string|max:500',
            'popup_expires_at'           => 'nullable|date',
            'popup_image'                => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            // School Timings & Operating Hours
            'school_hours_weekdays'      => 'nullable|string|max:255',
            'school_hours_friday'        => 'nullable|string|max:255',
            'school_hours_weekend'       => 'nullable|string|max:255',
            'office_hours'               => 'nullable|string|max:255',
            // Upcoming Exam Routine & Academic Schedules
            'exam_title'                 => 'nullable|string|max:255',
            'exam_start_date'            => 'nullable|string|max:100',
            'exam_end_date'              => 'nullable|string|max:100',
            'exam_instructions'          => 'nullable|string|max:2000',
            'exam_shift_details'         => 'nullable|string|max:1000',
            'exam_routine_file'          => 'nullable|file|mimes:pdf,jpeg,png,jpg,webp|max:5120',
            'remove_exam_routine_file'   => 'nullable|in:0,1',
            // Principal Photo & Settings
            'principal_image'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'remove_principal_image'     => 'nullable|in:0,1',
            'principal_name'             => 'nullable|string|max:255',
            'principal_title'            => 'nullable|string|max:255',
            // Founder / Managing Director (Rato Bangala style dual leadership)
            'founder_image'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'remove_founder_image'       => 'nullable|in:0,1',
            'founder_name'               => 'nullable|string|max:255',
            'founder_title'              => 'nullable|string|max:255',
            'founder_message'            => 'nullable|string|max:3000',
            // Institutional Heritage & Campus Tour Video
            'established_year'           => 'nullable|string|max:20',
            'heritage_tagline'           => 'nullable|string|max:255',
            'campus_video_url'           => 'nullable|string|max:500',
            // Prominent Admission Announcement Ribbon & Box Notice
            'admission_banner_active'    => 'nullable|in:0,1',
            'admission_banner_text'      => 'nullable|string|max:255',
            'admission_banner_btn_text'  => 'nullable|string|max:100',
            'admission_banner_btn_url'   => 'nullable|string|max:500',
            'admission_notice_text'      => 'nullable|string|max:500',
            'admission_notice_link_text' => 'nullable|string|max:150',
            'admission_notice_date'      => 'nullable|string|max:100',
            'admission_notice_term'      => 'nullable|string|max:255',
            // Hero Slider Banners (Supports Images & MP4 Videos)
            'new_slider_image'           => 'nullable|file|mimes:jpeg,png,jpg,webp,gif,mp4,webm,ogg,mov|max:51200',
            'new_slider_title'           => 'nullable|string|max:255',
            'new_slider_subtitle'        => 'nullable|string|max:1000',
            'new_slider_tag'             => 'nullable|string|max:100',
            'new_slider_btn_text'        => 'nullable|string|max:100',
            'new_slider_btn_url'         => 'nullable|string|max:500',
            'new_slider_secondary_btn_text' => 'nullable|string|max:100',
            'new_slider_secondary_btn_url'  => 'nullable|string|max:500',
            'slider_banners'             => 'nullable|array',
            'replace_slider_image.*'     => 'nullable|file|mimes:jpeg,png,jpg,webp,gif,mp4,webm,ogg,mov|max:51200',
            'delete_slider_banners'      => 'nullable|array',
            // Section Custom Images (Auto-converted to WebP)
            'admissions_slide1_image'    => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,bmp|max:10240',
            'admissions_slide2_image'    => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,bmp|max:10240',
            'admissions_slide3_image'    => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,bmp|max:10240',
            'program_ecc_image'          => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,bmp|max:10240',
            'principal_photo_image'      => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,bmp|max:10240',
            'founder_photo_image'        => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,bmp|max:10240',
            'outreach_visual_image'      => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,bmp|max:10240',
            'campus_video_cover_image'   => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,bmp|max:10240',
            'pub_blogs_image'            => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,bmp|max:10240',
            'pub_newsletter_image'       => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,bmp|max:10240',
            'pub_cornice_image'          => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,bmp|max:10240',
            'pub_circle_image'           => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,bmp|max:10240',
            'pathway_preschool_image'    => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,bmp|max:10240',
            'pathway_primary_image'      => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,bmp|max:10240',
            'pathway_secondary_image'    => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,bmp|max:10240',
            'pathway_plus2_image'        => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,bmp|max:10240',
            'pathway_steam_image'        => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,bmp|max:10240',
            'pathway_sports_image'       => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,bmp|max:10240',
            'facility_stem_image'        => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,bmp|max:10240',
            'facility_computer_image'    => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,bmp|max:10240',
            'facility_library_image'     => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,bmp|max:10240',
            'facility_sports_image'      => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,bmp|max:10240',
            'facility_transport_image'   => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,bmp|max:10240',
            'facility_classroom_image'   => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,bmp|max:10240',
        ]);

        // ── Principal Photo & Operational Settings ───────────────────────────
        $settings = $school->settings ?? [];
        if ($request->hasFile('principal_image')) {
            if (!empty($settings['principal_image'])) {
                Storage::disk('public')->delete($settings['principal_image']);
            }
            $settings['principal_image'] = \App\Services\ImageWebpService::storeAsWebp($request->file('principal_image'), 'school-staff', 'public');
        } elseif ($request->input('remove_principal_image') == '1' && !empty($settings['principal_image'])) {
            Storage::disk('public')->delete($settings['principal_image']);
            $settings['principal_image'] = null;
        }

        if ($request->has('principal_name')) {
            $settings['principal_name'] = $request->input('principal_name');
        }
        if ($request->has('principal_title')) {
            $settings['principal_title'] = $request->input('principal_title');
        }

        // ── Founder / Managing Director (Dual Leadership) ─────────────────────
        if ($request->hasFile('founder_image')) {
            if (!empty($settings['founder_image'])) {
                Storage::disk('public')->delete($settings['founder_image']);
            }
            $settings['founder_image'] = \App\Services\ImageWebpService::storeAsWebp($request->file('founder_image'), 'school-staff', 'public');
        } elseif ($request->input('remove_founder_image') == '1' && !empty($settings['founder_image'])) {
            Storage::disk('public')->delete($settings['founder_image']);
            $settings['founder_image'] = null;
        }

        if ($request->has('founder_name')) {
            $settings['founder_name'] = $request->input('founder_name');
        }
        if ($request->has('founder_title')) {
            $settings['founder_title'] = $request->input('founder_title');
        }
        if ($request->has('founder_message')) {
            $settings['founder_message'] = $request->input('founder_message');
        }

        // ── Institutional Heritage & Campus Tour Video ───────────────────────
        if ($request->has('established_year')) {
            $settings['established_year'] = $request->input('established_year');
        }
        if ($request->has('heritage_tagline')) {
            $settings['heritage_tagline'] = $request->input('heritage_tagline');
        }
        if ($request->has('campus_video_url')) {
            $settings['campus_video_url'] = $request->input('campus_video_url');
        }

        // ── Prominent Admission Announcement Ribbon ──────────────────────────
        $settings['admission_banner_active'] = $request->has('admission_banner_active') ? 1 : 0;
        if ($request->has('admission_banner_text')) {
            $settings['admission_banner_text'] = $request->input('admission_banner_text');
        }
        if ($request->has('admission_banner_btn_text')) {
            $settings['admission_banner_btn_text'] = $request->input('admission_banner_btn_text');
        }
        if ($request->has('admission_banner_btn_url')) {
            $settings['admission_banner_btn_url'] = $request->input('admission_banner_btn_url');
        }
        if ($request->has('admission_notice_text')) {
            $settings['admission_notice_text'] = $request->input('admission_notice_text');
        }
        if ($request->has('admission_notice_link_text')) {
            $settings['admission_notice_link_text'] = $request->input('admission_notice_link_text');
        }
        if ($request->has('admission_notice_date')) {
            $settings['admission_notice_date'] = $request->input('admission_notice_date');
        }
        if ($request->has('admission_notice_term')) {
            $settings['admission_notice_term'] = $request->input('admission_notice_term');
        }

        // College Counseling & Global Placements (Rato Bangala style)
        if ($request->has('college_counseling_active')) {
            $settings['college_counseling_active'] = $request->boolean('college_counseling_active');
        }
        if ($request->has('college_counseling_text')) {
            $settings['college_counseling_text'] = $request->input('college_counseling_text');
        }
        if ($request->has('college_counseling_btn_text')) {
            $settings['college_counseling_btn_text'] = $request->input('college_counseling_btn_text');
        }
        if ($request->has('college_counseling_btn_url')) {
            $settings['college_counseling_btn_url'] = $request->input('college_counseling_btn_url');
        }

        // Newsletter Subscription
        if ($request->has('newsletter_active')) {
            $settings['newsletter_active'] = $request->boolean('newsletter_active');
        }
        if ($request->has('newsletter_title')) {
            $settings['newsletter_title'] = $request->input('newsletter_title');
        }
        if ($request->has('newsletter_desc')) {
            $settings['newsletter_desc'] = $request->input('newsletter_desc');
        }

        // RBPOP / Outreach & History (Rato Bangala style)
        if ($request->has('outreach_active')) {
            $settings['outreach_active'] = $request->boolean('outreach_active');
        }
        if ($request->has('outreach_tag')) {
            $settings['outreach_tag'] = $request->input('outreach_tag');
        }
        if ($request->has('outreach_title')) {
            $settings['outreach_title'] = $request->input('outreach_title');
        }
        if ($request->has('outreach_desc')) {
            $settings['outreach_desc'] = $request->input('outreach_desc');
        }
        if ($request->has('outreach_btn_text')) {
            $settings['outreach_btn_text'] = $request->input('outreach_btn_text');
        }
        if ($request->has('outreach_btn_url')) {
            $settings['outreach_btn_url'] = $request->input('outreach_btn_url');
        }

        // School Operating Hours
        if ($request->has('school_hours_weekdays')) {
            $settings['school_hours_weekdays'] = $request->input('school_hours_weekdays');
        }
        if ($request->has('school_hours_friday')) {
            $settings['school_hours_friday'] = $request->input('school_hours_friday');
        }
        if ($request->has('school_hours_weekend')) {
            $settings['school_hours_weekend'] = $request->input('school_hours_weekend');
        }
        if ($request->has('office_hours')) {
            $settings['office_hours'] = $request->input('office_hours');
        }

        // Upcoming Exam Routine & Guidelines
        if ($request->has('exam_title')) {
            $settings['exam_title'] = $request->input('exam_title');
        }
        if ($request->has('exam_start_date')) {
            $settings['exam_start_date'] = $request->input('exam_start_date');
        }
        if ($request->has('exam_end_date')) {
            $settings['exam_end_date'] = $request->input('exam_end_date');
        }
        if ($request->has('exam_instructions')) {
            $settings['exam_instructions'] = $request->input('exam_instructions');
        }
        if ($request->has('exam_shift_details')) {
            $settings['exam_shift_details'] = $request->input('exam_shift_details');
        }

        // ── Academic Hall of Fame / Toppers Ranking Settings ────────────────
        if ($request->has('topper_ranks_active') || $request->has('topper_ranks_mode') || $request->has('topper_ranks_title')) {
            $settings['topper_ranks_active'] = $request->has('topper_ranks_active') ? 1 : 0;
            $settings['topper_ranks_mode'] = $request->input('topper_ranks_mode', 'auto');
            $settings['topper_ranks_title'] = $request->input('topper_ranks_title', 'Academic Hall of Fame · Top Rankers');
            $settings['topper_ranks_subtitle'] = $request->input('topper_ranks_subtitle', 'Celebrating academic dedication, discipline, and intellectual brilliance across all classes & sections.');
            $settings['topper_ranks_exam_term'] = $request->input('topper_ranks_exam_term');

            $customToppers = $settings['topper_ranks_custom'] ?? [];
            for ($r = 1; $r <= 3; $r++) {
                $pfx = "topper_{$r}_";
                if ($request->has($pfx . 'name')) {
                    $customToppers[$pfx . 'name'] = $request->input($pfx . 'name');
                    $customToppers[$pfx . 'class'] = $request->input($pfx . 'class');
                    $customToppers[$pfx . 'section'] = $request->input($pfx . 'section');
                    $customToppers[$pfx . 'gpa'] = $request->input($pfx . 'gpa');
                    $customToppers[$pfx . 'percentage'] = $request->input($pfx . 'percentage');
                    $customToppers[$pfx . 'honors'] = $request->input($pfx . 'honors');

                    if ($request->hasFile($pfx . 'photo')) {
                        if (!empty($customToppers[$pfx . 'photo'])) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($customToppers[$pfx . 'photo']);
                        }
                        $customToppers[$pfx . 'photo'] = \App\Services\ImageWebpService::storeAsWebp(
                            $request->file($pfx . 'photo'), 'topper-photos', 'public'
                        );
                    } elseif ($request->input('remove_' . $pfx . 'photo') == '1' && !empty($customToppers[$pfx . 'photo'])) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($customToppers[$pfx . 'photo']);
                        unset($customToppers[$pfx . 'photo']);
                    }
                }
            }
            $settings['topper_ranks_custom'] = $customToppers;
        }

        // Exam Routine File Upload
        if ($request->hasFile('exam_routine_file')) {
            if (!empty($settings['exam_routine_file'])) {
                Storage::disk('public')->delete($settings['exam_routine_file']);
            }
            $file = $request->file('exam_routine_file');
            $ext = strtolower($file->getClientOriginalExtension());
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $settings['exam_routine_file'] = \App\Services\ImageWebpService::storeAsWebp($file, 'exam-routines', 'public');
            } else {
                $settings['exam_routine_file'] = $file->store('exam-routines', 'public');
            }
        } elseif ($request->input('remove_exam_routine_file') == '1' && !empty($settings['exam_routine_file'])) {
            Storage::disk('public')->delete($settings['exam_routine_file']);
            $settings['exam_routine_file'] = null;
        }

        // ── Custom Section Images (Auto-converted to WebP) ───────────────────
        $sectionImageKeys = [
            'admissions_slide1_image', 'admissions_slide2_image', 'admissions_slide3_image',
            'program_ecc_image', 'principal_photo_image', 'founder_photo_image',
            'outreach_visual_image', 'campus_video_cover_image',
            'pub_blogs_image', 'pub_newsletter_image', 'pub_cornice_image', 'pub_circle_image',
            'pathway_preschool_image', 'pathway_primary_image', 'pathway_secondary_image',
            'pathway_plus2_image', 'pathway_steam_image', 'pathway_sports_image',
            'facility_stem_image', 'facility_computer_image', 'facility_library_image',
            'facility_sports_image', 'facility_transport_image', 'facility_classroom_image',
        ];

        $sectionImages = $settings['section_images'] ?? [];
        foreach ($sectionImageKeys as $secKey) {
            if ($request->hasFile($secKey)) {
                if (!empty($sectionImages[$secKey])) {
                    Storage::disk('public')->delete($sectionImages[$secKey]);
                }
                $sectionImages[$secKey] = \App\Services\ImageWebpService::storeAsWebp(
                    $request->file($secKey), 'section-images', 'public'
                );
            } elseif ($request->input('remove_' . $secKey) == '1' && !empty($sectionImages[$secKey])) {
                Storage::disk('public')->delete($sectionImages[$secKey]);
                unset($sectionImages[$secKey]);
            }
        }
        $settings['section_images'] = $sectionImages;

        $school->settings = $settings;

        // ── Logo ─────────────────────────────────────────────────────────────
        if ($request->hasFile('logo')) {
            if ($school->logo) Storage::disk('public')->delete($school->logo);
            $school->logo = \App\Services\ImageWebpService::storeAsWebp($request->file('logo'), 'school-logos', 'public');
        } elseif ($request->remove_logo == '1' && $school->logo) {
            Storage::disk('public')->delete($school->logo);
            $school->logo = null;
        }

        // ── Gallery ───────────────────────────────────────────────────────────
        $gallery = $school->gallery_images ?? [];

        // 1. Replace individual images (replace_gallery_image[idx] => file)
        if ($request->hasFile('replace_gallery_image')) {
            foreach ($request->file('replace_gallery_image') as $idx => $file) {
                // Delete old file from disk
                if (isset($gallery[$idx])) {
                    Storage::disk('public')->delete($gallery[$idx]);
                }
                // Store replacement as WebP
                $gallery[$idx] = \App\Services\ImageWebpService::storeAsWebp($file, 'school-gallery', 'public');
            }
        }

        // 2. Delete marked images (skip any that were also replaced)
        if ($request->has('delete_gallery_images')) {
            $replaceKeys = array_keys($request->file('replace_gallery_image') ?? []);
            foreach ((array) $request->input('delete_gallery_images') as $idx) {
                if (in_array((int)$idx, array_map('intval', $replaceKeys))) continue; // already replaced
                if (isset($gallery[$idx])) {
                    Storage::disk('public')->delete($gallery[$idx]);
                    unset($gallery[$idx]);
                }
            }
            $gallery = array_values($gallery);
        }

        // 3. Append new uploads
        if ($request->hasFile('new_gallery_images')) {
            foreach ($request->file('new_gallery_images') as $file) {
                $gallery[] = \App\Services\ImageWebpService::storeAsWebp($file, 'school-gallery', 'public');
            }
        }

        // ── Payment QR codes ──────────────────────────────────────────────────
        foreach (['payment_qr_esewa', 'payment_qr_khalti', 'payment_qr_bank', 'payment_qr_fonepay'] as $qrField) {
            if ($request->hasFile($qrField)) {
                // Delete old file
                if ($school->$qrField) {
                    Storage::disk('public')->delete($school->$qrField);
                }
                $school->$qrField = \App\Services\ImageWebpService::storeAsWebp(
                    $request->file($qrField), 'payment-qr', 'public'
                );
            } elseif ($request->input("remove_{$qrField}") == '1' && $school->$qrField) {
                Storage::disk('public')->delete($school->$qrField);
                $school->$qrField = null;
            }
        }
        if (isset($data['payment_instructions'])) {
            $school->payment_instructions = $data['payment_instructions'];
        }

        // ── Theme: if a preset was chosen, apply its colours unless the user
        //    also overrode them manually (non-empty custom hex wins) ──────────
        $preset = $data['preset_theme'] ?? null;
        if ($preset && isset(School::PRESET_THEMES[$preset])) {
            $theme = School::PRESET_THEMES[$preset];
            // Only overwrite if the user did NOT send a custom value
            if (empty($data['primary_color']))   $data['primary_color']   = $theme['primary'];
            if (empty($data['secondary_color'])) $data['secondary_color'] = $theme['secondary'];
        }

        // ── Hero Slider Banners (Images & MP4 Autoplay Videos) ────────────────
        $sliders = $school->slider_banners ?? [];

        // 1. Update text metadata for existing sliders
        if ($request->has('slider_banners') && is_array($request->input('slider_banners'))) {
            foreach ($request->input('slider_banners') as $idx => $meta) {
                if (isset($sliders[$idx])) {
                    $sliders[$idx]['title']              = $meta['title'] ?? ($sliders[$idx]['title'] ?? null);
                    $sliders[$idx]['subtitle']           = $meta['subtitle'] ?? ($sliders[$idx]['subtitle'] ?? null);
                    $sliders[$idx]['tag']                = $meta['tag'] ?? ($sliders[$idx]['tag'] ?? null);
                    $sliders[$idx]['btn_text']           = $meta['btn_text'] ?? ($sliders[$idx]['btn_text'] ?? null);
                    $sliders[$idx]['btn_url']            = $meta['btn_url'] ?? ($sliders[$idx]['btn_url'] ?? null);
                    $sliders[$idx]['secondary_btn_text'] = $meta['secondary_btn_text'] ?? ($sliders[$idx]['secondary_btn_text'] ?? null);
                    $sliders[$idx]['secondary_btn_url']  = $meta['secondary_btn_url'] ?? ($sliders[$idx]['secondary_btn_url'] ?? null);
                }
            }
        }

        // 2. Replace individual slide media (Image or MP4 Video)
        if ($request->hasFile('replace_slider_image')) {
            foreach ($request->file('replace_slider_image') as $idx => $file) {
                if (isset($sliders[$idx])) {
                    if (!empty($sliders[$idx]['image']) && Storage::disk('public')->exists($sliders[$idx]['image'])) {
                        Storage::disk('public')->delete($sliders[$idx]['image']);
                    }
                    if (\App\Services\ImageWebpService::isImage($file)) {
                        $sliders[$idx]['image'] = \App\Services\ImageWebpService::storeAsWebp($file, 'school-sliders', 'public');
                        $sliders[$idx]['type']  = 'image';
                    } else {
                        $sliders[$idx]['image'] = $file->store('school-sliders', 'public');
                        $sliders[$idx]['type']  = 'video';
                    }
                }
            }
        }

        // 3. Delete marked slides
        if ($request->has('delete_slider_banners')) {
            foreach ((array) $request->input('delete_slider_banners') as $idx) {
                if (isset($sliders[$idx])) {
                    if (!empty($sliders[$idx]['image']) && Storage::disk('public')->exists($sliders[$idx]['image'])) {
                        Storage::disk('public')->delete($sliders[$idx]['image']);
                    }
                    unset($sliders[$idx]);
                }
            }
            $sliders = array_values($sliders);
        }

        // 4. Add new slide if uploaded (Image or MP4 Video)
        if ($request->hasFile('new_slider_image')) {
            $file = $request->file('new_slider_image');
            if (\App\Services\ImageWebpService::isImage($file)) {
                $newMedia = \App\Services\ImageWebpService::storeAsWebp($file, 'school-sliders', 'public');
                $mediaType = 'image';
            } else {
                $newMedia = $file->store('school-sliders', 'public');
                $mediaType = 'video';
            }

            $sliders[] = [
                'image'              => $newMedia,
                'type'               => $mediaType,
                'title'              => $request->input('new_slider_title') ?: null,
                'subtitle'           => $request->input('new_slider_subtitle') ?: null,
                'tag'                => $request->input('new_slider_tag') ?: null,
                'btn_text'           => $request->input('new_slider_btn_text') ?: null,
                'btn_url'            => $request->input('new_slider_btn_url') ?: null,
                'secondary_btn_text' => $request->input('new_slider_secondary_btn_text') ?: null,
                'secondary_btn_url'  => $request->input('new_slider_secondary_btn_url') ?: null,
            ];
        }

        $school->slider_banners = $sliders;

        // ── Persist ───────────────────────────────────────────────────────────
        $school->gallery_images = $gallery;
        $school->fill(array_filter([
            'name'              => $data['name'],
            'iemis_code'         => $data['iemis_code']         ?? null,
            'email'             => $data['email'],
            'phone'             => $data['phone']             ?? null,
            'website'           => $data['website']           ?? null,
            'contact_person'    => $data['contact_person']    ?? null,
            'contact_email'     => $data['contact_email']     ?? null,
            'address'           => $data['address']           ?? null,
            'city'              => $data['city']              ?? null,
            'district'          => $data['district']          ?? null,
            'province'          => $data['province']          ?? null,
            'country'           => $data['country']           ?? null,
            'preset_theme'      => $data['preset_theme']      ?? null,
            'primary_color'     => $data['primary_color']     ?? null,
            'secondary_color'   => $data['secondary_color']   ?? null,
            'facebook_url'      => $data['facebook_url']      ?? null,
            'twitter_url'       => $data['twitter_url']       ?? null,
            'instagram_url'     => $data['instagram_url']     ?? null,
            'youtube_url'       => $data['youtube_url']       ?? null,
            'description'       => $data['description']       ?? null,
            'about_us'          => $data['about_us']          ?? null,
            'mission'           => $data['mission']           ?? null,
            'vision'            => $data['vision']            ?? null,
            'history'           => $data['history']           ?? null,
            'principal_message' => $data['principal_message'] ?? null,
            'admission_info'    => $data['admission_info']    ?? null,
            'result_info'       => $data['result_info']       ?? null,
            'notice_board'      => $data['notice_board']      ?? null,
        ], fn($v) => $v !== null));

        // ── Popup Notice ───────────────────────────────────────────────────────
        $school->popup_active    = $request->has('popup_active');
        $school->popup_show_once = $request->has('popup_show_once');
        $school->popup_title     = $request->input('popup_title');
        $school->popup_subtitle  = $request->input('popup_subtitle');
        $school->popup_btn_text  = $request->input('popup_btn_text');
        $school->popup_btn_url   = $request->input('popup_btn_url');
        $school->popup_expires_at= $request->input('popup_expires_at') ?: null;

        if ($request->hasFile('popup_image')) {
            if ($school->popup_image) {
                Storage::disk('public')->delete($school->popup_image);
            }
            $school->popup_image = \App\Services\ImageWebpService::storeAsWebp(
                $request->file('popup_image'), 'school-popup', 'public'
            );
        } elseif ($request->input('remove_popup_image') == '1' && $school->popup_image) {
            Storage::disk('public')->delete($school->popup_image);
            $school->popup_image = null;
        }

        $school->save();

        $step = $request->input('active_step', 1);

        return redirect()
            ->route('school.profile.edit', ['step' => $step])
            ->with('success', 'School profile saved successfully.');
    }
}
