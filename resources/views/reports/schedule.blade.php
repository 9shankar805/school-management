@extends('layouts.app')
@section('title', 'Report Scheduling')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-bold text-gray-800 mb-1"><i class="bi bi-clock-history text-indigo-500"></i> Scheduled Reports</h2>
            <p class="text-muted mb-0">Automate your reporting workflow by scheduling recurring emails.</p>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary shadow-sm"><i class="bi bi-arrow-left"></i> Back to Report Center</a>
    </div>

    <div class="row g-4">
        <!-- Schedule Form -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h6 class="font-bold text-gray-800 mb-0">Create New Schedule</h6>
                </div>
                <div class="card-body p-4">
                    <form>
                        <div class="mb-3">
                            <label class="form-label font-bold text-sm">Report Type</label>
                            <select class="form-select">
                                <option>Daily Attendance Summary</option>
                                <option>Weekly Fee Collection</option>
                                <option>Monthly Payroll Register</option>
                                <option>Library Overdue Books (Weekly)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label font-bold text-sm">Frequency</label>
                            <select class="form-select">
                                <option>Daily (8:00 AM)</option>
                                <option>Weekly (Monday 8:00 AM)</option>
                                <option>Weekly (Friday 5:00 PM)</option>
                                <option>Monthly (1st of Month)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label font-bold text-sm">Recipients (Email Addresses)</label>
                            <input type="text" class="form-control" placeholder="admin@school.com, principal@school.com">
                            <div class="form-text">Separate multiple emails with commas.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label font-bold text-sm">Format</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="format" id="f_pdf" checked>
                                    <label class="form-check-label" for="f_pdf">PDF Document</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="format" id="f_excel">
                                    <label class="form-check-label" for="f_excel">Excel Spreadsheet</label>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-primary w-100" onclick="alert('Schedule saved successfully!')"><i class="bi bi-calendar-plus"></i> Save Schedule</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Active Schedules -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h6 class="font-bold text-gray-800 mb-0">Active Schedules</h6>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted text-uppercase text-xs font-semibold">
                                <tr>
                                    <th>Report</th>
                                    <th>Frequency</th>
                                    <th>Recipients</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="font-semibold text-gray-800 text-sm">Daily Attendance Summary</td>
                                    <td class="text-muted text-sm">Daily (8:00 AM)</td>
                                    <td class="text-muted text-sm">principal@...</td>
                                    <td><span class="badge bg-success rounded-pill">Active</span></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-semibold text-gray-800 text-sm">Weekly Fee Collection</td>
                                    <td class="text-muted text-sm">Weekly (Friday 5:00 PM)</td>
                                    <td class="text-muted text-sm">finance@...</td>
                                    <td><span class="badge bg-success rounded-pill">Active</span></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-semibold text-gray-800 text-sm">Monthly Payroll Register</td>
                                    <td class="text-muted text-sm">Monthly (1st of Month)</td>
                                    <td class="text-muted text-sm">hr@...</td>
                                    <td><span class="badge bg-secondary rounded-pill">Paused</span></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
