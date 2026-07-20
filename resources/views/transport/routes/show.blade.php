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
                            <li class="breadcrumb-item"><a href="{{ route('transport.routes.index') }}">Routes</a></li>
                            <li class="breadcrumb-item active">{{ $route->name }}</li>
                        </ol>
                    </nav>

                    @if(session('status'))
                        <div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif

                    <div class="row g-4">
                        {{-- Route summary --}}
                        <div class="col-md-4">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="fw-bold">{{ $route->name }}</h5>
                                    @if($route->code)<code class="d-block mb-2">{{ $route->code }}</code>@endif
                                    {!! $route->status_badge !!}
                                    <dl class="row mt-3 mb-0 small">
                                        <dt class="col-5">Vehicle</dt><dd class="col-7">{{ $route->vehicle?->name ?? '—' }}</dd>
                                        <dt class="col-5">Driver</dt><dd class="col-7">{{ $route->driver?->name ?? '—' }}</dd>
                                        <dt class="col-5">Morning Dep.</dt><dd class="col-7">{{ $route->morning_departure ?? '—' }}</dd>
                                        <dt class="col-5">Morning Arr.</dt><dd class="col-7">{{ $route->morning_arrival ?? '—' }}</dd>
                                        <dt class="col-5">Afternoon Dep.</dt><dd class="col-7">{{ $route->afternoon_departure ?? '—' }}</dd>
                                        <dt class="col-5">Afternoon Arr.</dt><dd class="col-7">{{ $route->afternoon_arrival ?? '—' }}</dd>
                                        <dt class="col-5">Distance</dt><dd class="col-7">{{ $route->distance_km ? $route->distance_km.' km' : '—' }}</dd>
                                        <dt class="col-5">Monthly Fee</dt><dd class="col-7 fw-semibold">${{ number_format($route->monthly_fee, 2) }}</dd>
                                        <dt class="col-5">Students</dt><dd class="col-7"><span class="badge bg-primary">{{ $route->active_students_count }}</span></dd>
                                    </dl>
                                    @if($route->description)<p class="text-muted small mt-2">{{ $route->description }}</p>@endif
                                </div>
                                <div class="card-footer d-flex gap-2">
                                    <a href="{{ route('transport.routes.edit', $route->id) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                                    <a href="{{ route('transport.attendance.index', ['route_id'=>$route->id]) }}" class="btn btn-outline-success btn-sm"><i class="bi bi-check2-square"></i> Attendance</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            {{-- Stops management --}}
                            <div class="card shadow-sm mb-3">
                                <div class="card-header fw-semibold d-flex justify-content-between">
                                    <span><i class="bi bi-geo-alt"></i> Route Stops ({{ $route->stops->count() }})</span>
                                    <button class="btn btn-success btn-sm" data-bs-toggle="collapse" data-bs-target="#addStopForm">+ Add Stop</button>
                                </div>
                                <div class="collapse" id="addStopForm">
                                    <div class="card-body border-bottom bg-light">
                                        <form action="{{ route('transport.routes.stops.store', $route->id) }}" method="POST">
                                            @csrf
                                            <div class="row g-2">
                                                <div class="col-md-4"><label class="form-label small">Stop Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control form-control-sm" required></div>
                                                <div class="col-md-2"><label class="form-label small">Order <span class="text-danger">*</span></label><input type="number" name="stop_order" class="form-control form-control-sm" min="1" value="{{ $route->stops->count() + 1 }}" required></div>
                                                <div class="col-md-2"><label class="form-label small">Morning Pickup</label><input type="time" name="morning_pickup" class="form-control form-control-sm"></div>
                                                <div class="col-md-2"><label class="form-label small">Afternoon Drop</label><input type="time" name="afternoon_dropoff" class="form-control form-control-sm"></div>
                                                <div class="col-md-2"><label class="form-label small">Stop Fee ($)</label><input type="number" name="stop_fee" class="form-control form-control-sm" value="0" min="0" step="0.01"></div>
                                                <div class="col-md-6"><label class="form-label small">Landmark</label><input type="text" name="landmark" class="form-control form-control-sm" placeholder="Near Market, etc."></div>
                                                <div class="col-md-3"><label class="form-label small">Latitude</label><input type="number" name="latitude" class="form-control form-control-sm" step="0.0000001"></div>
                                                <div class="col-md-3"><label class="form-label small">Longitude</label><input type="number" name="longitude" class="form-control form-control-sm" step="0.0000001"></div>
                                                <div class="col-12"><button type="submit" class="btn btn-success btn-sm">Save Stop</button></div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light">
                                            <tr><th>#</th><th>Stop Name</th><th>Pickup</th><th>Dropoff</th><th>Landmark</th><th class="text-center">Students</th><th>Fee</th><th></th></tr>
                                        </thead>
                                        <tbody>
                                            @forelse($route->stops as $stop)
                                            <tr>
                                                <td class="text-muted">{{ $stop->stop_order }}</td>
                                                <td class="fw-semibold small">{{ $stop->name }}</td>
                                                <td class="small">{{ $stop->morning_pickup ?? '—' }}</td>
                                                <td class="small">{{ $stop->afternoon_dropoff ?? '—' }}</td>
                                                <td class="small text-muted">{{ $stop->landmark ?? '—' }}</td>
                                                <td class="text-center"><span class="badge bg-secondary">{{ $stop->student_count }}</span></td>
                                                <td class="small">{{ $stop->stop_fee > 0 ? '$'.$stop->stop_fee : '—' }}</td>
                                                <td>
                                                    <form action="{{ route('transport.routes.stops.destroy', [$route->id, $stop->id]) }}" method="POST" onsubmit="return confirm('Remove stop?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-outline-danger btn-sm py-0 px-1"><i class="bi bi-trash" style="font-size:.7rem"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="8" class="text-center text-muted py-2 small">No stops defined.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Students on route --}}
                            <div class="card shadow-sm">
                                <div class="card-header fw-semibold d-flex justify-content-between">
                                    <span><i class="bi bi-people"></i> Students ({{ $route->active_students_count }})</span>
                                    <a href="{{ route('transport.students.create', ['route_id'=>$route->id]) }}" class="btn btn-primary btn-sm">+ Allocate</a>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light">
                                            <tr><th>Student</th><th>Stop</th><th>Direction</th><th>Fee</th><th>Status</th></tr>
                                        </thead>
                                        <tbody>
                                            @forelse($route->activeStudents as $alloc)
                                            <tr>
                                                <td class="small fw-semibold">{{ $alloc->student->first_name }} {{ $alloc->student->last_name }}</td>
                                                <td class="small text-muted">{{ $alloc->stop?->name ?? '—' }}</td>
                                                <td class="small">{{ ucfirst(str_replace('_',' ',$alloc->direction)) }}</td>
                                                <td class="small">${{ number_format($alloc->monthly_fee,2) }}</td>
                                                <td>{!! $alloc->status_badge !!}</td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="5" class="text-center text-muted py-2 small">No students allocated.</td></tr>
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
