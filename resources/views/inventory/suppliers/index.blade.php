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
                            <h1 class="display-6 mb-0"><i class="bi bi-truck"></i> Suppliers</h1>
                            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item active">Suppliers</li>
                            </ol></nav>
                        </div>
                        @can('manage inventory')
                        <a href="{{ route('inventory.suppliers.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Add Supplier
                        </a>
                        @endcan
                    </div>

                    @include('session-messages')

                    {{-- Filters --}}
                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-sm-3">
                            <input type="text" name="search" value="{{ request('search') }}"
                                   class="form-control form-control-sm" placeholder="Search name / email / phone…">
                        </div>
                        <div class="col-sm-2">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Statuses</option>
                                <option value="active"   @selected(request('status')=='active')>Active</option>
                                <option value="inactive" @selected(request('status')=='inactive')>Inactive</option>
                            </select>
                        </div>
                        <div class="col-sm-auto">
                            <button class="btn btn-sm btn-secondary">Filter</button>
                            <a href="{{ route('inventory.suppliers.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                        </div>
                    </form>

                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Contact Person</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($suppliers as $s)
                                    <tr>
                                        <td class="fw-semibold">{{ $s->name }}</td>
                                        <td>{{ $s->contact_person ?? '—' }}</td>
                                        <td>{{ $s->email ?? '—' }}</td>
                                        <td>{{ $s->phone ?? '—' }}</td>
                                        <td><span class="badge {{ $s->status_badge }}">{{ ucfirst($s->status) }}</span></td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                @can('manage inventory')
                                                <a href="{{ route('inventory.suppliers.edit', $s->id) }}"
                                                   class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form method="POST" action="{{ route('inventory.suppliers.destroy', $s->id) }}"
                                                      class="d-inline" onsubmit="return confirm('Delete this supplier?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">No suppliers found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="mt-3">{{ $suppliers->links() }}</div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
