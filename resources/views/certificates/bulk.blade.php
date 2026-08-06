@extends('layouts.app')
@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">

    <nav class="text-xs text-slate-400 mb-5 flex items-center gap-1">
        <a href="{{ route('certificates.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Certificates</a>
        <i class="bi bi-chevron-right text-slate-300 text-xs"></i>
        <span class="text-slate-500 dark:text-slate-300">Bulk Issue</span>
    </nav>

    <div class="flex items-center gap-3 mb-7">
        <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
            <i class="bi bi-people-fill text-lg"></i>
        </div>
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Bulk Issue Certificates</h1>
            <p class="text-slate-400 text-sm">Generate certificates for an entire class in one PDF</p>
        </div>
    </div>

    @if(session('error'))
    <div class="mb-5 p-3 bg-rose-50 border border-rose-200 rounded-xl text-sm text-rose-700 flex items-center gap-2">
        <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
    </div>
    @endif
    @if($errors->any())
    <div class="mb-5 p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 rounded-xl text-sm text-rose-700 dark:text-rose-300">
        @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
    </div>
    @endif

    <div class="max-w-2xl">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-xs p-6 space-y-5">

            <form method="POST" action="{{ route('certificates.bulk.issue') }}">
                @csrf

                {{-- Template --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                        Certificate Template <span class="text-rose-400">*</span>
                    </label>
                    @if($templates->isEmpty())
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-700">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        No active templates.
                        <a href="{{ route('certificates.create') }}" class="underline font-medium">Create one first →</a>
                    </div>
                    @else
                    <select name="template_id" required id="bulkTemplateSelect"
                        class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">— Select template —</option>
                        @foreach($templates as $tpl)
                        <option value="{{ $tpl->id }}"
                            data-paper="{{ $tpl->paper_size }}"
                            data-lang="{{ $tpl->language }}"
                            {{ (old('template_id') == $tpl->id || request('template_id') == $tpl->id) ? 'selected' : '' }}>
                            {{ $tpl->name }} ({{ $tpl->typeLabel() }})
                        </option>
                        @endforeach
                    </select>
                    @endif
                </div>

                {{-- Class --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                        Class <span class="text-rose-400">*</span>
                    </label>
                    <select name="class_id" required id="classSelect"
                        class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">— Select class —</option>
                        @foreach($classes as $cls)
                        <option value="{{ $cls->id }}" {{ old('class_id') == $cls->id ? 'selected' : '' }}>
                            {{ $cls->class_name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Section (dynamic — loaded via AJAX) --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                        Section <span class="text-slate-400 font-normal">(optional — leave blank for whole class)</span>
                    </label>
                    <select name="section_id" id="sectionSelect"
                        class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">All Sections</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    {{-- Date --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Issue Date <span class="text-rose-400">*</span></label>
                        <input type="date" name="issued_date" value="{{ old('issued_date', now()->toDateString()) }}" required
                            class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>

                    {{-- Language --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Language</label>
                        <select name="language" id="bulkLangSelect"
                            class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="en" {{ old('language','en') === 'en' ? 'selected' : '' }}>English</option>
                            <option value="ne" {{ old('language','en') === 'ne' ? 'selected' : '' }}>Nepali (नेपाली)</option>
                        </select>
                    </div>

                    {{-- Paper size --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Paper Size</label>
                        <select name="paper_size" id="bulkPaperSelect"
                            class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="A4"     {{ old('paper_size','A4') === 'A4' ? 'selected' : '' }}>A4</option>
                            <option value="Letter" {{ old('paper_size','A4') === 'Letter' ? 'selected' : '' }}>Letter</option>
                        </select>
                    </div>
                </div>

                {{-- Extra notes --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                        Extra Notes <span class="text-slate-400 font-normal">(replaces <code class="bg-slate-100 dark:bg-slate-600 px-1 rounded">@{{extra_notes}}</code> token for all)</span>
                    </label>
                    <textarea name="extra_notes" rows="3"
                        class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 resize-y focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('extra_notes') }}</textarea>
                </div>

                {{-- Warning --}}
                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl p-3 text-xs text-amber-700 dark:text-amber-300 flex items-start gap-2">
                    <i class="bi bi-info-circle-fill mt-0.5 flex-shrink-0"></i>
                    <span>This will create individual certificate records for every student in the selected class/section and generate a single downloadable PDF. Large classes may take a moment to process.</span>
                </div>

                <button type="submit" @if($templates->isEmpty() || $classes->isEmpty()) disabled @endif
                    class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg text-sm font-semibold transition shadow-sm flex items-center justify-center gap-2">
                    <i class="bi bi-file-earmark-pdf-fill text-base"></i>
                    Generate Bulk Certificate PDF
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Sync defaults from template selection
document.getElementById('bulkTemplateSelect')?.addEventListener('change', function () {
    const opt  = this.options[this.selectedIndex];
    const ps   = document.getElementById('bulkPaperSelect');
    const ls   = document.getElementById('bulkLangSelect');
    if (opt.dataset.paper) {
        for (let i = 0; i < ps.options.length; i++) {
            if (ps.options[i].value === opt.dataset.paper) ps.selectedIndex = i;
        }
    }
    if (opt.dataset.lang) {
        for (let i = 0; i < ls.options.length; i++) {
            if (ls.options[i].value === opt.dataset.lang) ls.selectedIndex = i;
        }
    }
});

// Load sections dynamically when class changes
document.getElementById('classSelect')?.addEventListener('change', function () {
    const classId = this.value;
    const sel     = document.getElementById('sectionSelect');
    sel.innerHTML = '<option value="">All Sections</option>';

    if (!classId) return;

    fetch(`/api/sections?class_id=${classId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        (data.sections || data).forEach(s => {
            const o = document.createElement('option');
            o.value       = s.id;
            o.textContent = s.section_name ?? s.name;
            sel.appendChild(o);
        });
    })
    .catch(() => {}); // silently fail — "All Sections" still works
});
</script>
@endpush
@endsection
