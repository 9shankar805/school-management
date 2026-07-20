@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <h1 class="display-6 mb-1"><i class="bi bi-file-earmark-spreadsheet"></i> Financial Reports</h1>
                    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Reports</li>
                    </ol></nav>

                    <div class="row g-4">

                        {{-- Fee Collection --}}
                        <div class="col-md-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="fw-semibold mb-1">
                                        <i class="bi bi-cash-coin text-success me-2"></i>Fee Collection Report
                                    </h5>
                                    <p class="text-muted small mb-3">All payments received within a date range, grouped by method.</p>
                                    <form method="GET" action="{{ route('finance.reports.fee-collection') }}">
                                        <div class="row g-2 mb-3">
                                            <div class="col">
                                                <input type="date" name="from" class="form-control form-control-sm"
                                                       value="{{ now()->startOfMonth()->toDateString() }}">
                                            </div>
                                            <div class="col">
                                                <input type="date" name="to" class="form-control form-control-sm"
                                                       value="{{ now()->toDateString() }}">
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button name="format" value="html" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                            <button name="format" value="pdf" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-filetype-pdf"></i> PDF
                                            </button>
                                            <button name="format" value="excel" class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-file-earmark-excel"></i> Excel
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Outstanding Fees --}}
                        <div class="col-md-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="fw-semibold mb-1">
                                        <i class="bi bi-exclamation-circle text-warning me-2"></i>Outstanding Fees
                                    </h5>
                                    <p class="text-muted small mb-3">All unpaid or partially paid invoices with balance due.</p>
                                    <form method="GET" action="{{ route('finance.reports.outstanding') }}">
                                        <div class="row g-2 mb-3">
                                            <div class="col">
                                                <select name="session_id" class="form-select form-select-sm">
                                                    <option value="">All Sessions</option>
                                                    @foreach($sessions as $s)
                                                    <option value="{{ $s->id }}">{{ $s->session_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button name="format" value="html" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                            <button name="format" value="pdf" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-filetype-pdf"></i> PDF
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Expense Report --}}
                        <div class="col-md-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="fw-semibold mb-1">
                                        <i class="bi bi-arrow-up-circle text-danger me-2"></i>Expense Report
                                    </h5>
                                    <p class="text-muted small mb-3">Approved expenses by category for a date range.</p>
                                    <form method="GET" action="{{ route('finance.reports.expenses') }}">
                                        <div class="row g-2 mb-3">
                                            <div class="col">
                                                <input type="date" name="from" class="form-control form-control-sm"
                                                       value="{{ now()->startOfMonth()->toDateString() }}">
                                            </div>
                                            <div class="col">
                                                <input type="date" name="to" class="form-control form-control-sm"
                                                       value="{{ now()->toDateString() }}">
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button name="format" value="html" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                            <button name="format" value="pdf" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-filetype-pdf"></i> PDF
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Income Report --}}
                        <div class="col-md-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="fw-semibold mb-1">
                                        <i class="bi bi-arrow-down-circle text-success me-2"></i>Income Report
                                    </h5>
                                    <p class="text-muted small mb-3">Fee payments plus other income entries for a date range.</p>
                                    <form method="GET" action="{{ route('finance.reports.income') }}">
                                        <div class="row g-2 mb-3">
                                            <div class="col">
                                                <input type="date" name="from" class="form-control form-control-sm"
                                                       value="{{ now()->startOfMonth()->toDateString() }}">
                                            </div>
                                            <div class="col">
                                                <input type="date" name="to" class="form-control form-control-sm"
                                                       value="{{ now()->toDateString() }}">
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button name="format" value="html" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                            <button name="format" value="pdf" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-filetype-pdf"></i> PDF
                                            </button>
                                        </div>
                                    </form>
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
