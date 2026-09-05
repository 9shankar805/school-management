@extends('layouts.app')
@section('title', 'Admit Incoming Transfer Student')

@section('content')
<div class="container-fluid px-4 py-4">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,#059669,#0d9488);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(5,150,105,.3);">
                <i class="bi bi-person-plus-fill text-white fs-5"></i>
            </div>
            <div>
                <h2 class="h4 fw-bold text-slate-800 mb-0">Admit Incoming Transfer Student <span class="badge bg-emerald-100 text-emerald-700 ms-2 fs-6" style="background:#d1fae5;color:#065f46;">Scenario 3</span></h2>
                <p class="text-muted small mb-0">Register a student who comes from another school with a Transfer Certificate</p>
            </div>
        </div>
        <a href="{{ route('student.transfers.index') }}" class="btn btn-light border fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Back to Transfers
        </a>
    </div>

    @include('session-messages')

    <form action="{{ route('student.transfer.incoming.store') }}" method="POST">
        @csrf
        <div class="row g-4">

            {{-- Previous school info --}}
            <div class="col-12">
                <div class="bg-white rounded-3 border shadow-sm p-4">
                    <h6 class="fw-bold mb-3 text-emerald-700" style="color:#065f46;"><i class="bi bi-building-x me-2"></i>Previous School Information</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Previous School Name <span class="text-danger">*</span></label>
                            <input type="text" name="previous_school_name" class="form-control rounded-3" required placeholder="e.g. Shree Bal Mandir Secondary School, Lalitpur" value="{{ old('previous_school_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Transfer Certificate Number</label>
                            <input type="text" name="previous_tc_number" class="form-control rounded-3" placeholder="e.g. TC-2025-0042" value="{{ old('previous_tc_number') }}">
                            <div class="form-text">Used for cross-reference and verification records</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Student personal info --}}
            <div class="col-12">
                <div class="bg-white rounded-3 border shadow-sm p-4">
                    <h6 class="fw-bold mb-3" style="color:#1e1b4b;"><i class="bi bi-person-vcard me-2"></i>Student Personal Details</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control rounded-3" required value="{{ old('first_name') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control rounded-3" required value="{{ old('last_name') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Email</label>
                            <input type="email" name="email" class="form-control rounded-3" placeholder="student@email.com" value="{{ old('email') }}">
                            <div class="form-text">Leave blank to auto-generate</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Gender</label>
                            <select name="gender" class="form-select rounded-3">
                                <option value="">-- Select --</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Date of Birth</label>
                            <input type="date" name="birthday" class="form-control rounded-3" value="{{ old('birthday') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Phone</label>
                            <input type="text" name="phone" class="form-control rounded-3" value="{{ old('phone') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Nationality</label>
                            <input type="text" name="nationality" class="form-control rounded-3" value="{{ old('nationality') ?? 'Nepali' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Address</label>
                            <input type="text" name="address" class="form-control rounded-3" value="{{ old('address') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Religion</label>
                            <input type="text" name="religion" class="form-control rounded-3" value="{{ old('religion') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Placement at this school --}}
            <div class="col-12">
                <div class="bg-white rounded-3 border shadow-sm p-4">
                    <h6 class="fw-bold mb-3" style="color:#1e1b4b;"><i class="bi bi-journal-bookmark me-2"></i>Enrollment at This School</h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Academic Session <span class="text-danger">*</span></label>
                            <select name="to_session_id" class="form-select rounded-3" required>
                                <option value="">-- Select Session --</option>
                                @foreach($sessions as $sess)
                                    <option value="{{ $sess->id }}" {{ old('to_session_id') == $sess->id ? 'selected' : '' }}>{{ $sess->session_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Assign to Class</label>
                            <select name="to_class_id" class="form-select rounded-3">
                                <option value="">-- Select Class --</option>
                                @foreach($classes as $c)
                                    <option value="{{ $c->id }}" {{ old('to_class_id') == $c->id ? 'selected' : '' }}>{{ $c->class_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Assign to Section</label>
                            <select name="to_section_id" class="form-select rounded-3">
                                <option value="">-- Select Section --</option>
                                @foreach($sections as $s)
                                    <option value="{{ $s->id }}" {{ old('to_section_id') == $s->id ? 'selected' : '' }}>{{ $s->section_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Joining Date <span class="text-danger">*</span></label>
                            <input type="date" name="joining_date" class="form-control rounded-3" value="{{ old('joining_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Reason / Notes</label>
                            <textarea name="reason" rows="2" class="form-control rounded-3" placeholder="e.g. Family relocated, preferred school...">{{ old('reason') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="d-flex justify-content-end gap-3 mt-4">
            <a href="{{ route('student.transfers.index') }}" class="btn btn-light border fw-semibold px-4">Cancel</a>
            <button type="submit" class="btn px-5 fw-bold text-white" style="background:linear-gradient(135deg,#059669,#0d9488);border:none;border-radius:10px;">
                <i class="bi bi-person-check-fill me-1"></i> Admit & Enroll Student
            </button>
        </div>
    </form>

</div>
@endsection
