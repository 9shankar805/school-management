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
                            <h1 class="display-6 mb-0"><i class="bi bi-journal-bookmark"></i> Accounting Ledger</h1>
                            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item active">Ledger</li>
                            </ol></nav>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('finance.ledger.balance-sheet') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-bar-chart-line"></i> Balance Sheet
                            </a>
                            <a href="{{ route('finance.ledger.profit-loss') }}" class="btn btn-outline-success btn-sm">
                                <i class="bi bi-graph-up-arrow"></i> P&amp;L
                            </a>
                        </div>
                    </div>

                    @include('session-messages')

                    {{-- Summary strip --}}
                    <div class="row g-3 mb-4">
                        <div class="col-sm-4">
                            <div class="card border-0 bg-emerald-50 rounded-3 p-3">
                                <p class="text-muted small mb-1">Credits (period)</p>
                                <h4 class="text-success mb-0">${{ number_format($totalCredit, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="card border-0 bg-danger bg-opacity-10 rounded-3 p-3">
                                <p class="text-muted small mb-1">Debits (period)</p>
                                <h4 class="text-danger mb-0">${{ number_format($totalDebit, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="card border-0 bg-primary bg-opacity-10 rounded-3 p-3">
                                <p class="text-muted small mb-1">Running Balance</p>
                                <h4 class="text-primary mb-0">${{ number_format($netBalance, 2) }}</h4>
                            </div>
                        </div>
                    </div>

                    {{-- Filters --}}
                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-sm-2">
                            <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-sm-2">
                            <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-sm-2">
                            <select name="type" class="form-select form-select-sm">
                                <option value="">All Types</option>
                                <option value="credit" @selected($type=='credit')>Credit (Income)</option>
                                <option value="debit"  @selected($type=='debit')>Debit (Expense)</option>
                            </select>
                        </div>
                        <div class="col-sm-auto">
                            <button class="btn btn-sm btn-secondary">Filter</button>
                            <a href="{{ route('finance.ledger.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                        </div>
                    </form>

                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Description</th>
                                            <th>Category</th>
                                            <th>Type</th>
                                            <th class="text-end">Amount</th>
                                            <th class="text-end">Balance</th>
                                            <th>Ref</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($entries as $entry)
                                        <tr>
                                            <td class="text-nowrap">{{ $entry->transaction_date->format('d M Y') }}</td>
                                            <td>{{ $entry->description }}</td>
                                            <td><small class="text-muted">{{ ucfirst($entry->category ?? '—') }}</small></td>
                                            <td>
                                                <span class="badge {{ $entry->type_badge }}">
                                                    {{ ucfirst($entry->type) }}
                                                </span>
                                            </td>
                                            <td class="text-end {{ $entry->type === 'credit' ? 'text-success' : 'text-danger' }} fw-semibold">
                                                {{ $entry->type === 'debit' ? '−' : '+' }}${{ number_format($entry->amount, 2) }}
                                            </td>
                                            <td class="text-end font-monospace">${{ number_format($entry->balance, 2) }}</td>
                                            <td><small class="text-muted">{{ $entry->reference_type }} #{{ $entry->reference_id }}</small></td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="7" class="text-center text-muted py-4">No ledger entries for this period.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">{{ $entries->links() }}</div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
