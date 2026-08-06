@extends('layouts.app')
@section('title', 'Register New Transport Driver')

@section('content')
<style>
  .header-icon-box-transport {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%) !important;
    color: #ffffff !important;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
  }
  .header-icon-box-transport i {
    color: #ffffff !important;
    font-size: 22px !important;
    line-height: 1 !important;
    display: inline-block !important;
    opacity: 1 !important;
    visibility: visible !important;
  }
</style>

<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-3 mb-1">
                <div class="header-icon-box-transport">
                    <i class="bi bi-person-plus-fill"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Register New Transport Driver</h2>
                    <p class="text-slate-500 text-sm mb-0">Add driver profile, contact numbers, driving license compliance details, and assigned bus</p>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('transport.drivers.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-arrow-left me-1"></i> Back to Drivers
            </a>
        </div>
    </div>

    @include('session-messages')

    <div class="row justify-content-center">
        <div class="col-12 col-lg-9 col-xl-8">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
                    <div>
                        <h5 class="font-bold text-slate-800 mb-0">Driver Registration Form</h5>
                        <p class="text-xs text-slate-400 mb-0">Enter personal, contact, license, and vehicle assignment details</p>
                    </div>
                    <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 font-bold d-flex align-items-center justify-content-center">
                        <i class="bi bi-person-badge-fill"></i>
                    </span>
                </div>

                <form action="{{ route('transport.drivers.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Section 1: Personal & Contact Info -->
                    <h6 class="font-bold text-xs text-slate-700 uppercase tracking-wider mb-3 text-amber-700">
                        <i class="bi bi-person me-1"></i> 1. Personal & Contact Information
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-xs">Driver Full Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" class="form-control rounded-xl border-slate-200 py-2.5 text-sm font-bold text-slate-800" value="{{ old('name') }}" required placeholder="e.g. Hari Bahadur Gurung">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-xs">Employee ID / Code</label>
                            <input type="text" name="employee_id" class="form-control rounded-xl border-slate-200 py-2.5 text-sm text-slate-800" value="{{ old('employee_id') }}" placeholder="e.g. DRV-0104">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label font-semibold text-slate-700 text-xs">Phone Number</label>
                            <input type="text" name="phone" class="form-control rounded-xl border-slate-200 py-2 text-sm font-medium text-slate-800" value="{{ old('phone') }}" placeholder="98XXXXXXXX">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label font-semibold text-slate-700 text-xs">Email Address</label>
                            <input type="email" name="email" class="form-control rounded-xl border-slate-200 py-2 text-sm text-slate-800" value="{{ old('email') }}" placeholder="driver@school.edu">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label font-semibold text-slate-700 text-xs">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control rounded-xl border-slate-200 py-2 text-sm text-slate-800" value="{{ old('date_of_birth') }}">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-xs">Joining Date</label>
                            <input type="date" name="joining_date" class="form-control rounded-xl border-slate-200 py-2 text-sm text-slate-800" value="{{ old('joining_date') }}">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-xs">Monthly Salary</label>
                            <div class="input-group">
                                <span class="input-group-text rounded-s-xl bg-slate-100 border-slate-200 text-xs font-semibold">Rs.</span>
                                <input type="number" name="salary" class="form-control rounded-e-xl border-slate-200 py-2 text-sm font-bold text-slate-800" value="{{ old('salary') }}" step="0.01" min="0" placeholder="25000">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label font-semibold text-slate-700 text-xs">Residential Address</label>
                            <input type="text" name="address" class="form-control rounded-xl border-slate-200 py-2 text-sm text-slate-800" value="{{ old('address') }}" placeholder="Street address or municipality">
                        </div>
                    </div>

                    <hr class="my-4 border-slate-100">

                    <!-- Section 2: Driving License & Compliance -->
                    <h6 class="font-bold text-xs text-slate-700 uppercase tracking-wider mb-3 text-amber-700">
                        <i class="bi bi-card-checklist me-1"></i> 2. Driving License & Compliance
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-4">
                            <label class="form-label font-semibold text-slate-700 text-xs">Driving License No. <span class="text-rose-500">*</span></label>
                            <input type="text" name="license_number" class="form-control rounded-xl border-slate-200 py-2.5 text-sm font-bold text-slate-800" value="{{ old('license_number') }}" required placeholder="e.g. 01-06-0039201">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label font-semibold text-slate-700 text-xs">License Type / Category</label>
                            <input type="text" name="license_type" class="form-control rounded-xl border-slate-200 py-2.5 text-sm text-slate-800" value="{{ old('license_type') }}" placeholder="e.g. Heavy Bus (HMV), LMV">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label font-semibold text-slate-700 text-xs">License Expiry Date</label>
                            <input type="date" name="license_expiry" class="form-control rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800" value="{{ old('license_expiry') }}">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-xs">Citizenship / National ID No.</label>
                            <input type="text" name="national_id" class="form-control rounded-xl border-slate-200 py-2 text-sm text-slate-800" value="{{ old('national_id') }}">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-xs">Assign Fleet Vehicle</label>
                            <select name="current_vehicle_id" class="form-select rounded-xl border-slate-200 py-2 text-sm font-medium text-slate-800">
                                <option value="">-- Unassigned --</option>
                                @foreach($vehicles as $v)
                                    <option value="{{ $v->id }}" @selected(old('current_vehicle_id') == $v->id)>
                                        {{ $v->name }} (Reg: {{ $v->registration_number }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label font-semibold text-slate-700 text-xs">Driver Profile Photo</label>
                            <input type="file" name="photo" class="form-control rounded-xl border-slate-200 p-2 text-sm" accept="image/*">
                        </div>
                        <div class="col-12">
                            <label class="form-label font-semibold text-slate-700 text-xs">Additional Notes / Remarks</label>
                            <textarea name="notes" class="form-control rounded-xl border-slate-200 p-2.5 text-sm" rows="2" placeholder="e.g. Previous experience, emergency contact details...">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-t border-slate-100">
                        <a href="{{ route('transport.drivers.index') }}" class="btn btn-light border border-slate-200 text-slate-600 font-semibold px-4 py-2.5 rounded-xl text-xs">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-amber bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white font-bold px-6 py-2.5 rounded-xl shadow-md shadow-amber-500/20 transition">
                            <i class="bi bi-check-circle me-1.5"></i> Save Driver Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
