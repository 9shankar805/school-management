@extends('layouts.app')
@section('title', 'Leave Management')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-bold text-gray-800 mb-1"><i class="bi bi-calendar-x text-orange-500"></i> Leave Approvals</h2>
            <p class="text-muted mb-0">Review and manage staff leave applications.</p>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3 text-muted text-xs text-uppercase font-semibold">Employee</th>
                            <th class="py-3 text-muted text-xs text-uppercase font-semibold">Leave Type</th>
                            <th class="py-3 text-muted text-xs text-uppercase font-semibold">Dates</th>
                            <th class="py-3 text-muted text-xs text-uppercase font-semibold">Duration</th>
                            <th class="py-3 text-muted text-xs text-uppercase font-semibold text-center">Status</th>
                            <th class="px-4 py-3 text-muted text-xs text-uppercase font-semibold text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaves as $leave)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-blue-50 text-blue-600 rounded-circle d-flex justify-content-center align-items-center me-3 font-bold" style="width: 40px; height: 40px;">
                                        {{ substr($leave->user->first_name ?? $leave->user->name ?? 'U', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-800">{{ $leave->user->full_name ?? $leave->user->name ?? 'Unknown' }}</div>
                                        <div class="text-xs text-muted">Applied: {{ $leave->created_at ? $leave->created_at->format('M d') : '—' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 font-semibold text-sm">{{ $leave->leaveType->name ?? 'General Leave' }}</td>
                            <td class="py-3">
                                <div class="text-sm">{{ \Carbon\Carbon::parse($leave->from_date ?? $leave->start_date)->format('M d, Y') }}</div>
                                <div class="text-xs text-muted">to {{ \Carbon\Carbon::parse($leave->to_date ?? $leave->end_date)->format('M d, Y') }}</div>
                            </td>
                            <td class="py-3 font-bold text-gray-800">
                                {{ $leave->total_days ?? (\Carbon\Carbon::parse($leave->from_date ?? $leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->to_date ?? $leave->end_date)) + 1) }} Days
                            </td>
                            <td class="py-3 text-center">
                                @php
                                    $badge = match($leave->status) {
                                        'approved' => 'bg-green-100 text-green-700',
                                        'rejected' => 'bg-red-100 text-red-700',
                                        default => 'bg-warning-100 text-warning-700'
                                    };
                                @endphp
                                <span class="badge {{ $badge }} rounded-pill px-3">{{ ucfirst($leave->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-end">
                                @if($leave->status == 'pending')
                                    <form action="{{ route('hr.leaves.status', $leave->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-3 me-1"><i class="bi bi-check-lg"></i> Approve</button>
                                    </form>
                                    <form action="{{ route('hr.leaves.status', $leave->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3"><i class="bi bi-x-lg"></i> Reject</button>
                                    </form>
                                @else
                                    <span class="text-muted text-sm fst-italic">Processed</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted"><i class="bi bi-calendar-check display-4 text-gray-300"></i></div>
                                <h5 class="mt-3 text-gray-600">No Leave Requests</h5>
                                <p class="text-sm text-gray-400">All caught up! There are no pending leave applications.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if(isset($leaves) && $leaves->hasPages())
                <div class="card-footer bg-white border-top-0 px-4 py-3">
                    {{ $leaves->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
