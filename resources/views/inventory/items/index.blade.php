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
                            <h1 class="display-6 mb-0"><i class="bi bi-box-seam"></i> Consumable Stock</h1>
                            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item active">Stock Items</li>
                            </ol></nav>
                        </div>
                        @can('manage inventory')
                        <a href="{{ route('inventory.items.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Add Item
                        </a>
                        @endcan
                    </div>

                    @include('session-messages')

                    {{-- Stock alerts --}}
                    @if($outOfStockCount > 0)
                    <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-2">
                        <i class="bi bi-exclamation-octagon-fill"></i>
                        <strong>{{ $outOfStockCount }}</strong> item(s) are completely out of stock.
                        <a href="{{ request()->fullUrlWithQuery(['low_stock' => 1]) }}" class="ms-auto btn btn-sm btn-danger">View</a>
                    </div>
                    @endif
                    @if($lowStockCount > 0)
                    <div class="alert alert-warning d-flex align-items-center gap-2 py-2 mb-3">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <strong>{{ $lowStockCount }}</strong> item(s) are at or below their reorder level.
                        <a href="{{ request()->fullUrlWithQuery(['low_stock' => 1]) }}" class="ms-auto btn btn-sm btn-warning">View Low Stock</a>
                    </div>
                    @endif

                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-sm-3">
                            <input type="text" name="search" value="{{ request('search') }}"
                                   class="form-control form-control-sm" placeholder="Name / code…">
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
                            <select name="warehouse_id" class="form-select form-select-sm">
                                <option value="">All Stores</option>
                                @foreach($warehouses as $wid => $wname)
                                <option value="{{ $wid }}" @selected(request('warehouse_id')==$wid)>{{ $wname }}</option>
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
                        <div class="col-sm-auto d-flex gap-1 align-items-center">
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="checkbox" name="low_stock" value="1"
                                       id="chkLow" @checked(request()->boolean('low_stock'))>
                                <label class="form-check-label small" for="chkLow">Low stock only</label>
                            </div>
                            <button class="btn btn-sm btn-secondary">Filter</button>
                            <a href="{{ route('inventory.items.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
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
                                        <th>Store</th>
                                        <th class="text-center">Stock</th>
                                        <th class="text-center">Reorder At</th>
                                        <th>Unit</th>
                                        <th>Unit Price</th>
                                        <th>Stock Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $item)
                                    <tr>
                                        <td><code>{{ $item->item_code }}</code></td>
                                        <td class="fw-semibold">{{ $item->name }}</td>
                                        <td>{{ $item->category_label }}</td>
                                        <td>{{ $item->warehouse?->name ?? '—' }}</td>
                                        <td class="text-center fw-bold {{ $item->quantity_in_stock == 0 ? 'text-danger' : ($item->is_low_stock ? 'text-warning' : '') }}">
                                            {{ $item->quantity_in_stock }}
                                        </td>
                                        <td class="text-center text-muted">{{ $item->reorder_level }}</td>
                                        <td>{{ $item->unit_label }}</td>
                                        <td>{{ $item->unit_price ? '$'.number_format($item->unit_price,2) : '—' }}</td>
                                        <td><span class="badge {{ $item->stock_badge }}">{{ $item->stock_label }}</span></td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                @can('manage inventory')
                                                {{-- Quick adjust stock modal trigger --}}
                                                <button type="button" class="btn btn-sm btn-outline-success"
                                                        title="Adjust Stock"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#adjustModal"
                                                        data-id="{{ $item->id }}"
                                                        data-name="{{ $item->name }}"
                                                        data-qty="{{ $item->quantity_in_stock }}">
                                                    <i class="bi bi-arrow-left-right"></i>
                                                </button>
                                                <a href="{{ route('inventory.items.edit', $item->id) }}"
                                                   class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form method="POST" action="{{ route('inventory.items.destroy', $item->id) }}"
                                                      class="d-inline" onsubmit="return confirm('Delete this item?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="10" class="text-center text-muted py-4">No items found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="mt-3">{{ $items->links() }}</div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>

{{-- Adjust Stock Modal --}}
@can('manage inventory')
<div class="modal fade" id="adjustModal" tabindex="-1" aria-labelledby="adjustModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <form method="POST" id="adjustForm" action="">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="adjustModalLabel">Adjust Stock</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Item: <strong id="adjustItemName"></strong></p>
                    <p class="mb-3">Current qty: <strong id="adjustCurrentQty"></strong></p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Adjustment <span class="text-danger">*</span></label>
                        <input type="number" name="adjustment" class="form-control" required
                               placeholder="+10 to add, -5 to deduct">
                        <div class="form-text">Positive = receive stock. Negative = issue / deduct.</div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Reason</label>
                        <input type="text" name="reason" class="form-control" placeholder="Optional">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('adjustModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    document.getElementById('adjustItemName').textContent  = btn.dataset.name;
    document.getElementById('adjustCurrentQty').textContent = btn.dataset.qty;
    document.getElementById('adjustForm').action =
        '/inventory/items/' + btn.dataset.id + '/adjust-stock';
});
</script>
@endpush
@endcan

@endsection
