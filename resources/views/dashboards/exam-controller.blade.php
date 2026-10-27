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
.iemis-badge-indigo{ font-size: 10px !important; font-weight: 700 !important; color: #6366f1 !important; }
</style>
@endpush

@section('content')
<div class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto space-y-6 bg-slate-50/50 dark:bg-slate-950 min-h-screen">

        {{-- Header & Quick Actions --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-orange-500 to-amber-600 text-white flex items-center justify-center shadow-lg shadow-orange-500/25 flex-shrink-0">
                    <i class="bi bi-file-earmark-text-fill text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Exam Controller Dashboard</h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">{{ now()->format('l, F j, Y') }} &bull; Assessments, Schedules & Marksheets</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                @can('create exams')
                <a href="{{ route('exam.create.show') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-gradient-to-r from-orange-600 to-amber-600 hover:from-orange-500 hover:to-amber-500 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-sm shadow-orange-600/25 transition active:scale-95">
                    <i class="bi bi-file-plus text-sm"></i>
                    <span>Create Exam</span>
                </a>
                @endcan
                @can('view exams')
                <a href="{{ route('exam.list.show') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm font-semibold rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition active:scale-95">
                    <i class="bi bi-list-ul text-slate-500 text-sm"></i>
                    <span>All Exams</span>
                </a>
                @endcan
            </div>
        </div>

        {{-- CEHRD IEMIS 3D Stat Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-4">
            <a href="{{ route('exam.list.show') }}" class="iemis-stat-card">
                <div class="iemis-icon-wrapper">📋</div>
                <div class="iemis-card-details">
                    <span class="iemis-title">Total Exams</span>
                    <div class="iemis-num-group">
                        <span class="iemis-value">{{ $totalExams }}</span>
                        <span class="iemis-badge-green">Session</span>
                    </div>
                </div>
            </a>
            <div class="iemis-stat-card">
                <div class="iemis-icon-wrapper">📅</div>
                <div class="iemis-card-details">
                    <span class="iemis-title">Upcoming Exams</span>
                    <div class="iemis-num-group">
                        <span class="iemis-value">{{ $upcomingExams->count() }}</span>
                        <span class="iemis-badge-amber">30 Days</span>
                    </div>
                </div>
            </div>
            <a href="{{ route('student.list.show') }}" class="iemis-stat-card col-span-2 lg:col-span-1">
                <div class="iemis-icon-wrapper">🎓</div>
                <div class="iemis-card-details">
                    <span class="iemis-title">Candidate Pool</span>
                    <div class="iemis-num-group">
                        <span class="iemis-value">{{ number_format($studentCount) }}</span>
                        <span class="iemis-badge-indigo">Students</span>
                    </div>
                </div>
            </a>
        </div>

        {{-- Upcoming Exams & Past Exams Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 sm:gap-6">
            {{-- Upcoming Exams --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/60 dark:bg-slate-800/40">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <p class="text-sm font-bold text-slate-800 dark:text-slate-100">Upcoming Exams Schedule</p>
                    </div>
                    <span class="text-xs font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-900/60 px-2.5 py-0.5 rounded-full">
                        {{ $upcomingExams->count() }} active
                    </span>
                </div>
                <div class="p-4 flex-1">
                    @if($upcomingExams->count())
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800">
                                    <th class="px-3 py-2.5">Exam Name</th>
                                    <th class="px-3 py-2.5">Subject</th>
                                    <th class="px-3 py-2.5">Start Date</th>
                                    <th class="px-3 py-2.5">End Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                @foreach($upcomingExams as $exam)
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                                    <td class="px-3 py-3 font-bold text-slate-800 dark:text-slate-100 text-xs sm:text-sm">{{ $exam->exam_name }}</td>
                                    <td class="px-3 py-3 text-xs text-slate-500 dark:text-slate-400">{{ $exam->course?->course_name ?? $exam->course?->name ?? '—' }}</td>
                                    <td class="px-3 py-3 text-xs text-indigo-600 dark:text-indigo-400 font-semibold">{{ \Carbon\Carbon::parse($exam->start_date)->format('M d, Y') }}</td>
                                    <td class="px-3 py-3 text-xs text-slate-400 dark:text-slate-500">{{ $exam->end_date ? \Carbon\Carbon::parse($exam->end_date)->format('M d') : '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-10">
                        <i class="bi bi-calendar2-x text-3xl text-slate-300 dark:text-slate-600 block mb-2"></i>
                        <p class="text-xs text-slate-400 font-medium">No upcoming exams in the next 30 days.</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Recently Completed Exams --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/60 dark:bg-slate-800/40">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <p class="text-sm font-bold text-slate-800 dark:text-slate-100">Recently Concluded</p>
                    </div>
                </div>
                <div class="p-4 flex-1">
                    @if($pastExams->count())
                    <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        @foreach($pastExams as $exam)
                        <div class="py-3 px-2 flex items-center justify-between hover:bg-slate-50/70 dark:hover:bg-slate-800/40 rounded-xl transition-colors">
                            <div>
                                <p class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100">{{ $exam->exam_name }}</p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500">{{ $exam->course?->course_name ?? $exam->course?->name ?? 'Assessment' }} &bull; Ended {{ $exam->end_date ? \Carbon\Carbon::parse($exam->end_date)->format('M d, Y') : '—' }}</p>
                            </div>
                            <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                Completed
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-10">
                        <p class="text-xs text-slate-400 font-medium">No past exams recorded recently.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
@endsection
