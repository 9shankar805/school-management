@extends('layouts.app')
@section('title', 'Transport Reports & Exports')

@section('content')
<style>
  .header-icon-box-transport {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%) !important;
    color: #ffffff !important;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
  }
  .header-icon-box-transport i {
    color: #ffffff !important;
    font-size: 22px !important;
    line-height: 1 !important;
    display: inline-block !important;
    opacity: 1 !important;
    visibility: visible !important;
  }
</style>

<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-3 mb-1">
                <div class="header-icon-box-transport">
                    <i class="bi bi-file-earmark-bar-graph-fill"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Transport Reports & Export Center</h2>
                    <p class="text-slate-500 text-sm mb-0">Generate custom PDF & Excel export reports for fleet, drivers, student allocations, and fuel logs</p>
                </div>
            </div>
        </div>

        <!-- Navigation Bar -->
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('transport.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-speedometer2 me-1"></i> Overview
            </a>
            <a href="{{ route('transport.analytics') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-graph-up-arrow me-1"></i> Analytics
            </a>
            <a href="{{ route('transport.reports.form') }}" class="btn btn-amber bg-amber-600 text-white font-bold px-3.5 py-2 rounded-xl text-xs shadow-sm">
                <i class="bi bi-file-earmark-bar-graph me-1"></i> Reports
            </a>
        </div>
    </div>

    @include('session-messages')

    <div class="row g-4">
        <!-- Custom Report Form Card -->
        <div class="col-12 col-lg-7">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
                    <div>
                        <h5 class="font-bold text-slate-800 mb-0">Custom Transport Report Builder</h5>
                        <p class="text-xs text-slate-400 mb-0">Filter parameters and select desired export format</p>
                    </div>
                    <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 font-bold d-flex align-items-center justify-content-center">
                        <i class="bi bi-sliders"></i>
                    </span>
                </div>

                <form action="{{ route('transport.reports.export') }}" method="GET">
                    <div class="mb-4">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Report Category <span class="text-rose-500">*</span></label>
                        <select name="report_type" class="form-select rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800" required>
                            <option value="fleet">Fleet Summary (All Vehicles)</option>
                            <option value="drivers">Drivers Directory & License Status</option>
                            <option value="routes">Routes & Bus Stops Roster</option>
                            <option value="students">Student Transport Allocations</option>
                            <option value="fuel">Fuel Logs & Expenditure History</option>
                            <option value="maintenance">Vehicle Maintenance & Service Logs</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Filter Route (For Student Reports)</label>
                        <select name="route_id" class="form-select rounded-xl border-slate-200 py-2.5 text-sm text-slate-800">
                            <option value="">-- All Transport Routes --</option>
                            @foreach($routes as $r)
                                <option value="{{ $r->id }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Date From</label>
                            <input type="date" name="date_from" class="form-control rounded-xl border-slate-200 py-2.5 text-sm text-slate-800">
                        </div>
                        <div class="col-6">
                            <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Date To</label>
                            <input type="date" name="date_to" class="form-control rounded-xl border-slate-200 py-2.5 text-sm text-slate-800">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Export Format <span class="text-rose-500">*</span></label>
                        <div class="d-flex align-items-center gap-4 bg-slate-50 p-3 rounded-xl border border-slate-200">
                            <label class="d-inline-flex align-items-center gap-2 cursor-pointer font-bold text-xs text-rose-700 mb-0">
                                <input class="form-check-input" type="radio" name="format" id="fmtPdf" value="pdf" checked>
                                <span><i class="bi bi-file-earmark-pdf-fill fs-6 text-rose-600"></i> PDF Document (.pdf)</span>
                            </label>
                            <label class="d-inline-flex align-items-center gap-2 cursor-pointer font-bold text-xs text-emerald-700 mb-0">
                                <input class="form-check-input" type="radio" name="format" id="fmtXls" value="excel">
                                <span><i class="bi bi-file-earmark-excel-fill fs-6 text-emerald-600"></i> Excel Spreadsheet (.xlsx)</span>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-amber bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white font-bold w-100 py-3 rounded-xl shadow-md shadow-amber-500/20 transition">
                        <i class="bi bi-download me-1.5"></i> Generate & Download Report
                    </button>
                </form>
            </div>
        </div>

        <!-- Quick Preset Reports Sidebar Card -->
        <div class="col-12 col-lg-5">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
                    <div>
                        <h5 class="font-bold text-slate-800 mb-0">Quick Preset Export Downloads</h5>
                        <p class="text-xs text-slate-400 mb-0">Instant 1-click transport reports</p>
                    </div>
                    <span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs d-flex align-items-center justify-content-center">
                        <i class="bi bi-lightning-charge-fill"></i>
                    </span>
                </div>

                <div class="space-y-3">
                    <a href="{{ route('transport.reports.export', ['report_type'=>'fleet','format'=>'pdf']) }}" class="d-flex align-items-center justify-content-between p-3.5 rounded-xl border border-slate-200 hover:border-amber-400 hover:bg-amber-50/50 transition text-decoration-none">
                        <div class="d-flex align-items-center gap-3">
                            <span class="w-9 h-9 rounded-lg bg-rose-100 text-rose-700 d-flex align-items-center justify-content-center text-sm font-bold">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </span>
                            <div>
                                <h6 class="font-bold text-slate-800 text-xs mb-0">Fleet Vehicles Master List</h6>
                                <p class="text-[11px] text-slate-400 mb-0">All vehicles, registration, & compliance</p>
                            </div>
                        </div>
                        <i class="bi bi-download text-slate-400"></i>
                    </a>

                    <a href="{{ route('transport.reports.export', ['report_type'=>'drivers','format'=>'pdf']) }}" class="d-flex align-items-center justify-content-between p-3.5 rounded-xl border border-slate-200 hover:border-amber-400 hover:bg-amber-50/50 transition text-decoration-none">
                        <div class="d-flex align-items-center gap-3">
                            <span class="w-9 h-9 rounded-lg bg-rose-100 text-rose-700 d-flex align-items-center justify-content-center text-sm font-bold">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </span>
                            <div>
                                <h6 class="font-bold text-slate-800 text-xs mb-0">Drivers License Roster</h6>
                                <p class="text-[11px] text-slate-400 mb-0">Contact details & license expiry alerts</p>
                            </div>
                        </div>
                        <i class="bi bi-download text-slate-400"></i>
                    </a>

                    <a href="{{ route('transport.reports.export', ['report_type'=>'students','format'=>'excel']) }}" class="d-flex align-items-center justify-content-between p-3.5 rounded-xl border border-slate-200 hover:border-emerald-400 hover:bg-emerald-50/50 transition text-decoration-none">
                        <div class="d-flex align-items-center gap-3">
                            <span class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-700 d-flex align-items-center justify-content-center text-sm font-bold">
                                <i class="bi bi-file-earmark-excel"></i>
                            </span>
                            <div>
                                <h6 class="font-bold text-slate-800 text-xs mb-0">Student Passengers (Excel)</h6>
                                <p class="text-[11px] text-slate-400 mb-0">Route allocations & monthly bus fares</p>
                            </div>
                        </div>
                        <i class="bi bi-download text-slate-400"></i>
                    </a>

                    <a href="{{ route('transport.reports.export', ['report_type'=>'fuel','format'=>'excel']) }}" class="d-flex align-items-center justify-content-between p-3.5 rounded-xl border border-slate-200 hover:border-emerald-400 hover:bg-emerald-50/50 transition text-decoration-none">
                        <div class="d-flex align-items-center gap-3">
                            <span class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-700 d-flex align-items-center justify-content-center text-sm font-bold">
                                <i class="bi bi-file-earmark-excel"></i>
                            </span>
                            <div>
                                <h6 class="font-bold text-slate-800 text-xs mb-0">Fuel Logs Expenditure (Excel)</h6>
                                <p class="text-[11px] text-slate-400 mb-0">Monthly litres & fuel cost history</p>
                            </div>
                        </div>
                        <i class="bi bi-download text-slate-400"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
