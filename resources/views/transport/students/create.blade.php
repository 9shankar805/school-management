@extends('layouts.app')
@section('title', 'Allocate Student to Route')

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
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Allocate Student to Transport Route</h2>
                    <p class="text-slate-500 text-sm mb-0">Subscribe a student to a school bus route, assign pickup stops, and configure monthly fares</p>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('transport.students.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-3.5 py-2 rounded-xl text-xs hover:bg-slate-100 transition">
                <i class="bi bi-arrow-left me-1"></i> Back to Allocations
            </a>
        </div>
    </div>

    @include('session-messages')

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 col-xl-7">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
                    <div>
                        <h5 class="font-bold text-slate-800 mb-0">Student Bus Subscription Form</h5>
                        <p class="text-xs text-slate-400 mb-0">Fill in student details, transport route, and billing rate</p>
                    </div>
                    <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 font-bold d-flex align-items-center justify-content-center">
                        <i class="bi bi-bus-front"></i>
                    </span>
                </div>

                <form action="{{ route('transport.students.store') }}" method="POST" id="allocForm">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Select Student Passenger <span class="text-rose-500">*</span></label>
                        <select name="student_id" class="form-select rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800" required>
                            <option value="">-- Choose Student --</option>
                            @foreach($students as $s)
                                <option value="{{ $s->id }}" @selected(old('student_id', $student?->id) == $s->id)>
                                    {{ $s->first_name }} {{ $s->last_name }} ({{ $s->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Select Transport Route <span class="text-rose-500">*</span></label>
                        <select name="route_id" id="routeSelect" class="form-select rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800" required>
                            <option value="">-- Choose Bus Route --</option>
                            @foreach($routes as $r)
                                <option value="{{ $r->id }}"
                                        data-fee="{{ $r->monthly_fee }}"
                                        @selected(old('route_id', request('route_id')) == $r->id)>
                                    {{ $r->name }} — Standard Rate: Rs. {{ number_format($r->monthly_fee,2) }}/mo
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Designated Bus Stop</label>
                        <select name="stop_id" id="stopSelect" class="form-select rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800">
                            <option value="">Select transport route first...</option>
                        </select>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-4">
                            <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Travel Direction <span class="text-rose-500">*</span></label>
                            <select name="direction" class="form-select rounded-xl border-slate-200 py-2 text-sm font-medium text-slate-800" required>
                                <option value="both" @selected(old('direction')==='both')>Both Ways (Pickup + Drop)</option>
                                <option value="pickup_only" @selected(old('direction')==='pickup_only')>Pickup Only (Morning)</option>
                                <option value="dropoff_only" @selected(old('direction')==='dropoff_only')>Dropoff Only (Afternoon)</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Monthly Fee <span class="text-rose-500">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text rounded-s-xl bg-slate-100 border-slate-200 text-xs font-semibold">Rs.</span>
                                <input type="number" name="monthly_fee" id="feeInput" class="form-control rounded-e-xl border-slate-200 py-2 text-sm font-bold text-slate-800" value="{{ old('monthly_fee', 0) }}" step="0.01" min="0" required>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Subscription Start Date <span class="text-rose-500">*</span></label>
                            <input type="date" name="start_date" class="form-control rounded-xl border-slate-200 py-2 text-sm font-medium text-slate-800" value="{{ old('start_date', date('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Special Instructions / Remarks</label>
                        <textarea name="notes" class="form-control rounded-xl border-slate-200 p-2.5 text-sm" rows="2" placeholder="e.g. Needs front seat assistance, parent contact notes...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-t border-slate-100">
                        <a href="{{ route('transport.students.index') }}" class="btn btn-light border border-slate-200 text-slate-600 font-semibold px-4 py-2.5 rounded-xl text-xs">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-amber bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white font-bold px-6 py-2.5 rounded-xl shadow-md shadow-amber-500/20 transition">
                            <i class="bi bi-check-circle me-1.5"></i> Confirm Allocation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('routeSelect').addEventListener('change', function() {
    const routeId = this.value;
    const stopSel = document.getElementById('stopSelect');
    const feeInput = document.getElementById('feeInput');

    const opt = this.options[this.selectedIndex];
    if (opt.dataset.fee) feeInput.value = opt.dataset.fee;

    stopSel.innerHTML = '<option value="">Loading stops...</option>';
    if (!routeId) { stopSel.innerHTML = '<option value="">Select transport route first...</option>'; return; }

    fetch('{{ url("transport/routes") }}/' + routeId + '/stops')
        .then(r => r.json())
        .then(stops => {
            stopSel.innerHTML = '<option value="">-- No specific stop (Default Route Stop) --</option>';
            stops.forEach(s => {
                const o = document.createElement('option');
                o.value = s.id;
                o.textContent = s.stop_order + '. ' + s.name + (s.morning_pickup ? ' (' + s.morning_pickup + ')' : '');
                stopSel.appendChild(o);
            });
        });
});

const sel = document.getElementById('routeSelect');
if (sel.value) sel.dispatchEvent(new Event('change'));
</script>
@endpush
