@extends('layouts.app')

@section('content')

<div class="flex-1 p-6 lg:p-8 overflow-auto">

    <nav class="text-xs text-slate-400 mb-5 flex items-center gap-1">
        <a href="{{ route('payments.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Payments & Invoices</a>
        <i class="bi bi-chevron-right text-slate-300 text-xs"></i>
        <span class="text-slate-500 dark:text-slate-300">POS Fee Collection</span>
    </nav>

    <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> POS Cash Desk Terminal
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                    Invoice #{{ $invoice->invoice_number }}
                </span>
            </div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">
                Record Fee Payment & Cash Return
            </h1>
            <p class="text-slate-400 text-xs mt-0.5">Collect fee from student with real-time cash change calculation</p>
        </div>

        <a href="{{ route('payments.show', $invoice->id) }}"
           class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 transition">
            &larr; View Invoice Detail
        </a>
    </div>

    @php
        $paidTotal   = $invoice->payments->sum('amount_paid') ?? 0;
        $amountDue   = max(0, $invoice->amount - $paidTotal);
        $student     = $invoice->student;
        $latestClass = optional(optional($student?->promotions)->last())->schoolClass;
    @endphp

    {{-- ── 2-COLUMN SPLIT POS STUDIO GRID ────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        {{-- LEFT COLUMN: Payment Inputs & Student Summary (7 Cols) --}}
        <div class="lg:col-span-7 space-y-6">

            {{-- Student & Invoice Card --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl font-bold">
                            <i class="bi bi-person-badge-fill"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 dark:text-slate-100 text-base">{{ $student?->full_name ?? 'Student' }}</h3>
                            <p class="text-xs text-slate-400">
                                @if($latestClass) Class {{ $latestClass->class_name }} &middot; @endif
                                Email: {{ $student?->email ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 rounded-full text-xs font-extrabold uppercase tracking-wider">
                        {{ strtoupper($invoice->status) }}
                    </span>
                </div>

                <div class="grid grid-cols-3 gap-3 pt-1 text-center">
                    <div class="p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                        <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider block mb-0.5">Total Billed</span>
                        <span class="text-sm font-black text-slate-800 dark:text-slate-100">Rs. {{ number_format($invoice->amount, 2) }}</span>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                        <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider block mb-0.5">Paid to Date</span>
                        <span class="text-sm font-black text-emerald-600 dark:text-emerald-400">Rs. {{ number_format($paidTotal, 2) }}</span>
                    </div>
                    <div class="p-3 bg-rose-50 dark:bg-rose-900/30 rounded-xl border border-rose-100 dark:border-rose-800">
                        <span class="text-[10px] text-rose-500 uppercase font-bold tracking-wider block mb-0.5">Balance Due</span>
                        <span class="text-sm font-black text-rose-600 dark:text-rose-400">Rs. {{ number_format($amountDue, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Payment Form Controls --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 p-6 shadow-xs space-y-5">
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 pb-3 border-b border-slate-100 dark:border-slate-700 flex items-center gap-2">
                    <i class="bi bi-sliders text-indigo-500"></i> Transaction Controls
                </h3>

                <form id="posPaymentForm" action="{{ route('payments.process', $invoice->id) }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">
                            Amount to Pay (Rs.) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" step="0.01" name="amount" id="input_payable_amount"
                               value="{{ old('amount', $amountDue) }}" max="{{ $amountDue }}" min="0.01" required
                               class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-3 text-lg font-bold bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                        <p class="text-[11px] text-slate-400 mt-1">Maximum payable for this transaction: Rs. {{ number_format($amountDue, 2) }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Payment Method <span class="text-rose-500">*</span>
                            </label>
                            <select name="payment_method" id="input_payment_method" required
                                    class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2.5 text-sm font-bold bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                                <option value="cash" selected>💵 Cash (with POS Desk)</option>
                                <option value="esewa">🟢 eSewa Mobile Wallet (Online)</option>
                                <option value="khalti">🟣 Khalti Wallet (Online)</option>
                                <option value="fonepay">📲 Fonepay Direct QR Code (Online)</option>
                                <option value="connectips">🏦 ConnectIPS NetBanking (Online)</option>
                                <option value="stripe">💳 Credit / Debit Card (Online Stripe)</option>
                                <option value="paypal">🅿️ PayPal (Online)</option>
                                <option value="bank">🏦 Bank Transfer (Offline/Direct)</option>
                                <option value="cheque">🧾 Cheque</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">
                                Transaction Date <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required
                                   class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2.5 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">
                            Remarks / Reference Notes
                        </label>
                        <input type="text" name="notes" placeholder="e.g. Receipt given to father, Cheque No #1042"
                               class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2.5 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    </div>

                    <button type="submit" id="btn_submit_pos"
                            class="w-full py-3.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-xl text-sm font-bold shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <i class="bi bi-check-circle-fill text-base"></i> Complete Fee Collection & Issue Receipt
                    </button>
                </form>
            </div>

            {{-- Online Payment Quick Gateways --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 p-5 shadow-xs space-y-3">
                <h4 class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="bi bi-globe2 text-indigo-500"></i> Instant Online Payment Gateways
                </h4>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <form method="POST" action="{{ route('finance.payment.initiate') }}">
                        @csrf
                        <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                        <input type="hidden" name="gateway" value="esewa">
                        <button type="submit" class="w-full py-2.5 px-3 rounded-xl text-xs font-black text-white transition flex items-center justify-center gap-1 shadow-xs hover:opacity-95 cursor-pointer" style="background:#60bb46">
                            🟢 eSewa
                        </button>
                    </form>
                    <form method="POST" action="{{ route('finance.payment.initiate') }}">
                        @csrf
                        <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                        <input type="hidden" name="gateway" value="khalti">
                        <button type="submit" class="w-full py-2.5 px-3 rounded-xl text-xs font-black text-white transition flex items-center justify-center gap-1 shadow-xs hover:opacity-95 cursor-pointer" style="background:#5c2d91">
                            🟣 Khalti
                        </button>
                    </form>
                    <form method="POST" action="{{ route('finance.payment.initiate') }}">
                        @csrf
                        <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                        <input type="hidden" name="gateway" value="fonepay">
                        <button type="submit" class="w-full py-2.5 px-3 rounded-xl text-xs font-black text-white transition flex items-center justify-center gap-1 shadow-xs hover:opacity-95 cursor-pointer" style="background:#c026d3">
                            📲 Fonepay
                        </button>
                    </form>
                    <form method="POST" action="{{ route('finance.payment.initiate') }}">
                        @csrf
                        <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                        <input type="hidden" name="gateway" value="stripe">
                        <button type="submit" class="w-full py-2.5 px-3 rounded-xl text-xs font-black text-white transition flex items-center justify-center gap-1 shadow-xs hover:opacity-95 cursor-pointer" style="background:#6366f1">
                            💳 Card
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: POS Cash Desk & Return Calculator Studio (5 Cols) --}}
        <div class="lg:col-span-5 space-y-6">

            <div class="bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 text-white rounded-3xl p-6 shadow-2xl border border-slate-700 space-y-6 relative overflow-hidden">

                {{-- Decorative background glow --}}
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-emerald-500/10 rounded-full blur-2xl"></div>

                <div class="flex justify-between items-center pb-4 border-b border-slate-700/80">
                    <span class="text-xs font-bold text-emerald-400 uppercase tracking-widest flex items-center gap-1.5">
                        <i class="bi bi-calculator-fill"></i> POS Cash Return Studio
                    </span>
                    <span class="text-[10px] bg-slate-700 text-slate-300 px-2 py-0.5 rounded-full font-bold">REAL-TIME</span>
                </div>

                {{-- Payable Display --}}
                <div class="bg-slate-800/80 rounded-2xl p-4 border border-slate-700 text-center">
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest block mb-1">Net Payable Amount</span>
                    <div class="text-3xl font-black text-emerald-400 tracking-tight" id="display_net_payable">
                        Rs. {{ number_format($amountDue, 2) }}
                    </div>
                </div>

                {{-- Cash Received Input --}}
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                        Cash Received / Tendered (Rs.)
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-3.5 text-slate-400 font-bold text-sm">Rs.</span>
                        <input type="number" id="input_cash_tendered" placeholder="0.00" step="10"
                               class="w-full bg-slate-950 border-2 border-emerald-500/50 rounded-2xl pl-12 pr-4 py-3 text-2xl font-black text-white focus:outline-none focus:border-emerald-400 transition shadow-inner">
                    </div>
                </div>

                {{-- Quick Currency Bill Preset Buttons --}}
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">Quick Currency Notes Presets</span>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" onclick="setCashPreset(0)"
                                class="py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl text-xs font-bold text-emerald-400 transition">
                            Exact Cash
                        </button>
                        <button type="button" onclick="addCashPreset(500)"
                                class="py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl text-xs font-bold text-amber-300 transition">
                            + Rs. 500
                        </button>
                        <button type="button" onclick="addCashPreset(1000)"
                                class="py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl text-xs font-bold text-sky-300 transition">
                            + Rs. 1,000
                        </button>
                        <button type="button" onclick="addCashPreset(2000)"
                                class="py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl text-xs font-bold text-purple-300 transition">
                            + Rs. 2,000
                        </button>
                        <button type="button" onclick="addCashPreset(5000)"
                                class="py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl text-xs font-bold text-emerald-300 transition">
                            + Rs. 5,000
                        </button>
                        <button type="button" onclick="clearCashPreset()"
                                class="py-2 bg-rose-950/60 hover:bg-rose-900 border border-rose-800 rounded-xl text-xs font-bold text-rose-300 transition">
                            Clear
                        </button>
                    </div>
                </div>

                {{-- Real-Time Cash Change Return Box --}}
                <div id="change_result_card" class="rounded-2xl p-5 border transition-all text-center space-y-2 bg-slate-800/80 border-slate-700">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block" id="change_status_label">Cash Return Status</span>
                    <div class="text-3xl font-black tracking-tight text-slate-300" id="display_change_amount">
                        Rs. 0.00
                    </div>
                    <p class="text-xs text-slate-400" id="display_change_message">Enter cash received amount to calculate change return.</p>
                    <div id="denomination_breakdown" class="text-xs font-mono text-emerald-300 pt-2 border-t border-slate-700/60 hidden"></div>
                </div>

                <div class="text-center pt-2">
                    <p class="text-[10px] text-slate-400">
                        <i class="bi bi-shield-lock-fill me-1 text-emerald-400"></i> Instant automated receipt generation with full ledger recording
                    </p>
                </div>
            </div>

        </div>

    </div>

</div>

@endsection

@push('scripts')
<script>
(function() {
    const inputPayable = document.getElementById('input_payable_amount');
    const inputCash = document.getElementById('input_cash_tendered');
    const displayPayable = document.getElementById('display_net_payable');
    const changeCard = document.getElementById('change_result_card');
    const changeLabel = document.getElementById('change_status_label');
    const changeDisplay = document.getElementById('display_change_amount');
    const changeMessage = document.getElementById('display_change_message');
    const denomBreakdown = document.getElementById('denomination_breakdown');

    function calculatePOSChange() {
        const payable = parseFloat(inputPayable.value) || 0;
        const tendered = parseFloat(inputCash.value) || 0;

        displayPayable.textContent = 'Rs. ' + payable.toLocaleString('en-US', { minimumFractionDigits: 2 });

        if (!inputCash.value || tendered === 0) {
            changeCard.className = 'rounded-2xl p-5 border transition-all text-center space-y-2 bg-slate-800/80 border-slate-700';
            changeLabel.textContent = 'CASH RETURN STATUS';
            changeDisplay.className = 'text-3xl font-black tracking-tight text-slate-400';
            changeDisplay.textContent = 'Rs. 0.00';
            changeMessage.textContent = 'Enter cash received amount to calculate change return.';
            denomBreakdown.classList.add('hidden');
            return;
        }

        const change = tendered - payable;

        if (change > 0) {
            // CASH RETURN NEEDED
            changeCard.className = 'rounded-2xl p-5 border-2 transition-all text-center space-y-2 bg-gradient-to-b from-emerald-950 to-slate-900 border-emerald-400 shadow-lg shadow-emerald-900/30';
            changeLabel.className = 'text-[10px] font-bold text-emerald-400 uppercase tracking-widest block';
            changeLabel.textContent = 'CHANGE TO RETURN TO PARENT / STUDENT';
            changeDisplay.className = 'text-4xl font-black tracking-tight text-emerald-400 animate-pulse';
            changeDisplay.textContent = 'Rs. ' + change.toLocaleString('en-US', { minimumFractionDigits: 2 });
            changeMessage.textContent = 'Hand Rs. ' + change.toLocaleString() + ' change back to customer.';

            // Calculate Nepali Rupee Denomination Breakdown
            let rem = Math.floor(change);
            const notes = [1000, 500, 100, 50, 20, 10, 5];
            const list = [];
            notes.forEach(n => {
                const count = Math.floor(rem / n);
                if (count > 0) {
                    list.push(`${count} &times; Rs. ${n}`);
                    rem %= n;
                }
            });
            if (list.length > 0) {
                denomBreakdown.innerHTML = '<strong>Suggested Change Notes:</strong> ' + list.join(' &middot; ');
                denomBreakdown.classList.remove('hidden');
            } else {
                denomBreakdown.classList.add('hidden');
            }

        } else if (change === 0) {
            // EXACT AMOUNT
            changeCard.className = 'rounded-2xl p-5 border-2 transition-all text-center space-y-2 bg-slate-800 border-sky-400';
            changeLabel.className = 'text-[10px] font-bold text-sky-400 uppercase tracking-widest block';
            changeLabel.textContent = 'EXACT CASH RECEIVED';
            changeDisplay.className = 'text-3xl font-black tracking-tight text-sky-400';
            changeDisplay.textContent = 'Rs. 0.00';
            changeMessage.textContent = 'Exact cash received — zero change required.';
            denomBreakdown.classList.add('hidden');
        } else {
            // SHORT CASH / DUE REMAINING
            const short = Math.abs(change);
            changeCard.className = 'rounded-2xl p-5 border-2 transition-all text-center space-y-2 bg-slate-900 border-amber-500';
            changeLabel.className = 'text-[10px] font-bold text-amber-400 uppercase tracking-widest block';
            changeLabel.textContent = 'PARTIAL CASH RECEIVED';
            changeDisplay.className = 'text-3xl font-black tracking-tight text-amber-400';
            changeDisplay.textContent = '- Rs. ' + short.toLocaleString('en-US', { minimumFractionDigits: 2 });
            changeMessage.textContent = 'Cash tendered is short by Rs. ' + short.toLocaleString() + '.';
            denomBreakdown.classList.add('hidden');
        }
    }

    inputPayable?.addEventListener('input', calculatePOSChange);
    inputCash?.addEventListener('input', calculatePOSChange);

    window.setCashPreset = function(amt) {
        if (amt === 0) {
            inputCash.value = inputPayable.value;
        } else {
            inputCash.value = amt;
        }
        calculatePOSChange();
    };

    window.addCashPreset = function(amt) {
        const cur = parseFloat(inputCash.value) || 0;
        inputCash.value = cur + amt;
        calculatePOSChange();
    };

    window.clearCashPreset = function() {
        inputCash.value = '';
        calculatePOSChange();
    };
})();
</script>
@endpush
