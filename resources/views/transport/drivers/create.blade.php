@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-plus-circle"></i> Add Driver</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('transport.drivers.index') }}">Drivers</a></li>
                            <li class="breadcrumb-item active">Add</li>
                        </ol>
                    </nav>
                    @if($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif
                    <div class="card shadow-sm" style="max-width:750px">
                        <div class="card-body">
                            <form action="{{ route('transport.drivers.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-6"><label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old('name') }}" required></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Employee ID</label><input type="text" name="employee_id" class="form-control" value="{{ old('employee_id') }}"></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold">Phone</label><input type="text" name="phone" class="form-control" value="{{ old('phone') }}"></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold">Email</label><input type="email" name="email" class="form-control" value="{{ old('email') }}"></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold">Date of Birth</label><input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}"></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold">Joining Date</label><input type="date" name="joining_date" class="form-control" value="{{ old('joining_date') }}"></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold">Salary ($)</label><input type="number" name="salary" class="form-control" value="{{ old('salary') }}" step="0.01" min="0"></div>
                                    <div class="col-12"><label class="form-label fw-semibold">Address</label><input type="text" name="address" class="form-control" value="{{ old('address') }}"></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold">License Number <span class="text-danger">*</span></label><input type="text" name="license_number" class="form-control" value="{{ old('license_number') }}" required></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold">License Type</label><input type="text" name="license_type" class="form-control" value="{{ old('license_type') }}" placeholder="HMV, LMV…"></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold">License Expiry</label><input type="date" name="license_expiry" class="form-control" value="{{ old('license_expiry') }}"></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold">National ID</label><input type="text" name="national_id" class="form-control" value="{{ old('national_id') }}"></div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Assign Vehicle</label>
                                        <select name="current_vehicle_id" class="form-select">
                                            <option value="">— None —</option>
                                            @foreach($vehicles as $v)
                                                <option value="{{ $v->id }}" @selected(old('current_vehicle_id') == $v->id)>{{ $v->name }} ({{ $v->registration_number }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4"><label class="form-label fw-semibold">Photo</label><input type="file" name="photo" class="form-control" accept="image/*"></div>
                                    <div class="col-12"><label class="form-label fw-semibold">Notes</label><textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea></div>
                                    <div class="col-12 d-flex gap-2">
                                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Driver</button>
                                        <a href="{{ route('transport.drivers.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
