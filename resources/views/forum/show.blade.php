@extends('layouts.app')
@section('title', $thread->title)

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <!-- Nav -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="{{ route('forum.index') }}" class="btn btn-light border border-slate-200 text-slate-700 rounded-xl px-3 py-2 text-sm font-semibold">
                    <i class="bi bi-arrow-left me-1"></i> Back to Forum
                </a>

                @can('view staff')
                <div class="d-flex align-items-center gap-2">
                    <form action="{{ route('forum.pin', $thread->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-light border border-slate-200 rounded-xl font-medium text-xs">
                            <i class="bi bi-pin-angle me-1"></i> {{ $thread->is_pinned ? 'Unpin' : 'Pin' }}
                        </button>
                    </form>
                    <form action="{{ route('forum.lock', $thread->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-light border border-slate-200 rounded-xl font-medium text-xs">
                            <i class="bi bi-lock me-1"></i> {{ $thread->is_locked ? 'Unlock' : 'Lock' }}
                        </button>
                    </form>
                </div>
                @endcan
            </div>

            @include('session-messages')

            <!-- Question Card -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5 p-md-6 mb-4">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    @if($thread->is_pinned)
                    <span class="badge bg-amber-100 text-amber-800 text-xs px-2.5 py-1 rounded-full font-bold uppercase">
                        <i class="bi bi-pin-angle-fill me-1"></i> Pinned
                    </span>
                    @endif
                    @if($thread->is_locked)
                    <span class="badge bg-slate-200 text-slate-700 text-xs px-2.5 py-1 rounded-full font-bold uppercase">
                        <i class="bi bi-lock-fill me-1"></i> Locked
                    </span>
                    @endif
                    @if($thread->course)
                    <span class="badge bg-sky-100 text-sky-800 text-xs px-2.5 py-1 rounded-full font-semibold border border-sky-200">
                        {{ $thread->course->course_name }}
                    </span>
                    @endif
                    @if($thread->schoolClass)
                    <span class="badge bg-slate-100 text-slate-600 text-xs px-2.5 py-1 rounded-full font-medium">
                        Class {{ $thread->schoolClass->class_name }}
                    </span>
                    @endif
                    <span class="text-xs text-slate-400 ms-auto">
                        <i class="bi bi-eye me-1"></i>{{ $thread->views_count }} views · {{ $thread->created_at->format('M d, Y · h:i A') }}
                    </span>
                </div>

                <h1 class="h3 font-extrabold text-slate-900 mb-4">{{ $thread->title }}</h1>

                <div class="d-flex align-items-center gap-3 mb-4 pb-4 border-b border-slate-100">
                    <img src="{{ $thread->author->avatar }}" class="w-10 h-10 rounded-full object-cover border border-slate-200" alt="">
                    <div>
                        <div class="font-bold text-slate-800 text-sm">{{ $thread->author->full_name }}</div>
                        <div class="text-xs text-slate-400 text-capitalize">{{ $thread->author->primary_role }}</div>
                    </div>
                </div>

                <div class="text-slate-700 text-sm leading-relaxed prose max-w-none">
                    {!! nl2br(e($thread->content)) !!}
                </div>
            </div>

            <!-- Answers Section Header -->
            <div class="d-flex align-items-center justify-content-between mb-3 px-2">
                <h4 class="font-bold text-slate-800 text-lg mb-0">
                    {{ $replies->count() }} {{ \Illuminate\Support\Str::plural('Answer', $replies->count()) }}
                </h4>
            </div>

            <!-- Replies Listing -->
            <div class="space-y-3 mb-5">
                @forelse($replies as $reply)
                <div class="bg-white rounded-2xl border p-4 shadow-sm {{ $reply->is_solution ? 'border-emerald-300 bg-emerald-50/10' : 'border-slate-200' }}">
                    @if($reply->is_solution)
                    <div class="mb-3">
                        <span class="badge bg-emerald-100 text-emerald-800 border border-emerald-300 px-3 py-1.5 rounded-full text-xs font-bold inline-flex align-items-center gap-1">
                            <i class="bi bi-patch-check-fill text-emerald-600 fs-6"></i> Verified Teacher Solution
                        </span>
                    </div>
                    @endif

                    <div class="d-flex align-items-start gap-3">
                        <!-- Upvote Box -->
                        <div class="flex-shrink-0 text-center">
                            <form action="{{ route('forum.reply.vote', $reply->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $reply->isVotedBy(auth()->id()) ? 'btn-primary bg-indigo-600 text-white' : 'btn-light border border-slate-200 text-slate-600' }} rounded-xl px-2.5 py-1">
                                    <i class="bi bi-arrow-up-circle-fill"></i>
                                    <div class="font-bold text-xs mt-0.5">{{ $reply->upvotes_count }}</div>
                                </button>
                            </form>
                        </div>

                        <!-- Content -->
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $reply->author->avatar }}" class="w-7 h-7 rounded-full object-cover" alt="">
                                    <span class="font-bold text-slate-800 text-xs">{{ $reply->author->full_name }}</span>
                                    <span class="badge bg-slate-100 text-slate-500 text-[10px] text-capitalize">{{ $reply->author->primary_role }}</span>
                                </div>
                                <span class="text-[11px] text-slate-400">{{ $reply->created_at->diffForHumans() }}</span>
                            </div>

                            <div class="text-slate-700 text-sm leading-relaxed mb-3">
                                {!! nl2br(e($reply->content)) !!}
                            </div>

                            @if(auth()->id() === $thread->user_id || auth()->user()->hasRole(['teacher', 'class-teacher', 'admin', 'principal']))
                            <div class="text-end">
                                <form action="{{ route('forum.reply.solution', $reply->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-link text-xs p-0 {{ $reply->is_solution ? 'text-danger' : 'text-emerald-600' }} font-semibold text-decoration-none">
                                        <i class="bi bi-check2-circle me-1"></i> {{ $reply->is_solution ? 'Unmark Solution' : 'Accept as Solution' }}
                                    </button>
                                </form>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-white rounded-2xl border border-slate-200 p-4 text-center text-slate-400 text-sm">
                    No answers posted yet. Be the first to answer this question!
                </div>
                @endforelse
            </div>

            <!-- Answer Submission Box -->
            @if(!$thread->is_locked)
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5">
                <h5 class="font-bold text-slate-800 mb-3">Your Answer / Contribution</h5>
                <form action="{{ route('forum.reply.store', $thread->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <textarea name="content" rows="5" class="form-control rounded-2xl border-slate-200 text-sm" placeholder="Write your explanation or step-by-step solution here..." required></textarea>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary bg-sky-600 hover:bg-sky-700 text-white font-bold px-5 py-2.5 rounded-xl transition">
                            <i class="bi bi-send-fill me-1.5"></i> Post Answer
                        </button>
                    </div>
                </form>
            </div>
            @else
            <div class="alert alert-secondary rounded-2xl text-center text-sm font-medium">
                <i class="bi bi-lock me-1"></i> This discussion is locked. No new answers can be submitted.
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
