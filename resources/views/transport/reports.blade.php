@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-file-earmark-spreadsheet"></i> Transport Reports</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('transport.index') }}">Transport</a></li>
                            <li class="breadcrumb-item active">Reports</li>
                        </ol>
                    </nav>

                    <div class="card shadow-sm" style="max-width:600px">
                        <div class="card-header fw-semibold">Generate Report</div>
                        <div class="card-body">
                            <form action="{{ route('transport.reports.export') }}" method="GET">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Report Type <span class="text-danger">*</span></label>
                                    <select name="report_type" class="form-select" required>
                                        <option value="fleet">Fleet (All Vehicles)</option>
                                        <option value="drivers">Drivers</option>
                                        <option value="routes">Routes</option>
                                        <option value="students">Student Allocations</option>
                                        <option value="fuel">Fuel Logs</option>
                                        <option value="maintenance">Maintenance Logs</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Route (for student report)</label>
                                    <select name="route_id" class="form-select">
                                        <option value="">All Routes</option>
                                        @foreach($routes as $r)
                                            <option value="{{ $r->id }}">{{ $r->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Date From</label>
                                        <input type="date" name="date_from" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Date To</label>
                                        <input type="date" name="date_to" class="form-control">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Format</label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="format" id="fmtPdf" value="pdf" checked>
                                            <label class="form-check-label" for="fmtPdf"><i class="bi bi-file-earmark-pdf text-danger"></i> PDF</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="format" id="fmtXls" value="excel">
                                            <label class="form-check-label" for="fmtXls"><i class="bi bi-file-earmark-spreadsheet text-success"></i> Excel</label>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="bi bi-download"></i> Generate & Download</button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h6 class="text-muted mb-3">Quick Reports</h6>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('transport.reports.export', ['report_type'=>'fleet','format'=>'pdf']) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-file-earmark-pdf"></i> Fleet (PDF)</a>
                            <a href="{{ route('transport.reports.export', ['report_type'=>'drivers','format'=>'pdf']) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-file-earmark-pdf"></i> Drivers (PDF)</a>
                            <a href="{{ route('transport.reports.export', ['report_type'=>'students','format'=>'excel']) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-spreadsheet"></i> Students (Excel)</a>
                            <a href="{{ route('transport.reports.export', ['report_type'=>'fuel','format'=>'excel']) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-file-earmark-spreadsheet"></i> Fuel Logs (Excel)</a>
                        </div>
                    </div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
