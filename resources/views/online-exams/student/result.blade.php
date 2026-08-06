@extends('layouts.app')
@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto max-w-3xl mx-auto">

    @include('session-messages')

    {{-- Result banner --}}
    @php
        $isPractice = $attempt->onlineExam->is_practice;
        $passed     = $attempt->passed;
        $pending    = $attempt->score === null;
    @endphp
    <div class="rounded-2xl border shadow-sm p-8 text-center mb-6
        {{ $isPractice ? 'bg-emerald-50 border-emerald-100' : ($pending ? 'bg-amber-50 border-amber-100' : ($passed ? 'bg-emerald-50 border-emerald-100' : 'bg-rose-50 border-rose-100')) }}">

        <div class="text-5xl mb-3">{{ $isPractice ? '⚡' : ($pending ? '⏳' : ($passed ? '🎉' : '📝')) }}</div>
        <h1 class="text-2xl font-bold text-slate-800 mb-1">
            @if($isPractice)
                Practice Complete!
            @elseif($pending)
                Submitted — Awaiting Grade
            @elseif($passed)
                Congratulations, You Passed!
            @else
                Keep Practising!
            @endif
        </h1>
        <p class="text-slate-500 text-sm">{{ $attempt->onlineExam->title }}</p>

        {{-- Score cards --}}
        <div class="flex flex-wrap justify-center gap-4 mt-6">
            <div class="bg-white rounded-xl px-6 py-3 shadow-sm border border-slate-100 text-center">
                <p class="text-2xl font-bold text-indigo-700">
                    {{ $attempt->score !== null ? $attempt->score : '—' }}<span class="text-base font-normal text-slate-400">/{{ $attempt->onlineExam->total_marks }}</span>
                </p>
                <p class="text-xs text-slate-400 mt-0.5">Score</p>
            </div>
            @if($attempt->percentage !== null)
            <div class="bg-white rounded-xl px-6 py-3 shadow-sm border border-slate-100 text-center">
                <p class="text-2xl font-bold text-blue-600">{{ $attempt->percentage }}%</p>
                <p class="text-xs text-slate-400 mt-0.5">Percentage</p>
            </div>
            @endif
            @if(!$isPractice && !$pending)
            <div class="bg-white rounded-xl px-6 py-3 shadow-sm border border-slate-100 text-center">
                <p class="text-2xl font-bold {{ $passed ? 'text-emerald-600' : 'text-rose-600' }}">
                    {{ $passed ? 'PASS' : 'FAIL' }}
                </p>
                <p class="text-xs text-slate-400 mt-0.5">Result</p>
            </div>
            @endif
        </div>

        {{-- Progress bar --}}
        @if($attempt->percentage !== null)
        <div class="mt-4 max-w-xs mx-auto">
            <div class="h-2 bg-white/60 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-700
                    {{ $isPractice ? 'bg-emerald-500' : ($passed ? 'bg-emerald-500' : 'bg-rose-500') }}"
                     style="width:{{ min($attempt->percentage, 100) }}%"></div>
            </div>
            @if(!$isPractice)
            <p class="text-xs text-slate-500 mt-1">Pass mark: {{ $attempt->onlineExam->pass_marks }}/{{ $attempt->onlineExam->total_marks }}</p>
            @endif
        </div>
        @endif
    </div>

    {{-- Teacher feedback --}}
    @if($attempt->teacher_feedback)
    <div class="bg-blue-50 border border-blue-100 rounded-2xl px-5 py-4 mb-6 flex gap-3">
        <i class="bi bi-chat-quote-fill text-blue-500 text-lg mt-0.5 flex-shrink-0"></i>
        <div>
            <p class="text-xs font-semibold text-blue-700 mb-1">Teacher Feedback</p>
            <p class="text-sm text-blue-800">{{ $attempt->teacher_feedback }}</p>
        </div>
    </div>
    @endif

    {{-- Answer review --}}
    @if($attempt->onlineExam->show_result_immediately || $attempt->status === 'graded')
    <div class="space-y-4 mb-6">
        <h2 class="text-base font-semibold text-slate-700">Answer Review</h2>

        @foreach($attempt->onlineExam->questions as $i => $question)
        @php $answer = $attempt->answers->firstWhere('question_id', $question->id); @endphp
        <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white text-xs font-bold flex items-center justify-center">{{ $i+1 }}</span>
                    <span class="text-xs text-slate-500 capitalize">{{ str_replace('_',' ', $question->question_type) }}</span>
                </div>
                @if($answer && $answer->marks_awarded !== null)
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $answer->is_correct ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                    {{ $answer->marks_awarded }}/{{ $question->marks }} marks
                </span>
                @else
                <span class="text-xs text-slate-400">{{ $question->marks }} marks</span>
                @endif
            </div>
            <div class="px-5 py-4 space-y-3">
                <p class="text-sm font-medium text-slate-800">{{ $question->question_text }}</p>

                {{-- Student answer --}}
                @if($answer && ($answer->answer_text ?? '') !== '')
                <div class="bg-slate-50 rounded-xl px-4 py-2.5">
                    <p class="text-xs font-semibold text-slate-400 mb-1">Your answer</p>
                    @if($question->question_type === 'mcq' && $question->options)
                    <p class="text-sm text-slate-700">
                        {{ chr(65 + (int)$answer->answer_text) }}.
                        {{ $question->options[(int)$answer->answer_text] ?? $answer->answer_text }}
                    </p>
                    @else
                    <p class="text-sm text-slate-700">{{ $answer->answer_text }}</p>
                    @endif
                </div>
                @else
                <div class="bg-slate-50 rounded-xl px-4 py-2.5">
                    <p class="text-xs text-rose-500 italic">No answer provided</p>
                </div>
                @endif

                {{-- Correct answer --}}
                @if($question->isAutoGradable() && $question->correct_answer !== null)
                <div class="bg-emerald-50 rounded-xl px-4 py-2.5 flex gap-2 items-start">
                    <i class="bi bi-check-circle-fill text-emerald-500 mt-0.5 flex-shrink-0"></i>
                    <div>
                        <p class="text-xs font-semibold text-emerald-700 mb-0.5">Correct answer</p>
                        @if($question->question_type === 'mcq' && $question->options)
                        <p class="text-sm text-emerald-800">
                            {{ chr(65 + (int)$question->correct_answer) }}.
                            {{ $question->options[(int)$question->correct_answer] ?? '' }}
                        </p>
                        @else
                        <p class="text-sm text-emerald-800">{{ ucfirst($question->correct_answer) }}</p>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Explanation --}}
                @if($question->explanation)
                <div class="text-xs text-slate-500 bg-amber-50 rounded-xl px-4 py-2.5">
                    <span class="font-semibold text-amber-700">Explanation: </span>{{ $question->explanation }}
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @elseif(!$pending)
    <div class="bg-slate-50 border border-slate-100 rounded-2xl px-5 py-6 text-center text-sm text-slate-500 mb-6">
        <i class="bi bi-hourglass-split text-2xl text-slate-300 block mb-2"></i>
        Detailed review will be available once your teacher has graded the exam.
    </div>
    @endif

    {{-- Actions --}}
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('online-exams.student.index') }}"
           class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">
            <i class="bi bi-arrow-left"></i> Back to Exams
        </a>
        @if($attempt->onlineExam->is_practice)
        <a href="{{ route('online-exams.student.start', $attempt->online_exam_id) }}"
           class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition">
            <i class="bi bi-arrow-repeat"></i> Try Again
        </a>
        @endif
        <a href="{{ url('home') }}"
           class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-medium hover:bg-slate-50 transition">
            <i class="bi bi-house"></i> Dashboard
        </a>
    </div>

</div>
</div>
@endsection
