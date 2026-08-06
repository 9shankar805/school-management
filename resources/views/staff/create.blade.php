@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3"><i class="bi bi-person-lines-fill"></i> Add Staff</h1>
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('staff.store') }}" method="POST">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                                        <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label fw-semibold">Phone</label>
                                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                                        <input type="password" name="password" class="form-control" minlength="8" required>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                                        <input type="password" name="password_confirmation" class="form-control" minlength="8" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                                        <select name="role" class="form-select" required>
                                            <option value="" selected disabled>— Select a role —</option>
                                            @foreach($assignableRoles as $slug => $label)
                                                {{-- Don't pre-populate student/parent here — those flows are managed via
                                                     Add Student form (Admission / UserController::createStudent). --}}
                                                @continue(in_array($slug, ['student', 'parent', 'teacher', 'admin', 'class-teacher']))
                                                <option value="{{ $slug }}" @selected(old('role') == $slug)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">
                                            Teachers are added via the <strong>Add Teacher</strong> page,
                                            Students via <strong>Student Admission</strong>, and Admins via the Super Admin.
                                        </small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Gender</label>
                                        <select name="gender" class="form-select">
                                            <option value="">— Select —</option>
                                            <option value="Male" @selected(old('gender') == 'Male')>Male</option>
                                            <option value="Female" @selected(old('gender') == 'Female')>Female</option>
                                            <option value="Other" @selected(old('gender') == 'Other')>Other</option>
                                        </select>
                                    </div>
                                    <div class="col-12 mb-2">
                                        <label class="form-label fw-semibold">Address</label>
                                        <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                                    </div>
                                    <div class="col-12">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Address 2</label>
                                                <input type="text" name="address2" class="form-control" value="{{ old('address2') }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">City</label>
                                                <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">ZIP / Postal Code</label>
                                                <input type="text" name="zip" class="form-control" value="{{ old('zip') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Staff</button>
                                    <a href="{{ route('staff.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
