@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-plus-circle"></i> Add Vehicle</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('transport.vehicles.index') }}">Vehicles</a></li>
                            <li class="breadcrumb-item active">Add</li>
                        </ol>
                    </nav>

                    @if($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form action="{{ route('transport.vehicles.store') }}" method="POST">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Vehicle Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Bus 01" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Registration Number <span class="text-danger">*</span></label>
                                        <input type="text" name="registration_number" class="form-control" value="{{ old('registration_number') }}" placeholder="e.g. ABC-1234" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                                        <select name="type" class="form-select" required>
                                            @foreach(['bus'=>'Bus','van'=>'Van','minibus'=>'Minibus','car'=>'Car'] as $val=>$lbl)
                                                <option value="{{ $val }}" @selected(old('type')===$val)>{{ $lbl }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Fuel Type <span class="text-danger">*</span></label>
                                        <select name="fuel_type" class="form-select" required>
                                            @foreach(['diesel'=>'Diesel','petrol'=>'Petrol','cng'=>'CNG','electric'=>'Electric'] as $val=>$lbl)
                                                <option value="{{ $val }}" @selected(old('fuel_type','diesel')===$val)>{{ $lbl }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Capacity <span class="text-danger">*</span></label>
                                        <input type="number" name="capacity" class="form-control" value="{{ old('capacity', 40) }}" min="1" max="200" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Color</label>
                                        <input type="text" name="color" class="form-control" value="{{ old('color') }}" placeholder="e.g. Yellow">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Make</label>
                                        <input type="text" name="make" class="form-control" value="{{ old('make') }}" placeholder="e.g. Toyota">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Model</label>
                                        <input type="text" name="model" class="form-control" value="{{ old('model') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Year</label>
                                        <input type="number" name="year" class="form-control" value="{{ old('year') }}" min="1990" max="{{ date('Y') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">GPS Device ID</label>
                                        <input type="text" name="gps_device_id" class="form-control" value="{{ old('gps_device_id') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Insurance Expiry</label>
                                        <input type="date" name="insurance_expiry" class="form-control" value="{{ old('insurance_expiry') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Fitness Certificate Expiry</label>
                                        <input type="date" name="fitness_expiry" class="form-control" value="{{ old('fitness_expiry') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Permit Expiry</label>
                                        <input type="date" name="permit_expiry" class="form-control" value="{{ old('permit_expiry') }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Notes</label>
                                        <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                                    </div>
                                    <div class="col-12 d-flex gap-2">
                                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Vehicle</button>
                                        <a href="{{ route('transport.vehicles.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
