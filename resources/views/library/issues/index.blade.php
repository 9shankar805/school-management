@extends('layouts.app')
@section('title', 'Book Circulation & Issues')

@section('content')
<style>
  .header-icon-box-library {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%) !important;
    color: #ffffff !important;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
  }
  .header-icon-box-library i {
    color: #ffffff !important;
    font-size: 22px !important;
    line-height: 1 !important;
    display: inline-block !important;
    opacity: 1 !important;
    visibility: visible !important;
  }
</style>

<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-3 mb-1">
                <div class="header-icon-box-library">
                    <i class="bi bi-box-arrow-up-right"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Book Circulation & Issues</h2>
                    <p class="text-slate-500 text-sm mb-0">Track book checkouts, manage returns, view overdue loans, and fine settlements</p>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            @unlessrole('student|parent')
            <a href="{{ route('library.issues.create') }}" class="btn btn-emerald bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-3.5 py-2.5 rounded-xl text-xs shadow-sm transition">
                <i class="bi bi-box-arrow-up-right me-1"></i> Issue Book
            </a>
            <a href="{{ route('library.issues.return') }}" class="btn btn-indigo bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-3.5 py-2.5 rounded-xl text-xs shadow-sm transition">
                <i class="bi bi-box-arrow-in-left me-1"></i> Return Book
            </a>
            @endunlessrole
            <a href="{{ route('library.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2.5 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-journals me-1"></i> Catalog
            </a>
            <a href="{{ route('library.members.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2.5 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-people me-1"></i> Members
            </a>
        </div>
    </div>

    @include('session-messages')

    @if(isset($overdueCount) && $overdueCount > 0)
    <div class="alert alert-warning rounded-2xl border-0 shadow-sm p-3.5 mb-4 bg-rose-50 text-rose-900 border-l-4 border-rose-500 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2.5">
            <i class="bi bi-exclamation-triangle-fill text-rose-600 fs-5"></i>
            <span class="text-sm">
                <strong>{{ $overdueCount }}</strong> book loan(s) are past due date and require immediate return.
            </span>
        </div>
        <a href="{{ route('library.issues.index', ['status' => 'overdue']) }}" class="btn btn-sm btn-rose bg-rose-600 text-white font-semibold text-xs py-1 px-3 rounded-lg">
            Filter Overdue Books
        </a>
    </div>
    @endif

    <!-- Filter Control Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-4">
        <form method="GET" action="{{ route('library.issues.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-5">
                <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Search Catalog or Member</label>
                <input type="text" name="search" class="form-control rounded-xl border-slate-200 py-2 text-sm font-medium text-slate-800" placeholder="Search book title, ISBN, member name, or card number..." value="{{ request('search') }}">
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Loan Status</label>
                <select name="status" class="form-select rounded-xl border-slate-200 py-2 text-sm font-medium text-slate-800">
                    <option value="">All Loan Statuses</option>
                    <option value="issued" @selected(request('status') === 'issued')>Issued (Active)</option>
                    <option value="overdue" @selected(request('status') === 'overdue')>Overdue</option>
                    <option value="returned" @selected(request('status') === 'returned')>Returned</option>
                    <option value="lost" @selected(request('status') === 'lost')>Marked Lost</option>
                </select>
            </div>

            <div class="col-12 col-md-3">
                <button type="submit" class="btn btn-indigo bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-xl text-sm shadow-sm transition">
                    <i class="bi bi-filter me-1"></i> Apply Filter
                </button>
                @if(request()->hasAny(['search','status']))
                <a href="{{ route('library.issues.index') }}" class="btn btn-light border border-slate-200 text-slate-600 font-semibold py-2 px-3 rounded-xl text-sm ms-1">
                    Clear
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Circulation Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
            <div>
                <h5 class="font-bold text-slate-800 mb-0">Circulation Records</h5>
                <p class="text-xs text-slate-400 mb-0">Issued book history & active checkout register</p>
            </div>
            <span class="badge bg-indigo-50 text-indigo-700 font-semibold text-xs px-3 py-1 rounded-full">
                Total Loans: {{ $issues->total() }}
            </span>
        </div>

        @if($issues->count() > 0)
        <div class="table-responsive">
            <table class="table align-middle text-sm mb-0">
                <thead>
                    <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                        <th class="py-3 px-4 rounded-s-xl">Book Info</th>
                        <th class="py-3 px-4">Member</th>
                        <th class="py-3 px-4">Issued On</th>
                        <th class="py-3 px-4">Due Date</th>
                        <th class="py-3 px-4">Returned On</th>
                        <th class="py-3 px-4 text-center">Fine Status</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 rounded-e-xl text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($issues as $issue)
                    <tr class="{{ $issue->status === 'overdue' ? 'bg-rose-50/50' : '' }}">
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-800" title="{{ $issue->book->title ?? '' }}">
                                {{ Str::limit($issue->book->title ?? 'Book Title', 35) }}
                            </div>
                            <div class="text-xs text-slate-400 font-mono">ISBN: {{ $issue->book->isbn ?? 'N/A' }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-800">
                                {{ $issue->member->user->first_name ?? '' }} {{ $issue->member->user->last_name ?? 'Member' }}
                            </div>
                            <code class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[11px]">
                                {{ $issue->member->card_number ?? 'N/A' }}
                            </code>
                        </td>
                        <td class="py-3 px-4 text-xs font-semibold text-slate-700">
                            {{ $issue->issue_date->format('d M Y') }}
                        </td>
                        <td class="py-3 px-4 text-xs font-bold {{ $issue->status === 'overdue' ? 'text-rose-600' : 'text-slate-700' }}">
                            {{ $issue->due_date->format('d M Y') }}
                        </td>
                        <td class="py-3 px-4 text-xs text-slate-500 font-medium">
                            {{ $issue->return_date?->format('d M Y') ?? '—' }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($issue->fine_amount > 0)
                            <div class="font-bold text-xs {{ $issue->fine_status === 'pending' ? 'text-rose-600' : 'text-slate-700' }}">
                                Rs. {{ number_format($issue->fine_amount, 2) }}
                            </div>
                            <span class="badge {{ $issue->fine_status === 'paid' ? 'bg-emerald-100 text-emerald-700' : ($issue->fine_status === 'waived' ? 'bg-slate-100 text-slate-600' : 'bg-rose-100 text-rose-700') }} text-[10px] px-2 py-0.5 rounded-full font-semibold">
                                {{ ucfirst($issue->fine_status) }}
                            </span>
                            @else
                            <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($issue->status === 'issued')
                            <span class="badge bg-indigo-100 text-indigo-700 text-xs px-2.5 py-1 rounded-full font-semibold">
                                <i class="bi bi-clock me-1"></i> Issued
                            </span>
                            @elseif($issue->status === 'returned')
                            <span class="badge bg-emerald-100 text-emerald-700 text-xs px-2.5 py-1 rounded-full font-semibold">
                                <i class="bi bi-check-circle-fill me-1"></i> Returned
                            </span>
                            @elseif($issue->status === 'overdue')
                            <span class="badge bg-rose-100 text-rose-700 text-xs px-2.5 py-1 rounded-full font-semibold">
                                <i class="bi bi-exclamation-circle-fill me-1"></i> Overdue
                            </span>
                            @else
                            <span class="badge bg-slate-100 text-slate-700 text-xs px-2.5 py-1 rounded-full font-semibold">
                                Lost
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-end">
                            @if(in_array($issue->status, ['issued','overdue']))
                                @unlessrole('student|parent')
                                <a href="{{ route('library.issues.return', ['issue_id' => $issue->id]) }}" class="btn btn-sm btn-emerald bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs py-1 px-2.5 rounded-lg shadow-sm" title="Return Book">
                                    <i class="bi bi-box-arrow-in-left me-1"></i> Return
                                </a>
                                <form action="{{ route('library.issues.lost', $issue->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Mark this book as lost?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary text-xs py-1 px-2 rounded-lg ms-1" title="Mark Lost">
                                        <i class="bi bi-x-circle text-rose-500"></i>
                                    </button>
                                </form>
                                @endunlessrole
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $issues->links() }}
        </div>
        @else
        <div class="text-center py-5 text-slate-400">
            <i class="bi bi-box-arrow-up-right display-3 text-slate-200 mb-3 block"></i>
            <h6 class="font-bold text-slate-600 mb-1">No Circulation Records Found</h6>
            <p class="mb-0 text-xs">No active book loans or returned items match your filter criteria.</p>
        </div>
        @endif
    </div>
</div>
@endsection
