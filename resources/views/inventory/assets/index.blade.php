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
                            <h1 class="display-6 mb-0"><i class="bi bi-clipboard-check"></i> Asset Register</h1>
                            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item active">Assets</li>
                            </ol></nav>
                        </div>
                        @can('manage inventory')
                        <a href="{{ route('inventory.assets.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Register Asset
                        </a>
                        @endcan
                    </div>

                    @include('session-messages')

                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-sm-3">
                            <input type="text" name="search" value="{{ request('search') }}"
                                   class="form-control form-control-sm" placeholder="Name / code / serial…">
                        </div>
                        <div class="col-sm-2">
                            <select name="category" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                @foreach($categories as $k => $v)
                                <option value="{{ $k }}" @selected(request('category')==$k)>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Statuses</option>
                                @foreach($statuses as $k => $v)
                                <option value="{{ $k }}" @selected(request('status')==$k)>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <select name="condition" class="form-select form-select-sm">
                                <option value="">All Conditions</option>
                                @foreach($conditions as $k => $v)
                                <option value="{{ $k }}" @selected(request('condition')==$k)>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <select name="warehouse_id" class="form-select form-select-sm">
                                <option value="">All Stores</option>
                                @foreach($warehouses as $wid => $wname)
                                <option value="{{ $wid }}" @selected(request('warehouse_id')==$wid)>{{ $wname }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-auto">
                            <button class="btn btn-sm btn-secondary">Filter</button>
                            <a href="{{ route('inventory.assets.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                        </div>
                    </form>

                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Location</th>
                                        <th>Condition</th>
                                        <th>Status</th>
                                        <th>Purchase Price</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($assets as $a)
                                    <tr>
                                        <td><code>{{ $a->asset_code }}</code></td>
                                        <td class="fw-semibold">
                                            <a href="{{ route('inventory.assets.show', $a->id) }}" class="text-decoration-none">
                                                {{ $a->name }}
                                            </a>
                                        </td>
                                        <td>{{ $a->category_label }}</td>
                                        <td>{{ $a->location ?? '—' }}</td>
                                        <td><span class="badge {{ $a->condition_badge }}">{{ $a->condition_label }}</span></td>
                                        <td><span class="badge {{ $a->status_badge }}">{{ \App\Models\Asset::STATUSES[$a->status] ?? $a->status }}</span></td>
                                        <td>{{ $a->purchase_price ? '$'.number_format($a->purchase_price,2) : '—' }}</td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('inventory.assets.show', $a->id) }}"
                                                   class="btn btn-sm btn-outline-primary" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @can('manage inventory')
                                                <a href="{{ route('inventory.assets.edit', $a->id) }}"
                                                   class="btn btn-sm btn-outline-secondary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form method="POST" action="{{ route('inventory.assets.destroy', $a->id) }}"
                                                      class="d-inline" onsubmit="return confirm('Delete this asset?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="8" class="text-center text-muted py-4">No assets found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="mt-3">{{ $assets->links() }}</div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
