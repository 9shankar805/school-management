@extends('layouts.app')
@section('title', 'Fixed Assets')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-bold text-gray-800 mb-1"><i class="bi bi-pc-display text-indigo-400"></i> Fixed Assets</h2>
            <p class="text-muted mb-0">Track computers, furniture, vehicles, and their depreciation.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('inventory.dashboard') }}" class="btn btn-outline-secondary shadow-sm"><i class="bi bi-arrow-left"></i> Dashboard</a>
            <a href="{{ route('inventory.assets.create') }}" class="btn btn-primary shadow-sm"><i class="bi bi-plus-lg"></i> Register Asset</a>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3 text-muted text-xs text-uppercase font-semibold">Asset Tag & Name</th>
                            <th class="py-3 text-muted text-xs text-uppercase font-semibold">Category</th>
                            <th class="py-3 text-muted text-xs text-uppercase font-semibold">Assigned To</th>
                            <th class="py-3 text-muted text-xs text-uppercase font-semibold">Status</th>
                            <th class="px-4 py-3 text-muted text-xs text-uppercase font-semibold text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $asset)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-bold text-gray-800">{{ $asset->name }}</div>
                                <div class="text-xs text-muted font-monospace"><i class="bi bi-upc-scan"></i> {{ $asset->asset_tag }}</div>
                            </td>
                            <td class="py-3">
                                <span class="badge bg-secondary rounded-pill px-3">{{ $asset->category->name ?? 'Uncategorized' }}</span>
                            </td>
                            <td class="py-3">
                                @if($asset->status == 'assigned')
                                    <div class="text-sm font-semibold">{{ $asset->assignedUser->name ?? 'Unknown User' }}</div>
                                    <div class="text-xs text-muted">Assigned: {{ $asset->assigned_at ? $asset->assigned_at->format('M d, Y') : 'N/A' }}</div>
                                @else
                                    <span class="text-muted text-sm fst-italic">Not Assigned</span>
                                @endif
                            </td>
                            <td class="py-3">
                                @php
                                    $statusBadge = match($asset->status) {
                                        'available' => 'bg-green-100 text-green-700',
                                        'assigned' => 'bg-blue-100 text-blue-700',
                                        'maintenance' => 'bg-warning-100 text-warning-700',
                                        'disposed' => 'bg-gray-200 text-gray-700',
                                        default => 'bg-light text-dark'
                                    };
                                @endphp
                                <span class="badge {{ $statusBadge }} rounded-pill px-3 text-capitalize">
                                    {{ $asset->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-end">
                                <button class="btn btn-sm btn-light text-primary" title="Edit"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-light text-indigo-500" title="Assign"><i class="bi bi-person-plus"></i></button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted"><i class="bi bi-pc-display display-4 text-gray-300"></i></div>
                                <h5 class="mt-3 text-gray-600">No Assets Registered</h5>
                                <p class="text-sm text-gray-400">Add your first fixed asset to start tracking it.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if(isset($assets) && $assets->hasPages())
                <div class="card-footer bg-white border-top-0 px-4 py-3">
                    {{ $assets->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
