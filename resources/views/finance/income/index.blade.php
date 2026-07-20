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
                            <h1 class="display-6 mb-0"><i class="bi bi-arrow-down-circle"></i> Other Income</h1>
                            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item active">Income</li>
                            </ol></nav>
                        </div>
                        @can('create invoices')
                        <a href="{{ route('finance.income.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Record Income
                        </a>
                        @endcan
                    </div>

                    @include('session-messages')

                    <div class="alert alert-success d-flex align-items-center gap-2 py-2">
                        <i class="bi bi-bar-chart-fill"></i>
                        This month's other income: <strong class="ms-1">${{ number_format($totalMonth, 2) }}</strong>
                    </div>

                    {{-- Filters --}}
                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-sm-3">
                            <select name="category" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                @foreach($categories as $k => $v)
                                <option value="{{ $k }}" @selected(request('category')==$k)>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-sm-2">
                            <input type="date" name="to"   value="{{ request('to') }}"   class="form-control form-control-sm">
                        </div>
                        <div class="col-sm-auto">
                            <button class="btn btn-sm btn-secondary">Filter</button>
                            <a href="{{ route('finance.income.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                        </div>
                    </form>

                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th><th>Title</th><th>Category</th>
                                        <th>Source</th><th class="text-end">Amount</th>
                                        <th>Method</th><th>Invoice</th><th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($entries as $e)
                                    <tr>
                                        <td>{{ $e->income_date->format('d M Y') }}</td>
                                        <td class="fw-semibold">{{ $e->title }}</td>
                                        <td>{{ $e->category_label }}</td>
                                        <td>{{ $e->source ?? '—' }}</td>
                                        <td class="text-end text-success fw-semibold">${{ number_format($e->amount, 2) }}</td>
                                        <td>{{ ucfirst(str_replace('_',' ',$e->payment_method ?? '')) }}</td>
                                        <td>
                                            @if($e->invoice)
                                            <small class="font-monospace text-muted">{{ $e->invoice->invoice_number }}</small>
                                            @else —
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('finance.income.edit', $e->id) }}"
                                                   class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @can('create invoices')
                                                <form method="POST" action="{{ route('finance.income.destroy', $e->id) }}"
                                                      class="d-inline" onsubmit="return confirm('Delete this entry?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="8" class="text-center text-muted py-4">No income entries recorded.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
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
