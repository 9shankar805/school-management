@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4" style="max-width:700px">

                    <h1 class="display-6 mb-1"><i class="bi bi-pencil-square"></i> Edit Invoice</h1>
                    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-3">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('payments.index') }}">Invoices</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol></nav>

                    @include('session-messages')

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form method="POST" action="{{ route('payments.update', $invoice->id) }}">
                                @csrf @method('PUT')
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title"
                                               value="{{ old('title', $invoice->title) }}"
                                               class="form-control @error('title') is-invalid @enderror" required>
                                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Gross Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" name="amount" step="0.01" min="0"
                                                   value="{{ old('amount', $invoice->amount) }}"
                                                   class="form-control" id="e_amount" oninput="calcNet2()" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Discount</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" name="discount_amount" step="0.01" min="0"
                                                   value="{{ old('discount_amount', $invoice->discount_amount) }}"
                                                   class="form-control" id="e_discount" oninput="calcNet2()">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Tax / Levy</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" name="tax_amount" step="0.01" min="0"
                                                   value="{{ old('tax_amount', $invoice->tax_amount) }}"
                                                   class="form-control" id="e_tax" oninput="calcNet2()">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="alert alert-info py-2 mb-0">
                                            Net Amount: <strong id="e_net_display">${{ number_format($invoice->net_amount, 2) }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label fw-semibold">Due Date</label>
                                        <input type="date" name="due_date"
                                               value="{{ old('due_date', $invoice->due_date?->toDateString()) }}"
                                               class="form-control">
                                    </div>
                                    <div class="col-md-7">
                                        <label class="form-label fw-semibold">Notes</label>
                                        <input type="text" name="description"
                                               value="{{ old('description', $invoice->description) }}"
                                               class="form-control">
                                    </div>
                                </div>
                                <hr class="my-4">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update Invoice</button>
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
function calcNet2() {
    const a = parseFloat(document.getElementById('e_amount').value)   || 0;
    const d = parseFloat(document.getElementById('e_discount').value) || 0;
    const t = parseFloat(document.getElementById('e_tax').value)      || 0;
    document.getElementById('e_net_display').textContent = '$' + Math.max(0, a - d + t).toFixed(2);
}
</script>
@endpush
