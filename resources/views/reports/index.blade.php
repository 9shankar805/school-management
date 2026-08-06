@extends('layouts.app')
@section('title', 'Report Center')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-bold text-gray-800 mb-1"><i class="bi bi-file-earmark-spreadsheet text-emerald-600"></i> Report Center</h2>
            <p class="text-muted mb-0">Generate, schedule, and build custom reports across all modules.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('reports.schedule') }}" class="btn btn-outline-primary shadow-sm"><i class="bi bi-clock-history"></i> Scheduled Reports</a>
            <a href="{{ route('reports.builder') }}" class="btn btn-primary shadow-sm"><i class="bi bi-magic"></i> Custom Report Builder</a>
        </div>
    </div>

    <!-- Academics & Students -->
    <h5 class="font-bold text-gray-700 mb-3 mt-4">Academics & Students</h5>
    <div class="row g-4">
        <!-- Attendance Report -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-indigo-100 text-indigo-600 rounded p-2 me-3">
                            <i class="bi bi-calendar-check fs-4"></i>
                        </div>
                        <h6 class="font-bold mb-0">Attendance Reports</h6>
                    </div>
                    <p class="text-muted text-sm mb-4">Generate daily, weekly, or monthly student attendance summaries.</p>
                    <a href="{{ route('attendance.report.form') }}" class="btn btn-sm btn-light w-100"><i class="bi bi-box-arrow-up-right"></i> Open Generator</a>
                </div>
            </div>
        </div>

        <!-- Result/Exam Report -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-indigo-100 text-indigo-600 rounded p-2 me-3">
                            <i class="bi bi-file-earmark-text fs-4"></i>
                        </div>
                        <h6 class="font-bold mb-0">Exam & Results</h6>
                    </div>
                    <p class="text-muted text-sm mb-4">Export class-wise result sheets, transcripts, and report cards.</p>
                    <a href="{{ route('results.index') }}" class="btn btn-sm btn-light w-100"><i class="bi bi-box-arrow-up-right"></i> Open Generator</a>
                </div>
            </div>
        </div>

        <!-- Admissions Report -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-indigo-100 text-indigo-600 rounded p-2 me-3">
                            <i class="bi bi-person-lines-fill fs-4"></i>
                        </div>
                        <h6 class="font-bold mb-0">Admissions Report</h6>
                    </div>
                    <p class="text-muted text-sm mb-4">Track new enrollments and demographic data.</p>
                    <a href="{{ route('reports.builder') }}?preset=admissions" class="btn btn-sm btn-light w-100"><i class="bi bi-magic"></i> Use Custom Builder</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Finance & HR -->
    <h5 class="font-bold text-gray-700 mb-3 mt-5">Finance & HR</h5>
    <div class="row g-4">
        <!-- Revenue Report -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-emerald-100 text-emerald-600 rounded p-2 me-3">
                            <i class="bi bi-cash-coin fs-4"></i>
                        </div>
                        <h6 class="font-bold mb-0">Fee Collections</h6>
                    </div>
                    <p class="text-muted text-sm mb-4">Generate PDF/Excel reports of daily fee collections and defaults.</p>
                    <a href="{{ route('finance.reports') }}" class="btn btn-sm btn-light w-100"><i class="bi bi-box-arrow-up-right"></i> Open Generator</a>
                </div>
            </div>
        </div>

        <!-- Payroll Report -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-emerald-100 text-emerald-600 rounded p-2 me-3">
                            <i class="bi bi-wallet2 fs-4"></i>
                        </div>
                        <h6 class="font-bold mb-0">Payroll & Salary</h6>
                    </div>
                    <p class="text-muted text-sm mb-4">Export monthly teacher payroll reports and salary slips.</p>
                    <a href="{{ route('teacher.payroll.index') }}" class="btn btn-sm btn-light w-100"><i class="bi bi-box-arrow-up-right"></i> Open Generator</a>
                </div>
            </div>
        </div>

        <!-- Staff/Teacher Report -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-emerald-100 text-emerald-600 rounded p-2 me-3">
                            <i class="bi bi-person-badge fs-4"></i>
                        </div>
                        <h6 class="font-bold mb-0">Staff Directory</h6>
                    </div>
                    <p class="text-muted text-sm mb-4">Export complete lists of staff details and contact info.</p>
                    <a href="{{ route('reports.builder') }}?preset=staff" class="btn btn-sm btn-light w-100"><i class="bi bi-magic"></i> Use Custom Builder</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Operations -->
    <h5 class="font-bold text-gray-700 mb-3 mt-5">Operations</h5>
    <div class="row g-4 mb-5">
        <!-- Library Report -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-orange-100 text-orange-600 rounded p-2 me-3">
                            <i class="bi bi-book fs-4"></i>
                        </div>
                        <h6 class="font-bold mb-0">Library Reports</h6>
                    </div>
                    <p class="text-muted text-sm mb-4">Export book issues, returns, and inventory lists.</p>
                    <a href="{{ route('library.reports.form') }}" class="btn btn-sm btn-light w-100"><i class="bi bi-box-arrow-up-right"></i> Open Generator</a>
                </div>
            </div>
        </div>

        <!-- Inventory Report -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-orange-100 text-orange-600 rounded p-2 me-3">
                            <i class="bi bi-boxes fs-4"></i>
                        </div>
                        <h6 class="font-bold mb-0">Inventory Reports</h6>
                    </div>
                    <p class="text-muted text-sm mb-4">Generate stock levels, supplier records, and purchasing history.</p>
                    <a href="#" class="btn btn-sm btn-light w-100"><i class="bi bi-box-arrow-up-right"></i> Open Generator</a>
                </div>
            </div>
        </div>

        <!-- Transport Report -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-orange-100 text-orange-600 rounded p-2 me-3">
                            <i class="bi bi-bus-front fs-4"></i>
                        </div>
                        <h6 class="font-bold mb-0">Transport Reports</h6>
                    </div>
                    <p class="text-muted text-sm mb-4">View fuel logs, maintenance schedules, and route assignments.</p>
                    <a href="{{ route('transport.reports.form') }}" class="btn btn-sm btn-light w-100"><i class="bi bi-box-arrow-up-right"></i> Open Generator</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
