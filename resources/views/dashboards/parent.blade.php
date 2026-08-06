@extends('layouts.app')
@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">

    {{-- Header --}}
    <div class="flex flex-wrap justify-between items-start mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Parent Dashboard</h1>
            <p class="text-slate-400 text-sm mt-0.5">{{ now()->format('l, F j, Y') }} &middot; Welcome, <span class="font-medium text-slate-600">{{ $parent->first_name }}</span></p>
        </div>
        <div class="flex gap-2 flex-shrink-0">
            <a href="{{ route('parent.conversations') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 transition text-slate-700 {{ $unreadMessages > 0 ? 'ring-2 ring-indigo-200' : '' }}">
                <i class="bi bi-chat-dots-fill text-indigo-500"></i> Messages
                @if($unreadMessages > 0)
                <span class="bg-rose-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $unreadMessages }}</span>
                @endif
            </a>
            <a href="{{ route('events.show') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 transition text-slate-700">
                <i class="bi bi-calendar-event-fill text-amber-500"></i> Calendar
            </a>
        </div>
    </div>

    {{-- No children warning --}}
    @if($children->isEmpty())
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 mb-6 flex items-start gap-3">
        <i class="bi bi-exclamation-triangle-fill text-amber-500 text-lg mt-0.5"></i>
        <div>
            <p class="font-semibold text-amber-800 text-sm">No children linked to your account</p>
            <p class="text-amber-700 text-xs mt-1">Please contact the school administration to link your child's profile.</p>
        </div>
    </div>
    @endif

    {{-- Per-child cards --}}
    @foreach($childData as $cd)
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-6">

        {{-- Child header --}}
        <div class="px-5 py-4 bg-gradient-to-r from-indigo-50 to-slate-50 border-b border-slate-100 flex items-center gap-4">
            <img src="{{ $cd->child->avatar }}" class="w-12 h-12 rounded-full object-cover border-2 border-white shadow" alt="">
            <div class="flex-1 min-w-0">
                <p class="font-bold text-slate-800">{{ $cd->child->full_name }}</p>
                <p class="text-xs text-slate-500">
                    {{ $cd->promotion?->schoolClass?->class_name ?? '—' }}
                    @if($cd->promotion?->section) / {{ $cd->promotion->section->section_name }} @endif
                </p>
            </div>
        </div>

        {{-- KPI stats — 5 cols --}}
        <div class="grid grid-cols-2 sm:grid-cols-5 divide-y sm:divide-y-0 sm:divide-x divide-slate-100">
            <a href="{{ route('parent.attendance', $cd->child->id) }}"
               class="px-4 py-4 text-center hover:bg-slate-50 transition">
                <p class="text-2xl font-bold {{ $cd->pct >= 75 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $cd->pct }}%</p>
                <p class="text-xs text-slate-400 mt-0.5">Attendance</p>
                <p class="text-[11px] text-slate-400">{{ $cd->present }}/{{ $cd->total }}</p>
            </a>
            <a href="{{ route('parent.fees', $cd->child->id) }}"
               class="px-4 py-4 text-center hover:bg-slate-50 transition">
                <p class="text-2xl font-bold {{ $cd->invoices > 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $cd->invoices }}</p>
                <p class="text-xs text-slate-400 mt-0.5">Unpaid Fees</p>
                <p class="text-[11px] text-indigo-500">View invoices</p>
            </a>
            <a href="{{ route('parent.results', $cd->child->id) }}"
               class="px-4 py-4 text-center hover:bg-slate-50 transition">
                <p class="text-2xl font-bold text-blue-600">{{ $cd->marks->count() }}</p>
                <p class="text-xs text-slate-400 mt-0.5">Marks</p>
                <p class="text-[11px] text-indigo-500">View results</p>
            </a>
            <a href="{{ route('parent.homework', $cd->child->id) }}"
               class="px-4 py-4 text-center hover:bg-slate-50 transition">
                <p class="text-2xl font-bold {{ $cd->homeworkDue > 0 ? 'text-amber-600' : 'text-slate-400' }}">{{ $cd->homeworkDue }}</p>
                <p class="text-xs text-slate-400 mt-0.5">HW Due</p>
                <p class="text-[11px] text-indigo-500">View homework</p>
            </a>
            <a href="{{ route('parent.timetable', $cd->child->id) }}"
               class="px-4 py-4 text-center hover:bg-slate-50 transition">
                <p class="text-2xl font-bold text-violet-600">{{ $cd->upcomingExams->count() }}</p>
                <p class="text-xs text-slate-400 mt-0.5">Exams Soon</p>
                <p class="text-[11px] text-indigo-500">Timetable</p>
            </a>
        </div>

        {{-- Attendance progress bar --}}
        <div class="px-5 pt-1 pb-3 border-t border-slate-50">
            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full {{ $cd->pct >= 75 ? 'bg-emerald-500' : 'bg-rose-500' }}"
                     style="width:{{ $cd->pct }}%"></div>
            </div>
            @if($cd->pct < 75)
            <p class="text-xs text-rose-500 mt-1">
                <i class="bi bi-exclamation-triangle-fill"></i> Attendance below 75% — at risk of shortage
            </p>
            @endif
        </div>

        {{-- Upcoming exams strip --}}
        @if($cd->upcomingExams->count())
        <div class="px-5 py-3 border-t border-slate-50">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">
                <i class="bi bi-file-earmark-text me-1 text-rose-400"></i>Upcoming Exams
            </p>
            <div class="flex flex-wrap gap-2">
                @foreach($cd->upcomingExams as $exam)
                <span class="inline-flex items-center gap-1.5 text-xs bg-rose-50 text-rose-700 border border-rose-100 px-2.5 py-1 rounded-lg">
                    <span class="font-semibold">{{ $exam->exam_name }}</span>
                    <span class="text-rose-400">·</span>
                    {{ \Carbon\Carbon::parse($exam->start_date)->format('M d') }}
                </span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Recent marks --}}
        @if($cd->marks->count())
        <div class="px-5 py-3 border-t border-slate-50">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">Recent Results</p>
            <div class="flex flex-wrap gap-2">
                @foreach($cd->marks as $mark)
                <span class="text-xs bg-slate-100 text-slate-700 px-2.5 py-1 rounded-lg font-medium">
                    {{ $mark->exam?->exam_name ?? 'Exam' }}:
                    <span class="font-bold text-slate-900">{{ $mark->marks }}</span>
                </span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Quick action bar --}}
        <div class="px-5 py-3 border-t border-slate-50 bg-slate-50/50 flex flex-wrap gap-2">
            <a href="{{ route('parent.homework',    $cd->child->id) }}" class="text-xs px-3 py-1.5 bg-white hover:bg-indigo-50 text-slate-600 hover:text-indigo-700 rounded-lg border border-slate-200 transition">
                <i class="bi bi-pencil-fill me-1"></i>Homework
            </a>
            <a href="{{ route('parent.timetable',   $cd->child->id) }}" class="text-xs px-3 py-1.5 bg-white hover:bg-indigo-50 text-slate-600 hover:text-indigo-700 rounded-lg border border-slate-200 transition">
                <i class="bi bi-calendar4-week me-1"></i>Timetable
            </a>
            <a href="{{ route('parent.assignments', $cd->child->id) }}" class="text-xs px-3 py-1.5 bg-white hover:bg-indigo-50 text-slate-600 hover:text-indigo-700 rounded-lg border border-slate-200 transition">
                <i class="bi bi-file-earmark-text me-1"></i>Assignments
            </a>
            <a href="{{ route('parent.transport',   $cd->child->id) }}" class="text-xs px-3 py-1.5 bg-white hover:bg-indigo-50 text-slate-600 hover:text-indigo-700 rounded-lg border border-slate-200 transition">
                <i class="bi bi-bus-front-fill me-1"></i>Transport
            </a>
            <a href="{{ route('parent.leave',       $cd->child->id) }}" class="text-xs px-3 py-1.5 bg-white hover:bg-indigo-50 text-slate-600 hover:text-indigo-700 rounded-lg border border-slate-200 transition">
                <i class="bi bi-calendar-x me-1"></i>Apply Leave
            </a>
            <a href="{{ route('parent.performance', $cd->child->id) }}" class="text-xs px-3 py-1.5 bg-white hover:bg-indigo-50 text-slate-600 hover:text-indigo-700 rounded-lg border border-slate-200 transition">
                <i class="bi bi-graph-up me-1"></i>Performance
            </a>
        </div>
    </div>
    @endforeach

    {{-- Bottom row: Notices + Messages --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Notice Board --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-700"><i class="bi bi-megaphone me-1 text-amber-500"></i>School Notices</p>
            </div>
            @if($notices->count())
            <div class="divide-y divide-slate-50">
                @foreach($notices->take(5) as $notice)
                <div class="px-5 py-3 text-sm text-slate-600 line-clamp-2">
                    {!! \Stevebauman\Purify\Facades\Purify::clean(strip_tags($notice->notice)) !!}
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-slate-400 text-center py-6">No notices.</p>
            @endif
        </div>

        {{-- Recent conversations --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-700">
                    <i class="bi bi-chat-dots-fill me-1 text-indigo-500"></i>Teacher Messages
                    @if($unreadMessages > 0)
                    <span class="ml-1 text-[10px] bg-rose-500 text-white px-1.5 py-0.5 rounded-full">{{ $unreadMessages }} new</span>
                    @endif
                </p>
                <a href="{{ route('parent.conversations') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
            </div>
            @php
                try {
                    $recentConvos = \App\Models\Conversation::where('parent_id', $parent->id)
                        ->with(['teacher','latestMessage'])
                        ->withCount(['messages as um' => fn($q) => $q->where('sender_id', '!=', $parent->id)->whereNull('read_at')])
                        ->orderByDesc('updated_at')->take(4)->get();
                } catch(\Throwable $e) { $recentConvos = collect(); }
            @endphp
            @if($recentConvos->count())
            <div class="divide-y divide-slate-50">
                @foreach($recentConvos as $convo)
                <a href="{{ route('parent.conversation.show', $convo->id) }}"
                   class="px-5 py-3 flex items-start gap-3 hover:bg-slate-50 transition block">
                    <img src="{{ $convo->teacher->avatar ?? '' }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0 mt-0.5" alt="">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-700 truncate {{ $convo->um > 0 ? 'font-semibold' : '' }}">
                            {{ $convo->subject }}
                        </p>
                        <p class="text-xs text-slate-400 truncate">
                            {{ $convo->teacher->full_name ?? '—' }}
                            @if($convo->latestMessage)
                            · {{ $convo->latestMessage->created_at->diffForHumans() }}
                            @endif
                        </p>
                    </div>
                    @if($convo->um > 0)
                    <span class="flex-shrink-0 text-[10px] bg-rose-500 text-white font-bold px-1.5 py-0.5 rounded-full">{{ $convo->um }}</span>
                    @endif
                </a>
                @endforeach
            </div>
            @else
            <div class="px-5 py-8 text-center">
                <p class="text-sm text-slate-400">No messages yet.</p>
                <a href="{{ route('parent.conversation.new') }}"
                   class="mt-3 inline-flex items-center gap-1.5 text-xs text-indigo-600 hover:underline">
                    <i class="bi bi-plus-lg"></i> Start a conversation
                </a>
            </div>
            @endif
        </div>

    </div>

</div>
</div>
@endsection
