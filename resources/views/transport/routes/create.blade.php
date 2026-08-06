@extends('layouts.app')
@section('title', 'Create Transport Route')

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
                    <i class="bi bi-map-fill"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Create Transport Route</h2>
                    <p class="text-slate-500 text-sm mb-0">Define a new school bus route, set departure schedules, and assign fleet personnel</p>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('transport.routes.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-arrow-left me-1"></i> Back to Routes
            </a>
        </div>
    </div>

    @include('session-messages')

    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-9">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
                    <div>
                        <h5 class="font-bold text-slate-800 mb-0">Route Configuration Form</h5>
                        <p class="text-xs text-slate-400 mb-0">Enter route details, schedule times, and standard monthly fare</p>
                    </div>
                    <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 font-bold d-flex align-items-center justify-content-center">
                        <i class="bi bi-signpost-2"></i>
                    </span>
                </div>

                <form action="{{ route('transport.routes.store') }}" method="POST">
                    @csrf
                    
                    <!-- Section 1: Route Basics -->
                    <h6 class="font-bold text-xs text-slate-700 uppercase tracking-wider mb-3 text-amber-700">
                        <i class="bi bi-geo-alt me-1"></i> 1. Route Definition & Assignments
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-8">
                            <label class="form-label font-semibold text-slate-700 text-xs">Route Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" class="form-control rounded-xl border-slate-200 py-2.5 text-sm font-bold text-slate-800" value="{{ old('name') }}" placeholder="e.g. Downtown - School Campus (Morning)" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label font-semibold text-slate-700 text-xs">Route Code</label>
                            <input type="text" name="code" class="form-control rounded-xl border-slate-200 py-2.5 text-sm font-mono text-slate-800 bg-slate-50" value="{{ old('code') }}" placeholder="e.g. RT-001">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-xs">Assign Fleet Vehicle</label>
                            <select name="vehicle_id" class="form-select rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800">
                                <option value="">-- Unassigned --</option>
                                @foreach($vehicles as $v)
                                    <option value="{{ $v->id }}" @selected(old('vehicle_id')==$v->id)>
                                        {{ $v->name }} (Reg: {{ $v->registration_number }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-xs">Assign Driver</label>
                            <select name="driver_id" class="form-select rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800">
                                <option value="">-- Unassigned --</option>
                                @foreach($drivers as $d)
                                    <option value="{{ $d->id }}" @selected(old('driver_id')==$d->id)>{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label font-semibold text-slate-700 text-xs">Total Route Distance (km)</label>
                            <div class="input-group">
                                <input type="number" name="distance_km" class="form-control rounded-s-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800" value="{{ old('distance_km') }}" step="0.01" min="0" placeholder="15.5">
                                <span class="input-group-text rounded-e-xl bg-slate-100 border-slate-200 text-xs font-semibold">km</span>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4 border-slate-100">

                    <!-- Section 2: Route Timings -->
                    <h6 class="font-bold text-xs text-slate-700 uppercase tracking-wider mb-3 text-amber-700">
                        <i class="bi bi-clock-history me-1"></i> 2. Schedule Timings
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label font-semibold text-slate-700 text-xs">Morning Departure (Pickup)</label>
                            <input type="time" name="morning_departure" class="form-control rounded-xl border-slate-200 py-2 text-sm text-slate-800" value="{{ old('morning_departure') }}">
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label font-semibold text-slate-700 text-xs">Morning Arrival (Campus)</label>
                            <input type="time" name="morning_arrival" class="form-control rounded-xl border-slate-200 py-2 text-sm text-slate-800" value="{{ old('morning_arrival') }}">
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label font-semibold text-slate-700 text-xs">Afternoon Departure (Campus)</label>
                            <input type="time" name="afternoon_departure" class="form-control rounded-xl border-slate-200 py-2 text-sm text-slate-800" value="{{ old('afternoon_departure') }}">
                        </div>
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label font-semibold text-slate-700 text-xs">Afternoon Arrival (Dropoff)</label>
                            <input type="time" name="afternoon_arrival" class="form-control rounded-xl border-slate-200 py-2 text-sm text-slate-800" value="{{ old('afternoon_arrival') }}">
                        </div>
                    </div>

                    <hr class="my-4 border-slate-100">

                    <!-- Section 3: Financials & Details -->
                    <h6 class="font-bold text-xs text-slate-700 uppercase tracking-wider mb-3 text-amber-700">
                        <i class="bi bi-cash-stack me-1"></i> 3. Standard Fare & Description
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-xs">Standard Monthly Fee <span class="text-rose-500">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text rounded-s-xl bg-slate-100 border-slate-200 text-xs font-semibold">Rs.</span>
                                <input type="number" name="monthly_fee" class="form-control rounded-e-xl border-slate-200 py-2.5 text-sm font-bold text-slate-800" value="{{ old('monthly_fee', 0) }}" step="0.01" min="0" required>
                            </div>
                            <small class="text-slate-400 text-[11px] mt-1 d-block">Base rate billed to allocated students</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label font-semibold text-slate-700 text-xs">Route Description / Landmarks</label>
                            <textarea name="description" class="form-control rounded-xl border-slate-200 p-3 text-sm" rows="3" placeholder="Major stops, landmarks, specific road directions...">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-t border-slate-100">
                        <a href="{{ route('transport.routes.index') }}" class="btn btn-light border border-slate-200 text-slate-600 font-semibold px-4 py-2.5 rounded-xl text-xs">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-amber bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white font-bold px-6 py-2.5 rounded-xl shadow-md shadow-amber-500/20 transition">
                            <i class="bi bi-check-circle me-1.5"></i> Create Route
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
