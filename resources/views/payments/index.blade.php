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
                            <h1 class="display-6 mb-0"><i class="bi bi-receipt"></i> Invoices &amp; Payments</h1>
                            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item active">Invoices</li>
                            </ol></nav>
                        </div>
                        @can('create invoices')
                        <a href="{{ route('payments.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> New Invoice
                        </a>
                        @endcan
                    </div>

                    @include('session-messages')

                    {{-- KPI strip --}}
                    <div class="row g-3 mb-4">
                        <div class="col-sm-4">
                            <div class="card border-0 bg-emerald-50 rounded-3 p-3 d-flex flex-row align-items-center gap-3">
                                <i class="bi bi-check-circle-fill text-success fs-2"></i>
                                <div><p class="text-muted small mb-0">Paid</p><h4 class="mb-0">{{ $totalPaid }}</h4></div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="card border-0 bg-warning bg-opacity-10 rounded-3 p-3 d-flex flex-row align-items-center gap-3">
                                <i class="bi bi-hourglass-split text-warning fs-2"></i>
                                <div><p class="text-muted small mb-0">Partial</p><h4 class="mb-0">{{ $totalPartial }}</h4></div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="card border-0 bg-danger bg-opacity-10 rounded-3 p-3 d-flex flex-row align-items-center gap-3">
                                <i class="bi bi-x-circle-fill text-danger fs-2"></i>
                                <div><p class="text-muted small mb-0">Unpaid</p><h4 class="mb-0">{{ $totalUnpaid }}</h4></div>
                            </div>
                        </div>
                    </div>

                    {{-- Filters --}}
                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-sm-4">
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="form-control form-control-sm" placeholder="Search student or invoice #">
                        </div>
                        <div class="col-sm-3">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Statuses</option>
                                <option value="unpaid"  @selected(request('status')=='unpaid')>Unpaid</option>
                                <option value="partial" @selected(request('status')=='partial')>Partial</option>
                                <option value="paid"    @selected(request('status')=='paid')>Paid</option>
                            </select>
                        </div>
                        <div class="col-sm-auto">
                            <button class="btn btn-sm btn-secondary">Filter</button>
                            <a href="{{ route('payments.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                        </div>
                    </form>

                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Invoice #</th>
                                            <th>Student</th>
                                            <th>Title</th>
                                            <th class="text-end">Amount</th>
                                            <th class="text-end">Paid</th>
                                            <th class="text-end">Balance</th>
                                            <th>Status</th>
                                            <th>Due Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($invoices as $invoice)
                                        <tr class="{{ $invoice->is_overdue ? 'table-danger' : '' }}">
                                            <td><small class="text-muted font-monospace">{{ $invoice->invoice_number }}</small></td>
                                            <td>{{ optional($invoice->student)->full_name }}</td>
                                            <td>{{ $invoice->title }}</td>
                                            <td class="text-end">${{ number_format($invoice->net_amount ?: $invoice->amount, 2) }}</td>
                                            <td class="text-end text-success">${{ number_format($invoice->total_paid, 2) }}</td>
                                            <td class="text-end text-danger">${{ number_format($invoice->balance_due, 2) }}</td>
                                            <td>
                                                <span class="badge {{ $invoice->status_badge }}">
                                                    {{ ucfirst($invoice->status) }}
                                                    @if($invoice->is_overdue) <i class="bi bi-exclamation-triangle-fill ms-1"></i> @endif
                                                </span>
                                            </td>
                                            <td>{{ $invoice->due_date?->format('d M Y') ?? '—' }}</td>
                                            <td>
                                                <div class="d-flex gap-1 flex-wrap">
                                                    @if($invoice->status !== 'paid')
                                                    <a href="{{ route('payments.pay', $invoice->id) }}"
                                                       class="btn btn-sm btn-success">
                                                        <i class="bi bi-cash"></i> Pay
                                                    </a>
                                                    @endif
                                                    @can('create invoices')
                                                    <a href="{{ route('payments.edit', $invoice->id) }}"
                                                       class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form method="POST" action="{{ route('payments.destroy', $invoice->id) }}"
                                                          onsubmit="return confirm('Delete this invoice?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="9" class="text-center text-muted py-4">No invoices found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">{{ $invoices->links() }}</div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
