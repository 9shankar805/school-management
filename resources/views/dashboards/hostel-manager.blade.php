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
[data-theme="dark"] .iemis-stat-card:hover,
html.dark .iemis-stat-card:hover {
    border-color: #3b82f6 !important;
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
.iemis-badge-green {
    font-size: 10px !important;
    font-weight: 700 !important;
    color: #10b981 !important;
}
.iemis-badge-indigo {
    font-size: 10px !important;
    font-weight: 700 !important;
    color: #6366f1 !important;
}
</style>
@endpush

@section('content')
<div class="flex-1 p-4 sm:p-6 lg:p-8 overflow-auto bg-slate-50/60 dark:bg-slate-950 min-h-screen space-y-6">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 18 ? 'Afternoon' : 'Evening') }}, {{ auth()->user()->first_name }}! 👋
                </h1>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                    {{ now()->format('l, F j, Y') }} &bull; {{ now()->format('g:i A') }}
                    &bull; <span class="text-indigo-600 dark:text-indigo-400 font-bold">आ.व. {{ \App\Services\NepaliDateService::currentFiscalYear() }}</span>
                    &bull; <span class="font-semibold text-slate-600 dark:text-slate-300">Hostel Management</span>
                </p>
            </div>
        </div>

        {{-- IEMIS Stat Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4">
            <a href="{{ url('students') }}" class="iemis-stat-card">
                <div class="iemis-icon-wrapper">🎓</div>
                <div class="iemis-card-details">
                    <div class="iemis-title">Students</div>
                    <div class="iemis-num-group">
                        <span class="iemis-value">{{ number_format($studentCount) }}</span>
                        <span class="iemis-badge-green">Enrolled</span>
                    </div>
                </div>
            </a>

            <div class="iemis-stat-card">
                <div class="iemis-icon-wrapper">🏠</div>
                <div class="iemis-card-details">
                    <div class="iemis-title">Facilities</div>
                    <div class="iemis-num-group">
                        <span class="iemis-value">Active</span>
                        <span class="iemis-badge-indigo">Ready</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Capabilities & Features Grid --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-5">
            <div class="flex items-center gap-2 mb-4">
                <i class="bi bi-grid-fill text-indigo-500"></i>
                <p class="text-sm font-bold text-slate-800 dark:text-slate-100">Hostel Operational Modules</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach([
                    ['title' => 'Building & Block Management', 'icon' => 'bi-building'],
                    ['title' => 'Room & Bed Allocations', 'icon' => 'bi-layout-sidebar'],
                    ['title' => 'Student Check-in / Check-out', 'icon' => 'bi-person-check'],
                    ['title' => 'Curfew & Night Attendance', 'icon' => 'bi-moon-stars'],
                    ['title' => 'Visitor & Guest Pass Log', 'icon' => 'bi-shield-check'],
                    ['title' => 'Hostel Fee Billing & Dues', 'icon' => 'bi-cash-stack'],
                    ['title' => 'Maintenance & Repair Requests', 'icon' => 'bi-tools'],
                    ['title' => 'Warden & Staff Assignments', 'icon' => 'bi-person-badge'],
                    ['title' => 'Mess & Meal Schedule', 'icon' => 'bi-cup-hot']
                ] as $feat)
                <div class="flex items-center gap-2.5 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                    <span class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs flex-shrink-0">
                        <i class="bi {{ $feat['icon'] }}"></i>
                    </span>
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $feat['title'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

    </div>
@endsection
