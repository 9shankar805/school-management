@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-truck"></i> Fleet Management</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('transport.index') }}">Transport</a></li>
                            <li class="breadcrumb-item active">Vehicles</li>
                        </ol>
                    </nav>

                    @if(session('status'))
                        <div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif

                    @if($expiryAlerts->isNotEmpty())
                    <div class="alert alert-warning d-flex gap-2 align-items-start py-2">
                        <i class="bi bi-exclamation-triangle-fill fs-5 mt-1"></i>
                        <div><strong>{{ $expiryAlerts->count() }}</strong> vehicle(s) have documents expiring within 30 days or already expired.</div>
                    </div>
                    @endif

                    <div class="d-flex gap-2 mb-3">
                        <a href="{{ route('transport.vehicles.create') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle"></i> Add Vehicle
                        </a>
                    </div>

                    <form method="GET" action="{{ route('transport.vehicles.index') }}" class="row g-2 mb-3">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, reg. number, make…" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="type" class="form-select form-select-sm">
                                <option value="">All Types</option>
                                @foreach(['bus'=>'Bus','van'=>'Van','minibus'=>'Minibus','car'=>'Car'] as $val=>$lbl)
                                    <option value="{{ $val }}" @selected(request('type')===$val)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="active"      @selected(request('status')==='active')>Active</option>
                                <option value="maintenance" @selected(request('status')==='maintenance')>Maintenance</option>
                                <option value="retired"     @selected(request('status')==='retired')>Retired</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-secondary btn-sm w-100" type="submit"><i class="bi bi-search"></i> Filter</button>
                        </div>
                        @if(request()->hasAny(['search','type','status']))
                        <div class="col-md-1"><a href="{{ route('transport.vehicles.index') }}" class="btn btn-outline-secondary btn-sm w-100">Clear</a></div>
                        @endif
                    </form>

                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Vehicle</th><th>Type</th><th>Capacity</th><th>Driver</th>
                                        <th>Insurance</th><th>Fitness</th><th>Status</th><th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($vehicles as $v)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><a href="{{ route('transport.vehicles.show', $v->id) }}" class="text-decoration-none text-dark">{{ $v->name }}</a></div>
                                            <div class="small text-muted">{{ $v->registration_number }}</div>
                                        </td>
                                        <td><span class="badge bg-secondary">{{ ucfirst($v->type) }}</span></td>
                                        <td class="text-center">{{ $v->capacity }}</td>
                                        <td class="small">{{ $v->driver?->name ?? '—' }}</td>
                                        <td class="small {{ $v->is_insurance_expired ? 'text-danger fw-bold' : ($v->is_insurance_expiring_soon ? 'text-warning fw-semibold' : '') }}">
                                            {{ $v->insurance_expiry?->format('d M Y') ?? '—' }}
                                        </td>
                                        <td class="small {{ $v->fitness_expiry?->isPast() ? 'text-danger fw-bold' : '' }}">
                                            {{ $v->fitness_expiry?->format('d M Y') ?? '—' }}
                                        </td>
                                        <td>{!! $v->status_badge !!}</td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('transport.vehicles.show', $v->id) }}" class="btn btn-outline-info btn-sm"><i class="bi bi-eye"></i></a>
                                                <a href="{{ route('transport.vehicles.edit', $v->id) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i></a>
                                                <form action="{{ route('transport.vehicles.destroy', $v->id) }}" method="POST" onsubmit="return confirm('Delete this vehicle?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="8" class="text-center text-muted py-4">No vehicles found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="mt-3">{{ $vehicles->links() }}</div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
