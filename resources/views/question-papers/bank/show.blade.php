@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('question-bank.index') }}" class="hover:text-indigo-600">Question Bank</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">Question #{{ $question->id }}</span>
        </nav>

        @include('session-messages')

        <div class="max-w-3xl space-y-5">

            {{-- Header card --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <div class="flex flex-wrap justify-between items-start gap-4">
                    <div class="flex flex-wrap gap-2">
                        <span class="text-[11px] px-2.5 py-1 rounded-full font-semibold bg-indigo-100 text-indigo-700">
                            {{ \App\Models\QuestionBank::QUESTION_TYPES[$question->question_type] ?? $question->question_type }}
                        </span>
                        <span class="text-[11px] px-2.5 py-1 rounded-full font-semibold {{ $question->difficulty_badge }}">
                            {{ ucfirst($question->difficulty) }}
                        </span>
                        <span class="text-[11px] px-2.5 py-1 rounded-full font-semibold bg-amber-100 text-amber-700">
                            {{ $question->allocated_marks }} mark(s)
                        </span>
                        @if($question->bloom_taxonomy)
                        <span class="text-[11px] px-2.5 py-1 rounded-full bg-purple-100 text-purple-700">
                            {{ \App\Models\QuestionBank::BLOOM_LEVELS[$question->bloom_taxonomy] ?? $question->bloom_taxonomy }}
                        </span>
                        @endif
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        @can('create exams')
                        <a href="{{ route('question-bank.edit', $question->id) }}"
                           class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium transition">
                            <i class="bi bi-pencil me-1"></i> Edit
                        </a>
                        <form method="POST" action="{{ route('question-bank.duplicate', $question->id) }}">
                            @csrf
                            <button class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-medium transition">
                                <i class="bi bi-copy me-1"></i> Duplicate
                            </button>
                        </form>
                        <form method="POST" action="{{ route('question-bank.destroy', $question->id) }}"
                              onsubmit="return confirm('Delete this question from bank?')">
                            @csrf @method('DELETE')
                            <button class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg text-xs font-medium transition">
                                <i class="bi bi-trash me-1"></i> Delete
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>

                {{-- Question text --}}
                <div class="mt-4 text-sm text-slate-800 leading-relaxed">
                    {!! $question->question_text !!}
                </div>

                {{-- MCQ options --}}
                @if($question->question_type === 'mcq' && $question->options)
                <div class="mt-3 space-y-1.5">
                    @foreach($question->options as $i => $opt)
                    <div class="flex items-center gap-2 text-sm
                        {{ $question->correct_answer && strtoupper($question->correct_answer) === chr(65+$i) ? 'text-emerald-700 font-semibold' : 'text-slate-600' }}">
                        <span class="w-6 h-6 rounded-full border {{ $question->correct_answer && strtoupper($question->correct_answer) === chr(65+$i) ? 'bg-emerald-100 border-emerald-300' : 'bg-slate-100 border-slate-200' }} flex items-center justify-center text-xs font-bold flex-shrink-0">
                            {{ chr(65+$i) }}
                        </span>
                        {{ $opt }}
                        @if($question->correct_answer && strtoupper($question->correct_answer) === chr(65+$i))
                        <i class="bi bi-check-circle-fill text-emerald-500 text-xs ml-1"></i>
                        @endif
                    </div>
                    @endforeach
                </div>
                @elseif($question->question_type === 'true_false')
                <p class="mt-2 text-sm">
                    Answer: <strong class="text-emerald-700">{{ $question->correct_answer ?? '—' }}</strong>
                </p>
                @endif

                {{-- Images --}}
                @if($question->images->count())
                <div class="mt-4 flex flex-wrap gap-3">
                    @foreach($question->images as $img)
                    <a href="{{ asset('storage/' . $img->file_path) }}" target="_blank">
                        <img src="{{ asset('storage/' . $img->file_path) }}" class="w-32 h-32 object-cover rounded-xl border border-slate-200 hover:opacity-80 transition">
                    </a>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Metadata + Model Answer --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                    <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Metadata</h2>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-slate-400">Subject</dt><dd class="text-slate-700">{{ $question->subject ?: '—' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-400">Chapter</dt><dd class="text-slate-700">{{ $question->chapter ?: '—' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-400">Category</dt><dd class="text-slate-700">{{ $question->category?->name ?? '—' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-400">Created by</dt><dd class="text-slate-700">{{ $question->creator?->full_name ?? '—' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-400">Created</dt><dd class="text-slate-700">{{ $question->created_at->format('M d, Y') }}</dd></div>
                    </dl>
                    @if($question->tags->count())
                    <div class="mt-3 flex flex-wrap gap-1">
                        @foreach($question->tags as $tag)
                        <span class="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">{{ $tag->name }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>

                @if($question->answer_text)
                <div class="bg-emerald-50 rounded-2xl border border-emerald-100 shadow-sm p-5">
                    <h2 class="text-xs font-semibold text-emerald-600 uppercase tracking-wide mb-3"><i class="bi bi-shield-check me-1"></i>Model Answer</h2>
                    <p class="text-sm text-slate-700 whitespace-pre-line">{{ $question->answer_text }}</p>
                </div>
                @endif
            </div>

            @if($question->learning_outcome)
            <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 text-sm text-blue-700">
                <i class="bi bi-bullseye me-1"></i><strong>Learning Outcome:</strong> {{ $question->learning_outcome }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
