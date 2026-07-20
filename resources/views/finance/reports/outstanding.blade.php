@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h1 class="display-6 mb-0"><i class="bi bi-exclamation-circle text-warning"></i> Outstanding Fees</h1>
                        <div class="d-flex gap-2">
                            <a href="{{ route('finance.reports.outstanding', array_merge(request()->query(), ['format'=>'pdf'])) }}"
                               class="btn btn-sm btn-outline-danger" target="_blank">
                                <i class="bi bi-filetype-pdf"></i> PDF
                            </a>
                            <a href="{{ route('finance.reports.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Reports
                            </a>
                        </div>
                    </div>

                    <div class="alert alert-warning d-flex gap-2 align-items-center py-2">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        Total Outstanding: <strong class="ms-1">${{ number_format($totalOutstanding, 2) }}</strong>
                        &nbsp;across <strong>{{ $invoices->count() }}</strong> invoice(s)
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Invoice #</th><th>Student</th><th>Title</th>
                                            <th>Due Date</th><th class="text-end">Amount</th>
                                            <th class="text-end">Paid</th><th class="text-end">Balance</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($invoices as $inv)
                                        <tr class="{{ $inv->is_overdue ? 'table-warning' : '' }}">
                                            <td><small class="font-monospace">{{ $inv->invoice_number }}</small></td>
                                            <td>{{ optional($inv->student)->full_name }}</td>
                                            <td>{{ $inv->title }}</td>
                                            <td>{{ $inv->due_date?->format('d M Y') ?? '—' }}</td>
                                            <td class="text-end">${{ number_format($inv->net_amount ?: $inv->amount, 2) }}</td>
                                            <td class="text-end text-success">${{ number_format($inv->total_paid, 2) }}</td>
                                            <td class="text-end text-danger fw-bold">${{ number_format($inv->balance_due, 2) }}</td>
                                            <td>
                                                <span class="badge {{ $inv->status_badge }}">{{ ucfirst($inv->status) }}</span>
                                                @if($inv->is_overdue)<span class="badge bg-danger ms-1">Overdue</span>@endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="8" class="text-center text-muted py-4">No outstanding fees.</td></tr>
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
