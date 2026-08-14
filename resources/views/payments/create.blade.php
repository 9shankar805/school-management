@extends('layouts.app')

@section('content')

<div class="flex-1 p-6 lg:p-8 overflow-auto bg-slate-50/50 dark:bg-slate-900/50">

    {{-- Breadcrumb & Title Header --}}
    <nav class="text-xs text-slate-400 mb-3 flex items-center gap-1.5 font-medium">
        <a href="{{ route('payments.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">Payments & Finance</a>
        <i class="bi bi-chevron-right text-slate-300 text-[10px]"></i>
        <span class="text-slate-600 dark:text-slate-300 font-semibold">POS Billing Terminal</span>
    </nav>

    <div class="flex flex-wrap justify-between items-center mb-6 gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300 flex items-center gap-1.5 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-indigo-500 animate-ping"></span> Enterprise POS Terminal
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                    Genesis Global School Fee Desk
                </span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                Fee Collection & POS Checkout Terminal
            </h1>
            <p class="text-slate-500 text-xs mt-0.5">Streamlined 3-step fee billing, auto-loaded student ledgers, itemized fee builder & POS return desk</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('payments.index') }}"
               class="px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/60 shadow-2xs transition flex items-center gap-2">
                <i class="bi bi-arrow-left"></i> Back to Invoices
            </a>
        </div>
    </div>

    {{-- MAIN FORM CONTAINER --}}
    <form action="{{ route('payments.store') }}" method="POST" id="posBillingForm" class="space-y-6">
        @csrf
        <input type="hidden" name="student_id" id="form_student_id">

        {{-- ── STEP 1: STUDENT SELECTION & AUTO-LOADED LEDGER HEADER CARD ── --}}
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 p-6 shadow-sm space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-700/80">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-sm font-bold shadow-xs">
                        1
                    </span>
                    <div>
                        <h2 class="text-sm font-black text-slate-800 dark:text-slate-100">Select Student & Auto-Load Ledger Dues</h2>
                        <p class="text-[11px] text-slate-400">Choose a student to fetch their unpaid monthly fees & ledger history</p>
                    </div>
                </div>
                <div id="student_dues_summary_pill" class="hidden">
                    <span class="px-3 py-1 rounded-full text-xs font-black bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800 flex items-center gap-1.5">
                        <i class="bi bi-exclamation-circle-fill text-rose-500"></i> Outstanding Dues: <span id="summary_dues_text">Rs. 0.00</span>
                    </span>
                </div>
            </div>

            {{-- Student Select Row --}}
            <div class="space-y-2">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider whitespace-nowrap">
                        Select Student <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="selectAllUnpaidMonths(true)"
                                class="px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 rounded-xl text-xs font-bold transition flex items-center gap-1">
                            <i class="bi bi-check2-all"></i> Select All Unpaid
                        </button>
                        <button type="button" onclick="selectAllUnpaidMonths(false)"
                                class="px-3 py-1.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600 rounded-xl text-xs font-bold transition flex items-center gap-1">
                            <i class="bi bi-x-lg"></i> Clear Selection
                        </button>
                    </div>
                </div>

                <div class="relative w-full">
                    <select name="student_id_picker" required id="select_student" onchange="autoLoadStudentFees(this.value)"
                            class="w-full border border-slate-200 dark:border-slate-600 rounded-2xl px-4 py-3.5 text-sm font-extrabold bg-slate-50/60 dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition appearance-none cursor-pointer">
                        <option value="">— Click here to select student (600 Students Loaded) —</option>
                        @foreach($students as $st)
                        <option value="{{ $st->id }}" {{ old('student_id') == $st->id ? 'selected' : '' }}>
                            {{ $st->first_name }} {{ $st->last_name }} ({{ $st->email }})
                        </option>
                        @endforeach
                    </select>
                    <div class="absolute right-4 top-4 pointer-events-none text-slate-400">
                        <i class="bi bi-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            {{-- AUTO-LOADED MONTH-BY-MONTH FEE CHIPS CONTAINER --}}
            <div id="student_fee_breakdown_card" class="hidden rounded-2xl border border-indigo-100 dark:border-indigo-900/50 bg-indigo-50/40 dark:bg-slate-900/50 p-4 space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-indigo-100 dark:border-slate-800">
                    <span class="text-xs font-black text-indigo-900 dark:text-indigo-300 flex items-center gap-1.5">
                        <i class="bi bi-calendar-range-fill text-indigo-600"></i> Student Monthly Unpaid Ledger Cards:
                    </span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400" id="student_info_subtitle">Loading fees...</span>
                </div>

                {{-- List of month cards --}}
                <div id="monthly_invoices_list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 max-h-[260px] overflow-y-auto pr-1"></div>
            </div>
        </div>

        {{-- ── 2-COLUMN SPLIT POS STUDIO GRID (STEPS 2 & 3) ────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-7 items-start">

            {{-- LEFT COLUMN: STEP 2 - ITEMIZED FEE BUILDER STUDIO (7 Cols) --}}
            <div class="lg:col-span-7 space-y-6">

                <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 p-6 shadow-sm space-y-5">

                    <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 dark:border-slate-700/80">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-sm font-bold shadow-xs">
                                2
                            </span>
                            <div>
                                <h2 class="text-sm font-black text-slate-800 dark:text-slate-100">Itemized Fee Line Items Builder</h2>
                                <p class="text-[11px] text-slate-400">Add, edit or append individual fee items to build the student invoice</p>
                            </div>
                        </div>
                        <button type="button" onclick="addExtraFeeRow('', 0)"
                                class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 flex items-center gap-1 bg-indigo-50 dark:bg-indigo-900/30 px-3 py-1.5 rounded-xl border border-indigo-100 dark:border-indigo-800 transition">
                            <i class="bi bi-plus-circle-fill"></i> Add Custom Fee Row
                        </button>
                    </div>

                    {{-- Quick Component Add Chips --}}
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">⚡ Quick 1-Click Component Chips</span>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" onclick="addExtraFeeRow('Monthly Tuition Fee', 3500)"
                                    class="px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800/60 rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-2xs">
                                <i class="bi bi-plus-lg text-indigo-500"></i> Tuition (+3,500)
                            </button>
                            <button type="button" onclick="addExtraFeeRow('Term Examination Fee', 1200)"
                                    class="px-3 py-1.5 bg-amber-50 dark:bg-amber-900/30 hover:bg-amber-100 dark:hover:bg-amber-900/50 text-amber-800 dark:text-amber-300 border border-amber-100 dark:border-amber-800/60 rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-2xs">
                                <i class="bi bi-plus-lg text-amber-500"></i> Exam (+1,200)
                            </button>
                            <button type="button" onclick="addExtraFeeRow('School Bus Transport Fee', 1800)"
                                    class="px-3 py-1.5 bg-sky-50 dark:bg-sky-900/30 hover:bg-sky-100 dark:hover:bg-sky-900/50 text-sky-800 dark:text-sky-300 border border-sky-100 dark:border-sky-800/60 rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-2xs">
                                <i class="bi bi-plus-lg text-sky-500"></i> Bus (+1,800)
                            </button>
                            <button type="button" onclick="addExtraFeeRow('Hostel Accommodation Fee', 4500)"
                                    class="px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/30 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 text-emerald-800 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-800/60 rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-2xs">
                                <i class="bi bi-plus-lg text-emerald-500"></i> Hostel (+4,500)
                            </button>
                            <button type="button" onclick="addExtraFeeRow('Annual Admission Fee', 5000)"
                                    class="px-3 py-1.5 bg-purple-50 dark:bg-purple-900/30 hover:bg-purple-100 dark:hover:bg-purple-900/50 text-purple-800 dark:text-purple-300 border border-purple-100 dark:border-purple-800/60 rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-2xs">
                                <i class="bi bi-plus-lg text-purple-500"></i> Admission (+5,000)
                            </button>
                        </div>
                    </div>

                    {{-- ITEMIZED FEE TABLE CONTAINER --}}
                    <div class="border border-slate-200 dark:border-slate-700/80 rounded-2xl overflow-hidden shadow-2xs">
                        <div class="bg-slate-100/70 dark:bg-slate-700/60 px-4 py-2.5 flex items-center justify-between text-xs font-black text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-700/80 uppercase tracking-wider">
                            <span class="flex-1">Fee Component Name</span>
                            <span class="w-36 text-right pr-12">Amount (Rs.)</span>
                        </div>
                        <div id="fee_items_container" class="divide-y divide-slate-100 dark:divide-slate-700/60 p-2 space-y-2">
                            {{-- Default initial row --}}
                            <div class="fee-item-row flex items-center gap-2">
                                <input type="text" name="items[0][name]" value="Monthly Tuition Fee" placeholder="e.g. Monthly Tuition Fee" required
                                       class="item-name-input flex-1 border border-slate-200 dark:border-slate-600 rounded-xl px-3.5 py-2 text-xs font-bold bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
                                       oninput="recalculateFeeItemsTotal()">
                                <div class="w-36 relative">
                                    <span class="absolute left-2.5 top-2 text-slate-400 text-xs font-bold">Rs.</span>
                                    <input type="number" step="0.01" name="items[0][amount]" value="3500" placeholder="0.00" required
                                           class="item-amount-input w-full border border-slate-200 dark:border-slate-600 rounded-xl pl-9 pr-3 py-2 text-xs font-black bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 text-right focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
                                           oninput="recalculateFeeItemsTotal()">
                                </div>
                                <button type="button" onclick="removeFeeItemRow(this)" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center text-sm transition">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- INVOICE META INPUTS --}}
                    <div class="space-y-4 pt-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                Combined Invoice Title / Summary <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="title" id="input_title" value="{{ old('title', 'Monthly Tuition Fee') }}" required placeholder="e.g. Monthly Tuition & Examination Fee"
                                   class="w-full border border-slate-200 dark:border-slate-600/80 rounded-2xl px-4 py-3 text-sm font-bold bg-slate-50/50 dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Total Payable Amount (Rs.) <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-4 top-3 text-slate-400 font-bold text-sm">Rs.</span>
                                    <input type="number" step="0.01" name="amount" id="input_payable_amount" value="{{ old('amount', '3500') }}" required readonly
                                           class="w-full border border-slate-200 dark:border-slate-600/80 rounded-2xl pl-12 pr-4 py-2.5 text-lg font-black bg-slate-100 dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 cursor-not-allowed">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                    Due Date
                                </label>
                                <input type="date" name="due_date" value="{{ old('due_date', now()->addDays(15)->toDateString()) }}"
                                       class="w-full border border-slate-200 dark:border-slate-600/80 rounded-2xl px-4 py-3 text-sm font-semibold bg-slate-50/50 dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: STEP 3 - PAYMENT DESK & CHECKOUT STUDIO (5 Cols) --}}
            <div class="lg:col-span-5 space-y-6">

                <div class="bg-gradient-to-b from-slate-950 via-slate-900 to-indigo-950 text-white rounded-3xl p-6 shadow-2xl border border-slate-800 space-y-6 relative overflow-hidden">

                    {{-- Ambient background glow --}}
                    <div class="absolute -top-12 -right-12 w-48 h-48 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-emerald-500 text-slate-950 flex items-center justify-center text-sm font-black shadow-xs">
                                3
                            </span>
                            <div>
                                <h3 class="text-sm font-black text-emerald-400 uppercase tracking-widest">POS Payment Checkout Desk</h3>
                                <p class="text-[10px] text-slate-400">Collect immediate payment or issue unpaid invoice</p>
                            </div>
                        </div>
                        <span class="text-[10px] bg-emerald-950 border border-emerald-500/40 text-emerald-300 px-2.5 py-0.5 rounded-full font-black tracking-wider">
                            LIVE DESK
                        </span>
                    </div>

                    {{-- Collection Mode Radio Selector --}}
                    <div class="bg-slate-900/90 rounded-2xl p-4 border border-slate-800 space-y-3">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Collection Mode</span>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center justify-center gap-2 p-2.5 rounded-xl border border-emerald-500/50 bg-emerald-950/30 text-emerald-300 font-extrabold text-xs cursor-pointer hover:bg-emerald-950/60 transition">
                                <input type="radio" name="payment_status" value="paid" checked onchange="togglePaymentMethodBox(true)" class="text-emerald-500 focus:ring-emerald-500">
                                🟢 Record Paid
                            </label>
                            <label class="flex items-center justify-center gap-2 p-2.5 rounded-xl border border-rose-800/50 bg-rose-950/30 text-rose-300 font-extrabold text-xs cursor-pointer hover:bg-rose-950/60 transition">
                                <input type="radio" name="payment_status" value="unpaid" onchange="togglePaymentMethodBox(false)" class="text-rose-500 focus:ring-rose-500">
                                🔴 Bill Unpaid
                            </label>
                        </div>
                    </div>

                    {{-- Payment Method Selection Grid --}}
                    <div id="payment_method_container" class="space-y-2">
                        <label class="block text-[10px] font-extrabold text-slate-300 uppercase tracking-wider">
                            Select Payment Method / Gateway <span class="text-rose-400">*</span>
                        </label>
                        <select name="payment_method" id="select_payment_method"
                                class="w-full bg-slate-900 text-white border-2 border-slate-700 rounded-2xl px-4 py-3 text-sm font-bold focus:outline-none focus:border-emerald-400 transition cursor-pointer">
                            <option value="cash" selected>💵 Cash Counter (with Return Desk)</option>
                            <option value="esewa">🟢 eSewa Mobile Wallet (Online)</option>
                            <option value="khalti">🟣 Khalti Wallet (Online)</option>
                            <option value="fonepay">📲 Fonepay Direct QR Code (Online)</option>
                            <option value="connectips">🏦 ConnectIPS NetBanking (Online)</option>
                            <option value="stripe">💳 Credit / Debit Card (Online Stripe)</option>
                            <option value="paypal">🅿️ PayPal (Online)</option>
                            <option value="bank">🏦 Direct Bank Transfer</option>
                            <option value="cheque">🧾 Cheque</option>
                        </select>
                    </div>

                    {{-- Payable Counter Box --}}
                    <div class="bg-slate-900/90 rounded-2xl p-4 border border-slate-800 text-center shadow-inner">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest block mb-1">Total Fee Amount Payable</span>
                        <div class="text-3xl font-black text-emerald-400 tracking-tight" id="display_net_payable">
                            Rs. 3,500.00
                        </div>
                    </div>

                    {{-- Cash Received Input --}}
                    <div>
                        <label class="block text-xs font-extrabold text-slate-300 uppercase tracking-wider mb-2">
                            Cash Received / Tendered (Rs.)
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-3.5 text-slate-500 font-bold text-sm">Rs.</span>
                            <input type="number" id="input_cash_tendered" placeholder="0.00" step="10"
                                   class="w-full bg-white text-slate-950 border-2 border-emerald-400 rounded-2xl pl-12 pr-4 py-3.5 text-2xl font-black focus:outline-none focus:ring-4 focus:ring-emerald-500/30 transition shadow-lg">
                        </div>
                    </div>

                    {{-- Quick Currency Bill Preset Buttons --}}
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">Quick Currency Presets</span>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" onclick="setCashPreset(0)"
                                    class="py-2.5 bg-slate-800/90 hover:bg-slate-700 border border-sky-500/40 rounded-xl text-xs font-black text-sky-300 transition shadow-xs">
                                Exact Cash
                            </button>
                            <button type="button" onclick="addCashPreset(500)"
                                    class="py-2.5 bg-slate-800/90 hover:bg-slate-700 border border-amber-500/40 rounded-xl text-xs font-black text-amber-300 transition shadow-xs">
                                + Rs. 500
                            </button>
                            <button type="button" onclick="addCashPreset(1000)"
                                    class="py-2.5 bg-slate-800/90 hover:bg-slate-700 border border-cyan-500/40 rounded-xl text-xs font-black text-cyan-300 transition shadow-xs">
                                + Rs. 1,000
                            </button>
                            <button type="button" onclick="addCashPreset(2000)"
                                    class="py-2.5 bg-slate-800/90 hover:bg-slate-700 border border-purple-500/40 rounded-xl text-xs font-black text-purple-300 transition shadow-xs">
                                + Rs. 2,000
                            </button>
                            <button type="button" onclick="addCashPreset(5000)"
                                    class="py-2.5 bg-slate-800/90 hover:bg-slate-700 border border-emerald-500/40 rounded-xl text-xs font-black text-emerald-300 transition shadow-xs">
                                + Rs. 5,000
                            </button>
                            <button type="button" onclick="clearCashPreset()"
                                    class="py-2.5 bg-rose-950/80 hover:bg-rose-900 border border-rose-800 rounded-xl text-xs font-black text-rose-300 transition shadow-xs">
                                Clear
                            </button>
                        </div>
                    </div>

                    {{-- Real-Time Cash Change Return Box --}}
                    <div id="change_result_card" class="rounded-2xl p-5 border transition-all text-center space-y-2 bg-slate-900/90 border-slate-800">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block" id="change_status_label">Cash Return Status</span>
                        <div class="text-3xl font-black tracking-tight text-slate-400" id="display_change_amount">
                            Rs. 0.00
                        </div>
                        <p class="text-xs text-slate-400" id="display_change_message">Enter amount & cash tendered to calculate change.</p>
                        <div id="denomination_breakdown" class="text-xs font-mono text-emerald-300 pt-2 border-t border-slate-800 hidden"></div>
                    </div>

                    {{-- SUBMIT PRIMARY BUTTON --}}
                    <div class="pt-2">
                        <button type="submit"
                                class="w-full py-4 bg-gradient-to-r from-emerald-500 via-teal-500 to-indigo-600 hover:from-emerald-600 hover:to-indigo-700 text-white rounded-2xl text-base font-black shadow-xl hover:shadow-2xl transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                            <i class="bi bi-check-circle-fill text-lg"></i> Issue Receipt & Collect Fee
                        </button>
                    </div>

                    <div class="text-center">
                        <p class="text-[10px] text-slate-400">
                            <i class="bi bi-shield-check text-emerald-400 me-1"></i> Certified POS Financial Transaction Counter
                        </p>
                    </div>
                </div>

            </div>

        </div>
    </form>

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

    let itemRowCounter = 1;
    let rawLoadedInvoices = [];

    window.addExtraFeeRow = function(name, amount) {
        const container = document.getElementById('fee_items_container');
        const index = itemRowCounter++;

        const row = document.createElement('div');
        row.className = 'fee-item-row flex items-center gap-2 animate-fade-in';
        row.innerHTML = `
            <input type="text" name="items[${index}][name]" value="${name}" placeholder="Fee Item Name" required
                   class="item-name-input flex-1 border border-slate-200 dark:border-slate-600 rounded-xl px-3.5 py-2 text-xs font-bold bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
                   oninput="recalculateFeeItemsTotal()">
            <div class="w-36 relative">
                <span class="absolute left-2.5 top-2 text-slate-400 text-xs font-bold">Rs.</span>
                <input type="number" step="0.01" name="items[${index}][amount]" value="${amount}" placeholder="0.00" required
                       class="item-amount-input w-full border border-slate-200 dark:border-slate-600 rounded-xl pl-9 pr-3 py-2 text-xs font-black bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 text-right focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
                       oninput="recalculateFeeItemsTotal()">
            </div>
            <button type="button" onclick="removeFeeItemRow(this)" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center text-sm transition">
                <i class="bi bi-trash-fill"></i>
            </button>
        `;
        container.appendChild(row);
        recalculateFeeItemsTotal();
    };

    window.removeFeeItemRow = function(btn) {
        const container = document.getElementById('fee_items_container');
        if (container.querySelectorAll('.fee-item-row').length > 1) {
            btn.closest('.fee-item-row').remove();
            recalculateFeeItemsTotal();
        } else {
            alert('At least one fee item row is required.');
        }
    };

    window.recalculateFeeItemsTotal = function() {
        const amountInputs = document.querySelectorAll('.item-amount-input');
        const nameInputs = document.querySelectorAll('.item-name-input');

        let sum = 0;
        const names = [];

        amountInputs.forEach((inp, idx) => {
            const val = parseFloat(inp.value) || 0;
            sum += val;
            const nm = nameInputs[idx]?.value?.trim();
            if (nm) names.push(nm);
        });

        inputPayable.value = sum;
        if (names.length > 0) {
            document.getElementById('input_title').value = names.length === 1 ? names[0] : names.slice(0, 2).join(' + ') + (names.length > 2 ? ' (+ ' + (names.length - 2) + ' more)' : '');
        }
        calculatePOSChange();
    };

    window.togglePaymentMethodBox = function(isPaid) {
        const box = document.getElementById('payment_method_container');
        if (isPaid) {
            box.classList.remove('opacity-40', 'pointer-events-none');
        } else {
            box.classList.add('opacity-40', 'pointer-events-none');
        }
    };

    window.autoLoadStudentFees = function(studentId) {
        document.getElementById('form_student_id').value = studentId;
        const card = document.getElementById('student_fee_breakdown_card');
        const list = document.getElementById('monthly_invoices_list');
        const subtitle = document.getElementById('student_info_subtitle');
        const duesPill = document.getElementById('student_dues_summary_pill');
        const duesText = document.getElementById('summary_dues_text');

        if (!studentId) {
            card.classList.add('hidden');
            duesPill.classList.add('hidden');
            return;
        }

        card.classList.remove('hidden');
        subtitle.textContent = 'Fetching student fee ledger...';
        list.innerHTML = '<div class="col-span-full text-xs text-slate-400 text-center py-4"><i class="bi bi-arrow-repeat animate-spin text-lg block mb-1"></i> Loading monthly fees...</div>';

        fetch('/payments/student/' + studentId + '/fees-json')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    subtitle.textContent = data.student.name + ' (' + data.student.class + ')';
                    const duesVal = 'Rs. ' + Number(data.total_outstanding).toLocaleString('en-US', { minimumFractionDigits: 2 });
                    duesText.textContent = duesVal;
                    duesPill.classList.remove('hidden');

                    rawLoadedInvoices = data.invoices;
                    renderMonthlyInvoices(data.invoices);
                }
            })
            .catch(err => {
                subtitle.textContent = 'Failed to load fee ledger';
                list.innerHTML = '<div class="col-span-full text-xs text-rose-500 p-2">Error loading student fees.</div>';
            });
    };

    function renderMonthlyInvoices(invoices) {
        const list = document.getElementById('monthly_invoices_list');
        list.innerHTML = '';

        if (!invoices || invoices.length === 0) {
            list.innerHTML = '<div class="col-span-full text-xs text-slate-400 text-center py-3">No fee records found for this student.</div>';
            return;
        }

        invoices.forEach(inv => {
            const isPaid = inv.status === 'paid';
            const itemBadges = inv.items.map(i => `<span class="px-1.5 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 font-bold me-1 text-[10px] border border-indigo-100 dark:border-indigo-900/50">${i.name} (Rs. ${Number(i.amount).toLocaleString()})</span>`).join('');

            const card = document.createElement('div');
            card.className = `p-3 rounded-2xl border transition flex flex-col justify-between space-y-2 ${isPaid ? 'bg-emerald-50/40 dark:bg-emerald-950/20 border-emerald-200/80 dark:border-emerald-900/40 opacity-75' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700/80 shadow-xs hover:border-indigo-300'}`;

            card.innerHTML = `
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <input type="checkbox" class="fee-month-checkbox w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 ${isPaid ? 'hidden' : ''}"
                               data-id="${inv.id}" data-due="${inv.due}" data-title="${inv.title}"
                               onchange="updateCalculatedTotalFromCheckboxes()" ${!isPaid ? 'checked' : ''}>
                        <span class="text-xs font-black text-slate-800 dark:text-slate-100 truncate">${inv.title}</span>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase ${isPaid ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300'}">${inv.status}</span>
                </div>
                <div class="flex flex-wrap gap-1">${itemBadges}</div>
                <div class="flex items-center justify-between pt-1 border-t border-slate-100 dark:border-slate-700/60 text-[10px]">
                    <span class="text-slate-400">Due ${inv.due_date}</span>
                    <span class="font-black ${isPaid ? 'text-emerald-600' : 'text-rose-600 dark:text-rose-400'}">Rs. ${Number(inv.due).toLocaleString('en-US', { minimumFractionDigits: 2 })}</span>
                </div>
            `;
            list.appendChild(card);
        });

        updateCalculatedTotalFromCheckboxes();
    }

    window.selectAllUnpaidMonths = function(check) {
        const checkboxes = document.querySelectorAll('.fee-month-checkbox');
        checkboxes.forEach(cb => cb.checked = check);
        updateCalculatedTotalFromCheckboxes();
    };

    window.updateCalculatedTotalFromCheckboxes = function() {
        const checkboxes = document.querySelectorAll('.fee-month-checkbox:checked');
        const container = document.getElementById('fee_items_container');
        container.innerHTML = '';
        itemRowCounter = 0;

        checkboxes.forEach(cb => {
            const invId = cb.getAttribute('data-id');
            const inv = rawLoadedInvoices.find(i => i.id == invId);
            if (inv && inv.items && inv.items.length > 0) {
                inv.items.forEach(i => {
                    addExtraFeeRow(i.name, i.amount);
                });
            } else {
                addExtraFeeRow(cb.getAttribute('data-title'), cb.getAttribute('data-due'));
            }
        });

        if (checkboxes.length === 0) {
            addExtraFeeRow('Monthly Tuition Fee', 3500);
        }
    };

    function calculatePOSChange() {
        const payable = parseFloat(inputPayable.value) || 0;
        const tendered = parseFloat(inputCash.value) || 0;

        displayPayable.textContent = 'Rs. ' + payable.toLocaleString('en-US', { minimumFractionDigits: 2 });

        if (!inputCash.value || tendered === 0) {
            changeCard.className = 'rounded-2xl p-5 border transition-all text-center space-y-2 bg-slate-900/90 border-slate-800';
            changeLabel.textContent = 'CASH RETURN STATUS';
            changeDisplay.className = 'text-3xl font-black tracking-tight text-slate-400';
            changeDisplay.textContent = 'Rs. 0.00';
            changeMessage.textContent = 'Enter cash received amount to calculate change return.';
            denomBreakdown.classList.add('hidden');
            return;
        }

        const change = tendered - payable;

        if (change > 0) {
            changeCard.className = 'rounded-2xl p-5 border-2 transition-all text-center space-y-2 bg-gradient-to-b from-emerald-950 to-slate-950 border-emerald-400 shadow-xl shadow-emerald-950/50';
            changeLabel.className = 'text-[10px] font-extrabold text-emerald-400 uppercase tracking-widest block';
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
                denomBreakdown.innerHTML = '<strong>Suggested Change:</strong> ' + list.join(' &middot; ');
                denomBreakdown.classList.remove('hidden');
            } else {
                denomBreakdown.classList.add('hidden');
            }

        } else if (change === 0) {
            changeCard.className = 'rounded-2xl p-5 border-2 transition-all text-center space-y-2 bg-slate-900 border-sky-400';
            changeLabel.className = 'text-[10px] font-extrabold text-sky-400 uppercase tracking-widest block';
            changeLabel.textContent = 'EXACT CASH RECEIVED';
            changeDisplay.className = 'text-3xl font-black tracking-tight text-sky-400';
            changeDisplay.textContent = 'Rs. 0.00';
            changeMessage.textContent = 'Exact cash received — zero change required.';
            denomBreakdown.classList.add('hidden');
        } else {
            const short = Math.abs(change);
            changeCard.className = 'rounded-2xl p-5 border-2 transition-all text-center space-y-2 bg-slate-950 border-amber-500';
            changeLabel.className = 'text-[10px] font-extrabold text-amber-400 uppercase tracking-widest block';
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
