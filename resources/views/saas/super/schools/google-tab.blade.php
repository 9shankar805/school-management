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

                    <form method="POST" action="{{ route('super.schools.google.migrate', $school) }}" onsubmit="return confirm('Start full data migration to Google Education Workspace for {{ addslashes($school->name) }}? This will provision student accounts and create classrooms.')">
                        @csrf
                        <button type="submit" class="btn-primary !py-2.5 !px-4 text-xs font-bold shadow-md shadow-indigo-600/25 flex items-center gap-2 whitespace-nowrap {{ !$config->hasValidCredentials() ? 'opacity-50 cursor-not-allowed' : '' }}" {{ !$config->hasValidCredentials() ? 'disabled' : '' }}>
                            <i class="bi bi-arrow-repeat text-sm"></i> Run Full Migration Now
                        </button>
                    </form>
                </div>

                {{-- Migration Breakdown Badges --}}
                <div class="grid grid-cols-3 gap-3 mt-5 pt-4 border-t border-indigo-100 dark:border-indigo-900/40">
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
@endsection

