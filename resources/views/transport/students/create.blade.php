@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-1"><i class="bi bi-plus-circle"></i> Allocate Student to Route</h1>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('transport.students.index') }}">Students</a></li>
                            <li class="breadcrumb-item active">Allocate</li>
                        </ol>
                    </nav>
                    @if($errors->any())
                        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="card shadow-sm" style="max-width:650px">
                        <div class="card-body">
                            <form action="{{ route('transport.students.store') }}" method="POST" id="allocForm">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Student <span class="text-danger">*</span></label>
                                    <select name="student_id" class="form-select" required>
                                        <option value="">— Select student —</option>
                                        @foreach($students as $s)
                                            <option value="{{ $s->id }}" @selected(old('student_id', $student?->id) == $s->id)>
                                                {{ $s->first_name }} {{ $s->last_name }} ({{ $s->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Route <span class="text-danger">*</span></label>
                                    <select name="route_id" id="routeSelect" class="form-select" required>
                                        <option value="">— Select route —</option>
                                        @foreach($routes as $r)
                                            <option value="{{ $r->id }}"
                                                    data-fee="{{ $r->monthly_fee }}"
                                                    @selected(old('route_id', request('route_id')) == $r->id)>
                                                {{ $r->name }} — ${{ number_format($r->monthly_fee,2) }}/mo
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Pickup Stop</label>
                                    <select name="stop_id" id="stopSelect" class="form-select">
                                        <option value="">— Select route first —</option>
                                    </select>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Direction <span class="text-danger">*</span></label>
                                        <select name="direction" class="form-select" required>
                                            <option value="both"         @selected(old('direction')==='both')>Both (Pickup + Dropoff)</option>
                                            <option value="pickup_only"  @selected(old('direction')==='pickup_only')>Pickup Only</option>
                                            <option value="dropoff_only" @selected(old('direction')==='dropoff_only')>Dropoff Only</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Monthly Fee ($) <span class="text-danger">*</span></label>
                                        <input type="number" name="monthly_fee" id="feeInput" class="form-control" value="{{ old('monthly_fee', 0) }}" step="0.01" min="0" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                                        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', date('Y-m-d')) }}" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Notes</label>
                                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Allocate</button>
                                    <a href="{{ route('transport.students.index') }}" class="btn btn-outline-secondary">Cancel</a>
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

@push('scripts')
<script>
document.getElementById('routeSelect').addEventListener('change', function() {
    const routeId = this.value;
    const stopSel = document.getElementById('stopSelect');
    const feeInput = document.getElementById('feeInput');

    // Update fee from route
    const opt = this.options[this.selectedIndex];
    if (opt.dataset.fee) feeInput.value = opt.dataset.fee;

    stopSel.innerHTML = '<option value="">Loading…</option>';
    if (!routeId) { stopSel.innerHTML = '<option value="">— Select route first —</option>'; return; }

    fetch('{{ url("transport/routes") }}/' + routeId + '/stops')
        .then(r => r.json())
        .then(stops => {
            stopSel.innerHTML = '<option value="">— No specific stop —</option>';
            stops.forEach(s => {
                const o = document.createElement('option');
                o.value = s.id;
                o.textContent = s.stop_order + '. ' + s.name + (s.morning_pickup ? ' (' + s.morning_pickup + ')' : '');
                stopSel.appendChild(o);
            });
        });
});

// Trigger on load if route pre-selected
const sel = document.getElementById('routeSelect');
if (sel.value) sel.dispatchEvent(new Event('change'));
</script>
@endpush
