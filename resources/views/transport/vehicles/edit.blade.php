@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-pencil-square"></i> Edit Vehicle</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('transport.vehicles.index') }}">Vehicles</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </nav>

                    @if($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form action="{{ route('transport.vehicles.update', $vehicle->id) }}" method="POST">
                                @csrf @method('PUT')
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Vehicle Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name', $vehicle->name) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Registration Number <span class="text-danger">*</span></label>
                                        <input type="text" name="registration_number" class="form-control" value="{{ old('registration_number', $vehicle->registration_number) }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                                        <select name="type" class="form-select" required>
                                            @foreach(['bus'=>'Bus','van'=>'Van','minibus'=>'Minibus','car'=>'Car'] as $val=>$lbl)
                                                <option value="{{ $val }}" @selected(old('type',$vehicle->type)===$val)>{{ $lbl }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Fuel Type <span class="text-danger">*</span></label>
                                        <select name="fuel_type" class="form-select" required>
                                            @foreach(['diesel'=>'Diesel','petrol'=>'Petrol','cng'=>'CNG','electric'=>'Electric'] as $val=>$lbl)
                                                <option value="{{ $val }}" @selected(old('fuel_type',$vehicle->fuel_type)===$val)>{{ $lbl }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Capacity <span class="text-danger">*</span></label>
                                        <input type="number" name="capacity" class="form-control" value="{{ old('capacity', $vehicle->capacity) }}" min="1" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="active"      @selected(old('status',$vehicle->status)==='active')>Active</option>
                                            <option value="maintenance" @selected(old('status',$vehicle->status)==='maintenance')>Maintenance</option>
                                            <option value="retired"     @selected(old('status',$vehicle->status)==='retired')>Retired</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Color</label>
                                        <input type="text" name="color" class="form-control" value="{{ old('color', $vehicle->color) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Make</label>
                                        <input type="text" name="make" class="form-control" value="{{ old('make', $vehicle->make) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Model</label>
                                        <input type="text" name="model" class="form-control" value="{{ old('model', $vehicle->model) }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Year</label>
                                        <input type="number" name="year" class="form-control" value="{{ old('year', $vehicle->year) }}" min="1990" max="{{ date('Y') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">GPS Device ID</label>
                                        <input type="text" name="gps_device_id" class="form-control" value="{{ old('gps_device_id', $vehicle->gps_device_id) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Insurance Expiry</label>
                                        <input type="date" name="insurance_expiry" class="form-control" value="{{ old('insurance_expiry', $vehicle->insurance_expiry?->format('Y-m-d')) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Fitness Expiry</label>
                                        <input type="date" name="fitness_expiry" class="form-control" value="{{ old('fitness_expiry', $vehicle->fitness_expiry?->format('Y-m-d')) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Permit Expiry</label>
                                        <input type="date" name="permit_expiry" class="form-control" value="{{ old('permit_expiry', $vehicle->permit_expiry?->format('Y-m-d')) }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Notes</label>
                                        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $vehicle->notes) }}</textarea>
                                    </div>
                                    <div class="col-12 d-flex gap-2">
                                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Update</button>
                                        <a href="{{ route('transport.vehicles.show', $vehicle->id) }}" class="btn btn-outline-secondary">Cancel</a>
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
