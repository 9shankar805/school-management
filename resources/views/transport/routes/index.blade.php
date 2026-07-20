@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-signpost-split"></i> Routes</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('transport.index') }}">Transport</a></li>
                            <li class="breadcrumb-item active">Routes</li>
                        </ol>
                    </nav>

                    @if(session('status'))
                        <div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif

                    <div class="d-flex gap-2 mb-3">
                        <a href="{{ route('transport.routes.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Add Route</a>
                    </div>

                    <form method="GET" action="{{ route('transport.routes.index') }}" class="row g-2 mb-3">
                        <div class="col-md-5"><input type="text" name="search" class="form-control form-control-sm" placeholder="Route name or code…" value="{{ request('search') }}"></div>
                        <div class="col-md-2">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="active"       @selected(request('status')==='active')>Active</option>
                                <option value="suspended"    @selected(request('status')==='suspended')>Suspended</option>
                                <option value="discontinued" @selected(request('status')==='discontinued')>Discontinued</option>
                            </select>
                        </div>
                        <div class="col-md-2"><button class="btn btn-secondary btn-sm w-100" type="submit"><i class="bi bi-search"></i> Filter</button></div>
                        @if(request()->hasAny(['search','status']))<div class="col-md-1"><a href="{{ route('transport.routes.index') }}" class="btn btn-outline-secondary btn-sm w-100">Clear</a></div>@endif
                    </form>

                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr><th>Route</th><th>Vehicle</th><th>Driver</th><th>Morning</th><th>Afternoon</th><th class="text-center">Students</th><th>Fee</th><th>Status</th><th>Actions</th></tr>
                                </thead>
                                <tbody>
                                    @forelse($routes as $r)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><a href="{{ route('transport.routes.show', $r->id) }}" class="text-decoration-none text-dark">{{ $r->name }}</a></div>
                                            @if($r->code)<div class="small text-muted"><code>{{ $r->code }}</code></div>@endif
                                        </td>
                                        <td class="small">{{ $r->vehicle?->name ?? '—' }}</td>
                                        <td class="small">{{ $r->driver?->name ?? '—' }}</td>
                                        <td class="small">{{ $r->morning_departure ?? '—' }}</td>
                                        <td class="small">{{ $r->afternoon_departure ?? '—' }}</td>
                                        <td class="text-center"><span class="badge bg-primary">{{ $r->active_students_count }}</span></td>
                                        <td class="small">${{ number_format($r->monthly_fee, 2) }}/mo</td>
                                        <td>{!! $r->status_badge !!}</td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('transport.routes.show', $r->id) }}" class="btn btn-outline-info btn-sm"><i class="bi bi-eye"></i></a>
                                                <a href="{{ route('transport.routes.edit', $r->id) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i></a>
                                                <form action="{{ route('transport.routes.destroy', $r->id) }}" method="POST" onsubmit="return confirm('Delete route?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="9" class="text-center text-muted py-4">No routes found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="mt-3">{{ $routes->links() }}</div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
