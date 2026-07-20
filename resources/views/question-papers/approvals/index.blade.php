@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="mb-7">
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight"><i class="bi bi-check2-circle me-2"></i>Question Paper Approvals</h1>
            <p class="text-slate-400 text-sm mt-0.5">Papers pending review, approval, or lock</p>
        </div>

        @if(session('status'))
        <div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>
        @endif
        @include('session-messages')

        @if($papers->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center text-slate-400">
            <i class="bi bi-check2-all text-5xl mb-3 block text-emerald-300"></i>
            <p class="text-sm">No papers pending approval.</p>
        </div>
        @else
        <div class="space-y-4">
            @foreach($papers as $p)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <div class="flex justify-between items-start gap-4 flex-wrap">
                    <div>
                        <p class="font-semibold text-slate-800">{{ $p->title }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $p->subject ?? $p->course?->course_name ?? '—' }} ·
                            {{ $p->creator?->full_name ?? '—' }} ·
                            Updated {{ $p->updated_at->diffForHumans() }}
                        </p>
                    </div>
                    <span class="inline-block px-3 py-1 rounded-xl text-xs font-semibold {{ $p->status_badge }}">
                        {{ \App\Models\QuestionPaper::STATUSES[$p->status] }}
                    </span>
                </div>

                {{-- Approval history --}}
                @if($p->approvals->isNotEmpty())
                <div class="mt-3 border-t border-slate-50 pt-3 space-y-1">
                    @foreach($p->approvals->take(3) as $a)
                    <div class="flex items-center gap-2 text-xs text-slate-500">
                        <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $a->action_badge }}">{{ \App\Models\QuestionApproval::ACTIONS[$a->action] ?? $a->action }}</span>
                        <span>by {{ $a->reviewer?->full_name ?? '—' }}</span>
                        <span class="text-slate-300">{{ $a->actioned_at->format('d M H:i') }}</span>
                        @if($a->comments)<span class="text-slate-400 italic">— {{ $a->comments }}</span>@endif
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Action buttons --}}
                <div class="flex flex-wrap gap-2 mt-4 pt-3 border-t border-slate-50">
                    <a href="{{ route('question-papers.show', $p->id) }}" class="px-3 py-1.5 bg-slate-50 hover:bg-indigo-50 text-slate-600 hover:text-indigo-600 rounded-xl text-xs font-medium transition">
                        <i class="bi bi-eye me-1"></i> View
                    </a>
                    @if($p->status === 'submitted')
                    <form method="POST" action="{{ route('question-papers.review', $p->id) }}" class="flex gap-1">
                        @csrf
                        <input type="text" name="comments" placeholder="Comments (optional)" class="border border-slate-200 rounded-xl px-3 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-400">
                        <button class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-xl text-xs font-medium transition">Mark Reviewed</button>
                    </form>
                    @endif
                    @if($p->status === 'reviewed')
                    <form method="POST" action="{{ route('question-papers.approve', $p->id) }}">
                        @csrf
                        <button class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 rounded-xl text-xs font-medium transition"><i class="bi bi-check-lg me-1"></i> Approve</button>
                    </form>
                    <form method="POST" action="{{ route('question-papers.reject', $p->id) }}" class="flex gap-1">
                        @csrf
                        <input type="text" name="comments" required placeholder="Reason for rejection *" class="border border-slate-200 rounded-xl px-3 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-rose-400">
                        <button class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl text-xs font-medium transition"><i class="bi bi-x-lg me-1"></i> Reject</button>
                    </form>
                    @endif
                    @if($p->status === 'approved')
                    <form method="POST" action="{{ route('question-papers.lock', $p->id) }}">
                        @csrf
                        <button class="px-3 py-1.5 bg-violet-50 hover:bg-violet-100 text-violet-600 rounded-xl text-xs font-medium transition"><i class="bi bi-lock me-1"></i> Lock for Print</button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
