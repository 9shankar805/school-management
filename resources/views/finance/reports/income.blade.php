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
                            <h1 class="display-6 mb-0"><i class="bi bi-arrow-down-circle text-success"></i> Income Report</h1>
                            <p class="text-muted mb-0">{{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('finance.reports.income', ['from'=>$from,'to'=>$to,'format'=>'pdf']) }}"
                               class="btn btn-sm btn-outline-danger" target="_blank">
                                <i class="bi bi-filetype-pdf"></i> PDF
                            </a>
                            <a href="{{ route('finance.reports.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Reports
                            </a>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-sm-auto">
                            <div class="card border-0 bg-emerald-50 rounded-3 p-3 text-center">
                                <p class="text-muted small mb-1">Total Income</p>
                                <h4 class="text-success mb-0">${{ number_format($totalIncome, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-sm-auto">
                            <div class="card border-0 bg-light rounded-3 p-3 text-center">
                                <p class="text-muted small mb-1">Fee Payments</p>
                                <h5 class="mb-0 text-success">${{ number_format($totalFees, 2) }}</h5>
                            </div>
                        </div>
                        <div class="col-sm-auto">
                            <div class="card border-0 bg-light rounded-3 p-3 text-center">
                                <p class="text-muted small mb-1">Other Income</p>
                                <h5 class="mb-0 text-primary">${{ number_format($totalOther, 2) }}</h5>
                            </div>
                        </div>
                    </div>

                    <h5 class="fw-semibold mb-2">Fee Payments</h5>
                    <div class="card shadow-sm mb-4">
                        <div class="card-body p-0">
                            <table class="table table-hover table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Receipt #</th><th>Date</th><th>Student</th>
                                        <th>Invoice #</th><th class="text-end">Amount</th><th>Method</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($feePayments as $p)
                                    <tr>
                                        <td><small class="font-monospace">{{ $p->receipt_number }}</small></td>
                                        <td>{{ $p->payment_date->format('d M Y') }}</td>
                                        <td>{{ optional($p->invoice?->student)->full_name ?? '—' }}</td>
                                        <td><small class="font-monospace text-muted">{{ $p->invoice?->invoice_number }}</small></td>
                                        <td class="text-end text-success fw-semibold">${{ number_format($p->amount_paid, 2) }}</td>
                                        <td>{{ ucfirst(str_replace('_',' ',$p->payment_method ?? '')) }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="6" class="text-center text-muted py-3">No fee payments.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <h5 class="fw-semibold mb-2">Other Income</h5>
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <table class="table table-hover table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th><th>Title</th><th>Category</th>
                                        <th>Source</th><th class="text-end">Amount</th><th>Method</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($otherIncome as $e)
                                    <tr>
                                        <td>{{ $e->income_date->format('d M Y') }}</td>
                                        <td>{{ $e->title }}</td>
                                        <td>{{ $e->category_label }}</td>
                                        <td>{{ $e->source ?? '—' }}</td>
                                        <td class="text-end text-success fw-semibold">${{ number_format($e->amount, 2) }}</td>
                                        <td>{{ ucfirst(str_replace('_',' ',$e->payment_method ?? '')) }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="6" class="text-center text-muted py-3">No other income entries.</td></tr>
                                    @endforelse
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
