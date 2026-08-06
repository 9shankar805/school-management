@extends('layouts.app')
@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">

    <div class="flex flex-wrap justify-between items-start mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Online Exams & Quizzes</h1>
            <p class="text-slate-400 text-sm mt-0.5">Exams and practice quizzes available for you</p>
        </div>
    </div>

    @include('session-messages')

    {{-- Tabs --}}
    <div class="flex gap-1 bg-slate-100 p-1 rounded-xl w-fit mb-6">
        <button onclick="showTab('practice')" id="tab-practice"
                class="tab-btn px-5 py-2 rounded-lg text-sm font-medium transition bg-white shadow text-emerald-700">
            <i class="bi bi-lightning-fill me-1"></i>Practice Quizzes
            <span class="ml-1 text-[10px] bg-emerald-100 px-1.5 py-0.5 rounded-full">{{ $exams->where('is_practice', true)->count() }}</span>
        </button>
        <button onclick="showTab('formal')" id="tab-formal"
                class="tab-btn px-5 py-2 rounded-lg text-sm font-medium transition text-slate-500">
            <i class="bi bi-laptop-fill me-1"></i>Formal Exams
            <span class="ml-1 text-[10px] bg-slate-200 px-1.5 py-0.5 rounded-full">{{ $exams->where('is_practice', false)->count() }}</span>
        </button>
    </div>

    {{-- Practice Quizzes --}}
    <div id="panel-practice">
        @php $quizzes = $exams->where('is_practice', true); @endphp
        @if($quizzes->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($quizzes as $quiz)
            @php
                $attemptsUsed = $quiz->student_attempts->count();
                $lastAttempt  = $quiz->student_attempts->sortByDesc('started_at')->first();
            @endphp
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden hover:shadow-md transition">
                <div class="px-5 pt-5 pb-3">
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full uppercase tracking-wide">
                            <i class="bi bi-lightning-fill"></i> Practice
                        </span>
                        <span class="text-xs text-slate-400">{{ $quiz->duration_minutes }} min</span>
                    </div>
                    <h3 class="font-semibold text-slate-800 mb-1">{{ $quiz->title }}</h3>
                    <p class="text-xs text-slate-400 mb-3">
                        {{ $quiz->course?->course_name ?? 'General' }}
                        · {{ $quiz->questions()->count() }} questions
                        · {{ $quiz->total_marks }} marks
                    </p>
                    @if($lastAttempt && $lastAttempt->score !== null)
                    <div class="bg-slate-50 rounded-xl px-3 py-2 mb-3 flex items-center justify-between">
                        <span class="text-xs text-slate-500">Best score</span>
                        <span class="text-sm font-bold text-indigo-700">{{ $lastAttempt->score }}/{{ $quiz->total_marks }}</span>
                    </div>
                    @endif
                    @if($quiz->instructions)
                    <p class="text-xs text-slate-400 line-clamp-2 mb-3">{{ $quiz->instructions }}</p>
                    @endif
                </div>
                <div class="px-5 pb-5">
                    <a href="{{ route('online-exams.student.start', $quiz->id) }}"
                       class="w-full inline-flex items-center justify-center gap-2 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition">
                        <i class="bi bi-play-fill"></i>
                        {{ $attemptsUsed > 0 ? 'Practice Again' : 'Start Practice' }}
                    </a>
                    @if($attemptsUsed > 0)
                    <p class="text-[10px] text-center text-slate-400 mt-1.5">Attempted {{ $attemptsUsed }} time{{ $attemptsUsed > 1 ? 's' : '' }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-14 text-center">
            <i class="bi bi-lightning text-5xl text-slate-200 block mb-3"></i>
            <p class="text-slate-500 font-medium">No practice quizzes available yet.</p>
            <p class="text-slate-400 text-sm mt-1">Your teachers will publish quizzes for revision here.</p>
        </div>
        @endif
    </div>

    {{-- Formal Exams --}}
    <div id="panel-formal" class="hidden">
        @php $formalList = $exams->where('is_practice', false); @endphp
        @if($formalList->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($formalList as $exam)
            @php
                $attemptsUsed = $exam->student_attempts->count();
                $lastAttempt  = $exam->student_attempts->sortByDesc('started_at')->first();
                $canAttempt   = $attemptsUsed < $exam->max_attempts && $exam->is_available;
            @endphp
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden hover:shadow-md transition">
                <div class="px-5 pt-5 pb-3">
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full uppercase tracking-wide">
                            <i class="bi bi-laptop-fill"></i> Exam
                        </span>
                        <span class="text-xs text-slate-400">{{ $exam->duration_minutes }} min</span>
                    </div>
                    <h3 class="font-semibold text-slate-800 mb-1">{{ $exam->title }}</h3>
                    <p class="text-xs text-slate-400 mb-1">
                        {{ $exam->course?->course_name ?? 'General' }}
                        · {{ $exam->questions()->count() }} questions
                    </p>
                    <p class="text-xs text-slate-500 mb-3">
                        Total: <span class="font-semibold">{{ $exam->total_marks }}</span> marks
                        · Pass: <span class="font-semibold">{{ $exam->pass_marks }}</span>
                        · Attempts: {{ $attemptsUsed }}/{{ $exam->max_attempts }}
                    </p>
                    @if($lastAttempt && $lastAttempt->score !== null)
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $lastAttempt->passed ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                            {{ $lastAttempt->passed ? '✓ Passed' : '✗ Failed' }}
                        </span>
                        <span class="text-xs text-slate-500">{{ $lastAttempt->score }}/{{ $exam->total_marks }} ({{ $lastAttempt->percentage }}%)</span>
                    </div>
                    @elseif($lastAttempt && $lastAttempt->score === null)
                    <span class="text-xs bg-amber-100 text-amber-700 px-2.5 py-1 rounded-full mb-3 inline-block">Pending grade</span>
                    @endif
                    @if($exam->instructions)
                    <p class="text-xs text-slate-400 line-clamp-2">{{ $exam->instructions }}</p>
                    @endif
                </div>
                <div class="px-5 pb-5">
                    @if($canAttempt)
                    <a href="{{ route('online-exams.student.start', $exam->id) }}"
                       class="w-full inline-flex items-center justify-center gap-2 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">
                        <i class="bi bi-play-fill"></i>
                        {{ $attemptsUsed > 0 ? 'Retry Exam' : 'Start Exam' }}
                    </a>
                    @elseif($attemptsUsed >= $exam->max_attempts)
                    <button disabled class="w-full py-2.5 bg-slate-100 text-slate-400 rounded-xl text-sm font-medium cursor-not-allowed">
                        All Attempts Used
                    </button>
                    @else
                    <button disabled class="w-full py-2.5 bg-slate-100 text-slate-400 rounded-xl text-sm font-medium cursor-not-allowed">
                        Not Available
                    </button>
                    @endif
                    @if($lastAttempt)
                    <a href="{{ route('online-exams.student.result', $lastAttempt->id) }}"
                       class="w-full mt-2 inline-flex items-center justify-center gap-1 py-1.5 text-xs text-indigo-600 hover:underline">
                        View last result →
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-14 text-center">
            <i class="bi bi-laptop text-5xl text-slate-200 block mb-3"></i>
            <p class="text-slate-500 font-medium">No online exams scheduled.</p>
        </div>
        @endif
    </div>

</div>
</div>

<script>
function showTab(name) {
    ['practice','formal'].forEach(t => {
        document.getElementById('panel-'+t).classList.toggle('hidden', t !== name);
        const btn = document.getElementById('tab-'+t);
        if (t === name) {
            btn.classList.add('bg-white','shadow');
            btn.classList.remove('text-slate-500');
            btn.classList.add(t === 'practice' ? 'text-emerald-700' : 'text-indigo-700');
        } else {
            btn.classList.remove('bg-white','shadow','text-emerald-700','text-indigo-700');
            btn.classList.add('text-slate-500');
        }
    });
}
</script>
@endsection
