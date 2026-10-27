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
        <a href="{{ $school->subdomain_url }}" target="_blank"
           class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-400 rounded-xl text-xs font-semibold hover:bg-blue-100 transition">
            <i class="bi bi-box-arrow-up-right"></i> {{ $school->subdomain }}.{{ config('app.domain', 'nshlms.com') }}
        </a>
    </div>
</div>

{{-- ── Alerts ── --}}
@if(session('success'))
<div class="mb-5 flex items-center gap-2 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl text-sm text-emerald-700 dark:text-emerald-400">
    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
</div>
@endif
@if(isset($errors) && $errors->isNotEmpty())
<div class="mb-5 px-4 py-3 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl text-sm text-rose-700 dark:text-rose-400">
    <p class="font-semibold mb-1 flex items-center gap-1"><i class="bi bi-exclamation-circle-fill"></i> Please fix the errors below:</p>
    <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

{{-- ── Step progress bar ── --}}
@php
$steps = $steps ?? [
    ['icon'=>'bi-building',       'label'=>'Basic Info'],
    ['icon'=>'bi-palette',        'label'=>'Branding'],
    ['icon'=>'bi-sliders2',       'label'=>'Hero Slider'],
    ['icon'=>'bi-file-text',       'label'=>'Content'],
    ['icon'=>'bi-images',         'label'=>'Gallery'],
    ['icon'=>'bi-qr-code',         'label'=>'Payment QR'],
    ['icon'=>'bi-megaphone',       'label'=>'Popup Notice'],
];
$activeStep = (int)(request('step', old('active_step', 1)));
$activeStep = max(1, min(count($steps), $activeStep));
$progressPct = (int) round(($activeStep / count($steps)) * 100);
@endphp

