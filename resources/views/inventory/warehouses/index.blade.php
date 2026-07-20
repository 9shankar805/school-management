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
                            <h1 class="display-6 mb-0"><i class="bi bi-building"></i> Stores &amp; Warehouses</h1>
                            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item active">Warehouses</li>
                            </ol></nav>
                        </div>
                        @can('manage inventory')
                        <a href="{{ route('inventory.warehouses.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Add Store
                        </a>
                        @endcan
                    </div>

                    @include('session-messages')

                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-sm-3">
                            <select name="type" class="form-select form-select-sm">
                                <option value="">All Types</option>
                                @foreach($types as $k => $v)
                                <option value="{{ $k }}" @selected(request('type')==$k)>{{ $v }}</option>
                                @endforeach
                            </select>
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
                            <a href="{{ route('inventory.warehouses.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                        </div>
                    </form>

                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Location</th>
                                        <th>Assets</th>
                                        <th>Items</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($warehouses as $w)
                                    <tr>
                                        <td><code>{{ $w->code }}</code></td>
                                        <td class="fw-semibold">{{ $w->name }}</td>
                                        <td>{{ $w->type_label }}</td>
                                        <td>{{ $w->location ?? '—' }}</td>
                                        <td><span class="badge bg-primary">{{ $w->assets_count }}</span></td>
                                        <td><span class="badge bg-info text-dark">{{ $w->inventory_items_count }}</span></td>
                                        <td><span class="badge {{ $w->status_badge }}">{{ ucfirst($w->status) }}</span></td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                @can('manage inventory')
                                                <a href="{{ route('inventory.warehouses.edit', $w->id) }}"
                                                   class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form method="POST" action="{{ route('inventory.warehouses.destroy', $w->id) }}"
                                                      class="d-inline" onsubmit="return confirm('Delete this store?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="8" class="text-center text-muted py-4">No stores found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="mt-3">{{ $warehouses->links() }}</div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
