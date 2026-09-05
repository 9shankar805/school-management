@extends('layouts.app')
@section('title', 'School Profile')

@section('content')
<div class="flex-1 p-4 sm:p-6 lg:p-8 overflow-auto">

{{-- ── Page header ── --}}
<div class="flex flex-wrap items-start justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2">
            <i class="bi bi-building text-indigo-500"></i> School Profile
        </h1>
        <p class="text-sm text-slate-400 mt-0.5">Manage your school's information, branding and public landing page</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <a href="{{ route('school.profile.preview') }}" target="_blank"
           class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 rounded-xl text-xs font-semibold hover:bg-emerald-100 transition">
            <i class="bi bi-eye"></i> Preview Landing Page
        </a>
        <a href="{{ url('/school/' . $school->slug) }}" target="_blank"
           class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-400 rounded-xl text-xs font-semibold hover:bg-blue-100 transition">
            <i class="bi bi-box-arrow-up-right"></i> Public Page
        </a>
    </div>
</div>

{{-- ── Alerts ── --}}
@if(session('success'))
<div class="mb-5 flex items-center gap-2 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl text-sm text-emerald-700 dark:text-emerald-400">
    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
</div>
@endif
@if($errors->isNotEmpty())
<div class="mb-5 px-4 py-3 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl text-sm text-rose-700 dark:text-rose-400">
    <p class="font-semibold mb-1 flex items-center gap-1"><i class="bi bi-exclamation-circle-fill"></i> Please fix the errors below:</p>
    <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

{{-- ── Step progress bar ── --}}
@php
$steps = [
    ['icon'=>'bi-info-circle',    'label'=>'Basic Info'],
    ['icon'=>'bi-palette',        'label'=>'Branding'],
    ['icon'=>'bi-file-earmark-text','label'=>'Content'],
    ['icon'=>'bi-images',         'label'=>'Gallery & Notices'],
];
$activeStep = (int)(request('step', old('active_step', 1)));
$activeStep = max(1, min(4, $activeStep));
@endphp