<div class="profile-stepper-wrap mb-6" id="profileStepperContainer">
    {{-- Header with Step status and percentage --}}
    <div class="stepper-header flex items-center justify-between px-5 pt-4 pb-3 border-b border-slate-100 dark:border-slate-800/80">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-bold text-xs shadow-sm ring-1 ring-indigo-500/20">
                <span id="stepperStepNum">{{ $activeStep }}</span>/{{ count($steps) }}
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Current Section:</span>
                    <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200" id="stepperActiveTitle">{{ $steps[$activeStep-1]['label'] }}</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            <div class="hidden sm:flex flex-col items-end">
                <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Profile Setup</span>
            </div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/60 shadow-sm" id="stepperProgressBadge">
                <i class="bi bi-speedometer2 text-indigo-500"></i>
                <span id="stepperPercentText">{{ $progressPct }}%</span> Complete
            </div>
        </div>
    </div>

    {{-- Continuous Progress Track Bar --}}
    <div class="stepper-track-wrap w-full bg-slate-100 dark:bg-slate-800/80 h-1.5 relative overflow-hidden">
        <div class="stepper-progress-fill h-full bg-gradient-to-r from-indigo-500 via-indigo-600 to-emerald-500 transition-all duration-500 ease-out"
             id="stepperProgressFill"
             style="width: {{ $progressPct }}%;"></div>
    </div>

    {{-- Interactive Step Buttons Grid --}}
    <div class="profile-stepper-grid p-2 sm:p-2.5">
        @foreach($steps as $i => $step)
        @php
            $n = $i + 1;
            $done = $n < $activeStep;
            $active = $n === $activeStep;
            $stateClass = $active ? 'is-active' : ($done ? 'is-completed' : 'is-idle');
        @endphp
        <button type="button" onclick="goStep({{ $n }})"
                class="profile-step-btn {{ $stateClass }} step-btn group"
                data-step="{{ $n }}"
                id="step-tab-btn-{{ $n }}"
                title="{{ $step['label'] }} (Step {{ $n }})"
                aria-label="Step {{ $n }}: {{ $step['label'] }}">
            <div class="step-icon-badge" data-ring>
                @if($done)
                    <i class="bi bi-check2 text-base"></i>
                @else
                    <i class="bi {{ $step['icon'] }}"></i>
                @endif
            </div>
            <div class="step-info-col">
                <div class="flex items-center gap-1.5">
                    <span class="step-sub" data-sub>{{ $done ? 'Completed' : 'Step ' . $n }}</span>
                    @if($done)
                        <i class="bi bi-check-circle-fill text-[9px] text-emerald-500 dark:text-emerald-400"></i>
                    @endif
                </div>
                <span class="step-title" data-label>{{ $step['label'] }}</span>
            </div>
            {{-- Active indicator bar --}}
            <div class="step-active-indicator"></div>
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
        <div class="mt-4 bg-gradient-to-br from-indigo-50/80 to-white dark:from-indigo-950/30 dark:to-slate-800 border border-indigo-100 dark:border-indigo-800/60 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between gap-2 mb-2">
                <p class="text-xs font-extrabold text-indigo-700 dark:text-indigo-300 flex items-center gap-1.5">
                    <i class="bi bi-globe2 text-indigo-500"></i> Official School Domain
                </p>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                    <i class="bi bi-patch-check-fill"></i> Live Subdomain
                </span>
            </div>
            
            {{-- Primary: Dedicated Subdomain URL --}}
            <code class="block text-xs font-semibold text-indigo-950 dark:text-indigo-200 bg-white dark:bg-slate-900 border border-indigo-200 dark:border-indigo-800/80 rounded-xl px-3.5 py-2.5 break-all shadow-inner tracking-tight">{{ $school->subdomain_url }}</code>

            <div class="mt-3 flex items-center gap-2 flex-wrap">
                <a href="{{ $school->subdomain_url }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                    <i class="bi bi-box-arrow-up-right"></i> Open Subdomain
                </a>
                <button type="button" onclick="navigator.clipboard.writeText('{{ $school->subdomain_url }}'); alert('Domain copied!');"
                   class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-medium text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 hover:bg-slate-50 transition">
                    <i class="bi bi-copy"></i> Copy
                </button>
            </div>

            {{-- Secondary: Direct Path Link --}}
            <div class="mt-4 pt-3 border-t border-indigo-100/80 dark:border-slate-700/60">
                <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 block mb-1">Direct portal path:</span>
                <div class="flex items-center justify-between gap-2">
                    <code class="text-[11px] text-slate-600 dark:text-slate-300 truncate">{{ url('/school/'.$school->slug) }}</code>
                    <a href="{{ url('/school/'.$school->slug) }}" target="_blank" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline shrink-0 flex items-center gap-1">
                        View <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
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

    {{-- Left: Preset themes and brand customization --}}
    <div class="xl:col-span-2 flex flex-col gap-6">
        {{-- Preset Themes --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-1">
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm shadow-sm ring-1 ring-indigo-500/20">
                        <i class="bi bi-palette-fill"></i>
                    </span>
                    Preset Color Themes
                </h3>
                <span class="text-[11px] font-semibold text-slate-400">1-Click Apply</span>
            </div>
            <p class="text-xs text-slate-400 mb-5 leading-relaxed">Select a professionally designed color scheme that reflects your institution's heritage and academic excellence.</p>
            
            <input type="hidden" name="preset_theme" id="preset_theme_input" value="{{ old('preset_theme', $school->preset_theme ?? 'royal_blue') }}">
            
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                @foreach($presetThemes as $key => $theme)
                <button type="button"
                        onclick="selectPreset('{{ $key }}', '{{ $theme['primary'] }}', '{{ $theme['secondary'] }}')"
                        id="preset-{{ $key }}"
                        class="preset-card group relative rounded-2xl border-2 p-3.5 text-left transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 focus:outline-none
                               {{ ($school->preset_theme ?? 'royal_blue') === $key ? 'border-indigo-600 bg-indigo-50/20 dark:bg-indigo-950/30 shadow-md ring-2 ring-indigo-500/20' : 'border-slate-200 dark:border-slate-700 hover:border-indigo-300' }}">
                    {{-- Colour swatches --}}
                    <div class="flex items-center gap-1.5 mb-2.5">
                        <div class="w-8 h-8 rounded-xl shadow-sm border border-black/10 transition-transform group-hover:scale-105" style="background:{{ $theme['primary'] }};"></div>
                        <div class="w-6 h-6 rounded-lg shadow-sm border border-black/10 transition-transform group-hover:scale-105" style="background:{{ $theme['secondary'] }};"></div>
                    </div>
                    <div class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate">{{ $theme['label'] }}</div>
                    <div class="text-[10px] font-mono text-slate-400 mt-0.5 truncate">{{ $theme['primary'] }}</div>
                    
                    {{-- Checkmark badge --}}
                    <div class="absolute top-2.5 right-2.5 w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[10px] shadow-sm transition-all
                                {{ ($school->preset_theme ?? 'royal_blue') === $key ? 'opacity-100 scale-100' : 'opacity-0 scale-75' }}"
                         id="preset-check-{{ $key }}">
                        <i class="bi bi-check-lg font-bold"></i>
                    </div>
                </button>
                @endforeach
            </div>
        </div>

        {{-- Custom Colors --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-1">
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center text-sm shadow-sm">
                        <i class="bi bi-eyedropper"></i>
                    </span>
                    Custom Brand Colors
                </h3>
                <span class="text-[11px] font-semibold text-slate-400">Custom Palette</span>
            </div>
            <p class="text-xs text-slate-400 mb-5">Fine-tune your brand colors with precise HEX codes or interactive color pickers.</p>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                {{-- Primary Brand Color --}}
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-200">Primary Brand Color</label>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-800/60">Main Brand</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mb-3">Used for navigation bars, hero headers, primary buttons, and key focus states.</p>
                    <div class="flex items-center gap-3 p-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl shadow-xs">
                        <input type="color" name="primary_color" id="primary_color"
                               value="{{ old('primary_color', $school->primary_color ?? '#1a365d') }}"
                               oninput="syncColorText('primary_color','primary_color_text'); updatePreview();"
                               class="w-10 h-10 rounded-lg cursor-pointer border-0 bg-transparent p-0">
                        <div class="flex-1">
                            <span class="text-[9px] uppercase font-bold text-slate-400 block tracking-wider">HEX CODE</span>
                            <input type="text" id="primary_color_text"
                                   value="{{ old('primary_color', $school->primary_color ?? '#1a365d') }}"
                                   oninput="syncColorPicker('primary_color_text','primary_color'); updatePreview();"
                                   maxlength="7" placeholder="#1a365d"
                                   class="w-full font-mono text-xs font-bold bg-transparent border-0 focus:outline-none text-slate-800 dark:text-slate-100">
                        </div>
                    </div>
                    {{-- Quick Palette Chips --}}
                    <div class="mt-3 flex items-center gap-1.5 flex-wrap">
                        <span class="text-[9px] font-bold text-slate-400 uppercase mr-1">Prestige:</span>
                        @foreach([
                            ['#1a365d','Oxford Navy'],
                            ['#881337','Crimson'],
                            ['#064e3b','Dartmouth'],
                            ['#ea580c','Warm Amber'],
                            ['#1d4ed8','Sapphire'],
                            ['#4c1d95','Imperial'],
                            ['#0f172a','Slate Dark'],
                            ['#0f766e','Deep Teal']
                        ] as [$chipHex, $chipName])
                        <button type="button" onclick="setColor('primary_color', '{{ $chipHex }}'); updatePreview();" 
                                class="w-5 h-5 rounded-md shadow-xs border border-black/10 hover:scale-110 transition-transform" 
                                style="background: {{ $chipHex }};" 
                                title="{{ $chipName }} ({{ $chipHex }})"></button>
                        @endforeach
                    </div>
                </div>

                {{-- Secondary Accent Color --}}
                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-200">Secondary Accent Color</label>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-amber-50 dark:bg-amber-950 text-amber-600 dark:text-amber-400 border border-amber-200/60 dark:border-amber-800/60">CTA Accent</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mb-3">Used for "Apply Now" buttons, highlights, ribbons, and prominent badges.</p>
                    <div class="flex items-center gap-3 p-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl shadow-xs">
                        <input type="color" name="secondary_color" id="secondary_color"
                               value="{{ old('secondary_color', $school->secondary_color ?? '#c59b27') }}"
                               oninput="syncColorText('secondary_color','secondary_color_text'); updatePreview();"
                               class="w-10 h-10 rounded-lg cursor-pointer border-0 bg-transparent p-0">
                        <div class="flex-1">
                            <span class="text-[9px] uppercase font-bold text-slate-400 block tracking-wider">HEX CODE</span>
                            <input type="text" id="secondary_color_text"
                                   value="{{ old('secondary_color', $school->secondary_color ?? '#c59b27') }}"
                                   oninput="syncColorPicker('secondary_color_text','secondary_color'); updatePreview();"
                                   maxlength="7" placeholder="#c59b27"
                                   class="w-full font-mono text-xs font-bold bg-transparent border-0 focus:outline-none text-slate-800 dark:text-slate-100">
                        </div>
                    </div>
                    {{-- Quick Palette Chips --}}
                    <div class="mt-3 flex items-center gap-1.5 flex-wrap">
                        <span class="text-[9px] font-bold text-slate-400 uppercase mr-1">Accents:</span>
                        @foreach([
                            ['#c59b27','Classic Gold'],
                            ['#d97706','Warm Amber'],
                            ['#10b981','Emerald Spark'],
                            ['#3b82f6','Azure Light'],
                            ['#0f172a','Executive Dark'],
                            ['#f59e0b','Solar Gold'],
                            ['#06b6d4','Vibrant Cyan'],
                            ['#e11d48','Ruby Rose']
                        ] as [$chipHex, $chipName])
                        <button type="button" onclick="setColor('secondary_color', '{{ $chipHex }}'); updatePreview();" 
                                class="w-5 h-5 rounded-md shadow-xs border border-black/10 hover:scale-110 transition-transform" 
                                style="background: {{ $chipHex }};" 
                                title="{{ $chipName }} ({{ $chipHex }})"></button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Social Links --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-1">
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-sky-500/10 text-sky-500 flex items-center justify-center text-sm shadow-sm">
                        <i class="bi bi-share-fill"></i>
                    </span>
                    Official Social Channels
                </h3>
                <span class="text-[11px] font-semibold text-slate-400">Live Topbar Sync</span>
            </div>
            <p class="text-xs text-slate-400 mb-5">Connect your official social media pages. Icons in the header will automatically link to these profiles.</p>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach([
                    ['facebook_url','bi-facebook','#1877f2','Facebook Page URL','https://facebook.com/your-school'],
                    ['twitter_url','bi-twitter-x','#0f172a','Twitter / X Profile URL','https://x.com/your-school'],
                    ['instagram_url','bi-instagram','#e4405f','Instagram Profile URL','https://instagram.com/your-school'],
                    ['youtube_url','bi-youtube','#ff0000','YouTube Channel URL','https://youtube.com/@your-school']
                ] as [$field,$icon,$color,$label,$placeholder])
                <div>
                    <label class="field-label flex items-center gap-1.5">
                        <i class="bi {{ $icon }}" style="color:{{ $color }};"></i>
                        {{ $label }}
                    </label>
                    <div class="relative">
                        <input type="url" name="{{ $field }}" value="{{ old($field, $school->$field) }}"
                               placeholder="{{ $placeholder }}" class="field-input text-xs">
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Right: Realtime Interactive Side Preview --}}
    <div class="xl:col-span-1">
        <div class="sticky top-20 flex flex-col gap-4">
            
            {{-- Preview Simulator Container --}}
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-5">
                {{-- Header & Device Mode Switcher --}}
                <div class="flex items-center justify-between mb-3 pb-3 border-b border-slate-100 dark:border-slate-700/60">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                        </span>
                        <h3 class="text-xs font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-wider">
                            Live Portal Preview
                        </h3>
                    </div>
                    
                    {{-- Device Mode Toggles --}}
                    <div class="inline-flex p-0.5 rounded-lg bg-slate-100 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600">
                        <button type="button" onclick="setPreviewDevice('desktop')" id="btn-prev-desktop"
                                class="px-2 py-1 rounded-md text-[10px] font-bold text-indigo-600 dark:text-indigo-400 bg-white dark:bg-slate-800 shadow-xs transition" title="Desktop View">
                            <i class="bi bi-display"></i> Desktop
                        </button>
                        <button type="button" onclick="setPreviewDevice('mobile')" id="btn-prev-mobile"
                                class="px-2 py-1 rounded-md text-[10px] font-bold text-slate-500 dark:text-slate-400 hover:text-slate-800 transition" title="Mobile Phone View">
                            <i class="bi bi-phone"></i> Mobile
                        </button>
                    </div>
                </div>

                {{-- Interactive Device Frame --}}
                <div id="previewFrameWrap" class="transition-all duration-300 mx-auto w-full">
                    
                    {{-- Browser Window Top Bar Chrome --}}
                    <div class="bg-slate-900 text-slate-300 rounded-t-2xl px-3 py-2 flex items-center justify-between border-b border-white/10 text-[9px]">
                        <div class="flex items-center gap-1.5 shrink-0">
                            <span class="w-2 h-2 rounded-full bg-rose-500 inline-block"></span>
                            <span class="w-2 h-2 rounded-full bg-amber-500 inline-block"></span>
                            <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                        </div>
                        <div class="flex items-center gap-1 px-2.5 py-0.5 bg-slate-950/80 rounded-full border border-white/10 text-[8px] font-mono text-slate-300 truncate max-w-[170px] shadow-inner">
                            <i class="bi bi-lock-fill text-emerald-400 text-[7px]"></i>
                            <span class="truncate" id="prev-browser-url">{{ $school->subdomain }}.{{ config('app.domain', 'nshlms.com') }}</span>
                        </div>
                        <div class="text-[8px] text-slate-400 flex items-center gap-1">
                            <i class="bi bi-arrow-clockwise cursor-pointer hover:text-white" onclick="updatePreview();" title="Refresh Preview"></i>
                        </div>
                    </div>

                {{-- Mini Landing Page Canvas (Exact Replica of Public Website) --}}
                <div id="livePreviewContainer" class="rounded-b-2xl overflow-hidden border-x border-b border-slate-200 dark:border-slate-700 shadow-md text-[10px] bg-white text-slate-900 transition-all duration-300 relative select-none">
                    
                    {{-- 1. Exact Navbar Replica --}}
                    <div id="prev-navbar" class="sticky top-0 z-30 flex items-center justify-between px-3 py-2 bg-white border-b border-slate-200/90 shadow-xs">
                        <div class="flex items-center gap-2 min-w-0">
                            {{-- Logo --}}
                            <div class="w-8 h-8 rounded-full overflow-hidden flex items-center justify-center border border-slate-200/80 shrink-0 bg-white p-0.5 shadow-xs" id="prev-logo-container">
                                @if($school->logo)
                                    <img id="prev-logo-img" src="{{ $school->logo_url }}" alt="Logo" class="w-full h-full object-contain rounded-full">
                                    <i class="bi bi-building text-slate-400 text-xs hidden" id="prev-logo-fallback"></i>
                                @else
                                    <img id="prev-logo-img" src="{{ asset('imgs/logo_icon.webp') }}" alt="Logo" class="w-full h-full object-contain rounded-full hidden">
                                    <i class="bi bi-building text-indigo-600 text-xs" id="prev-logo-fallback"></i>
                                @endif
                            </div>
                            {{-- School Title & Subtitle --}}
                            <div class="min-w-0 leading-tight">
                                <span id="prev-school-name" class="font-extrabold text-[10px] text-slate-900 truncate block">
                                    {{ $school->name }}
                                </span>
                                <span class="text-[7.5px] text-slate-500 truncate block font-medium">
                                    <span id="prev-nav-city">{{ $school->city ?? 'Siraha' }}</span> · <span id="prev-nav-slogan">{{ Str::limit($school->description ?? 'Excellence in Education & Character', 32) }}</span>
                                </span>
                            </div>
                        </div>

                        {{-- Right Buttons --}}
                        <div class="flex items-center gap-1.5 shrink-0">
                            <div id="prev-btn-apply" class="px-2.5 py-1 rounded-md text-white font-extrabold text-[8px] shrink-0 transition-colors shadow-xs flex items-center gap-1"
                                 style="background: {{ $school->primary_color_resolved }};">
                                Apply
                            </div>
                            <div class="w-6 h-6 rounded-md border border-slate-200 flex items-center justify-center text-slate-700 bg-white text-[10px] shadow-xs">
                                <i class="bi bi-list"></i>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Pure Hero Banner Slider Image or Autoplay MP4 Video (Edge to Edge) --}}
                    @php
                        $firstHeroMedia = asset('imgs/hero/hero_1.webp');
                        $firstHeroIsVid = false;
                        if (!empty($school->slider_banners) && is_array($school->slider_banners) && count($school->slider_banners) > 0) {
                            foreach ($school->slider_banners as $sb) {
                                if (!empty($sb['image'])) {
                                    $firstHeroMedia = (!empty($sb['is_asset']) || str_starts_with($sb['image'], 'imgs/')) 
                                        ? asset($sb['image']) 
                                        : asset('storage/' . $sb['image']);
                                    $firstHeroIsVid = (!empty($sb['type']) && $sb['type'] === 'video') || preg_match('/\.(mp4|webm|ogg|mov)$/i', $sb['image']);
                                    break;
                                }
                            }
                        }
                    @endphp
                    <div class="relative w-full h-44 sm:h-52 bg-slate-900 overflow-hidden" id="prev-hero-wrap">
                        @if($firstHeroIsVid)
                            <video id="prev-hero-banner-media" src="{{ $firstHeroMedia }}" class="w-full h-full object-cover object-center select-none" autoplay muted loop playsinline></video>
                        @else
                            <img id="prev-hero-banner-media" src="{{ $firstHeroMedia }}" alt="Hero Banner" class="w-full h-full object-cover object-center select-none">
                        @endif
                        
                        {{-- Scroll Down Indicator --}}
                        <div class="absolute bottom-2 left-1/2 -translate-x-1/2 z-10 text-white/90 flex flex-col items-center gap-0.5 text-[7px] font-bold tracking-widest uppercase drop-shadow-md pointer-events-none">
                            <span>SCROLL</span>
                            <i class="bi bi-chevron-down text-[8px] animate-bounce"></i>
                        </div>
                    </div>

                    {{-- 3. Floating Left Social Icons Bar --}}
                    <div class="absolute left-2 top-52 z-20 flex flex-col gap-1 drop-shadow-sm" id="prev-floating-socials">
                        <div class="w-5 h-5 rounded-full bg-white border border-slate-200 flex items-center justify-center shadow-xs text-blue-600 text-[8px]" id="prev-soc-fb">
                            <i class="bi bi-facebook"></i>
                        </div>
                        <div class="w-5 h-5 rounded-full bg-white border border-slate-200 flex items-center justify-center shadow-xs text-red-600 text-[8px]" id="prev-soc-yt">
                            <i class="bi bi-youtube"></i>
                        </div>
                        <div class="w-5 h-5 rounded-full bg-white border border-slate-200 flex items-center justify-center shadow-xs text-pink-600 text-[8px]" id="prev-soc-ig">
                            <i class="bi bi-instagram"></i>
                        </div>
                        <div class="w-5 h-5 rounded-full bg-white border border-slate-200 flex items-center justify-center shadow-xs text-emerald-600 text-[8px]" id="prev-soc-wa">
                            <i class="bi bi-whatsapp"></i>
                        </div>
                    </div>

                    {{-- 4. Institutional Welcome & Content Section --}}
                    <div class="p-3.5 bg-white relative">
                        
                        {{-- Badges Row --}}
                        <div class="flex items-center gap-1.5 mb-2 flex-wrap pl-6">
                            <span id="prev-badge-name" class="text-white text-[7.5px] font-bold px-2 py-0.5 rounded-md shadow-xs"
                                  style="background: {{ $school->primary_color_resolved }};">
                                {{ $school->name }}
                            </span>
                            <span class="text-slate-500 font-semibold text-[7.5px]">
                                ESTD {{ $school->established_year ?? '2004' }} · <span id="prev-badge-city">{{ $school->city ?? 'siraha' }}</span>
                            </span>
                            <span class="bg-slate-100 text-slate-800 text-[7px] font-bold px-1.5 py-0.5 rounded border border-slate-200 flex items-center gap-1">
                                <i class="bi bi-patch-check-fill text-amber-500" id="prev-neb-icon"></i> Govt. / NEB Approved
                            </span>
                        </div>

                        {{-- Main Headline --}}
                        <div class="pl-6 mb-2">
                            <h4 class="text-[12px] sm:text-[13px] font-extrabold text-slate-900 tracking-tight leading-snug">
                                We help our students 
                                <span id="prev-headline-accent" class="underline decoration-4 underline-offset-4" style="text-decoration-color: {{ $school->primary_color_resolved }};">
                                    realise their potential
                                </span>
                            </h4>
                        </div>

                        {{-- Subtitle / Description --}}
                        <div class="pl-6 mb-3">
                            <p id="prev-hero-slogan" class="text-slate-600 text-[8px] leading-relaxed line-clamp-2">
                                {{ $school->description ?: 'Premier institution dedicated to academic excellence, disciplined leadership, holistic sports, and modern STEM innovation.' }}
                            </p>
                        </div>

                        {{-- Action Buttons Row --}}
                        <div class="space-y-1.5 pl-6">
                            <div id="prev-main-cta" class="w-full text-center py-2 rounded-xl text-white font-extrabold text-[8.5px] shadow-sm transition-transform flex items-center justify-center gap-1.5 cursor-pointer"
                                 style="background: {{ $school->primary_color_resolved }};">
                                <i class="bi bi-pencil-square"></i>
                                <span>Apply Online for 2026/27</span>
                            </div>

                            <div class="grid grid-cols-2 gap-1.5">
                                <div class="text-center py-1.5 rounded-lg text-slate-800 font-bold text-[7.5px] bg-white border border-slate-300 shadow-xs flex items-center justify-center gap-1">
                                    <i class="bi bi-download" id="prev-prospectus-icon" style="color: {{ $school->primary_color_resolved }};"></i> Prospectus
                                </div>
                                <div class="text-center py-1.5 rounded-lg text-slate-800 font-bold text-[7.5px] bg-white border border-slate-300 shadow-xs flex items-center justify-center gap-1">
                                    <i class="bi bi-mortarboard" id="prev-results-icon" style="color: {{ $school->primary_color_resolved }};"></i> Exam Results
                                </div>
                            </div>
                        </div>



                    </div>
                </div>
                </div>

                {{-- Live Palette Token Specs --}}
                <div class="mt-4 p-3 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-200 dark:border-slate-700/80">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Active Color Tokens</span>
                        <span class="text-[9px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-1.5 py-0.5 rounded border border-emerald-200 dark:border-emerald-800">Synced</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="flex items-center gap-2 p-1.5 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">
                            <div id="swatch-primary" class="w-6 h-6 rounded-md shadow-xs border border-black/10 shrink-0" style="background: {{ $school->primary_color_resolved }};"></div>
                            <div class="min-w-0">
                                <div class="text-[8px] font-bold text-slate-400 uppercase">Primary</div>
                                <div class="text-[10px] font-mono font-bold text-slate-800 dark:text-slate-100 truncate" id="swatch-primary-label">{{ $school->primary_color_resolved }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 p-1.5 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">
                            <div id="swatch-secondary" class="w-6 h-6 rounded-md shadow-xs border border-black/10 shrink-0" style="background: {{ $school->secondary_color_resolved }};"></div>
                            <div class="min-w-0">
                                <div class="text-[8px] font-bold text-slate-400 uppercase">Secondary</div>
                                <div class="text-[10px] font-mono font-bold text-slate-800 dark:text-slate-100 truncate" id="swatch-secondary-label">{{ $school->secondary_color_resolved }}</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>{{-- /grid --}}
</div>{{-- /step-2 --}}

{{-- ════════════════════════════════════════════════
     STEP 3 — HERO SLIDER BANNERS & MP4 VIDEOS
════════════════════════════════════════════════ --}}
<div class="step-panel hidden" id="step-3">
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Left Column: Upload New Slider Banner Card --}}
    <div class="xl:col-span-1">
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-2xl shadow-sm p-6 sticky top-20">
            <div class="flex items-center justify-between gap-2 mb-2">
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center text-sm shadow-sm">
                        <i class="bi bi-cloud-arrow-up-fill"></i>
                    </span>
                    Add New Slide / Video
                </h3>
                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-800/60">
                    Image / MP4
                </span>
            </div>
            <p class="text-xs text-slate-400 dark:text-slate-400 mb-5 leading-relaxed">
                Upload panoramic banner images or <strong>MP4 videos</strong> (auto-plays silently in a loop) that cover the whole screen on your homepage.
            </p>

            <div class="space-y-4">
                {{-- Banner image / video dropzone --}}
                <div>
                    <label class="field-label">Slide Media <span class="text-rose-500">*</span> <span class="text-slate-400 font-normal">(16:9 Image or MP4 Video)</span></label>
                    <label class="flex flex-col items-center justify-center gap-2 w-full border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-2xl p-5 cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/50 dark:hover:bg-indigo-950/20 transition group">
                        <div id="newSliderPlaceholder" class="flex flex-col items-center text-center">
                            <i class="bi bi-camera-video text-3xl text-slate-400 group-hover:text-indigo-500 transition-colors"></i>
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 mt-2">Choose or drop high-res banner or MP4</span>
                            <span class="text-[10px] text-slate-400 mt-0.5">JPEG, PNG, WebP or MP4 video (max 50 MB)</span>
                        </div>
                        <img id="newSliderPreviewImg" class="hidden w-full h-36 object-cover rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm" alt="Preview">
                        <video id="newSliderPreviewVideo" class="hidden w-full h-36 object-cover rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm" autoplay muted loop playsinline></video>
                        <input type="file" name="new_slider_image" id="newSliderInput" accept="image/*,video/mp4,video/webm,video/ogg,video/quicktime" class="hidden" onchange="previewNewSlider(this)">
                    </label>
                </div>

                {{-- Tag / Badge --}}
                <div>
                    <label class="field-label">Tag / Eyebrow Badge <span class="text-slate-400 font-normal">(optional)</span></label>
                    <input type="text" name="new_slider_tag" id="new_slider_tag" class="field-input" placeholder="e.g. ✨ Admissions Open 2026-2027">
                </div>

                {{-- Headline --}}
                <div>
                    <label class="field-label">Headline / Title <span class="text-slate-400 font-normal">(prominent text)</span></label>
                    <input type="text" name="new_slider_title" id="new_slider_title" class="field-input font-bold" placeholder="e.g. Inspiring Minds, Shaping Futures">
                </div>

                {{-- Subtitle / Description --}}
                <div>
                    <label class="field-label">Subtitle / Description</label>
                    <textarea name="new_slider_subtitle" id="new_slider_subtitle" rows="2" class="field-input resize-y" placeholder="Brief supporting message about excellence, faculty, or admission deadlines..."></textarea>
                </div>

                {{-- Primary Button --}}
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="field-label">Primary Button Text</label>
                        <input type="text" name="new_slider_btn_text" class="field-input" placeholder="Apply Online">
                    </div>
                    <div>
                        <label class="field-label">Primary Button URL</label>
                        <input type="text" name="new_slider_btn_url" class="field-input" placeholder="/admissions/apply">
                    </div>
                </div>

                {{-- Secondary Button --}}
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="field-label">Secondary Button Text</label>
                        <input type="text" name="new_slider_secondary_btn_text" class="field-input" placeholder="Explore Campus">
                    </div>
                    <div>
                        <label class="field-label">Secondary Button URL</label>
                        <input type="text" name="new_slider_secondary_btn_url" class="field-input" placeholder="#about">
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 dark:border-slate-700/60">
                    <p class="text-[11px] text-slate-400 leading-normal">
                        💡 <em>Tip: MP4 videos auto-play silently in an infinite loop on your homepage banner. Click <strong>Save Changes</strong> to apply.</em>
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Column: Existing Slider Banners List --}}
    <div class="xl:col-span-2">
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-2xl shadow-sm p-6">
            <div class="flex items-center justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="bi bi-collection-play text-indigo-500"></i> Active Homepage Sliders &amp; Videos
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        These slides will appear on your public website hero slider in sequential order.
                    </p>
                </div>
                @php
                    $existingSliders = $school->slider_banners && is_array($school->slider_banners) ? $school->slider_banners : [];
                @endphp
                <span class="text-xs font-bold px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                    {{ count($existingSliders) }} Custom Slide{{ count($existingSliders) !== 1 ? 's' : '' }}
                </span>
            </div>

            @if(count($existingSliders) > 0)
            <div class="space-y-6" id="existingSlidersList">
                @foreach($existingSliders as $sIdx => $sSlide)
                @php
                    $isVid = (!empty($sSlide['type']) && $sSlide['type'] === 'video') || preg_match('/\.(mp4|webm|ogg|mov)$/i', $sSlide['image'] ?? '');
                    $slideSrc = (!empty($sSlide['is_asset']) || str_starts_with($sSlide['image'] ?? '', 'imgs/')) ? asset($sSlide['image']) : asset('storage/' . ($sSlide['image'] ?? ''));
                @endphp
                <div class="border border-slate-200 dark:border-slate-700 rounded-2xl p-5 bg-slate-50/50 dark:bg-slate-900/50 relative group transition-all"
                     id="slider-item-{{ $sIdx }}">
                    
                    <div class="flex items-center justify-between gap-3 mb-4 pb-3 border-b border-slate-200/80 dark:border-slate-700/60">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-200">
                            <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs font-bold">
                                {{ $sIdx + 1 }}
                            </span>
                            Slide #{{ $sIdx + 1 }}
                            @if($isVid)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 border border-amber-300 flex items-center gap-1">
                                    <i class="bi bi-play-circle-fill"></i> MP4 Video
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200/80 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-300 flex items-center gap-1">
                                    <i class="bi bi-image"></i> Image
                                </span>
                            @endif
                        </span>
                        
                        <div class="flex items-center gap-2">
                            <label class="cursor-pointer text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 flex items-center gap-1">
                                <i class="bi bi-arrow-repeat"></i> Replace Media
                                <input type="file" name="replace_slider_image[{{ $sIdx }}]" accept="image/*,video/mp4,video/webm,video/ogg,video/quicktime" class="hidden"
                                       onchange="previewReplaceSlider(this, {{ $sIdx }})">
                            </label>
                            <span class="text-slate-300 dark:text-slate-600">|</span>
                            <label class="cursor-pointer text-xs font-semibold text-rose-600 dark:text-rose-400 hover:text-rose-700 flex items-center gap-1">
                                <input type="checkbox" name="delete_slider_banners[]" value="{{ $sIdx }}" class="rounded accent-rose-500"
                                       onchange="toggleSliderDelete({{ $sIdx }}, this.checked)">
                                Remove Slide
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        {{-- Slide media preview --}}
                        <div class="md:col-span-1">
                            <div class="relative rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 aspect-video bg-black" id="slider-media-box-{{ $sIdx }}">
                                @if($isVid)
                                    <video src="{{ $slideSrc }}"
                                           id="slider-preview-media-{{ $sIdx }}"
                                           class="w-full h-full object-cover select-none"
                                           autoplay muted loop playsinline></video>
                                    <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-black/70 backdrop-blur-xs text-white text-[9px] font-bold flex items-center gap-1 border border-white/20">
                                        <i class="bi bi-play-circle-fill text-amber-400"></i> Autoplay MP4
                                    </span>
                                @else
                                    <img src="{{ $slideSrc }}"
                                         id="slider-preview-media-{{ $sIdx }}"
                                         alt="Slide {{ $sIdx + 1 }}"
                                         class="w-full h-full object-cover">
                                @endif
                                <div class="absolute inset-0 bg-rose-900/80 hidden flex-col items-center justify-center gap-1"
                                     id="slider-del-mask-{{ $sIdx }}">
                                    <i class="bi bi-trash-fill text-white text-2xl"></i>
                                    <span class="text-white text-xs font-bold">Will be deleted</span>
                                </div>
                            </div>
                            <p class="text-[10px] text-slate-400 text-center mt-1.5">Widescreen Fullscreen Aspect</p>
                        </div>

                        {{-- Editable slide metadata --}}
                        <div class="md:col-span-2 space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="field-label">Badge Tag</label>
                                    <input type="text" name="slider_banners[{{ $sIdx }}][tag]"
                                           value="{{ $sSlide['tag'] ?? '' }}"
                                           placeholder="e.g. Admissions Open 2026"
                                           class="field-input">
                                </div>
                                <div>
                                    <label class="field-label">Headline / Title</label>
                                    <input type="text" name="slider_banners[{{ $sIdx }}][title]"
                                           value="{{ $sSlide['title'] ?? '' }}"
                                           placeholder="e.g. Excellence in Education"
                                           class="field-input font-bold">
                                </div>
                            </div>

                            <div>
                                <label class="field-label">Subtitle / Description</label>
                                <textarea name="slider_banners[{{ $sIdx }}][subtitle]" rows="2"
                                          placeholder="Description..."
                                          class="field-input resize-y">{{ $sSlide['subtitle'] ?? '' }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="field-label">Primary Button Text</label>
                                    <input type="text" name="slider_banners[{{ $sIdx }}][btn_text]"
                                           value="{{ $sSlide['btn_text'] ?? '' }}"
                                           placeholder="Apply Online"
                                           class="field-input">
                                </div>
                                <div>
                                    <label class="field-label">Primary Button URL</label>
                                    <input type="text" name="slider_banners[{{ $sIdx }}][btn_url]"
                                           value="{{ $sSlide['btn_url'] ?? '' }}"
                                           placeholder="/admissions/apply"
                                           class="field-input">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="text-center py-12 px-4 border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-2xl">
                <i class="bi bi-camera-video text-4xl text-slate-300 dark:text-slate-600 mb-3 block"></i>
                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-1">No Custom Hero Slides or Videos Yet</h4>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">
                    Upload high-resolution panoramic images or MP4 videos on the left to customize your school website banner.
                </p>
            </div>
            @endif

            {{-- Website preview button --}}
            <div class="mt-6 pt-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between flex-wrap gap-3">
                <span class="text-xs text-slate-400">Preview changes on your public website:</span>
                <a href="{{ route('school.profile.preview') }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/60 rounded-xl text-xs font-bold hover:bg-indigo-100 transition shadow-sm">
                    <i class="bi bi-eye"></i> Preview Fullscreen Slider
                </a>
            </div>
        </div>
    </div>

</div>
</div>{{-- /step-3 --}}

{{-- ════════════════════════════════════════════════
     STEP 4 — CONTENT
════════════════════════════════════════════════ --}}
<div class="step-panel hidden" id="step-4">
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

            {{-- Principal Section --}}
            <div class="border-t border-slate-200 dark:border-slate-700 pt-4 mt-4">
                <h4 class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                    <i class="bi bi-person-badge-fill"></i> Principal / Head of Institution
                </h4>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-3">
                    <div>
                        <label class="field-label">Principal Full Name</label>
                        <input type="text" name="principal_name" value="{{ old('principal_name', $school->settings['principal_name'] ?? $school->contact_person) }}" class="field-input" placeholder="e.g. Dr. Ram Bahadur Shrestha">
                    </div>
                    <div>
                        <label class="field-label">Principal Title / Designation</label>
                        <input type="text" name="principal_title" value="{{ old('principal_title', $school->settings['principal_title'] ?? 'Principal & Head of Institution') }}" class="field-input" placeholder="e.g. Principal & Academic Director">
                    </div>
                </div>

                {{-- Principal Photo Upload with Live Preview --}}
                <div class="bg-slate-50 dark:bg-slate-900/40 p-4 rounded-xl border border-slate-200 dark:border-slate-700 mb-3">
                    <label class="field-label mb-2 block">Principal Portrait Photo</label>
                    <div class="flex items-center gap-4">
                        <div class="w-20 h-20 rounded-2xl overflow-hidden border-2 border-amber-400/80 bg-white dark:bg-slate-800 shrink-0 shadow-sm">
                            <img id="principalPhotoPreview" src="{{ $school->principal_photo_url }}" alt="Principal Photo Preview" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1 min-w-0">
                            <input type="file" name="principal_image" id="principalImageInput" accept="image/*" class="text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" onchange="previewPrincipalPhoto(this)">
                            <p class="text-[11px] text-slate-400 mt-1">Recommended square or portrait photo (JPEG, PNG, WebP up to 3MB). Displayed on landing page and about page.</p>
                            @if(!empty($school->settings['principal_image']))
                            <label class="inline-flex items-center gap-1.5 mt-2 text-xs text-rose-600 cursor-pointer">
                                <input type="checkbox" name="remove_principal_image" value="1" class="rounded text-rose-600">
                                <span>Remove custom photo (use fallback)</span>
                            </label>
                            @endif
                        </div>
                    </div>
                </div>

                <div>
                    <label class="field-label">Principal's Message</label>
                    <textarea name="principal_message" rows="4" class="field-input resize-y" placeholder="Welcome message to prospective parents and students…">{{ old('principal_message', $school->principal_message) }}</textarea>
                </div>
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
                    <textarea name="admission_info" rows="4" class="field-input resize-y" placeholder="Admission process, requirements, important dates…">{{ old('admission_info', $school->admission_info) }}</textarea>
                </div>
                <div>
                    <label class="field-label">Results Information</label>
                    <textarea name="result_info" rows="3" class="field-input resize-y" placeholder="How to check results, recent achievements…">{{ old('result_info', $school->result_info) }}</textarea>
                </div>
            </div>
        </div>

        {{-- ── School Operating Hours & Live Status Configuration ── --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-6">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-2 flex items-center justify-between">
                <span class="flex items-center gap-2">
                    <i class="bi bi-clock-history text-amber-500"></i> School Operational Timings
                </span>
                <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full border {{ $school->open_status_badge['class'] }}">
                    {{ $school->open_status_badge['badge'] }}
                </span>
            </h3>
            <p class="text-xs text-slate-400 mb-4">Set exact school opening, closing, and visitor hours shown on your public website and contact page.</p>

            <div class="space-y-3">
                <div>
                    <label class="field-label">Regular School Hours (Sun – Thu)</label>
                    <input type="text" name="school_hours_weekdays" class="field-input"
                           value="{{ old('school_hours_weekdays', $school->settings['school_hours_weekdays'] ?? '09:00 AM – 04:00 PM (Sun – Thu)') }}"
                           placeholder="e.g. 09:00 AM – 04:00 PM (Sun – Thu)">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="field-label">Friday Timings (Half Day)</label>
                        <input type="text" name="school_hours_friday" class="field-input"
                               value="{{ old('school_hours_friday', $school->settings['school_hours_friday'] ?? '09:00 AM – 01:30 PM (Friday)') }}"
                               placeholder="e.g. 09:00 AM – 01:30 PM">
                    </div>
                    <div>
                        <label class="field-label">Weekend / Saturday Status</label>
                        <input type="text" name="school_hours_weekend" class="field-input"
                               value="{{ old('school_hours_weekend', $school->settings['school_hours_weekend'] ?? 'Closed (Saturday & Public Holidays)') }}"
                               placeholder="e.g. Closed (Saturday & Public Holidays)">
                    </div>
                </div>
                <div>
                    <label class="field-label">Administration &amp; Admission Office Hours</label>
                    <input type="text" name="office_hours" class="field-input"
                           value="{{ old('office_hours', $school->settings['office_hours'] ?? '08:30 AM – 05:00 PM') }}"
                           placeholder="e.g. 08:30 AM – 05:00 PM">
                </div>
            </div>
        </div>

        {{-- ── Upcoming Exam Routine & Academic Schedules ── --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-6">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-2 flex items-center gap-2">
                <i class="bi bi-calendar2-range-fill text-indigo-500"></i> Upcoming Exam Routine &amp; Schedules
            </h3>
            <p class="text-xs text-slate-400 mb-4">Publish upcoming term examination dates, instructions, shift timings, and downloadable routine file for students and parents.</p>

            <div class="space-y-3">
                <div>
                    <label class="field-label">Exam Name / Examination Term</label>
                    <input type="text" name="exam_title" class="field-input"
                           value="{{ old('exam_title', $school->settings['exam_title'] ?? '') }}"
                           placeholder="e.g. Final Term Examination 2082 / SEE Pre-Board Examination">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="field-label">Start Date</label>
                        <input type="text" name="exam_start_date" class="field-input"
                               value="{{ old('exam_start_date', $school->settings['exam_start_date'] ?? '') }}"
                               placeholder="e.g. Chaitra 15, 2082 (March 28, 2026)">
                    </div>
                    <div>
                        <label class="field-label">End Date / Duration</label>
                        <input type="text" name="exam_end_date" class="field-input"
                               value="{{ old('exam_end_date', $school->settings['exam_end_date'] ?? '') }}"
                               placeholder="e.g. Chaitra 25, 2082 (April 07, 2026)">
                    </div>
                </div>

                <div>
                    <label class="field-label">Shift &amp; Reporting Timings</label>
                    <input type="text" name="exam_shift_details" class="field-input"
                           value="{{ old('exam_shift_details', $school->settings['exam_shift_details'] ?? '') }}"
                           placeholder="e.g. Morning Shift (Grades 1–5): 09:30 AM – 12:30 PM | Day Shift (Grades 6–12): 01:00 PM – 04:00 PM">
                </div>

                <div>
                    <label class="field-label">Exam Rules &amp; Student Instructions</label>
                    <textarea name="exam_instructions" rows="3" class="field-input resize-y"
                              placeholder="e.g. 1. Admit card is compulsory. 2. Mobile phones prohibited in exam hall. 3. Reporting 15 mins prior to bell.">{{ old('exam_instructions', $school->settings['exam_instructions'] ?? '') }}</textarea>
                </div>

                {{-- Exam Routine File Upload (PDF / Image) --}}
                <div class="pt-2 border-t border-slate-100 dark:border-slate-700/60">
                    <label class="field-label mb-1.5 block">Official Routine Document / Sheet (PDF or Image)</label>
                    @if(!empty($school->settings['exam_routine_file']))
                    <div class="flex items-center justify-between p-3 bg-indigo-50 dark:bg-indigo-950/40 rounded-xl border border-indigo-200 dark:border-indigo-800/60 mb-2.5">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <i class="bi bi-file-earmark-pdf-fill text-rose-500 text-lg"></i>
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">Routine Document Active</div>
                                <a href="{{ asset('storage/'.$school->settings['exam_routine_file']) }}" target="_blank" class="text-[11px] text-indigo-600 hover:underline">
                                    <i class="bi bi-box-arrow-up-right"></i> View/Download Current File
                                </a>
                            </div>
                        </div>
                        <label class="inline-flex items-center gap-1.5 text-xs text-rose-600 cursor-pointer">
                            <input type="checkbox" name="remove_exam_routine_file" value="1" class="rounded accent-rose-500">
                            <span>Remove File</span>
                        </label>
                    </div>
                    @endif

                    <input type="file" name="exam_routine_file" accept=".pdf,image/*"
                           class="text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="text-[11px] text-slate-400 mt-1">Upload printable routine sheet (PDF, JPEG, PNG, WebP up to 5MB).</p>
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
</div>{{-- /step-4 --}}

{{-- ════════════════════════════════════════════════
     STEP 5 — GALLERY & NOTICES
════════════════════════════════════════════════ --}}
<div class="step-panel hidden" id="step-5">
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
</div>{{-- /step-5 --}}

{{-- ════════════════════════════════════════════════
     STEP 6 — PAYMENT QR CODES
════════════════════════════════════════════════ --}}
<div class="step-panel hidden" id="step-6">
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
</div>{{-- /step-6 --}}

{{-- ════════════════════════════════════════════════
     STEP 7 — POPUP NOTICE / FLYER
════════════════════════════════════════════════ --}}
<div class="step-panel hidden" id="step-7">
<div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-6 mb-6">
    <div class="flex items-start justify-between flex-wrap gap-4 mb-6">
        <div>
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="bi bi-megaphone-fill text-indigo-500"></i> Principal Announcement & Urgent Notice Popup
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Display an eye-catching urgent admission flyer, festival greeting, scholarship announcement, or urgent school notice modal directly when visitors open your public school website.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="popup_active" value="1" class="sr-only peer" id="popupActiveToggle" {{ old('popup_active', $school->popup_active) ? 'checked' : '' }} onchange="togglePopupLivePreview()">
                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                <span class="ml-2 text-sm font-semibold text-slate-700 dark:text-slate-300">Enable Popup</span>
            </label>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        {{-- Left Form Fields (7 cols) --}}
        <div class="lg:col-span-7 space-y-5">
            {{-- Popup Title --}}
            <div>
                <label class="field-label">Notice / Announcement Title</label>
                <input type="text" name="popup_title" id="popupTitleInput" class="field-input font-medium"
                       placeholder="e.g. Admissions Open for Academic Year 2026-2027"
                       value="{{ old('popup_title', $school->popup_title) }}"
                       oninput="updatePopupPreview()">
                <p class="text-[11px] text-slate-400 mt-1">Main heading displayed prominently on the popup.</p>
            </div>

            {{-- Popup Subtitle / Body --}}
            <div>
                <label class="field-label">Subtitle / Key Information</label>
                <textarea name="popup_subtitle" id="popupSubtitleInput" rows="3" class="field-input"
                          placeholder="e.g. Entrance Examination scheduled for Chaitra 25. Scholarship applications now open for Science and Management streams."
                          oninput="updatePopupPreview()">{{ old('popup_subtitle', $school->popup_subtitle) }}</textarea>
                <p class="text-[11px] text-slate-400 mt-1">Summary description or guidelines shown under the title.</p>
            </div>

            {{-- Flyer / Notice Image Upload --}}
            <div>
                <label class="field-label">Notice Flyer / Banner Graphic</label>
                @if($school->popup_image)
                <div class="flex items-start gap-4 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl border border-slate-200 dark:border-slate-600 mb-3">
                    <img src="{{ $school->popupImageUrl }}" alt="Current Popup Flyer" id="currentPopupImg" class="w-28 h-28 object-cover rounded-lg border border-slate-200 shadow-sm bg-white">
                    <div class="flex flex-col gap-2">
                        <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                            <i class="bi bi-check-circle-fill"></i> Current flyer active
                        </span>
                        <p class="text-xs text-slate-500">Upload a new file below to replace it, or check the box to remove.</p>
                        <label class="inline-flex items-center gap-2 cursor-pointer text-xs text-rose-600 font-medium">
                            <input type="checkbox" name="remove_popup_image" value="1" class="rounded accent-rose-500" onchange="toggleRemovePopupImage(this)">
                            Remove this flyer image
                        </label>
                    </div>
                </div>
                @endif

                <label class="flex flex-col items-center justify-center gap-2 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl p-6 cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/50 dark:hover:bg-indigo-900/20 transition group">
                    <i class="bi bi-cloud-arrow-up text-3xl text-slate-400 group-hover:text-indigo-600 transition"></i>
                    <div class="text-center">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200">
                            {{ $school->popup_image ? 'Choose a new flyer image' : 'Upload admission flyer / official notice image' }}
                        </span>
                        <p class="text-[11px] text-slate-400 mt-0.5">JPEG, PNG, WebP · Max 3 MB</p>
                    </div>
                    <input type="file" name="popup_image" id="popupImageInput" accept="image/*" class="hidden" onchange="previewPopupImage(this)">
                </label>
            </div>

            {{-- CTA Button configuration --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="field-label">Action Button Text <span class="text-slate-400 font-normal">(Optional)</span></label>
                    <input type="text" name="popup_btn_text" id="popupBtnTextInput" class="field-input"
                           placeholder="e.g. Apply Online Now, View Details"
                           value="{{ old('popup_btn_text', $school->popup_btn_text) }}"
                           oninput="updatePopupPreview()">
                </div>
                <div>
                    <label class="field-label">Action Button Link / URL <span class="text-slate-400 font-normal">(Optional)</span></label>
                    <input type="text" name="popup_btn_url" id="popupBtnUrlInput" class="field-input"
                           placeholder="e.g. /admissions or https://example.com/form"
                           value="{{ old('popup_btn_url', $school->popup_btn_url) }}"
                           oninput="updatePopupPreview()">
                </div>
            </div>

            {{-- Scheduling and Behavior Options --}}
            <div class="border-t border-slate-200 dark:border-slate-700 pt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="field-label">Auto-Expire Date & Time <span class="text-slate-400 font-normal">(Optional)</span></label>
                    <input type="datetime-local" name="popup_expires_at" class="field-input"
                           value="{{ old('popup_expires_at', $school->popup_expires_at ? $school->popup_expires_at->format('Y-m-d\TH:i') : '') }}">
                    <p class="text-[11px] text-slate-400 mt-1">Leave empty to keep showing until manually toggled off.</p>
                </div>
                <div class="flex flex-col justify-center">
                    <label class="flex items-start gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" name="popup_show_once" value="1" class="rounded accent-indigo-600 mt-1"
                               {{ old('popup_show_once', $school->popup_show_once) ? 'checked' : '' }}>
                        <span class="text-xs text-slate-700 dark:text-slate-300 leading-snug">
                            <strong>Show only once per visitor session</strong><br>
                            <span class="text-slate-400 text-[11px]">When checked, visitors won't be repeatedly interrupted if they have already dismissed it today.</span>
                        </span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Right Live Simulation (5 cols) --}}
        <div class="lg:col-span-5 flex flex-col items-center">
            <div class="w-full bg-slate-900/90 dark:bg-slate-950 p-4 rounded-2xl border border-slate-700 shadow-2xl relative">
                <div class="flex items-center justify-between text-xs text-slate-400 pb-3 border-b border-slate-800 mb-3">
                    <span class="flex items-center gap-1.5 font-semibold text-slate-300">
                        <i class="bi bi-eye text-indigo-400"></i> Live Visitor Preview
                    </span>
                    <span id="previewStatusBadge" class="px-2 py-0.5 rounded text-[10px] font-bold {{ $school->popup_active ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-slate-700 text-slate-400' }}">
                        {{ $school->popup_active ? 'ENABLED' : 'DISABLED' }}
                    </span>
                </div>

                {{-- Modal Preview Card --}}
                <div class="bg-white dark:bg-slate-900 rounded-xl overflow-hidden shadow-xl border border-slate-200 dark:border-slate-700 max-w-sm mx-auto transition-all">
                    {{-- Preview Top Bar --}}
                    <div class="px-4 py-2.5 bg-slate-800 text-white flex items-center justify-between">
                        <span class="text-xs font-bold flex items-center gap-1.5 text-amber-300">
                            <i class="bi bi-bell-fill"></i> Official Notice
                        </span>
                        <span class="text-xs text-slate-400">&times;</span>
                    </div>

                    {{-- Preview Flyer Image --}}
                    <div id="previewFlyerContainer" class="{{ $school->popup_image ? '' : 'hidden' }} relative aspect-video bg-slate-100 dark:bg-slate-800 overflow-hidden border-b border-slate-200 dark:border-slate-800">
                        <img id="previewFlyerImg" src="{{ $school->popupImageUrl }}" alt="Preview" class="w-full h-full object-cover">
                    </div>

                    {{-- Preview Content --}}
                    <div class="p-4">
                        <h4 id="previewTitle" class="font-extrabold text-sm text-slate-900 dark:text-white mb-1.5 leading-snug">
                            {{ $school->popup_title ?: 'Your Notice Title Here' }}
                        </h4>
                        <p id="previewSubtitle" class="text-xs text-slate-600 dark:text-slate-300 mb-3 line-clamp-3 leading-relaxed">
                            {{ $school->popup_subtitle ?: 'Enter details such as admission dates, entrance exam requirements, or administrative updates.' }}
                        </p>

                        <div class="flex items-center gap-2 pt-1 border-t border-slate-100 dark:border-slate-800">
                            <span id="previewBtn" class="flex-1 text-center py-1.5 px-3 rounded-lg text-xs font-bold text-white bg-indigo-600 shadow-sm">
                                {{ $school->popup_btn_text ?: 'View Details' }}
                            </span>
                            <span class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 bg-slate-100 dark:bg-slate-800">
                                Close
                            </span>
                        </div>
                    </div>
                </div>

                <p class="text-[11px] text-slate-400 text-center mt-3">
                    This modal displays centered over a darkened backdrop on the landing page.
                </p>
            </div>
        </div>
    </div>
</div>
</div>{{-- /step-7 --}}

{{-- ════════════════════════════════════════════════
     FOOTER NAV (Prev / Next / Save)
════════════════════════════════════════════════ --}}
<div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-2xl shadow-sm px-6 py-4 transition-all">
    {{-- Previous Button --}}
    <button type="button" id="btnPrev" onclick="goStep(currentStep - 1)"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700/80 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-600 transition-all duration-200 group invisible focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
        <i class="bi bi-arrow-left transition-transform group-hover:-translate-x-1 text-sm"></i>
        <span>Previous Step</span>
    </button>

    {{-- Interactive Step Dots --}}
    <div class="flex items-center gap-2" id="footerDotsContainer">
        @for($i=1;$i<=count($steps);$i++)
        <button type="button" onclick="goStep({{ $i }})"
                class="transition-all duration-300 dot-indicator focus:outline-none rounded-full h-2.5
                       {{ $i === $activeStep ? 'bg-indigo-600 w-8 shadow-sm ring-2 ring-indigo-500/30' : ($i < $activeStep ? 'bg-emerald-500 hover:bg-emerald-600 w-2.5' : 'bg-slate-300 dark:bg-slate-600 hover:bg-slate-400 w-2.5') }}"
                id="dot-{{ $i }}"
                title="Go to Step {{ $i }}: {{ $steps[$i-1]['label'] }}"
                aria-label="Go to Step {{ $i }}"></button>
        @endfor
    </div>

    {{-- Action Buttons: Save & Next --}}
    <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
        {{-- Save on any step --}}
        <button type="submit" id="btnSaveBottom"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-sm hover:shadow-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
            <i class="bi bi-check2-circle text-sm"></i>
            <span id="btnSaveText">Save Changes</span>
        </button>
        {{-- Next step button --}}
        <button type="button" id="btnNext"
                onclick="goStep(currentStep + 1)"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 shadow-sm hover:shadow-md transition-all duration-200 group focus:outline-none focus:ring-2 focus:ring-indigo-500/40">
            <span id="btnNextText">Next Step</span>
            <i class="bi bi-arrow-right transition-transform group-hover:translate-x-1 text-sm"></i>
        </button>
    </div>
</div>

</form>
</div>{{-- /container --}}

{{-- ════════ Shared field styles ════════ --}}
<style>
.profile-stepper-wrap {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.06), 0 2px 6px -1px rgba(15, 23, 42, 0.04);
    overflow: hidden;
    transition: all 0.3s ease;
}
html[data-theme="dark"] .profile-stepper-wrap,
html.dark .profile-stepper-wrap {
    background: #0f172a !important;
    border-color: #1e293b !important;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5) !important;
}

.profile-stepper-grid {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: 8px;
}
@media (max-width: 1350px) {
    .profile-stepper-grid {
        display: flex;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        scrollbar-width: thin;
        -webkit-overflow-scrolling: touch;
        gap: 8px;
        padding: 10px 8px;
    }
    .profile-stepper-grid::-webkit-scrollbar {
        height: 4px;
    }
    .profile-stepper-grid::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    html[data-theme="dark"] .profile-stepper-grid::-webkit-scrollbar-thumb,
    html.dark .profile-stepper-grid::-webkit-scrollbar-thumb {
        background: #334155;
    }
}

.profile-step-btn {
    appearance: none !important;
    -webkit-appearance: none !important;
    background: #ffffff !important;
    border: 1.5px solid #f1f5f9 !important;
    outline: none !important;
    padding: 10px 12px;
    border-radius: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    position: relative;
    overflow: hidden;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    text-align: left;
    min-width: 148px;
    width: 100%;
    user-select: none;
    scroll-snap-align: start;
}
html[data-theme="dark"] .profile-step-btn,
html.dark .profile-step-btn {
    background: #131d31 !important;
    border-color: #1e293b !important;
}
.profile-step-btn:focus-visible {
    outline: 2px solid #6366f1 !important;
    outline-offset: 2px !important;
}

.profile-step-btn.is-idle {
    background: #f8fafc !important;
    border-color: #f1f5f9 !important;
}
html[data-theme="dark"] .profile-step-btn.is-idle,
html.dark .profile-step-btn.is-idle {
    background: #141f33 !important;
    border-color: #1e293b !important;
}
.profile-step-btn.is-idle:hover {
    background: #f1f5f9 !important;
    border-color: #e2e8f0 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.08);
}
html[data-theme="dark"] .profile-step-btn.is-idle:hover,
html.dark .profile-step-btn.is-idle:hover {
    background: #192742 !important;
    border-color: #2e3e5b !important;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.35);
}
.profile-step-btn.is-idle .step-icon-badge {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    color: #64748b;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
html[data-theme="dark"] .profile-step-btn.is-idle .step-icon-badge,
html.dark .profile-step-btn.is-idle .step-icon-badge {
    background: #1a263d !important;
    border-color: #273754 !important;
    color: #94a3b8 !important;
    box-shadow: none;
}
.profile-step-btn.is-idle .step-sub {
    color: #94a3b8;
}
html[data-theme="dark"] .profile-step-btn.is-idle .step-sub,
html.dark .profile-step-btn.is-idle .step-sub {
    color: #64748b !important;
}
.profile-step-btn.is-idle .step-title {
    color: #475569;
}
html[data-theme="dark"] .profile-step-btn.is-idle .step-title,
html.dark .profile-step-btn.is-idle .step-title {
    color: #94a3b8 !important;
}

.profile-step-btn.is-active {
    background: linear-gradient(145deg, #ffffff, #eff6ff) !important;
    border-color: #6366f1 !important;
    box-shadow: 0 6px 18px -2px rgba(79, 70, 229, 0.18), 0 2px 6px -1px rgba(79, 70, 229, 0.12) !important;
    transform: translateY(-1px);
}
html[data-theme="dark"] .profile-step-btn.is-active,
html.dark .profile-step-btn.is-active {
    background: linear-gradient(145deg, #18233c, #1f2e4e) !important;
    border-color: #6366f1 !important;
    box-shadow: 0 6px 20px -2px rgba(99, 102, 241, 0.35) !important;
}
.profile-step-btn.is-active .step-icon-badge {
    background: linear-gradient(135deg, #4f46e5, #6366f1);
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(79, 70, 229, 0.45);
    border: none;
    transform: scale(1.04);
}
html[data-theme="dark"] .profile-step-btn.is-active .step-icon-badge,
html.dark .profile-step-btn.is-active .step-icon-badge {
    background: linear-gradient(135deg, #6366f1, #818cf8);
    color: #ffffff;
    box-shadow: 0 4px 16px rgba(99, 102, 241, 0.6);
    border: none;
}
.profile-step-btn.is-active .step-sub {
    color: #4f46e5;
    font-weight: 800;
}
html[data-theme="dark"] .profile-step-btn.is-active .step-sub,
html.dark .profile-step-btn.is-active .step-sub {
    color: #a5b4fc !important;
}
.profile-step-btn.is-active .step-title {
    color: #0f172a;
    font-weight: 800;
}
html[data-theme="dark"] .profile-step-btn.is-active .step-title,
html.dark .profile-step-btn.is-active .step-title {
    color: #ffffff !important;
}

.step-active-indicator {
    position: absolute;
    bottom: 0;
    left: 10px;
    right: 10px;
    height: 3px;
    background: linear-gradient(90deg, #4f46e5, #06b6d4);
    border-radius: 3px 3px 0 0;
    opacity: 0;
    transform: scaleX(0.4);
    transition: all 0.25s ease;
}
.profile-step-btn.is-active .step-active-indicator {
    opacity: 1;
    transform: scaleX(1);
}

.profile-step-btn.is-completed {
    background: #fcfdfc !important;
    border-color: #dcfce7 !important;
}
html[data-theme="dark"] .profile-step-btn.is-completed,
html.dark .profile-step-btn.is-completed {
    background: #112224 !important;
    border-color: #173b32 !important;
}
.profile-step-btn.is-completed:hover {
    background: #f0fdf4 !important;
    border-color: #bbf7d0 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px -2px rgba(16, 185, 129, 0.12);
}
html[data-theme="dark"] .profile-step-btn.is-completed:hover,
html.dark .profile-step-btn.is-completed:hover {
    background: #152d2f !important;
    border-color: #245c4e !important;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.2);
}
.profile-step-btn.is-completed .step-icon-badge {
    background: linear-gradient(135deg, #10b981, #059669);
    color: #ffffff;
    border: none;
    box-shadow: 0 3px 10px rgba(16, 185, 129, 0.35);
}
html[data-theme="dark"] .profile-step-btn.is-completed .step-icon-badge,
html.dark .profile-step-btn.is-completed .step-icon-badge {
    background: linear-gradient(135deg, #059669, #10b981);
    color: #ffffff;
    border: none;
    box-shadow: 0 3px 12px rgba(16, 185, 129, 0.4);
}
.profile-step-btn.is-completed .step-sub {
    color: #059669;
    font-weight: 700;
}
html[data-theme="dark"] .profile-step-btn.is-completed .step-sub,
html.dark .profile-step-btn.is-completed .step-sub {
    color: #34d399 !important;
}
.profile-step-btn.is-completed .step-title {
    color: #15803d;
}
html[data-theme="dark"] .profile-step-btn.is-completed .step-title,
html.dark .profile-step-btn.is-completed .step-title {
    color: #cbd5e1 !important;
}

.step-icon-badge {
    width: 36px;
    height: 36px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.step-info-col {
    display: flex;
    flex-direction: column;
    min-width: 0;
    line-height: 1.25;
    flex-grow: 1;
}
.step-sub {
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 2px;
    transition: color 0.2s ease;
    white-space: nowrap;
}
.step-title {
    font-size: 12.5px;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: color 0.2s ease;
}

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
const STEP_TITLES = @json(collect($steps)->pluck('label')->values());
const TOTAL_STEPS = {{ count($steps) }};
let currentStep   = {{ $activeStep }};

function goStep(n) {
    n = Math.max(1, Math.min(TOTAL_STEPS, n));

    // Hide all panels, show target
    document.querySelectorAll('.step-panel').forEach(p => p.classList.add('hidden'));
    const target = document.getElementById('step-' + n);
    if (target) target.classList.remove('hidden');

    // Update progress percentage and track
    const pct = Math.round((n / TOTAL_STEPS) * 100);
    const progressFill = document.getElementById('stepperProgressFill');
    if (progressFill) progressFill.style.width = pct + '%';
    
    const pctText = document.getElementById('stepperPercentText');
    if (pctText) pctText.textContent = pct + '%';

    const stepNum = document.getElementById('stepperStepNum');
    if (stepNum) stepNum.textContent = n;

    const activeTitle = document.getElementById('stepperActiveTitle');
    if (activeTitle && STEP_TITLES[n-1]) activeTitle.textContent = STEP_TITLES[n-1];

    // Update step indicator buttons
    document.querySelectorAll('.step-btn').forEach(btn => {
        const s    = parseInt(btn.dataset.step);
        const ring = btn.querySelector('[data-ring]');
        const sub  = btn.querySelector('[data-sub]');

        btn.classList.remove('is-active', 'is-completed', 'is-idle');

        if (s < n) {
            btn.classList.add('is-completed');
            if (ring) ring.innerHTML = '<i class="bi bi-check2 text-base"></i>';
            if (sub)  sub.innerHTML = 'Completed <i class="bi bi-check-circle-fill text-[9px] text-emerald-500 dark:text-emerald-400"></i>';
        } else if (s === n) {
            btn.classList.add('is-active');
            if (ring) ring.innerHTML = '<i class="bi ' + STEP_ICONS[s-1] + '"></i>';
            if (sub)  sub.textContent = 'Step ' + s;
            // Auto scroll button into view on mobile/tablet
            btn.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        } else {
            btn.classList.add('is-idle');
            if (ring) ring.innerHTML = '<i class="bi ' + STEP_ICONS[s-1] + '"></i>';
            if (sub)  sub.textContent = 'Step ' + s;
        }
    });

    // Update dots in footer
    document.querySelectorAll('.dot-indicator').forEach((d, i) => {
        const s = i + 1;
        d.classList.remove('w-8', 'w-2.5', 'bg-indigo-600', 'bg-emerald-500', 'bg-slate-300', 'dark:bg-slate-600', 'ring-2', 'ring-indigo-500/30');
        if (s === n) {
            d.classList.add('w-8', 'bg-indigo-600', 'ring-2', 'ring-indigo-500/30');
        } else if (s < n) {
            d.classList.add('w-2.5', 'bg-emerald-500');
        } else {
            d.classList.add('w-2.5', 'bg-slate-300', 'dark:bg-slate-600');
        }
    });

    // Prev/Next buttons
    const prev = document.getElementById('btnPrev');
    const next = document.getElementById('btnNext');
    if (prev) {
        if (n <= 1) {
            prev.classList.add('invisible', 'pointer-events-none');
        } else {
            prev.classList.remove('invisible', 'pointer-events-none');
        }
    }
    if (next) {
        if (n >= TOTAL_STEPS) {
            next.classList.add('hidden');
        } else {
            next.classList.remove('hidden');
        }
    }

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
        c.classList.remove('border-indigo-600','bg-indigo-50/20','dark:bg-indigo-950/30','shadow-md','ring-2','ring-indigo-500/20');
        c.classList.add('border-slate-200','dark:border-slate-700');
    });
    document.querySelectorAll('[id^="preset-check-"]').forEach(el => {
        el.classList.add('opacity-0','scale-75');
        el.classList.remove('opacity-100','scale-100');
    });
    const card  = document.getElementById('preset-' + key);
    const check = document.getElementById('preset-check-' + key);
    if (card)  { 
        card.classList.remove('border-slate-200','dark:border-slate-700'); 
        card.classList.add('border-indigo-600','bg-indigo-50/20','dark:bg-indigo-950/30','shadow-md','ring-2','ring-indigo-500/20'); 
    }
    if (check) { 
        check.classList.remove('opacity-0','scale-75'); 
        check.classList.add('opacity-100','scale-100'); 
    }
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

/* ── Device Viewport Switcher ────────────────────────────────── */
function setPreviewDevice(device) {
    const frameWrap = document.getElementById('previewFrameWrap');
    const btnDesktop = document.getElementById('btn-prev-desktop');
    const btnMobile = document.getElementById('btn-prev-mobile');
    if (!frameWrap) return;

    if (device === 'mobile') {
        frameWrap.style.maxWidth = '290px';
        if (btnMobile) {
            btnMobile.className = 'px-2 py-1 rounded-md text-[10px] font-bold text-indigo-600 dark:text-indigo-400 bg-white dark:bg-slate-800 shadow-xs transition';
        }
        if (btnDesktop) {
            btnDesktop.className = 'px-2 py-1 rounded-md text-[10px] font-bold text-slate-500 dark:text-slate-400 hover:text-slate-800 transition';
        }
    } else {
        frameWrap.style.maxWidth = '100%';
        if (btnDesktop) {
            btnDesktop.className = 'px-2 py-1 rounded-md text-[10px] font-bold text-indigo-600 dark:text-indigo-400 bg-white dark:bg-slate-800 shadow-xs transition';
        }
        if (btnMobile) {
            btnMobile.className = 'px-2 py-1 rounded-md text-[10px] font-bold text-slate-500 dark:text-slate-400 hover:text-slate-800 transition';
        }
    }
}

/* ── Live preview updater & Realtime Sync Engine ─────────────────── */
function updatePreview() {
    const p = document.getElementById('primary_color')?.value   || '#1e4080';
    const s = document.getElementById('secondary_color')?.value || '#e6b800';

    const set = (id, prop, val) => { const el = document.getElementById(id); if(el) el.style[prop] = val; };
    
    // Buttons & Brand Color Highlights
    set('prev-btn-apply', 'background', p);
    set('prev-badge-name', 'background', p);
    set('prev-main-cta', 'background', p);
    set('prev-headline-accent', 'textDecorationColor', p);
    set('prev-prospectus-icon', 'color', p);
    set('prev-results-icon', 'color', p);
    set('swatch-primary', 'background', p);
    set('swatch-secondary', 'background', s);

    // Swatch hex labels
    const pl = document.getElementById('swatch-primary-label');
    const sl = document.getElementById('swatch-secondary-label');
    if(pl) pl.textContent = p;
    if(sl) sl.textContent = s;

    // School Name Sync
    const nameInput = document.querySelector('input[name="name"]');
    if (nameInput) {
        const val = nameInput.value.trim() || 'School Name';
        const elNav = document.getElementById('prev-school-name');
        const elBadge = document.getElementById('prev-badge-name');
        if (elNav) elNav.textContent = val;
        if (elBadge) elBadge.textContent = val;
    }

    // Slogan / Description Sync
    const descInput = document.querySelector('input[name="description"]') || 
                      document.querySelector('textarea[name="description"]') || 
                      document.querySelector('input[name="slogan"]');
    if (descInput) {
        const val = descInput.value.trim();
        const elSlogan = document.getElementById('prev-hero-slogan');
        const elNavSlogan = document.getElementById('prev-nav-slogan');
        if (elSlogan) {
            elSlogan.textContent = val || 'Premier institution dedicated to academic excellence, disciplined leadership, holistic sports, and modern STEM innovation.';
        }
        if (elNavSlogan) {
            elNavSlogan.textContent = val ? (val.length > 32 ? val.substring(0, 32) + '...' : val) : 'Excellence in Education & Character';
        }
    }

    // City / Location Sync
    const cityInput = document.querySelector('input[name="city"]');
    if (cityInput) {
        const val = cityInput.value.trim();
        const elNavCity = document.getElementById('prev-nav-city');
        const elBadgeCity = document.getElementById('prev-badge-city');
        if (elNavCity) elNavCity.textContent = val || 'Siraha';
        if (elBadgeCity) elBadgeCity.textContent = val ? val.toLowerCase() : 'siraha';
    }

    // Social Links Sync
    const fbInput = document.querySelector('input[name="facebook_url"]')?.value?.trim();
    const ytInput = document.querySelector('input[name="youtube_url"]')?.value?.trim();
    const igInput = document.querySelector('input[name="instagram_url"]')?.value?.trim();
    
    const socFb = document.getElementById('prev-soc-fb');
    const socYt = document.getElementById('prev-soc-yt');
    const socIg = document.getElementById('prev-soc-ig');

    if (socFb) socFb.style.opacity = fbInput ? '1' : '0.4';
    if (socYt) socYt.style.opacity = ytInput ? '1' : '0.4';
    if (socIg) socIg.style.opacity = igInput ? '1' : '0.4';
}

/* ── Logo preview & live sync ─────────────────────────────────── */
const logoInputEl = document.getElementById('logoInput');
if (logoInputEl) {
    logoInputEl.addEventListener('change', function() {
        if (!this.files || !this.files[0]) return;
        const url = URL.createObjectURL(this.files[0]);
        const img = document.getElementById('logoPreview');
        const ph  = document.getElementById('logoPlaceholder');
        const prevLogo = document.getElementById('prev-logo-img');
        const prevLogoFb = document.getElementById('prev-logo-fallback');

        if (img) { img.src = url; img.classList.remove('hidden'); }
        if (ph)  ph.classList.add('hidden');
        if (prevLogo) { 
            prevLogo.src = url; 
            prevLogo.classList.remove('hidden'); 
        }
        if (prevLogoFb) {
            prevLogoFb.classList.add('hidden');
        }
    });
}

// Banner Slider Live sync to preview
const newSliderInputEl = document.getElementById('newSliderInput');
if (newSliderInputEl) {
    newSliderInputEl.addEventListener('change', function() {
        if (!this.files || !this.files[0]) return;
        const url = URL.createObjectURL(this.files[0]);
        const prevHeroImg = document.getElementById('prev-hero-banner-img');
        if (prevHeroImg) prevHeroImg.src = url;
    });
}

// Remove Logo Live sync
const removeLogoChk = document.querySelector('input[name="remove_logo"]');
if (removeLogoChk) {
    removeLogoChk.addEventListener('change', function() {
        const prevLogo = document.getElementById('prev-logo-img');
        const prevLogoFb = document.getElementById('prev-logo-fallback');
        if (this.checked) {
            if (prevLogo) prevLogo.classList.add('hidden');
            if (prevLogoFb) prevLogoFb.classList.remove('hidden');
        } else {
            if (prevLogo && prevLogo.getAttribute('src')) {
                prevLogo.classList.remove('hidden');
                if (prevLogoFb) prevLogoFb.classList.add('hidden');
            }
        }
    });
}

// Realtime listeners for inputs across the form
document.addEventListener('DOMContentLoaded', function() {
    updatePreview();

    const watchSelectors = [
        'input[name="name"]',
        'input[name="phone"]',
        'input[name="email"]',
        'input[name="city"]',
        'input[name="address"]',
        'input[name="description"]',
        'textarea[name="description"]',
        'input[name="slogan"]',
        'input[name="facebook_url"]',
        'input[name="twitter_url"]',
        'input[name="instagram_url"]',
        'input[name="youtube_url"]',
        '#primary_color',
        '#primary_color_text',
        '#secondary_color',
        '#secondary_color_text'
    ];

    watchSelectors.forEach(sel => {
        const els = document.querySelectorAll(sel);
        els.forEach(el => {
            ['input', 'keyup', 'change', 'paste'].forEach(ev => {
                el.addEventListener(ev, updatePreview);
            });
        });
    });
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

/* ── Popup notice live preview ────────────────────────────────── */
function updatePopupPreview() {
    const title = document.getElementById('popupTitleInput')?.value;
    const sub   = document.getElementById('popupSubtitleInput')?.value;
    const btn   = document.getElementById('popupBtnTextInput')?.value;

    const elTitle = document.getElementById('previewTitle');
    const elSub   = document.getElementById('previewSubtitle');
    const elBtn   = document.getElementById('previewBtn');

    if (elTitle) elTitle.textContent = title || 'Your Notice Title Here';
    if (elSub)   elSub.textContent   = sub   || 'Enter details such as admission dates, entrance exam requirements, or administrative updates.';
    if (elBtn)   elBtn.textContent   = btn   || 'View Details';
}

function togglePopupLivePreview() {
    const chk   = document.getElementById('popupActiveToggle');
    const badge = document.getElementById('previewStatusBadge');
    if (!badge || !chk) return;
    if (chk.checked) {
        badge.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30';
        badge.textContent = 'ENABLED';
    } else {
        badge.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-slate-700 text-slate-400';
        badge.textContent = 'DISABLED';
    }
}

function previewPopupImage(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const previewFlyerImg       = document.getElementById('previewFlyerImg');
        const previewFlyerContainer = document.getElementById('previewFlyerContainer');
        if (previewFlyerImg) previewFlyerImg.src = e.target.result;
        if (previewFlyerContainer) previewFlyerContainer.classList.remove('hidden');
    };
    reader.readAsDataURL(input.files[0]);
}

function toggleRemovePopupImage(chk) {
    const previewFlyerContainer = document.getElementById('previewFlyerContainer');
    const currentPopupImg       = document.getElementById('currentPopupImg');
    if (chk.checked) {
        if (previewFlyerContainer) previewFlyerContainer.classList.add('opacity-30');
        if (currentPopupImg)       currentPopupImg.classList.add('opacity-30');
    } else {
        if (previewFlyerContainer) previewFlyerContainer.classList.remove('opacity-30');
        if (currentPopupImg)       currentPopupImg.classList.remove('opacity-30');
    }
}

/* ── Hero slider banner previews (Images & MP4 Videos) ────── */
function previewNewSlider(input) {
    const previewImg   = document.getElementById('newSliderPreviewImg');
    const previewVideo = document.getElementById('newSliderPreviewVideo');
    const placeholder  = document.getElementById('newSliderPlaceholder');
    const sideMedia    = document.getElementById('prev-hero-banner-media');
    const sideHeroWrap = document.getElementById('prev-hero-wrap');

    if (input.files && input.files[0]) {
        const file = input.files[0];
        const isVideo = file.type.startsWith('video/') || /\.(mp4|webm|ogg|mov)$/i.test(file.name);
        const objectUrl = URL.createObjectURL(file);

        if (isVideo) {
            if (previewImg) previewImg.classList.add('hidden');
            if (previewVideo) {
                previewVideo.src = objectUrl;
                previewVideo.muted = true;
                previewVideo.loop = true;
                previewVideo.playsInline = true;
                previewVideo.classList.remove('hidden');
                previewVideo.play().catch(() => {});
            }
            if (sideHeroWrap) {
                let currentSideMedia = document.getElementById('prev-hero-banner-media');
                if (currentSideMedia && currentSideMedia.tagName.toLowerCase() !== 'video') {
                    const newVid = document.createElement('video');
                    newVid.id = 'prev-hero-banner-media';
                    newVid.className = 'w-full h-full object-cover object-center select-none';
                    newVid.autoplay = true;
                    newVid.muted = true;
                    newVid.loop = true;
                    newVid.playsInline = true;
                    newVid.src = objectUrl;
                    currentSideMedia.replaceWith(newVid);
                    newVid.play().catch(() => {});
                } else if (currentSideMedia) {
                    currentSideMedia.src = objectUrl;
                    currentSideMedia.play().catch(() => {});
                }
            }
        } else {
            if (previewVideo) {
                previewVideo.pause();
                previewVideo.src = '';
                previewVideo.classList.add('hidden');
            }
            if (previewImg) {
                previewImg.src = objectUrl;
                previewImg.classList.remove('hidden');
            }
            if (sideHeroWrap) {
                let currentSideMedia = document.getElementById('prev-hero-banner-media');
                if (currentSideMedia && currentSideMedia.tagName.toLowerCase() !== 'img') {
                    const newImg = document.createElement('img');
                    newImg.id = 'prev-hero-banner-media';
                    newImg.className = 'w-full h-full object-cover object-center select-none';
                    newImg.alt = 'Hero Banner';
                    newImg.src = objectUrl;
                    currentSideMedia.replaceWith(newImg);
                } else if (currentSideMedia) {
                    currentSideMedia.src = objectUrl;
                }
            }
        }

        if (placeholder) placeholder.classList.add('hidden');
    }
}

function previewReplaceSlider(input, idx) {
    const previewContainer = document.getElementById('slider-preview-wrap-' + idx);
    const previewImg = document.getElementById('slider-preview-img-' + idx);
    const previewVideo = document.getElementById('slider-preview-video-' + idx);
    const badge = document.getElementById('slider-type-badge-' + idx);

    if (input.files && input.files[0]) {
        const file = input.files[0];
        const isVideo = file.type.startsWith('video/') || /\.(mp4|webm|ogg|mov)$/i.test(file.name);
        const objectUrl = URL.createObjectURL(file);

        if (isVideo) {
            if (previewImg) previewImg.classList.add('hidden');
            if (previewVideo) {
                previewVideo.src = objectUrl;
                previewVideo.muted = true;
                previewVideo.loop = true;
                previewVideo.playsInline = true;
                previewVideo.classList.remove('hidden');
                previewVideo.play().catch(() => {});
            }
            if (badge) {
                badge.className = 'absolute top-2 left-2 z-10 bg-amber-500/90 backdrop-blur-sm text-slate-950 text-[10px] font-black px-2 py-0.5 rounded-md flex items-center gap-1 shadow-xs';
                badge.innerHTML = '<i class="bi bi-camera-video-fill"></i> MP4 Video';
            }
        } else {
            if (previewVideo) {
                previewVideo.pause();
                previewVideo.src = '';
                previewVideo.classList.add('hidden');
            }
            if (previewImg) {
                previewImg.src = objectUrl;
                previewImg.classList.remove('hidden');
            }
            if (badge) {
                badge.className = 'absolute top-2 left-2 z-10 bg-slate-900/80 backdrop-blur-sm text-white text-[10px] font-bold px-2 py-0.5 rounded-md flex items-center gap-1';
                badge.innerHTML = '<i class="bi bi-image"></i> Image (WebP)';
            }
        }

        // If this was the first slide, also update side preview
        if (idx === 0) {
            const sideHeroWrap = document.getElementById('prev-hero-wrap');
            if (sideHeroWrap) {
                let currentSideMedia = document.getElementById('prev-hero-banner-media');
                if (isVideo) {
                    if (currentSideMedia && currentSideMedia.tagName.toLowerCase() !== 'video') {
                        const newVid = document.createElement('video');
                        newVid.id = 'prev-hero-banner-media';
                        newVid.className = 'w-full h-full object-cover object-center select-none';
                        newVid.autoplay = true;
                        newVid.muted = true;
                        newVid.loop = true;
                        newVid.playsInline = true;
                        newVid.src = objectUrl;
                        currentSideMedia.replaceWith(newVid);
                        newVid.play().catch(() => {});
                    } else if (currentSideMedia) {
                        currentSideMedia.src = objectUrl;
                        currentSideMedia.play().catch(() => {});
                    }
                } else {
                    if (currentSideMedia && currentSideMedia.tagName.toLowerCase() !== 'img') {
                        const newImg = document.createElement('img');
                        newImg.id = 'prev-hero-banner-media';
                        newImg.className = 'w-full h-full object-cover object-center select-none';
                        newImg.alt = 'Hero Banner';
                        newImg.src = objectUrl;
                        currentSideMedia.replaceWith(newImg);
                    } else if (currentSideMedia) {
                        currentSideMedia.src = objectUrl;
                    }
                }
            }
        }
    }
}

function toggleSliderDelete(idx, checked) {
    const mask = document.getElementById('slider-del-mask-' + idx);
    if (mask) {
        if (checked) {
            mask.classList.remove('hidden');
            mask.classList.add('flex');
        } else {
            mask.classList.add('hidden');
            mask.classList.remove('flex');
        }
    }
}

function previewPrincipalPhoto(input) {
    const preview = document.getElementById('principalPhotoPreview');
    if (!preview) return;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
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
