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

        return view('school-profile.edit', compact('school', 'presetThemes'));
    }

    public function update(Request $request): RedirectResponse
    {
        $school = auth()->user()->school;
        if (!$school) abort(404, 'School not found');

        $data = $request->validate([
            // Basic
            'name'              => 'required|string|max:255',
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
        ]);

        // ── Logo ─────────────────────────────────────────────────────────────
        if ($request->hasFile('logo')) {
            if ($school->logo) Storage::disk('public')->delete($school->logo);
            $school->logo = $request->file('logo')->store('school-logos', 'public');
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
                // Store replacement
                $gallery[$idx] = $file->store('school-gallery', 'public');
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
                $gallery[] = $file->store('school-gallery', 'public');
            }
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
