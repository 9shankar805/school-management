@extends('layouts.app')
@section('title', 'Academic Discussion Forum')

@section('content')
<style>
  .forum-hero-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: linear-gradient(135deg, #0284c7 0%, #0d9488 100%) !important;
    color: #ffffff !important;
    box-shadow: 0 4px 14px rgba(2, 132, 199, 0.3);
  }
  .forum-row {
    transition: all 0.15s ease;
    border: 1px solid #f1f5f9;
  }
  .forum-row:hover {
    border-color: #cbd5e1;
    background-color: #f8fafc;
    transform: translateY(-1px);
  }
</style>

<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="forum-hero-icon">
                <i class="bi bi-chat-square-dots-fill fs-4"></i>
            </div>
            <div>
                <h2 class="h3 font-extrabold text-slate-800 mb-0">Academic Discussion Forum</h2>
                <p class="text-slate-500 text-sm mb-0">Collaborative Q&A community for students and teachers</p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('forum.create') }}" class="btn btn-primary bg-sky-600 hover:bg-sky-700 text-white font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
                <i class="bi bi-plus-circle me-1.5"></i> Ask a Question / Start Topic
            </a>
        </div>
    </div>

    @include('session-messages')

    <!-- Metrics Strip -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-sm d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Topics</span>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1 mb-0">{{ $stats['total_threads'] }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 d-flex align-items-center justify-content-center">
                    <i class="bi bi-journal-text fs-5"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-sm d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Replies</span>
                    <h3 class="text-2xl font-bold text-emerald-600 mt-1 mb-0">{{ $stats['total_replies'] }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 d-flex align-items-center justify-content-center">
                    <i class="bi bi-chat-left-text-fill fs-5"></i>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-sm d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Teacher Verified Solutions</span>
                    <h3 class="text-2xl font-bold text-indigo-600 mt-1 mb-0">{{ $stats['solved_count'] }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 d-flex align-items-center justify-content-center">
                    <i class="bi bi-patch-check-fill fs-5"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white rounded-2xl border border-slate-100 p-3 shadow-sm mb-4">
        <form action="{{ route('forum.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-slate-50 border-slate-200 text-slate-400">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" class="form-control border-slate-200 text-sm" placeholder="Search questions or topics...">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select name="course_id" class="form-select border-slate-200 text-sm" onchange="this.form.submit()">
                    <option value="">All Subjects</option>
                    @foreach($courses as $c)
                    <option value="{{ $c->id }}" {{ $courseId == $c->id ? 'selected' : '' }}>{{ $c->course_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select name="status" class="form-select border-slate-200 text-sm" onchange="this.form.submit()">
                    <option value="">Status: All Topics</option>
                    <option value="solved" {{ $status === 'solved' ? 'selected' : '' }}>✅ Has Verified Solution</option>
                    <option value="unsolved" {{ $status === 'unsolved' ? 'selected' : '' }}>⏳ Unsolved / Open</option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-dark w-100 font-semibold text-sm rounded-xl">Filter</button>
                @if($search || $courseId || $status)
                <a href="{{ route('forum.index') }}" class="btn btn-light border border-slate-200 text-sm rounded-xl">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Thread List -->
    @if($threads->isEmpty())
    <div class="bg-white rounded-2xl border border-slate-100 p-5 text-center shadow-sm">
        <div class="w-16 h-16 rounded-full bg-sky-50 text-sky-500 d-inline-flex align-items-center justify-content-center mb-3">
            <i class="bi bi-chat-quote fs-2"></i>
        </div>
        <h5 class="font-bold text-slate-700">No discussions found</h5>
        <p class="text-slate-400 text-sm mb-3">Be the first to post an academic question or study inquiry!</p>
        <a href="{{ route('forum.create') }}" class="btn btn-primary bg-sky-600 text-white rounded-xl px-4 py-2 font-semibold">
            Post First Question
        </a>
    </div>
    @else
    <div class="space-y-3">
        @foreach($threads as $t)
        <div class="bg-white rounded-2xl p-4 shadow-sm forum-row">
            <div class="d-flex align-items-start gap-3">
                <img src="{{ $t->author->avatar }}" class="w-10 h-10 rounded-full object-cover border border-slate-200 flex-shrink-0" alt="">
                <div class="flex-grow-1 min-w-0">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        @if($t->is_pinned)
                        <span class="badge bg-amber-100 text-amber-800 text-[10px] font-bold uppercase rounded-full">
                            <i class="bi bi-pin-angle-fill me-0.5"></i> Pinned
                        </span>
                        @endif
                        @if($t->is_locked)
                        <span class="badge bg-slate-200 text-slate-700 text-[10px] font-bold uppercase rounded-full">
                            <i class="bi bi-lock-fill me-0.5"></i> Locked
                        </span>
                        @endif
                        @if($t->solutionReply)
                        <span class="badge bg-emerald-100 text-emerald-800 text-[10px] font-bold uppercase rounded-full border border-emerald-200">
                            <i class="bi bi-patch-check-fill me-0.5 text-emerald-600"></i> Solved
                        </span>
                        @endif
                        @if($t->course)
                        <span class="badge bg-sky-50 text-sky-700 text-[11px] font-semibold border border-sky-200 rounded-full">
                            {{ $t->course->course_name }}
                        </span>
                        @endif
                        @if($t->schoolClass)
                        <span class="badge bg-slate-100 text-slate-600 text-[11px] font-medium rounded-full">
                            Class {{ $t->schoolClass->class_name }}
                        </span>
                        @endif
                    </div>

                    <h5 class="font-bold text-slate-800 mb-1 text-base">
                        <a href="{{ route('forum.show', $t->id) }}" class="text-slate-800 hover:text-sky-600 text-decoration-none">
                            {{ $t->title }}
                        </a>
                    </h5>

                    <p class="text-slate-500 text-xs mb-2 line-clamp-2">
                        {{ \Illuminate\Support\Str::limit(strip_tags($t->content), 180) }}
                    </p>

                    <div class="d-flex flex-wrap align-items-center gap-3 text-xs text-slate-400">
                        <span>Asked by <strong>{{ $t->author->full_name }}</strong></span>
                        <span><i class="bi bi-clock me-1"></i>{{ $t->created_at->diffForHumans() }}</span>
                        <span><i class="bi bi-eye me-1"></i>{{ $t->views_count }} views</span>
                        <span class="font-semibold text-slate-600"><i class="bi bi-chat-dots me-1 text-sky-500"></i>{{ $t->replies_count }} answers</span>
                    </div>
                </div>

                <div class="flex-shrink-0 text-end d-none d-md-block">
                    <a href="{{ route('forum.show', $t->id) }}" class="btn btn-sm btn-light border border-slate-200 rounded-xl px-3 py-1.5 font-semibold text-xs text-slate-700 hover:bg-slate-100">
                        View Discussion →
                    </a>
                </div>
            </div>
        </div>
        @endforeach

        <div class="mt-4">
            {{ $threads->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
