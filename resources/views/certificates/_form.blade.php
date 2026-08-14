{{-- Shared form partial for create + edit --}}
@php $t = $template ?? null; @endphp

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

    {{-- LEFT COLUMN: FORM CONTROLS --}}
    <div class="lg:col-span-7 space-y-6">

        {{-- ── 1. Color & Design Presets ────────────────────────────────────── --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-xs p-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <i class="bi bi-palette-fill text-indigo-500"></i> Color & Design Presets
                </h3>
                <span class="text-xs text-slate-400">Click to apply instant theme</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5">
                <button type="button" onclick="applyPresetColor('#b45309', '#d97706', 'ornate')"
                    class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-amber-500 bg-amber-50/50 dark:bg-amber-900/10 text-left transition cursor-pointer">
                    <div class="flex gap-1 mb-1.5">
                        <span class="w-4 h-4 rounded-full bg-[#b45309]"></span>
                        <span class="w-4 h-4 rounded-full bg-[#d97706]"></span>
                    </div>
                    <div class="text-xs font-bold text-amber-900 dark:text-amber-300">Royal Gold</div>
                </button>

                <button type="button" onclick="applyPresetColor('#1e3a8a', '#0284c7', 'classic')"
                    class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-blue-500 bg-blue-50/50 dark:bg-blue-900/10 text-left transition cursor-pointer">
                    <div class="flex gap-1 mb-1.5">
                        <span class="w-4 h-4 rounded-full bg-[#1e3a8a]"></span>
                        <span class="w-4 h-4 rounded-full bg-[#0284c7]"></span>
                    </div>
                    <div class="text-xs font-bold text-blue-900 dark:text-blue-300">Ocean Blue</div>
                </button>

                <button type="button" onclick="applyPresetColor('#065f46', '#10b981', 'modern')"
                    class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-emerald-500 bg-emerald-50/50 dark:bg-emerald-900/10 text-left transition cursor-pointer">
                    <div class="flex gap-1 mb-1.5">
                        <span class="w-4 h-4 rounded-full bg-[#065f46]"></span>
                        <span class="w-4 h-4 rounded-full bg-[#10b981]"></span>
                    </div>
                    <div class="text-xs font-bold text-emerald-900 dark:text-emerald-300">Emerald</div>
                </button>

                <button type="button" onclick="applyPresetColor('#881337', '#f59e0b', 'ornate')"
                    class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-rose-500 bg-rose-50/50 dark:bg-rose-900/10 text-left transition cursor-pointer">
                    <div class="flex gap-1 mb-1.5">
                        <span class="w-4 h-4 rounded-full bg-[#881337]"></span>
                        <span class="w-4 h-4 rounded-full bg-[#f59e0b]"></span>
                    </div>
                    <div class="text-xs font-bold text-rose-900 dark:text-rose-300">Crimson</div>
                </button>

                <button type="button" onclick="applyPresetColor('#581c87', '#a855f7', 'modern')"
                    class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-purple-500 bg-purple-50/50 dark:bg-purple-900/10 text-left transition cursor-pointer">
                    <div class="flex gap-1 mb-1.5">
                        <span class="w-4 h-4 rounded-full bg-[#581c87]"></span>
                        <span class="w-4 h-4 rounded-full bg-[#a855f7]"></span>
                    </div>
                    <div class="text-xs font-bold text-purple-900 dark:text-purple-300">Imperial</div>
                </button>
            </div>
        </div>

        {{-- ── 2. Basic Info ────────────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-xs p-6">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-2">
                <i class="bi bi-info-circle text-indigo-500"></i> Basic Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Name --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                        Template Name <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" id="input_name" name="name" value="{{ old('name', $t->name ?? '') }}" required
                        oninput="updateLivePreview()"
                        placeholder="e.g. Standard Character Certificate"
                        class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>

                {{-- Type (grouped select) --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                        Certificate Type <span class="text-rose-400">*</span>
                    </label>
                    <select id="input_type" name="type" required onchange="updateLivePreview()"
                        class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach($typeGroups as $group => $typeCodes)
                        <optgroup label="{{ $group }}">
                            @foreach($typeCodes as $code)
                            <option value="{{ $code }}" {{ old('type', $t->type ?? '') === $code ? 'selected' : '' }}>
                                {{ $types[$code] }}
                            </option>
                            @endforeach
                        </optgroup>
                        @endforeach
                    </select>
                </div>

                {{-- Language --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                        Default Language <span class="text-rose-400">*</span>
                    </label>
                    <select id="input_language" name="language" required onchange="updateLivePreview()"
                        class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="en" {{ old('language', $t->language ?? 'en') === 'en' ? 'selected' : '' }}>English</option>
                        <option value="ne" {{ old('language', $t->language ?? 'en') === 'ne' ? 'selected' : '' }}>Nepali (नेपाली)</option>
                    </select>
                </div>

                {{-- Active toggle (edit only) --}}
                @if($t)
                <div class="flex items-center gap-3 pt-5">
                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $t->is_active ?? true) ? 'checked' : '' }}
                            class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
                        <span class="text-sm text-slate-700 dark:text-slate-200 font-medium">Active (available for use)</span>
                    </label>
                </div>
                @endif
            </div>
        </div>

        {{-- ── 3. Page Layout & Styling ──────────────────────────────────────── --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-xs p-6">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-2">
                <i class="bi bi-layout-text-window text-indigo-500"></i> Page Layout & Styling
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

                {{-- Paper size --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Paper Size</label>
                    <select id="input_paper_size" name="paper_size" onchange="updateLivePreview()"
                        class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="A4"     {{ old('paper_size', $t->paper_size ?? 'A4') === 'A4' ? 'selected' : '' }}>A4</option>
                        <option value="Letter" {{ old('paper_size', $t->paper_size ?? 'A4') === 'Letter' ? 'selected' : '' }}>Letter</option>
                    </select>
                </div>

                {{-- Orientation --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Orientation</label>
                    <select id="input_orientation" name="orientation" onchange="updateLivePreview()"
                        class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="landscape" {{ old('orientation', $t->orientation ?? 'landscape') === 'landscape' ? 'selected' : '' }}>Landscape</option>
                        <option value="portrait"  {{ old('orientation', $t->orientation ?? 'landscape') === 'portrait'  ? 'selected' : '' }}>Portrait</option>
                    </select>
                </div>

                {{-- Border style --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Border Style</label>
                    <select id="input_border_style" name="border_style" onchange="updateLivePreview()"
                        class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach(\App\Models\CertificateTemplate::BORDER_STYLES as $val => $label)
                        <option value="{{ $val }}" {{ old('border_style', $t->border_style ?? 'classic') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Colors --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Colors</label>
                    <div class="flex gap-2">
                        <div class="flex-1">
                            <input type="color" id="input_primary_color" name="primary_color" value="{{ old('primary_color', $t->primary_color ?? '#b45309') }}"
                                title="Primary color" oninput="updateLivePreview()"
                                class="w-full h-9 rounded-lg border border-slate-200 dark:border-slate-600 cursor-pointer">
                            <p class="text-xs text-slate-400 mt-0.5 text-center">Primary</p>
                        </div>
                        <div class="flex-1">
                            <input type="color" id="input_secondary_color" name="secondary_color" value="{{ old('secondary_color', $t->secondary_color ?? '#d97706') }}"
                                title="Secondary color" oninput="updateLivePreview()"
                                class="w-full h-9 rounded-lg border border-slate-200 dark:border-slate-600 cursor-pointer">
                            <p class="text-xs text-slate-400 mt-0.5 text-center">Secondary</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Toggles --}}
            <div class="flex flex-wrap gap-6 mt-4 pt-4 border-t border-slate-50 dark:border-slate-700">
                @foreach([
                    ['name'=>'show_qr_code',       'label'=>'Show QR Code',          'default'=>true],
                    ['name'=>'show_student_photo',  'label'=>'Include Student Photo', 'default'=>false],
                    ['name'=>'show_serial_number',  'label'=>'Show Certificate No.',  'default'=>true],
                ] as $toggle)
                <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                    <input type="hidden" name="{{ $toggle['name'] }}" value="0">
                    <input type="checkbox" id="input_{{ $toggle['name'] }}" name="{{ $toggle['name'] }}" value="1" onchange="updateLivePreview()"
                        {{ old($toggle['name'], $t?->{$toggle['name']} ?? $toggle['default']) ? 'checked' : '' }}
                        class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
                    <span class="text-sm text-slate-700 dark:text-slate-200">{{ $toggle['label'] }}</span>
                </label>
                @endforeach
            </div>
        </div>

        {{-- ── 4. Content & Template Text ────────────────────────────────────── --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-xs p-6">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-2">
                <i class="bi bi-file-text text-indigo-500"></i> Certificate Content
            </h3>
            <div class="space-y-4">

                {{-- Header text --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Header / Title Text</label>
                    <input type="text" id="input_header_text" name="header_text" value="{{ old('header_text', $t->header_text ?? '') }}" oninput="updateLivePreview()"
                        placeholder="e.g. Certificate of Achievement / character certificate"
                        class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>

                {{-- Body text (English) --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                        Body Text (English) <span class="text-rose-400">*</span>
                    </label>
                    <textarea id="input_body_text" name="body_text" rows="6" required oninput="updateLivePreview()"
                        placeholder="This is to certify that @{{student_name}} of Class @{{class}}, Section @{{section}}, Roll No. @{{roll_no}}, is a bonafide student of @{{school_name}} for the academic year @{{academic_year}}."
                        class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 resize-y focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('body_text', $t->body_text ?? '') }}</textarea>
                </div>

                {{-- Body text (Nepali) --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                        Body Text (Nepali — नेपाली)
                        <span class="text-slate-400 font-normal">(optional — used when language is set to Nepali)</span>
                    </label>
                    <textarea id="input_body_text_ne" name="body_text_ne" rows="4" oninput="updateLivePreview()"
                        placeholder="यो प्रमाणित गरिन्छ कि @{{student_name}} यस @{{school_name}} का विद्यार्थी हुन् ..."
                        class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 resize-y focus:outline-none focus:ring-2 focus:ring-indigo-400 font-sans">{{ old('body_text_ne', $t->body_text_ne ?? '') }}</textarea>
                </div>

                {{-- Footer text --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Footer Text</label>
                    <input type="text" id="input_footer_text" name="footer_text" value="{{ old('footer_text', $t->footer_text ?? '') }}" oninput="updateLivePreview()"
                        placeholder="e.g. This certificate is issued upon request with official seal."
                        class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>

                {{-- Token helper chips --}}
                <div class="bg-slate-50 dark:bg-slate-700/40 rounded-xl p-3 text-xs text-slate-500 dark:text-slate-400">
                    <p class="font-semibold mb-2 text-slate-600 dark:text-slate-300">Click a token to copy:</p>
                    <div class="flex flex-wrap gap-1.5" id="tokenList">
                        @php
                        $availableTokens = [
                            '{{student_name}}','{{first_name}}','{{last_name}}','{{class}}',
                            '{{section}}','{{roll_no}}','{{admission_no}}','{{board_reg_no}}',
                            '{{academic_year}}','{{birthday}}','{{age}}','{{gender}}',
                            '{{nationality}}','{{religion}}','{{father_name}}','{{mother_name}}',
                            '{{address}}','{{date}}','{{school_name}}','{{issued_by}}',
                        ];
                        @endphp
                        @foreach($availableTokens as $tok)
                        <button type="button" onclick="copyToken('{{ $tok }}')"
                            class="bg-slate-200 dark:bg-slate-600 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-300 px-2 py-0.5 rounded font-mono cursor-pointer transition text-[11px]">
                            {{ $tok }}
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ── 5. Signatures & Authority ────────────────────────────────────── --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-xs p-6">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-2">
                <i class="bi bi-pen text-indigo-500"></i> Signatory & Authority
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Signatory Name</label>
                    <input type="text" id="input_signature_name" name="signature_name" value="{{ old('signature_name', $t->signature_name ?? '') }}" oninput="updateLivePreview()"
                        placeholder="e.g. Sunil Yadav"
                        class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Signatory Title</label>
                    <input type="text" id="input_signature_title" name="signature_title" value="{{ old('signature_title', $t->signature_title ?? '') }}" oninput="updateLivePreview()"
                        placeholder="e.g. Principal / Head of Institution"
                        class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
            </div>
        </div>

        {{-- ── 6. Branding & Uploads ────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-xs p-6">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-2">
                <i class="bi bi-image text-indigo-500"></i> Branding & Uploads
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- School logo --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                        School Logo <span class="text-slate-400 font-normal">(PNG/JPG, max 5MB)</span>
                    </label>
                    @if($t && $t->logo_path)
                    <div class="mb-2 flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-700 rounded-lg">
                        <img src="{{ Storage::disk('public')->url($t->logo_path) }}" alt="Logo" class="h-10 object-contain">
                        <label class="inline-flex items-center gap-1.5 text-xs text-rose-600 cursor-pointer">
                            <input type="checkbox" name="remove_logo" value="1" class="w-3 h-3 rounded text-rose-500">
                            Remove logo
                        </label>
                    </div>
                    @endif
                    <input type="file" name="logo" accept="image/png,image/jpeg"
                        class="w-full text-sm text-slate-600 dark:text-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/30 dark:file:text-indigo-300 cursor-pointer">
                </div>

                {{-- Signature image --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                        Signature Image <span class="text-slate-400 font-normal">(PNG/JPG, max 2MB)</span>
                    </label>
                    @if($t && $t->signature_image_path)
                    <div class="mb-2 flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-700 rounded-lg">
                        <img src="{{ Storage::disk('public')->url($t->signature_image_path) }}" alt="Signature" class="h-10 object-contain">
                        <label class="inline-flex items-center gap-1.5 text-xs text-rose-600 cursor-pointer">
                            <input type="checkbox" name="remove_signature_image" value="1" class="w-3 h-3 rounded text-rose-500">
                            Remove signature
                        </label>
                    </div>
                    @endif
                    <input type="file" name="signature_image" accept="image/png,image/jpeg"
                        class="w-full text-sm text-slate-600 dark:text-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/30 dark:file:text-indigo-300 cursor-pointer">
                </div>
            </div>
        </div>

    </div>

    {{-- RIGHT COLUMN: STICKY LIVE CERTIFICATE PREVIEW STUDIO --}}
    <div class="lg:col-span-5 lg:sticky lg:top-6 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="bi bi-eye-fill text-indigo-500"></i> Live Certificate Studio
            </h3>
            <span class="text-xs bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-semibold px-2.5 py-1 rounded-full">
                Real-Time Preview
            </span>
        </div>

        {{-- Live Canvas Wrapper --}}
        <div id="certPreviewContainer" class="bg-slate-900/80 rounded-2xl p-4 shadow-xl border border-slate-700/60 overflow-hidden">
            <div id="liveCertCard" class="relative rounded-xl overflow-hidden shadow-2xl transition-all duration-300 p-6 flex flex-col justify-between text-center min-h-[380px]"
                 style="background: linear-gradient(135deg, #fffdfa 0%, #fef8ec 100%);">

                {{-- Decorative Border Frame --}}
                <div id="prevBorderFrame" class="absolute inset-3 border-4 border-amber-600 rounded-lg pointer-events-none transition-all"></div>
                <div id="prevInnerFrame" class="absolute inset-5 border border-amber-400/60 rounded-md pointer-events-none transition-all"></div>

                {{-- Header Block --}}
                <div class="relative z-10 space-y-2">
                    <div class="flex items-center justify-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-700 font-bold text-xs">
                            <i class="bi bi-award text-lg text-amber-600"></i>
                        </div>
                        <div>
                            <div id="prevSchoolName" class="text-xs font-black tracking-widest uppercase text-slate-800 font-serif">GENESIS GLOBAL SCHOOL</div>
                            <div id="prevSchoolAddr" class="text-[9px] font-semibold text-slate-500 uppercase tracking-wide">SIRAHA, NEPAL</div>
                        </div>
                    </div>

                    {{-- Title Badge --}}
                    <div class="pt-2">
                        <span id="prevHeaderTitle" id="prevTitleBadge"
                              class="inline-block px-4 py-1 rounded-md text-xs font-extrabold tracking-wider uppercase shadow-xs transition-colors"
                              style="background:#b45309; color:#ffffff;">
                            CERTIFICATE OF ACHIEVEMENT
                        </span>
                    </div>
                </div>

                {{-- Body Block --}}
                <div class="relative z-10 my-4 space-y-2 px-2">
                    <div class="text-[10px] text-slate-400 italic">PROUDLY PRESENTED TO</div>
                    <div id="prevStudentName" class="text-base font-black tracking-wide text-slate-900 font-serif" style="color:#b45309;">
                        AARAV SHRESTHA
                    </div>
                    <div class="w-24 h-[2px] mx-auto rounded-full" style="background:#b45309;"></div>
                    <div id="prevBodyText" class="text-[10.5px] leading-relaxed text-slate-700 font-serif max-h-[140px] overflow-hidden px-3">
                        This is to certify that Aarav Shrestha of Class 10, Roll No. 12, has demonstrated outstanding academic performance and exemplary conduct during the academic year 2081-82.
                    </div>
                </div>

                {{-- Bottom Signatures & Seal --}}
                <div class="relative z-10 flex items-end justify-between pt-2 border-t border-slate-200/80">
                    <div class="text-left">
                        <div id="prevCertNo" class="text-[9px] font-mono text-slate-500">NO: CERT-2082-0042</div>
                        <div id="prevDate" class="text-[9px] text-slate-500">Date: {{ date('d M Y') }}</div>
                    </div>

                    {{-- Gold Seal Badge --}}
                    <div class="w-11 h-11 rounded-full bg-gradient-to-br from-amber-400 via-amber-500 to-amber-700 text-white flex flex-col items-center justify-center p-1 shadow-md border-2 border-amber-300">
                        <i class="bi bi-patch-check-fill text-sm"></i>
                        <span class="text-[5px] font-black uppercase tracking-tighter">OFFICIAL SEAL</span>
                    </div>

                    <div class="text-right">
                        <div id="prevSigName" class="text-[10px] font-bold text-slate-800">Sunil Yadav</div>
                        <div id="prevSigTitle" class="text-[9px] text-slate-500 border-t border-slate-400 pt-0.5 mt-0.5">Principal</div>
                    </div>
                </div>

            </div>
        </div>

        <div class="bg-indigo-50/70 dark:bg-indigo-900/20 rounded-xl p-3.5 text-xs text-indigo-700 dark:text-indigo-300 flex items-start gap-2.5">
            <i class="bi bi-info-circle-fill text-base text-indigo-500 flex-shrink-0 mt-0.5"></i>
            <div>
                <strong>Pro Tip:</strong> Select preset color combinations or border styles to dynamically transform the visual theme. All text inputs automatically update this canvas in real time.
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function applyPresetColor(primary, secondary, borderStyle) {
    document.getElementById('input_primary_color').value = primary;
    document.getElementById('input_secondary_color').value = secondary;
    if (borderStyle) {
        document.getElementById('input_border_style').value = borderStyle;
    }
    updateLivePreview();
}

function updateLivePreview() {
    const primary   = document.getElementById('input_primary_color')?.value || '#b45309';
    const secondary = document.getElementById('input_secondary_color')?.value || '#d97706';
    const borderStyle = document.getElementById('input_border_style')?.value || 'classic';

    const headerText  = document.getElementById('input_header_text')?.value || 'CERTIFICATE OF ACHIEVEMENT';
    const bodyText    = document.getElementById('input_body_text')?.value || 'This is to certify that Aarav Shrestha of Class 10 has demonstrated outstanding performance.';
    const sigName     = document.getElementById('input_signature_name')?.value || 'Sunil Yadav';
    const sigTitle    = document.getElementById('input_signature_title')?.value || 'Principal';
    const showPhoto   = document.getElementById('input_show_student_photo')?.checked;
    const showQr      = document.getElementById('input_show_qr_code')?.checked;

    // Update Preview Elements
    const titleBadge = document.getElementById('prevHeaderTitle');
    if (titleBadge) {
        titleBadge.textContent = headerText.toUpperCase();
        titleBadge.style.backgroundColor = primary;
    }

    const studentName = document.getElementById('prevStudentName');
    if (studentName) {
        studentName.style.color = primary;
    }

    const bodyContainer = document.getElementById('prevBodyText');
    if (bodyContainer) {
        let cleanBody = bodyText
            .replace(/\{\{student_name\}\}/g, 'Aarav Shrestha')
            .replace(/\{\{class\}\}/g, '10')
            .replace(/\{\{section\}\}/g, 'A')
            .replace(/\{\{roll_no\}\}/g, '12')
            .replace(/\{\{school_name\}\}/g, 'Genesis Global School')
            .replace(/\{\{academic_year\}\}/g, '2081-82');
        bodyContainer.textContent = cleanBody;
    }

    const sigNameEl = document.getElementById('prevSigName');
    if (sigNameEl) sigNameEl.textContent = sigName;

    const sigTitleEl = document.getElementById('prevSigTitle');
    if (sigTitleEl) sigTitleEl.textContent = sigTitle;

    // Update Border Frame Styling
    const outerFrame = document.getElementById('prevBorderFrame');
    const innerFrame = document.getElementById('prevInnerFrame');

    if (outerFrame) {
        outerFrame.style.borderColor = primary;
        if (borderStyle === 'minimal') {
            outerFrame.style.borderWidth = '1px';
            if (innerFrame) innerFrame.style.display = 'none';
        } else if (borderStyle === 'modern') {
            outerFrame.style.borderWidth = '6px';
            outerFrame.style.borderRadius = '16px';
            if (innerFrame) innerFrame.style.display = 'none';
        } else if (borderStyle === 'ornate') {
            outerFrame.style.borderWidth = '4px';
            outerFrame.style.borderStyle = 'double';
            if (innerFrame) {
                innerFrame.style.display = 'block';
                innerFrame.style.borderColor = secondary;
            }
        } else {
            outerFrame.style.borderWidth = '4px';
            outerFrame.style.borderStyle = 'solid';
            if (innerFrame) {
                innerFrame.style.display = 'block';
                innerFrame.style.borderColor = secondary;
            }
        }
    }
}

function copyToken(token) {
    navigator.clipboard.writeText(token).then(() => {
        const tip = document.createElement('div');
        tip.textContent = 'Copied: ' + token;
        tip.className = 'fixed bottom-4 right-4 bg-slate-800 text-white text-xs px-3 py-2 rounded-lg shadow-lg z-50';
        document.body.appendChild(tip);
        setTimeout(() => tip.remove(), 1800);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    updateLivePreview();
});
</script>
@endpush

@push('scripts')
<script>
function copyToken(token) {
    navigator.clipboard.writeText(token).then(() => {
        // Flash feedback
        const tip = document.createElement('div');
        tip.textContent = 'Copied: ' + token;
        tip.className = 'fixed bottom-4 right-4 bg-slate-800 text-white text-xs px-3 py-2 rounded-lg shadow-lg z-50';
        document.body.appendChild(tip);
        setTimeout(() => tip.remove(), 1800);
    });
}
</script>
@endpush
