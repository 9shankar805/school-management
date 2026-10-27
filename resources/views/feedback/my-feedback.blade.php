@extends('layouts.app')
@section('title', 'My Submitted Feedback & Grievances')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="h3 font-extrabold text-slate-800 mb-0">My Submitted Feedback & Requests</h2>
            <p class="text-slate-500 text-sm mb-0">Track responses and resolution status of your grievances</p>
        </div>
        <a href="{{ route('feedback.create') }}" class="btn btn-primary bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2.5 rounded-xl shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Submit New Feedback
        </a>
    </div>

    @include('session-messages')

    @if($feedbacks->isEmpty())
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 text-center shadow-sm">
        <div class="w-16 h-16 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 d-inline-flex align-items-center justify-content-center mb-3">
            <i class="bi bi-chat-heart fs-2"></i>
        </div>
        <h5 class="font-bold text-slate-700 dark:text-slate-200">No Feedback Tickets Submitted</h5>
        <p class="text-slate-400 text-sm mb-3">Have a suggestion, concern, or compliment? Share it with the school administration.</p>
        <a href="{{ route('feedback.create') }}" class="btn btn-primary bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl px-4 py-2">
            Submit Feedback
        </a>
    </div>
    @else
    <div class="space-y-3">
        @foreach($feedbacks as $fb)
        @php
            $statusBadge = match($fb->status) {
                'resolved'   => 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                'in_review'  => 'bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                'rejected'   => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700',
                default      => 'bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-800',
            };
        @endphp
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-2">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge {{ $statusBadge }} text-[10px] px-2.5 py-1 rounded-full text-uppercase border font-bold">
                            {{ str_replace('_', ' ', $fb->status) }}
                        </span>
                        <span class="badge bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] text-uppercase">
                            {{ str_replace('_', ' ', $fb->category) }}
                        </span>
                        <span class="text-xs text-slate-400 font-mono ms-2">
                            #TK-{{ str_pad($fb->id, 5, '0', STR_PAD_LEFT) }}
                        </span>
                    </div>

                    <h5 class="font-bold text-slate-800 dark:text-slate-100 text-base mb-1">
                        {{ $fb->title }}
                    </h5>
                    <p class="text-slate-600 dark:text-slate-300 text-xs mb-2">
                        {{ \Illuminate\Support\Str::limit($fb->description, 160) }}
                    </p>

                    @if($fb->admin_response)
                    <div class="bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/80 rounded-xl p-2.5 text-xs text-emerald-800 dark:text-emerald-300 mb-2">
                        <i class="bi bi-chat-quote-fill me-1 text-emerald-600 dark:text-emerald-400"></i><strong>Admin Response:</strong> {{ \Illuminate\Support\Str::limit($fb->admin_response, 180) }}
                    </div>
                    @endif

                    <div class="text-[11px] text-slate-400">
                        Submitted on {{ $fb->created_at->format('M d, Y · h:i A') }}
                    </div>
                </div>

                <div class="flex-shrink-0">
                    <a href="{{ route('feedback.show', $fb->id) }}" class="btn btn-sm btn-light border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl px-3 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-700">
                        View Details →
                    </a>
                </div>
            </div>
        </div>
        @endforeach

        <div class="mt-4">
            {{ $feedbacks->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
