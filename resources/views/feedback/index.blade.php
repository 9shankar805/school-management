@extends('layouts.app')
@section('title', 'Feedback & Grievance Console')

@section('content')
<style>
  .feedback-hero-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: linear-gradient(135deg, #059669 0%, #0d9488 100%) !important;
    color: #ffffff !important;
    box-shadow: 0 4px 14px rgba(5, 150, 105, 0.3);
  }
</style>

<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="feedback-hero-icon">
                <i class="bi bi-inbox-fill fs-4"></i>
            </div>
            <div>
                <h2 class="h3 font-extrabold text-slate-800 mb-0">Feedback & Grievance Management</h2>
                <p class="text-slate-500 text-sm mb-0">Review suggestions, grievances, and complaints from parents, students, and staff</p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('feedback.create') }}" class="btn btn-primary bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
                <i class="bi bi-plus-lg me-1.5"></i> Submit New Feedback
            </a>
        </div>
    </div>

    @include('session-messages')

    <!-- Metrics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Tickets</span>
                        <h3 class="text-2xl font-bold text-slate-800 mt-1 mb-0">{{ $stats['total'] }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 d-flex align-items-center justify-content-center">
                        <i class="bi bi-ticket-detailed fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-xs font-semibold text-amber-500 uppercase tracking-wider">Pending Review</span>
                        <h3 class="text-2xl font-bold text-amber-600 mt-1 mb-0">{{ $stats['pending'] }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 d-flex align-items-center justify-content-center">
                        <i class="bi bi-clock-history fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-xs font-semibold text-rose-500 uppercase tracking-wider">Urgent Action</span>
                        <h3 class="text-2xl font-bold text-rose-600 mt-1 mb-0">{{ $stats['urgent'] }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 d-flex align-items-center justify-content-center">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-xs font-semibold text-emerald-500 uppercase tracking-wider">Resolved</span>
                        <h3 class="text-2xl font-bold text-emerald-600 mt-1 mb-0">{{ $stats['resolved'] }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 d-flex align-items-center justify-content-center">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl border border-slate-100 p-3 shadow-sm mb-4">
        <form action="{{ route('feedback.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-slate-50 border-slate-200 text-slate-400">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" class="form-control border-slate-200 text-sm" placeholder="Search by title, keyword, or submitter...">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select name="status" class="form-select border-slate-200 text-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_review" {{ $status === 'in_review' ? 'selected' : '' }}>In Review</option>
                    <option value="resolved" {{ $status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select name="category" class="form-select border-slate-200 text-sm" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <option value="academic" {{ $category === 'academic' ? 'selected' : '' }}>Academic</option>
                    <option value="transport" {{ $category === 'transport' ? 'selected' : '' }}>Transport</option>
                    <option value="fee_billing" {{ $category === 'fee_billing' ? 'selected' : '' }}>Fee & Billing</option>
                    <option value="facilities" {{ $category === 'facilities' ? 'selected' : '' }}>Facilities</option>
                    <option value="canteen" {{ $category === 'canteen' ? 'selected' : '' }}>Canteen</option>
                    <option value="other" {{ $category === 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-dark w-100 font-semibold text-sm rounded-xl">Filter</button>
                @if($search || $status || $category)
                <a href="{{ route('feedback.index') }}" class="btn btn-light border border-slate-200 text-sm rounded-xl">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tickets List Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        @if($feedbacks->isEmpty())
        <div class="p-5 text-center text-slate-400">
            <i class="bi bi-inbox fs-1 d-block mb-2 text-slate-300"></i>
            No feedback tickets match the selected criteria.
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-sm">
                <thead class="bg-slate-50 border-bottom border-slate-200 text-xs text-slate-500">
                    <tr>
                        <th class="ps-4 py-3">Ticket #</th>
                        <th class="py-3">Subject & Category</th>
                        <th class="py-3">Submitter</th>
                        <th class="py-3">Priority</th>
                        <th class="py-3">Status</th>
                        <th class="py-3">Date</th>
                        <th class="pe-4 py-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($feedbacks as $fb)
                    @php
                        $statusBadge = match($fb->status) {
                            'resolved'   => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'in_review'  => 'bg-blue-100 text-blue-800 border-blue-200',
                            'rejected'   => 'bg-slate-100 text-slate-600 border-slate-200',
                            default      => 'bg-amber-100 text-amber-800 border-amber-200',
                        };
                        $priorityBadge = match($fb->priority) {
                            'urgent' => 'bg-rose-100 text-rose-700 font-bold',
                            'high'   => 'bg-orange-100 text-orange-700 font-semibold',
                            default  => 'bg-slate-100 text-slate-600',
                        };
                    @endphp
                    <tr>
                        <td class="ps-4 font-mono font-bold text-slate-700 text-xs">
                            #TK-{{ str_pad($fb->id, 5, '0', STR_PAD_LEFT) }}
                        </td>
                        <td>
                            <div class="font-bold text-slate-800">{{ $fb->title }}</div>
                            <span class="badge bg-slate-100 text-slate-600 text-[10px] font-medium text-uppercase mt-1">
                                {{ str_replace('_', ' ', $fb->category) }}
                            </span>
                        </td>
                        <td>
                            @if($fb->is_anonymous)
                            <span class="badge bg-slate-100 text-slate-500 font-medium"><i class="bi bi-incognito me-1"></i> Anonymous</span>
                            @else
                            <div class="font-semibold text-slate-800 text-xs">{{ $fb->submitter_name }}</div>
                            <div class="text-[10px] text-slate-400 text-capitalize">{{ $fb->submitter_role }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $priorityBadge }} text-[10px] px-2 py-0.5 rounded-full text-uppercase">
                                {{ $fb->priority }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $statusBadge }} text-[10px] px-2.5 py-1 rounded-full text-uppercase border font-bold">
                                {{ str_replace('_', ' ', $fb->status) }}
                            </span>
                        </td>
                        <td class="text-xs text-slate-500">
                            {{ $fb->created_at->format('M d, Y') }}
                        </td>
                        <td class="pe-4 text-end">
                            <a href="{{ route('feedback.show', $fb->id) }}" class="btn btn-sm btn-light border border-slate-200 rounded-xl px-3 py-1 font-semibold text-xs text-slate-700 hover:bg-slate-100">
                                Review & Resolve →
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top border-slate-100">
            {{ $feedbacks->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
