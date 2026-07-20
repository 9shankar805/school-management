@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-plus-circle"></i> Create Route</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('transport.routes.index') }}">Routes</a></li>
                            <li class="breadcrumb-item active">Create</li>
                        </ol>
                    </nav>
                    @if($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif
                    <div class="card shadow-sm" style="max-width:750px">
                        <div class="card-body">
                            <form action="{{ route('transport.routes.store') }}" method="POST">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-8"><label class="form-label fw-semibold">Route Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old('name') }}" required></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold">Code</label><input type="text" name="code" class="form-control" value="{{ old('code') }}" placeholder="RT-001"></div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Vehicle</label>
                                        <select name="vehicle_id" class="form-select">
                                            <option value="">— None —</option>
                                            @foreach($vehicles as $v)
                                                <option value="{{ $v->id }}" @selected(old('vehicle_id')==$v->id)>{{ $v->name }} ({{ $v->registration_number }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Driver</label>
                                        <select name="driver_id" class="form-select">
                                            <option value="">— None —</option>
                                            @foreach($drivers as $d)
                                                <option value="{{ $d->id }}" @selected(old('driver_id')==$d->id)>{{ $d->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3"><label class="form-label fw-semibold">Morning Departure</label><input type="time" name="morning_departure" class="form-control" value="{{ old('morning_departure') }}"></div>
                                    <div class="col-md-3"><label class="form-label fw-semibold">Morning Arrival</label><input type="time" name="morning_arrival" class="form-control" value="{{ old('morning_arrival') }}"></div>
                                    <div class="col-md-3"><label class="form-label fw-semibold">Afternoon Departure</label><input type="time" name="afternoon_departure" class="form-control" value="{{ old('afternoon_departure') }}"></div>
                                    <div class="col-md-3"><label class="form-label fw-semibold">Afternoon Arrival</label><input type="time" name="afternoon_arrival" class="form-control" value="{{ old('afternoon_arrival') }}"></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold">Distance (km)</label><input type="number" name="distance_km" class="form-control" value="{{ old('distance_km') }}" step="0.01" min="0"></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold">Monthly Fee ($) <span class="text-danger">*</span></label><input type="number" name="monthly_fee" class="form-control" value="{{ old('monthly_fee', 0) }}" step="0.01" min="0" required></div>
                                    <div class="col-12"><label class="form-label fw-semibold">Description</label><textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea></div>
                                    <div class="col-12 d-flex gap-2">
                                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Create Route</button>
                                        <a href="{{ route('transport.routes.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
