@extends('layouts.app')
@section('title', 'Transfer Student')

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,#6366f1,#3b82f6);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(99,102,241,.3);">
                <i class="bi bi-person-gear text-white fs-5"></i>
            </div>
            <div>
                <h2 class="h4 fw-bold text-slate-800 mb-0">Transfer: {{ $student->first_name }} {{ $student->last_name }}</h2>
                <p class="text-muted small mb-0">Select the type and destination — three scenarios supported</p>
            </div>
        </div>
        <a href="{{ route('student.transfers.index') }}" class="btn btn-light border fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Back to Transfers
        </a>
    </div>

    @include('session-messages')

    {{-- Scenario picker --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="scenario-card h-100 p-4 rounded-3 border border-2 text-center cursor-pointer" id="card-intraschool" onclick="pickScenario('intraschool')"
                 style="border-color:#e2e8f0!important;cursor:pointer;transition:all .2s;">
                <div class="mx-auto mb-3" style="width:50px;height:50px;border-radius:12px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-building fs-4 text-indigo-500" style="color:#6366f1;"></i>
                </div>
                <h6 class="fw-bold mb-1">Intra-School</h6>
                <p class="text-muted small mb-0">Move between classes or sections <strong>within this school</strong></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="scenario-card h-100 p-4 rounded-3 border border-2 text-center cursor-pointer" id="card-scenario1" onclick="pickScenario('scenario1')"
                 style="border-color:#e2e8f0!important;cursor:pointer;transition:all .2s;">
                <div class="mx-auto mb-3" style="width:50px;height:50px;border-radius:12px;background:#eff6ff;display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-diagram-3-fill fs-4" style="color:#3b82f6;"></i>
                </div>
                <h6 class="fw-bold mb-1">Scenario 1 — Same Platform</h6>
                <p class="text-muted small mb-0">Transfer to <strong>another school on this system</strong> — data moves automatically</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="scenario-card h-100 p-4 rounded-3 border border-2 text-center cursor-pointer" id="card-scenario2" onclick="pickScenario('scenario2')"
                 style="border-color:#e2e8f0!important;cursor:pointer;transition:all .2s;">
                <div class="mx-auto mb-3" style="width:50px;height:50px;border-radius:12px;background:#fdf2f8;display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-file-earmark-text-fill fs-4" style="color:#9333ea;"></i>
                </div>
                <h6 class="fw-bold mb-1">Scenario 2 — External School</h6>
                <p class="text-muted small mb-0">Transfer to an <strong>outside school</strong> — issues a TC certificate PDF</p>
            </div>
        </div>
    </div>

    {{-- FORM --}}
    <form action="{{ route('student.transfer.store', $student->id) }}" method="POST" id="transferForm">
        @csrf

        {{-- Hidden until scenario chosen --}}
        <div id="formBody" style="display:none;">
            <div class="bg-white rounded-3 border shadow-sm p-4 p-md-5">

                {{-- Current placement info --}}
                <div class="alert alert-light border mb-4 d-flex align-items-center gap-3">
                    <i class="bi bi-info-circle-fill text-indigo-500 fs-5" style="color:#6366f1;"></i>
                    <div>
                        <strong class="text-dark">Current Placement:</strong>
                        Class <strong>{{ $fromPromotion->schoolClass->class_name ?? 'Unassigned' }}</strong>
                        &nbsp;/&nbsp; Section <strong>{{ $fromPromotion->section->section_name ?? 'Unassigned' }}</strong>
                        <span class="text-muted small ms-2">Student ID: #{{ $student->id }}</span>
                    </div>
                </div>

                {{-- Scenario 1: platform school --}}
                <div id="fields-scenario1" style="display:none;">
                    <h6 class="fw-bold text-slate-700 mb-3"><i class="bi bi-diagram-3 me-2" style="color:#3b82f6;"></i>Destination School (on this platform)</h6>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Select Destination School <span class="text-danger">*</span></label>
                        <select name="to_school_id" id="to_school_id" class="form-select rounded-3">
                            <option value="">-- Select School --</option>
                            @foreach($platformSchools as $ps)
                                <option value="{{ $ps->id }}">{{ $ps->name }} ({{ $ps->district ?? $ps->city ?? 'Nepal' }})</option>
                            @endforeach
                        </select>
                        <div class="form-text">The student's account will be reassigned to this school automatically upon approval.</div>
                    </div>
                    <input type="hidden" name="transfer_type" id="transfer_type_s1" value="inter_school" disabled>
                </div>

                {{-- Scenario 2: external school --}}
                <div id="fields-scenario2" style="display:none;">
                    <h6 class="fw-bold text-slate-700 mb-3"><i class="bi bi-file-earmark-text me-2" style="color:#9333ea;"></i>External School Details</h6>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Destination School Name <span class="text-danger">*</span></label>
                        <input type="text" name="to_school" class="form-control rounded-3" placeholder="e.g. Shree Saraswati Secondary School, Kathmandu">
                    </div>
                    <input type="hidden" name="transfer_type" id="transfer_type_s2" value="inter_school" disabled>
                    <div class="alert alert-light border small text-muted">
                        <i class="bi bi-file-pdf-fill text-danger me-1"></i>
                        A Transfer Certificate (TC) PDF with a unique verification QR code will be issued automatically when this transfer is approved.
                    </div>
                </div>

                {{-- Intra-school --}}
                <div id="fields-intraschool" style="display:none;">
                    <h6 class="fw-bold text-slate-700 mb-3"><i class="bi bi-building me-2" style="color:#6366f1;"></i>Target Class & Section</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Transfer Type <span class="text-danger">*</span></label>
                            <select name="transfer_type" id="transfer_type_intra" class="form-select rounded-3" disabled>
                                <option value="inter_class">Class Transfer</option>
                                <option value="inter_section">Section Transfer</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Target Class</label>
                            <select name="to_class_id" class="form-select rounded-3">
                                <option value="">-- Same Class --</option>
                                @foreach($classes as $c)
                                    <option value="{{ $c->id }}">{{ $c->class_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Target Section</label>
                            <select name="to_section_id" class="form-select rounded-3">
                                <option value="">-- Same Section --</option>
                                @foreach($sections as $s)
                                    <option value="{{ $s->id }}">{{ $s->section_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Common fields --}}
                <hr class="my-4">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Transfer Date <span class="text-danger">*</span></label>
                        <input type="date" name="transfer_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Reason for Transfer</label>
                        <input type="text" name="reason" class="form-control rounded-3" placeholder="e.g. Parental relocation, academic preference...">
                    </div>
                </div>

                {{-- Documents to be handed over (Scenario 2 only) --}}
                <div id="docs-section" style="display:none;" class="mt-4 p-3 rounded-3 border bg-light">
                    <h6 class="fw-bold small text-muted mb-3"><i class="bi bi-paperclip me-1"></i>Documents Issued with Transfer Certificate</h6>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="carry_marksheet" value="1" id="cm1">
                            <label class="form-check-label small" for="cm1">Mark Sheet</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="carry_character" value="1" id="cm2">
                            <label class="form-check-label small" for="cm2">Character Certificate</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="carry_birth_cert" value="1" id="cm3">
                            <label class="form-check-label small" for="cm3">Birth Certificate Copy</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="carry_medical" value="1" id="cm4">
                            <label class="form-check-label small" for="cm4">Medical Records</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-4">
                    <a href="{{ route('student.transfers.index') }}" class="btn btn-light border fw-semibold px-4">Cancel</a>
                    <button type="submit" class="btn px-5 fw-bold text-white" style="background:linear-gradient(135deg,#6366f1,#3b82f6);border:none;border-radius:10px;">
                        <i class="bi bi-send-fill me-1"></i> Submit Transfer Request
                    </button>
                </div>

            </div>{{-- card --}}
        </div>{{-- formBody --}}
    </form>

</div>

<style>
.scenario-card.active { border-color: #6366f1 !important; background: #f5f3ff !important; }
.scenario-card:hover  { border-color: #818cf8 !important; background: #fafafe !important; }
</style>

<script>
function pickScenario(type) {
    document.querySelectorAll('.scenario-card').forEach(c => c.classList.remove('active'));
    document.querySelectorAll('#fields-scenario1,#fields-scenario2,#fields-intraschool').forEach(e => e.style.display='none');

    // Disable all hidden transfer_type inputs first
    document.querySelectorAll('[name="transfer_type"]').forEach(el => { el.disabled = true; });
    document.getElementById('to_school_id').disabled = true;

    document.getElementById('formBody').style.display = 'block';
    document.getElementById('docs-section').style.display = 'none';

    if (type === 'scenario1') {
        document.getElementById('card-scenario1').classList.add('active');
        document.getElementById('fields-scenario1').style.display = 'block';
        document.getElementById('transfer_type_s1').disabled = false;
        document.getElementById('to_school_id').disabled = false;
    } else if (type === 'scenario2') {
        document.getElementById('card-scenario2').classList.add('active');
        document.getElementById('fields-scenario2').style.display = 'block';
        document.getElementById('transfer_type_s2').disabled = false;
        document.getElementById('docs-section').style.display = 'block';
    } else {
        document.getElementById('card-intraschool').classList.add('active');
        document.getElementById('fields-intraschool').style.display = 'block';
        document.getElementById('transfer_type_intra').disabled = false;
    }

    document.getElementById('formBody').scrollIntoView({behavior:'smooth', block:'start'});
}
</script>
@endsection