<div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm mb-6 p-5">
    <div class="flex items-center justify-between gap-2 relative">
        {{-- connecting line behind --}}
        <div class="absolute left-0 right-0 top-5 h-0.5 bg-slate-200 dark:bg-slate-700 z-0 mx-8"></div>
        @foreach($steps as $i => $step)
        @php $n = $i + 1; $done = $n < $activeStep; $active = $n === $activeStep; @endphp
        <button type="button" onclick="goStep({{ $n }})"
                class="relative z-10 flex flex-col items-center gap-1.5 flex-1 group focus:outline-none step-btn"
                data-step="{{ $n }}">
            <div data-ring
                 class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-200 ring-2
                {{ $done   ? 'bg-emerald-500 text-white ring-emerald-200 dark:ring-emerald-800' : '' }}
                {{ $active ? 'bg-indigo-600 text-white ring-indigo-200 dark:ring-indigo-800 shadow-lg' : '' }}
                {{ (!$done && !$active) ? 'bg-white dark:bg-slate-700 text-slate-400 dark:text-slate-500 ring-slate-200 dark:ring-slate-600' : '' }}">
                @if($done)
                    <i class="bi bi-check-lg"></i>
                @else
                    <i class="bi {{ $step['icon'] }}"></i>
                @endif
            </div>
            <span data-label
                  class="text-xs font-semibold hidden sm:block transition-colors
                {{ $active ? 'text-indigo-600 dark:text-indigo-400' : ($done ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500') }}">
                {{ $step['label'] }}
            </span>
        </button>
        @endforeach
    </div>
</div>

{{-- ── Main form ── --}}
<form id="profileForm" action="{{ route('school.profile.update') }}" method="POST" enctype="multipart/form-data">
@csrf
<input type="hidden" name="active_step" id="active_step_input" value="{{ $activeStep }}">

{{-- ════════════════════════════════════════════════
     STEP 1 — BASIC INFO
════════════════════════════════════════════════ --}}
<div class="step-panel" id="step-1">
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Left: logo card --}}
    <div class="xl:col-span-1">
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-6">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
                <i class="bi bi-image text-violet-500"></i> School Logo
            </h3>
            <div class="flex flex-col items-center gap-4">
                <div id="logoPreviewWrap" class="w-28 h-28 rounded-2xl overflow-hidden border-2 border-dashed border-slate-300 dark:border-slate-600 flex items-center justify-center bg-slate-50 dark:bg-slate-700">
                    @if($school->logo)
                        <img src="{{ $school->logo_url }}" id="logoPreview" alt="Logo" class="w-full h-full object-cover">
                    @else
                        <i class="bi bi-building text-4xl text-slate-300 dark:text-slate-500" id="logoPlaceholder"></i>
                        <img id="logoPreview" class="hidden w-full h-full object-cover" alt="Preview">
                    @endif
                </div>
                <label class="cursor-pointer px-4 py-2 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-800 text-indigo-700 dark:text-indigo-400 text-xs font-semibold hover:bg-indigo-100 transition flex items-center gap-1.5">
                    <i class="bi bi-upload"></i> Upload Logo
                    <input type="file" name="logo" id="logoInput" accept="image/*" class="hidden">
                </label>
                <p class="text-xs text-slate-400 text-center">Square image, min 200×200px<br>JPEG, PNG — max 2 MB</p>
                @if($school->logo)
                <label class="flex items-center gap-2 cursor-pointer text-xs text-rose-600 dark:text-rose-400 hover:underline mt-1">
                    <input type="checkbox" name="remove_logo" value="1" class="rounded accent-rose-500">
                    Remove current logo
                </label>
                @endif
            </div>
        </div>

        {{-- Public URL card --}}
        <div class="mt-4 bg-gradient-to-br from-indigo-50 to-white dark:from-indigo-900/20 dark:to-slate-800 border border-indigo-100 dark:border-indigo-800 rounded-2xl p-5">
            <p class="text-xs font-bold text-indigo-700 dark:text-indigo-400 mb-1 flex items-center gap-1.5">
                <i class="bi bi-globe"></i> Public Landing Page
            </p>
            <code class="block text-xs text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 mt-2 break-all">{{ url('/school/'.$school->slug) }}</code>
            <a href="{{ url('/school/'.$school->slug) }}" target="_blank"
               class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                <i class="bi bi-box-arrow-up-right"></i> Open page
            </a>
        </div>
    </div>

    {{-- Right: fields --}}
    <div class="xl:col-span-2 flex flex-col gap-6">

        {{-- Basic info --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-6">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
                <i class="bi bi-info-circle text-blue-500"></i> School Details
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="field-label">School Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $school->name) }}" required class="field-input">
                </div>
                <div>
                    <label class="field-label">School IEMIS Code <span class="text-slate-400 font-normal">(9-Digit Code)</span></label>
                    <input type="text" name="iemis_code" value="{{ old('iemis_code', $school->iemis_code) }}" placeholder="e.g. 270010001" class="field-input">
                </div>
                <div>
                    <label class="field-label">Email Address <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $school->email) }}" required class="field-input">
                </div>
                <div>
                    <label class="field-label">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $school->phone) }}" class="field-input">
                </div>
                <div>
                    <label class="field-label">Website URL</label>
                    <input type="url" name="website" value="{{ old('website', $school->website) }}" placeholder="https://" class="field-input">
                </div>
                <div>
                    <label class="field-label">Short Description</label>
                    <input type="text" name="description" value="{{ old('description', $school->description) }}" placeholder="Shown on landing page hero" class="field-input">
                </div>
            </div>
        </div>

        {{-- Address --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-6">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
                <i class="bi bi-geo-alt text-emerald-500"></i> Address
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="field-label">Street Address</label>
                    <input type="text" name="address" value="{{ old('address', $school->address) }}" class="field-input">
                </div>
                <div><label class="field-label">City</label><input type="text" name="city" value="{{ old('city', $school->city) }}" class="field-input"></div>
                <div><label class="field-label">District</label><input type="text" name="district" value="{{ old('district', $school->district) }}" class="field-input"></div>
                <div><label class="field-label">Province / State</label><input type="text" name="province" value="{{ old('province', $school->province) }}" class="field-input"></div>
                <div><label class="field-label">Country</label><input type="text" name="country" value="{{ old('country', $school->country) }}" class="field-input"></div>
            </div>
        </div>

        {{-- Contact person --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-6">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
                <i class="bi bi-person-badge text-purple-500"></i> Contact Person
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><label class="field-label">Name</label><input type="text" name="contact_person" value="{{ old('contact_person', $school->contact_person) }}" class="field-input"></div>
                <div><label class="field-label">Email</label><input type="email" name="contact_email" value="{{ old('contact_email', $school->contact_email) }}" class="field-input"></div>
            </div>
        </div>

    </div>{{-- /right --}}
</div>{{-- /grid --}}
</div>{{-- /step-1 --}}

{{-- ════════════════════════════════════════════════
     STEP 2 — BRANDING & COLORS
════════════════════════════════════════════════ --}}
<div class="step-panel hidden" id="step-2">
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Left: preset themes --}}
    <div class="xl:col-span-2 flex flex-col gap-6">
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-6">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-1 flex items-center gap-2">
                <i class="bi bi-palette text-indigo-500"></i> Preset Themes
            </h3>
            <p class="text-xs text-slate-400 mb-5">Pick a ready-made colour scheme or customise your own below.</p>
            <input type="hidden" name="preset_theme" id="preset_theme_input" value="{{ old('preset_theme', $school->preset_theme ?? 'royal_blue') }}">
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                @foreach($presetThemes as $key => $theme)
                <button type="button"
                        onclick="selectPreset('{{ $key }}', '{{ $theme['primary'] }}', '{{ $theme['secondary'] }}')"
                        id="preset-{{ $key }}"
                        class="preset-card group relative rounded-xl border-2 p-4 text-left transition-all duration-200 hover:shadow-md focus:outline-none
                               {{ ($school->preset_theme ?? 'royal_blue') === $key ? 'border-indigo-500 shadow-md' : 'border-slate-200 dark:border-slate-600 hover:border-indigo-300' }}">
                    {{-- Colour swatches --}}
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 rounded-lg shadow-sm" style="background:{{ $theme['primary'] }};"></div>
                        <div class="w-5 h-5 rounded-md shadow-sm" style="background:{{ $theme['secondary'] }};"></div>
                    </div>
                    <div class="text-xs font-bold text-slate-700 dark:text-slate-200">{{ $theme['label'] }}</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $theme['primary'] }} · {{ $theme['secondary'] }}</div>
                    {{-- Checkmark when selected --}}
                    <div class="absolute top-2 right-2 w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[10px] transition-all
                                {{ ($school->preset_theme ?? 'royal_blue') === $key ? 'opacity-100 scale-100' : 'opacity-0 scale-75' }}"
                         id="preset-check-{{ $key }}">
                        <i class="bi bi-check-lg"></i>
                    </div>
                </button>
                @endforeach
            </div>
        </div>

        {{-- Custom colors --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-6">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-1 flex items-center gap-2">
                <i class="bi bi-eyedropper text-amber-500"></i> Custom Colors
            </h3>
            <p class="text-xs text-slate-400 mb-5">Override the preset with your own brand colours.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="field-label">Primary Brand Color</label>
                    <p class="text-[11px] text-slate-400 mb-2">Main buttons, navbar accents, section highlights</p>
                    <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-xl">
                        <input type="color" name="primary_color" id="primary_color"
                               value="{{ old('primary_color', $school->primary_color ?? '#1e4080') }}"
                               oninput="syncColorText('primary_color','primary_color_text'); updatePreview();"
                               class="w-11 h-11 rounded-lg cursor-pointer border-0 bg-transparent p-0">
                        <div class="flex-1">
                            <input type="text" id="primary_color_text"
                                   value="{{ old('primary_color', $school->primary_color ?? '#1e4080') }}"
                                   oninput="syncColorPicker('primary_color_text','primary_color'); updatePreview();"
                                   maxlength="7" placeholder="#1e4080"
                                   class="w-full font-mono text-sm bg-transparent border-0 focus:outline-none text-slate-700 dark:text-slate-200">
                        </div>
                    </div>
                </div>
                <div>
                    <label class="field-label">Secondary Accent Color</label>
                    <p class="text-[11px] text-slate-400 mb-2">Supporting accents, gold trim, info elements</p>
                    <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-xl">
                        <input type="color" name="secondary_color" id="secondary_color"
                               value="{{ old('secondary_color', $school->secondary_color ?? '#e6b800') }}"
                               oninput="syncColorText('secondary_color','secondary_color_text'); updatePreview();"
                               class="w-11 h-11 rounded-lg cursor-pointer border-0 bg-transparent p-0">
                        <div class="flex-1">
                            <input type="text" id="secondary_color_text"
                                   value="{{ old('secondary_color', $school->secondary_color ?? '#e6b800') }}"
                                   oninput="syncColorPicker('secondary_color_text','secondary_color'); updatePreview();"
                                   maxlength="7" placeholder="#e6b800"
                                   class="w-full font-mono text-sm bg-transparent border-0 focus:outline-none text-slate-700 dark:text-slate-200">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Social links --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-6">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
                <i class="bi bi-share text-sky-500"></i> Social Media Links
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach([['facebook_url','bi-facebook','#2563eb','Facebook page URL'],['twitter_url','bi-twitter-x','#0f172a','Twitter / X profile URL'],['instagram_url','bi-instagram','#db2777','Instagram profile URL'],['youtube_url','bi-youtube','#dc2626','YouTube channel URL']] as [$field,$icon,$color,$placeholder])
                <div>
                    <label class="field-label flex items-center gap-1.5">
                        <i class="bi {{ $icon }}" style="color:{{ $color }};"></i>
                        {{ ucfirst(str_replace(['_url','twitter'],['','X'],$field)) }}
                    </label>
                    <input type="url" name="{{ $field }}" value="{{ old($field, $school->$field) }}"
                           placeholder="{{ $placeholder }}" class="field-input">
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Right: live preview --}}
    <div class="xl:col-span-1">
        <div class="sticky top-24 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-5">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-3 flex items-center gap-2">
                <i class="bi bi-display text-emerald-500"></i> Live Preview
            </h3>
            {{-- Mini landing page preview --}}
            <div class="rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 text-[10px]">
                {{-- Fake nav --}}
                <div id="prev-nav" class="flex items-center justify-between px-3 py-2" style="background:#f8fafc;">
                    <div class="flex items-center gap-1.5">
                        <div class="w-5 h-5 rounded" id="prev-logo-swatch" style="background:#1e4080;"></div>
                        <span class="font-bold text-[9px] text-slate-700">{{ $school->name }}</span>
                    </div>
                    <div id="prev-btn" class="px-2 py-0.5 rounded text-white font-bold text-[8px]"
                         style="background:#1e4080;">Apply</div>
                </div>
                {{-- Fake hero --}}
                <div id="prev-hero" class="px-3 py-5 text-white text-center"
                     style="background:linear-gradient(135deg,#0a1628,#1e4080);">
                    <div class="font-extrabold text-[11px] leading-tight mb-1">{{ Str::limit($school->name,24) }}</div>
                    <div class="text-[8px] opacity-75 mb-3">Excellence in Education</div>
                    <div id="prev-cta" class="inline-block px-3 py-1 rounded-lg font-bold text-[8px] text-slate-900"
                         style="background:#e6b800;">Apply Now</div>
                </div>
                {{-- Fake stats bar --}}
                <div id="prev-stat" class="flex justify-around py-2 text-white text-center text-[8px] font-bold"
                     style="background:#0f2240;">
                    <div>500+ <div class="opacity-60 font-normal">Students</div></div>
                    <div>50+ <div class="opacity-60 font-normal">Teachers</div></div>
                    <div>100% <div class="opacity-60 font-normal">Results</div></div>
                </div>
                {{-- Fake card --}}
                <div class="px-3 py-3" style="background:#f8fafc;">
                    <div class="flex gap-2">
                        @foreach(['bi-book-half','bi-display','bi-shield-lock'] as $ico)
                        <div class="flex-1 rounded-lg p-2 text-center bg-white border border-slate-100 shadow-sm">
                            <i class="bi {{ $ico }} text-[14px]" id="prev-icon-{{ $loop->index }}"></i>
                            <div class="text-[7px] text-slate-500 mt-0.5">Feature</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                {{-- Primary / Outline buttons --}}
                <div class="px-3 pb-3 pt-1 flex gap-2" style="background:#f8fafc;">
                    <div id="prev-primary-btn" class="flex-1 text-center py-1.5 rounded-lg text-[8px] font-bold text-white"
                         style="background:#1e4080;">Primary button</div>
                    <div id="prev-outline-btn" class="flex-1 text-center py-1.5 rounded-lg text-[8px] font-bold border-2"
                         style="border-color:#1e4080;color:#1e4080;">Outline button</div>
                </div>
            </div>

            {{-- Swatch strip --}}
            <div class="mt-4 flex items-center gap-2">
                <div id="swatch-primary" class="w-8 h-8 rounded-lg shadow border border-slate-200" style="background:#1e4080;"></div>
                <div id="swatch-secondary" class="w-6 h-6 rounded-md shadow border border-slate-200" style="background:#e6b800;"></div>
                <div class="ml-auto text-[10px] text-slate-400">
                    <span id="swatch-primary-label">#1e4080</span> · <span id="swatch-secondary-label">#e6b800</span>
                </div>
            </div>
        </div>
    </div>

