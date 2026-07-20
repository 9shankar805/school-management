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
                            <h1 class="display-6 mb-0"><i class="bi bi-layout-text-sidebar"></i> Fee Structures</h1>
                            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item active">Fee Structures</li>
                            </ol></nav>
                        </div>
                        @can('create invoices')
                        <a href="{{ route('finance.structures.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> New Structure
                        </a>
                        @endcan
                    </div>

                    @include('session-messages')

                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th><th>Session</th><th>Class</th>
                                        <th>Term</th><th class="text-end">Total</th>
                                        <th>Items</th><th>Status</th><th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($structures as $s)
                                    <tr>
                                        <td class="fw-semibold">{{ $s->name }}</td>
                                        <td>{{ optional($s->session)->session_name ?? '—' }}</td>
                                        <td>{{ optional($s->schoolClass)->name ?? '—' }}</td>
                                        <td>{{ optional($s->term)->name ?? '—' }}</td>
                                        <td class="text-end fw-semibold">${{ number_format($s->total_amount, 2) }}</td>
                                        <td><span class="badge bg-secondary">{{ $s->items->count() }}</span></td>
                                        <td>
                                            <span class="badge {{ $s->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $s->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('finance.structures.edit', $s->id) }}"
                                                   class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @can('create invoices')
                                                <form method="POST" action="{{ route('finance.structures.destroy', $s->id) }}"
                                                      onsubmit="return confirm('Delete this fee structure?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="8" class="text-center text-muted py-4">No fee structures defined yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="mt-3">{{ $structures->links() }}</div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
