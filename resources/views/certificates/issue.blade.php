@extends('layouts.app')
@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">

    {{-- Breadcrumb --}}
    <nav class="text-xs text-slate-400 mb-5 flex items-center gap-1">
        <a href="{{ route('student.list.show') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Students</a>
        <i class="bi bi-chevron-right text-slate-300 text-xs"></i>
        <a href="{{ url('students/'.$student->id) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">{{ $student->full_name }}</a>
        <i class="bi bi-chevron-right text-slate-300 text-xs"></i>
        <span class="text-slate-500 dark:text-slate-300">Issue Certificate</span>
    </nav>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- ── Left: Issue Form ──────────────────────────────────────── --}}
        <div class="xl:col-span-2 space-y-5">

            {{-- Student card --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-xs p-5 flex items-center gap-4">
                @if($student->photo)
                <img src="{{ asset('storage/'.$student->photo) }}" alt="{{ $student->full_name }}"
                     class="w-14 h-14 rounded-full object-cover border-2 border-indigo-200 flex-shrink-0">
                @else
                <div class="w-14 h-14 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-xl font-bold flex-shrink-0">
                    {{ strtoupper(substr($student->first_name, 0, 1)) }}
                </div>
                @endif
                <div>
                    <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $student->full_name }}</h2>
                    @php $latestPromotion = $student->promotions->sortByDesc('id')->first(); @endphp
                    <p class="text-sm text-slate-400">
                        {{ $latestPromotion?->schoolClass?->class_name ?? '—' }}
                        @if($latestPromotion?->section?->section_name) &middot; {{ $latestPromotion->section->section_name }} @endif
                        @if($student->academic_info?->admission_no) &middot; Adm# {{ $student->academic_info->admission_no }} @endif
                    </p>
                </div>
            </div>

            @if(session('status'))
            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700 flex items-center gap-2">
                <i class="bi bi-check-circle-fill"></i> {{ session('status') }}
            </div>
            @endif

            @if($errors->any())
            <div class="p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 rounded-xl text-sm text-rose-700 dark:text-rose-300">
                @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
            </div>
            @endif

            {{-- Issue form --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-xs p-6">
                <h3 class="text-base font-semibold text-slate-700 dark:text-slate-200 mb-5 flex items-center gap-2">
                    <i class="bi bi-award text-indigo-500"></i> Issue New Certificate
                </h3>

                <form method="POST" action="{{ route('certificates.issue', $student->id) }}" class="space-y-4">
                    @csrf

                    {{-- Template select --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                            Certificate Template <span class="text-rose-400">*</span>
                        </label>
                        @if($templates->isEmpty())
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-700">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            No active templates found.
                            <a href="{{ route('certificates.create') }}" class="underline font-medium">Create one first →</a>
                        </div>
                        @else
                        <select name="template_id" required id="templateSelect"
                            class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">— Select template —</option>
                            @foreach($templates as $tpl)
                            <option value="{{ $tpl->id }}"
                                data-paper="{{ $tpl->paper_size }}"
                                data-lang="{{ $tpl->language }}"
                                data-editable="{{ json_encode($tpl->editable_fields ?? []) }}"
                                data-labels="{{ json_encode(\App\Models\CertificateTemplate::DYNAMIC_FIELDS()) }}"
                                {{ old('template_id') == $tpl->id ? 'selected' : '' }}>
                                {{ $tpl->name }} ({{ $tpl->typeLabel() }})
                            </option>
                            @endforeach
                        </select>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        {{-- Issued date --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                                Issue Date <span class="text-rose-400">*</span>
                            </label>
                            <input type="date" name="issued_date" value="{{ old('issued_date', now()->toDateString()) }}" required
                                class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>

                        {{-- Valid until --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                                Valid Until <span class="text-slate-400 font-normal">(optional)</span>
                            </label>
                            <input type="date" name="valid_until" value="{{ old('valid_until') }}"
                                class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>

                        {{-- Language override --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Language</label>
                            <select name="language" id="langSelect"
                                class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                <option value="en" {{ old('language','en') === 'en' ? 'selected' : '' }}>English</option>
                                <option value="ne" {{ old('language','en') === 'ne' ? 'selected' : '' }}>Nepali (नेपाली)</option>
                            </select>
                        </div>

                        {{-- Paper size override --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Paper Size</label>
                            <select name="paper_size" id="paperSelect"
                                class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                <option value="A4"     {{ old('paper_size','A4') === 'A4' ? 'selected' : '' }}>A4</option>
                                <option value="Letter" {{ old('paper_size','A4') === 'Letter' ? 'selected' : '' }}>Letter</option>
                            </select>
                        </div>
                    </div>

                    {{-- Purpose --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                            Purpose <span class="text-slate-400 font-normal">(optional — e.g. Bank, Visa, Scholarship)</span>
                        </label>
                        <input type="text" name="purpose" value="{{ old('purpose') }}"
                            placeholder="e.g. Bank loan application"
                            class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>

                    {{-- Extra notes --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">
                            Extra Notes / Remarks
                            <span class="text-slate-400 font-normal">(replaces <code class="bg-slate-100 dark:bg-slate-600 px-1 rounded">@{{extra_notes}}</code> token)</span>
                        </label>
                        <textarea name="extra_notes" rows="3"
                            placeholder="Any additional information to include in the certificate..."
                            class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 resize-y focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('extra_notes') }}</textarea>
                    </div>

                    {{-- Editable fields (populated by JS based on selected template) --}}
                    <div id="editableFields" class="hidden">
                        <div class="border-t border-dashed border-slate-200 dark:border-slate-700 pt-4">
                            <h4 class="text-xs font-semibold text-fuchsia-700 dark:text-fuchsia-400 mb-3 flex items-center gap-1.5 uppercase tracking-wide">
                                <i class="bi bi-pencil-square"></i> Editable Fields
                                <span class="text-slate-400 font-normal normal-case tracking-normal">(customised each issuance)</span>
                            </h4>
                            <div id="editableFieldsInner" class="space-y-3 grid grid-cols-1 md:grid-cols-2 gap-x-4"></div>
                        </div>
                    </div>

                    <button type="submit" @if($templates->isEmpty()) disabled @endif
                        class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg text-sm font-medium transition shadow-sm flex items-center justify-center gap-2">
                        <i class="bi bi-download"></i> Issue & Download PDF
                    </button>
                </form>
            </div>
        </div>

        {{-- ── Right: Previously Issued ─────────────────────────────── --}}
        <div>
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
                <i class="bi bi-clock-history text-emerald-500"></i> Previously Issued ({{ $issued->count() }})
            </h3>
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-xs overflow-hidden">
                @forelse($issued as $rec)
                <div class="p-4 {{ !$loop->last ? 'border-b border-slate-50 dark:border-slate-700' : '' }}">
                    <div class="flex items-start justify-between gap-2 mb-1">
                        <div>
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $rec->template->name ?? $rec->typeLabel() }}</p>
                            <p class="text-xs text-slate-400 font-mono">{{ $rec->certificate_number }}</p>
                        </div>
                        <span class="text-xs {{ $rec->statusBadgeClass() }} px-2 py-0.5 rounded-full flex-shrink-0">
                            {{ ucfirst($rec->status) }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mb-2">
                        <i class="bi bi-calendar3 me-1"></i>{{ $rec->issued_date->format('d M Y') }}
                        @if($rec->purpose) &middot; {{ $rec->purpose }}@endif
                    </p>
                    <div class="flex gap-2">
                        @if($rec->status === 'issued')
                        <a href="{{ route('certificates.download', $rec->id) }}" target="_blank"
                           class="flex-1 text-center py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-medium transition flex items-center justify-center gap-1.5">
                            <i class="bi bi-printer-fill"></i> View & Print
                        </a>
                        <form method="POST" action="{{ route('certificates.revoke', $rec->id) }}" class="flex-shrink-0">
                            @csrf
                            <button onclick="return confirm('Revoke this certificate? This cannot be undone.')"
                                    class="px-3 py-1.5 bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-100 text-rose-700 dark:text-rose-400 rounded-lg text-xs font-medium transition">
                                <i class="bi bi-x-circle"></i> Revoke
                            </button>
                        </form>
                        @else
                        <span class="text-xs text-slate-400 italic px-1">{{ ucfirst($rec->status) }}</span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="p-8 text-center">
                    <i class="bi bi-award text-3xl text-slate-200 dark:text-slate-600 block mb-2"></i>
                    <p class="text-slate-400 text-sm">No certificates issued to this student yet.</p>
                </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
(function() {
    const sel  = document.getElementById('templateSelect');
    const ls   = document.getElementById('langSelect');
    const ps   = document.getElementById('paperSelect');
    const wrap = document.getElementById('editableFields');
    const inner= document.getElementById('editableFieldsInner');

    function syncTemplate() {
        const opt = sel.options[sel.selectedIndex];
        if (!opt || !opt.value) { wrap?.classList.add('hidden'); return; }
        if (opt.dataset.paper && ps) {
            for (let i = 0; i < ps.options.length; i++)
                if (ps.options[i].value === opt.dataset.paper) ps.selectedIndex = i;
        }
        if (opt.dataset.lang && ls) {
            for (let i = 0; i < ls.options.length; i++)
                if (ls.options[i].value === opt.dataset.lang) ls.selectedIndex = i;
        }
        // Editable fields
        let fields = [], labels = {};
        try { fields = JSON.parse(opt.dataset.editable || '[]'); } catch(e){}
        try { labels = JSON.parse(opt.dataset.labels   || '{}'); } catch(e){}
        if (!wrap) return;
        inner.innerHTML = '';
        if (!fields.length) { wrap.classList.add('hidden'); return; }
        wrap.classList.remove('hidden');
        fields.forEach(key => {
            const label = labels[key] || key;
            const div = document.createElement('div');
            div.innerHTML =
                '<label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">' + label + '</label>' +
                '<input type="text" name="field_overrides[' + key + ']" ' +
                       'class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-fuchsia-400">';
            inner.appendChild(div);
        });
    }
    sel?.addEventListener('change', syncTemplate);
    // Fire once if there's an initial selection (e.g. old input)
    if (sel && sel.value) syncTemplate();
})();
</script>
@endpush
@endsection