</div>{{-- /grid --}}
</div>{{-- /step-2 --}}

{{-- ════════════════════════════════════════════════
     STEP 3 — CONTENT
════════════════════════════════════════════════ --}}
<div class="step-panel hidden" id="step-3">
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-6">
        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
            <i class="bi bi-file-earmark-person text-blue-500"></i> About the School
        </h3>
        <div class="space-y-4">
            <div>
                <label class="field-label">About Us <span class="text-[10px] text-slate-400 font-normal">— shown under "Why Choose Us"</span></label>
                <textarea name="about_us" rows="4" class="field-input resize-y">{{ old('about_us', $school->about_us) }}</textarea>
            </div>
            <div>
                <label class="field-label">Mission Statement</label>
                <textarea name="mission" rows="3" class="field-input resize-y">{{ old('mission', $school->mission) }}</textarea>
            </div>
            <div>
                <label class="field-label">Vision Statement</label>
                <textarea name="vision" rows="3" class="field-input resize-y">{{ old('vision', $school->vision) }}</textarea>
            </div>
            <div>
                <label class="field-label">School History</label>
                <textarea name="history" rows="4" class="field-input resize-y">{{ old('history', $school->history) }}</textarea>
            </div>
            <div>
                <label class="field-label">Principal's Message</label>
                <textarea name="principal_message" rows="4" class="field-input resize-y">{{ old('principal_message', $school->principal_message) }}</textarea>
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-6">
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-6">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
                <i class="bi bi-mortarboard text-purple-500"></i> Admission &amp; Results
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="field-label">Admission Information</label>
                    <textarea name="admission_info" rows="5" class="field-input resize-y" placeholder="Admission process, requirements, important dates…">{{ old('admission_info', $school->admission_info) }}</textarea>
                </div>
                <div>
                    <label class="field-label">Results Information</label>
                    <textarea name="result_info" rows="5" class="field-input resize-y" placeholder="How to check results, recent achievements…">{{ old('result_info', $school->result_info) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Tips panel --}}
        <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 rounded-2xl p-5">
            <p class="text-xs font-bold text-indigo-700 dark:text-indigo-400 mb-2 flex items-center gap-1.5">
                <i class="bi bi-lightbulb-fill"></i> Writing Tips
            </p>
            <ul class="text-xs text-indigo-600 dark:text-indigo-300 space-y-1.5 list-disc list-inside">
                <li>Keep <strong>About Us</strong> under 300 words — visitors skim.</li>
                <li>Mission &amp; Vision should be 1–2 clear sentences each.</li>
                <li>For Admission info, list dates, fees, and required docs clearly.</li>
                <li>Results — highlight board exam pass rates and top achievers.</li>
            </ul>
        </div>
    </div>

