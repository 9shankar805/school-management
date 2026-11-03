@extends('layouts.app')
@section('title', 'Google Education Workspace - ' . $school->name)

@php
$activeTab = 'google';
@endphp

@section('breadcrumb')
<x-breadcrumb :items="[
    ['label'=>'Schools','url'=>route('super.schools.index')],
    ['label'=>$school->name,'url'=>route('super.schools.show', $school)],
    ['label'=>'Google Education']
]"/>
@endsection

@section('content')
<div class="page-wrapper">

    {{-- School Header --}}
    <div class="page-header">
        <div class="flex items-center gap-4">
            <img src="{{ $school->logo_url }}" class="w-14 h-14 rounded-2xl object-cover border" style="border-color:var(--border-card)" alt="">
            <div>
                <h1 class="page-title">{{ $school->name }}</h1>
                <div class="flex items-center gap-2 mt-1 flex-wrap">
                    <span class="badge-{{ match($school->status_color){ 'emerald'=>'success','rose'=>'danger','amber'=>'warning',default=>'neutral' } }}">
                        {{ ucfirst($school->status) }}
                    </span>
                    <span class="badge-primary text-[10px]">{{ $school->plan_label }} Plan</span>
                    <span class="text-xs text-slate-400 font-mono">{{ $school->slug }}.nepalschoolhub.com</span>
                </div>
            </div>
        </div>
        <div class="page-actions flex-wrap">
            <a href="{{ route('super.schools.show', $school) }}" class="btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to School Overview
            </a>
        </div>
    </div>

    @include('session-messages')

    {{-- Tabs Bar --}}
    <div class="card-modern mb-6 !p-0">
        <div class="flex border-b text-sm font-medium" style="border-color:var(--border-card)">
            <a href="{{ route('super.schools.show', $school) }}"
               class="flex items-center gap-2 px-5 py-3 border-b-2 transition border-transparent hover:text-indigo-500"
               style="color:var(--text-muted)">
                <i class="bi bi-building"></i> Overview
            </a>
            <a href="{{ route('super.schools.committee', $school) }}"
               class="flex items-center gap-2 px-5 py-3 border-b-2 transition border-transparent hover:text-indigo-500"
               style="color:var(--text-muted)">
                <i class="bi bi-diagram-3"></i> Governance (SMC)
            </a>
            <a href="{{ route('super.schools.google', $school) }}"
               class="flex items-center gap-2 px-5 py-3 border-b-2 transition border-indigo-500 text-indigo-600 font-bold">
                <i class="bi bi-google text-indigo-500"></i> Google Education Workspace
                @if($config->connection_status === 'connected')
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                @endif
            </a>
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- Left Column: Configuration & Credentials (5 cols) --}}
        <div class="lg:col-span-5 space-y-6">
            <div class="card-modern">
                <div class="card-header-modern flex items-center justify-between">
                    <span class="card-title-modern flex items-center gap-2">
                        <i class="bi bi-shield-lock-fill text-indigo-500"></i> Google Workspace Credentials
                    </span>
                    @if($config->connection_status === 'connected')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                            <i class="bi bi-check-circle-fill"></i> Connected
                        </span>
                    @elseif($config->connection_status === 'error')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300">
                            <i class="bi bi-x-circle-fill"></i> Connection Error
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                            <i class="bi bi-clock-history"></i> Untested
                        </span>
                    @endif
                </div>

                <form method="POST" action="{{ route('super.schools.google.config', $school) }}" enctype="multipart/form-data" class="card-body-modern space-y-4">
                    @csrf

                    <div>
                        <label class="label-modern text-xs font-semibold">School Workspace Domain <span class="text-rose-500">*</span></label>
                        <input type="text" name="workspace_domain" value="{{ old('workspace_domain', $config->workspace_domain) }}" required
                               placeholder="e.g. prithvi.edu.np" class="input-modern font-mono">
                        <p class="text-[11px] text-slate-400 mt-1">The primary domain registered in Google Workspace for Education.</p>
                    </div>

                    <div>
                        <label class="label-modern text-xs font-semibold">Super Admin Account Email <span class="text-rose-500">*</span></label>
                        <input type="email" name="admin_email" value="{{ old('admin_email', $config->admin_email) }}" required
                               placeholder="e.g. principal@prithvi.edu.np" class="input-modern font-mono">
                        <p class="text-[11px] text-slate-400 mt-1">The Google Workspace admin account used for domain-wide delegation.</p>
                    </div>

                    <div>
                        <label class="label-modern text-xs font-semibold">
                            Upload Service Account Key (credentials.json)
                            @if($config->hasValidCredentials())
                                <span class="text-emerald-600 font-normal">(Key already configured)</span>
                            @endif
                        </label>
                        <input type="file" name="credentials_file" accept=".json,application/json" class="input-modern text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-indigo-50 file:text-indigo-600 file:font-semibold">
                        <p class="text-[11px] text-slate-400 mt-1">Google Cloud service account JSON key with domain-wide delegation enabled.</p>
                    </div>

                    <div class="border-t pt-3 space-y-2.5" style="border-color:var(--border-card)">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 block mb-1">Feature Toggles</span>
                        <label class="flex items-center gap-2 cursor-pointer text-xs">
                            <input type="checkbox" name="sync_classroom" value="1" {{ $config->sync_classroom ? 'checked' : '' }} class="rounded text-indigo-600">
                            <span>Google Classroom Courses &amp; Rosters Sync</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs">
                            <input type="checkbox" name="sync_directory_users" value="1" {{ $config->sync_directory_users ? 'checked' : '' }} class="rounded text-indigo-600">
                            <span>Google Directory API Account Provisioning</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs">
                            <input type="checkbox" name="is_active" value="1" {{ $config->is_active ? 'checked' : '' }} class="rounded text-indigo-600">
                            <span class="font-bold text-indigo-600">Plugin Active for this School</span>
                        </label>
                    </div>

                    <div class="pt-2 flex items-center gap-2">
                        <button type="submit" class="btn-primary flex-1 justify-center">
                            <i class="bi bi-save"></i> Save Configuration
                        </button>
                    </div>
                </form>

                {{-- Test Connection Button --}}
                @if($config->hasValidCredentials())
                <div class="p-4 border-t bg-slate-50 dark:bg-slate-800/40" style="border-color:var(--border-card)">
                    <form method="POST" action="{{ route('super.schools.google.test', $school) }}">
                        @csrf
                        <button type="submit" class="w-full py-2 px-3 rounded-xl border border-indigo-200 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300 bg-white dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-indigo-900/50 text-xs font-semibold flex items-center justify-center gap-2 transition">
                            <i class="bi bi-broadcast"></i> Test API Authorization &amp; Scopes
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>

        {{-- Right Column: Migration Engine & Live Classrooms (7 cols) --}}
        <div class="lg:col-span-7 space-y-6">

            {{-- 1-Click Migration Card --}}
            <div class="card-modern bg-gradient-to-br from-indigo-500/5 via-cyan-500/5 to-transparent border-indigo-200/60 dark:border-indigo-800/60">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i class="bi bi-rocket-takeoff-fill text-indigo-600"></i>
                            Migrate All Data to Google Education Workspace
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-lg">
                            One-click batch migration: provisions Google accounts for all students and teachers, builds Google Classroom courses for every class, section &amp; subject, and enrolls full rosters.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('super.schools.google.migrate', $school) }}" id="migrationFallbackForm" onsubmit="return confirm('Start full data migration to Google Education Workspace for {{ addslashes($school->name) }}? This will provision student accounts and create classrooms.')">
                        @csrf
                        <button type="button"
                                id="migrateLaunchBtn"
                                onclick="startMigration()"
                                class="btn-primary !py-2.5 !px-4 text-xs font-bold shadow-md shadow-indigo-600/25 flex items-center gap-2 whitespace-nowrap {{ !$config->hasValidCredentials() ? 'opacity-50 cursor-not-allowed' : '' }}"
                                {{ !$config->hasValidCredentials() ? 'disabled' : '' }}>
                            <i class="bi bi-arrow-repeat text-sm" id="migrateBtnIcon"></i>
                            <span id="migrateBtnText">Run Full Migration Now</span>
                        </button>
                    </form>
                </div>

                {{-- ── Migration Progress Panel (shown while SSE stream is active) ── --}}
                <div id="migrationProgressPanel" class="hidden mt-5 pt-4 border-t border-indigo-100 dark:border-indigo-900/40">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200 flex items-center gap-1.5">
                            <i class="bi bi-arrow-repeat" id="migrationSpinIcon" style="display:none"></i>
                            <span id="migrationStepLabel">Starting…</span>
                        </span>
                        <span id="migrationPctBadge" class="text-xs font-extrabold px-2.5 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300">0%</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-700/60 rounded-full h-3 overflow-hidden mb-2">
                        <div id="migrationProgressBar" class="h-full rounded-full bg-gradient-to-r from-indigo-500 via-blue-500 to-emerald-500 transition-all duration-500 ease-out" style="width:0%"></div>
                    </div>
                    <p id="migrationDetailText" class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed"></p>
                    <div class="grid grid-cols-3 gap-2 mt-3" id="migrationChecklist">
                        <div id="chk-accounts"   class="flex items-center gap-1.5 text-[10px] font-semibold px-2 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-400 transition-all"><i class="bi bi-person-check"></i> Accounts</div>
                        <div id="chk-classrooms" class="flex items-center gap-1.5 text-[10px] font-semibold px-2 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-400 transition-all"><i class="bi bi-mortarboard"></i> Classrooms</div>
                        <div id="chk-done"       class="flex items-center gap-1.5 text-[10px] font-semibold px-2 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-400 transition-all"><i class="bi bi-patch-check"></i> Finalised</div>
                    </div>
                </div>
                <div id="migrationResultBanner" class="hidden mt-4 px-4 py-3 rounded-xl text-xs font-semibold"></div>

                {{-- Migration Breakdown Badges --}}
                    <div class="p-3 rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 text-center">
                        <span class="text-lg font-bold text-slate-800 dark:text-slate-100 block">{{ $config->stats['accounts_count'] ?? 0 }}</span>
                        <span class="text-[11px] text-slate-400 font-medium">Directory Accounts</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 text-center">
                        <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400 block">{{ $config->stats['classrooms_count'] ?? 0 }}</span>
                        <span class="text-[11px] text-slate-400 font-medium">Classroom Courses</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/60 text-center">
                        <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400 block">{{ $config->stats['roster_count'] ?? 0 }}</span>
                        <span class="text-[11px] text-slate-400 font-medium">Enrolled Rosters</span>
                    </div>
                </div>
            </div>

            {{-- Active Google Classrooms Table --}}
            <div class="card-modern !p-0 overflow-hidden">
                <div class="p-4 border-b flex items-center justify-between" style="border-color:var(--border-card)">
                    <h3 class="font-bold text-xs text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="bi bi-mortarboard-fill text-indigo-500"></i> Active Google Classrooms ({{ $courses->total() }})
                    </h3>
                    @if($courses->total() > 0)
                        <span class="text-[11px] text-slate-400">Classrooms synced with Google</span>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="table-minimal w-full text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/60" style="border-bottom:1px solid var(--border-card)">
                                <th class="py-2.5 px-3 text-left font-semibold">Classroom Course</th>
                                <th class="py-2.5 px-3 text-left font-semibold">Class &amp; Section</th>
                                <th class="py-2.5 px-3 text-center font-semibold">Enrollment Code</th>
                                <th class="py-2.5 px-3 text-right font-semibold">Classroom URL</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="border-color:var(--border-card)">
                            @forelse($courses as $c)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
                                <td class="py-2.5 px-3 font-semibold text-slate-800 dark:text-slate-200">
                                    <i class="bi bi-book text-indigo-500 mr-1.5"></i> {{ $c->name }}
                                </td>
                                <td class="py-2.5 px-3 text-slate-600 dark:text-slate-400">
                                    {{ $c->schoolClass?->class_name ?? '—' }} (Sec {{ $c->section_name ?: '—' }})
                                </td>
                                <td class="py-2.5 px-3 text-center font-mono font-bold text-slate-700 dark:text-slate-300">
                                    {{ $c->enrollment_code ?? '—' }}
                                </td>
                                <td class="py-2.5 px-3 text-right">
                                    @if($c->alternate_link)
                                        <a href="{{ $c->alternate_link }}" target="_blank" class="inline-flex items-center gap-1 text-indigo-600 hover:underline">
                                            Open <i class="bi bi-box-arrow-up-right text-[10px]"></i>
                                        </a>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400">
                                    No Google Classrooms provisioned yet. Click <strong>"Run Full Migration Now"</strong> to create them.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($courses->hasPages())
                <div class="p-3 border-t" style="border-color:var(--border-card)">
                    {{ $courses->links() }}
                </div>
                @endif
            </div>

            {{-- Recent Sync & Migration Logs --}}
            @if($logs->isNotEmpty())
            <div class="card-modern !p-0 overflow-hidden">
                <div class="p-3.5 border-b flex items-center justify-between" style="border-color:var(--border-card)">
                    <span class="font-bold text-xs text-slate-700 dark:text-slate-300 flex items-center gap-2">
                        <i class="bi bi-journal-text text-slate-400"></i> Recent Migration Logs
                    </span>
                    <span class="text-[10px] text-slate-400">Last 10 events</span>
                </div>
                <div class="divide-y text-xs" style="border-color:var(--border-card)">
                    @foreach($logs as $log)
                    <div class="p-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full {{ $log->status === 'success' ? 'bg-emerald-500' : ($log->status === 'warning' ? 'bg-amber-500' : 'bg-rose-500') }}"></span>
                            <span class="text-slate-700 dark:text-slate-300">{{ $log->summary }}</span>
                        </div>
                        <span class="text-[10px] text-slate-400 whitespace-nowrap">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>

