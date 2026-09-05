@extends('layouts.app')

@section('title', 'Live Quiz Arena')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-7xl mx-auto">

    {{-- ── Hero Banner ────────────────────────────────────────────────────── --}}
    <div class="relative overflow-hidden rounded-3xl mb-8 p-8 md:p-10"
         style="background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);">

        {{-- Animated background glow orbs --}}
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div style="position:absolute;top:-60px;left:-60px;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(139,92,246,0.25) 0%,transparent 70%);"></div>
            <div style="position:absolute;bottom:-80px;right:-40px;width:350px;height:350px;border-radius:50%;background:radial-gradient(circle,rgba(245,158,11,0.15) 0%,transparent 70%);"></div>
        </div>

        <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold mb-4"
                     style="background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.3);color:#fbbf24;">
                    <span class="inline-block w-2 h-2 rounded-full bg-yellow-400 animate-pulse"></span>
                    LIVE COMPETITION ARENA
                </div>
                <h1 class="text-4xl md:text-5xl font-black text-white mb-3 leading-tight">
                    School Quiz<br><span style="color:#a78bfa;">Championship</span>
                </h1>
                <p class="text-slate-300 text-base max-w-lg mb-6 leading-relaxed">
                    Real-time group battles. Students connect with a 6-digit PIN, race the countdown, and compete live on the big screen.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('live-quiz.create') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm text-slate-900 shadow-lg transition hover:scale-105"
                       style="background:linear-gradient(135deg,#fbbf24,#f59e0b);">
                        <i class="bi bi-plus-circle-fill"></i> Create New Quiz
                    </a>
                    <form action="{{ route('live-quiz.seed-demo') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm transition hover:scale-105"
                                style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);color:#fff;">
                            <i class="bi bi-magic"></i> Load Demo Quiz
                        </button>
                    </form>
                    <a href="{{ route('live-quiz.player.join') }}" target="_blank"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm transition hover:scale-105"
                       style="background:rgba(139,92,246,0.2);border:1px solid rgba(139,92,246,0.4);color:#c4b5fd;">
                        <i class="bi bi-phone"></i> Player Screen
                    </a>
                </div>
            </div>

            {{-- Stats mini cards --}}
            <div class="flex gap-4 flex-shrink-0">
                <div class="text-center px-6 py-4 rounded-2xl" style="background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.1);">
                    <div class="text-3xl font-black" style="color:#fbbf24;">{{ $quizzes->total() }}</div>
                    <div class="text-xs text-slate-400 font-semibold mt-1">Quizzes</div>
                </div>
                <div class="text-center px-6 py-4 rounded-2xl" style="background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.1);">
                    <div class="text-3xl font-black" style="color:#34d399;">Live</div>
                    <div class="text-xs text-slate-400 font-semibold mt-1">Leaderboard</div>
                </div>
            </div>
        </div>
    </div>

    @include('session-messages')

    {{-- ── Quiz Grid ───────────────────────────────────────────────────────── --}}
    @if($quizzes->isEmpty())
    <div class="text-center py-20 rounded-3xl border-2 border-dashed border-slate-200 bg-white">
        <i class="bi bi-broadcast text-6xl" style="color:#a78bfa;"></i>
        <h3 class="text-xl font-bold text-slate-700 mt-4 mb-2">No Quizzes Yet</h3>
        <p class="text-slate-400 text-sm max-w-sm mx-auto mb-6">Create your first live quiz competition or load the demo to test the arena experience.</p>
        <a href="{{ route('live-quiz.create') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-bold text-sm text-white" style="background:#6366f1;">
            <i class="bi bi-plus-lg"></i> Create First Quiz
        </a>
    </div>
    @else

    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Quiz Repository</h2>
            <p class="text-xs text-slate-400 mt-0.5">{{ $quizzes->total() }} competition{{ $quizzes->total() !== 1 ? 's' : '' }} ready to host</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @foreach($quizzes as $quiz)
        <div class="quiz-card bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex flex-col transition-all duration-200 hover:-translate-y-1 hover:shadow-lg">

            {{-- Coloured top stripe based on mode --}}
            <div class="h-1.5 w-full" style="background:{{ $quiz->mode === 'buzzer' ? 'linear-gradient(90deg,#ef4444,#f59e0b)' : 'linear-gradient(90deg,#6366f1,#8b5cf6)' }};"></div>

            <div class="p-5 flex flex-col flex-1">
                {{-- Category + mode badges --}}
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full" style="background:#ede9fe;color:#6d28d9;">
                        <i class="bi bi-tag-fill me-1"></i>{{ $quiz->category ?: 'General' }}
                    </span>
                    @if($quiz->mode === 'buzzer')
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full" style="background:#fef2f2;color:#dc2626;">
                        <i class="bi bi-lightning-charge-fill me-1"></i>BUZZER
                    </span>
                    @elseif($quiz->mode === 'direct')
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full" style="background:#fff7ed;color:#c2410c;">
                        <i class="bi bi-person-video3 me-1"></i>DIRECT
                    </span>
                    @else
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full" style="background:#f0fdf4;color:#16a34a;">
                        <i class="bi bi-grid-3x3-gap-fill me-1"></i>MCQ
                    </span>
                    @endif
                </div>

                <h3 class="font-bold text-slate-800 text-base mb-1 leading-snug">{{ $quiz->title }}</h3>
                <p class="text-xs text-slate-400 mb-4 flex-1" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                    {{ $quiz->description ?: 'Interactive classroom challenge for school teams.' }}
                </p>

                {{-- Stats row --}}
                <div class="flex items-center gap-3 text-xs text-slate-500 mb-4">
                    <span class="flex items-center gap-1 bg-slate-50 px-2 py-1 rounded-lg">
                        <i class="bi bi-question-circle text-indigo-500"></i>
                        <strong>{{ $quiz->questions_count }}</strong> Qs
                    </span>
                    <span class="flex items-center gap-1 bg-slate-50 px-2 py-1 rounded-lg">
                        <i class="bi bi-stopwatch text-amber-500"></i>
                        {{ $quiz->default_time_limit }}s
                    </span>
                    <span class="flex items-center gap-1 bg-slate-50 px-2 py-1 rounded-lg">
                        <i class="bi bi-lightning {{ $quiz->speed_bonus ? 'text-yellow-500' : 'text-slate-300' }}"></i>
                        {{ $quiz->speed_bonus ? 'Bonus ON' : 'Standard' }}
                    </span>
                </div>

                {{-- Action buttons --}}
                <div class="flex gap-2 mt-auto pt-3 border-t border-slate-100">
                    <a href="{{ route('live-quiz.host.create-session', $quiz->id) }}"
                       class="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl font-bold text-sm text-white transition hover:opacity-90"
                       style="background:linear-gradient(135deg,#4f46e5,#7c3aed);">
                        <i class="bi bi-broadcast"></i> Host Live
                    </a>
                    <a href="{{ route('live-quiz.show', $quiz->id) }}"
                       class="flex items-center justify-center w-10 rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition"
                       title="Edit Quiz">
                        <i class="bi bi-pencil-square text-sm"></i>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-6">{{ $quizzes->links() }}</div>
    @endif

</div>
@endsection
