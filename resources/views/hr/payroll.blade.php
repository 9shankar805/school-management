@extends('layouts.app')
@section('title', 'Payroll Processing')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-bold text-gray-800 mb-1"><i class="bi bi-cash-stack text-green-500"></i> Payroll Processing</h2>
            <p class="text-muted mb-0">Generate and manage staff salary slips.</p>
        </div>
        <a href="{{ route('teacher.payroll.index') }}" class="btn btn-primary shadow-sm"><i class="bi bi-magic"></i> Generate Payroll</a>
    </div>

    <!-- Data Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3 text-muted text-xs text-uppercase font-semibold">Employee</th>
                            <th class="py-3 text-muted text-xs text-uppercase font-semibold">Salary Month</th>
                            <th class="py-3 text-muted text-xs text-uppercase font-semibold text-end">Basic Salary</th>
                            <th class="py-3 text-muted text-xs text-uppercase font-semibold text-end">Net Payable</th>
                            <th class="py-3 text-muted text-xs text-uppercase font-semibold text-center">Status</th>
                            <th class="px-4 py-3 text-muted text-xs text-uppercase font-semibold text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payrolls as $payroll)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-bold text-gray-800">{{ $payroll->teacher->full_name ?? $payroll->teacher->first_name ?? $payroll->teacher->name ?? 'Unknown Employee' }}</div>
                                <div class="text-xs text-muted">Role: {{ ucfirst($payroll->teacher->roles->first()->name ?? 'Staff') }}</div>
                            </td>
                            <td class="py-3 font-semibold text-sm">
                                {{ ($payroll->year && $payroll->month) ? \Carbon\Carbon::createFromDate($payroll->year, $payroll->month, 1)->format('F Y') : (\Carbon\Carbon::parse($payroll->salary_month ?? now())->format('F Y')) }}
                            </td>
                            <td class="py-3 text-end text-muted">
                                Rs. {{ number_format($payroll->basic_salary, 2) }}
                            </td>
                            <td class="py-3 text-end font-bold text-gray-800">
                                Rs. {{ number_format($payroll->net_salary, 2) }}
                            </td>
                            <td class="py-3 text-center">
                                <span class="badge {{ $payroll->status == 'paid' ? 'bg-green-100 text-green-700' : 'bg-warning-100 text-warning-700' }} rounded-pill px-3">
                                    {{ ucfirst($payroll->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-end">
                                <a href="{{ route('teacher.payroll.index') }}?month={{ $payroll->month }}&year={{ $payroll->year }}" class="btn btn-sm btn-light text-primary" title="View In Payroll"><i class="bi bi-eye"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted"><i class="bi bi-cash display-4 text-gray-300"></i></div>
                                <h5 class="mt-3 text-gray-600">No Payroll Records</h5>
                                <p class="text-sm text-gray-400">Run the payroll generator to calculate salaries for this month.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if(isset($payrolls) && $payrolls->hasPages())
                <div class="card-footer bg-white border-top-0 px-4 py-3">
                    {{ $payrolls->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
