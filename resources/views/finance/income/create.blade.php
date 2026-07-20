@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4" style="max-width:700px">

                    <h1 class="display-6 mb-1"><i class="bi bi-arrow-down-circle"></i> Record Income</h1>
                    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-3">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('finance.income.index') }}">Income</a></li>
                        <li class="breadcrumb-item active">New</li>
                    </ol></nav>

                    @include('session-messages')

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form method="POST" action="{{ route('finance.income.store') }}">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" value="{{ old('title') }}"
                                               class="form-control @error('title') is-invalid @enderror"
                                               placeholder="e.g. Anonymous Donation — Jan 2026" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                                        <select name="category" class="form-select" required>
                                            <option value="">— select —</option>
                                            @foreach($categories as $k => $v)
                                            <option value="{{ $k }}" @selected(old('category')==$k)>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" name="amount" step="0.01" min="0.01"
                                                   value="{{ old('amount') }}" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                                        <input type="date" name="income_date"
                                               value="{{ old('income_date', now()->toDateString()) }}"
                                               class="form-control" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Payment Method</label>
                                        <select name="payment_method" class="form-select">
                                            <option value="cash">Cash</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                            <option value="cheque">Cheque</option>
                                            <option value="online">Online</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Source</label>
                                        <input type="text" name="source" value="{{ old('source') }}"
                                               class="form-control" placeholder="Donor, grant body, etc.">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Reference No.</label>
                                        <input type="text" name="reference_no" value="{{ old('reference_no') }}"
                                               class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Link to Invoice</label>
                                        <select name="invoice_id" class="form-select">
                                            <option value="">— none —</option>
                                            @foreach($invoices as $inv)
                                            <option value="{{ $inv->id }}" @selected(old('invoice_id')==$inv->id)>
                                                {{ $inv->invoice_number }} — {{ optional($inv->student)->full_name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Description</label>
                                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                                    </div>
                                </div>
                                <hr class="my-4">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Entry</button>
                                    <a href="{{ route('finance.income.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
