<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Template Name <span class="text-rose-400">*</span></label>
            <input type="text" name="name" value="{{ old('name', $template->name ?? '') }}" required
                class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Certificate Type <span class="text-rose-400">*</span></label>
            <select name="type" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                @foreach(\App\Models\CertificateTemplate::TYPES as $v => $l)
                <option value="{{ $v }}" {{ old('type', $template->type ?? '') === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="block text-xs font-medium text-slate-500 mb-1">Header Text</label>
            <input type="text" name="header_text" value="{{ old('header_text', $template->header_text ?? '') }}"
                placeholder="e.g. Certificate of Achievement"
                class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>
        <div class="md:col-span-2">
            <label class="block text-xs font-medium text-slate-500 mb-1">Body Text <span class="text-rose-400">*</span></label>
            <textarea name="body_text" rows="6" required
                placeholder="This is to certify that {{student_name}} of Class {{class}}, Section {{section}} has successfully completed..."
                class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm resize-y focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('body_text', $template->body_text ?? '') }}</textarea>
            <p class="text-xs text-slate-400 mt-1">Use tokens like <code class="bg-slate-100 px-1 rounded">{{student_name}}</code>, <code class="bg-slate-100 px-1 rounded">{{class}}</code>, <code class="bg-slate-100 px-1 rounded">{{date}}</code></p>
        </div>
        <div class="md:col-span-2">
            <label class="block text-xs font-medium text-slate-500 mb-1">Footer Text</label>
            <input type="text" name="footer_text" value="{{ old('footer_text', $template->footer_text ?? '') }}"
                placeholder="e.g. Awarded with distinction"
                class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Signatory Name</label>
            <input type="text" name="signature_name" value="{{ old('signature_name', $template->signature_name ?? '') }}"
                class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Signatory Title</label>
            <input type="text" name="signature_title" value="{{ old('signature_title', $template->signature_title ?? '') }}"
                placeholder="e.g. Principal"
                class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>
    </div>
</div>
