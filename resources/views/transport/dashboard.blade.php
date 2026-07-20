@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-bus-front"></i> Transport Management</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item active">Transport</li>
                        </ol>
                    </nav>

                    @if(session('status'))
                        <div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif

                    {{-- KPI strip --}}
                    <div class="row g-3 mb-4">
                        @php $kpis = [
                            ['val'=>$totalVehicles,  'label'=>'Total Vehicles',  'icon'=>'bi-truck',              'color'=>'primary',   'sub'=>$activeVehicles.' active'],
                            ['val'=>$inMaintenance,  'label'=>'In Maintenance',  'icon'=>'bi-tools',              'color'=>'warning',   'sub'=>'vehicles'],
                            ['val'=>$totalDrivers,   'label'=>'Total Drivers',   'icon'=>'bi-person-badge',       'color'=>'info',      'sub'=>$activeDrivers.' active'],
                            ['val'=>$totalRoutes,    'label'=>'Routes',           'icon'=>'bi-signpost-split',     'color'=>'success',   'sub'=>$activeRoutes.' active'],
                            ['val'=>$totalStudents,  'label'=>'Students on Bus',  'icon'=>'bi-people',             'color'=>'secondary', 'sub'=>'active allocations'],
                            ['val'=>$todayPresent,   'label'=>'Today Present',    'icon'=>'bi-check2-circle',      'color'=>'success',   'sub'=>'transport attendance'],
                            ['val'=>$todayAbsent,    'label'=>'Today Absent',     'icon'=>'bi-x-circle',           'color'=>'danger',    'sub'=>'transport attendance'],
                        ]; @endphp
                        @foreach($kpis as $kpi)
                        <div class="col-6 col-md-3">
                            <div class="card border-0 bg-{{ $kpi['color'] }} bg-opacity-10 text-center py-3">
                                <div><i class="bi {{ $kpi['icon'] }} fs-4 text-{{ $kpi['color'] }}"></i></div>
                                <div class="fs-4 fw-bold text-{{ $kpi['color'] }}">{{ $kpi['val'] }}</div>
                                <div class="small fw-semibold">{{ $kpi['label'] }}</div>
                                <div class="text-muted" style="font-size:.72rem">{{ $kpi['sub'] }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Alerts row --}}
                    @if($vehicleAlerts->isNotEmpty() || $driverAlerts->isNotEmpty())
                    <div class="row g-3 mb-4">
                        @if($vehicleAlerts->isNotEmpty())
                        <div class="col-md-6">
                            <div class="card border-warning shadow-sm">
                                <div class="card-header fw-semibold text-warning bg-warning bg-opacity-10">
                                    <i class="bi bi-exclamation-triangle"></i> Vehicle Document Alerts ({{ $vehicleAlerts->count() }})
                                </div>
                                <ul class="list-group list-group-flush">
                                    @foreach($vehicleAlerts->take(5) as $v)
                                    <li class="list-group-item small d-flex justify-content-between align-items-center">
                                        <span><a href="{{ route('transport.vehicles.show', $v->id) }}" class="text-decoration-none">{{ $v->name }}</a> ({{ $v->registration_number }})</span>
                                        <span>
                                            @if($v->insurance_expiry?->isPast() || $v->insurance_expiry?->diffInDays(now()) <= 30)
                                                <span class="badge bg-danger">Insurance</span>
                                            @endif
                                            @if($v->fitness_expiry?->isPast() || $v->fitness_expiry?->diffInDays(now()) <= 30)
                                                <span class="badge bg-warning text-dark">Fitness</span>
                                            @endif
                                        </span>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        @endif
                        @if($driverAlerts->isNotEmpty())
                        <div class="col-md-6">
                            <div class="card border-warning shadow-sm">
                                <div class="card-header fw-semibold text-warning bg-warning bg-opacity-10">
                                    <i class="bi bi-card-checklist"></i> Driver License Alerts ({{ $driverAlerts->count() }})
                                </div>
                                <ul class="list-group list-group-flush">
                                    @foreach($driverAlerts->take(5) as $d)
                                    <li class="list-group-item small d-flex justify-content-between align-items-center">
                                        <span><a href="{{ route('transport.drivers.show', $d->id) }}" class="text-decoration-none">{{ $d->name }}</a></span>
                                        <span class="badge {{ $d->is_license_expired ? 'bg-danger' : 'bg-warning text-dark' }}">
                                            Exp: {{ $d->license_expiry?->format('d M Y') }}
                                        </span>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif

                    <div class="row g-4 mb-4">
                        {{-- Fuel cost chart --}}
                        <div class="col-md-7">
                            <div class="card shadow-sm">
                                <div class="card-header fw-semibold">Monthly Fuel Cost (Last 6 Months)</div>
                                <div class="card-body"><div id="fuelChart"></div></div>
                            </div>
                        </div>
                        {{-- Upcoming maintenance --}}
                        <div class="col-md-5">
                            <div class="card shadow-sm">
                                <div class="card-header fw-semibold"><i class="bi bi-tools"></i> Upcoming Maintenance</div>
                                <div class="card-body p-0">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light"><tr><th>Vehicle</th><th>Type</th><th>Date</th></tr></thead>
                                        <tbody>
                                            @forelse($upcomingMaint as $m)
                                            <tr>
                                                <td><a href="{{ route('transport.vehicles.show', $m->vehicle_id) }}" class="text-decoration-none small">{{ $m->vehicle->name }}</a></td>
                                                <td class="small">{{ $m->type_label }}</td>
                                                <td class="small">{{ $m->service_date->format('d M Y') }}</td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="3" class="text-center text-muted py-2 small">No upcoming maintenance.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Route statistics --}}
                    <div class="card shadow-sm">
                        <div class="card-header fw-semibold"><i class="bi bi-signpost-split"></i> Route Overview</div>
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr><th>Route</th><th>Vehicle</th><th>Morning</th><th class="text-center">Students</th><th>Status</th><th></th></tr>
                                </thead>
                                <tbody>
                                    @forelse($routeStats as $route)
                                    <tr>
                                        <td class="fw-semibold small">{{ $route->name }}</td>
                                        <td class="small text-muted">{{ $route->vehicle?->name ?? '—' }}</td>
                                        <td class="small">{{ $route->morning_departure ?? '—' }}</td>
                                        <td class="text-center"><span class="badge bg-primary">{{ $route->active_students_count }}</span></td>
                                        <td>{!! $route->status_badge !!}</td>
                                        <td><a href="{{ route('transport.routes.show', $route->id) }}" class="btn btn-outline-info btn-sm"><i class="bi bi-eye"></i></a></td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="6" class="text-center text-muted py-3">No routes yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
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
const fuelData  = @json($fuelByMonth);
const months    = Object.keys(fuelData);
const costs     = Object.values(fuelData).map(Number);

new ApexCharts(document.getElementById('fuelChart'), {
    series: [{ name: 'Fuel Cost ($)', data: costs }],
    chart: { type: 'bar', height: 220, toolbar: { show: false } },
    xaxis: { categories: months },
    colors: ['#fd7e14'],
    dataLabels: { enabled: false },
    plotOptions: { bar: { borderRadius: 4 } },
}).render();
</script>
@endpush
