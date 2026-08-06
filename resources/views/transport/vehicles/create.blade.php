@extends('layouts.app')
@section('title', 'Register New Fleet Vehicle')

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
                    <i class="bi bi-bus-front-fill"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Register New Fleet Vehicle</h2>
                    <p class="text-slate-500 text-sm mb-0">Add a new transport vehicle, its specifications, and regulatory compliance dates</p>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('transport.vehicles.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-arrow-left me-1"></i> Back to Fleet
            </a>
        </div>
    </div>

    @include('session-messages')

    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-9">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
                    <div>
                        <h5 class="font-bold text-slate-800 mb-0">Vehicle Registration Form</h5>
                        <p class="text-xs text-slate-400 mb-0">Enter fleet details, capacity, and insurance expiry dates</p>
                    </div>
                    <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 font-bold d-flex align-items-center justify-content-center">
                        <i class="bi bi-card-heading"></i>
                    </span>
                </div>

                <form action="{{ route('transport.vehicles.store') }}" method="POST">
                    @csrf
                    
                    <!-- Section 1: General Info -->
                    <h6 class="font-bold text-xs text-slate-700 uppercase tracking-wider mb-3 text-amber-700">
                        <i class="bi bi-info-circle me-1"></i> 1. General Information
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-xs">Vehicle Name / Callsign <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" class="form-control rounded-xl border-slate-200 py-2.5 text-sm font-bold text-slate-800" value="{{ old('name') }}" placeholder="e.g. Yellow School Bus 01" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-xs">Registration / License Plate Number <span class="text-rose-500">*</span></label>
                            <input type="text" name="registration_number" class="form-control rounded-xl border-slate-200 py-2.5 text-sm font-mono text-slate-800 bg-slate-50" value="{{ old('registration_number') }}" placeholder="e.g. BA-1-KHA-1234" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-xs">Vehicle Category / Type <span class="text-rose-500">*</span></label>
                            <select name="type" class="form-select rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800" required>
                                @foreach(['bus'=>'Bus', 'van'=>'Van', 'minibus'=>'Minibus', 'car'=>'Car'] as $val=>$lbl)
                                    <option value="{{ $val }}" @selected(old('type')===$val)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-xs">Fuel Type <span class="text-rose-500">*</span></label>
                            <select name="fuel_type" class="form-select rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800" required>
                                @foreach(['diesel'=>'Diesel', 'petrol'=>'Petrol', 'cng'=>'CNG', 'electric'=>'Electric'] as $val=>$lbl)
                                    <option value="{{ $val }}" @selected(old('fuel_type','diesel')===$val)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr class="my-4 border-slate-100">

                    <!-- Section 2: Specifications -->
                    <h6 class="font-bold text-xs text-slate-700 uppercase tracking-wider mb-3 text-amber-700">
                        <i class="bi bi-gear-wide-connected me-1"></i> 2. Vehicle Specifications
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-4">
                            <label class="form-label font-semibold text-slate-700 text-xs">Passenger Seating Capacity <span class="text-rose-500">*</span></label>
                            <input type="number" name="capacity" class="form-control rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800" value="{{ old('capacity', 40) }}" min="1" max="200" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label font-semibold text-slate-700 text-xs">Vehicle Color</label>
                            <input type="text" name="color" class="form-control rounded-xl border-slate-200 py-2.5 text-sm text-slate-800" value="{{ old('color') }}" placeholder="e.g. Yellow">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label font-semibold text-slate-700 text-xs">Manufacturing Year</label>
                            <input type="number" name="year" class="form-control rounded-xl border-slate-200 py-2.5 text-sm text-slate-800" value="{{ old('year') }}" min="1990" max="{{ date('Y') }}" placeholder="YYYY">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label font-semibold text-slate-700 text-xs">Vehicle Make / Brand</label>
                            <input type="text" name="make" class="form-control rounded-xl border-slate-200 py-2.5 text-sm text-slate-800" value="{{ old('make') }}" placeholder="e.g. Tata, Ashok Leyland, Toyota">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label font-semibold text-slate-700 text-xs">Vehicle Model</label>
                            <input type="text" name="model" class="form-control rounded-xl border-slate-200 py-2.5 text-sm text-slate-800" value="{{ old('model') }}" placeholder="e.g. Starbus">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label font-semibold text-slate-700 text-xs">GPS Tracker Device ID</label>
                            <input type="text" name="gps_device_id" class="form-control rounded-xl border-slate-200 py-2.5 text-sm text-slate-800 font-mono" value="{{ old('gps_device_id') }}" placeholder="e.g. TK-89302">
                        </div>
                    </div>

                    <hr class="my-4 border-slate-100">

                    <!-- Section 3: Compliance & Expiry Dates -->
                    <h6 class="font-bold text-xs text-slate-700 uppercase tracking-wider mb-3 text-amber-700">
                        <i class="bi bi-shield-check me-1"></i> 3. Compliance & Validity
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-4">
                            <label class="form-label font-semibold text-slate-700 text-xs">Insurance Expiry Date</label>
                            <input type="date" name="insurance_expiry" class="form-control rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800" value="{{ old('insurance_expiry') }}">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label font-semibold text-slate-700 text-xs">Fitness Certificate Expiry Date</label>
                            <input type="date" name="fitness_expiry" class="form-control rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800" value="{{ old('fitness_expiry') }}">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label font-semibold text-slate-700 text-xs">Transport Permit Expiry Date</label>
                            <input type="date" name="permit_expiry" class="form-control rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800" value="{{ old('permit_expiry') }}">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label font-semibold text-slate-700 text-xs">Additional Notes</label>
                        <textarea name="notes" class="form-control rounded-xl border-slate-200 p-2.5 text-sm" rows="3" placeholder="Condition details, specific maintenance instructions...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-t border-slate-100">
                        <a href="{{ route('transport.vehicles.index') }}" class="btn btn-light border border-slate-200 text-slate-600 font-semibold px-4 py-2.5 rounded-xl text-xs">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-amber bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white font-bold px-6 py-2.5 rounded-xl shadow-md shadow-amber-500/20 transition">
                            <i class="bi bi-check-circle me-1.5"></i> Save Vehicle Registration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
