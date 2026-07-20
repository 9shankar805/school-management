@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4" style="max-width:700px">

                    <h1 class="display-6 mb-1"><i class="bi bi-cash-coin"></i> Record Payment</h1>
                    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-3">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('payments.index') }}">Invoices</a></li>
                        <li class="breadcrumb-item active">Pay</li>
                    </ol></nav>

                    @include('session-messages')

                    {{-- Invoice summary --}}
                    <div class="card mb-4 border-0 bg-light rounded-3">
                        <div class="card-body">
                            <div class="row g-2 text-sm">
                                <div class="col-6 col-md-3">
                                    <p class="text-muted mb-0 small">Invoice #</p>
                                    <strong class="font-monospace">{{ $invoice->invoice_number }}</strong>
                                </div>
                                <div class="col-6 col-md-3">
                                    <p class="text-muted mb-0 small">Student</p>
                                    <strong>{{ optional($invoice->student)->full_name }}</strong>
                                </div>
                                <div class="col-6 col-md-3">
                                    <p class="text-muted mb-0 small">Net Amount</p>
                                    <strong>${{ number_format($invoice->net_amount ?: $invoice->amount, 2) }}</strong>
                                </div>
                                <div class="col-6 col-md-3">
                                    <p class="text-muted mb-0 small">Balance Due</p>
                                    <strong class="text-danger">${{ number_format($invoice->balance_due, 2) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Payment history --}}
                    @if($invoice->payments->count())
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-white fw-semibold">Payment History</div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Receipt #</th><th>Date</th>
                                        <th class="text-end">Amount</th><th>Method</th><th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoice->payments as $p)
                                    <tr>
                                        <td><small class="font-monospace">{{ $p->receipt_number }}</small></td>
                                        <td>{{ $p->payment_date->format('d M Y') }}</td>
                                        <td class="text-end">${{ number_format($p->amount_paid, 2) }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $p->payment_method ?? '')) }}</td>
                                        <td>
                                            <a href="{{ route('payments.receipt', $p->id) }}"
                                               class="btn btn-xs btn-outline-secondary btn-sm" target="_blank">
                                                <i class="bi bi-printer"></i> Receipt
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    @if($invoice->status !== 'paid')
                    <div class="card shadow-sm">
                        <div class="card-header bg-white fw-semibold">Record New Payment</div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('payments.process', $invoice->id) }}">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <label class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" name="amount_paid" step="0.01" min="0.01"
                                                   value="{{ old('amount_paid', number_format($invoice->balance_due, 2)) }}"
                                                   class="form-control @error('amount_paid') is-invalid @enderror" required>
                                        </div>
                                        @error('amount_paid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                                        <input type="date" name="payment_date"
                                               value="{{ old('payment_date', now()->toDateString()) }}"
                                               class="form-control @error('payment_date') is-invalid @enderror" required>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Method <span class="text-danger">*</span></label>
                                        <select name="payment_method" class="form-select" required>
                                            @foreach($paymentMethods as $key => $label)
                                            <option value="{{ $key }}" @selected(old('payment_method')==$key)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6" id="bankFields">
                                        <label class="form-label fw-semibold">Bank Name</label>
                                        <input type="text" name="bank_name" value="{{ old('bank_name') }}"
                                               class="form-control" placeholder="e.g. First National Bank">
                                    </div>

                                    <div class="col-md-6" id="chequeField">
                                        <label class="form-label fw-semibold">Cheque / Transaction Ref</label>
                                        <input type="text" name="transaction_reference"
                                               value="{{ old('transaction_reference') }}"
                                               class="form-control" placeholder="Reference number">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Notes</label>
                                        <input type="text" name="notes" value="{{ old('notes') }}"
                                               class="form-control" placeholder="Optional">
                                    </div>
                                </div>

                                <hr class="my-3">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-cash-coin"></i> Record &amp; Print Receipt
                                    </button>
                                    <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i> This invoice is fully paid.</div>
                    @endif

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
