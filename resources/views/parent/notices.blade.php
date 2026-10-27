@extends('layouts.app')
@section('title', 'Notices & Circulars — ' . $child->full_name)

@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">

    <nav class="text-xs text-slate-400 mb-4">
        <a href="{{ url('home') }}" class="hover:text-indigo-600">Dashboard</a>
        <span class="mx-1">/</span>
        <span class="text-slate-600">{{ $child->full_name }} — Notice Board</span>
    </nav>

    <div class="flex flex-wrap justify-between items-start mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">School Notices & Circulars</h1>
            <p class="text-slate-400 text-sm mt-0.5">Official announcements, holidays, and event schedules for parents</p>
        </div>
        <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-full border border-slate-200 shadow-sm">
            <img src="{{ $child->avatar }}" class="w-7 h-7 rounded-full object-cover border border-indigo-100" alt="">
            <span class="text-xs font-semibold text-slate-700">{{ $child->full_name }}</span>
        </div>
    </div>

    @include('session-messages')

    @if($notices->isEmpty())
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-14 text-center">
        <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 inline-flex items-center justify-center mb-3">
            <i class="bi bi-megaphone text-3xl"></i>
        </div>
        <p class="text-slate-600 font-semibold">No active circulars at this moment.</p>
        <p class="text-slate-400 text-xs mt-1">Check back later for school announcements and holiday updates.</p>
    </div>
    @else
    <div class="space-y-4">
        @foreach($notices as $ntc)
        @php
            $priorityStyle = match($ntc->priority) {
                'urgent'  => 'border-l-4 border-l-rose-500 bg-rose-50/20',
                'holiday' => 'border-l-4 border-l-emerald-500 bg-emerald-50/20',
                'exam'    => 'border-l-4 border-l-purple-500 bg-purple-50/20',
                default   => 'border-l-4 border-l-indigo-500 bg-white',
            };
        @endphp
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 {{ $priorityStyle }}">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full
                        {{ $ntc->priority === 'urgent' ? 'bg-rose-100 text-rose-700' : ($ntc->priority === 'holiday' ? 'bg-emerald-100 text-emerald-700' : ($ntc->priority === 'exam' ? 'bg-purple-100 text-purple-700' : 'bg-indigo-100 text-indigo-700')) }}">
                        {{ ucfirst($ntc->priority ?? 'normal') }}
                    </span>
                    <span class="text-xs text-slate-400">
                        <i class="bi bi-calendar-event me-1"></i>{{ $ntc->created_at->format('M d, Y') }}
                    </span>
                </div>
                @if($ntc->expiry_date)
                <span class="text-[11px] text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-0.5 rounded-full font-medium">
                    Valid till: {{ $ntc->expiry_date->format('M d, Y') }}
                </span>
                @endif
            </div>

            <h3 class="font-bold text-slate-800 text-lg mb-2">
                {{ $ntc->title ?: 'Notice Announcement' }}
            </h3>

            <div class="text-slate-600 text-sm prose max-w-none mb-4">
                {!! $ntc->notice !!}
            </div>

            @if($ntc->attachment_path)
            <div class="mt-2">
                <a href="{{ asset('storage/' . $ntc->attachment_path) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-lg hover:bg-indigo-100 transition">
                    <i class="bi bi-paperclip"></i> View Official Circular Document
                </a>
            </div>
            @endif
        </div>
        @endforeach

        <div class="mt-4">
            {{ $notices->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
