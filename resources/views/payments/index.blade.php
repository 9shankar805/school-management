@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3"><i class="bi bi-currency-exchange"></i> Payments</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Payments</li>
                        </ol>
                    </nav>
                    <div class="mb-3">
                        <a href="{{ route('payments.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Create Invoice</a>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Title</th>
                                        <th>Amount</th>
                                        <th>Paid</th>
                                        <th>Status</th>
                                        <th>Due Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoices as $invoice)
                                    <tr>
                                        <td>{{ optional($invoice->student)->first_name }} {{ optional($invoice->student)->last_name }}</td>
                                        <td>{{ $invoice->title }}</td>
                                        <td>${{ number_format($invoice->amount, 2) }}</td>
                                        <td>${{ number_format($invoice->payments->sum('amount_paid'), 2) }}</td>
                                        <td>
                                            @if($invoice->status == 'paid')
                                                <span class="badge bg-success">Paid</span>
                                            @elseif($invoice->status == 'partial')
                                                <span class="badge bg-warning text-dark">Partial</span>
                                            @else
                                                <span class="badge bg-danger">Unpaid</span>
                                            @endif
                                        </td>
                                        <td>{{ $invoice->due_date }}</td>
                                        <td>
                                            @if($invoice->status != 'paid')
                                            <a href="{{ route('payments.pay', $invoice->id) }}" class="btn btn-sm btn-success">Pay</a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
