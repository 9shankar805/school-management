@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-box-arrow-in-left"></i> Return Book</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('library.issues.index') }}">Issues</a></li>
                            <li class="breadcrumb-item active">Return</li>
                        </ol>
                    </nav>

                    @if(session('status'))
                        <div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif

                    <div class="row g-4">
                        {{-- Lookup / selected issue --}}
                        <div class="col-md-5">
                            <div class="card shadow-sm">
                                <div class="card-header fw-semibold">Find Issue Record</div>
                                <div class="card-body">
                                    <form method="GET" action="{{ route('library.issues.return') }}">
                                        <div class="mb-3">
                                            <label class="form-label">Select Active Issue</label>
                                            <select name="issue_id" class="form-select" onchange="this.form.submit()">
                                                <option value="">— Select —</option>
                                                @foreach($activeIssues as $ai)
                                                    <option value="{{ $ai->id }}" @selected($issue && $issue->id == $ai->id)>
                                                        {{ $ai->book->title }} → {{ $ai->member->user->first_name }} {{ $ai->member->user->last_name }}
                                                        (due {{ $ai->due_date->format('d M Y') }})
                                                        @if($ai->is_overdue) ⚠ OVERDUE @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Return form --}}
                        @if($issue)
                        <div class="col-md-7">
                            <div class="card shadow-sm border-{{ $issue->is_overdue ? 'danger' : 'success' }}">
                                <div class="card-header fw-semibold bg-{{ $issue->is_overdue ? 'danger' : 'success' }} bg-opacity-10">
                                    <i class="bi bi-book"></i> {{ $issue->book->title }}
                                </div>
                                <div class="card-body">
                                    <dl class="row mb-3">
                                        <dt class="col-sm-5">Member</dt>
                                        <dd class="col-sm-7">{{ $issue->member->user->first_name }} {{ $issue->member->user->last_name }} <code class="small">{{ $issue->member->card_number }}</code></dd>
                                        <dt class="col-sm-5">Issue Date</dt>
                                        <dd class="col-sm-7">{{ $issue->issue_date->format('d M Y') }}</dd>
                                        <dt class="col-sm-5">Due Date</dt>
                                        <dd class="col-sm-7 {{ $issue->is_overdue ? 'text-danger fw-bold' : '' }}">
                                            {{ $issue->due_date->format('d M Y') }}
                                            @if($issue->is_overdue)
                                                <span class="badge bg-danger ms-1">
                                                    {{ $issue->calculated_overdue_days }} days overdue
                                                </span>
                                            @endif
                                        </dd>
                                        <dt class="col-sm-5">Return Date</dt>
                                        <dd class="col-sm-7">{{ now()->format('d M Y') }} (today)</dd>
                                        <dt class="col-sm-5">Fine Per Day</dt>
                                        <dd class="col-sm-7">${{ number_format($issue->fine_per_day, 2) }}</dd>
                                        <dt class="col-sm-5">Estimated Fine</dt>
                                        <dd class="col-sm-7">
                                            @if($issue->calculated_fine > 0)
                                                <span class="text-danger fw-bold">${{ number_format($issue->calculated_fine, 2) }}</span>
                                            @else
                                                <span class="text-success">$0.00 — no fine</span>
                                            @endif
                                        </dd>
                                    </dl>

                                    <form action="{{ route('library.issues.return.process', $issue->id) }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">Notes (optional)</label>
                                            <textarea name="notes" class="form-control" rows="2" placeholder="Condition of book, remarks…"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-check-circle"></i> Confirm Return
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
