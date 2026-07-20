@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h1 class="display-6 mb-0"><i class="bi bi-percent"></i> Discounts &amp; Waivers</h1>
                            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item active">Discounts</li>
                            </ol></nav>
                        </div>
                        @can('create invoices')
                        <a href="{{ route('finance.discounts.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> New Discount
                        </a>
                        @endcan
                    </div>

                    @include('session-messages')

                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th><th>Type</th><th>Value</th>
                                            <th>Category</th><th>Student</th>
                                            <th>Valid Until</th><th>Status</th><th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($discounts as $d)
                                        <tr>
                                            <td class="fw-semibold">{{ $d->name }}</td>
                                            <td>{{ ucfirst($d->type) }}</td>
                                            <td>
                                                @if($d->type === 'percentage')
                                                    {{ $d->value }}%
                                                @else
                                                    ${{ number_format($d->value, 2) }}
                                                @endif
                                            </td>
                                            <td>{{ optional($d->feeCategory)->name ?? '<span class="text-muted">All</span>' }}</td>
                                            <td>{{ optional($d->student)->full_name ?? '<span class="text-muted">Global</span>' }}</td>
                                            <td>{{ $d->valid_until?->format('d M Y') ?? '—' }}</td>
                                            <td><span class="badge {{ $d->status_badge }}">{{ $d->is_active ? 'Active' : 'Inactive' }}</span></td>
                                            <td>
                                                @can('create invoices')
                                                <button class="btn btn-sm btn-outline-secondary"
                                                        onclick="openEdit({{ $d->id }}, {{ json_encode($d) }})">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form method="POST" action="{{ route('finance.discounts.destroy', $d->id) }}"
                                                      class="d-inline" onsubmit="return confirm('Delete this discount?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                </form>
                                                @endcan
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="8" class="text-center text-muted py-4">No discounts defined.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">{{ $discounts->links() }}</div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
