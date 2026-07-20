@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-person-plus"></i> Enroll Library Member</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('library.members.index') }}">Members</a></li>
                            <li class="breadcrumb-item active">Enroll</li>
                        </ol>
                    </nav>

                    @if($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif

                    <div class="card shadow-sm" style="max-width:600px">
                        <div class="card-body">
                            <form action="{{ route('library.members.store') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">User <span class="text-danger">*</span></label>
                                    <select name="user_id" class="form-select" required>
                                        <option value="">— Select a user —</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>
                                                {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Member Type <span class="text-danger">*</span></label>
                                    <select name="member_type" class="form-select" required>
                                        <option value="student" @selected(old('member_type') === 'student')>Student</option>
                                        <option value="teacher" @selected(old('member_type') === 'teacher')>Teacher</option>
                                        <option value="staff"   @selected(old('member_type') === 'staff')>Staff</option>
                                    </select>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Membership Start <span class="text-danger">*</span></label>
                                        <input type="date" name="membership_start" class="form-control" value="{{ old('membership_start', date('Y-m-d')) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Membership End</label>
                                        <input type="date" name="membership_end" class="form-control" value="{{ old('membership_end') }}">
                                        <div class="form-text">Leave blank for indefinite.</div>
                                    </div>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Max Books Allowed</label>
                                        <input type="number" name="max_books" class="form-control" value="{{ old('max_books', 3) }}" min="1" max="20">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Loan Period (days)</label>
                                        <input type="number" name="loan_days" class="form-control" value="{{ old('loan_days', 14) }}" min="1" max="90">
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Enroll</button>
                                    <a href="{{ route('library.members.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
