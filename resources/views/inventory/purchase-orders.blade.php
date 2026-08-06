@extends('layouts.app')
@section('title', 'Purchase Orders')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-bold text-gray-800 mb-1"><i class="bi bi-receipt text-indigo-400"></i> Purchase Orders</h2>
            <p class="text-muted mb-0">Manage stock requests and vendor orders.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('inventory.dashboard') }}" class="btn btn-outline-secondary shadow-sm"><i class="bi bi-arrow-left"></i> Dashboard</a>
            <button class="btn btn-primary shadow-sm"><i class="bi bi-plus-lg"></i> Raise PO</button>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3 text-muted text-xs text-uppercase font-semibold">PO Number</th>
                            <th class="py-3 text-muted text-xs text-uppercase font-semibold">Supplier</th>
                            <th class="py-3 text-muted text-xs text-uppercase font-semibold">Order Date</th>
                            <th class="py-3 text-muted text-xs text-uppercase font-semibold">Total Amount</th>
                            <th class="py-3 text-muted text-xs text-uppercase font-semibold">Status</th>
                            <th class="px-4 py-3 text-muted text-xs text-uppercase font-semibold text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td class="px-4 py-3 font-monospace font-bold text-indigo-600">
                                #{{ $order->po_number }}
                            </td>
                            <td class="py-3">
                                <div class="font-bold text-gray-800">{{ $order->supplier->name ?? 'Unknown Supplier' }}</div>
                            </td>
                            <td class="py-3 text-sm">
                                {{ $order->order_date ? $order->order_date->format('M d, Y') : 'N/A' }}
                            </td>
                            <td class="py-3 font-bold text-gray-800">
                                Rs. {{ number_format($order->total_amount, 2) }}
                            </td>
                            <td class="py-3">
                                @php
                                    $statusBadge = match($order->status) {
                                        'draft' => 'bg-gray-200 text-gray-700',
                                        'pending' => 'bg-warning-100 text-warning-700',
                                        'approved' => 'bg-blue-100 text-blue-700',
                                        'ordered' => 'bg-indigo-100 text-indigo-700',
                                        'partially_received' => 'bg-purple-100 text-purple-700',
                                        'completed' => 'bg-green-100 text-green-700',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                        default => 'bg-light text-dark'
                                    };
                                @endphp
                                <span class="badge {{ $statusBadge }} rounded-pill px-3 text-capitalize">
                                    {{ str_replace('_', ' ', $order->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-end">
                                <button class="btn btn-sm btn-light text-primary" title="View Details"><i class="bi bi-eye"></i></button>
                                <button class="btn btn-sm btn-light text-success" title="Mark Received"><i class="bi bi-box-arrow-in-down"></i></button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted"><i class="bi bi-receipt display-4 text-gray-300"></i></div>
                                <h5 class="mt-3 text-gray-600">No Purchase Orders</h5>
                                <p class="text-sm text-gray-400">Create a purchase order to restock your inventory.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if(isset($orders) && $orders->hasPages())
                <div class="card-footer bg-white border-top-0 px-4 py-3">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
