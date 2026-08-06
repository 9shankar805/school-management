@extends('layouts.app')
@section('title', 'Consumables')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-bold text-gray-800 mb-1"><i class="bi bi-box-seam text-indigo-400"></i> Consumables</h2>
            <p class="text-muted mb-0">Manage stock levels for chalk, stationery, and lab equipment.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('inventory.dashboard') }}" class="btn btn-outline-secondary shadow-sm"><i class="bi bi-arrow-left"></i> Dashboard</a>
            <a href="{{ route('inventory.items.create') }}" class="btn btn-primary shadow-sm"><i class="bi bi-plus-lg"></i> Add New Item</a>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3 text-muted text-xs text-uppercase font-semibold">Item Details</th>
                            <th class="py-3 text-muted text-xs text-uppercase font-semibold">Category</th>
                            <th class="py-3 text-muted text-xs text-uppercase font-semibold text-end">In Stock</th>
                            <th class="py-3 text-muted text-xs text-uppercase font-semibold text-end">Min Threshold</th>
                            <th class="px-4 py-3 text-muted text-xs text-uppercase font-semibold text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-bold text-gray-800">{{ $item->name }}</div>
                                <div class="text-xs text-muted">SKU: {{ $item->sku }}</div>
                            </td>
                            <td class="py-3">
                                <span class="badge bg-secondary rounded-pill px-3">{{ $item->category->name ?? 'General' }}</span>
                            </td>
                            <td class="py-3 text-end">
                                <span class="badge {{ $item->quantity_in_stock <= $item->min_stock_level ? 'bg-danger' : 'bg-green-500' }} rounded-pill px-3">
                                    {{ $item->quantity_in_stock }} {{ $item->unit }}
                                </span>
                            </td>
                            <td class="py-3 text-end text-muted">{{ $item->min_stock_level }} {{ $item->unit }}</td>
                            <td class="px-4 py-3 text-center">
                                <button class="btn btn-sm btn-outline-success rounded-pill px-3"><i class="bi bi-plus-circle"></i> Add</button>
                                <button class="btn btn-sm btn-outline-warning rounded-pill px-3"><i class="bi bi-dash-circle"></i> Issue</button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted"><i class="bi bi-box-seam display-4 text-gray-300"></i></div>
                                <h5 class="mt-3 text-gray-600">No Items Found</h5>
                                <p class="text-sm text-gray-400">Start tracking stock by adding your first inventory item.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if(isset($items) && $items->hasPages())
                <div class="card-footer bg-white border-top-0 px-4 py-3">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
