@extends('layouts.app')
@section('title', 'Virtual Live Classes (Zoom / Meet) — ' . $child->full_name)

@section('content')
<style>
  .pulse-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #ef4444;
    animation: pulse-ring 1.5s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
  }
  @keyframes pulse-ring {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
  }
</style>

<div class="flex-1 p-6 lg:p-8 overflow-auto">
    <nav class="text-xs text-slate-400 mb-4">
        <a href="{{ url('home') }}" class="hover:text-indigo-600">Dashboard</a>
        <span class="mx-1">/</span>
        <span class="text-slate-600">{{ $child->full_name }} — Virtual Classes</span>
    </nav>

    <div class="flex flex-wrap justify-between items-start mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Virtual Classrooms & Live Sessions</h1>
            <p class="text-slate-400 text-sm mt-0.5">Online Zoom, Google Meet & live lectures for {{ $child->first_name }}</p>
        </div>
        <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-full border border-slate-200 shadow-sm">
            <img src="{{ $child->avatar }}" class="w-7 h-7 rounded-full object-cover border border-indigo-100" alt="">
            <span class="text-xs font-semibold text-slate-700">{{ $child->full_name }}</span>
        </div>
    </div>

    @include('session-messages')

    @if(!$promotion)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-sm text-amber-800">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>No class/section linked for this academic session.
    </div>
    @elseif($classes->isEmpty())
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-14 text-center">
        <div class="w-16 h-16 rounded-full bg-blue-50 text-blue-600 inline-flex items-center justify-center mb-3">
            <i class="bi bi-camera-video text-3xl"></i>
        </div>
        <p class="text-slate-600 font-semibold">No live virtual classes scheduled right now.</p>
        <p class="text-slate-400 text-xs mt-1">When subject teachers schedule Zoom or Google Meet classes, they will appear here with 1-click join links.</p>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($classes as $c)
        @php
            $isLive = $c->status === 'live' || ($c->scheduled_at->isPast() && $c->scheduled_at->copy()->addMinutes($c->duration_minutes)->isFuture());
            $platformBadge = match($c->platform) {
                'zoom'        => 'bg-blue-50 text-blue-700 border-blue-200',
                'google_meet' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'teams'       => 'bg-purple-50 text-purple-700 border-purple-200',
                default       => 'bg-slate-50 text-slate-700 border-slate-200',
            };
        @endphp
        <div class="bg-white rounded-2xl border p-5 shadow-sm {{ $isLive ? 'border-rose-300 ring-2 ring-rose-500/10' : 'border-slate-100' }}">
            <div class="flex items-center justify-between gap-2 mb-3">
                <div class="flex items-center gap-2">
                    <span class="badge {{ $platformBadge }} text-xs px-2.5 py-1 rounded-full border font-semibold">
                        <i class="bi bi-camera-video me-1"></i>{{ ucfirst(str_replace('_', ' ', $c->platform)) }}
                    </span>
                    @if($isLive)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-700">
                        <span class="pulse-dot"></span> LIVE NOW
                    </span>
                    @else
                    <span class="text-xs text-slate-400">
                        <i class="bi bi-calendar3 me-1"></i>{{ $c->scheduled_at->format('M d, Y · h:i A') }}
                    </span>
                    @endif
                </div>
                <span class="text-xs text-slate-400 font-medium">
                    <i class="bi bi-clock me-1"></i>{{ $c->duration_minutes }} mins
                </span>
            </div>

            <h3 class="font-bold text-slate-800 text-base mb-1">{{ $c->title }}</h3>
            <p class="text-xs text-slate-500 mb-3">
                Subject: <strong>{{ $c->course->course_name ?? '—' }}</strong> · Teacher: {{ $c->teacher->full_name ?? 'Class Instructor' }}
            </p>

            @if($c->description)
            <p class="text-xs text-slate-600 mb-3 line-clamp-2">{{ $c->description }}</p>
            @endif

            @if($c->meeting_id || $c->meeting_password)
            <div class="bg-slate-50 rounded-xl p-2.5 border border-slate-200/80 mb-3 text-xs flex flex-wrap gap-3">
                @if($c->meeting_id)
                <span>Meeting ID: <strong class="font-mono text-slate-800">{{ $c->meeting_id }}</strong></span>
                @endif
                @if($c->meeting_password)
                <span>Passcode: <strong class="font-mono text-slate-800">{{ $c->meeting_password }}</strong></span>
                @endif
            </div>
            @endif

            <div class="d-flex items-center justify-between mt-3 pt-3 border-t border-slate-100">
                @if($c->recording_url)
                <a href="{{ $c->recording_url }}" target="_blank" class="text-xs text-indigo-600 font-semibold hover:underline">
                    <i class="bi bi-play-circle me-1"></i> Watch Recording
                </a>
                @else
                <span class="text-xs text-slate-400">Online Classroom Session</span>
                @endif

                <a href="{{ $c->meeting_url }}" target="_blank"
                   class="btn btn-sm {{ $isLive ? 'btn-danger bg-rose-600 hover:bg-rose-700 text-white font-bold' : 'btn-primary bg-indigo-600 text-white font-semibold' }} rounded-xl px-4 py-2">
                    <i class="bi bi-box-arrow-up-right me-1.5"></i> {{ $isLive ? 'Join Live Class Now' : 'Join Virtual Classroom' }}
                </a>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $classes->links() }}
    </div>
    @endif
</div>
@endsection
