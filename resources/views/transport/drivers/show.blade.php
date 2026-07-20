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
                            <li class="breadcrumb-item"><a href="{{ route('transport.drivers.index') }}">Drivers</a></li>
                            <li class="breadcrumb-item active">{{ $driver->name }}</li>
                        </ol>
                    </nav>
                    @if(session('status'))
                        <div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="card shadow-sm text-center">
                                <div class="card-body">
                                    @if($driver->photo)
                                        <img src="{{ Storage::url($driver->photo) }}" class="rounded-circle mb-3" style="width:100px;height:100px;object-fit:cover">
                                    @else
                                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:100px;height:100px">
                                            <i class="bi bi-person-fill text-secondary" style="font-size:3rem"></i>
                                        </div>
                                    @endif
                                    <h5 class="fw-bold">{{ $driver->name }}</h5>
                                    <p class="text-muted small mb-1">{{ $driver->employee_id ?? '' }}</p>
                                    {!! $driver->status_badge !!}
                                </div>
                                <div class="card-footer">
                                    <dl class="row mb-0 small text-start">
                                        <dt class="col-5">Phone</dt><dd class="col-7">{{ $driver->phone ?? '—' }}</dd>
                                        <dt class="col-5">Email</dt><dd class="col-7">{{ $driver->email ?? '—' }}</dd>
                                        <dt class="col-5">License</dt><dd class="col-7">{{ $driver->license_number }}</dd>
                                        <dt class="col-5">Type</dt><dd class="col-7">{{ $driver->license_type ?? '—' }}</dd>
                                        <dt class="col-5">Expiry</dt>
                                        <dd class="col-7 {{ $driver->is_license_expired ? 'text-danger fw-bold' : '' }}">
                                            {{ $driver->license_expiry?->format('d M Y') ?? '—' }}
                                        </dd>
                                        <dt class="col-5">Vehicle</dt><dd class="col-7">{{ $driver->currentVehicle?->name ?? '—' }}</dd>
                                        <dt class="col-5">Salary</dt><dd class="col-7">{{ $driver->salary ? '$'.number_format($driver->salary,2) : '—' }}</dd>
                                        <dt class="col-5">Joined</dt><dd class="col-7">{{ $driver->joining_date?->format('d M Y') ?? '—' }}</dd>
                                    </dl>
                                </div>
                                <div class="card-footer d-flex gap-2">
                                    <a href="{{ route('transport.drivers.edit', $driver->id) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="card shadow-sm">
                                <div class="card-header fw-semibold"><i class="bi bi-signpost-split"></i> Assigned Routes</div>
                                <div class="card-body p-0">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light"><tr><th>Route</th><th>Vehicle</th><th>Morning</th><th>Students</th><th>Status</th></tr></thead>
                                        <tbody>
                                            @forelse($driver->routes as $route)
                                            <tr>
                                                <td><a href="{{ route('transport.routes.show', $route->id) }}" class="text-decoration-none">{{ $route->name }}</a></td>
                                                <td class="small">{{ $route->vehicle?->name ?? '—' }}</td>
                                                <td class="small">{{ $route->morning_departure ?? '—' }}</td>
                                                <td><span class="badge bg-primary">{{ $route->student_count }}</span></td>
                                                <td>{!! $route->status_badge !!}</td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="5" class="text-center text-muted py-3">No routes assigned.</td></tr>
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
