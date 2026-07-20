@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4" style="max-width:900px">

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h1 class="display-6 mb-0"><i class="bi bi-bar-chart-line"></i> Balance Sheet</h1>
                            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('finance.ledger.index') }}">Ledger</a></li>
                                <li class="breadcrumb-item active">Balance Sheet</li>
                            </ol></nav>
                        </div>
                        {{-- Period selector --}}
                        <form method="GET" class="d-flex gap-2 align-items-center">
                            <select name="month" class="form-select form-select-sm" style="width:130px">
                                @foreach(range(1,12) as $m)
                                <option value="{{ $m }}" @selected($m == $month)>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                                @endforeach
                            </select>
                            <select name="year" class="form-select form-select-sm" style="width:90px">
                                @foreach(range(now()->year-3, now()->year+1) as $y)
                                <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-sm btn-secondary">Go</button>
                        </form>
                    </div>

                    <p class="text-muted mb-4">
                        Period: <strong>{{ $start->format('d M Y') }}</strong> — <strong>{{ $end->format('d M Y') }}</strong>
                    </p>

                    {{-- KPI row --}}
                    <div class="row g-3 mb-4">
                        <div class="col-sm-3">
                            <div class="card border-0 bg-emerald-50 rounded-3 p-3 text-center">
                                <p class="text-muted small mb-1">Total Income</p>
                                <h4 class="text-success mb-0">${{ number_format($totalIncome, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="card border-0 bg-danger bg-opacity-10 rounded-3 p-3 text-center">
                                <p class="text-muted small mb-1">Total Expenses</p>
                                <h4 class="text-danger mb-0">${{ number_format($totalExpense, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="card border-0 {{ $netProfit >= 0 ? 'bg-success' : 'bg-danger' }} bg-opacity-10 rounded-3 p-3 text-center">
                                <p class="text-muted small mb-1">Net {{ $netProfit >= 0 ? 'Surplus' : 'Deficit' }}</p>
                                <h4 class="{{ $netProfit >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                                    ${{ number_format(abs($netProfit), 2) }}
                                </h4>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="card border-0 bg-warning bg-opacity-10 rounded-3 p-3 text-center">
                                <p class="text-muted small mb-1">Outstanding Fees</p>
                                <h4 class="text-warning mb-0">${{ number_format($outstanding, 2) }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        {{-- Income breakdown --}}
                        <div class="col-md-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-emerald-50 fw-semibold text-success">
                                    <i class="bi bi-arrow-down-circle me-1"></i> Income Breakdown
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm mb-0">
                                        <tbody>
                                            <tr>
                                                <td>Student Fee Payments</td>
                                                <td class="text-end text-success">${{ number_format($feePayments, 2) }}</td>
                                            </tr>
                                            @foreach($incomeByCategory as $cat => $amt)
                                            <tr>
                                                <td class="text-capitalize">{{ str_replace('_',' ',$cat) }}</td>
                                                <td class="text-end text-success">${{ number_format($amt, 2) }}</td>
                                            </tr>
                                            @endforeach
                                            <tr class="table-light fw-bold">
                                                <td>Total</td>
                                                <td class="text-end text-success">${{ number_format($totalIncome, 2) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Expense breakdown --}}
                        <div class="col-md-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-danger bg-opacity-10 fw-semibold text-danger">
                                    <i class="bi bi-arrow-up-circle me-1"></i> Expense Breakdown
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm mb-0">
                                        <tbody>
                                            @forelse($expenseByCategory as $cat => $amt)
                                            <tr>
                                                <td class="text-capitalize">{{ str_replace('_',' ',$cat) }}</td>
                                                <td class="text-end text-danger">${{ number_format($amt, 2) }}</td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="2" class="text-muted text-center py-3">No expenses this period.</td></tr>
                                            @endforelse
                                            <tr class="table-light fw-bold">
                                                <td>Total</td>
                                                <td class="text-end text-danger">${{ number_format($totalExpense, 2) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
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