</div>
</div>{{-- /step-3 --}}

{{-- ════════════════════════════════════════════════
     STEP 4 — GALLERY & NOTICES
════════════════════════════════════════════════ --}}
<div class="step-panel hidden" id="step-4">
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

    {{-- Gallery --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                <i class="bi bi-images text-emerald-500"></i> School Gallery
            </h3>
            @if($school->gallery_images && is_array($school->gallery_images) && count($school->gallery_images))
            <span class="text-xs text-slate-400 bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-full">
                {{ count($school->gallery_images) }} image{{ count($school->gallery_images) !== 1 ? 's' : '' }}
            </span>
            @endif
        </div>

        {{-- ── Upload drop zone ── --}}
        <label id="galleryDropZone"
               class="flex flex-col items-center justify-center gap-2 w-full border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl py-8 cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition mb-4">
            <i class="bi bi-cloud-arrow-up text-3xl text-slate-400"></i>
            <span class="text-xs text-slate-500 font-semibold">Click to upload new images</span>
            <span class="text-[10px] text-slate-400">JPEG, PNG, WebP — max 2 MB each · Multiple allowed</span>
            <input type="file" name="new_gallery_images[]" id="galleryInput"
                   multiple accept="image/jpeg,image/png,image/jpg,image/webp"
                   class="hidden" onchange="previewGalleryFiles(this)">
        </label>

        {{-- New-file preview (JS-populated) --}}
        <div id="newGalleryPreview" class="grid grid-cols-3 sm:grid-cols-4 gap-3 mb-5"></div>

        {{-- ── Existing images ── --}}
        @if($school->gallery_images && is_array($school->gallery_images) && count($school->gallery_images))

        <div class="flex items-center justify-between mb-3">
            <p class="field-label mb-0">Existing Images</p>
            <button type="button" onclick="confirmDeleteAll()"
                    class="text-[11px] font-semibold text-rose-600 hover:text-rose-700 flex items-center gap-1 hover:underline">
                <i class="bi bi-trash"></i> Mark all for deletion
            </button>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4" id="galleryGrid">
            @foreach($school->gallery_images as $idx => $img)
            <div class="gallery-item rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 overflow-hidden transition-all duration-200"
                 id="gallery-item-{{ $idx }}">

                {{-- Image --}}
                <div class="relative" style="aspect-ratio:4/3;">
                    <img src="{{ asset('storage/'.$img) }}"
                         class="w-full h-full object-cover"
                         id="gallery-img-{{ $idx }}"
                         alt="Gallery image {{ $idx + 1 }}">

                    {{-- Image number badge --}}
                    <span class="absolute top-1.5 left-1.5 bg-black/50 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full">
                        #{{ $idx + 1 }}
                    </span>

                    {{-- "Will be deleted" full overlay --}}
                    <div class="absolute inset-0 bg-rose-700/80 hidden flex-col items-center justify-center gap-1"
                         id="gallery-del-mask-{{ $idx }}">
                        <i class="bi bi-trash-fill text-white text-2xl"></i>
                        <span class="text-white text-xs font-bold">Will be deleted</span>
                    </div>

                    {{-- "Replaced" badge --}}
                    <div class="absolute top-1.5 right-1.5 bg-indigo-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full hidden"
                         id="gallery-rep-badge-{{ $idx }}">
                        <i class="bi bi-arrow-repeat"></i> Replaced
                    </div>
                </div>

                {{-- ── Always-visible action bar ── --}}
                <div class="flex gap-0 border-t border-slate-200 dark:border-slate-600" id="gallery-actions-{{ $idx }}">

                    {{-- Replace button --}}
                    <label class="flex-1 flex items-center justify-center gap-1.5 py-2 cursor-pointer text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition border-r border-slate-200 dark:border-slate-600"
                           title="Replace this image with a new one">
                        <i class="bi bi-pencil-square"></i> Replace
                        <input type="file"
                               name="replace_gallery_image[{{ $idx }}]"
                               accept="image/jpeg,image/png,image/jpg,image/webp"
                               class="hidden"
                               onchange="replaceGalleryPreview(this, {{ $idx }})">
                    </label>

                    {{-- Delete / Undo button --}}
                    <button type="button"
                            onclick="toggleGalleryDelete({{ $idx }})"
                            id="gallery-del-btn-{{ $idx }}"
                            class="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/30 transition"
                            title="Mark this image for deletion">
                        <i class="bi bi-trash" id="gallery-del-icon-{{ $idx }}"></i>
                        <span id="gallery-del-label-{{ $idx }}">Delete</span>
                    </button>
                </div>

                {{-- Hidden checkbox submitted with form --}}
                <input type="checkbox"
                       name="delete_gallery_images[]"
                       value="{{ $idx }}"
                       id="gallery-del-chk-{{ $idx }}"
                       class="hidden">
            </div>
            @endforeach
        </div>

        {{-- Deletion summary bar --}}
        <div id="galleryDeleteBar"
             class="hidden mt-4 flex items-center gap-3 px-4 py-2.5 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl text-sm">
            <i class="bi bi-exclamation-triangle-fill text-rose-500"></i>
            <span class="text-rose-700 dark:text-rose-400 text-xs font-semibold flex-1">
                <span id="galleryDeleteCount">0</span> image(s) marked for deletion — will be removed when you save.
            </span>
            <button type="button" onclick="clearAllDeletes()"
                    class="text-xs text-rose-600 hover:underline font-semibold">
                Clear all
            </button>
        </div>

        @else
        <div class="text-center py-8 text-slate-400">
            <i class="bi bi-images text-4xl block mb-2 opacity-30"></i>
            <p class="text-xs italic">No gallery images yet. Upload some above.</p>
        </div>
        @endif
    </div>{{-- /gallery card --}}

    {{-- Notice board --}}
    <div class="flex flex-col gap-6">
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-6">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
                <i class="bi bi-megaphone text-amber-500"></i> Notice Board
            </h3>
            <label class="field-label">Latest Announcements</label>
            <p class="text-[11px] text-slate-400 mb-2">Each line becomes a separate notice on the landing page.</p>
            <textarea name="notice_board" rows="10" class="field-input resize-y"
                      placeholder="• Admission open for session 2082-83&#10;• Parent-Teacher meeting on 15 Aug 2026&#10;• Annual sports day on 20 Sep 2026">{{ old('notice_board', $school->notice_board) }}</textarea>
        </div>

        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 rounded-2xl p-5">
            <p class="text-xs font-bold text-amber-700 dark:text-amber-400 mb-2 flex items-center gap-1.5">
                <i class="bi bi-info-circle-fill"></i> Notice Board Tips
            </p>
            <ul class="text-xs text-amber-700 dark:text-amber-300 space-y-1.5 list-disc list-inside">
                <li>One notice per line — displayed as a styled card on your landing page.</li>
                <li>Keep each notice short and direct — ideally under 120 characters.</li>
                <li>Start with the most important / recent notice.</li>
            </ul>
        </div>
    </div>

</div>
</div>{{-- /step-4 --}}

{{-- ════════════════════════════════════════════════
     STEP 5 — PAYMENT QR CODES
════════════════════════════════════════════════ --}}
<div class="step-panel hidden" id="step-5">
<div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-6 mb-6">
    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-1 flex items-center gap-2">
        <i class="bi bi-qr-code text-emerald-600"></i> Payment QR Codes
    </h3>
    <p class="text-xs text-slate-400 mb-6">
        Upload QR images for eSewa, Khalti, FonePay or your bank account.
        These will appear on every fee invoice so parents can scan and pay directly from their phone — no gateway setup needed.
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        @foreach([
            ['field'=>'payment_qr_esewa',   'label'=>'eSewa QR',   'color'=>'#60bb46', 'icon'=>'bi-phone'],
            ['field'=>'payment_qr_khalti',  'label'=>'Khalti QR',  'color'=>'#5c2d91', 'icon'=>'bi-phone'],
            ['field'=>'payment_qr_bank',    'label'=>'Bank / Connect IPS QR', 'color'=>'#1d4ed8', 'icon'=>'bi-bank'],
            ['field'=>'payment_qr_fonepay', 'label'=>'FonePay QR', 'color'=>'#e3001e', 'icon'=>'bi-upc-scan'],
        ] as $qr)
        @php $field = $qr['field']; $current = $school->$field ?? null; @endphp
        <div class="border border-slate-200 dark:border-slate-700 rounded-2xl p-4">
            <div class="flex items-center gap-2 mb-3">
                <span class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-sm"
                      style="background:{{ $qr['color'] }}">
                    <i class="bi {{ $qr['icon'] }}"></i>
                </span>
                <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ $qr['label'] }}</span>
                @if($current)
                <span class="ml-auto text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-semibold">Active</span>
                @endif
            </div>

            {{-- Current QR preview --}}
            @if($current)
            <div class="flex items-start gap-3 mb-3 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                <img src="{{ Storage::disk('public')->url($current) }}"
                     alt="{{ $qr['label'] }}"
                     class="w-24 h-24 object-contain rounded-lg border border-slate-200 dark:border-slate-600 bg-white">
                <div class="flex flex-col gap-2">
                    <p class="text-xs text-slate-500 dark:text-slate-400">Current QR active. Upload a new one to replace it.</p>
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-rose-600">
                        <input type="checkbox" name="remove_{{ $field }}" value="1" class="rounded accent-rose-500">
                        Remove QR
                    </label>
                </div>
            </div>
            @endif

            {{-- Upload --}}
            <label class="flex flex-col items-center justify-center gap-1.5 w-full border-2 border-dashed
                           border-slate-300 dark:border-slate-600 rounded-xl py-5 cursor-pointer
                           hover:border-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition">
                <i class="bi bi-qr-code-scan text-2xl text-slate-400"></i>
                <span class="text-xs text-slate-500 font-semibold">
                    {{ $current ? 'Replace' : 'Upload' }} {{ $qr['label'] }}
                </span>
                <span class="text-[10px] text-slate-400">PNG / JPG / WebP · max 1 MB</span>
                <input type="file" name="{{ $field }}" accept="image/*" class="hidden"
                       onchange="previewQR(this, '{{ $field }}-preview')">
            </label>
            <img id="{{ $field }}-preview" class="hidden mt-3 mx-auto w-24 h-24 object-contain rounded-xl border border-slate-200" alt="Preview">
        </div>
        @endforeach
    </div>

    {{-- Payment instructions --}}
    <div class="mt-6">
        <label class="field-label">Payment Instructions <span class="text-slate-400 font-normal">(optional)</span></label>
        <p class="text-[11px] text-slate-400 mb-2">
            Text shown below the QR on every invoice — e.g. account name, phone number for eSewa, or notes for parents.
        </p>
        <textarea name="payment_instructions" rows="4" class="field-input resize-y"
                  placeholder="eSewa / Khalti: 98XXXXXXXX (School Name)&#10;Bank: Nepal Investment Bank, A/C 012345XXXXX, Branch: Kathmandu">{{ old('payment_instructions', $school->payment_instructions) }}</textarea>
    </div>
