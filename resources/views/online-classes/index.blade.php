@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Online Classes</h1>
                <p class="text-slate-400 text-sm mt-0.5">Google Meet, Zoom &amp; Teams sessions</p>
            </div>
            @can('create online classes')
            <a href="{{ route('online-classes.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                <i class="bi bi-plus-lg"></i> Schedule Class
            </a>
            @endcan
        </div>

        @include('session-messages')

        {{-- Status filter tabs --}}
        <div class="flex gap-2 mb-6 flex-wrap">
            @foreach(['all' => 'All', 'scheduled' => 'Scheduled', 'live' => 'Live', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $val => $label)
            <a href="{{ route('online-classes.index', ['status' => $val === 'all' ? null : $val]) }}"
               class="px-4 py-2 rounded-lg text-xs font-medium transition
                   {{ (request('status', 'all') === $val || ($val === 'all' && !request('status')))
                       ? 'bg-indigo-600 text-white'
                       : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>

        @php
            $platformLogos = ['google_meet' => '🟢', 'zoom' => '🔵', 'teams' => '🟣', 'custom' => '⚪'];
            $statusColors  = ['scheduled' => 'bg-amber-100 text-amber-700', 'live' => 'bg-rose-100 text-rose-700 animate-pulse', 'completed' => 'bg-emerald-100 text-emerald-700', 'cancelled' => 'bg-slate-100 text-slate-500'];
        @endphp

        <div class="space-y-3">
            @forelse($onlineClasses as $oc)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:shadow-md transition">
                <div class="flex flex-wrap justify-between items-start gap-3">
                    <div class="flex gap-3 items-start flex-1 min-w-0">
                        <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center flex-shrink-0 text-lg">
                            {{ $platformLogos[$oc->platform] ?? '⚪' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <h3 class="font-semibold text-slate-800">{{ $oc->title }}</h3>
                                <span class="text-[11px] px-2 py-0.5 rounded-full font-medium {{ $statusColors[$oc->status] ?? '' }}">
                                    {{ $oc->status === 'live' ? '🔴 LIVE' : ucfirst($oc->status) }}
                                </span>
                            </div>
                            <div class="flex flex-wrap gap-4 text-xs text-slate-500">
                                <span><i class="bi bi-book me-1"></i>{{ $oc->course->course_name ?? '—' }}</span>
                                <span><i class="bi bi-layers me-1"></i>{{ $oc->schoolClass->class_name ?? '—' }}</span>
                                <span><i class="bi bi-person me-1"></i>{{ $oc->teacher->full_name ?? '—' }}</span>
                                <span><i class="bi bi-calendar-event me-1"></i>{{ $oc->scheduled_at->format('M d, Y') }}</span>
                                <span><i class="bi bi-clock me-1"></i>{{ $oc->scheduled_at->format('H:i') }} ({{ $oc->duration_minutes }}min)</span>
                                <span class="capitalize"><i class="bi bi-camera-video me-1"></i>{{ str_replace('_', ' ', $oc->platform) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-2 flex-shrink-0 items-center">
                        @if(in_array($oc->status, ['scheduled', 'live']))
                        <a href="{{ $oc->meeting_url }}" target="_blank"
                           class="inline-flex items-center gap-1.5 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition">
                            <i class="bi bi-camera-video-fill"></i> Join
                        </a>
                        @elseif($oc->recording_url)
                        <a href="{{ $oc->recording_url }}" target="_blank"
                           class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-medium rounded-lg transition">
                            <i class="bi bi-play-circle"></i> Recording
                        </a>
                        @endif

                        @can('create online classes')
                        @if($oc->teacher_id === auth()->id())
                        <a href="{{ route('online-classes.edit', $oc->id) }}"
                           class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition">
                            <i class="bi bi-pencil text-sm"></i>
                        </a>
                        <form action="{{ route('online-classes.destroy', $oc->id) }}" method="POST" onsubmit="return confirm('Delete this class?')">
                            @csrf @method('DELETE')
                            <button class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                <i class="bi bi-trash text-sm"></i>
                            </button>
                        </form>
                        @endif
                        @endcan
                    </div>
                </div>

                @if($oc->description)
                <p class="mt-2 text-xs text-slate-400 ml-13">{{ $oc->description }}</p>
                @endif

                @if($oc->meeting_id || $oc->meeting_password)
                <div class="mt-2 flex gap-4 text-xs text-slate-500 ml-13">
                    @if($oc->meeting_id)<span><i class="bi bi-hash me-0.5"></i>ID: {{ $oc->meeting_id }}</span>@endif
                    @if($oc->meeting_password)<span><i class="bi bi-key me-0.5"></i>Password: {{ $oc->meeting_password }}</span>@endif
                </div>
                @endif
            </div>
            @empty
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
                <i class="bi bi-camera-video text-5xl text-slate-200"></i>
                <p class="mt-3 text-slate-400">No online classes scheduled.</p>
                @can('create online classes')
                <a href="{{ route('online-classes.create') }}" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                    <i class="bi bi-plus-lg"></i> Schedule First Class
                </a>
                @endcan
            </div>
            @endforelse
        </div>
        <div class="mt-4">{{ $onlineClasses->links() }}</div>
    </div>
</div>
@endsection
