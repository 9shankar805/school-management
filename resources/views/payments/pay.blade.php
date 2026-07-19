@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3"><i class="bi bi-currency-exchange"></i> Record Payment</h1>
                    <div class="card">
                        <div class="card-body">
                            <p><strong>Invoice Title:</strong> {{ $invoice->title }}</p>
                            <p><strong>Student:</strong> {{ optional($invoice->student)->first_name }} {{ optional($invoice->student)->last_name }}</p>
                            <p><strong>Total Amount:</strong> ${{ number_format($invoice->amount, 2) }}</p>
                            <p><strong>Amount Due:</strong> ${{ number_format($invoice->amount - $invoice->payments->sum('amount_paid'), 2) }}</p>

                            <form action="{{ route('payments.process', $invoice->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Payment Amount</label>
                                    <input type="number" step="0.01" name="amount_paid" class="form-control" max="{{ $invoice->amount - $invoice->payments->sum('amount_paid') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Payment Date</label>
                                    <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Payment Method</label>
                                    <select name="payment_method" class="form-select">
                                        <option value="Cash">Cash</option>
                                        <option value="Credit Card">Credit Card</option>
                                        <option value="Bank Transfer">Bank Transfer</option>
                                        <option value="Check">Check</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-success">Submit Payment</button>
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
