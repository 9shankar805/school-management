@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-pencil-square"></i> Edit Member</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('library.members.index') }}">Members</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </nav>

                    @if($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif

                    <div class="card shadow-sm" style="max-width:600px">
                        <div class="card-header fw-semibold">
                            {{ $member->user->first_name }} {{ $member->user->last_name }}
                            <code class="ms-2 small">{{ $member->card_number }}</code>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('library.members.update', $member->id) }}" method="POST">
                                @csrf @method('PUT')
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Member Type <span class="text-danger">*</span></label>
                                    <select name="member_type" class="form-select" required>
                                        <option value="student" @selected(old('member_type', $member->member_type) === 'student')>Student</option>
                                        <option value="teacher" @selected(old('member_type', $member->member_type) === 'teacher')>Teacher</option>
                                        <option value="staff"   @selected(old('member_type', $member->member_type) === 'staff')>Staff</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select" required>
                                        <option value="active"    @selected(old('status', $member->status) === 'active')>Active</option>
                                        <option value="suspended" @selected(old('status', $member->status) === 'suspended')>Suspended</option>
                                        <option value="expired"   @selected(old('status', $member->status) === 'expired')>Expired</option>
                                    </select>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Membership Start</label>
                                        <input type="date" name="membership_start" class="form-control" value="{{ old('membership_start', $member->membership_start->format('Y-m-d')) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Membership End</label>
                                        <input type="date" name="membership_end" class="form-control" value="{{ old('membership_end', $member->membership_end?->format('Y-m-d')) }}">
                                    </div>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Max Books Allowed</label>
                                        <input type="number" name="max_books" class="form-control" value="{{ old('max_books', $member->max_books) }}" min="1" max="20">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Loan Period (days)</label>
                                        <input type="number" name="loan_days" class="form-control" value="{{ old('loan_days', $member->loan_days) }}" min="1" max="90">
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Update</button>
                                    <a href="{{ route('library.members.show', $member->id) }}" class="btn btn-outline-secondary">Cancel</a>
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
