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
                            <h1 class="display-6 mb-0"><i class="bi bi-graph-up-arrow"></i> Profit &amp; Loss</h1>
                            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('finance.ledger.index') }}">Ledger</a></li>
                                <li class="breadcrumb-item active">P&amp;L</li>
                            </ol></nav>
                        </div>
                        <form method="GET" class="d-flex gap-2 align-items-center">
                            <select name="year" class="form-select form-select-sm" style="width:90px">
                                @foreach($years as $y)
                                <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-sm btn-secondary">Go</button>
                        </form>
                    </div>

                    {{-- Annual totals --}}
                    @php
                        $annualIncome  = $months->sum('income');
                        $annualExpense = $months->sum('expense');
                        $annualNet     = $months->sum('net');
                    @endphp
                    <div class="row g-3 mb-4">
                        <div class="col-sm-4">
                            <div class="card border-0 bg-emerald-50 rounded-3 p-3 text-center">
                                <p class="text-muted small mb-1">Annual Income</p>
                                <h4 class="text-success mb-0">${{ number_format($annualIncome, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="card border-0 bg-danger bg-opacity-10 rounded-3 p-3 text-center">
                                <p class="text-muted small mb-1">Annual Expenses</p>
                                <h4 class="text-danger mb-0">${{ number_format($annualExpense, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="card border-0 {{ $annualNet >= 0 ? 'bg-success' : 'bg-danger' }} bg-opacity-10 rounded-3 p-3 text-center">
                                <p class="text-muted small mb-1">Net {{ $annualNet >= 0 ? 'Surplus' : 'Deficit' }}</p>
                                <h4 class="{{ $annualNet >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                                    ${{ number_format(abs($annualNet), 2) }}
                                </h4>
                            </div>
                        </div>
                    </div>

                    {{-- ApexCharts bar --}}
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white fw-semibold">Monthly Income vs Expense — {{ $year }}</div>
                        <div class="card-body">
                            <div id="plChart"></div>
                        </div>
                    </div>

                    {{-- Monthly breakdown table --}}
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Month</th>
                                        <th class="text-end">Income</th>
                                        <th class="text-end">Expenses</th>
                                        <th class="text-end">Net</th>
                                        <th style="min-width:160px">Margin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($months as $m)
                                    @php $net = $m['net']; @endphp
                                    <tr>
                                        <td class="fw-semibold">{{ $m['month'] }}</td>
                                        <td class="text-end text-success">${{ number_format($m['income'], 2) }}</td>
                                        <td class="text-end text-danger">${{ number_format($m['expense'], 2) }}</td>
                                        <td class="text-end fw-bold {{ $net >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $net >= 0 ? '+' : '' }}${{ number_format($net, 2) }}
                                        </td>
                                        <td>
                                            @if($m['income'] > 0)
                                            @php $pct = min(100, max(0, round($net / $m['income'] * 100))); @endphp
                                            <div class="progress" style="height:6px">
                                                <div class="progress-bar {{ $pct >= 0 ? 'bg-success' : 'bg-danger' }}"
                                                     style="width:{{ abs($pct) }}%"></div>
                                            </div>
                                            <small class="text-muted">{{ $pct }}%</small>
                                            @else —
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                    <tr class="table-light fw-bold">
                                        <td>Total</td>
                                        <td class="text-end text-success">${{ number_format($annualIncome, 2) }}</td>
                                        <td class="text-end text-danger">${{ number_format($annualExpense, 2) }}</td>
                                        <td class="text-end {{ $annualNet >= 0 ? 'text-success' : 'text-danger' }}">
                                            ${{ number_format($annualNet, 2) }}
                                        </td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const months   = @json($months->pluck('month'));
const income   = @json($months->pluck('income'));
const expenses = @json($months->pluck('expense'));
const net      = @json($months->pluck('net'));

new ApexCharts(document.getElementById('plChart'), {
    chart:  { type: 'bar', height: 300, toolbar: { show: false } },
    series: [
        { name: 'Income',   data: income   },
        { name: 'Expenses', data: expenses },
        { name: 'Net',      data: net, type: 'line' },
    ],
    xaxis:  { categories: months },
    colors: ['#10b981', '#ef4444', '#6366f1'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: [0, 0, 2] },
    legend: { position: 'top' },
    yaxis:  { labels: { formatter: v => '$' + Number(v).toLocaleString() } },
    tooltip:{ y: { formatter: v => '$' + Number(v).toLocaleString() } },
}).render();
</script>
@endpush
