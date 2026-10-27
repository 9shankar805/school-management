@extends('layouts.app')

@push('head-scripts')
<style>
.iemis-stat-card {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 16px !important;
    padding: 14px 16px !important;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important;
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    gap: 14px !important;
    text-decoration: none !important;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    position: relative !important;
    overflow: hidden !important;
}
.iemis-stat-card:hover {
    box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08) !important;
    transform: translateY(-2px) !important;
    border-color: #cbd5e1 !important;
}
[data-theme="dark"] .iemis-stat-card,
html.dark .iemis-stat-card {
    background: #111927 !important;
    border-color: #223049 !important;
    box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.4) !important;
}
.iemis-icon-wrapper {
    font-size: 2.25rem !important;
    line-height: 1 !important;
    flex-shrink: 0 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}
.iemis-card-details {
    display: flex !important;
    flex-direction: column !important;
    justify-content: center !important;
    align-items: flex-start !important;
    flex: 1 !important;
    min-width: 0 !important;
}
.iemis-title {
    font-size: 11px !important;
    font-weight: 700 !important;
    color: #64748b !important;
    text-transform: uppercase !important;
    letter-spacing: 0.03em !important;
    line-height: 1.1 !important;
    margin-bottom: 2px !important;
}
[data-theme="dark"] .iemis-title,
html.dark .iemis-title {
    color: #94a3b8 !important;
}
.iemis-num-group {
    display: flex !important;
    align-items: baseline !important;
    gap: 6px !important;
}
.iemis-value {
    font-size: 22px !important;
    font-weight: 800 !important;
    color: #0f172a !important;
    line-height: 1.1 !important;
}
[data-theme="dark"] .iemis-value,
html.dark .iemis-value {
    color: #f8fafc !important;
}
.iemis-badge-green { font-size: 10px !important; font-weight: 700 !important; color: #10b981 !important; }
.iemis-badge-blue  { font-size: 10px !important; font-weight: 700 !important; color: #3b82f6 !important; }
.iemis-badge-pink  { font-size: 10px !important; font-weight: 700 !important; color: #ec4899 !important; }
</style>
@endpush

@section('content')
<div class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto space-y-6 bg-slate-50/50 dark:bg-slate-950 min-h-screen">

        {{-- Header & Quick Actions --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-sky-600 to-blue-600 text-white flex items-center justify-center shadow-lg shadow-sky-500/25 flex-shrink-0">
                    <i class="bi bi-person-badge-fill text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">HR Manager Dashboard</h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">{{ now()->format('l, F j, Y') }} &bull; Staff Directory, Roles & Birthdays</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                @can('create staff')
                <a href="{{ route('staff.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-gradient-to-r from-sky-600 to-blue-600 hover:from-sky-500 hover:to-blue-500 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-sm shadow-sky-600/25 transition active:scale-95">
                    <i class="bi bi-person-plus text-sm"></i>
                    <span>Add Staff</span>
                </a>
                @endcan
                <a href="{{ route('staff.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm font-semibold rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition active:scale-95">
                    <i class="bi bi-people text-slate-500 text-sm"></i>
                    <span>All Staff</span>
                </a>
            </div>
        </div>

        {{-- CEHRD IEMIS 3D Stat Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-4">
            <a href="{{ route('staff.index') }}" class="iemis-stat-card">
                <div class="iemis-icon-wrapper">💼</div>
                <div class="iemis-card-details">
                    <span class="iemis-title">Total Staff</span>
                    <div class="iemis-num-group">
                        <span class="iemis-value">{{ number_format($staffCount) }}</span>
                        <span class="iemis-badge-green">Active</span>
                    </div>
                </div>
            </a>
            <a href="{{ route('teacher.list.show') }}" class="iemis-stat-card">
                <div class="iemis-icon-wrapper">🧑‍🏫</div>
                <div class="iemis-card-details">
                    <span class="iemis-title">Teachers</span>
                    <div class="iemis-num-group">
                        <span class="iemis-value">{{ number_format($teacherCount) }}</span>
                        <span class="iemis-badge-blue">Faculty</span>
                    </div>
                </div>
            </a>
            <div class="iemis-stat-card col-span-2 lg:col-span-1">
                <div class="iemis-icon-wrapper">🎂</div>
                <div class="iemis-card-details">
                    <span class="iemis-title">Birthdays</span>
                    <div class="iemis-num-group">
                        <span class="iemis-value text-pink-600">{{ $birthdayStaff->count() }}</span>
                        <span class="iemis-badge-pink">This Month</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Staff Breakdown & Birthdays Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 sm:gap-6 mb-6">
            {{-- Staff by Role Chart --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-5 sm:p-6">
                <div class="flex items-center gap-2 mb-4">
                    <i class="bi bi-pie-chart-fill text-sky-500"></i>
                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100">Staff Distribution by Role</p>
                </div>
                <div id="chart-staff-roles" style="min-height:220px"></div>
            </div>

            {{-- Birthdays This Month --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2 bg-slate-50/60 dark:bg-slate-800/40">
                    <span class="w-2 h-2 rounded-full bg-pink-500"></span>
                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100">Birthdays This Month</p>
                </div>
                <div class="p-4 flex-1">
                    @if($birthdayStaff->count())
                    <div class="divide-y divide-slate-100 dark:divide-slate-800/80 max-h-64 overflow-y-auto">
                        @foreach($birthdayStaff as $u)
                        <div class="py-2.5 px-2 flex items-center gap-3 rounded-xl hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            <img src="{{ $u->avatar }}" class="w-9 h-9 rounded-2xl object-cover flex-shrink-0 border border-slate-200 dark:border-slate-700" alt="">
                            <div class="flex-1 min-w-0">
                                <p class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 truncate">{{ $u->full_name }}</p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 capitalize">{{ str_replace('-', ' ', $u->primary_role) }}</p>
                            </div>
                            <span class="text-xs font-bold text-pink-700 dark:text-pink-300 bg-pink-50 dark:bg-pink-950/60 border border-pink-200 dark:border-pink-900/60 px-2.5 py-1 rounded-xl flex-shrink-0">
                                {{ \Carbon\Carbon::parse($u->birthday)->format('M d') }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-10">
                        <p class="text-xs text-slate-400 font-medium">No staff birthdays recorded this month.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recently Added Staff --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/60 dark:bg-slate-800/40">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100">Recently Added Staff</p>
                </div>
                <a href="{{ route('staff.index') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">All staff directory &rarr;</a>
            </div>
            <div class="p-4 flex-1">
                @if($recentStaff->count())
                <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    @foreach($recentStaff as $u)
                    <div class="py-2.5 px-2 flex items-center gap-3 rounded-xl hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                        <img src="{{ $u->avatar }}" class="w-9 h-9 rounded-2xl object-cover flex-shrink-0 border border-slate-200 dark:border-slate-700" alt="">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 truncate">{{ $u->full_name }}</p>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500">{{ $u->email }}</p>
                        </div>
                        <span class="text-xs font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-2.5 py-1 rounded-xl capitalize flex-shrink-0">
                            {{ str_replace('-', ' ', $u->primary_role) }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-10">
                    <p class="text-xs text-slate-400 font-medium">No staff members listed yet.</p>
                </div>
                @endif
            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script>
(function () {
    @php
        $roleMap = [];
        if (isset($roleBreakdown)) {
            foreach ($roleBreakdown as $rb) {
                if (is_array($rb)) {
                    $roleMap[$rb['role'] ?? ''] = $rb['count'] ?? 0;
                } elseif (is_object($rb)) {
                    $roleMap[$rb->role ?? ''] = $rb->count ?? 0;
                }
            }
        } elseif (isset($staffByRole)) {
            $roleMap = (array)$staffByRole;
        }
    @endphp
    var roleData = @json($roleMap);
    var labels = Object.keys(roleData).map(k => k.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase()));
    var series = Object.values(roleData).map(v => parseInt(v));

    if (document.getElementById('chart-staff-roles') && labels.length) {
        new ApexCharts(document.getElementById('chart-staff-roles'), {
            chart: { 
                type: 'donut', 
                height: 220,
                fontFamily: 'Inter, system-ui, sans-serif'
            },
            labels: labels,
            series: series,
            legend: { 
                position: 'bottom', 
                fontSize: '11px',
                labels: { colors: '#64748b' }
            },
            dataLabels: { enabled: false },
            colors: ['#0284c7', '#6366f1', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#14b8a6']
        }).render();
    }
})();
</script>
@endpush
