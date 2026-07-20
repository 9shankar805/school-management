@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-arrow-left-right"></i> Book Issues & Returns</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('library.index') }}">Library</a></li>
                            <li class="breadcrumb-item active">Issues</li>
                        </ol>
                    </nav>

                    @if(session('status'))
                        <div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif

                    @if($overdueCount > 0)
                    <div class="alert alert-warning d-flex align-items-center gap-2 py-2">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <span><strong>{{ $overdueCount }}</strong> book(s) are overdue.
                            <a href="{{ route('library.issues.index', ['status' => 'overdue']) }}" class="alert-link">View overdue</a>
                        </span>
                    </div>
                    @endif

                    <div class="d-flex gap-2 mb-3">
                        @can('issue books')
                        <a href="{{ route('library.issues.create') }}" class="btn btn-success btn-sm">
                            <i class="bi bi-box-arrow-up-right"></i> Issue Book
                        </a>
                        @endcan
                        @can('return books')
                        <a href="{{ route('library.issues.return') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-box-arrow-in-left"></i> Return Book
                        </a>
                        @endcan
                    </div>

                    <form method="GET" action="{{ route('library.issues.index') }}" class="row g-2 mb-3">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control form-control-sm"
                                   placeholder="Search book title, ISBN, member name, card…"
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="issued"   @selected(request('status') === 'issued')>Issued</option>
                                <option value="overdue"  @selected(request('status') === 'overdue')>Overdue</option>
                                <option value="returned" @selected(request('status') === 'returned')>Returned</option>
                                <option value="lost"     @selected(request('status') === 'lost')>Lost</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-secondary btn-sm w-100" type="submit"><i class="bi bi-search"></i> Filter</button>
                        </div>
                        @if(request()->hasAny(['search','status']))
                        <div class="col-md-1">
                            <a href="{{ route('library.issues.index') }}" class="btn btn-outline-secondary btn-sm w-100">Clear</a>
                        </div>
                        @endif
                    </form>

                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Book</th>
                                        <th>Member</th>
                                        <th>Issued</th>
                                        <th>Due</th>
                                        <th>Returned</th>
                                        <th>Fine</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($issues as $issue)
                                    <tr class="{{ $issue->status === 'overdue' ? 'table-danger' : '' }}">
                                        <td>
                                            <div class="fw-semibold small">{{ Str::limit($issue->book->title, 35) }}</div>
                                            <div class="text-muted" style="font-size:.75rem">{{ $issue->book->isbn }}</div>
                                        </td>
                                        <td>
                                            <div>{{ $issue->member->user->first_name }} {{ $issue->member->user->last_name }}</div>
                                            <div class="text-muted" style="font-size:.75rem"><code>{{ $issue->member->card_number }}</code></div>
                                        </td>
                                        <td class="small">{{ $issue->issue_date->format('d M Y') }}</td>
                                        <td class="small {{ $issue->status === 'overdue' ? 'text-danger fw-bold' : '' }}">
                                            {{ $issue->due_date->format('d M Y') }}
                                        </td>
                                        <td class="small">{{ $issue->return_date?->format('d M Y') ?? '—' }}</td>
                                        <td class="small">
                                            @if($issue->fine_amount > 0)
                                                <span class="{{ $issue->fine_status === 'pending' ? 'text-danger' : 'text-muted' }}">
                                                    ${{ number_format($issue->fine_amount, 2) }}
                                                    @if($issue->fine_status !== 'none')
                                                        <span class="badge bg-{{ $issue->fine_status === 'paid' ? 'success' : ($issue->fine_status === 'waived' ? 'secondary' : 'danger') }} ms-1">{{ $issue->fine_status }}</span>
                                                    @endif
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $sc = match($issue->status) {
                                                    'issued'   => 'bg-primary',
                                                    'returned' => 'bg-success',
                                                    'overdue'  => 'bg-danger',
                                                    'lost'     => 'bg-dark',
                                                    default    => 'bg-secondary',
                                                };
                                            @endphp
                                            <span class="badge {{ $sc }}">{{ ucfirst($issue->status) }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                @if(in_array($issue->status, ['issued','overdue']))
                                                    @can('return books')
                                                    <a href="{{ route('library.issues.return', ['issue_id' => $issue->id]) }}"
                                                       class="btn btn-success btn-sm" title="Return">
                                                        <i class="bi bi-box-arrow-in-left"></i>
                                                    </a>
                                                    @endcan
                                                    @can('issue books')
                                                    <form action="{{ route('library.issues.lost', $issue->id) }}" method="POST"
                                                          onsubmit="return confirm('Mark this book as lost?')">
                                                        @csrf
                                                        <button class="btn btn-outline-dark btn-sm" title="Mark Lost">
                                                            <i class="bi bi-x-circle"></i>
                                                        </button>
                                                    </form>
                                                    @endcan
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="8" class="text-center text-muted py-4">No records found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-3">{{ $issues->links() }}</div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
