@extends('layouts.app')
@section('content')
<div class="px-4 lg:px-8 py-6 max-w-7xl mx-auto">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-10 h-10 rounded-2xl bg-amber-600/10 text-amber-600 flex items-center justify-center font-bold text-xl shadow-xs">
                    <i class="bi bi-bus-front"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Transport Management</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Manage vehicles, drivers, routes, and student allocations</p>
                </div>
            </div>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800/80 px-3 py-1.5 rounded-xl">
                <li><a href="{{ route('home') }}" class="hover:text-amber-600 transition">Home</a></li>
                <li class="text-slate-300 dark:text-slate-600">/</li>
                <li class="font-semibold text-slate-800 dark:text-slate-200">Transport</li>
            </ol>
        </nav>
    </div>

    @include('session-messages')

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
        @php $kpis = [
            ['val'=>$totalVehicles,  'label'=>'Total Vehicles',  'icon'=>'bi-truck',              'color'=>'indigo',   'sub'=>$activeVehicles.' active'],
            ['val'=>$inMaintenance,  'label'=>'In Maintenance',  'icon'=>'bi-tools',              'color'=>'amber',   'sub'=>'vehicles'],
            ['val'=>$totalDrivers,   'label'=>'Total Drivers',   'icon'=>'bi-person-badge',       'color'=>'blue',    'sub'=>$activeDrivers.' active'],
            ['val'=>$totalRoutes,    'label'=>'Routes',           'icon'=>'bi-signpost-split',     'color'=>'emerald', 'sub'=>$activeRoutes.' active'],
            ['val'=>$totalStudents,  'label'=>'Students on Bus',  'icon'=>'bi-people',             'color'=>'slate',   'sub'=>'active allocations'],
            ['val'=>$todayPresent,   'label'=>'Today Present',    'icon'=>'bi-check2-circle',      'color'=>'emerald', 'sub'=>'transport attendance'],
        ]; @endphp
        @foreach($kpis as $kpi)
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-4">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-{{ $kpi['color'] }}-500/10 text-{{ $kpi['color'] }}-600 flex items-center justify-center">
                    <i class="bi {{ $kpi['icon'] }} text-lg"></i>
                </span>
                <div>
                    <div class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $kpi['val'] }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ $kpi['label'] }}</div>
                    <div class="text-[10px] text-slate-400">{{ $kpi['sub'] }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Alerts row --}}
    @if($vehicleAlerts->isNotEmpty() || $driverAlerts->isNotEmpty())
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
        @if($vehicleAlerts->isNotEmpty())
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-amber-200 dark:border-amber-900/50 shadow-xs p-6">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                <span class="w-2 h-5 bg-amber-600 rounded-full"></span>
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Vehicle Document Alerts ({{ $vehicleAlerts->count() }})</h2>
            </div>
            <div class="space-y-2">
                @foreach($vehicleAlerts->take(5) as $v)
                <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                    <span class="text-xs text-slate-700 dark:text-slate-300">
                        <a href="{{ route('transport.vehicles.show', $v->id) }}" class="hover:text-amber-600 transition">{{ $v->name }}</a> ({{ $v->registration_number }})
                    </span>
                    <span class="flex gap-1">
                        @if($v->insurance_expiry?->isPast() || $v->insurance_expiry?->diffInDays(now()) <= 30)
                            <span class="px-2 py-0.5 rounded-lg bg-rose-100 dark:bg-rose-900/30 text-rose-600 text-[10px] font-semibold">Insurance</span>
                        @endif
                        @if($v->fitness_expiry?->isPast() || $v->fitness_expiry?->diffInDays(now()) <= 30)
                            <span class="px-2 py-0.5 rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-600 text-[10px] font-semibold">Fitness</span>
                        @endif
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        @if($driverAlerts->isNotEmpty())
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-amber-200 dark:border-amber-900/50 shadow-xs p-6">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                <span class="w-2 h-5 bg-amber-600 rounded-full"></span>
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Driver License Alerts ({{ $driverAlerts->count() }})</h2>
            </div>
            <div class="space-y-2">
                @foreach($driverAlerts->take(5) as $d)
                <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 dark:bg-slate-800/50">
                    <span class="text-xs text-slate-700 dark:text-slate-300">
                        <a href="{{ route('transport.drivers.show', $d->id) }}" class="hover:text-amber-600 transition">{{ $d->name }}</a>
                    </span>
                    <span class="px-2 py-0.5 rounded-lg {{ $d->is_license_expired ? 'bg-rose-100 dark:bg-rose-900/30 text-rose-600' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-600' }} text-[10px] font-semibold">
                        Exp: {{ $d->license_expiry?->format('d M Y') }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
        {{-- Fuel cost chart --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                <span class="w-2 h-5 bg-emerald-600 rounded-full"></span>
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Monthly Fuel Cost (Last 6 Months)</h2>
            </div>
            <div id="fuelChart"></div>
        </div>
        {{-- Upcoming maintenance --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                <span class="w-2 h-5 bg-amber-600 rounded-full"></span>
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Upcoming Maintenance</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-semibold bg-slate-50/50 dark:bg-slate-800/20">
                        <tr>
                            <th class="py-3 px-4">Vehicle</th>
                            <th class="py-3 px-4">Type</th>
                            <th class="py-3 px-4">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($upcomingMaint as $m)
                        <tr class="border-b border-slate-50 dark:border-slate-800/50 hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                            <td class="py-3 px-4"><a href="{{ route('transport.vehicles.show', $m->vehicle_id) }}" class="hover:text-amber-600 transition">{{ $m->vehicle->name }}</a></td>
                            <td class="py-3 px-4">{{ $m->type_label }}</td>
                            <td class="py-3 px-4">{{ $m->service_date->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-slate-400 py-6">No upcoming maintenance.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const fuelCtx = document.getElementById('fuelChart');
    if (fuelCtx) {
        new Chart(fuelCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($fuelByMonth->keys()) !!},
                datasets: [{
                    label: 'Fuel Cost',
                    data: {!! json_encode($fuelByMonth->values()) !!},
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }
</script>
@endpush
@endsection