</div>
</div>{{-- /step-5 --}}

{{-- ════════════════════════════════════════════════
     FOOTER NAV (Prev / Next / Save)
════════════════════════════════════════════════ --}}
<div class="mt-6 flex items-center justify-between gap-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm px-6 py-4">
    <button type="button" id="btnPrev" onclick="goStep(currentStep - 1)"
            class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-sm font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 transition invisible">
        <i class="bi bi-arrow-left"></i> Previous
    </button>

    <div class="flex items-center gap-1.5">
        @for($i=1;$i<=5;$i++)
        <div class="w-2 h-2 rounded-full transition-all duration-300 dot-indicator
                    {{ $i === $activeStep ? 'bg-indigo-600 w-6' : ($i < $activeStep ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-600') }}"
             id="dot-{{ $i }}"></div>
        @endfor
    </div>

    <div class="flex items-center gap-2">
        {{-- Save on any step --}}
        <button type="submit"
                class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition">
            <i class="bi bi-floppy"></i> Save
        </button>
        {{-- Next step — always rendered, JS shows/hides it --}}
        <button type="button" id="btnNext"
                onclick="goStep(currentStep + 1)"
                class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition">
            Next <i class="bi bi-arrow-right"></i>
        </button>
    </div>
</div>

</form>
</div>{{-- /container --}}

{{-- ════════ Shared field styles ════════ --}}
<style>
.field-label { display:block; font-size:.7rem; font-weight:600; color:#64748b; margin-bottom:.25rem; }
.dark .field-label { color:#94a3b8; }
.field-input {
    width:100%;
    border:1px solid #e2e8f0;
    border-radius:.5rem;
    padding:.5rem .75rem;
    font-size:.875rem;
    background:#fff;
    color:#1e293b;
    transition:box-shadow .15s, border-color .15s;
}
.field-input:focus { outline:none; border-color:#818cf8; box-shadow:0 0 0 3px rgba(99,102,241,.15); }
.dark .field-input { background:#1e293b; border-color:#475569; color:#e2e8f0; }
.dark .field-input:focus { border-color:#818cf8; }
.preset-card { background:#fff; cursor:pointer; }
.dark .preset-card { background:#1e293b; }
</style>

<script>
/* ── Baked data from PHP ──────────────────────────────────────── */
const STEP_ICONS  = @json(collect($steps)->pluck('icon')->values());
const TOTAL_STEPS = 5;
let currentStep   = {{ $activeStep }};

function goStep(n) {
    n = Math.max(1, Math.min(TOTAL_STEPS, n));

    // Hide all panels, show target
    document.querySelectorAll('.step-panel').forEach(p => p.classList.add('hidden'));
    const target = document.getElementById('step-' + n);
    if (target) target.classList.remove('hidden');

    // Update step indicator circles
    document.querySelectorAll('.step-btn').forEach(btn => {
        const s    = parseInt(btn.dataset.step);
        const ring = btn.querySelector('[data-ring]');
        if (!ring) return;

        // Wipe existing colour classes cleanly
        const colourClasses = [
            'bg-emerald-500','bg-indigo-600','bg-white','dark:bg-slate-700',
            'text-white','text-slate-400','dark:text-slate-500',
            'ring-2','ring-emerald-200','ring-indigo-200','ring-slate-200',
            'dark:ring-emerald-800','dark:ring-indigo-800','dark:ring-slate-600',
            'shadow-lg','shadow-indigo-200'
        ];
        ring.classList.remove(...colourClasses);

        if (s < n) {
            ring.classList.add('bg-emerald-500','text-white','ring-2','ring-emerald-200','dark:ring-emerald-800');
            ring.innerHTML = '<i class="bi bi-check-lg"></i>';
        } else if (s === n) {
            ring.classList.add('bg-indigo-600','text-white','ring-2','ring-indigo-200','dark:ring-indigo-800','shadow-lg');
            ring.innerHTML = '<i class="bi ' + STEP_ICONS[s-1] + '"></i>';
        } else {
            ring.classList.add('bg-white','dark:bg-slate-700','text-slate-400','dark:text-slate-500','ring-2','ring-slate-200','dark:ring-slate-600');
            ring.innerHTML = '<i class="bi ' + STEP_ICONS[s-1] + '"></i>';
        }
    });

    // Dots
    document.querySelectorAll('.dot-indicator').forEach((d, i) => {
        const s = i + 1;
        d.style.cssText = 'height:0.5rem;border-radius:9999px;transition:all 0.3s;';
        if (s === n)      { d.style.width = '1.5rem'; d.style.background = '#4f46e5'; }
        else if (s < n)   { d.style.width = '0.5rem'; d.style.background = '#10b981'; }
        else              { d.style.width = '0.5rem'; d.style.background = '#cbd5e1'; }
    });

    // Prev/Next visibility
    const prev = document.getElementById('btnPrev');
    const next = document.getElementById('btnNext');
    if (prev) prev.classList.toggle('invisible', n <= 1);
    if (next) next.classList.toggle('invisible', n >= TOTAL_STEPS);

    // Sync hidden input
    const inp = document.getElementById('active_step_input');
    if (inp) inp.value = n;

    currentStep = n;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/* ── Preset theme picker ─────────────────────────────────────── */
function selectPreset(key, primary, secondary) {
    document.getElementById('preset_theme_input').value = key;
    // Update color inputs
    setColor('primary_color', primary);
    setColor('secondary_color', secondary);
    // Highlight card
    document.querySelectorAll('.preset-card').forEach(c => {
        c.classList.remove('border-indigo-500','shadow-md');
        c.classList.add('border-slate-200','dark:border-slate-600');
    });
    document.querySelectorAll('[id^="preset-check-"]').forEach(el => {
        el.classList.add('opacity-0','scale-75');
        el.classList.remove('opacity-100','scale-100');
    });
    const card  = document.getElementById('preset-' + key);
    const check = document.getElementById('preset-check-' + key);
    if (card)  { card.classList.remove('border-slate-200','dark:border-slate-600'); card.classList.add('border-indigo-500','shadow-md'); }
    if (check) { check.classList.remove('opacity-0','scale-75'); check.classList.add('opacity-100','scale-100'); }
    updatePreview();
}

function setColor(id, hex) {
    const picker = document.getElementById(id);
    const text   = document.getElementById(id + '_text');
    if (picker) picker.value = hex;
    if (text)   text.value   = hex;
}

function syncColorText(pickerId, textId) {
    const v = document.getElementById(pickerId)?.value;
    if (v) document.getElementById(textId).value = v;
}
function syncColorPicker(textId, pickerId) {
    const v = document.getElementById(textId)?.value;
    if (v && /^#[0-9a-fA-F]{6}$/.test(v)) document.getElementById(pickerId).value = v;
}

/* ── Live preview updater ──────────────────────────────────────── */
function updatePreview() {
    const p = document.getElementById('primary_color')?.value   || '#1e4080';
    const s = document.getElementById('secondary_color')?.value || '#e6b800';
    // Darken primary for hero bg
    const darken = shadeHex(p, -40);

    const set = (id, prop, val) => { const el = document.getElementById(id); if(el) el.style[prop] = val; };
    set('prev-logo-swatch','background', p);
    set('prev-btn','background', p);
    set('prev-hero','background', `linear-gradient(135deg, ${darken}, ${p})`);
    set('prev-cta','background', s);
    set('prev-stat','background', darken);
    set('prev-primary-btn','background', p);
    set('prev-outline-btn','borderColor', p);
    set('prev-outline-btn','color', p);
    set('swatch-primary','background', p);
    set('swatch-secondary','background', s);
    ['prev-icon-0','prev-icon-1','prev-icon-2'].forEach(id => { const el=document.getElementById(id); if(el) el.style.color=p; });
    const pl = document.getElementById('swatch-primary-label');
    const sl = document.getElementById('swatch-secondary-label');
    if(pl) pl.textContent = p;
    if(sl) sl.textContent = s;
}

function shadeHex(hex, amt) {
    let r = parseInt(hex.slice(1,3),16)+amt;
    let g = parseInt(hex.slice(3,5),16)+amt;
    let b = parseInt(hex.slice(5,7),16)+amt;
    r = Math.max(0,Math.min(255,r));
    g = Math.max(0,Math.min(255,g));
    b = Math.max(0,Math.min(255,b));
    return '#'+[r,g,b].map(x=>x.toString(16).padStart(2,'0')).join('');
}

/* ── Logo preview ─────────────────────────────────────────────── */
document.getElementById('logoInput')?.addEventListener('change', function() {
    if (!this.files[0]) return;
    const url = URL.createObjectURL(this.files[0]);
    const img = document.getElementById('logoPreview');
    const ph  = document.getElementById('logoPlaceholder');
    if (img) { img.src = url; img.classList.remove('hidden'); }
    if (ph)  ph.classList.add('hidden');
});

/* ── Gallery management ─────────────────────────────────────── */
function previewGalleryFiles(input) {
    const wrap = document.getElementById('newGalleryPreview');
    wrap.innerHTML = '';
    Array.from(input.files).forEach((f, i) => {
        const url = URL.createObjectURL(f);
        wrap.innerHTML += `
        <div class="relative rounded-xl overflow-hidden border-2 border-indigo-300 dark:border-indigo-700 bg-slate-100 dark:bg-slate-700" style="aspect-ratio:1;">
            <img src="${url}" class="w-full h-full object-cover">
            <div class="absolute top-1 left-1 bg-indigo-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full shadow">New</div>
        </div>`;
    });
    // Update drop zone label
    const label = document.getElementById('galleryDropZone')?.querySelector('span');
    if (label) label.textContent = `${input.files.length} file(s) selected`;
}

const _galleryDeleted = new Set();

function toggleGalleryDelete(idx) {
    const chk     = document.getElementById('gallery-del-chk-'   + idx);
    const mask    = document.getElementById('gallery-del-mask-'  + idx);
    const item    = document.getElementById('gallery-item-'      + idx);
    const icon    = document.getElementById('gallery-del-icon-'  + idx);
    const label   = document.getElementById('gallery-del-label-' + idx);
    const btn     = document.getElementById('gallery-del-btn-'   + idx);
    if (!chk) return;

    const marking = !chk.checked;
    chk.checked   = marking;

    if (marking) {
        _galleryDeleted.add(idx);
        // Show red overlay on image
        if (mask) { mask.classList.remove('hidden'); mask.classList.add('flex'); }
        // Red border on card
        if (item) { item.classList.add('border-rose-500'); item.classList.remove('border-slate-200','dark:border-slate-600'); }
        // Change button to "Undo"
        if (icon)  icon.className  = 'bi bi-arrow-counterclockwise';
        if (label) label.textContent = 'Undo';
        if (btn)   { btn.classList.add('text-amber-600','dark:text-amber-400'); btn.classList.remove('text-rose-600','dark:text-rose-400'); }
    } else {
        _galleryDeleted.delete(idx);
        if (mask) { mask.classList.add('hidden'); mask.classList.remove('flex'); }
        if (item) { item.classList.remove('border-rose-500'); item.classList.add('border-slate-200','dark:border-slate-600'); }
        if (icon)  icon.className  = 'bi bi-trash';
        if (label) label.textContent = 'Delete';
        if (btn)   { btn.classList.remove('text-amber-600','dark:text-amber-400'); btn.classList.add('text-rose-600','dark:text-rose-400'); }
    }
    _updateDeleteBar();
}

function _updateDeleteBar() {
    const bar   = document.getElementById('galleryDeleteBar');
    const count = document.getElementById('galleryDeleteCount');
    if (!bar) return;
    if (_galleryDeleted.size > 0) {
        bar.classList.remove('hidden');
        bar.classList.add('flex');
        if (count) count.textContent = _galleryDeleted.size;
    } else {
        bar.classList.add('hidden');
        bar.classList.remove('flex');
    }
}

function clearAllDeletes() {
    [..._galleryDeleted].forEach(idx => toggleGalleryDelete(idx));
}

function confirmDeleteAll() {
    if (!confirm('Mark ALL existing images for deletion? They will be removed when you save.')) return;
    document.querySelectorAll('[id^="gallery-del-chk-"]').forEach(chk => {
        const idx = parseInt(chk.id.replace('gallery-del-chk-', ''));
        if (!chk.checked) toggleGalleryDelete(idx);
    });
}

function replaceGalleryPreview(input, idx) {
    if (!input.files[0]) return;
    const url   = URL.createObjectURL(input.files[0]);
    const img   = document.getElementById('gallery-img-'      + idx);
    const badge = document.getElementById('gallery-rep-badge-'+ idx);
    const item  = document.getElementById('gallery-item-'     + idx);

    if (img)   img.src = url;
    if (badge) { badge.classList.remove('hidden'); }
    // Blue border = replaced
    if (item) {
        item.classList.remove('border-slate-200','dark:border-slate-600','border-rose-500');
        item.classList.add('border-indigo-500');
    }
    // If it was marked for deletion, unmark it
    const chk = document.getElementById('gallery-del-chk-' + idx);
    if (chk && chk.checked) toggleGalleryDelete(idx);
}

/* ── Init ─────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    goStep({{ $activeStep }});

    // QR image preview
    function previewQR(input, previewId) {
        const preview = document.getElementById(previewId);
        if (!preview) return;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    updatePreview();
});
</script>

@endsection
