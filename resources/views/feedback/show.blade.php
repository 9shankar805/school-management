@extends('layouts.app')
@section('title', 'Ticket Details — #' . $feedback->id)

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="{{ auth()->user()->hasRole(['admin', 'principal', 'teacher']) ? route('feedback.index') : route('feedback.my') }}" class="btn btn-light border border-slate-200 text-slate-700 rounded-xl px-3 py-2 text-sm font-semibold">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
                <span class="font-mono text-xs font-bold text-slate-400">
                    TICKET REF: #TK-{{ str_pad($feedback->id, 5, '0', STR_PAD_LEFT) }}
                </span>
            </div>

            @include('session-messages')

            <!-- Main Ticket Card -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5 p-md-6 mb-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        @php
                            $statusBadge = match($feedback->status) {
                                'resolved'   => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                'in_review'  => 'bg-blue-100 text-blue-800 border-blue-200',
                                'rejected'   => 'bg-slate-100 text-slate-600 border-slate-200',
                                default      => 'bg-amber-100 text-amber-800 border-amber-200',
                            };
                            $priorityBadge = match($feedback->priority) {
                                'urgent' => 'bg-rose-100 text-rose-700 font-bold',
                                'high'   => 'bg-orange-100 text-orange-700 font-semibold',
                                default  => 'bg-slate-100 text-slate-600',
                            };
                        @endphp
                        <span class="badge {{ $statusBadge }} text-xs px-3 py-1 rounded-full text-uppercase border font-bold">
                            Status: {{ str_replace('_', ' ', $feedback->status) }}
                        </span>
                        <span class="badge {{ $priorityBadge }} text-xs px-2.5 py-1 rounded-full text-uppercase">
                            Priority: {{ $feedback->priority }}
                        </span>
                        <span class="badge bg-slate-100 text-slate-700 text-xs px-2.5 py-1 rounded-full font-medium text-uppercase">
                            Category: {{ str_replace('_', ' ', $feedback->category) }}
                        </span>
                    </div>

                    <span class="text-xs text-slate-400">
                        Submitted: {{ $feedback->created_at->format('M d, Y · h:i A') }}
                    </span>
                </div>

                <h1 class="h4 font-extrabold text-slate-900 mb-3">{{ $feedback->title }}</h1>

                <div class="d-flex align-items-center gap-2 mb-4 pb-3 border-b border-slate-100 text-xs text-slate-500">
                    @if($feedback->is_anonymous)
                    <span class="badge bg-slate-100 text-slate-600 px-2.5 py-1 rounded-full"><i class="bi bi-incognito me-1"></i> Anonymous Submission</span>
                    @else
                    <span>Submitted by: <strong>{{ $feedback->submitter_name }}</strong></span>
                    <span>·</span>
                    <span class="text-capitalize">{{ $feedback->submitter_role }}</span>
                    @endif
                </div>

                <div class="text-slate-700 text-sm leading-relaxed mb-4">
                    {!! nl2br(e($feedback->description)) !!}
                </div>

                <!-- Admin Response Section if exists -->
                @if($feedback->admin_response)
                <div class="bg-emerald-50/70 border border-emerald-200 rounded-2xl p-4 mt-4">
                    <div class="d-flex align-items-center gap-2 mb-2 text-emerald-900 font-bold text-sm">
                        <i class="bi bi-shield-check text-emerald-600 fs-5"></i>
                        Official School Administration Response
                    </div>
                    <div class="text-slate-700 text-sm leading-relaxed">
                        {!! nl2br(e($feedback->admin_response)) !!}
                    </div>
                    <div class="text-xs text-slate-400 mt-2 text-end">
                        Responded by {{ $feedback->resolver->full_name ?? 'School Administration' }} on {{ $feedback->resolved_at?->format('M d, Y · h:i A') }}
                    </div>
                </div>
                @endif
            </div>

            <!-- Admin Action Box (for authorized staff) -->
            @can('view staff')
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5">
                <h5 class="font-bold text-slate-800 mb-3">Update Status & Official Resolution</h5>
                <form action="{{ route('feedback.update-status', $feedback->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label font-bold text-slate-700 text-xs">Update Ticket Status</label>
                        <select name="status" class="form-select rounded-xl border-slate-200 text-sm w-auto">
                            <option value="pending" {{ $feedback->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_review" {{ $feedback->status === 'in_review' ? 'selected' : '' }}>Under Review</option>
                            <option value="resolved" {{ $feedback->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="rejected" {{ $feedback->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-bold text-slate-700 text-xs">Resolution Remarks / Reply to Submitter</label>
                        <textarea name="admin_response" rows="4" class="form-control rounded-2xl border-slate-200 text-sm" placeholder="Provide details of actions taken or administrative response...">{{ $feedback->admin_response }}</textarea>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-xl">
                            Save Ticket Updates
                        </button>
                    </div>
                </form>
            </div>
            @endcan
        </div>
    </div>
</div>
@endsection
