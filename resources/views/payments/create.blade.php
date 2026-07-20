@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4" style="max-width:760px">

                    <h1 class="display-6 mb-1"><i class="bi bi-receipt"></i> New Invoice</h1>
                    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-3">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('payments.index') }}">Invoices</a></li>
                        <li class="breadcrumb-item active">New</li>
                    </ol></nav>

                    @include('session-messages')

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form method="POST" action="{{ route('payments.store') }}">
                                @csrf

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Student <span class="text-danger">*</span></label>
                                        <select name="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
                                            <option value="">— select student —</option>
                                            @foreach($students as $s)
                                            <option value="{{ $s->id }}" @selected(old('student_id')==$s->id)>
                                                {{ $s->full_name }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('student_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Fee Structure</label>
                                        <select name="fee_structure_id" id="fee_structure_id"
                                                class="form-select @error('fee_structure_id') is-invalid @enderror">
                                            <option value="">— none / manual —</option>
                                            @foreach($structures as $s)
                                            <option value="{{ $s->id }}" @selected(old('fee_structure_id')==$s->id)>
                                                {{ $s->name }} (${{ number_format($s->total_amount, 2) }})
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-8">
                                        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" value="{{ old('title') }}"
                                               class="form-control @error('title') is-invalid @enderror"
                                               placeholder="e.g. Term 1 Fees — 2026" required>
                                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Academic Session</label>
                                        <select name="session_id" class="form-select">
                                            <option value="">— none —</option>
                                            @foreach($sessions as $sess)
                                            <option value="{{ $sess->id }}" @selected(old('session_id')==$sess->id)>
                                                {{ $sess->session_name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Gross Amount <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" name="amount" id="inp_amount" step="0.01" min="0"
                                                   value="{{ old('amount', 0) }}"
                                                   class="form-control @error('amount') is-invalid @enderror"
                                                   required oninput="calcNet()">
                                        </div>
                                        @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Discount</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" name="discount_amount" id="inp_discount" step="0.01" min="0"
                                                   value="{{ old('discount_amount', 0) }}"
                                                   class="form-control" oninput="calcNet()">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Tax / Levy</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" name="tax_amount" id="inp_tax" step="0.01" min="0"
                                                   value="{{ old('tax_amount', 0) }}"
                                                   class="form-control" oninput="calcNet()">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="alert alert-info d-flex align-items-center gap-2 py-2 mb-0">
                                            <i class="bi bi-calculator"></i>
                                            Net Amount: <strong id="net_display">$0.00</strong>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Due Date</label>
                                        <input type="date" name="due_date" value="{{ old('due_date') }}" class="form-control">
                                    </div>

                                    <div class="col-md-8">
                                        <label class="form-label fw-semibold">Notes</label>
                                        <input type="text" name="description" value="{{ old('description') }}"
                                               class="form-control" placeholder="Optional notes">
                                    </div>
                                </div>

                                <hr class="my-4">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Create Invoice
                                    </button>
                                    <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
function calcNet() {
    const amount   = parseFloat(document.getElementById('inp_amount').value)   || 0;
    const discount = parseFloat(document.getElementById('inp_discount').value) || 0;
    const tax      = parseFloat(document.getElementById('inp_tax').value)      || 0;
    const net      = Math.max(0, amount - discount + tax);
    document.getElementById('net_display').textContent = '$' + net.toFixed(2);
}

// Auto-fill amount from fee structure selection
document.getElementById('fee_structure_id').addEventListener('change', function () {
    const id = this.value;
    if (!id) return;
    fetch(`/finance/structures/${id}/items`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('inp_amount').value = data.total;
            calcNet();
        });
});

calcNet();
</script>
@endpush
