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
.iemis-badge-amber { font-size: 10px !important; font-weight: 700 !important; color: #f59e0b !important; }
.iemis-badge-blue  { font-size: 10px !important; font-weight: 700 !important; color: #3b82f6 !important; }
</style>
@endpush

@section('content')
<div class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto space-y-6 bg-slate-50/50 dark:bg-slate-950 min-h-screen">

        {{-- Header & Quick Actions --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-cyan-600 to-blue-600 text-white flex items-center justify-center shadow-lg shadow-cyan-500/25 flex-shrink-0">
                    <i class="bi bi-person-plus-fill text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Admission Officer Dashboard</h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">{{ now()->format('l, F j, Y') }} &bull; Academic Intake & Registration</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                @can('create students')
                <a href="{{ route('student.create.show') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-sm shadow-indigo-600/25 transition active:scale-95">
                    <i class="bi bi-person-plus text-sm"></i>
                    <span>Admit Student</span>
                </a>
                @endcan
                @can('view students')
                <a href="{{ route('student.list.show') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm font-semibold rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition active:scale-95">
                    <i class="bi bi-people text-slate-500 text-sm"></i>
                    <span>All Students</span>
                </a>
                @endcan
            </div>
        </div>

        {{-- CEHRD IEMIS 3D Stat Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
            <a href="{{ route('student.list.show') }}" class="iemis-stat-card">
                <div class="iemis-icon-wrapper">🎓</div>
                <div class="iemis-card-details">
                    <span class="iemis-title">Total Students</span>
                    <div class="iemis-num-group">
                        <span class="iemis-value">{{ number_format($totalStudents) }}</span>
                        <span class="iemis-badge-green">Session</span>
                    </div>
                </div>
            </a>
            <div class="iemis-stat-card">
                <div class="iemis-icon-wrapper">📝</div>
                <div class="iemis-card-details">
                    <span class="iemis-title">This Month</span>
                    <div class="iemis-num-group">
                        <span class="iemis-value">{{ $thisMonthAdmissions }}</span>
                        <span class="iemis-badge-green">New</span>
                    </div>
                </div>
            </div>
            <a href="{{ url('classes') }}" class="iemis-stat-card">
                <div class="iemis-icon-wrapper">🏫</div>
                <div class="iemis-card-details">
                    <span class="iemis-title">Classes</span>
                    <div class="iemis-num-group">
                        <span class="iemis-value">{{ $classCount }}</span>
                        <span class="iemis-badge-amber">Available</span>
                    </div>
                </div>
            </a>
            <div class="iemis-stat-card">
                <div class="iemis-icon-wrapper">📈</div>
                <div class="iemis-card-details">
                    <span class="iemis-title">Avg / Month</span>
                    <div class="iemis-num-group">
                        @php $avgPerMonth = $totalStudents > 0 ? round($totalStudents / max(1, now()->month)) : 0; @endphp
                        <span class="iemis-value">{{ $avgPerMonth }}</span>
                        <span class="iemis-badge-blue">Intake</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Admissions Table & Card List --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/60 dark:bg-slate-800/40">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100">Recent Admissions</p>
                </div>
                @can('view students')
                <a href="{{ route('student.list.show') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">View all students &rarr;</a>
                @endcan
            </div>
            @if($recentAdmissions->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold text-slate-400 dark:text-slate-500 bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800">
                            <th class="px-5 py-3">Student</th>
                            <th class="px-5 py-3">Email</th>
                            <th class="px-5 py-3">Gender</th>
                            <th class="px-5 py-3">Admitted Date</th>
                            @can('view students')
                            <th class="px-5 py-3 text-right">Action</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @foreach($recentAdmissions as $s)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <img src="{{ $s->avatar }}" class="w-8 h-8 rounded-xl object-cover flex-shrink-0 border border-slate-200 dark:border-slate-700" alt="">
                                    <div>
                                        <span class="font-bold text-slate-800 dark:text-slate-100 text-xs sm:text-sm block">{{ $s->full_name }}</span>
                                        <span class="text-[11px] text-slate-400 dark:text-slate-500">ID: {{ $s->id }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-500 dark:text-slate-400">{{ $s->email }}</td>
                            <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-300 capitalize">{{ $s->gender ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-xs text-slate-400 dark:text-slate-500">{{ $s->created_at?->format('M d, Y') ?? '—' }}</td>
                            @can('view students')
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('student.profile.show', $s->id) }}" class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/50 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 rounded-lg transition active:scale-95">
                                    <span>Profile</span>
                                    <i class="bi bi-arrow-right text-[10px]"></i>
                                </a>
                            </td>
                            @endcan
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-12 px-4">
                <i class="bi bi-person-slash text-3xl text-slate-300 dark:text-slate-600 block mb-2"></i>
                <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">No students admitted yet this session.</p>
            </div>
            @endif
        </div>

    </div>
@endsection
