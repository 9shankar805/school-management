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
                            <h1 class="display-6 mb-0"><i class="bi bi-clipboard-check"></i> {{ $asset->name }}</h1>
                            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('inventory.assets.index') }}">Assets</a></li>
                                <li class="breadcrumb-item active">{{ $asset->asset_code }}</li>
                            </ol></nav>
                        </div>
                        @can('manage inventory')
                        <div class="d-flex gap-2">
                            <a href="{{ route('inventory.assets.edit', $asset->id) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                        </div>
                        @endcan
                    </div>

                    @include('session-messages')

                    <div class="row g-4">
                        {{-- Detail card --}}
                        <div class="col-md-5">
                            <div class="card shadow-sm h-100">
                                @if($asset->image_path)
                                <img src="{{ asset('storage/'.$asset->image_path) }}"
                                     class="card-img-top object-fit-cover" style="max-height:220px" alt="{{ $asset->name }}">
                                @endif
                                <div class="card-body">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr><th class="text-muted" style="width:40%">Code</th><td><code>{{ $asset->asset_code }}</code></td></tr>
                                        <tr><th class="text-muted">Category</th><td>{{ $asset->category_label }}</td></tr>
                                        <tr><th class="text-muted">Brand / Model</th><td>{{ $asset->brand ?? '—' }} / {{ $asset->model ?? '—' }}</td></tr>
                                        <tr><th class="text-muted">Serial No.</th><td>{{ $asset->serial_number ?? '—' }}</td></tr>
                                        <tr><th class="text-muted">Condition</th>
                                            <td><span class="badge {{ $asset->condition_badge }}">{{ $asset->condition_label }}</span></td></tr>
                                        <tr><th class="text-muted">Status</th>
                                            <td><span class="badge {{ $asset->status_badge }}">{{ \App\Models\Asset::STATUSES[$asset->status] ?? $asset->status }}</span></td></tr>
                                        <tr><th class="text-muted">Location</th><td>{{ $asset->location ?? '—' }}</td></tr>
                                        <tr><th class="text-muted">Assigned To</th><td>{{ $asset->assigned_to ?? '—' }}</td></tr>
                                        <tr><th class="text-muted">Store</th><td>{{ $asset->warehouse?->name ?? '—' }}</td></tr>
                                        <tr><th class="text-muted">Supplier</th><td>{{ $asset->supplier?->name ?? '—' }}</td></tr>
                                        <tr><th class="text-muted">Purchase Date</th><td>{{ $asset->purchase_date?->format('d M Y') ?? '—' }}</td></tr>
                                        <tr><th class="text-muted">Purchase Price</th><td>{{ $asset->purchase_price ? '$'.number_format($asset->purchase_price,2) : '—' }}</td></tr>
                                        <tr><th class="text-muted">Current Value</th><td>{{ $asset->current_value ? '$'.number_format($asset->current_value,2) : '—' }}</td></tr>
                                        <tr><th class="text-muted">Warranty Expiry</th>
                                            <td>
                                                @if($asset->warranty_expiry)
                                                    <span class="{{ $asset->warranty_expiry->isPast() ? 'text-danger' : '' }}">
                                                        {{ $asset->warranty_expiry->format('d M Y') }}
                                                        @if($asset->warranty_expiry->isPast()) (Expired) @endif
                                                    </span>
                                                @else —
                                                @endif
                                            </td></tr>
                                    </table>
                                    @if($asset->description)
                                    <p class="text-muted small mt-3 mb-0">{{ $asset->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Maintenance log panel --}}
                        <div class="col-md-7">
                            <div class="card shadow-sm">
                                <div class="card-header d-flex align-items-center justify-content-between">
                                    <span class="fw-semibold"><i class="bi bi-tools me-1"></i> Maintenance Log</span>
                                </div>
                                <div class="card-body p-0" style="max-height:280px;overflow-y:auto">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th><th>Type</th><th>Status</th>
                                                <th>Cost</th><th>Next Due</th>
                                                @can('manage inventory')<th></th>@endcan
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($asset->maintenanceLogs as $log)
                                            <tr>
                                                <td>{{ $log->maintenance_date->format('d M Y') }}</td>
                                                <td>{{ $log->type_label }}</td>
                                                <td><span class="badge {{ $log->status_badge }}">{{ \App\Models\AssetMaintenanceLog::STATUSES[$log->status] ?? $log->status }}</span></td>
                                                <td>{{ $log->cost ? '$'.number_format($log->cost,2) : '—' }}</td>
                                                <td>{{ $log->next_due_date?->format('d M Y') ?? '—' }}</td>
                                                @can('manage inventory')
                                                <td>
                                                    <form method="POST"
                                                          action="{{ route('inventory.assets.maintenance.destroy', $log->id) }}"
                                                          class="d-inline" onsubmit="return confirm('Remove this log?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-sm btn-link text-danger p-0">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                                @endcan
                                            </tr>
                                            @empty
                                            <tr><td colspan="6" class="text-muted text-center py-3">No maintenance records.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                @can('manage inventory')
                                <div class="card-footer">
                                    <p class="fw-semibold small mb-2">Add Maintenance Entry</p>
                                    <form method="POST" action="{{ route('inventory.assets.maintenance.store', $asset->id) }}">
                                        @csrf
                                        <div class="row g-2">
                                            <div class="col-md-3">
                                                <label class="form-label form-label-sm">Type <span class="text-danger">*</span></label>
                                                <select name="type" class="form-select form-select-sm" required>
                                                    @foreach(\App\Models\AssetMaintenanceLog::TYPES as $k => $v)
                                                    <option value="{{ $k }}">{{ $v }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label form-label-sm">Date <span class="text-danger">*</span></label>
                                                <input type="date" name="maintenance_date" class="form-control form-control-sm"
                                                       value="{{ now()->toDateString() }}" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label form-label-sm">Status <span class="text-danger">*</span></label>
                                                <select name="status" class="form-select form-select-sm" required>
                                                    @foreach(\App\Models\AssetMaintenanceLog::STATUSES as $k => $v)
                                                    <option value="{{ $k }}" @selected($k=='completed')>{{ $v }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label form-label-sm">Cost ($)</label>
                                                <input type="number" name="cost" step="0.01" min="0"
                                                       class="form-control form-control-sm" placeholder="0.00">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label form-label-sm">Next Due</label>
                                                <input type="date" name="next_due_date" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label form-label-sm">Vendor</label>
                                                <input type="text" name="vendor" class="form-control form-control-sm" placeholder="Repair vendor">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label form-label-sm">Description <span class="text-danger">*</span></label>
                                                <textarea name="description" class="form-control form-control-sm" rows="2" required
                                                          placeholder="What was done?"></textarea>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label form-label-sm">Findings / Notes</label>
                                                <textarea name="findings" class="form-control form-control-sm" rows="1"
                                                          placeholder="Observations, parts replaced, etc."></textarea>
                                            </div>
                                            <div class="col-auto">
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-plus-circle"></i> Add Log
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                @endcan
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
