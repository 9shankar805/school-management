@extends('layouts.app')
@section('content')
<div class="px-4 lg:px-8 py-6 max-w-4xl mx-auto">

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Invoice Detail</h1>
            <p class="text-xs text-slate-500 mt-0.5">{{ $invoice->invoice_number }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('manage finance')
            @if($invoice->status !== 'paid')
            <a href="{{ route('payments.pay', $invoice->id) }}"
               class="btn-primary text-sm px-4 py-2 rounded-xl">
                <i class="bi bi-cash-coin mr-1"></i> Record Payment
            </a>
            <a href="{{ route('finance.installments.index', $invoice->id) }}"
               class="inline-flex items-center gap-1.5 text-sm px-4 py-2 rounded-xl font-semibold text-purple-700 bg-purple-50 dark:bg-purple-900/20 dark:text-purple-300 hover:bg-purple-100 dark:hover:bg-purple-900/40 border border-purple-200 dark:border-purple-800 transition">
                <i class="bi bi-calendar2-range mr-1"></i> Instalments
            </a>
            @endif
            @endcan
            @if($invoice->payments->isNotEmpty())
            <div class="inline-flex rounded-xl shadow-sm" role="group">
                <a href="{{ route('payments.receipt-download', $invoice->payments->last()->id) }}"
                   class="btn-secondary text-sm px-3.5 py-2 rounded-l-xl border-r-0" target="_blank" title="Standard A5 PDF Receipt">
                    <i class="bi bi-file-pdf mr-1 text-rose-500"></i> Receipt
                </a>
                <a href="{{ route('payments.receipt-download', ['paymentId' => $invoice->payments->last()->id, 'format' => 'dual']) }}"
                   class="btn-secondary text-sm px-3 py-2 border-r-0" target="_blank" title="Dual Copy (A4) Student + Office">
                    <i class="bi bi-files text-indigo-500"></i> Dual
                </a>
                <a href="{{ route('payments.receipt-download', ['paymentId' => $invoice->payments->last()->id, 'format' => 'thermal']) }}"
                   class="btn-secondary text-sm px-3 py-2 rounded-r-xl" target="_blank" title="POS Thermal (80mm) Receipt">
                    <i class="bi bi-receipt text-amber-500"></i> POS
                </a>
            </div>
            @endif
            <a href="{{ route('payments.student-ledger', $invoice->student_id) }}"
               class="btn-secondary text-sm px-4 py-2 rounded-xl">
                <i class="bi bi-journal-text mr-1"></i> Ledger
            </a>
        </div>
    </div>

    @include('session-messages')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Invoice card --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Invoice meta --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <div class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $invoice->title }}</div>
                        @if($invoice->description)
                        <div class="text-xs text-slate-500 mt-1">{{ $invoice->description }}</div>
                        @endif
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold
                        {{ $invoice->status === 'paid' ? 'bg-emerald-100 text-emerald-700' :
                           ($invoice->status === 'partial' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">
                        {{ ucfirst($invoice->status) }}
                        @if($invoice->is_overdue) · OVERDUE @endif
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm mb-5">
                    <div><div class="text-xs text-slate-400 mb-0.5">Invoice #</div><div class="font-semibold text-slate-700 dark:text-slate-200">{{ $invoice->invoice_number }}</div></div>
                    <div><div class="text-xs text-slate-400 mb-0.5">Student</div><div class="font-semibold text-slate-700 dark:text-slate-200">{{ $invoice->student?->full_name ?? '—' }}</div></div>
                    <div><div class="text-xs text-slate-400 mb-0.5">Due Date</div><div class="font-semibold {{ $invoice->is_overdue ? 'text-rose-600' : 'text-slate-700 dark:text-slate-200' }}">{{ $invoice->due_date?->format('d M Y') ?? '—' }}</div></div>
                    <div><div class="text-xs text-slate-400 mb-0.5">Period</div><div class="font-semibold text-slate-700 dark:text-slate-200">{{ $invoice->billing_month ? \Carbon\Carbon::create($invoice->billing_year, $invoice->billing_month)->format('M Y') : '—' }}</div></div>
                </div>

                {{-- Line items --}}
                <div class="border border-slate-100 dark:border-slate-800 rounded-xl overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs text-slate-500 dark:text-slate-400">
                            <tr>
                                <th class="text-left px-4 py-3">Description</th>
                                <th class="text-right px-4 py-3">Amount</th>
                                <th class="text-right px-4 py-3">Discount</th>
                                <th class="text-right px-4 py-3">Tax</th>
                                <th class="text-right px-4 py-3">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoice->items as $item)
                            <tr class="border-t border-slate-100 dark:border-slate-800">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-700 dark:text-slate-200">{{ $item->description }}</div>
                                    @if($item->feeType)
                                    <div class="text-xs text-slate-400">{{ $item->feeType->name }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">Rs. {{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-4 py-3 text-right text-emerald-600">{{ $item->discount_amount > 0 ? '-Rs. '.number_format($item->discount_amount, 2) : '—' }}</td>
                                <td class="px-4 py-3 text-right text-slate-500">{{ $item->tax_amount > 0 ? '+Rs. '.number_format($item->tax_amount, 2) : '—' }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-800 dark:text-slate-100">Rs. {{ number_format($item->total, 2) }}</td>
                            </tr>
                            @empty
                            <tr class="border-t border-slate-100 dark:border-slate-800">
                                <td colspan="5" class="px-4 py-4 text-center text-slate-400 text-xs">No line items (legacy invoice)</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-slate-50 dark:bg-slate-800/40 text-sm font-semibold">
                            <tr class="border-t border-slate-200 dark:border-slate-700">
                                <td colspan="4" class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">Current Month Charges Subtotal</td>
                                <td class="px-4 py-3 text-right">Rs. {{ number_format($invoice->total_amount, 2) }}</td>
                            </tr>
                            @if($invoice->discount_amount > 0)
                            <tr>
                                <td colspan="4" class="px-4 py-2 text-right text-emerald-600">Discounts Applied</td>
                                <td class="px-4 py-2 text-right text-emerald-600">-Rs. {{ number_format($invoice->discount_amount, 2) }}</td>
                            </tr>
                            @endif
                            @if($invoice->tax_amount > 0)
                            <tr>
                                <td colspan="4" class="px-4 py-2 text-right text-slate-500">Tax</td>
                                <td class="px-4 py-2 text-right">+Rs. {{ number_format($invoice->tax_amount, 2) }}</td>
                            </tr>
                            @endif
                            <tr class="border-t border-slate-200 dark:border-slate-700">
                                <td colspan="4" class="px-4 py-2 text-right text-slate-700 dark:text-slate-200 font-semibold">Current Month Net Fee</td>
                                <td class="px-4 py-2 text-right text-slate-700 dark:text-slate-200 font-semibold">Rs. {{ number_format($invoice->amount, 2) }}</td>
                            </tr>
                            @if($invoice->previous_due > 0)
                            <tr class="bg-amber-50/60 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400">
                                <td colspan="4" class="px-4 py-2.5 text-right font-semibold">
                                    <span class="inline-flex items-center gap-1">
                                        <i class="bi bi-clock-history text-xs"></i>
                                        Previous Unpaid Dues (बक्यौता Arrears Carryover)
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-right font-bold">+Rs. {{ number_format($invoice->previous_due, 2) }}</td>
                            </tr>
                            @endif
                            @if($invoice->fine_amount > 0)
                            <tr class="bg-rose-50/60 dark:bg-rose-950/20 text-rose-700 dark:text-rose-400">
                                <td colspan="4" class="px-4 py-2.5 text-right font-semibold">
                                    <span class="inline-flex items-center gap-1">
                                        <i class="bi bi-exclamation-triangle text-xs"></i>
                                        Late Payment Surcharge / Fine (विलम्ब शुल्क)
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-right font-bold">+Rs. {{ number_format($invoice->fine_amount, 2) }}</td>
                            </tr>
                            @endif
                            <tr class="border-t-2 border-slate-300 dark:border-slate-600 bg-slate-100/70 dark:bg-slate-800/80">
                                <td colspan="4" class="px-4 py-3.5 text-right text-slate-900 dark:text-white font-extrabold text-base">
                                    Total Statement Payable (कुल तिर्नुपर्ने)
                                </td>
                                <td class="px-4 py-3.5 text-right text-emerald-700 dark:text-emerald-400 font-extrabold text-lg">
                                    Rs. {{ number_format($invoice->total_payable, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Payment history --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-4">Payment History</h3>
                @forelse($invoice->payments as $pay)
                <div class="flex items-center justify-between py-3 border-b border-slate-100 dark:border-slate-800 last:border-0">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 flex items-center justify-center">
                            <i class="bi bi-check-circle text-sm"></i>
                        </span>
                        <div>
                            <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $pay->receipt_number }}</div>
                            <div class="text-xs text-slate-400">{{ $pay->payment_date?->format('d M Y') }} · {{ ucfirst($pay->gateway ?? $pay->payment_method) }} @if($pay->recordedBy) · {{ $pay->recordedBy->full_name }} @endif</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="font-bold text-emerald-600">Rs. {{ number_format($pay->amount, 2) }}</span>
                        <a href="{{ route('finance.receipt', $pay->id) }}" target="_blank" class="text-slate-400 hover:text-emerald-600 transition">
                            <i class="bi bi-receipt"></i>
                        </a>
                    </div>
                </div>
                @empty
                <p class="text-sm text-slate-400 text-center py-4">No payments recorded yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Summary sidebar --}}
        <div class="space-y-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
                <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-4">Billing Summary</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Current Month Net</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-200">Rs. {{ number_format($invoice->amount, 2) }}</span>
                    </div>
                    @if($invoice->previous_due > 0)
                    <div class="flex justify-between text-amber-600 dark:text-amber-400">
                        <span class="flex items-center gap-1"><i class="bi bi-clock-history text-xs"></i> Previous Arrears</span>
                        <span class="font-semibold">+Rs. {{ number_format($invoice->previous_due, 2) }}</span>
                    </div>
                    @endif
                    @if($invoice->fine_amount > 0)
                    <div class="flex justify-between text-rose-600 dark:text-rose-400">
                        <span class="flex items-center gap-1"><i class="bi bi-exclamation-circle text-xs"></i> Late Fine</span>
                        <span class="font-semibold">+Rs. {{ number_format($invoice->fine_amount, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between border-t border-slate-100 dark:border-slate-800 pt-2 font-bold text-slate-800 dark:text-slate-100">
                        <span>Total Payable</span>
                        <span>Rs. {{ number_format($invoice->total_payable, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-emerald-600">
                        <span>Paid to Date</span>
                        <span class="font-semibold">-Rs. {{ number_format($invoice->amount_paid, 2) }}</span>
                    </div>
                    <div class="flex justify-between border-t-2 border-slate-200 dark:border-slate-700 pt-3 {{ $invoice->amount_due > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600' }}">
                        <span class="font-extrabold text-base">{{ $invoice->status === 'paid' ? 'Settled' : 'Total Due' }}</span>
                        <span class="font-extrabold text-lg">Rs. {{ number_format($invoice->amount_due, 2) }}</span>
                    </div>
                </div>
            </div>

            @if($invoice->status !== 'paid')
            <a href="{{ route('payments.pay', $invoice->id) }}"
               class="block w-full text-center bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 rounded-xl transition">
                <i class="bi bi-cash-coin mr-2"></i> Pay Rs. {{ number_format($invoice->amount_due, 2) }}
            </a>

            {{-- Payment QR codes — scan & pay (eSewa / Khalti / Bank / FonePay) --}}
            @php
                $hasQR = $paySchool && (
                    $paySchool->payment_qr_esewa  ||
                    $paySchool->payment_qr_khalti ||
                    $paySchool->payment_qr_bank   ||
                    $paySchool->payment_qr_fonepay
                );
            @endphp
            @if($hasQR)
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-3 uppercase tracking-wide flex items-center gap-1.5">
                    <i class="bi bi-qr-code text-emerald-600"></i> Scan to Pay
                </p>
                <p class="text-[11px] text-slate-400 mb-4">
                    Open your eSewa, Khalti or banking app → Scan QR → Enter
                    <span class="font-bold text-slate-600 dark:text-slate-300">Rs. {{ number_format($invoice->amount_due, 2) }}</span>
                    → Pay.
                </p>
                <div class="grid grid-cols-2 gap-3">
                    @foreach([
                        ['field'=>'payment_qr_esewa',   'label'=>'eSewa',   'color'=>'#60bb46'],
                        ['field'=>'payment_qr_khalti',  'label'=>'Khalti',  'color'=>'#5c2d91'],
                        ['field'=>'payment_qr_bank',    'label'=>'Bank/ConnectIPS', 'color'=>'#1d4ed8'],
                        ['field'=>'payment_qr_fonepay', 'label'=>'FonePay', 'color'=>'#e3001e'],
                    ] as $qr)
                    @if($paySchool->{$qr['field']})
                    <div class="flex flex-col items-center gap-2 p-3 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/60">
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full text-white"
                              style="background:{{ $qr['color'] }}">{{ $qr['label'] }}</span>
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($paySchool->{$qr['field']}) }}"
                             alt="{{ $qr['label'] }} QR"
                             class="w-28 h-28 object-contain rounded-lg bg-white p-1 border border-slate-200 dark:border-slate-700 cursor-pointer"
                             onclick="document.getElementById('qr-modal-{{ $loop->index }}').classList.remove('hidden')">
                        <span class="text-[10px] text-slate-400">Tap to enlarge</span>

                        {{-- Full-screen QR modal --}}
                        <div id="qr-modal-{{ $loop->index }}"
                             class="hidden fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4"
                             onclick="this.classList.add('hidden')">
                            <div class="bg-white rounded-3xl p-6 flex flex-col items-center gap-3 shadow-2xl max-w-xs w-full" onclick="event.stopPropagation()">
                                <span class="text-sm font-bold px-3 py-1 rounded-full text-white"
                                      style="background:{{ $qr['color'] }}">{{ $qr['label'] }}</span>
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($paySchool->{$qr['field']}) }}"
                                     alt="{{ $qr['label'] }} QR"
                                     class="w-56 h-56 object-contain">
                                <p class="text-xs text-slate-500 text-center font-semibold">
                                    Amount: <span class="text-slate-800">Rs. {{ number_format($invoice->amount_due, 2) }}</span>
                                </p>
                                @if($paySchool->payment_instructions)
                                <p class="text-[11px] text-slate-400 text-center whitespace-pre-line">{{ $paySchool->payment_instructions }}</p>
                                @endif
                                <button onclick="this.closest('[id^=qr-modal]').classList.add('hidden')"
                                        class="text-xs text-slate-500 hover:text-slate-700 mt-1">
                                    <i class="bi bi-x-circle mr-1"></i>Close
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
                @if($paySchool->payment_instructions)
                <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 rounded-xl">
                    <p class="text-[11px] text-amber-800 dark:text-amber-300 whitespace-pre-line">{{ $paySchool->payment_instructions }}</p>
                </div>
                @endif
            </div>
            @endif
            @endif

            <a href="{{ route('payments.student-ledger', $invoice->student_id) }}"
               class="block w-full text-center bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold py-3 rounded-xl transition text-sm">
                <i class="bi bi-journal-text mr-2"></i> View Full Ledger
            </a>
        </div>
    </div>
</div>
@endsection
