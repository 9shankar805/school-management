@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="mb-7">
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Parent Dashboard</h1>
            <p class="text-slate-400 text-sm mt-0.5">{{ now()->format('l, F j, Y') }} &middot; Welcome, <span class="font-medium text-slate-600">{{ $parent->first_name }}</span></p>
        </div>

        {{-- No children linked --}}
        @if($children->isEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 mb-6 flex items-start gap-3">
            <i class="bi bi-exclamation-triangle-fill text-amber-500 text-lg mt-0.5"></i>
            <div>
                <p class="font-semibold text-amber-800 text-sm">No children linked to your account</p>
                <p class="text-amber-700 text-xs mt-1">Please contact the school administration to link your child's profile to this account.</p>
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
                        @php
                            $promo = \App\Models\Promotion::where('student_id', $cd->child->id)->with('schoolClass','section')->latest()->first();
                        @endphp
                        {{ $promo?->schoolClass?->name ?? '—' }}
                        @if($promo?->section) / {{ $promo->section->name }} @endif
                    </p>
                </div>
                <a href="{{ route('parent.attendance', $cd->child->id) }}" class="text-xs text-indigo-600 hover:underline flex-shrink-0">View Details</a>
            </div>

            {{-- Stats row --}}
            <div class="grid grid-cols-3 divide-x divide-slate-100">
                <a href="{{ route('parent.attendance', $cd->child->id) }}" class="px-5 py-4 text-center hover:bg-slate-50 transition">
                    <p class="text-2xl font-bold {{ $cd->pct >= 75 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $cd->pct }}%</p>
                    <p class="text-xs text-slate-400 mt-0.5">Attendance</p>
                    <p class="text-xs text-slate-400">{{ $cd->present }}/{{ $cd->total }} classes</p>
                </a>
                <a href="{{ route('parent.fees', $cd->child->id) }}" class="px-5 py-4 text-center hover:bg-slate-50 transition">
                    <p class="text-2xl font-bold {{ $cd->invoices > 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $cd->invoices }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">Unpaid Fees</p>
                    <p class="text-xs text-indigo-500">View invoices</p>
                </a>
                <a href="{{ route('parent.results', $cd->child->id) }}" class="px-5 py-4 text-center hover:bg-slate-50 transition">
                    <p class="text-2xl font-bold text-blue-600">{{ $cd->marks->count() }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">Marks Recorded</p>
                    <p class="text-xs text-indigo-500">View results</p>
                </a>
            </div>

            {{-- Attendance progress bar --}}
            <div class="px-5 pb-2">
                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full {{ $cd->pct >= 75 ? 'bg-emerald-500' : 'bg-rose-500' }}" style="width:{{ $cd->pct }}%"></div>
                </div>
                @if($cd->pct < 75)
                <p class="text-xs text-rose-500 mt-1"><i class="bi bi-exclamation-triangle-fill"></i> Attendance below 75% — at risk of shortage</p>
                @endif
            </div>

            {{-- Recent marks --}}
            @if($cd->marks->count())
            <div class="px-5 py-3 border-t border-slate-50">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">Recent Results</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($cd->marks as $mark)
                    <span class="text-xs bg-slate-100 text-slate-700 px-2.5 py-1 rounded-lg font-medium">
                        {{ $mark->exam?->name ?? 'Exam' }}: <span class="font-bold text-slate-900">{{ $mark->marks }}</span>
                    </span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Quick action links --}}
            <div class="px-5 py-3 border-t border-slate-50 flex flex-wrap gap-2">
                <a href="{{ route('parent.assignments', $cd->child->id) }}" class="text-xs px-3 py-1.5 bg-slate-50 hover:bg-indigo-50 text-slate-600 hover:text-indigo-700 rounded-lg border border-slate-200 transition">
                    <i class="bi bi-file-earmark-text me-1"></i>Assignments
                </a>
                <a href="{{ route('parent.leave', $cd->child->id) }}" class="text-xs px-3 py-1.5 bg-slate-50 hover:bg-indigo-50 text-slate-600 hover:text-indigo-700 rounded-lg border border-slate-200 transition">
                    <i class="bi bi-calendar-x me-1"></i>Apply Leave
                </a>
                <a href="{{ route('parent.performance', $cd->child->id) }}" class="text-xs px-3 py-1.5 bg-slate-50 hover:bg-indigo-50 text-slate-600 hover:text-indigo-700 rounded-lg border border-slate-200 transition">
                    <i class="bi bi-graph-up me-1"></i>Performance
                </a>
            </div>
        </div>
        @endforeach

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

    </div>
</div>
@endsection
