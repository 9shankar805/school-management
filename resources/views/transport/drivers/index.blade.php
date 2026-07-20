@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-person-badge"></i> Drivers</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('transport.index') }}">Transport</a></li>
                            <li class="breadcrumb-item active">Drivers</li>
                        </ol>
                    </nav>

                    @if(session('status'))
                        <div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif

                    @if($licenseAlerts->isNotEmpty())
                    <div class="alert alert-warning py-2">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <strong>{{ $licenseAlerts->count() }}</strong> driver(s) have licenses expiring within 30 days.
                    </div>
                    @endif

                    <div class="d-flex gap-2 mb-3">
                        <a href="{{ route('transport.drivers.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Add Driver</a>
                    </div>

                    <form method="GET" action="{{ route('transport.drivers.index') }}" class="row g-2 mb-3">
                        <div class="col-md-5"><input type="text" name="search" class="form-control form-control-sm" placeholder="Name, phone, license…" value="{{ request('search') }}"></div>
                        <div class="col-md-2">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="active"     @selected(request('status')==='active')>Active</option>
                                <option value="on_leave"   @selected(request('status')==='on_leave')>On Leave</option>
                                <option value="terminated" @selected(request('status')==='terminated')>Terminated</option>
                            </select>
                        </div>
                        <div class="col-md-2"><button class="btn btn-secondary btn-sm w-100" type="submit"><i class="bi bi-search"></i> Filter</button></div>
                        @if(request()->hasAny(['search','status']))
                        <div class="col-md-1"><a href="{{ route('transport.drivers.index') }}" class="btn btn-outline-secondary btn-sm w-100">Clear</a></div>
                        @endif
                    </form>

                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr><th>Name</th><th>Phone</th><th>License</th><th>License Expiry</th><th>Vehicle</th><th>Status</th><th>Actions</th></tr>
                                </thead>
                                <tbody>
                                    @forelse($drivers as $d)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><a href="{{ route('transport.drivers.show', $d->id) }}" class="text-decoration-none text-dark">{{ $d->name }}</a></div>
                                            <div class="small text-muted">{{ $d->employee_id ?? '' }}</div>
                                        </td>
                                        <td class="small">{{ $d->phone ?? '—' }}</td>
                                        <td class="small">{{ $d->license_number }}</td>
                                        <td class="small {{ $d->is_license_expired ? 'text-danger fw-bold' : ($d->is_license_expiring_soon ? 'text-warning fw-semibold' : '') }}">
                                            {{ $d->license_expiry?->format('d M Y') ?? '—' }}
                                            @if($d->is_license_expired)<span class="badge bg-danger ms-1">Expired</span>
                                            @elseif($d->is_license_expiring_soon)<span class="badge bg-warning text-dark ms-1">Soon</span>@endif
                                        </td>
                                        <td class="small">{{ $d->currentVehicle?->name ?? '—' }}</td>
                                        <td>{!! $d->status_badge !!}</td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('transport.drivers.show', $d->id) }}" class="btn btn-outline-info btn-sm"><i class="bi bi-eye"></i></a>
                                                <a href="{{ route('transport.drivers.edit', $d->id) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i></a>
                                                <form action="{{ route('transport.drivers.destroy', $d->id) }}" method="POST" onsubmit="return confirm('Remove driver?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="7" class="text-center text-muted py-4">No drivers found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="mt-3">{{ $drivers->links() }}</div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
