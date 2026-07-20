@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('library.members.index') }}">Members</a></li>
                            <li class="breadcrumb-item active">{{ $member->user->first_name }} {{ $member->user->last_name }}</li>
                        </ol>
                    </nav>

                    @if(session('status'))
                        <div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif

                    <div class="row g-4">
                        {{-- Profile card --}}
                        <div class="col-md-4">
                            <div class="card shadow-sm text-center">
                                <div class="card-body">
                                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px">
                                        <i class="bi bi-person-fill text-secondary" style="font-size:2.5rem"></i>
                                    </div>
                                    <h5 class="fw-bold mb-0">{{ $member->user->first_name }} {{ $member->user->last_name }}</h5>
                                    <p class="text-muted small mb-2">{{ $member->user->email }}</p>
                                    <code class="d-block mb-2">{{ $member->card_number }}</code>
                                    <span class="badge bg-secondary mb-2">{{ ucfirst($member->member_type) }}</span>
                                    @php
                                        $sc = match($member->status) {
                                            'active'    => 'bg-success',
                                            'suspended' => 'bg-warning text-dark',
                                            'expired'   => 'bg-secondary',
                                            default     => 'bg-light text-dark',
                                        };
                                    @endphp
                                    <span class="badge {{ $sc }}">{{ ucfirst($member->status) }}</span>

                                    @if($member->outstanding_fine > 0)
                                    <div class="alert alert-danger mt-3 py-2 small">
                                        Outstanding Fine: <strong>${{ number_format($member->outstanding_fine, 2) }}</strong>
                                    </div>
                                    @endif
                                </div>
                                <div class="card-footer d-flex gap-2 justify-content-center">
                                    <a href="{{ route('library.members.edit', $member->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
                                    @if($member->outstanding_fine > 0)
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#fineModal">
                                        <i class="bi bi-cash-coin"></i> Settle Fine
                                    </button>
                                    @endif
                                </div>
                            </div>

                            <div class="card shadow-sm mt-3">
                                <div class="card-body">
                                    <dl class="row mb-0 small">
                                        <dt class="col-6">Max Books</dt><dd class="col-6">{{ $member->max_books }}</dd>
                                        <dt class="col-6">Loan Days</dt><dd class="col-6">{{ $member->loan_days }}</dd>
                                        <dt class="col-6">Quota Left</dt><dd class="col-6">{{ $member->remaining_quota }}</dd>
                                        <dt class="col-6">Member Since</dt><dd class="col-6">{{ $member->membership_start->format('d M Y') }}</dd>
                                        @if($member->membership_end)
                                        <dt class="col-6">Expires</dt><dd class="col-6">{{ $member->membership_end->format('d M Y') }}</dd>
                                        @endif
                                    </dl>
                                </div>
                            </div>
                        </div>

                        {{-- Loans --}}
                        <div class="col-md-8">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header fw-semibold">
                                    <i class="bi bi-book-half"></i> Active Loans
                                    <span class="badge bg-primary ms-1">{{ $activeIssues->count() }}</span>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light">
                                            <tr><th>Book</th><th>Issued</th><th>Due</th><th>Status</th><th>Action</th></tr>
                                        </thead>
                                        <tbody>
                                            @forelse($activeIssues as $issue)
                                            <tr>
                                                <td>{{ $issue->book->title }}</td>
                                                <td>{{ $issue->issue_date->format('d M Y') }}</td>
                                                <td class="{{ $issue->is_overdue ? 'text-danger fw-semibold' : '' }}">
                                                    {{ $issue->due_date->format('d M Y') }}
                                                    @if($issue->is_overdue)
                                                        <span class="badge bg-danger">Overdue</span>
                                                    @endif
                                                </td>
                                                <td><span class="badge bg-primary">{{ ucfirst($issue->status) }}</span></td>
                                                <td>
                                                    @can('return books')
                                                    <a href="{{ route('library.issues.return', ['issue_id' => $issue->id]) }}" class="btn btn-success btn-sm">Return</a>
                                                    @endcan
                                                </td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="5" class="text-center text-muted py-2">No active loans.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="card shadow-sm">
                                <div class="card-header fw-semibold">
                                    <i class="bi bi-clock-history"></i> Loan History
                                    <span class="badge bg-secondary ms-1">{{ $historyIssues->count() }}</span>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light">
                                            <tr><th>Book</th><th>Issued</th><th>Returned</th><th>Overdue Days</th><th>Fine</th><th>Fine Status</th></tr>
                                        </thead>
                                        <tbody>
                                            @forelse($historyIssues as $issue)
                                            <tr>
                                                <td>{{ $issue->book->title }}</td>
                                                <td>{{ $issue->issue_date->format('d M Y') }}</td>
                                                <td>{{ $issue->return_date?->format('d M Y') ?? '—' }}</td>
                                                <td>{{ $issue->overdue_days > 0 ? $issue->overdue_days . ' days' : '—' }}</td>
                                                <td>{{ $issue->fine_amount > 0 ? '$' . number_format($issue->fine_amount, 2) : '—' }}</td>
                                                <td>
                                                    @php
                                                        $fc = match($issue->fine_status) {
                                                            'none'   => 'bg-light text-dark',
                                                            'pending'=> 'bg-danger',
                                                            'paid'   => 'bg-success',
                                                            'waived' => 'bg-secondary',
                                                            default  => 'bg-light text-dark',
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $fc }}">{{ ucfirst($issue->fine_status) }}</span>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="6" class="text-center text-muted py-2">No history.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>

{{-- Settle Fine Modal --}}
@if($member->outstanding_fine > 0)
<div class="modal fade" id="fineModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form action="{{ route('library.members.fine.settle', $member->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Settle Fine</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Outstanding: <strong class="text-danger">${{ number_format($member->outstanding_fine, 2) }}</strong></p>
                    <div class="mb-3">
                        <label class="form-label">Action</label>
                        <select name="action" class="form-select">
                            <option value="paid">Mark as Paid</option>
                            <option value="waived">Waive Fine</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
