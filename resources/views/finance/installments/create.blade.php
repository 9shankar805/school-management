@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4" style="max-width:820px">

                    <h1 class="display-6 mb-1"><i class="bi bi-calendar-range"></i> New Installment Plan</h1>
                    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-3">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('finance.installments.index') }}">Installments</a></li>
                        <li class="breadcrumb-item active">New</li>
                    </ol></nav>

                    @include('session-messages')

                    <form method="POST" action="{{ route('finance.installments.store') }}">
                        @csrf
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white fw-semibold">Plan Details</div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Plan Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" value="{{ old('name') }}"
                                               class="form-control @error('name') is-invalid @enderror"
                                               placeholder="e.g. Term 1 — 4 Monthly Payments" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Student <span class="text-danger">*</span></label>
                                        <select name="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
                                            <option value="">— select student —</option>
                                            @foreach($students as $st)
                                            <option value="{{ $st->id }}" @selected(old('student_id')==$st->id)>{{ $st->full_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Total Amount <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" name="total_amount" step="0.01" min="0.01"
                                                   value="{{ old('total_amount') }}"
                                                   class="form-control @error('total_amount') is-invalid @enderror" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Late Fee (per overdue)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" name="late_fee" step="0.01" min="0"
                                                   value="{{ old('late_fee', 0) }}" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Fee Structure</label>
                                        <select name="fee_structure_id" class="form-select">
                                            <option value="">— none —</option>
                                            @foreach($structures as $s)
                                            <option value="{{ $s->id }}" @selected(old('fee_structure_id')==$s->id)>{{ $s->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                                <span class="fw-semibold">Installment Schedule</span>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addInstRow()">
                                    <i class="bi bi-plus"></i> Add Installment
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <table class="table align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Amount <span class="text-danger">*</span></th>
                                            <th>Due Date <span class="text-danger">*</span></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="instBody"></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Create Plan</button>
                            <a href="{{ route('finance.installments.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let instIdx = 0;
function addInstRow() {
    const tbody = document.getElementById('instBody');
    const tr    = document.createElement('tr');
    instIdx++;
    tr.innerHTML = `
        <td>${instIdx}</td>
        <td><div class="input-group input-group-sm">
            <span class="input-group-text">$</span>
            <input type="number" name="installments[${instIdx-1}][amount]" step="0.01" min="0.01" class="form-control" required>
        </div></td>
        <td><input type="date" name="installments[${instIdx-1}][due_date]" class="form-control form-control-sm" required></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove(); renumber()"><i class="bi bi-x"></i></button></td>`;
    tbody.appendChild(tr);
}
function renumber() {
    document.querySelectorAll('#instBody tr').forEach((tr, i) => { tr.cells[0].textContent = i+1; });
}
addInstRow();
</script>
@endpush
