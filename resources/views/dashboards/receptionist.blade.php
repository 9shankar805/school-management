@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="mb-7">
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Receptionist Dashboard</h1>
            <p class="text-slate-400 text-sm mt-0.5">{{ now()->format('l, F j, Y') }} &middot; Welcome, {{ auth()->user()->first_name }}</p>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Students</p>
                    <span class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 text-sm"><i class="bi bi-people-fill"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ number_format($studentCount) }}</p>
                <p class="mt-1 text-xs text-indigo-600">Enrolled</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Teachers</p>
                    <span class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 text-sm"><i class="bi bi-person-badge-fill"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ number_format($teacherCount) }}</p>
                <p class="mt-1 text-xs text-blue-600">On staff</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Notices</p>
                    <span class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600 text-sm"><i class="bi bi-megaphone"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ $notices->count() }}</p>
                <p class="mt-1 text-xs text-amber-600">Active</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Events</p>
                    <span class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center text-violet-600 text-sm"><i class="bi bi-calendar-event"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ $upcomingEvents->count() }}</p>
                <p class="mt-1 text-xs text-violet-600">Upcoming</p>
            </div>
        </div>

        {{-- Upcoming Events + Notices --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-calendar-event me-1 text-violet-500"></i>Upcoming Events</p>
                    @can('view events')
                    <a href="{{ route('events.show') }}" class="text-xs text-indigo-600 hover:underline">View calendar</a>
                    @endcan
                </div>
                @if($upcomingEvents->count())
                <div class="divide-y divide-slate-50">
                    @foreach($upcomingEvents as $event)
                    <div class="px-5 py-3 flex justify-between items-center">
                        <p class="text-sm text-slate-700">{{ $event->title }}</p>
                        <span class="text-xs text-violet-700 bg-violet-50 px-2 py-1 rounded-lg flex-shrink-0 ml-3">
                            {{ \Carbon\Carbon::parse($event->start)->format('M d, Y') }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-8">No upcoming events.</p>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-megaphone me-1 text-amber-500"></i>Notice Board</p>
                </div>
                @if($notices->count())
                <div class="divide-y divide-slate-50">
                    @foreach($notices->take(8) as $notice)
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
</div>
@endsection
