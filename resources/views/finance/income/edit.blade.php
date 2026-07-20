@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4" style="max-width:700px">

                    <h1 class="display-6 mb-1"><i class="bi bi-pencil-square"></i> Edit Income Entry</h1>
                    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-3">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('finance.income.index') }}">Income</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol></nav>

                    @include('session-messages')

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form method="POST" action="{{ route('finance.income.update', $entry->id) }}">
                                @csrf @method('PUT')
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label fw-semibold">Title</label>
                                        <input type="text" name="title" value="{{ old('title', $entry->title) }}"
                                               class="form-control" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Category</label>
                                        <select name="category" class="form-select" required>
                                            @foreach($categories as $k => $v)
                                            <option value="{{ $k }}" @selected(old('category', $entry->category)==$k)>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" name="amount" step="0.01" min="0.01"
                                                   value="{{ old('amount', $entry->amount) }}" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Date</label>
                                        <input type="date" name="income_date"
                                               value="{{ old('income_date', $entry->income_date->toDateString()) }}"
                                               class="form-control" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Payment Method</label>
                                        <select name="payment_method" class="form-select">
                                            @foreach(['cash'=>'Cash','bank_transfer'=>'Bank Transfer','cheque'=>'Cheque','online'=>'Online'] as $k => $v)
                                            <option value="{{ $k }}" @selected(old('payment_method', $entry->payment_method)==$k)>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Source</label>
                                        <input type="text" name="source" value="{{ old('source', $entry->source) }}" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Reference No.</label>
                                        <input type="text" name="reference_no"
                                               value="{{ old('reference_no', $entry->reference_no) }}" class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Description</label>
                                        <textarea name="description" class="form-control" rows="2">{{ old('description', $entry->description) }}</textarea>
                                    </div>
                                </div>
                                <hr class="my-4">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update</button>
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
