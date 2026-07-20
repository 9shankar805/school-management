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
                            <h1 class="display-6 mb-0"><i class="bi bi-cash-coin"></i> Fee Collection Report</h1>
                            <p class="text-muted mb-0">{{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('finance.reports.fee-collection', ['from'=>$from,'to'=>$to,'format'=>'pdf']) }}"
                               class="btn btn-sm btn-outline-danger" target="_blank">
                                <i class="bi bi-filetype-pdf"></i> PDF
                            </a>
                            <a href="{{ route('finance.reports.fee-collection', ['from'=>$from,'to'=>$to,'format'=>'excel']) }}"
                               class="btn btn-sm btn-outline-success">
                                <i class="bi bi-file-earmark-excel"></i> Excel
                            </a>
                            <a href="{{ route('finance.reports.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Reports
                            </a>
                        </div>
                    </div>

                    {{-- Summary --}}
                    <div class="row g-3 mb-4">
                        <div class="col-sm-4">
                            <div class="card border-0 bg-emerald-50 rounded-3 p-3 text-center">
                                <p class="text-muted small mb-1">Total Collected</p>
                                <h4 class="text-success mb-0">${{ number_format($total, 2) }}</h4>
                            </div>
                        </div>
                        @foreach($byMethod as $method => $amt)
                        <div class="col-sm-auto">
                            <div class="card border-0 bg-light rounded-3 p-3 text-center">
                                <p class="text-muted small mb-1">{{ ucfirst(str_replace('_',' ',$method)) }}</p>
                                <h5 class="mb-0">${{ number_format($amt, 2) }}</h5>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Receipt #</th><th>Date</th><th>Student</th>
                                            <th>Invoice #</th><th class="text-end">Amount</th>
                                            <th>Method</th><th>Reference</th><th>Received By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($payments as $p)
                                        <tr>
                                            <td><small class="font-monospace">{{ $p->receipt_number }}</small></td>
                                            <td>{{ $p->payment_date->format('d M Y') }}</td>
                                            <td>{{ optional($p->invoice?->student)->full_name ?? '—' }}</td>
                                            <td><small class="font-monospace text-muted">{{ $p->invoice?->invoice_number }}</small></td>
                                            <td class="text-end text-success fw-semibold">${{ number_format($p->amount_paid, 2) }}</td>
                                            <td>{{ ucfirst(str_replace('_',' ',$p->payment_method ?? '')) }}</td>
                                            <td><small>{{ $p->transaction_reference ?? '—' }}</small></td>
                                            <td>{{ optional($p->receivedBy)->full_name ?? '—' }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="8" class="text-center text-muted py-4">No payments in this period.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
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
