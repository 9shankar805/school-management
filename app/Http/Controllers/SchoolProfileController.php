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
            ['icon' => 'bi-file-text',       'label' => 'Content'],
            ['icon' => 'bi-images',          'label' => 'Gallery'],
            ['icon' => 'bi-qr-code',         'label' => 'Payment QR'],
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
        ]);

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

        // ── Persist ───────────────────────────────────────────────────────────
        $school->gallery_images = $gallery;
        $school->fill(array_filter([
            'name'              => $data['name'],
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
        $school->save();

        $step = $request->input('active_step', 1);

        return redirect()
            ->route('school.profile.edit', ['step' => $step])
            ->with('success', 'School profile saved successfully.');
    }
}
