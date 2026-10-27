@extends('layouts.app')
@section('title', 'Employee Records')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-bold text-gray-800 mb-1"><i class="bi bi-person-lines-fill text-indigo-500"></i> Employee Records</h2>
            <p class="text-muted mb-0">Manage staff contracts, performance reviews, and exits.</p>
        </div>
        <a href="{{ route('staff.contracts.index') }}" class="btn btn-primary shadow-sm"><i class="bi bi-file-earmark-text"></i> Manage Contracts</a>
    </div>

    <!-- Data Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3 text-muted text-xs text-uppercase font-semibold">Employee</th>
                            <th class="py-3 text-muted text-xs text-uppercase font-semibold">Contract Type</th>
                            <th class="py-3 text-muted text-xs text-uppercase font-semibold">Start Date</th>
                            <th class="py-3 text-muted text-xs text-uppercase font-semibold">End Date</th>
                            <th class="py-3 text-muted text-xs text-uppercase font-semibold text-center">Status</th>
                            <th class="px-4 py-3 text-muted text-xs text-uppercase font-semibold text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contracts as $contract)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-bold text-gray-800">{{ $contract->user->full_name ?? $contract->user->name ?? 'Unknown Employee' }}</div>
                                <div class="text-xs text-muted">Role: {{ ucfirst($contract->user->roles->first()->name ?? $contract->user->role ?? 'Staff') }}</div>
                            </td>
                            <td class="py-3 font-semibold text-sm">
                                {{ ucfirst(str_replace('_', ' ', $contract->contract_type)) }}
                            </td>
                            <td class="py-3 text-sm">
                                {{ \Carbon\Carbon::parse($contract->start_date)->format('M d, Y') }}
                            </td>
                            <td class="py-3 text-sm">
                                @if($contract->end_date)
                                    @php
                                        $daysLeft = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($contract->end_date), false);
                                    @endphp
                                    <span class="{{ $daysLeft < 30 && $daysLeft > 0 ? 'text-danger font-bold' : '' }}">
                                        {{ \Carbon\Carbon::parse($contract->end_date)->format('M d, Y') }}
                                    </span>
                                    @if($daysLeft < 30 && $daysLeft > 0)
                                        <div class="text-xs text-danger"><i class="bi bi-exclamation-circle"></i> Expiring Soon</div>
                                    @endif
                                @else
                                    <span class="text-muted fst-italic">Permanent</span>
                                @endif
                            </td>
                            <td class="py-3 text-center">
                                @php
                                    $cStatus = $contract->status ?? ($contract->is_active ? 'active' : 'expired');
                                @endphp
                                <span class="badge {{ $cStatus === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} rounded-pill px-3">
                                    {{ ucfirst($cStatus) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-end">
                                @can('manage roles')
                                    @isset($contract->user)
                                    <a href="{{ route('roles.user-roles', $contract->user->id) }}"
                                       class="btn btn-sm btn-outline-success me-1" title="Assign Roles">
                                        <i class="bi bi-person-check"></i>
                                    </a>
                                    @endisset
                                @endcan
                                @if($contract->user_id)
                                <a href="{{ route('staff.edit', $contract->user_id) }}" class="btn btn-sm btn-light text-primary" title="Edit Staff Profile"><i class="bi bi-pencil-square"></i></a>
                                @endif
                                <a href="{{ route('staff.contracts.index') }}" class="btn btn-sm btn-light text-indigo-600" title="Contracts Vault"><i class="bi bi-file-earmark-text"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted"><i class="bi bi-file-earmark-person display-4 text-gray-300"></i></div>
                                <h5 class="mt-3 text-gray-600">No Employee Contracts</h5>
                                <p class="text-sm text-gray-400">Add staff contracts to start tracking employee lifecycles.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if(isset($contracts) && $contracts->hasPages())
                <div class="card-footer bg-white border-top-0 px-4 py-3">
                    {{ $contracts->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
