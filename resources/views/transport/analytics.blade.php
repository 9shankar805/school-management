@extends('layouts.app')
@section('title', 'Transport Analytics & Fuel Reports')

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
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Transport & Fleet Analytics</h2>
                    <p class="text-slate-500 text-sm mb-0">Comprehensive fuel consumption, route attendance rates, and vehicle maintenance analysis</p>
                </div>
            </div>
        </div>

        <!-- Navigation Bar -->
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('transport.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-speedometer2 me-1"></i> Overview
            </a>
            <a href="{{ route('transport.vehicles.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-bus-front me-1"></i> Fleet
            </a>
            <a href="{{ route('transport.routes.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-map me-1"></i> Routes
            </a>
            <a href="{{ route('transport.analytics') }}" class="btn btn-amber bg-amber-600 text-white font-bold px-3.5 py-2 rounded-xl text-xs shadow-sm">
                <i class="bi bi-graph-up-arrow me-1"></i> Analytics
            </a>
        </div>
    </div>

    @include('session-messages')

    <!-- Charts Row 1: Fleet Distribution & Monthly Fuel Litres -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-3">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-b border-slate-100">
                    <h6 class="font-bold text-slate-800 mb-0 text-xs uppercase tracking-wider">Fleet by Type</h6>
                    <span class="w-6 h-6 rounded bg-amber-50 text-amber-600 d-flex align-items-center justify-content-center text-xs"><i class="bi bi-bus-front"></i></span>
                </div>
                <div id="typeChart"></div>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-b border-slate-100">
                    <h6 class="font-bold text-slate-800 mb-0 text-xs uppercase tracking-wider">Fleet Status</h6>
                    <span class="w-6 h-6 rounded bg-emerald-50 text-emerald-600 d-flex align-items-center justify-content-center text-xs"><i class="bi bi-pie-chart"></i></span>
                </div>
                <div id="statusChart"></div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-b border-slate-100">
                    <h6 class="font-bold text-slate-800 mb-0 text-xs uppercase tracking-wider">Monthly Fuel Volume (Litres)</h6>
                    <span class="w-6 h-6 rounded bg-sky-50 text-sky-600 d-flex align-items-center justify-content-center text-xs"><i class="bi bi-fuel-pump"></i></span>
                </div>
                <div id="fuelLitresChart"></div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2: Fuel Costs & Maintenance Costs -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-b border-slate-100">
                    <h6 class="font-bold text-slate-800 mb-0 text-xs uppercase tracking-wider">Monthly Fuel Expenditure (Rs.)</h6>
                    <span class="w-6 h-6 rounded bg-amber-50 text-amber-600 d-flex align-items-center justify-content-center text-xs"><i class="bi bi-cash-stack"></i></span>
                </div>
                <div id="fuelCostChart"></div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-b border-slate-100">
                    <h6 class="font-bold text-slate-800 mb-0 text-xs uppercase tracking-wider">Maintenance Costs by Category</h6>
                    <span class="w-6 h-6 rounded bg-rose-50 text-rose-600 d-flex align-items-center justify-content-center text-xs"><i class="bi bi-tools"></i></span>
                </div>
                <div id="maintChart"></div>
            </div>
        </div>
    </div>

    <!-- Analytics Tables Row: Attendance & Top Fuel Vehicles -->
    <div class="row g-4">
        <!-- Route Attendance Rates -->
        <div class="col-12 col-md-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
                    <div>
                        <h5 class="font-bold text-slate-800 mb-0">Route Attendance Rate (30 Days)</h5>
                        <p class="text-xs text-slate-400 mb-0">Average passenger turnout per transport route</p>
                    </div>
                    <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 font-bold text-xs d-flex align-items-center justify-content-center">
                        <i class="bi bi-clipboard-data"></i>
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle text-sm mb-0">
                        <thead>
                            <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                                <th class="py-3 px-4 rounded-s-xl">Route Name</th>
                                <th class="py-3 px-4 text-center">Present</th>
                                <th class="py-3 px-4 text-center">Total Trips</th>
                                <th class="py-3 px-4 rounded-e-xl" style="width: 35%;">Turnout Rate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($attByRoute as $r)
                            @php
                                $pct = $r->total_count > 0 ? round(($r->present_count / $r->total_count) * 100) : 0;
                            @endphp
                            <tr>
                                <td class="py-3 px-4 font-bold text-slate-800 text-xs">{{ $r->name }}</td>
                                <td class="py-3 px-4 text-center font-semibold text-emerald-700 text-xs">{{ $r->present_count }}</td>
                                <td class="py-3 px-4 text-center font-semibold text-slate-700 text-xs">{{ $r->total_count }}</td>
                                <td class="py-3 px-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1 rounded-full bg-slate-100" style="height: 10px;">
                                            <div class="progress-bar rounded-full bg-{{ $pct >= 75 ? 'emerald-500' : 'amber-500' }}" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="font-bold text-xs {{ $pct >= 75 ? 'text-emerald-700' : 'text-amber-700' }}">{{ $pct }}%</span>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-slate-400 py-4 text-xs">No attendance data recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Fuel Cost Vehicles -->
        <div class="col-12 col-md-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
                    <div>
                        <h5 class="font-bold text-slate-800 mb-0">Highest Fuel Consumers (3 Months)</h5>
                        <p class="text-xs text-slate-400 mb-0">Vehicles with highest total fuel logs expenditure</p>
                    </div>
                    <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 font-bold text-xs d-flex align-items-center justify-content-center">
                        <i class="bi bi-fuel-pump-fill"></i>
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle text-sm mb-0">
                        <thead>
                            <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                                <th class="py-3 px-4 rounded-s-xl" style="width: 40px;">#</th>
                                <th class="py-3 px-4">Vehicle Name</th>
                                <th class="py-3 px-4">Reg. Number</th>
                                <th class="py-3 px-4 rounded-e-xl text-end">Total Fuel Cost</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($topFuelVehicles as $i => $v)
                            <tr>
                                <td class="py-3 px-4 text-xs font-semibold text-slate-400">{{ $i+1 }}</td>
                                <td class="py-3 px-4 font-bold text-slate-800 text-xs">
                                    <a href="{{ route('transport.vehicles.show', $v->id) }}" class="text-slate-800 hover:text-amber-600 transition">
                                        {{ $v->name }}
                                    </a>
                                </td>
                                <td class="py-3 px-4">
                                    <code class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded text-[11px] font-mono">
                                        {{ $v->registration_number }}
                                    </code>
                                </td>
                                <td class="py-3 px-4 text-end font-bold text-amber-700 text-xs">
                                    Rs. {{ number_format($v->total_fuel_cost ?? 0, 2) }}
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-slate-400 py-4 text-xs">No fuel consumption logs recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Fleet by type donut
new ApexCharts(document.getElementById('typeChart'), {
    series: @json($byType->values()),
    labels: @json($byType->keys()->map(fn($k) => ucfirst($k))),
    chart: { type: 'donut', height: 200 },
    legend: { position: 'bottom', fontSize: '11px' },
    dataLabels: { enabled: false },
}).render();

// Fleet by status donut
new ApexCharts(document.getElementById('statusChart'), {
    series: @json($byStatus->values()),
    labels: @json($byStatus->keys()->map(fn($k) => ucfirst($k))),
    chart: { type: 'donut', height: 200 },
    colors: ['#10b981','#f59e0b','#64748b'],
    legend: { position: 'bottom', fontSize: '11px' },
    dataLabels: { enabled: false },
}).render();

// Monthly fuel litres
const fuelMonthly = @json($fuelMonthly);
const months   = fuelMonthly.map(r => r.month);
const litres   = fuelMonthly.map(r => parseFloat(r.litres));
const fuelCosts= fuelMonthly.map(r => parseFloat(r.cost));

new ApexCharts(document.getElementById('fuelLitresChart'), {
    series: [{ name: 'Litres', data: litres }],
    chart: { type: 'bar', height: 200, toolbar: { show: false } },
    xaxis: { categories: months },
    colors: ['#0284c7'],
    dataLabels: { enabled: false },
    plotOptions: { bar: { borderRadius: 4 } },
}).render();

new ApexCharts(document.getElementById('fuelCostChart'), {
    series: [{ name: 'Cost (Rs)', data: fuelCosts }],
    chart: { type: 'area', height: 200, toolbar: { show: false } },
    xaxis: { categories: months },
    colors: ['#f59e0b'],
    stroke: { curve: 'smooth', width: 2 },
    fill: { type: 'gradient', gradient: { opacityFrom: 0.5, opacityTo: 0.1 } },
    dataLabels: { enabled: false },
}).render();

// Maintenance by type
const maintData = @json($maintByType);
new ApexCharts(document.getElementById('maintChart'), {
    series: Object.values(maintData).map(Number),
    labels: Object.keys(maintData).map(k => k.replace('_',' ').replace(/\b\w/g, l => l.toUpperCase())),
    chart: { type: 'pie', height: 200 },
    legend: { position: 'bottom', fontSize: '11px' },
    dataLabels: { enabled: false },
}).render();
</script>
@endpush
