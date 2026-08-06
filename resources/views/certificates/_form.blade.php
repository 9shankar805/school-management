{{-- Shared form partial for create + edit --}}
{{-- Expects: $template (existing or null), $typeGroups, $types --}}
@php $t = $template ?? null; @endphp

<div class="space-y-6">

    {{-- ── Basic Info ────────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-xs p-6">
        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
            <i class="bi bi-info-circle text-indigo-500"></i> Basic Information
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- Name --}}
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                    Template Name <span class="text-rose-400">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name', $t->name ?? '') }}" required
                    placeholder="e.g. Standard Character Certificate"
                    class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>

            {{-- Type (grouped select) --}}
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                    Certificate Type <span class="text-rose-400">*</span>
                </label>
                <select name="type" required
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
                <select name="language" required
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

    {{-- ── Page Layout ────────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-xs p-6">
        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
            <i class="bi bi-layout-text-window text-indigo-500"></i> Page Layout
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- Paper size --}}
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Paper Size</label>
                <select name="paper_size"
                    class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="A4"     {{ old('paper_size', $t->paper_size ?? 'A4') === 'A4' ? 'selected' : '' }}>A4</option>
                    <option value="Letter" {{ old('paper_size', $t->paper_size ?? 'A4') === 'Letter' ? 'selected' : '' }}>Letter</option>
                </select>
            </div>

            {{-- Orientation --}}
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Orientation</label>
                <select name="orientation"
                    class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="landscape" {{ old('orientation', $t->orientation ?? 'landscape') === 'landscape' ? 'selected' : '' }}>Landscape</option>
                    <option value="portrait"  {{ old('orientation', $t->orientation ?? 'landscape') === 'portrait'  ? 'selected' : '' }}>Portrait</option>
                </select>
            </div>

            {{-- Border style --}}
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Border Style</label>
                <select name="border_style"
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
                        <input type="color" name="primary_color" value="{{ old('primary_color', $t->primary_color ?? '#4f46e5') }}"
                            title="Primary color"
                            class="w-full h-9 rounded-lg border border-slate-200 dark:border-slate-600 cursor-pointer">
                        <p class="text-xs text-slate-400 mt-0.5 text-center">Primary</p>
                    </div>
                    <div class="flex-1">
                        <input type="color" name="secondary_color" value="{{ old('secondary_color', $t->secondary_color ?? '#7c3aed') }}"
                            title="Secondary color"
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
                <input type="checkbox" name="{{ $toggle['name'] }}" value="1"
                    {{ old($toggle['name'], $t?->{$toggle['name']} ?? $toggle['default']) ? 'checked' : '' }}
                    class="w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
                <span class="text-sm text-slate-700 dark:text-slate-200">{{ $toggle['label'] }}</span>
            </label>
            @endforeach
        </div>
    </div>

    {{-- ── Content ─────────────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-xs p-6">
        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
            <i class="bi bi-file-text text-indigo-500"></i> Certificate Content
        </h3>
        <div class="space-y-4">

            {{-- Header text --}}
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Header / Title Text</label>
                <input type="text" name="header_text" value="{{ old('header_text', $t->header_text ?? '') }}"
                    placeholder="e.g. Certificate of Good Conduct"
                    class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>

            {{-- Body text (English) --}}
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                    Body Text (English) <span class="text-rose-400">*</span>
                </label>
                <textarea name="body_text" rows="7" required
                    placeholder="This is to certify that @{{student_name}} of Class @{{class}}, Section @{{section}}, Roll No. @{{roll_no}}, is a bonafide student of @{{school_name}} for the academic year @{{academic_year}}."
                    class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 resize-y focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('body_text', $t->body_text ?? '') }}</textarea>
            </div>

            {{-- Body text (Nepali) --}}
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                    Body Text (Nepali — नेपाली)
                    <span class="text-slate-400 font-normal">(optional — used when language is set to Nepali)</span>
                </label>
                <textarea name="body_text_ne" rows="5"
                    placeholder="यो प्रमाणित गरिन्छ कि @{{student_name}} यस @{{school_name}} का विद्यार्थी हुन् ..."
                    class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 resize-y focus:outline-none focus:ring-2 focus:ring-indigo-400 font-sans">{{ old('body_text_ne', $t->body_text_ne ?? '') }}</textarea>
            </div>

            {{-- Footer text --}}
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Footer Text</label>
                <input type="text" name="footer_text" value="{{ old('footer_text', $t->footer_text ?? '') }}"
                    placeholder="e.g. This certificate is issued upon request."
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
                        '{{extra_notes}}','{{purpose}}',
                    ];
                    @endphp
                    @foreach($availableTokens as $tok)
                    <button type="button" onclick="copyToken('{{ $tok }}')"
                        class="bg-slate-200 dark:bg-slate-600 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-300 px-2 py-0.5 rounded font-mono cursor-pointer transition">
                        {{ $tok }}
                    </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ── Signature & Authority ───────────────────────────────────────── --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-xs p-6">
        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
            <i class="bi bi-pen text-indigo-500"></i> Signatory & Authority
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Signatory Name</label>
                <input type="text" name="signature_name" value="{{ old('signature_name', $t->signature_name ?? '') }}"
                    placeholder="e.g. Ram Bahadur Shrestha"
                    class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Signatory Title</label>
                <input type="text" name="signature_title" value="{{ old('signature_title', $t->signature_title ?? '') }}"
                    placeholder="e.g. Principal"
                    class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
        </div>
    </div>

    {{-- ── Branding Uploads ────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-xs p-6">
        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
            <i class="bi bi-image text-indigo-500"></i> Branding
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

        {{-- Background Image --}}
        <div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-700">
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-2 flex items-center gap-1.5">
                <i class="bi bi-file-image text-violet-500"></i>
                Certificate Background Image
                <span class="text-slate-400 font-normal ml-1">(PNG/JPG, max 5MB — recommended A4/Letter sized scan or higher resolution)</span>
            </label>
            @if($t && $t->background_image_path)
            <div class="mb-3 p-3 bg-slate-50 dark:bg-slate-700 rounded-lg">
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                    <div class="flex-shrink-0 w-full sm:w-auto">
                        <img src="{{ Storage::disk('public')->url($t->background_image_path) }}"
                             alt="Background preview"
                             class="w-full sm:w-56 h-auto rounded border border-slate-200 dark:border-slate-600 shadow-sm bg-white">
                    </div>
                    <div class="flex-1 space-y-1.5">
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Current background. All text (header, body, signature, QR code, etc.) will be rendered <strong class="text-slate-700 dark:text-slate-200">on top</strong> of this image.
                        </p>
                        <label class="inline-flex items-center gap-1.5 text-xs text-rose-600 cursor-pointer">
                            <input type="checkbox" name="remove_background_image" value="1" class="w-3 h-3 rounded text-rose-500">
                            Remove background image
                        </label>
                    </div>
                </div>
            </div>
            @else
            <div class="mb-2 p-3 bg-indigo-50/60 dark:bg-indigo-900/20 rounded-lg text-xs text-indigo-700 dark:text-indigo-300 space-y-1">
                <p><i class="bi bi-info-circle mr-1"></i> Upload a decorative certificate border/frame template (e.g. gold foil border, ornate frame, custom school design). Set <strong>Border Style = Minimal</strong> in Page Layout to avoid double borders.</p>
            </div>
            @endif
            <input type="file" name="background_image" accept="image/png,image/jpeg"
                class="w-full text-sm text-slate-600 dark:text-slate-300 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100 dark:file:bg-violet-900/30 dark:file:text-violet-300 cursor-pointer">
        </div>

        {{-- Verification URL --}}
        <div class="mt-4">
            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                Custom Verification URL
                <span class="text-slate-400 font-normal">(overrides the auto-generated QR URL)</span>
            </label>
            <input type="url" name="verification_url" value="{{ old('verification_url', $t->verification_url ?? '') }}"
                placeholder="https://yourschool.edu.np/verify/..."
                class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>
    </div>

</div>

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
