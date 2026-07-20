@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('question-papers.index') }}" class="hover:text-indigo-600">Question Papers</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">{{ Str::limit($paper->title, 50) }}</span>
        </nav>

        @include('session-messages')

        {{-- Header --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6">
            <div class="flex flex-wrap justify-between items-start gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <h1 class="text-xl font-bold text-slate-800">{{ $paper->title }}</h1>
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $paper->status_badge }}">
                            {{ \App\Models\QuestionPaper::STATUSES[$paper->status] }}
                        </span>
                        @if($paper->is_locked)
                        <span class="inline-flex items-center gap-1 text-xs text-violet-600 bg-violet-50 px-2 py-0.5 rounded-full">
                            <i class="bi bi-lock-fill"></i> Locked
                        </span>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-4 text-xs text-slate-500 mt-1">
                        @if($paper->subject)<span><i class="bi bi-book me-1"></i>{{ $paper->subject }}</span>@endif
                        @if($paper->class_label)<span><i class="bi bi-layers me-1"></i>{{ $paper->class_label }}</span>@endif
                        @if($paper->exam_name)<span><i class="bi bi-file-text me-1"></i>{{ $paper->exam_name }}</span>@endif
                        @if($paper->duration)<span><i class="bi bi-clock me-1"></i>{{ $paper->duration }}</span>@endif
                        <span><i class="bi bi-award me-1"></i>FM: {{ $paper->full_marks ?: $paper->total_marks }} / PM: {{ $paper->pass_marks }}</span>
                        <span><i class="bi bi-person me-1"></i>{{ $paper->creator?->full_name }}</span>
                        <span><i class="bi bi-calendar3 me-1"></i>{{ $paper->updated_at->format('M d, Y') }}</span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if($paper->is_editable && auth()->id() == $paper->created_by)
                    <a href="{{ route('question-papers.edit', $paper->id) }}"
                       class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-medium transition">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    @endif
                    <a href="{{ route('question-papers.pdf', $paper->id) }}" target="_blank"
                       class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-medium transition">
                        <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                    </a>
                    <a href="{{ route('question-papers.versions', $paper->id) }}"
                       class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-medium transition">
                        <i class="bi bi-clock-history me-1"></i> History
                    </a>

                    @can('create exams')
                    {{-- Approval workflow actions --}}
                    @if($paper->status === 'draft')
                    <form method="POST" action="{{ route('question-papers.submit', $paper->id) }}">
                        @csrf
                        <button class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-medium transition">
                            <i class="bi bi-send me-1"></i> Submit for Review
                        </button>
                    </form>
                    @elseif($paper->status === 'submitted')
                    <form method="POST" action="{{ route('question-papers.review', $paper->id) }}">
                        @csrf
                        <button class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-xl text-xs font-medium transition">
                            <i class="bi bi-eye-fill me-1"></i> Mark Reviewed
                        </button>
                    </form>
                    @elseif($paper->status === 'reviewed')
                    <form method="POST" action="{{ route('question-papers.approve', $paper->id) }}">
                        @csrf
                        <button class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-medium transition">
                            <i class="bi bi-check-circle me-1"></i> Approve
                        </button>
                    </form>
                    <form method="POST" action="{{ route('question-papers.reject', $paper->id) }}">
                        @csrf
                        <button class="px-4 py-2 bg-rose-500 hover:bg-rose-600 text-white rounded-xl text-xs font-medium transition">
                            <i class="bi bi-x-circle me-1"></i> Reject
                        </button>
                    </form>
                    @elseif($paper->status === 'approved')
                    <form method="POST" action="{{ route('question-papers.lock', $paper->id) }}">
                        @csrf
                        <button class="px-4 py-2 bg-violet-500 hover:bg-violet-600 text-white rounded-xl text-xs font-medium transition">
                            <i class="bi bi-lock-fill me-1"></i> Lock Paper
                        </button>
                    </form>
                    @endif
                    @endcan
                </div>
            </div>
        </div>

        {{-- Approval history --}}
        @if($paper->approvals->count())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6">
            <h2 class="text-sm font-semibold text-slate-700 mb-3"><i class="bi bi-shield-check me-1 text-indigo-400"></i>Approval Trail</h2>
            <div class="space-y-2">
                @foreach($paper->approvals as $approval)
                <div class="flex items-start gap-3 text-xs text-slate-500">
                    <span class="inline-block px-2 py-0.5 rounded-full font-semibold text-[10px]
                        {{ match($approval->action) { 'submitted'=>'bg-amber-100 text-amber-700', 'reviewed'=>'bg-blue-100 text-blue-700', 'approved'=>'bg-emerald-100 text-emerald-700', 'rejected'=>'bg-rose-100 text-rose-700', 'locked'=>'bg-violet-100 text-violet-700', default=>'bg-slate-100 text-slate-600' } }}">
                        {{ strtoupper($approval->action) }}
                    </span>
                    <span><strong>{{ $approval->user?->full_name }}</strong> — {{ $approval->created_at->format('M d, Y H:i') }}</span>
                    @if($approval->notes)<span class="text-slate-400">· {{ $approval->notes }}</span>@endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Sections and questions (read-only) --}}
        <div class="space-y-4">
            @forelse($paper->sections as $section)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 bg-indigo-50 border-b border-indigo-100 flex justify-between items-center">
                    <h3 class="font-semibold text-indigo-700 text-sm">{{ $section->title }}</h3>
                    <span class="text-xs text-indigo-400">{{ $section->questions->count() }} question(s) · {{ $section->total_marks }} marks</span>
                </div>
                @if($section->instructions)
                <p class="px-5 py-2 text-xs text-slate-500 italic border-b border-slate-50">{{ $section->instructions }}</p>
                @endif
                <div class="divide-y divide-slate-50">
                    @foreach($section->questions as $q)
                    <div class="px-5 py-4">
                        <div class="flex gap-3 items-start">
                            <span class="text-xs font-bold text-slate-400 w-6 flex-shrink-0 pt-0.5">{{ $q->question_number }}.</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-slate-800 leading-relaxed">{!! $q->question_text !!}</p>
                                @if($q->question_type === 'mcq' && $q->options)
                                <div class="mt-2 space-y-1">
                                    @foreach($q->options as $i => $opt)
                                    <p class="text-xs text-slate-600 flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full border border-slate-200 flex items-center justify-center text-[10px] font-bold">{{ chr(65+$i) }}</span>
                                        {{ $opt }}
                                    </p>
                                    @endforeach
                                </div>
                                @endif
                                <div class="flex flex-wrap gap-3 mt-2">
                                    <span class="text-[10px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">{{ \App\Models\QuestionBank::QUESTION_TYPES[$q->question_type] ?? $q->question_type }}</span>
                                    <span class="text-[10px] {{ $q->bank?->difficulty_badge ?? 'bg-slate-100 text-slate-500' }} px-2 py-0.5 rounded-full capitalize">{{ $q->difficulty }}</span>
                                    <span class="text-[10px] bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full font-semibold">{{ $q->allocated_marks }} mark(s)</span>
                                    @if($q->chapter)<span class="text-[10px] text-slate-400">Ch: {{ $q->chapter }}</span>@endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center text-slate-400">
                <i class="bi bi-file-earmark-text text-4xl mb-2 block"></i>
                <p class="text-sm">No sections yet.
                    @if($paper->is_editable && auth()->id() == $paper->created_by)
                    <a href="{{ route('question-papers.edit', $paper->id) }}" class="text-indigo-500 hover:underline">Open editor to add content.</a>
                    @endif
                </p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
