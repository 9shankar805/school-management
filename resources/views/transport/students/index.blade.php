@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-people"></i> Student Transport Allocations</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('transport.index') }}">Transport</a></li>
                            <li class="breadcrumb-item active">Students</li>
                        </ol>
                    </nav>

                    @if(session('status'))
                        <div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif

                    <div class="d-flex gap-2 mb-3">
                        <a href="{{ route('transport.students.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Allocate Student</a>
                    </div>

                    <form method="GET" action="{{ route('transport.students.index') }}" class="row g-2 mb-3">
                        <div class="col-md-4"><input type="text" name="search" class="form-control form-control-sm" placeholder="Student name or email…" value="{{ request('search') }}"></div>
                        <div class="col-md-3">
                            <select name="route_id" class="form-select form-select-sm">
                                <option value="">All Routes</option>
                                @foreach($routes as $r)
                                    <option value="{{ $r->id }}" @selected(request('route_id') == $r->id)>{{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="active"    @selected(request('status')==='active')>Active</option>
                                <option value="suspended" @selected(request('status')==='suspended')>Suspended</option>
                                <option value="cancelled" @selected(request('status')==='cancelled')>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-2"><button class="btn btn-secondary btn-sm w-100" type="submit"><i class="bi bi-search"></i> Filter</button></div>
                        @if(request()->hasAny(['search','route_id','status']))<div class="col-md-1"><a href="{{ route('transport.students.index') }}" class="btn btn-outline-secondary btn-sm w-100">Clear</a></div>@endif
                    </form>

                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr><th>Student</th><th>Route</th><th>Stop</th><th>Direction</th><th>Monthly Fee</th><th>Start Date</th><th>Status</th><th>Actions</th></tr>
                                </thead>
                                <tbody>
                                    @forelse($allocations as $a)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold small">{{ $a->student->first_name }} {{ $a->student->last_name }}</div>
                                            <div class="small text-muted">{{ $a->student->email }}</div>
                                        </td>
                                        <td class="small">{{ $a->route->name }}</td>
                                        <td class="small text-muted">{{ $a->stop?->name ?? '—' }}</td>
                                        <td class="small">{{ ucfirst(str_replace('_',' ',$a->direction)) }}</td>
                                        <td class="small">${{ number_format($a->monthly_fee,2) }}</td>
                                        <td class="small">{{ $a->start_date->format('d M Y') }}</td>
                                        <td>{!! $a->status_badge !!}</td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('transport.students.edit', $a->id) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i></a>
                                                <form action="{{ route('transport.students.destroy', $a->id) }}" method="POST" onsubmit="return confirm('Remove allocation?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="8" class="text-center text-muted py-4">No allocations found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="mt-3">{{ $allocations->links() }}</div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
