@extends('layouts.app')

@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">

    {{-- Page Header --}}
    <div class="mb-7 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight"><i class="bi bi-receipt-cutoff me-2 text-indigo-600"></i>My Fees & Invoices</h1>
            <p class="text-slate-400 text-sm mt-0.5">View your current fee statement, outstanding balance, and payment history</p>
        </div>
    </div>

    @include('session-messages')

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Outstanding</p>
                <p class="text-3xl font-bold {{ $totalOutstanding > 0 ? 'text-rose-600' : 'text-emerald-600' }} mt-1">
                    NPR {{ number_format($totalOutstanding, 2) }}
                </p>
            </div>
            <span class="w-12 h-12 rounded-xl {{ $totalOutstanding > 0 ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center text-xl font-bold">
                <i class="bi {{ $totalOutstanding > 0 ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill' }}"></i>
            </span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Paid Invoices</p>
                <p class="text-3xl font-bold text-slate-800 mt-1">
                    {{ $invoices->where('status', 'paid')->count() }} / {{ $invoices->count() }}
                </p>
            </div>
            <span class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-receipt"></i>
            </span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Recent Payments</p>
                <p class="text-3xl font-bold text-emerald-600 mt-1">
                    NPR {{ number_format($paymentHistory->sum('amount'), 2) }}
                </p>
            </div>
            <span class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-wallet2"></i>
            </span>
        </div>
    </div>

    {{-- Invoice List --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-sm font-semibold text-slate-800">Fee Invoices</h2>
        </div>
        @if($invoices->isEmpty())
            <div class="p-10 text-center text-slate-400 text-sm">
                <i class="bi bi-receipt text-4xl text-slate-200 block mb-2"></i>
                No invoices found for your account.
            </div>
        @else
            <div class="divide-y divide-slate-50">
                @foreach($invoices as $inv)
                @php
                    $paidAmount = $inv->payments->sum('amount');
                    $remaining  = max(0, $inv->amount - $paidAmount);
                    $badgeClass = match($inv->status) {
                        'paid'    => 'bg-emerald-100 text-emerald-700',
                        'partial' => 'bg-amber-100 text-amber-700',
                        default   => 'bg-rose-100 text-rose-700',
                    };
                @endphp
                <div class="px-5 py-4 flex items-center justify-between gap-4 flex-wrap hover:bg-slate-50/50 transition">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="font-semibold text-slate-800 text-sm">{{ $inv->title }}</p>
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold {{ $badgeClass }} capitalize">
                                {{ $inv->status }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">
                            Amount: <span class="font-medium text-slate-700">NPR {{ number_format($inv->amount, 2) }}</span>
                            @if($inv->due_date) · Due Date: <span class="font-medium text-slate-600">{{ \Carbon\Carbon::parse($inv->due_date)->format('M d, Y') }}</span> @endif
                            @if($remaining > 0 && $remaining < $inv->amount) · Remaining: <span class="text-rose-600 font-medium">NPR {{ number_format($remaining, 2) }}</span> @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        @if($inv->status !== 'paid')
                            <a href="{{ route('payments.pay', $inv->id) }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-xs transition inline-flex items-center gap-1.5">
                                <i class="bi bi-credit-card-fill"></i> Pay Now
                            </a>
                        @else
                            <span class="text-xs text-emerald-600 font-semibold flex items-center gap-1">
                                <i class="bi bi-check-circle-fill"></i> Paid
                            </span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Payment History --}}
    @if($paymentHistory->isNotEmpty())
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-sm font-semibold text-slate-800">Payment History</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50/80 text-xs text-slate-500 font-semibold uppercase tracking-wider">
                    <tr>
                        <th class="text-left px-5 py-3">Invoice</th>
                        <th class="text-center px-5 py-3">Amount Paid</th>
                        <th class="text-center px-5 py-3">Payment Date</th>
                        <th class="text-center px-5 py-3">Method</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-slate-700">
                    @foreach($paymentHistory as $pay)
                    <tr>
                        <td class="px-5 py-3.5 font-medium text-slate-800">{{ $pay->invoice?->title ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-center font-bold text-emerald-600">NPR {{ number_format($pay->amount, 2) }}</td>
                        <td class="px-5 py-3.5 text-center text-slate-500">{{ \Carbon\Carbon::parse($pay->payment_date)->format('M d, Y') }}</td>
                        <td class="px-5 py-3.5 text-center capitalize text-slate-500">{{ str_replace('_', ' ', $pay->payment_method ?? 'Cash') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
