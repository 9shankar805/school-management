@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('transport.vehicles.index') }}">Vehicles</a></li>
                            <li class="breadcrumb-item active">{{ $vehicle->name }}</li>
                        </ol>
                    </nav>

                    @if(session('status'))
                        <div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif

                    <div class="row g-4">
                        {{-- Vehicle info card --}}
                        <div class="col-md-4">
                            <div class="card shadow-sm">
                                <div class="card-body text-center">
                                    <div class="mb-2"><i class="bi bi-bus-front text-primary" style="font-size:3.5rem"></i></div>
                                    <h5 class="fw-bold">{{ $vehicle->name }}</h5>
                                    <p class="text-muted mb-1">{{ $vehicle->registration_number }}</p>
                                    <span class="badge bg-secondary mb-1">{{ ucfirst($vehicle->type) }}</span>
                                    {!! $vehicle->status_badge !!}
                                </div>
                                <div class="card-footer">
                                    <dl class="row mb-0 small">
                                        <dt class="col-6">Make / Model</dt><dd class="col-6">{{ $vehicle->make }} {{ $vehicle->model }}</dd>
                                        <dt class="col-6">Year</dt><dd class="col-6">{{ $vehicle->year ?? '—' }}</dd>
                                        <dt class="col-6">Capacity</dt><dd class="col-6">{{ $vehicle->capacity }} seats</dd>
                                        <dt class="col-6">Fuel</dt><dd class="col-6">{{ ucfirst($vehicle->fuel_type) }}</dd>
                                        <dt class="col-6">Color</dt><dd class="col-6">{{ $vehicle->color ?? '—' }}</dd>
                                        <dt class="col-6">GPS ID</dt><dd class="col-6">{{ $vehicle->gps_device_id ?? '—' }}</dd>
                                        <dt class="col-6">Driver</dt><dd class="col-6">{{ $vehicle->driver?->name ?? '—' }}</dd>
                                        <dt class="col-6">Routes</dt><dd class="col-6">{{ $vehicle->routes_count }}</dd>
                                    </dl>
                                </div>
                            </div>

                            {{-- Document expiry card --}}
                            <div class="card shadow-sm mt-3">
                                <div class="card-header fw-semibold small">Documents</div>
                                <div class="card-body p-2">
                                    @foreach([
                                        ['Insurance',  $vehicle->insurance_expiry],
                                        ['Fitness',    $vehicle->fitness_expiry],
                                        ['Permit',     $vehicle->permit_expiry],
                                    ] as [$label, $date])
                                    <div class="d-flex justify-content-between align-items-center mb-1 small">
                                        <span>{{ $label }}</span>
                                        @if($date)
                                            <span class="{{ $date->isPast() ? 'text-danger fw-bold' : ($date->diffInDays(now()) <= 30 ? 'text-warning fw-semibold' : 'text-success') }}">
                                                {{ $date->format('d M Y') }}
                                                @if($date->isPast()) <span class="badge bg-danger">Expired</span>
                                                @elseif($date->diffInDays(now()) <= 30) <span class="badge bg-warning text-dark">Soon</span>
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="card shadow-sm mt-3">
                                <div class="card-body">
                                    <div class="d-flex gap-2">
                                        <span class="small text-muted">Total Fuel Cost:</span>
                                        <span class="fw-semibold">${{ number_format($totalFuelCost, 2) }}</span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <span class="small text-muted">Total Maint. Cost:</span>
                                        <span class="fw-semibold">${{ number_format($totalMaintCost, 2) }}</span>
                                    </div>
                                </div>
                                <div class="card-footer d-flex gap-2">
                                    <a href="{{ route('transport.vehicles.edit', $vehicle->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
                                </div>
                            </div>
                        </div>

                        {{-- Right column --}}
                        <div class="col-md-8">
                            {{-- Upcoming maintenance --}}
                            @if($upcomingMaint->isNotEmpty())
                            <div class="alert alert-info py-2 small mb-3">
                                <i class="bi bi-tools me-1"></i>
                                <strong>{{ $upcomingMaint->count() }}</strong> scheduled maintenance task(s).
                            </div>
                            @endif

                            {{-- Add fuel log --}}
                            <div class="card shadow-sm mb-3">
                                <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-fuel-pump"></i> Fuel Logs</span>
                                    <button class="btn btn-success btn-sm" data-bs-toggle="collapse" data-bs-target="#fuelForm">+ Add</button>
                                </div>
                                <div class="collapse" id="fuelForm">
                                    <div class="card-body border-bottom">
                                        <form action="{{ route('transport.vehicles.fuel.store', $vehicle->id) }}" method="POST">
                                            @csrf
                                            <div class="row g-2">
                                                <div class="col-md-3"><label class="form-label small">Date</label><input type="date" name="date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required></div>
                                                <div class="col-md-2"><label class="form-label small">Litres</label><input type="number" name="litres" class="form-control form-control-sm" step="0.01" min="0.1" required></div>
                                                <div class="col-md-2"><label class="form-label small">Cost/Litre ($)</label><input type="number" name="cost_per_litre" class="form-control form-control-sm" step="0.01" min="0" required></div>
                                                <div class="col-md-2"><label class="form-label small">Odometer</label><input type="number" name="odometer_reading" class="form-control form-control-sm"></div>
                                                <div class="col-md-3"><label class="form-label small">Station</label><input type="text" name="fuel_station" class="form-control form-control-sm"></div>
                                                <div class="col-12"><button type="submit" class="btn btn-success btn-sm">Save Fuel Log</button></div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light"><tr><th>Date</th><th>Litres</th><th>Cost/L</th><th>Total</th><th>Odometer</th><th>Station</th><th></th></tr></thead>
                                        <tbody>
                                            @forelse($vehicle->fuelLogs as $fl)
                                            <tr>
                                                <td>{{ $fl->date->format('d M Y') }}</td>
                                                <td>{{ $fl->litres }}</td>
                                                <td>${{ $fl->cost_per_litre }}</td>
                                                <td class="fw-semibold">${{ number_format($fl->total_cost, 2) }}</td>
                                                <td>{{ $fl->odometer_reading ?? '—' }}</td>
                                                <td class="small text-muted">{{ $fl->fuel_station ?? '—' }}</td>
                                                <td>
                                                    <form action="{{ route('transport.vehicles.fuel.destroy', [$vehicle->id, $fl->id]) }}" method="POST" onsubmit="return confirm('Delete?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-outline-danger btn-sm py-0 px-1"><i class="bi bi-trash" style="font-size:.7rem"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="7" class="text-center text-muted py-2 small">No fuel logs.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Maintenance logs --}}
                            <div class="card shadow-sm">
                                <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-tools"></i> Maintenance Logs</span>
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="collapse" data-bs-target="#maintForm">+ Add</button>
                                </div>
                                <div class="collapse" id="maintForm">
                                    <div class="card-body border-bottom">
                                        <form action="{{ route('transport.vehicles.maintenance.store', $vehicle->id) }}" method="POST">
                                            @csrf
                                            <div class="row g-2">
                                                <div class="col-md-3">
                                                    <label class="form-label small">Type</label>
                                                    <select name="type" class="form-select form-select-sm" required>
                                                        @foreach(\App\Models\MaintenanceLog::TYPES as $val=>$lbl)
                                                            <option value="{{ $val }}">{{ $lbl }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-5"><label class="form-label small">Title</label><input type="text" name="title" class="form-control form-control-sm" required></div>
                                                <div class="col-md-2"><label class="form-label small">Service Date</label><input type="date" name="service_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required></div>
                                                <div class="col-md-2"><label class="form-label small">Next Service</label><input type="date" name="next_service_date" class="form-control form-control-sm"></div>
                                                <div class="col-md-3"><label class="form-label small">Cost ($)</label><input type="number" name="cost" class="form-control form-control-sm" step="0.01" value="0" required></div>
                                                <div class="col-md-3"><label class="form-label small">Provider</label><input type="text" name="service_provider" class="form-control form-control-sm"></div>
                                                <div class="col-md-2"><label class="form-label small">Odometer</label><input type="number" name="odometer_reading" class="form-control form-control-sm"></div>
                                                <div class="col-md-2">
                                                    <label class="form-label small">Status</label>
                                                    <select name="status" class="form-select form-select-sm">
                                                        <option value="completed">Completed</option>
                                                        <option value="scheduled">Scheduled</option>
                                                        <option value="in_progress">In Progress</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-12 d-flex gap-2 mt-1"><button type="submit" class="btn btn-warning btn-sm">Save Record</button></div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light"><tr><th>Date</th><th>Type</th><th>Title</th><th>Cost</th><th>Provider</th><th>Status</th></tr></thead>
                                        <tbody>
                                            @forelse($vehicle->maintenanceLogs as $ml)
                                            <tr>
                                                <td>{{ $ml->service_date->format('d M Y') }}</td>
                                                <td class="small">{{ $ml->type_label }}</td>
                                                <td class="small">{{ $ml->title }}</td>
                                                <td>${{ number_format($ml->cost, 2) }}</td>
                                                <td class="small text-muted">{{ $ml->service_provider ?? '—' }}</td>
                                                <td>{!! $ml->status_badge !!}</td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="6" class="text-center text-muted py-2 small">No maintenance records.</td></tr>
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
