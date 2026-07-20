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
                            <h1 class="display-6 mb-0"><i class="bi bi-arrow-up-circle"></i> Expenses</h1>
                            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item active">Expenses</li>
                            </ol></nav>
                        </div>
                        @can('create invoices')
                        <a href="{{ route('finance.expenses.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Record Expense
                        </a>
                        @endcan
                    </div>

                    @include('session-messages')

                    <div class="alert alert-warning d-flex align-items-center gap-2 py-2">
                        <i class="bi bi-bar-chart-fill"></i>
                        This month's approved expenses: <strong class="ms-1">${{ number_format($totalMonth, 2) }}</strong>
                    </div>

                    {{-- Filters --}}
                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-sm-3">
                            <select name="category" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                @foreach($categories as $key => $label)
                                <option value="{{ $key }}" @selected(request('category')==$key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Statuses</option>
                                <option value="approved" @selected(request('status')=='approved')>Approved</option>
                                <option value="pending"  @selected(request('status')=='pending')>Pending</option>
                                <option value="rejected" @selected(request('status')=='rejected')>Rejected</option>
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-sm-2">
                            <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-sm-auto">
                            <button class="btn btn-sm btn-secondary">Filter</button>
                            <a href="{{ route('finance.expenses.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                        </div>
                    </form>

                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th><th>Title</th><th>Category</th>
                                        <th>Vendor</th><th class="text-end">Amount</th>
                                        <th>Method</th><th>Status</th><th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($expenses as $e)
                                    <tr>
                                        <td>{{ $e->expense_date->format('d M Y') }}</td>
                                        <td class="fw-semibold">{{ $e->title }}</td>
                                        <td>{{ $e->category_label }}</td>
                                        <td>{{ $e->vendor ?? '—' }}</td>
                                        <td class="text-end text-danger fw-semibold">${{ number_format($e->amount, 2) }}</td>
                                        <td>{{ ucfirst(str_replace('_',' ',$e->payment_method)) }}</td>
                                        <td><span class="badge {{ $e->status_badge }}">{{ ucfirst($e->status) }}</span></td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('finance.expenses.edit', $e->id) }}"
                                                   class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @can('create invoices')
                                                <form method="POST" action="{{ route('finance.expenses.destroy', $e->id) }}"
                                                      class="d-inline" onsubmit="return confirm('Delete this expense?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="8" class="text-center text-muted py-4">No expenses recorded.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="mt-3">{{ $expenses->links() }}</div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
