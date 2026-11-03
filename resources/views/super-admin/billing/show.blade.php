@extends('layouts.app')
@section('title', 'SaaS Invoice: ' . $invoice->invoice_number . ' - NepalSchoolHub')

@section('content')
<div class="px-6 py-6 max-w-6xl mx-auto space-y-6">

    {{-- Breadcrumb & Action Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('super.billing.index') }}" class="hover:text-indigo-600">SaaS Billing</a>
                <span>&bull;</span>
                <span class="font-mono">{{ $invoice->invoice_number }}</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                <i class="bi bi-receipt-cutoff text-indigo-600"></i>
                Invoice: {{ $invoice->invoice_number }}
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Billed to: <strong class="text-slate-800 dark:text-slate-200">{{ $invoice->school_name }}</strong> &bull;
                Date: {{ $invoice->invoice_date->format('M d, Y') }}
                @if($invoice->invoice_date_bs) ({{ $invoice->invoice_date_bs }}) @endif
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Print Bill button --}}
            <a href="{{ route('super.billing.print', $invoice->id) }}" target="_blank"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-bold text-sm shadow-sm shadow-red-200 transition-all">
                <i class="bi bi-printer-fill"></i>
                Print Bill Pad (भौचर प्रिन्ट)
            </a>

            @if($invoice->due_amount > 0)
                {{-- Record Payment button --}}
                <button type="button" onclick="openPaymentModal()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-sm shadow-emerald-200 transition-all">
                    <i class="bi bi-credit-card-2-front-fill"></i>
                    Record Payment (भुक्तानी दर्ता)
                </button>
            @endif

            {{-- Edit --}}
            <a href="{{ route('super.billing.edit', $invoice->id) }}"
                class="px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-semibold text-sm hover:bg-slate-50">
                <i class="bi bi-pencil-square"></i> Edit
            </a>

            {{-- Delete --}}
            <form action="{{ route('super.billing.destroy', $invoice->id) }}" method="POST"
                onsubmit="return confirm('Are you sure you want to delete this invoice?');" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="p-2.5 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-sm">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-2">
            <i class="bi bi-check-circle-fill text-emerald-600"></i>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill text-rose-600"></i>
            {{ session('error') }}
        </div>
    @endif

    {{-- Metric Stat Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
            <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Invoice Status</p>
            <div class="mt-2">
                @if($invoice->status === 'paid')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                        <i class="bi bi-check-circle-fill"></i> Paid (भुक्तान भएको)
                    </span>
                @elseif($invoice->status === 'partial')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                        <i class="bi bi-pie-chart-fill"></i> Partially Paid
                    </span>
                @elseif($invoice->status === 'overdue')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                        <i class="bi bi-exclamation-triangle-fill"></i> Overdue
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                        <i class="bi bi-clock-fill"></i> Unpaid (बाँकी)
                    </span>
                @endif
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
            <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Total Invoice Amount</p>
            <h3 class="text-xl font-black text-slate-900 dark:text-white font-mono mt-1">
                रु. {{ number_format($invoice->total_amount, 2) }}
            </h3>
            <p class="text-[11px] text-slate-400 mt-0.5">Subtotal: रु. {{ number_format($invoice->subtotal, 2) }}</p>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
            <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Amount Paid (प्राप्त)</p>
            <h3 class="text-xl font-black text-emerald-600 dark:text-emerald-400 font-mono mt-1">
                रु. {{ number_format($invoice->paid_amount, 2) }}
            </h3>
            <p class="text-[11px] text-slate-400 mt-0.5">{{ $invoice->payments->count() }} transaction(s)</p>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
            <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Balance Due (बाँकी)</p>
            <h3 class="text-xl font-black text-rose-600 dark:text-rose-400 font-mono mt-1">
                रु. {{ number_format($invoice->due_amount, 2) }}
            </h3>
            @if($invoice->due_date)
                <p class="text-[11px] text-slate-400 mt-0.5">Due: {{ $invoice->due_date->format('M d, Y') }}</p>
            @endif
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Left 2 cols: Line items & Details --}}
        <div class="md:col-span-2 space-y-6">

            {{-- Bill Line Items --}}
            <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="bi bi-box-seam text-indigo-600"></i>
                        Invoiced Services &amp; Hardware (बिल गरिएका सामग्रीहरू)
                    </h3>
                    <span class="text-xs text-slate-400 font-mono">{{ $invoice->items->count() }} item(s)</span>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-900 font-bold text-slate-600 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="py-2.5 px-3">Item / Service</th>
                                <th class="py-2.5 px-3 text-center">Qty</th>
                                <th class="py-2.5 px-3 text-right">Unit Rate</th>
                                <th class="py-2.5 px-3 text-right">Amount (रु.)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @foreach($invoice->items as $it)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20">
                                    <td class="py-3 px-3">
                                        <div class="font-bold text-slate-900 dark:text-white text-xs">
                                            {{ $it->title }}
                                        </div>
                                        @if($it->description)
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $it->description }}</p>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-center font-mono font-medium">
                                        {{ $it->quantity }}
                                    </td>
                                    <td class="py-3 px-3 text-right font-mono text-slate-700 dark:text-slate-300">
                                        रु. {{ number_format($it->unit_price, 2) }}
                                    </td>
                                    <td class="py-3 px-3 text-right font-mono font-bold text-slate-900 dark:text-white">
                                        रु. {{ number_format($it->total_amount, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Bill Summary Box --}}
                <div class="bg-slate-50 dark:bg-slate-900/60 rounded-xl p-4 space-y-2 max-w-sm ml-auto text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Subtotal:</span>
                        <span class="font-bold font-mono">रु. {{ number_format($invoice->subtotal, 2) }}</span>
                    </div>
                    @if($invoice->discount_amount > 0)
                        <div class="flex justify-between text-emerald-600">
                            <span>Discount:</span>
                            <span class="font-bold font-mono">- रु. {{ number_format($invoice->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    @if($invoice->tax_amount > 0)
                        <div class="flex justify-between text-slate-600">
                            <span>Tax ({{ $invoice->tax_percent }}%):</span>
                            <span class="font-bold font-mono">+ रु. {{ number_format($invoice->tax_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="pt-2 border-t border-slate-200 dark:border-slate-700 flex justify-between text-sm font-black text-slate-900 dark:text-white">
                        <span>Total Due:</span>
                        <span class="font-mono text-indigo-600 dark:text-indigo-400">रु. {{ number_format($invoice->total_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-xs font-bold text-emerald-600 pt-1">
                        <span>Paid to date:</span>
                        <span class="font-mono">रु. {{ number_format($invoice->paid_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-xs font-bold text-rose-600">
                        <span>Remaining Balance:</span>
                        <span class="font-mono">रु. {{ number_format($invoice->due_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Payment Transactions History --}}
            <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-emerald-600 flex items-center gap-2">
                        <i class="bi bi-wallet2"></i>
                        Recorded Payments (भुक्तानी अभिलेख)
                    </h3>
                    @if($invoice->due_amount > 0)
                        <button type="button" onclick="openPaymentModal()" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                            <i class="bi bi-plus-circle"></i> Add Payment
                        </button>
                    @endif
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-900 font-bold text-slate-600 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="py-2.5 px-3">Receipt #</th>
                                <th class="py-2.5 px-3">Date</th>
                                <th class="py-2.5 px-3">Method</th>
                                <th class="py-2.5 px-3">Txn Reference</th>
                                <th class="py-2.5 px-3 text-right">Amount (रु.)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @forelse($invoice->payments as $pmt)
                                <tr>
                                    <td class="py-2.5 px-3 font-mono font-bold text-slate-800 dark:text-slate-200">{{ $pmt->payment_number }}</td>
                                    <td class="py-2.5 px-3">{{ $pmt->payment_date->format('M d, Y') }}</td>
                                    <td class="py-2.5 px-3 uppercase text-[11px] font-semibold text-slate-600 dark:text-slate-300">{{ str_replace('_', ' ', $pmt->payment_method) }}</td>
                                    <td class="py-2.5 px-3 font-mono text-slate-500">{{ $pmt->transaction_reference ?: '—' }}</td>
                                    <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-600">रु. {{ number_format($pmt->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-slate-400">
                                        No payments recorded yet for this invoice.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right 1 col: School Info & Linked Agreement --}}
        <div class="space-y-6">

            {{-- School Details --}}
            <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-5 shadow-xs space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 dark:border-slate-700 pb-2">
                    Client School Details
                </h3>
                <div class="text-xs space-y-1.5">
                    <p class="font-bold text-slate-900 dark:text-white text-sm">{{ $invoice->school_name }}</p>
                    @if($invoice->school_address)
                        <p class="text-slate-500"><i class="bi bi-geo-alt"></i> {{ $invoice->school_address }}</p>
                    @endif
                    @if($invoice->school_pan)
                        <p class="text-slate-500">PAN / VAT: <strong class="text-slate-800 dark:text-slate-200 font-mono">{{ $invoice->school_pan }}</strong></p>
                    @endif
                    @if($invoice->school_phone)
                        <p class="text-slate-500"><i class="bi bi-telephone"></i> {{ $invoice->school_phone }}</p>
                    @endif
                    @if($invoice->school_email)
                        <p class="text-slate-500"><i class="bi bi-envelope"></i> {{ $invoice->school_email }}</p>
                    @endif
                </div>
            </div>

            {{-- Linked Agreement --}}
            @if($invoice->agreement)
                <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-5 shadow-xs space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-2">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-600 flex items-center gap-1.5">
                            <i class="bi bi-file-earmark-check-fill"></i>
                            Linked Agreement
                        </h3>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">Active</span>
                    </div>
                    <div class="text-xs space-y-1.5">
                        <p class="font-mono font-bold text-slate-900 dark:text-white">
                            <a href="{{ route('super.agreements.show', $invoice->agreement->id) }}" class="text-emerald-600 hover:underline">
                                {{ $invoice->agreement->agreement_number }}
                            </a>
                        </p>
                        <p class="text-slate-500">Date: {{ $invoice->agreement->agreement_date_bs }}</p>
                        <p class="text-slate-500">Contract Total: रु. {{ number_format($invoice->agreement->grand_total) }}</p>
                        <div class="pt-2">
                            <a href="{{ route('super.agreements.print', $invoice->agreement->id) }}" target="_blank"
                                class="inline-flex items-center gap-1 text-emerald-600 font-bold hover:underline text-xs">
                                <i class="bi bi-printer"></i> View Official Agreement Paper &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Instructions & Banking --}}
            <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-5 shadow-xs space-y-2 text-xs">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 dark:border-slate-700 pb-2">
                    Payment Instructions
                </h3>
                <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                    {{ $invoice->terms ?: 'Tech Pro Code Pvt. Ltd. | Bank Transfer / eSewa / Khalti' }}
                </p>
                @if($invoice->notes)
                    <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-900 text-slate-500 mt-2">
                        <strong>Notes:</strong> {{ $invoice->notes }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── RECORD PAYMENT MODAL ─────────────────────────────────────── --}}
<div id="paymentModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700">
            <h3 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i class="bi bi-wallet2 text-emerald-600"></i>
                Record Payment (भुक्तानी दर्ता)
            </h3>
            <button type="button" onclick="closePaymentModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>

        <form action="{{ route('super.billing.payments.store', $invoice->id) }}" method="POST" class="space-y-4 mt-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Payment Amount (रु.) *</label>
                <input type="number" step="0.01" max="{{ $invoice->due_amount > 0 ? $invoice->due_amount : $invoice->total_amount }}"
                    name="amount" value="{{ $invoice->due_amount }}" required
                    class="w-full px-3.5 py-2 text-base font-bold font-mono rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-emerald-600">
                <p class="text-[11px] text-slate-400 mt-1">Current Due Amount: रु. {{ number_format($invoice->due_amount, 2) }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Payment Date *</label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                        class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Payment Method *</label>
                    <select name="payment_method" required class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                        <option value="bank_transfer">Bank Transfer (बैंक दाखिला)</option>
                        <option value="esewa">eSewa (ईसेवा)</option>
                        <option value="khalti">Khalti (खल्ती)</option>
                        <option value="connect_ips">ConnectIPS</option>
                        <option value="cheque">Cheque (चेक)</option>
                        <option value="cash">Cash (नगद)</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Txn Ref / Voucher #</label>
                    <input type="text" name="transaction_reference" placeholder="e.g. TXN-948123 or Cheque #"
                        class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Bank Name</label>
                    <input type="text" name="bank_name" placeholder="e.g. Nabil / RBB / Global IME"
                        class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Remarks / Note</label>
                <input type="text" name="note" placeholder="Optional payment note"
                    class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                <button type="button" onclick="closePaymentModal()" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 text-xs font-semibold">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold">Record Payment</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPaymentModal() {
    document.getElementById('paymentModal').classList.remove('hidden');
}
function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
}
</script>
@endsection

