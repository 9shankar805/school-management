@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2"><div class="col ps-4">
                <h1 class="display-6 mb-1"><i class="bi bi-pencil-square"></i> Edit Staff Member</h1>
                <nav aria-label="breadcrumb" class="mb-3"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li><li class="breadcrumb-item"><a href="{{ route('staff.index') }}">Staff</a></li><li class="breadcrumb-item active">Edit</li></ol></nav>
                @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

                <div class="card shadow-sm" style="max-width:720px">
                    <div class="card-body">
                        <form action="{{ route('staff.update', $staff->id) }}" method="POST">
                            @csrf @method('PUT')
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label><input type="text" name="first_name" class="form-control" value="{{ old('first_name', $staff->first_name) }}" required></div>
                                <div class="col-md-6"><label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label><input type="text" name="last_name" class="form-control" value="{{ old('last_name', $staff->last_name) }}" required></div>
                                <div class="col-md-6"><label class="form-label fw-semibold">Email <span class="text-danger">*</span></label><input type="email" name="email" class="form-control" value="{{ old('email', $staff->email) }}" required></div>
                                <div class="col-md-6"><label class="form-label fw-semibold">Phone</label><input type="text" name="phone" class="form-control" value="{{ old('phone', $staff->phone ?? '') }}"></div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Role</label>
                                    <select name="role" class="form-select">
                                        <option value="">— (keep current) —</option>
                                        @foreach($assignableRoles as $slug => $label)
                                            @continue(in_array($slug, ['student', 'parent']))
                                            <option value="{{ $slug }}" @selected(old('role', $staff->role ?? '') == $slug)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Current Spatie roles: {{ $staff->getRoleNames()->implode(', ') ?: '— none —' }}</small>
                                </div>
                                <div class="col-md-6"><label class="form-label fw-semibold">Address</label><input type="text" name="address" class="form-control" value="{{ old('address', $staff->address ?? '') }}"></div>
                                <div class="col-md-4"><label class="form-label fw-semibold">Address 2</label><input type="text" name="address2" class="form-control" value="{{ old('address2', $staff->address2 ?? '') }}"></div>
                                <div class="col-md-4"><label class="form-label fw-semibold">City</label><input type="text" name="city" class="form-control" value="{{ old('city', $staff->city ?? '') }}"></div>
                                <div class="col-md-4"><label class="form-label fw-semibold">ZIP / Postal</label><input type="text" name="zip" class="form-control" value="{{ old('zip', $staff->zip ?? '') }}"></div>
                                <div class="col-12 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Update</button>
                                    <a href="{{ route('staff.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div></div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
