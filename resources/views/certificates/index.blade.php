@extends('layouts.app')
@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">

    {{-- ── Page Header ──────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Certificate Management</h1>
            <p class="text-slate-400 text-sm mt-0.5">Design templates, issue certificates, and manage the digital ledger</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('certificates.designs.index') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white text-sm font-semibold rounded-xl hover:from-purple-700 hover:to-indigo-700 shadow-sm transition">
                <i class="bi bi-palette-fill"></i> Design Gallery
            </a>
            <a href="{{ route('certificates.bulk') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-xl hover:bg-emerald-700 transition">
                <i class="bi bi-people-fill"></i> Bulk Issue
            </a>
            <a href="{{ route('certificates.issued') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-700 text-white text-sm font-medium rounded-xl hover:bg-slate-800 transition">
                <i class="bi bi-journal-text"></i> Issued Ledger
            </a>
            <a href="{{ route('certificates.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-xl hover:bg-indigo-700 transition">
                <i class="bi bi-plus-lg"></i> New Template
            </a>
        </div>
    </div>

    @if(session('status'))
    <div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700 flex items-center gap-2">
        <i class="bi bi-check-circle-fill"></i> {{ session('status') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-5 p-3 bg-rose-50 border border-rose-200 rounded-xl text-sm text-rose-700 flex items-center gap-2">
        <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
    </div>
    @endif

    {{-- ── Stats Row ────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach([
            ['label'=>'Templates',    'value'=>$stats['templates'],    'icon'=>'bi-file-earmark-richtext', 'color'=>'indigo'],
            ['label'=>'Total Issued', 'value'=>$stats['total_issued'], 'icon'=>'bi-award-fill',            'color'=>'emerald'],
            ['label'=>'This Month',   'value'=>$stats['this_month'],   'icon'=>'bi-calendar-check-fill',   'color'=>'sky'],
            ['label'=>'Revoked',      'value'=>$stats['revoked'],      'icon'=>'bi-x-circle-fill',         'color'=>'rose'],
        ] as $s)
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-xs p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-{{ $s['color'] }}-100 dark:bg-{{ $s['color'] }}-900/30 flex items-center justify-center text-{{ $s['color'] }}-600 dark:text-{{ $s['color'] }}-400 text-xl flex-shrink-0">
                <i class="bi {{ $s['icon'] }}"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ $s['value'] }}</p>
                <p class="text-xs text-slate-400">{{ $s['label'] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- ── Templates Grid ───────────────────────────────────────────── --}}
        <div class="xl:col-span-2">
            <h2 class="text-base font-semibold text-slate-700 dark:text-slate-300 mb-4 flex items-center gap-2">
                <i class="bi bi-grid-3x3-gap-fill text-indigo-500"></i> Templates
                <span class="text-xs font-normal text-slate-400">({{ $templates->count() }} total)</span>
            </h2>

            @if($templates->count())
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($templates as $t)
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-xs p-5 flex flex-col gap-3 hover:shadow-md transition">
                    {{-- Template header with color swatch --}}
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl flex-shrink-0 flex items-center justify-center"
                             style="background:{{ $t->primary_color }}20; color:{{ $t->primary_color }}">
                            <i class="bi bi-award-fill text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-slate-800 dark:text-slate-100 truncate">{{ $t->name }}</p>
                            <span class="text-xs bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 px-2 py-0.5 rounded-full">
                                {{ $t->typeLabel() }}
                            </span>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full flex-shrink-0 {{ $t->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $t->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    {{-- Meta badges --}}
                    <div class="flex flex-wrap gap-1.5">
                        <span class="text-xs bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 px-2 py-0.5 rounded-full">
                            <i class="bi bi-file-earmark me-1"></i>{{ $t->paper_size }} {{ ucfirst($t->orientation) }}
                        </span>
                        <span class="text-xs bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 px-2 py-0.5 rounded-full">
                            <i class="bi bi-translate me-1"></i>{{ $t->language === 'ne' ? 'Nepali' : 'English' }}
                        </span>
                        @if($t->show_qr_code)
                        <span class="text-xs bg-sky-100 dark:bg-sky-900/30 text-sky-700 dark:text-sky-300 px-2 py-0.5 rounded-full">
                            <i class="bi bi-qr-code me-1"></i>QR
                        </span>
                        @endif
                        @if($t->show_student_photo)
                        <span class="text-xs bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 px-2 py-0.5 rounded-full">
                            <i class="bi bi-person-bounding-box me-1"></i>Photo
                        </span>
                        @endif
                        @if($t->logo_path)
                        <span class="text-xs bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300 px-2 py-0.5 rounded-full">
                            <i class="bi bi-image me-1"></i>Logo
                        </span>
                        @endif
                        @if($t->background_image_path)
                        <span class="text-xs bg-fuchsia-100 dark:bg-fuchsia-900/30 text-fuchsia-700 dark:text-fuchsia-300 px-2 py-0.5 rounded-full">
                            <i class="bi bi-file-image me-1"></i>BG
                        </span>
                        @endif
                    </div>

                    @if($t->signature_name)
                    <p class="text-xs text-slate-400">
                        <i class="bi bi-pen me-1"></i>{{ $t->signature_name }}@if($t->signature_title), {{ $t->signature_title }}@endif
                    </p>
                    @endif

                    {{-- Actions --}}
                    {{-- Primary: Preview + Issue --}}
                    <div class="flex gap-2 mt-auto">
                        <a href="{{ route('certificates.builder', $t->id) }}"
                           class="flex-shrink-0 px-3 py-2 bg-fuchsia-50 dark:bg-fuchsia-900/20 hover:bg-fuchsia-100 text-fuchsia-700 dark:text-fuchsia-400 rounded-lg text-xs font-semibold transition flex items-center gap-1.5"
                           title="Visual Drag & Drop Builder">
                            <i class="bi bi-sliders"></i>
                        </a>
                        <a href="{{ route('certificates.preview', $t->id) }}" target="_blank"
                           class="flex-1 text-center py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold transition flex items-center justify-center gap-1.5">
                            <i class="bi bi-eye-fill"></i> Preview
                        </a>
                        <a href="{{ route('certificates.bulk') }}?template_id={{ $t->id }}"
                           class="flex-1 text-center py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold transition flex items-center justify-center gap-1.5">
                            <i class="bi bi-award-fill"></i> Issue
                        </a>
                        <a href="{{ route('certificates.bulk') }}?template_id={{ $t->id }}"
                           class="px-3 py-2 bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 text-emerald-700 dark:text-emerald-400 rounded-lg text-xs font-medium transition flex items-center gap-1.5"
                           title="Bulk issue to a class">
                            <i class="bi bi-people-fill"></i>
                        </a>
                    </div>
                    {{-- Secondary: Edit + History + Delete --}}
                    <div class="flex gap-2 pt-1 border-t border-slate-50 dark:border-slate-700">
                        <a href="{{ route('certificates.edit', $t->id) }}"
                           class="flex-1 text-center py-1.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-medium transition">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </a>
                        <a href="{{ route('certificates.issued') }}?type={{ $t->type }}"
                           class="flex-1 text-center py-1.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-medium transition">
                            <i class="bi bi-journal-text me-1"></i>History
                        </a>
                        <form method="POST" action="{{ route('certificates.destroy', $t->id) }}">
                            @csrf @method('DELETE')
                            <button onclick="return confirm('Delete template \'{{ addslashes($t->name) }}\'? This cannot be undone.')"
                                    class="px-3 py-1.5 bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-100 text-rose-700 dark:text-rose-400 rounded-lg text-xs font-medium transition">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-xs p-12 text-center">
                <i class="bi bi-file-earmark-text text-5xl text-slate-200 dark:text-slate-600"></i>
                <p class="mt-3 text-slate-400 text-sm">No templates yet.</p>
                <a href="{{ route('certificates.create') }}"
                   class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                    <i class="bi bi-plus-lg"></i> Create Your First Template
                </a>
            </div>
            @endif
        </div>

        {{-- ── Recent Issued Sidebar ─────────────────────────────────────── --}}
        <div>
            <h2 class="text-base font-semibold text-slate-700 dark:text-slate-300 mb-4 flex items-center gap-2">
                <i class="bi bi-clock-history text-emerald-500"></i> Recently Issued
            </h2>

            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-xs overflow-hidden">
                @forelse($recent as $rec)
                <div class="flex items-center gap-3 p-4 {{ !$loop->last ? 'border-b border-slate-50 dark:border-slate-700' : '' }} hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 flex-shrink-0 font-bold text-xs">
                        {{ strtoupper(substr($rec->student->first_name ?? '?', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-200 truncate">
                            {{ $rec->student->full_name ?? '—' }}
                        </p>
                        <p class="text-xs text-slate-400 truncate">{{ $rec->template->name ?? $rec->typeLabel() }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <span class="text-xs {{ $rec->statusBadgeClass() }} px-2 py-0.5 rounded-full block mb-0.5">
                            {{ ucfirst($rec->status) }}
                        </span>
                        <p class="text-xs text-slate-400">{{ $rec->issued_date->format('d M Y') }}</p>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-slate-400 text-sm">
                    <i class="bi bi-inbox text-2xl block mb-2"></i> No certificates issued yet.
                </div>
                @endforelse

                @if($recent->count())
                <div class="p-3 bg-slate-50 dark:bg-slate-700/30 text-center">
                    <a href="{{ route('certificates.issued') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                        View full ledger →
                    </a>
                </div>
                @endif
            </div>

            {{-- Quick-link token reference --}}
            <div class="mt-5 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl p-4">
                <p class="text-xs font-semibold text-blue-700 dark:text-blue-300 mb-2">
                    <i class="bi bi-braces me-1"></i>Available Template Tokens
                </p>
                <div class="flex flex-wrap gap-1.5">
                    @php
                    $indexTokens = [
                        '{{student_name}}','{{class}}','{{section}}','{{roll_no}}',
                        '{{admission_no}}','{{date}}','{{academic_year}}','{{father_name}}',
                        '{{school_name}}','{{issued_by}}','{{extra_notes}}','{{purpose}}',
                        '{{birthday}}','{{age}}','{{board_reg_no}}',
                    ];
                    @endphp
                    @foreach($indexTokens as $tok)
                    <code class="text-xs bg-blue-100 dark:bg-blue-800/50 text-blue-800 dark:text-blue-200 px-1.5 py-0.5 rounded font-mono">{{ $tok }}</code>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
