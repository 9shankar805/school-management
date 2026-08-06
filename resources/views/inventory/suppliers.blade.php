@extends('layouts.app')
@section('title', 'Suppliers')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-bold text-gray-800 mb-1"><i class="bi bi-truck text-indigo-400"></i> Suppliers</h2>
            <p class="text-muted mb-0">Manage vendor contact details and catalogs.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('inventory.dashboard') }}" class="btn btn-outline-secondary shadow-sm"><i class="bi bi-arrow-left"></i> Dashboard</a>
            <button class="btn btn-primary shadow-sm"><i class="bi bi-plus-lg"></i> Add Supplier</button>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3 text-muted text-xs text-uppercase font-semibold">Supplier Name</th>
                            <th class="py-3 text-muted text-xs text-uppercase font-semibold">Contact Person</th>
                            <th class="py-3 text-muted text-xs text-uppercase font-semibold">Email / Phone</th>
                            <th class="py-3 text-muted text-xs text-uppercase font-semibold">Status</th>
                            <th class="px-4 py-3 text-muted text-xs text-uppercase font-semibold text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $supplier)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-indigo-50 text-indigo-600 rounded-circle d-flex justify-content-center align-items-center me-3 font-bold" style="width: 40px; height: 40px;">
                                        {{ substr($supplier->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-800">{{ $supplier->name }}</div>
                                        <div class="text-xs text-muted">{{ $supplier->address ?? 'No address on file' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 font-semibold text-sm">{{ $supplier->contact_person }}</td>
                            <td class="py-3">
                                <div class="text-sm"><i class="bi bi-envelope text-muted"></i> {{ $supplier->email ?? 'N/A' }}</div>
                                <div class="text-sm"><i class="bi bi-telephone text-muted"></i> {{ $supplier->phone ?? 'N/A' }}</div>
                            </td>
                            <td class="py-3">
                                <span class="badge {{ $supplier->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} rounded-pill px-3">
                                    {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-end">
                                <button class="btn btn-sm btn-light text-primary" title="Edit"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-light text-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted"><i class="bi bi-truck display-4 text-gray-300"></i></div>
                                <h5 class="mt-3 text-gray-600">No Suppliers Found</h5>
                                <p class="text-sm text-gray-400">Add a supplier to start issuing purchase orders.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if(isset($suppliers) && $suppliers->hasPages())
                <div class="card-footer bg-white border-top-0 px-4 py-3">
                    {{ $suppliers->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
