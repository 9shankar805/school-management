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
                            <h1 class="display-6 mb-0"><i class="bi bi-arrow-up-circle text-danger"></i> Expense Report</h1>
                            <p class="text-muted mb-0">{{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('finance.reports.expenses', ['from'=>$from,'to'=>$to,'format'=>'pdf']) }}"
                               class="btn btn-sm btn-outline-danger" target="_blank">
                                <i class="bi bi-filetype-pdf"></i> PDF
                            </a>
                            <a href="{{ route('finance.reports.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Reports
                            </a>
                        </div>
                    </div>

                    {{-- By category --}}
                    <div class="row g-3 mb-4">
                        @foreach($byCategory as $cat => $amt)
                        <div class="col-sm-auto">
                            <div class="card border-0 bg-danger bg-opacity-10 rounded-3 p-3 text-center">
                                <p class="text-muted small mb-1 text-capitalize">{{ str_replace('_',' ',$cat) }}</p>
                                <h5 class="text-danger mb-0">${{ number_format($amt, 2) }}</h5>
                            </div>
                        </div>
                        @endforeach
                        <div class="col-sm-auto">
                            <div class="card border-0 bg-light rounded-3 p-3 text-center">
                                <p class="text-muted small mb-1">Total</p>
                                <h5 class="text-danger mb-0 fw-bold">${{ number_format($total, 2) }}</h5>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0 table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th><th>Title</th><th>Category</th>
                                        <th>Vendor</th><th class="text-end">Amount</th><th>Method</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($expenses as $e)
                                    <tr>
                                        <td>{{ $e->expense_date->format('d M Y') }}</td>
                                        <td>{{ $e->title }}</td>
                                        <td>{{ $e->category_label }}</td>
                                        <td>{{ $e->vendor ?? '—' }}</td>
                                        <td class="text-end text-danger fw-semibold">${{ number_format($e->amount, 2) }}</td>
                                        <td>{{ ucfirst(str_replace('_',' ',$e->payment_method)) }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">No expenses in this period.</td></tr>
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
