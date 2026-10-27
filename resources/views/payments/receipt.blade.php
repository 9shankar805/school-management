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
        <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-indigo-700 px-8 py-6 flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-4">
                @if(!empty($logoSrc))
                <img src="{{ $logoSrc }}" class="h-14 w-14 rounded-2xl object-contain bg-white p-1.5 shadow-md border border-white/20" alt="Logo">
                @elseif($school?->logo_url)
                <img src="{{ $school->logo_url }}" class="h-14 w-14 rounded-2xl object-contain bg-white p-1.5 shadow-md border border-white/20" alt="Logo">
                @else
                <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-white font-black text-xl shadow-inner border border-white/20">
                    {{ strtoupper(substr($school?->name ?? config('app.name'), 0, 2)) }}
                </div>
                @endif
                <div>
                    <h2 class="text-white font-black text-xl leading-tight uppercase tracking-tight">{{ $school?->name ?? config('app.name') }}</h2>
                    <p class="text-emerald-100 text-xs mt-0.5 flex items-center gap-2 flex-wrap">
                        <span>{{ $school?->address ?? 'Kathmandu, Nepal' }}</span>
                        @if($school?->phone) <span>&bull; Tel: {{ $school->phone }}</span> @endif
                        @if($school?->pan_number) <span class="bg-white/20 px-1.5 py-0.5 rounded text-[10px] font-bold">&bull; PAN: {{ $school->pan_number }}</span> @endif
                    </p>
                </div>
            </div>
            <div class="text-right">
                <span class="text-emerald-100 text-xs font-semibold uppercase tracking-widest block">Official Fee Receipt</span>
                <span class="text-white font-black text-2xl tracking-tight">#{{ $payment->receipt_number ?? str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</span>
            </div>
        </div>

        {{-- Status banner --}}
        <div class="bg-emerald-50 dark:bg-emerald-950/40 border-b border-emerald-100 dark:border-emerald-800/60 px-8 py-3 flex items-center justify-between gap-3 flex-wrap">
            <div class="flex items-center gap-2">
                <i class="bi bi-check-circle-fill text-emerald-600 text-lg"></i>
                <p class="text-emerald-800 dark:text-emerald-300 font-bold text-sm">Payment Verified &amp; Cleared</p>
            </div>
            <div class="text-emerald-700 dark:text-emerald-400 text-xs font-semibold flex items-center gap-2 flex-wrap">
                @if(class_exists(\App\Services\NepaliDateService::class))
                    <span class="bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-200 px-2 py-0.5 rounded font-bold">
                        नेपाली मिति: {{ \App\Services\NepaliDateService::formatBS($payment->payment_date, true) }}
                    </span>
                    <span>&bull;</span>
                @endif
                <span>English Date: {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</span>
            </div>
        </div>

        <div class="px-8 py-6 space-y-5">

            {{-- Student info --}}
            @php
                $student = $payment->invoice?->student;
                $className = $payment->invoice?->schoolClass?->name ?? $student?->academic_class?->name ?? '—';
                $sectionName = $payment->invoice?->section?->name ?? $student?->section?->name ?? '';
                $classSection = $sectionName ? ($className . ' (' . $sectionName . ')') : $className;
                $rollNo = $student?->roll_no ?? $student?->admission_no ?? '—';
            @endphp
            <div class="flex items-center justify-between gap-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800 flex-wrap">
                <div class="flex items-center gap-3.5">
                    <img src="{{ $student?->avatar }}" class="w-12 h-12 rounded-xl object-cover flex-shrink-0 border border-slate-200 dark:border-slate-700" alt="">
                    <div>
                        <p class="font-extrabold text-slate-900 dark:text-slate-100 text-base">{{ $student?->full_name ?? '—' }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Class: <strong class="text-slate-700 dark:text-slate-200">{{ $classSection }}</strong> &bull;
                            Roll / Adm: <strong class="text-slate-700 dark:text-slate-200">{{ $rollNo }}</strong>
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Invoice Reference</span>
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-200">#{{ $payment->invoice?->invoice_number ?? '—' }}</span>
                </div>
            </div>

            {{-- Payment details grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="p-3.5 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800">
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wide mb-0.5">Amount Paid</p>
                    <p class="text-xl font-black text-emerald-600">Rs. {{ number_format($payment->amount, 2) }}</p>
                </div>
                <div class="p-3.5 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800">
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wide mb-0.5">Payment Method</p>
                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100 uppercase">
                        {{ $payment->payment_method ?? 'Cash' }}
                    </p>
                </div>
                <div class="p-3.5 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800">
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wide mb-0.5">Settlement Mode</p>
                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100">
                        @if($payment->bankAccount)
                            {{ $payment->bankAccount->bank_name }}
                        @elseif($payment->gateway_transaction_id)
                            Online Gateway
                        @else
                            Cash Counter
                        @endif
                    </p>
                </div>
                <div class="p-3.5 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800">
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wide mb-0.5">Invoice Status</p>
                    @php $status = $payment->invoice?->status ?? 'unpaid'; @endphp
                    <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-0.5 rounded-full
                        {{ $status === 'paid' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                           : ($status === 'partial' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'
                           : 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400') }}">
                        <i class="bi bi-{{ $status === 'paid' ? 'check-circle-fill' : ($status === 'partial' ? 'clock-fill' : 'x-circle-fill') }}"></i>
                        {{ ucfirst($status) }}
                    </span>
                </div>
            </div>

            {{-- Itemized Particulars Breakdown --}}
            @php $items = $payment->invoice?->items ?? collect(); @endphp
            <div class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
                <div class="bg-slate-100/70 dark:bg-slate-800/60 px-4 py-2.5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Particulars / Fee Breakdown</span>
                    <span class="text-[11px] text-slate-500 font-semibold">{{ $payment->invoice?->title ?? 'Fee Payment' }}</span>
                </div>
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/30 text-slate-500 dark:text-slate-400 border-b border-slate-100 dark:border-slate-800">
                        <tr>
                            <th class="py-2 px-4 font-semibold">Fee Head</th>
                            <th class="py-2 px-4 font-semibold text-right">Amount (NPR)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @if($items->count() > 0)
                            @foreach($items as $item)
                            <tr>
                                <td class="py-2 px-4 text-slate-800 dark:text-slate-200 font-medium">
                                    {{ $item->feeType?->name ?? $item->description ?? 'Tuition Fee' }}
                                </td>
                                <td class="py-2 px-4 text-right font-bold text-slate-800 dark:text-slate-200">
                                    Rs. {{ number_format((float)($item->total ?? $item->amount ?? $item->unit_price ?? 0), 2) }}
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td class="py-2 px-4 text-slate-800 dark:text-slate-200 font-medium">
                                    {{ $payment->invoice?->title ?? 'Tuition & Academic Fees' }}
                                </td>
                                <td class="py-2 px-4 text-right font-bold text-slate-800 dark:text-slate-200">
                                    Rs. {{ number_format($payment->amount, 2) }}
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
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
            <p class="text-[10px] text-slate-400">Generated: {{ now()->format('d M Y, h:i A') }} &bull; {{ $school?->name ?? config('app.name') }}</p>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('payments.receipt-download', ['paymentId' => $payment->id, 'format' => 'dual']) }}"
                   target="_blank"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700 rounded-xl text-xs font-semibold text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 transition shadow-sm">
                    <i class="bi bi-files text-indigo-600"></i> Dual Copy (A4)
                </a>
                <a href="{{ route('payments.receipt-download', ['paymentId' => $payment->id, 'format' => 'thermal']) }}"
                   target="_blank"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700 rounded-xl text-xs font-semibold text-amber-800 dark:text-amber-300 hover:bg-amber-100 transition shadow-sm">
                    <i class="bi bi-receipt text-amber-600"></i> POS Thermal (80mm)
                </a>
                <a href="{{ route('payments.receipt-download', $payment->id) }}"
                   target="_blank"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 transition shadow-sm">
                    <i class="bi bi-file-pdf text-rose-500"></i> Standard A5 PDF
                </a>
                <button onclick="window.print()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white dark:bg-slate-100 dark:text-slate-900 rounded-xl text-xs font-semibold transition shadow-sm">
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
