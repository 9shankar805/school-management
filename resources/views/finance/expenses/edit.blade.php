@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4" style="max-width:700px">

                    <h1 class="display-6 mb-1"><i class="bi bi-pencil-square"></i> Edit Expense</h1>
                    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-3">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('finance.expenses.index') }}">Expenses</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol></nav>

                    @include('session-messages')

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form method="POST" action="{{ route('finance.expenses.update', $expense->id) }}" enctype="multipart/form-data">
                                @csrf @method('PUT')
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label fw-semibold">Title</label>
                                        <input type="text" name="title" value="{{ old('title', $expense->title) }}"
                                               class="form-control" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Category</label>
                                        <select name="category" class="form-select" required>
                                            @foreach($categories as $k => $v)
                                            <option value="{{ $k }}" @selected(old('category', $expense->category)==$k)>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" name="amount" step="0.01" min="0.01"
                                                   value="{{ old('amount', $expense->amount) }}" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Date</label>
                                        <input type="date" name="expense_date"
                                               value="{{ old('expense_date', $expense->expense_date->toDateString()) }}"
                                               class="form-control" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Payment Method</label>
                                        <select name="payment_method" class="form-select" required>
                                            @foreach($paymentMethods as $k => $v)
                                            <option value="{{ $k }}" @selected(old('payment_method', $expense->payment_method)==$k)>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Vendor</label>
                                        <input type="text" name="vendor"
                                               value="{{ old('vendor', $expense->vendor) }}" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Reference No.</label>
                                        <input type="text" name="reference_no"
                                               value="{{ old('reference_no', $expense->reference_no) }}" class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Description</label>
                                        <textarea name="description" class="form-control" rows="2">{{ old('description', $expense->description) }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Replace Receipt</label>
                                        <input type="file" name="receipt" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                        @if($expense->receipt_path)
                                        <div class="form-text">Current: <a href="{{ asset('storage/' . $expense->receipt_path) }}" target="_blank">View Receipt</a></div>
                                        @endif
                                    </div>
                                </div>
                                <hr class="my-4">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update</button>
                                    <a href="{{ route('finance.expenses.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