</div>

@push('scripts')
<script>
(function () {
    const STREAM_URL = '{{ route('super.schools.google.migrate.stream', $school) }}'
                     + '?_token={{ csrf_token() }}';
    const FALLBACK_URL = '{{ route('super.schools.google.migrate', $school) }}';

    let evtSource = null;

    function el(id) { return document.getElementById(id); }

    function setProgress(pct, step, detail) {
        const bar    = el('migrationProgressBar');
        const badge  = el('migrationPctBadge');
        const label  = el('migrationStepLabel');
        const detail_ = el('migrationDetailText');
        if (bar)    bar.style.width = Math.min(100, pct) + '%';
        if (badge)  badge.textContent = pct + '%';
        if (label)  label.textContent = step;
        if (detail_) detail_.textContent = detail;

        // Tick checklist
        if (pct >= 40) tickCheck('chk-accounts');
        if (pct >= 95) tickCheck('chk-classrooms');
        if (pct >= 100) tickCheck('chk-done');
    }

    function tickCheck(id) {
        const div = el(id);
        if (!div || div.classList.contains('text-emerald-600')) return;
        div.classList.remove('text-slate-400', 'border-slate-200', 'dark:border-slate-700');
        div.classList.add('text-emerald-600', 'border-emerald-300', 'bg-emerald-50', 'dark:bg-emerald-900/20', 'dark:border-emerald-700');
        div.querySelector('i').className = 'bi bi-check-circle-fill';
    }

    function showResult(ok, msg) {
        const banner = el('migrationResultBanner');
        if (!banner) return;
        banner.classList.remove('hidden');
        if (ok) {
            banner.className = 'mt-4 px-4 py-3 rounded-xl text-xs font-semibold flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-700 dark:bg-emerald-900/20 dark:border-emerald-700 dark:text-emerald-400';
            banner.innerHTML = '<i class="bi bi-check-circle-fill text-emerald-500"></i> ' + msg;
        } else {
            banner.className = 'mt-4 px-4 py-3 rounded-xl text-xs font-semibold flex items-center gap-2 bg-rose-50 border border-rose-200 text-rose-700 dark:bg-rose-900/20 dark:border-rose-700 dark:text-rose-400';
            banner.innerHTML = '<i class="bi bi-exclamation-triangle-fill text-rose-500"></i> ' + msg;
        }
    }

    function resetBtn(running) {
        const btn  = el('migrateLaunchBtn');
        const icon = el('migrateBtnIcon');
        const txt  = el('migrateBtnText');
        if (!btn) return;
        btn.disabled = running;
        if (running) {
            btn.classList.add('opacity-60', 'cursor-not-allowed');
            if (icon) icon.className = 'bi bi-arrow-repeat text-sm animate-spin';
            if (txt)  txt.textContent = 'Migrating…';
        } else {
            btn.classList.remove('opacity-60', 'cursor-not-allowed');
            if (icon) icon.className = 'bi bi-arrow-repeat text-sm';
            if (txt)  txt.textContent = 'Run Full Migration Now';
        }
    }

    window.startMigration = function () {
        if (!confirm('Start full Google Education migration for {{ addslashes($school->name) }}? This provisions accounts and creates classrooms.')) return;

        // Show progress panel, reset state
        const panel = el('migrationProgressPanel');
        if (panel) panel.classList.remove('hidden');
        const result = el('migrationResultBanner');
        if (result) result.classList.add('hidden');

        // Reset checklist
        ['chk-accounts', 'chk-classrooms', 'chk-done'].forEach(id => {
            const div = el(id);
            if (!div) return;
            div.className = 'flex items-center gap-1.5 text-[10px] font-semibold px-2 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-400 transition-all';
            const i = div.querySelector('i');
            if (i) i.className = i.className.replace('bi-check-circle-fill', ['chk-accounts','chk-classrooms','chk-done'].indexOf(id) === 0 ? 'bi-person-check' : ['chk-accounts','chk-classrooms','chk-done'].indexOf(id) === 1 ? 'bi-mortarboard' : 'bi-patch-check');
        });

        setProgress(0, 'Connecting…', 'Establishing connection to migration service…');
        resetBtn(true);

        // Check SSE support, fall back to regular form POST
        if (typeof EventSource === 'undefined') {
            el('migrationFallbackForm')?.submit();
            return;
        }

        if (evtSource) evtSource.close();
        evtSource = new EventSource(STREAM_URL);

        evtSource.addEventListener('progress', function (e) {
            try {
                const d = JSON.parse(e.data);
                setProgress(d.pct || 0, d.step || '', d.detail || '');
            } catch (_) {}
        });

        evtSource.addEventListener('done', function (e) {
            evtSource.close();
            evtSource = null;
            try {
                const d = JSON.parse(e.data);
                setProgress(100, 'Complete ✓', d.detail || 'Migration completed successfully.');
            } catch (_) {
                setProgress(100, 'Complete ✓', 'Migration completed successfully.');
            }
            resetBtn(false);
            showResult(true, 'Migration completed successfully. Refresh to see updated stats.');
        });

        evtSource.addEventListener('error', function (e) {
            evtSource.close();
            evtSource = null;
            let msg = 'Migration failed. Check the logs for details.';
            try { msg = JSON.parse(e.data)?.detail || msg; } catch (_) {}
            setProgress(0, 'Failed', msg);
            resetBtn(false);
            showResult(false, msg);
        });

        evtSource.onerror = function () {
            if (evtSource && evtSource.readyState === EventSource.CLOSED) {
                evtSource = null;
                resetBtn(false);
            }
        };
    };
})();
</script>
@endpush

@endsection

