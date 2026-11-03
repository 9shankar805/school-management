@extends('layouts.app')
@section('title', 'Google Education Workspace Hub')

@section('breadcrumb')
<x-breadcrumb :items="[ ['label' => 'Super Admin'], ['label' => 'Google Workspace Hub'] ]"/>
@endsection

@section('content')
<div class="page-wrapper">

    {{-- Page Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title flex items-center gap-2.5">
                <i class="bi bi-google text-indigo-600"></i>
                Google Education Workspace Hub
            </h1>
            <p class="page-subtitle">Configure Google identities, domains, API credentials, and migrate school data</p>
        </div>
    </div>

    @include('session-messages')

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <x-stat-card label="Total Schools"       :value="$stats['total_schools']"     icon="bi-building"        icon-color="indigo"/>
        <x-stat-card label="Connected Schools"   :value="$stats['connected_schools']" icon="bi-check2-circle"   icon-color="emerald"/>
        <x-stat-card label="Active Classrooms"   :value="$stats['total_classrooms']"  icon="bi-mortarboard"     icon-color="amber"/>
        <x-stat-card label="Synced Accounts"     :value="$stats['total_accounts']"    icon="bi-people"          icon-color="violet"/>
    </div>

    {{-- Search Filter --}}
    <form method="GET" class="card-modern p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div class="form-group-modern flex-1 min-w-48">
            <label class="label-modern">Search School</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   class="input-modern" placeholder="Search by school name, slug, email…">
        </div>
        <button type="submit" class="btn-primary"><i class="bi bi-search"></i> Filter</button>
        @if(request('search'))
            <a href="{{ route('super.google.index') }}" class="btn-secondary">Clear</a>
        @endif
    </form>

    {{-- Schools Google Workspace Table --}}
    <div class="card-modern !p-0 overflow-hidden">
        <div class="p-4 border-b flex items-center justify-between" style="border-color:var(--border-card)">
            <h3 class="font-bold text-sm text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="bi bi-buildings text-indigo-500"></i> All School Workspaces
            </h3>
            <span class="text-xs text-slate-400">{{ $schools->total() }} Schools</span>
        </div>

        <div class="overflow-x-auto">
            <table class="table-minimal w-full text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60" style="border-bottom:1px solid var(--border-card)">
                        <th class="py-3 px-4 text-left font-semibold">School</th>
                        <th class="py-3 px-4 text-left font-semibold">Workspace Domain</th>
                        <th class="py-3 px-4 text-left font-semibold">Admin Account</th>
                        <th class="py-3 px-4 text-center font-semibold">Status</th>
                        <th class="py-3 px-4 text-center font-semibold">Classrooms</th>
                        <th class="py-3 px-4 text-left font-semibold">Last Synced</th>
                        <th class="py-3 px-4 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color:var(--border-card)">
                    @forelse($schools as $school)
                    @php $gConf = $school->googleConfig; @endphp
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $school->logo_url }}" class="w-8 h-8 rounded-lg object-cover border" style="border-color:var(--border-card)" alt="">
                                <div>
                                    <div class="font-bold text-slate-800 dark:text-slate-100">{{ $school->name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $school->slug }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 font-mono font-medium text-slate-700 dark:text-slate-300">
                            {{ $gConf?->workspace_domain ?? '—' }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400">
                            {{ $gConf?->admin_email ?? '—' }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if(!$gConf || !$gConf->hasValidCredentials())
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                    <i class="bi bi-dash-circle"></i> Not Configured
                                </span>
                            @elseif($gConf->connection_status === 'connected')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                                    <i class="bi bi-check-circle-fill"></i> Connected
                                </span>
                            @elseif($gConf->connection_status === 'error')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300" title="{{ $gConf->last_error_message }}">
                                    <i class="bi bi-exclamation-triangle-fill"></i> Error
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">
                                    <i class="bi bi-clock-history"></i> Untested
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center font-bold">
                            {{ $gConf?->stats['classrooms_count'] ?? 0 }}
                        </td>
                        <td class="py-3.5 px-4 text-slate-400 text-[11px]">
                            {{ $gConf?->last_synced_at?->diffForHumans() ?? 'Never' }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('super.schools.google', $school) }}" class="btn-primary !py-1 !px-2.5 !text-xs" title="Configure & Migrate">
                                    <i class="bi bi-sliders"></i> Configure &amp; Migrate
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">No schools found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($schools->hasPages())
        <div class="p-4 border-t" style="border-color:var(--border-card)">
            {{ $schools->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

