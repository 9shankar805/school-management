@extends('layouts.app')
@section('title', 'Payment Receipt #' . $payment->receipt_number)

@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto max-w-2xl mx-auto">

    <nav class="text-xs text-slate-400 mb-5 flex items-center gap-1">
        <a href="{{ route('payments.index') }}" class="hover:text-indigo-600">Payments</a>
        <i class="bi bi-chevron-right text-xs text-slate-300"></i>
        <a href="{{ route('payments.show', $payment->invoice_id) }}" class="hover:text-indigo-600">Invoice</a>
        <i class="bi bi-chevron-right text-xs text-slate-300"></i>
        <span class="text-slate-500">Receipt</span>
    </nav>

    {{-- Receipt card --}}
    <div id="receipt-card" class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-lg overflow-hidden">

        {{-- Header band --}}
        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-8 py-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                @if($school?->logo)
                <img src="{{ $school->logo_url }}" class="h-12 w-12 rounded-xl object-contain bg-white p-1" alt="Logo">
                @else
                <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center text-white font-black text-lg">
                    {{ strtoupper(substr($school?->name ?? config('app.name'), 0, 2)) }}
                </div>
                @endif
                <div>
                    <p class="text-white font-black text-lg leading-tight">{{ $school?->name ?? config('app.name') }}</p>
                    @if($school?->address)
                    <p class="text-emerald-100 text-xs mt-0.5">{{ $school->address }}</p>
                    @endif
                </div>
            </div>
            <div class="text-right">
                <span class="text-emerald-100 text-xs font-semibold uppercase tracking-widest block">Receipt</span>
                <span class="text-white font-black text-xl">{{ $payment->receipt_number }}</span>
            </div>
        </div>

        {{-- Status banner --}}
        <div class="bg-emerald-50 dark:bg-emerald-900/20 border-b border-emerald-100 dark:border-emerald-800 px-8 py-3 flex items-center gap-2">
            <i class="bi bi-check-circle-fill text-emerald-600 text-lg"></i>
            <p class="text-emerald-800 dark:text-emerald-300 font-bold text-sm">Payment Successfully Recorded</p>
            <span class="ml-auto text-emerald-600 dark:text-emerald-400 text-xs font-semibold">
                {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}
            </span>
        </div>

        <div class="px-8 py-6 space-y-5">

            {{-- Student info --}}
            <div class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl">
                <img src="{{ $payment->invoice?->student?->avatar }}" class="w-12 h-12 rounded-xl object-cover flex-shrink-0" alt="">
                <div>
                    <p class="font-bold text-slate-800 dark:text-slate-100">{{ $payment->invoice?->student?->full_name ?? '—' }}</p>
                    <p class="text-xs text-slate-400">
                        {{ $payment->invoice?->student?->roll_no ? 'Roll: ' . $payment->invoice->student->roll_no . ' ·' : '' }}
                        Invoice: {{ $payment->invoice?->invoice_number ?? '—' }}
                    </p>
                </div>
            </div>

            {{-- Payment details grid --}}
            <div class="grid grid-cols-2 gap-3">
                <div class="p-3.5 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wide mb-0.5">Amount Paid</p>
                    <p class="text-2xl font-black text-emerald-600">NPR {{ number_format($payment->amount, 2) }}</p>
                </div>
                <div class="p-3.5 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wide mb-0.5">Payment Method</p>
                    <p class="text-base font-bold text-slate-800 dark:text-slate-100 capitalize">
                        {{ str_replace(['_','-'], ' ', $payment->payment_method ?? 'Cash') }}
                    </p>
                </div>
                <div class="p-3.5 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wide mb-0.5">Payment Date</p>
                    <p class="text-base font-bold text-slate-800 dark:text-slate-100">
                        {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}
                    </p>
                </div>
                <div class="p-3.5 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wide mb-0.5">Invoice Status</p>
                    @php $status = $payment->invoice?->status ?? 'unpaid'; @endphp
                    <span class="inline-flex items-center gap-1 text-sm font-bold px-2.5 py-0.5 rounded-full
                        {{ $status === 'paid' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                           : ($status === 'partial' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'
                           : 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400') }}">
                        <i class="bi bi-{{ $status === 'paid' ? 'check-circle-fill' : ($status === 'partial' ? 'clock-fill' : 'x-circle-fill') }}"></i>
                        {{ ucfirst($status) }}
                    </span>
                </div>
            </div>

            {{-- Invoice title --}}
            <div class="p-4 border border-slate-100 dark:border-slate-700 rounded-xl">
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wide mb-1">For</p>
                <p class="font-semibold text-slate-700 dark:text-slate-200">{{ $payment->invoice?->title ?? '—' }}</p>
                @if($payment->gateway_transaction_id)
                <p class="text-xs text-slate-400 mt-1">Transaction Ref: <code class="bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-xs">{{ $payment->gateway_transaction_id }}</code></p>
                @endif
                @if($payment->notes)
                <p class="text-xs text-slate-500 mt-1">{{ $payment->notes }}</p>
                @endif
            </div>

            {{-- Outstanding balance --}}
            @if($payment->invoice)
            @php
                $remaining = $payment->invoice->amount_due;
            @endphp
            <div class="flex items-center justify-between p-4 {{ $remaining > 0 ? 'bg-rose-50 dark:bg-rose-900/20 border border-rose-100 dark:border-rose-800' : 'bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800' }} rounded-xl">
                <p class="text-sm font-semibold {{ $remaining > 0 ? 'text-rose-700 dark:text-rose-300' : 'text-emerald-700 dark:text-emerald-300' }}">
                    {{ $remaining > 0 ? 'Remaining Balance Due' : '✅ Invoice Fully Paid' }}
                </p>
                @if($remaining > 0)
                <p class="text-lg font-black text-rose-600">NPR {{ number_format($remaining, 2) }}</p>
                @endif
            </div>
            @endif

            {{-- Recorded by --}}
            @if($payment->recordedBy)
            <p class="text-xs text-slate-400 text-center">
                Recorded by <span class="font-semibold">{{ $payment->recordedBy->full_name }}</span>
            </p>
            @endif

        </div>

        {{-- Footer --}}
        <div class="px-8 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3 flex-wrap">
            <p class="text-[10px] text-slate-400">Generated: {{ now()->format('d M Y, h:i A') }} · {{ config('app.name') }}</p>
            <div class="flex gap-2">
                <a href="{{ route('finance.receipt', $payment->id) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 transition">
                    <i class="bi bi-file-pdf text-rose-500"></i> Download PDF
                </a>
                <button onclick="window.print()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold transition">
                    <i class="bi bi-printer-fill"></i> Print
                </button>
            </div>
        </div>

    </div>

    <div class="mt-4 flex gap-3 justify-center">
        <a href="{{ route('payments.show', $payment->invoice_id) }}"
           class="text-sm text-indigo-600 hover:underline">
            ← Back to Invoice
        </a>
    </div>

</div>

@push('styles')
<style>
@media print {
    body > *:not(#receipt-card) { display: none !important; }
    nav, button, .sidebar, header { display: none !important; }
    #receipt-card { box-shadow: none !important; border: none !important; }
}
</style>
@endpush
@endsection
