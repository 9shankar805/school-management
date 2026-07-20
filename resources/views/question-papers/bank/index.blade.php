@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight"><i class="bi bi-collection me-2"></i>Question Bank</h1>
                <p class="text-slate-400 text-sm mt-0.5">Reusable questions filterable by subject, chapter, difficulty, and Bloom's taxonomy</p>
            </div>
            @can('create exams')
            <a href="{{ route('question-bank.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition flex items-center gap-1.5">
                <i class="bi bi-plus-lg"></i> Add Question
            </a>
            @endcan
        </div>

        @if(session('status'))
        <div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>
        @endif

        {{-- Filters --}}
        <form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-6 flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-slate-500 mb-1">Subject</label>
                <select name="subject" class="border border-slate-200 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="">All</option>
                    @foreach($subjects as $s)<option value="{{ $s }}" {{ request('subject')==$s?'selected':'' }}>{{ $s }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Type</label>
                <select name="question_type" class="border border-slate-200 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="">All</option>
                    @foreach($types as $k=>$v)<option value="{{ $k }}" {{ request('question_type')==$k?'selected':'' }}>{{ $v }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Difficulty</label>
                <select name="difficulty" class="border border-slate-200 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="">All</option>
                    @foreach($diffs as $k=>$v)<option value="{{ $k }}" {{ request('difficulty')==$k?'selected':'' }}>{{ $v }}</option>@endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs text-slate-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search question text…"
                       class="w-full border border-slate-200 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <button type="submit" class="px-4 py-1.5 bg-slate-700 hover:bg-slate-800 text-white rounded-xl text-sm font-medium transition">Filter</button>
            <a href="{{ route('question-bank.index') }}" class="px-4 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-medium transition">Clear</a>
        </form>

        {{-- List --}}
        <div class="space-y-3">
            @forelse($questions as $q)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
                <div class="flex gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-2 flex-wrap">
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">{{ $types[$q->question_type] ?? $q->question_type }}</span>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $q->difficulty_badge }}">{{ ucfirst($q->difficulty) }}</span>
                            @if($q->subject)<span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">{{ $q->subject }}</span>@endif
                            @if($q->chapter)<span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-400">Ch: {{ $q->chapter }}</span>@endif
                            @if($q->bloom_taxonomy)<span class="text-[10px] px-2 py-0.5 rounded-full bg-violet-100 text-violet-600">{{ ucfirst($q->bloom_taxonomy) }}</span>@endif
                        </div>
                        <p class="text-sm text-slate-800 leading-relaxed line-clamp-2">{!! strip_tags($q->question_text) !!}</p>
                    </div>
                    <div class="flex-shrink-0 text-right">
                        <p class="text-lg font-bold text-indigo-600">{{ $q->allocated_marks }}</p>
                        <p class="text-[10px] text-slate-400">marks</p>
                    </div>
                </div>
                @can('create exams')
                <div class="flex gap-2 mt-3 pt-3 border-t border-slate-50">
                    <a href="{{ route('question-bank.edit', $q->id) }}" class="text-xs px-3 py-1 rounded-lg bg-slate-50 hover:bg-indigo-50 text-slate-600 hover:text-indigo-600 transition">Edit</a>
                    <a href="{{ route('question-bank.duplicate', $q->id) }}" class="text-xs px-3 py-1 rounded-lg bg-slate-50 hover:bg-amber-50 text-slate-600 hover:text-amber-600 transition">Duplicate</a>
                    <form method="POST" action="{{ route('question-bank.destroy', $q->id) }}" onsubmit="return confirm('Delete this question?')">
                        @csrf @method('DELETE')
                        <button class="text-xs px-3 py-1 rounded-lg bg-slate-50 hover:bg-rose-50 text-slate-600 hover:text-rose-600 transition">Delete</button>
                    </form>
                </div>
                @endcan
            </div>
            @empty
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center text-slate-400">
                <i class="bi bi-collection text-5xl mb-3 block"></i>
                <p class="text-sm">No questions found. Adjust filters or <a href="{{ route('question-bank.create') }}" class="text-indigo-500 hover:underline">add a question</a>.</p>
            </div>
            @endforelse
        </div>

        <div class="mt-5">{{ $questions->links() }}</div>
    </div>
</div>
@endsection
