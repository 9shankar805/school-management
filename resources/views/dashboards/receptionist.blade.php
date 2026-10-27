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
.iemis-badge-amber {
    font-size: 10px !important;
    font-weight: 700 !important;
    color: #f59e0b !important;
}
.iemis-badge-rose {
    font-size: 10px !important;
    font-weight: 700 !important;
    color: #ef4444 !important;
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

    {{-- Top Notification Alerts --}}
    @if(session('status'))
    <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-200 rounded-xl text-xs sm:text-sm font-semibold flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-2">
            <i class="bi bi-check-circle-fill text-emerald-600 dark:text-emerald-400"></i>
            <span>{{ session('status') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 dark:hover:text-emerald-300">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 18 ? 'Afternoon' : 'Evening') }}, {{ auth()->user()->first_name }}! 👋
                </h1>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/60">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse mr-1"></span> Front Desk Active
                </span>
            </div>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                {{ now()->format('l, F j, Y') }} &bull; {{ now()->format('g:i A') }}
                &bull; <span class="text-indigo-600 dark:text-indigo-400 font-bold">आ.व. {{ \App\Services\NepaliDateService::currentFiscalYear() }}</span>
                &bull; <span class="font-semibold text-slate-600 dark:text-slate-300">Reception & Visitor Management Hub</span>
            </p>
        </div>
        <div class="flex flex-wrap gap-2 items-center">
            <button type="button" onclick="openVisitorModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm shadow-indigo-600/25 transition active:scale-95 cursor-pointer">
                <i class="bi bi-person-plus-fill"></i> Check-In Visitor
            </button>
            <a href="{{ route('visitors.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <i class="bi bi-card-checklist text-slate-500"></i> Full Visitor Log
            </a>
            @can('create notices')
            <a href="{{ route('notice.create') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <i class="bi bi-megaphone-fill text-amber-500"></i> Post Notice
            </a>
            @endcan
        </div>
    </div>

    {{-- IEMIS Stat Cards (6 Cards) --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5 sm:gap-4">
        <a href="{{ route('visitors.index') }}" class="iemis-stat-card">
            <div class="iemis-icon-wrapper">👤</div>
            <div class="iemis-card-details">
                <div class="iemis-title">Visitors</div>
                <div class="iemis-num-group">
                    <span class="iemis-value">{{ $todayVisitorsCount }}</span>
                    <span class="iemis-badge-green">{{ $activeVisitorsCount }} Active</span>
                </div>
            </div>
        </a>

        <a href="{{ route('admissions.index') }}" class="iemis-stat-card">
            <div class="iemis-icon-wrapper">📝</div>
            <div class="iemis-card-details">
                <div class="iemis-title">Inquiries</div>
                <div class="iemis-num-group">
                    <span class="iemis-value">{{ $pendingAdmissions }}</span>
                    <span class="iemis-badge-amber">Pending</span>
                </div>
            </div>
        </a>

        <a href="{{ url('students') }}" class="iemis-stat-card">
            <div class="iemis-icon-wrapper">🎓</div>
            <div class="iemis-card-details">
                <div class="iemis-title">Students</div>
                <div class="iemis-num-group">
                    <span class="iemis-value">{{ number_format($studentCount) }}</span>
                    <span class="iemis-badge-indigo">Enrolled</span>
                </div>
            </div>
        </a>

        <a href="{{ url('teachers') }}" class="iemis-stat-card">
            <div class="iemis-icon-wrapper">🧑‍🏫</div>
            <div class="iemis-card-details">
                <div class="iemis-title">Teachers</div>
                <div class="iemis-num-group">
                    <span class="iemis-value">{{ number_format($teacherCount) }}</span>
                    <span class="iemis-badge-green">Faculty</span>
                </div>
            </div>
        </a>

        <a href="{{ route('notice.index') }}" class="iemis-stat-card">
            <div class="iemis-icon-wrapper">📢</div>
            <div class="iemis-card-details">
                <div class="iemis-title">Notices</div>
                <div class="iemis-num-group">
                    <span class="iemis-value">{{ $notices->count() }}</span>
                    <span class="iemis-badge-amber">Active</span>
                </div>
            </div>
        </a>

        <a href="{{ route('events.show') }}" class="iemis-stat-card">
            <div class="iemis-icon-wrapper">🗓️</div>
            <div class="iemis-card-details">
                <div class="iemis-title">Events</div>
                <div class="iemis-num-group">
                    <span class="iemis-value">{{ $upcomingEvents->count() }}</span>
                    <span class="iemis-badge-indigo">Scheduled</span>
                </div>
            </div>
        </a>
    </div>

    {{-- Live Campus Visitors & Quick Check-out Management --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex items-center gap-2.5">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                <div>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Today's Campus Visitor Log</h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Live reception check-in, badges, and guest tracking</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/80">
                    {{ $activeVisitorsCount }} Currently On Campus
                </span>
                <a href="{{ route('visitors.index') }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                    View All &rarr;
                </a>
            </div>
        </div>

        @if($recentVisitors->count())
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-bold tracking-wider">
                    <tr>
                        <th class="px-5 py-3">Pass / Badge</th>
                        <th class="px-5 py-3">Visitor Info</th>
                        <th class="px-5 py-3">Purpose / Meeting</th>
                        <th class="px-5 py-3">Check-In Time</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($recentVisitors as $visitor)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-3 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                            #{{ $visitor->badge_number ?? 'VIS-'.$visitor->id }}
                        </td>
                        <td class="px-5 py-3">
                            <div class="font-bold text-slate-900 dark:text-white">{{ $visitor->name }}</div>
                            <div class="text-[11px] text-slate-400">{{ $visitor->phone }} @if($visitor->organization) &bull; {{ $visitor->organization }} @endif</div>
                        </td>
                        <td class="px-5 py-3">
                            <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $visitor->purpose }}</div>
                            <div class="text-[11px] text-slate-400">To Meet: <span class="text-indigo-600 dark:text-indigo-400 font-medium">{{ $visitor->person_to_meet }}</span></div>
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap font-medium text-slate-500 dark:text-slate-400">
                            <i class="bi bi-clock mr-1 text-slate-400"></i>{{ \Carbon\Carbon::parse($visitor->check_in_at)->format('h:i A') }}
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap">
                            @if(is_null($visitor->check_out_at) && $visitor->status !== 'checked_out')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/80">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> On Campus
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                Checked Out ({{ \Carbon\Carbon::parse($visitor->check_out_at)->format('h:i A') }})
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right whitespace-nowrap space-x-1.5">
                            @if(is_null($visitor->check_out_at) && $visitor->status !== 'checked_out')
                            <form action="{{ route('visitors.checkout', $visitor->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 text-[11px] font-bold text-rose-600 hover:text-white bg-rose-50 hover:bg-rose-600 dark:bg-rose-950/50 dark:hover:bg-rose-600 border border-rose-200 dark:border-rose-900 rounded-lg transition cursor-pointer" onclick="return confirm('Check out {{ $visitor->name }}?')">
                                    <i class="bi bi-box-arrow-right mr-1"></i>Check Out
                                </button>
                            </form>
                            @endif
                            <a href="{{ route('visitors.badge', $visitor->id) }}" target="_blank" class="px-2.5 py-1 text-[11px] font-semibold text-slate-600 dark:text-slate-300 hover:text-indigo-600 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 rounded-lg transition" title="Print Visitor Pass">
                                <i class="bi bi-printer"></i> Pass
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-10 px-4">
            <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-3 text-xl">
                <i class="bi bi-people"></i>
            </div>
            <p class="text-sm font-bold text-slate-700 dark:text-slate-300">No visitors logged today</p>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">When parents, vendors, or guests arrive at the front gate, log them here.</p>
            <button type="button" onclick="openVisitorModal()" class="mt-3 inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/80 rounded-xl transition cursor-pointer">
                <i class="bi bi-person-plus-fill"></i> Check In First Visitor
            </button>
        </div>
        @endif
    </div>

    {{-- Front Desk Quick Lookups & Operations Hub --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Student Quick Lookup --}}
        <a href="{{ url('students') }}" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs hover:border-indigo-400 dark:hover:border-indigo-600 hover:shadow-md transition group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg font-bold group-hover:scale-110 transition-transform">
                    <i class="bi bi-search"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white">Student & Parent Finder</h3>
                    <p class="text-[11px] text-slate-400">Search roll, class, guardian phone</p>
                </div>
            </div>
        </a>

        {{-- Staff Directory --}}
        <a href="{{ url('teachers') }}" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs hover:border-indigo-400 dark:hover:border-indigo-600 hover:shadow-md transition group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg font-bold group-hover:scale-110 transition-transform">
                    <i class="bi bi-telephone-outbound"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white">Staff Extension Directory</h3>
                    <p class="text-[11px] text-slate-400">Faculty phone numbers & departments</p>
                </div>
            </div>
        </a>

        {{-- Admissions & Inquiries --}}
        <a href="{{ route('admissions.index') }}" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs hover:border-indigo-400 dark:hover:border-indigo-600 hover:shadow-md transition group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg font-bold group-hover:scale-110 transition-transform">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white">Admission Leads & Inquiries</h3>
                    <p class="text-[11px] text-slate-400">Prospectus, online applications</p>
                </div>
            </div>
        </a>

        {{-- New Student Registration --}}
        <a href="{{ route('student.create.show') }}" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-xs hover:border-indigo-400 dark:hover:border-indigo-600 hover:shadow-md transition group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-lg font-bold group-hover:scale-110 transition-transform">
                    <i class="bi bi-person-badge"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white">Direct Student Enrolment</h3>
                    <p class="text-[11px] text-slate-400">Register new student admission</p>
                </div>
            </div>
        </a>
    </div>

    {{-- Upcoming Events & Notices Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 sm:gap-6">
        {{-- Scheduled Appointments & Events --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-violet-500"></span>
                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100">Scheduled Events & Parent Meetings</p>
                </div>
                @can('view events')
                <a href="{{ route('events.show') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Full Calendar &rarr;</a>
                @endcan
            </div>
            @if($upcomingEvents->count())
            <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
                @foreach($upcomingEvents as $event)
                <div class="px-5 py-3.5 flex justify-between items-center hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                    <div class="min-w-0 pr-3">
                        <p class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-200 truncate">{{ $event->title }}</p>
                        @if($event->description)
                        <p class="text-[11px] text-slate-400 truncate">{{ $event->description }}</p>
                        @endif
                    </div>
                    <span class="text-xs font-bold text-violet-700 dark:text-violet-300 bg-violet-50 dark:bg-violet-950/60 border border-violet-200 dark:border-violet-900/60 px-2.5 py-1 rounded-xl flex-shrink-0">
                        {{ \Carbon\Carbon::parse($event->start)->format('M d, Y') }}
                    </span>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-10 px-4">
                <p class="text-sm text-slate-400 dark:text-slate-500">No upcoming meetings or events on the calendar.</p>
            </div>
            @endif
        </div>

        {{-- Notice Board --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100">School Bulletins & Daily Notices</p>
                </div>
                <a href="{{ route('notice.index') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">All notices &rarr;</a>
            </div>
            @if($notices->count())
            <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
                @foreach($notices->take(6) as $notice)
                <div class="px-5 py-3.5 hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                    <div class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 line-clamp-2 leading-relaxed">
                        {!! \Stevebauman\Purify\Facades\Purify::clean(strip_tags($notice->notice)) !!}
                    </div>
                    <span class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 block">
                        <i class="bi bi-clock mr-1"></i>{{ $notice->created_at->diffForHumans() }}
                    </span>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-10 px-4">
                <p class="text-sm text-slate-400 dark:text-slate-500">No notices published yet.</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Front Office Emergency Hotline & Key Extensions Card --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-sm font-bold">
                    <i class="bi bi-telephone-fill"></i>
                </div>
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">Emergency & Department Intercom Extensions</h3>
                    <p class="text-[11px] text-slate-400">Quick-dial key school offices from the front desk</p>
                </div>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Internal Speed Dial</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 text-center">
                <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Principal Office</div>
                <div class="text-sm font-mono font-bold text-slate-800 dark:text-slate-100 mt-0.5">Ext. 101</div>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 text-center">
                <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Vice Principal</div>
                <div class="text-sm font-mono font-bold text-slate-800 dark:text-slate-100 mt-0.5">Ext. 102</div>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 text-center">
                <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Accounts & Cashier</div>
                <div class="text-sm font-mono font-bold text-slate-800 dark:text-slate-100 mt-0.5">Ext. 105</div>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 text-center">
                <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">First Aid Infirmary</div>
                <div class="text-sm font-mono font-bold text-rose-600 dark:text-rose-400 mt-0.5">Ext. 109 🚑</div>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 text-center">
                <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Transport & Bus Dispatch</div>
                <div class="text-sm font-mono font-bold text-slate-800 dark:text-slate-100 mt-0.5">Ext. 112 🚌</div>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 text-center">
                <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Gate 1 Main Security</div>
                <div class="text-sm font-mono font-bold text-slate-800 dark:text-slate-100 mt-0.5">Ext. 100 🛡️</div>
            </div>
        </div>
    </div>

</div>

{{-- Quick Visitor Check-In Modal --}}
<div id="quickVisitorModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4 hidden transition-opacity duration-200" onclick="if(event.target === this) closeVisitorModal()">
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-3xl max-w-xl w-full p-6 sm:p-7 shadow-2xl shadow-slate-950/20 transform transition-all duration-200 space-y-5">
        
        {{-- Modal Header --}}
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-600 text-white flex items-center justify-center text-lg font-bold shadow-md shadow-indigo-500/25">
                    <i class="bi bi-person-badge"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight">Quick Visitor Check-In</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Issue instant campus badge & print visitor pass</p>
                </div>
            </div>
            <button type="button" onclick="closeVisitorModal()" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>

        {{-- Modal Form --}}
        <form action="{{ route('visitors.store') }}" method="POST" class="space-y-4">
            @csrf
            
            {{-- Visitor Name & Phone --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                        Visitor Full Name <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <i class="bi bi-person absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="text" name="name" required placeholder="e.g. Ramesh Sharma" 
                            class="w-full pl-9 pr-3.5 py-2.5 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/80 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:bg-white dark:focus:bg-slate-800 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-hidden transition">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                        Phone Number <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <i class="bi bi-telephone absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="text" name="phone" required placeholder="e.g. 98XXXXXXXX" 
                            class="w-full pl-9 pr-3.5 py-2.5 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/80 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:bg-white dark:focus:bg-slate-800 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-hidden transition">
                    </div>
                </div>
            </div>

            {{-- Organization & Person to Meet --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                        Organization / Relation
                    </label>
                    <div class="relative">
                        <i class="bi bi-building absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="text" name="organization" placeholder="e.g. Parent of Grade 8 / ABC Supplies" 
                            class="w-full pl-9 pr-3.5 py-2.5 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/80 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:bg-white dark:focus:bg-slate-800 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-hidden transition">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                        Person to Meet <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <i class="bi bi-person-check absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="text" name="person_to_meet" required placeholder="e.g. Principal / Teacher Hari" 
                            class="w-full pl-9 pr-3.5 py-2.5 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/80 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:bg-white dark:focus:bg-slate-800 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-hidden transition">
                    </div>
                </div>
            </div>

            {{-- Purpose of Visit with Quick Chips --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-200">
                        Purpose of Visit <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[11px] text-slate-400">Click quick tag or type below</span>
                </div>
                <div class="relative">
                    <i class="bi bi-card-text absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" id="visitorPurposeInput" name="purpose" required placeholder="e.g. Parent-Teacher Meeting / Admission Inquiry" 
                        class="w-full pl-9 pr-3.5 py-2.5 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/80 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:bg-white dark:focus:bg-slate-800 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-hidden transition">
                </div>
                
                {{-- Quick Tags --}}
                <div class="flex flex-wrap gap-1.5 mt-2">
                    <button type="button" onclick="setVisitorPurpose('Parent-Teacher Meeting')" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-950/60 dark:hover:text-indigo-300 border border-slate-200/80 dark:border-slate-700/80 transition cursor-pointer">
                        👨‍👩‍👧 Parent Meeting
                    </button>
                    <button type="button" onclick="setVisitorPurpose('Admission Inquiry')" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-950/60 dark:hover:text-indigo-300 border border-slate-200/80 dark:border-slate-700/80 transition cursor-pointer">
                        📝 Admission Inquiry
                    </button>
                    <button type="button" onclick="setVisitorPurpose('Fee Counter & Accounts')" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-950/60 dark:hover:text-indigo-300 border border-slate-200/80 dark:border-slate-700/80 transition cursor-pointer">
                        💳 Fee Payment
                    </button>
                    <button type="button" onclick="setVisitorPurpose('Principal Meeting')" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-950/60 dark:hover:text-indigo-300 border border-slate-200/80 dark:border-slate-700/80 transition cursor-pointer">
                        🏛️ Principal Meeting
                    </button>
                    <button type="button" onclick="setVisitorPurpose('Official Vendor / Delivery')" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-950/60 dark:hover:text-indigo-300 border border-slate-200/80 dark:border-slate-700/80 transition cursor-pointer">
                        📦 Vendor / Delivery
                    </button>
                </div>
            </div>

            {{-- Additional Notes / ID details --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5">
                    Additional Notes <span class="text-slate-400 font-normal">(Optional)</span>
                </label>
                <div class="relative">
                    <textarea name="notes" rows="2" placeholder="Vehicle number, belongings, ID details..." 
                        class="w-full px-3.5 py-2.5 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/80 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:bg-white dark:focus:bg-slate-800 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-hidden transition resize-none"></textarea>
                </div>
            </div>

            {{-- Footer Action Buttons --}}
            <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-800">
                <span class="text-[11px] text-slate-400 dark:text-slate-500 flex items-center gap-1">
                    <i class="bi bi-shield-check text-emerald-500"></i> Auto-generates visitor badge #
                </span>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="closeVisitorModal()" 
                        class="px-4 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="inline-flex items-center gap-1.5 px-5 py-2.5 text-xs font-bold text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 rounded-xl shadow-md shadow-indigo-600/30 transition transform active:scale-95 cursor-pointer">
                        <i class="bi bi-check-circle-fill"></i> Check-In & Issue Badge
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openVisitorModal() {
    const modal = document.getElementById('quickVisitorModal');
    if (modal) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            const nameInput = modal.querySelector('input[name="name"]');
            if (nameInput) nameInput.focus();
        }, 50);
    }
}
function closeVisitorModal() {
    const modal = document.getElementById('quickVisitorModal');
    if (modal) modal.classList.add('hidden');
}
function setVisitorPurpose(purposeText) {
    const input = document.getElementById('visitorPurposeInput');
    if (input) {
        input.value = purposeText;
        input.focus();
    }
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeVisitorModal();
    }
});
</script>
@endsection

