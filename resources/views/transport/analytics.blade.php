@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-bar-chart-line"></i> Transport Analytics</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('transport.index') }}">Transport</a></li>
                            <li class="breadcrumb-item active">Analytics</li>
                        </ol>
                    </nav>

                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <div class="card shadow-sm">
                                <div class="card-header fw-semibold small">Fleet by Type</div>
                                <div class="card-body"><div id="typeChart"></div></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card shadow-sm">
                                <div class="card-header fw-semibold small">Fleet by Status</div>
                                <div class="card-body"><div id="statusChart"></div></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-header fw-semibold small">Monthly Fuel Usage (12 months)</div>
                                <div class="card-body"><div id="fuelLitresChart"></div></div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-header fw-semibold small">Monthly Fuel Cost ($)</div>
                                <div class="card-body"><div id="fuelCostChart"></div></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-header fw-semibold small">Maintenance Cost by Type</div>
                                <div class="card-body"><div id="maintChart"></div></div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        {{-- Route attendance rates --}}
                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-header fw-semibold small">Route Attendance Rate (Last 30 Days)</div>
                                <div class="card-body p-0">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light"><tr><th>Route</th><th class="text-center">Present</th><th class="text-center">Total Trips</th><th>Rate</th></tr></thead>
                                        <tbody>
                                            @forelse($attByRoute as $r)
                                            @php
                                                $pct = $r->total_count > 0 ? round(($r->present_count / $r->total_count) * 100) : 0;
                                            @endphp
                                            <tr>
                                                <td class="small">{{ $r->name }}</td>
                                                <td class="text-center small">{{ $r->present_count }}</td>
                                                <td class="text-center small">{{ $r->total_count }}</td>
                                                <td>
                                                    <div class="progress" style="height:14px">
                                                        <div class="progress-bar bg-{{ $pct >= 75 ? 'success' : 'warning' }}" style="width:{{ $pct }}%">{{ $pct }}%</div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="4" class="text-center text-muted py-2 small">No data.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Top fuel consumers --}}
                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-header fw-semibold small">Top Fuel Cost Vehicles (Last 3 Months)</div>
                                <div class="card-body p-0">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light"><tr><th>#</th><th>Vehicle</th><th>Reg. No.</th><th>Total Fuel Cost</th></tr></thead>
                                        <tbody>
                                            @forelse($topFuelVehicles as $i => $v)
                                            <tr>
                                                <td class="text-muted">{{ $i+1 }}</td>
                                                <td class="small"><a href="{{ route('transport.vehicles.show', $v->id) }}" class="text-decoration-none">{{ $v->name }}</a></td>
                                                <td class="small text-muted">{{ $v->registration_number }}</td>
                                                <td class="fw-semibold">${{ number_format($v->total_fuel_cost ?? 0, 2) }}</td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="4" class="text-center text-muted py-2 small">No data.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
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

@push('scripts')
<script>
// Fleet by type donut
new ApexCharts(document.getElementById('typeChart'), {
    series: @json($byType->values()),
    labels: @json($byType->keys()->map(fn($k) => ucfirst($k))),
    chart: { type: 'donut', height: 180 },
    legend: { position: 'bottom', fontSize: '10px' },
    dataLabels: { enabled: false },
}).render();

// Fleet by status donut
new ApexCharts(document.getElementById('statusChart'), {
    series: @json($byStatus->values()),
    labels: @json($byStatus->keys()->map(fn($k) => ucfirst($k))),
    chart: { type: 'donut', height: 180 },
    colors: ['#198754','#ffc107','#6c757d'],
    legend: { position: 'bottom', fontSize: '10px' },
    dataLabels: { enabled: false },
}).render();

// Monthly fuel litres
const fuelMonthly = @json($fuelMonthly);
const months   = fuelMonthly.map(r => r.month);
const litres   = fuelMonthly.map(r => parseFloat(r.litres));
const fuelCosts= fuelMonthly.map(r => parseFloat(r.cost));

new ApexCharts(document.getElementById('fuelLitresChart'), {
    series: [{ name: 'Litres', data: litres }],
    chart: { type: 'bar', height: 180, toolbar: { show: false } },
    xaxis: { categories: months },
    colors: ['#0dcaf0'],
    dataLabels: { enabled: false },
    plotOptions: { bar: { borderRadius: 3 } },
}).render();

new ApexCharts(document.getElementById('fuelCostChart'), {
    series: [{ name: 'Cost ($)', data: fuelCosts }],
    chart: { type: 'area', height: 180, toolbar: { show: false } },
    xaxis: { categories: months },
    colors: ['#fd7e14'],
    stroke: { curve: 'smooth', width: 2 },
    fill: { type: 'gradient', gradient: { opacityFrom: 0.5, opacityTo: 0.1 } },
    dataLabels: { enabled: false },
}).render();

// Maintenance by type
const maintData = @json($maintByType);
new ApexCharts(document.getElementById('maintChart'), {
    series: Object.values(maintData).map(Number),
    labels: Object.keys(maintData).map(k => k.replace('_',' ').replace(/\b\w/g, l => l.toUpperCase())),
    chart: { type: 'pie', height: 180 },
    legend: { position: 'bottom', fontSize: '10px' },
    dataLabels: { enabled: false },
}).render();
</script>
@endpush
