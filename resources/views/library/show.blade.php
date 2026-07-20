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
                            <li class="breadcrumb-item"><a href="{{ route('library.index') }}">Library</a></li>
                            <li class="breadcrumb-item active">{{ Str::limit($book->title, 40) }}</li>
                        </ol>
                    </nav>

                    @if(session('status'))
                        <div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif

                    <div class="row g-4">
                        {{-- Book detail card --}}
                        <div class="col-md-4">
                            <div class="card shadow-sm text-center">
                                <div class="card-body">
                                    @if($book->cover_image)
                                        <img src="{{ Storage::url($book->cover_image) }}" alt="{{ $book->title }}" class="img-fluid rounded mb-3" style="max-height:220px;object-fit:cover">
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" style="height:160px">
                                            <i class="bi bi-book text-muted" style="font-size:4rem"></i>
                                        </div>
                                    @endif
                                    <h5 class="fw-bold">{{ $book->title }}</h5>
                                    <p class="text-muted mb-1">{{ $book->author ?? '—' }}</p>
                                    @if($book->category)
                                        <span class="badge mb-2" style="background-color:{{ $book->category->color }}">{{ $book->category->name }}</span>
                                    @endif
                                    <div class="mt-2">{!! $book->availability_badge !!}</div>
                                </div>
                                <div class="card-footer d-flex gap-2 justify-content-center">
                                    @can('edit books')
                                    <a href="{{ route('library.edit', $book->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
                                    @endcan
                                    @can('issue books')
                                    <a href="{{ route('library.issues.create', ['book_id' => $book->id]) }}" class="btn btn-sm btn-success"><i class="bi bi-box-arrow-up-right"></i> Issue</a>
                                    @endcan
                                </div>
                            </div>
                        </div>

                        {{-- Details + issue history --}}
                        <div class="col-md-8">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header fw-semibold"><i class="bi bi-info-circle"></i> Book Details</div>
                                <div class="card-body">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-4">Publisher</dt>
                                        <dd class="col-sm-8">{{ $book->publisher ?? '—' }}</dd>
                                        <dt class="col-sm-4">Edition</dt>
                                        <dd class="col-sm-8">{{ $book->edition ?? '—' }}</dd>
                                        <dt class="col-sm-4">Year</dt>
                                        <dd class="col-sm-8">{{ $book->publication_year ?? '—' }}</dd>
                                        <dt class="col-sm-4">Language</dt>
                                        <dd class="col-sm-8">{{ $book->language }}</dd>
                                        <dt class="col-sm-4">ISBN</dt>
                                        <dd class="col-sm-8">{{ $book->isbn ?? '—' }}</dd>
                                        <dt class="col-sm-4">Barcode</dt>
                                        <dd class="col-sm-8">{{ $book->barcode ?? '—' }}</dd>
                                        <dt class="col-sm-4">Shelf</dt>
                                        <dd class="col-sm-8">{{ $book->shelf_location ?? '—' }}</dd>
                                        <dt class="col-sm-4">Price</dt>
                                        <dd class="col-sm-8">{{ $book->price ? '$' . number_format($book->price, 2) : '—' }}</dd>
                                        <dt class="col-sm-4">Total Copies</dt>
                                        <dd class="col-sm-8">{{ $book->qty }}</dd>
                                        <dt class="col-sm-4">Available</dt>
                                        <dd class="col-sm-8">{{ $book->available_qty }}</dd>
                                        <dt class="col-sm-4">On Loan</dt>
                                        <dd class="col-sm-8">{{ $book->active_issues_count }}</dd>
                                    </dl>
                                    @if($book->description)
                                        <hr>
                                        <p class="mb-0 text-muted small">{{ $book->description }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="card shadow-sm">
                                <div class="card-header fw-semibold"><i class="bi bi-clock-history"></i> Recent Issue History</div>
                                <div class="card-body p-0">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Member</th>
                                                <th>Issued</th>
                                                <th>Due</th>
                                                <th>Returned</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentIssues as $issue)
                                            <tr>
                                                <td>{{ $issue->member->user->first_name ?? '—' }} {{ $issue->member->user->last_name ?? '' }}</td>
                                                <td>{{ $issue->issue_date->format('d M Y') }}</td>
                                                <td>{{ $issue->due_date->format('d M Y') }}</td>
                                                <td>{{ $issue->return_date ? $issue->return_date->format('d M Y') : '—' }}</td>
                                                <td>
                                                    @php
                                                        $badgeClass = match($issue->status) {
                                                            'issued'   => 'bg-primary',
                                                            'returned' => 'bg-success',
                                                            'overdue'  => 'bg-danger',
                                                            'lost'     => 'bg-dark',
                                                            default    => 'bg-secondary',
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }}">{{ ucfirst($issue->status) }}</span>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="5" class="text-center text-muted py-3">No issue history.</td></tr>
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
@endsection
