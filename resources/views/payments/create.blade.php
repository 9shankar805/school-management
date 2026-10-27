@extends('layouts.app')

@section('content')

<style>
.fintech-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
    transition: all 0.2s ease;
}
html.dark .fintech-card,
[data-theme="dark"] .fintech-card {
    background: #0f172a !important;
    border-color: #1e293b !important;
    box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.4) !important;
}
.fintech-hero-box {
    background: linear-gradient(135deg, #1e1b4b 0%, #312e81 60%, #4338ca 100%);
    border-radius: 16px;
    padding: 16px 20px;
    position: relative;
    overflow: hidden;
    color: #ffffff;
    box-shadow: 0 10px 25px -5px rgba(49, 46, 129, 0.35);
}
.fintech-hero-box::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 140px;
    height: 140px;
    background: radial-gradient(circle, rgba(52, 211, 153, 0.25) 0%, rgba(52, 211, 153, 0) 70%);
    border-radius: 50%;
    pointer-events: none;
}
.fintech-btn-preset {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #334155;
    font-size: 11px;
    font-weight: 700;
    padding: 8px 6px;
    border-radius: 10px;
    transition: all 0.15s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}
.fintech-btn-preset:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
    transform: translateY(-1px);
    color: #0f172a;
}
.fintech-btn-preset:active {
    transform: scale(0.96);
}
html.dark .fintech-btn-preset,
[data-theme="dark"] .fintech-btn-preset {
    background: #1e293b;
    border-color: #334155;
    color: #cbd5e1;
}
html.dark .fintech-btn-preset:hover,
[data-theme="dark"] .fintech-btn-preset:hover {
    background: #27354f;
    border-color: #475569;
    color: #ffffff;
}
.fintech-submit-btn {
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 50%, #4338ca 100%);
    box-shadow: 0 8px 20px -4px rgba(79, 70, 229, 0.4);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.fintech-submit-btn:hover {
    box-shadow: 0 12px 28px -4px rgba(79, 70, 229, 0.55);
    transform: translateY(-1.5px);
}
.fintech-submit-btn:active {
    transform: scale(0.98);
}
</style>

<div class="flex-1 p-4 sm:p-6 lg:p-8 overflow-auto bg-slate-50/60 dark:bg-slate-950 min-h-screen">

    {{-- Breadcrumb & Title Header --}}
    <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
        <div>
            <nav class="text-xs text-slate-400 mb-1 flex items-center gap-1.5 font-medium">
                <a href="{{ route('payments.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition">Payments & Finance</a>
                <span class="text-slate-300 dark:text-slate-700">/</span>
                <span class="text-slate-600 dark:text-slate-300 font-semibold">POS Billing Terminal</span>
            </nav>
            <h1 class="text-2xl font-black text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-2.5">
                <span>💳</span> Fee Collection & POS Billing Terminal
            </h1>
            <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">Streamlined instant fee settlement, ledger dues integration & receipt counter</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('payments.index') }}"
               class="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 shadow-sm transition inline-flex items-center gap-1.5">
                <i class="bi bi-arrow-left"></i> Back to Invoices
            </a>
        </div>
    </div>

    @include('session-messages')

    {{-- MAIN FORM CONTAINER --}}
    <form action="{{ route('payments.store') }}" method="POST" id="posBillingForm">
        @csrf
        <input type="hidden" name="student_id" id="form_student_id">

        {{-- 2-COLUMN UNIFIED POS STUDIO GRID --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- LEFT COLUMN: STUDENT & ITEMIZED FEE BUILDER (7 Cols) --}}
            <div class="lg:col-span-7 space-y-5">

                {{-- 1. STUDENT SELECTION & LEDGER --}}
                <div class="fintech-card p-5 sm:p-6 space-y-4">
                    <div class="flex items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-xs font-black shadow-xs">
                                1
                            </span>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100">Student & Academic Ledger</h2>
                                <p class="text-[11px] text-slate-400">Select a student to load outstanding dues and history</p>
                            </div>
                        </div>
                        <div id="student_dues_summary_pill" class="hidden">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-900/60 flex items-center gap-1.5">
                                <i class="bi bi-exclamation-circle-fill text-rose-500"></i> Dues: <span id="summary_dues_text" class="font-black">Rs. 0.00</span>
                            </span>
                        </div>
                    </div>

                    {{-- Student Dropdown & Actions --}}
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <select name="student_id_picker" required id="select_student" onchange="autoLoadStudentFees(this.value)"
                                        class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-xs font-bold bg-slate-50/60 dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition appearance-none cursor-pointer">
                                    <option value="">— Select Student ({{ $students->count() }} Students Loaded) —</option>
                                    @foreach($students as $st)
                                    <option value="{{ $st->id }}" {{ old('student_id') == $st->id ? 'selected' : '' }}>
                                        {{ $st->first_name }} {{ $st->last_name }} ({{ $st->email }})
                                    </option>
                                    @endforeach
                                </select>
                                <div class="absolute right-3.5 top-3 pointer-events-none text-slate-400 text-xs">
                                    <i class="bi bi-chevron-down"></i>
                                </div>
                            </div>
                            <button type="button" onclick="selectAllUnpaidMonths(true)" title="Select all unpaid ledger months"
                                    class="px-3 py-2.5 bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-100 text-indigo-700 dark:text-indigo-300 border border-indigo-200/70 dark:border-indigo-900/50 rounded-xl text-xs font-bold transition flex items-center gap-1 whitespace-nowrap">
                                <i class="bi bi-check2-all"></i> <span class="hidden sm:inline">Select All Dues</span>
                            </button>
                            <button type="button" onclick="selectAllUnpaidMonths(false)" title="Clear selection"
                                    class="px-3 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Auto-Loaded Month Cards --}}
                    <div id="student_fee_breakdown_card" class="hidden rounded-xl border border-indigo-100 dark:border-indigo-950 bg-indigo-50/30 dark:bg-slate-900/50 p-3.5 space-y-2.5">
                        <div class="flex items-center justify-between pb-2 border-b border-indigo-100/70 dark:border-slate-800">
                            <span class="text-xs font-bold text-indigo-950 dark:text-indigo-300 flex items-center gap-1.5">
                                <i class="bi bi-calendar3 text-indigo-600"></i> Monthly Unpaid Invoices:
                            </span>
                            <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400" id="student_info_subtitle">Loading fees...</span>
                        </div>
                        <div id="monthly_invoices_list" class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-[180px] overflow-y-auto pr-1"></div>
                    </div>
                </div>

                {{-- 2. ITEMIZED FEE LINE ITEMS BUILDER --}}
                <div class="fintech-card p-5 sm:p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-xs font-black shadow-xs">
                                2
                            </span>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100">Fee Line Items & Summary</h2>
                                <p class="text-[11px] text-slate-400">Configure fee components and invoice summary</p>
                            </div>
                        </div>
                        <button type="button" onclick="addExtraFeeRow('', 0)"
                                class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 flex items-center gap-1 bg-indigo-50 dark:bg-indigo-950/60 px-3 py-1.5 rounded-xl border border-indigo-100 dark:border-indigo-900/50 transition">
                            <i class="bi bi-plus-circle"></i> Add Custom Row
                        </button>
                    </div>

                    {{-- Quick 1-Click Component Chips --}}
                    <div>
                        <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">⚡ Quick 1-Click Fee Presets</span>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" onclick="addExtraFeeRow('Monthly Tuition Fee', 3500)"
                                    class="px-3 py-1 bg-indigo-50 dark:bg-indigo-950/40 hover:bg-indigo-100 text-indigo-700 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-900/50 rounded-lg text-xs font-bold transition flex items-center gap-1">
                                <i class="bi bi-plus text-indigo-500 font-bold"></i> Tuition (3,500)
                            </button>
                            <button type="button" onclick="addExtraFeeRow('Term Examination Fee', 1200)"
                                    class="px-3 py-1 bg-amber-50 dark:bg-amber-950/40 hover:bg-amber-100 text-amber-800 dark:text-amber-300 border border-amber-200/60 dark:border-amber-900/50 rounded-lg text-xs font-bold transition flex items-center gap-1">
                                <i class="bi bi-plus text-amber-500 font-bold"></i> Exam (1,200)
                            </button>
                            <button type="button" onclick="addExtraFeeRow('School Bus Transport Fee', 1800)"
                                    class="px-3 py-1 bg-sky-50 dark:bg-sky-950/40 hover:bg-sky-100 text-sky-800 dark:text-sky-300 border border-sky-200/60 dark:border-sky-900/50 rounded-lg text-xs font-bold transition flex items-center gap-1">
                                <i class="bi bi-plus text-sky-500 font-bold"></i> Bus (1,800)
                            </button>
                            <button type="button" onclick="addExtraFeeRow('Hostel Accommodation Fee', 4500)"
                                    class="px-3 py-1 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 text-emerald-800 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-900/50 rounded-lg text-xs font-bold transition flex items-center gap-1">
                                <i class="bi bi-plus text-emerald-500 font-bold"></i> Hostel (4,500)
                            </button>
                            <button type="button" onclick="addExtraFeeRow('Annual Admission Fee', 5000)"
                                    class="px-3 py-1 bg-purple-50 dark:bg-purple-950/40 hover:bg-purple-100 text-purple-800 dark:text-purple-300 border border-purple-200/60 dark:border-purple-900/50 rounded-lg text-xs font-bold transition flex items-center gap-1">
                                <i class="bi bi-plus text-purple-500 font-bold"></i> Admission (5,000)
                            </button>
                        </div>
                    </div>

                    {{-- ITEMIZED FEE TABLE CONTAINER --}}
                    <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
                        <div class="bg-slate-50 dark:bg-slate-800/80 px-4 py-2 flex items-center justify-between text-[11px] font-bold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider">
                            <span class="flex-1">Fee Component Name</span>
                            <span class="w-36 text-right pr-9">Amount (NPR)</span>
                        </div>
                        <div id="fee_items_container" class="divide-y divide-slate-100 dark:divide-slate-800 p-2 space-y-2 max-h-[240px] overflow-y-auto">
                            {{-- Default initial row --}}
                            <div class="fee-item-row flex items-center gap-2">
                                <input type="text" name="items[0][name]" value="Monthly Tuition Fee" placeholder="e.g. Monthly Tuition Fee" required
                                       class="item-name-input flex-1 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs font-bold bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
                                       oninput="recalculateFeeItemsTotal()">
                                <div class="w-36 relative">
                                    <span class="absolute left-3 top-2 text-slate-400 text-xs font-bold">Rs.</span>
                                    <input type="number" step="0.01" name="items[0][amount]" value="3500" placeholder="0.00" required
                                           class="item-amount-input w-full border border-slate-200 dark:border-slate-700 rounded-xl pl-9 pr-3 py-2 text-xs font-black bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-right focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
                                           oninput="recalculateFeeItemsTotal()">
                                </div>
                                <button type="button" onclick="removeFeeItemRow(this)" title="Remove item" class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 hover:bg-rose-100 flex items-center justify-center text-xs transition">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- INVOICE TITLE & DUE DATE ROW --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Combined Invoice Title <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="title" id="input_title" value="{{ old('title', 'Monthly Tuition Fee') }}" required placeholder="e.g. Monthly Tuition Fee"
                                   class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs font-bold bg-slate-50/60 dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Due Date
                            </label>
                            <input type="date" name="due_date" value="{{ old('due_date', now()->addDays(15)->toDateString()) }}"
                                   class="w-full border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-semibold bg-slate-50/60 dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                        </div>
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN: ULTRA-MODERN FINTECH CHECKOUT STUDIO (5 Cols) --}}
            <div class="lg:col-span-5 lg:sticky lg:top-6 space-y-4">

                <div class="fintech-card p-5 sm:p-6 space-y-4 relative overflow-hidden">

                    {{-- Header Row --}}
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-xl bg-emerald-500 text-slate-950 flex items-center justify-center text-xs font-black shadow-xs">
                                3
                            </span>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 dark:text-slate-100 tracking-tight">Payment & Settlement</h3>
                                <p class="text-[11px] text-slate-400">Choose collection mode and settle transaction</p>
                            </div>
                        </div>
                        <span class="text-[10px] bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/80 px-2.5 py-0.5 rounded-full font-black tracking-wider uppercase flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Live Desk
                        </span>
                    </div>

                    {{-- Collection Status Segmented Toggle --}}
                    <div class="space-y-1.5">
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Collection Status</span>
                        <div class="grid grid-cols-2 gap-1.5 p-1 bg-slate-100 dark:bg-slate-800/80 rounded-xl border border-slate-200 dark:border-slate-700">
                            <label class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg cursor-pointer transition text-xs font-black text-slate-600 dark:text-slate-300 hover:text-slate-900 has-checked:bg-white dark:has-checked:bg-slate-700 has-checked:text-emerald-600 dark:has-checked:text-emerald-400 has-checked:shadow-sm">
                                <input type="radio" name="payment_status" value="paid" checked onchange="togglePaymentMethodBox(true)" class="hidden">
                                <i class="bi bi-check-circle-fill text-emerald-500"></i> Record Paid
                            </label>
                            <label class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg cursor-pointer transition text-xs font-black text-slate-600 dark:text-slate-300 hover:text-slate-900 has-checked:bg-white dark:has-checked:bg-slate-700 has-checked:text-rose-600 dark:has-checked:text-rose-400 has-checked:shadow-sm">
                                <input type="radio" name="payment_status" value="unpaid" onchange="togglePaymentMethodBox(false)" class="hidden">
                                <i class="bi bi-clock-history text-rose-500"></i> Bill as Unpaid
                            </label>
                        </div>
                    </div>

                    {{-- Hero Fintech Amount Display Card --}}
                    <div class="fintech-hero-box text-center">
                        <span class="text-[10px] font-extrabold text-indigo-200 uppercase tracking-widest block mb-1">Total Fee Amount Payable</span>
                        <div class="text-3xl sm:text-4xl font-black text-emerald-300 tracking-tight" id="display_net_payable">
                            Rs. 3,500.00
                        </div>
                        <input type="hidden" name="amount" id="input_payable_amount" value="{{ old('amount', '3500') }}">
                    </div>

                    {{-- Payment Method Selection Grid --}}
                    <div id="payment_method_container" class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                            Payment Method <span class="text-rose-500">*</span>
                        </label>
                        <select name="payment_method" id="select_payment_method"
                                class="w-full bg-slate-50/70 dark:bg-slate-800 text-slate-900 dark:text-slate-100 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition cursor-pointer">
                            <option value="cash" selected>💵 Cash Counter (with Change Desk)</option>
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

                    {{-- Cash Received Input & 3x2 Modern Preset Grid --}}
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                Cash Received / Tendered
                            </label>
                            <span class="text-[10px] font-bold text-slate-400">Nepali Rupee (NPR)</span>
                        </div>
                        <div class="relative">
                            <span class="absolute left-3.5 top-2.5 text-slate-400 font-black text-xs">Rs.</span>
                            <input type="number" id="input_cash_tendered" placeholder="0.00" step="10"
                                   class="w-full bg-slate-50/60 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 focus:border-indigo-500 dark:focus:border-indigo-400 rounded-xl pl-10 pr-3.5 py-2 text-base font-black text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition">
                        </div>

                        {{-- 3x2 Sleek Currency Preset Grid --}}
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px;" class="pt-0.5">
                            <button type="button" onclick="setCashPreset(0)" class="fintech-btn-preset">
                                <span class="font-black text-indigo-600 dark:text-indigo-400 me-1">⚡</span> Exact
                            </button>
                            <button type="button" onclick="addCashPreset(500)" class="fintech-btn-preset">
                                + Rs. 500
                            </button>
                            <button type="button" onclick="addCashPreset(1000)" class="fintech-btn-preset">
                                + Rs. 1,000
                            </button>
                            <button type="button" onclick="addCashPreset(2000)" class="fintech-btn-preset">
                                + Rs. 2,000
                            </button>
                            <button type="button" onclick="addCashPreset(5000)" class="fintech-btn-preset">
                                + Rs. 5,000
                            </button>
                            <button type="button" onclick="clearCashPreset()" class="fintech-btn-preset text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 hover:border-rose-200">
                                <i class="bi bi-x-circle me-1"></i> Clear
                            </button>
                        </div>
                    </div>

                    {{-- Real-Time Dynamic Cash Change Return Box --}}
                    <div id="change_result_card" class="rounded-xl p-3.5 border transition-all text-center space-y-1 bg-slate-50 dark:bg-slate-800/70 border-slate-200 dark:border-slate-700">
                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block" id="change_status_label">Cash Return Status</span>
                        <div class="text-xl font-black tracking-tight text-slate-700 dark:text-slate-300" id="display_change_amount">
                            Rs. 0.00
                        </div>
                        <p class="text-[11px] text-slate-400" id="display_change_message">Enter cash received to calculate return amount.</p>
                        <div id="denomination_breakdown" class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 pt-1.5 border-t border-slate-200 dark:border-slate-700 hidden"></div>
                    </div>

                    {{-- SUBMIT PRIMARY BUTTON --}}
                    <div class="pt-2">
                        <button type="submit"
                                class="fintech-submit-btn w-full py-3.5 px-4 text-white rounded-xl text-sm font-black flex items-center justify-center gap-2 cursor-pointer">
                            <i class="bi bi-check2-circle text-lg"></i> Settle Payment & Print Receipt
                        </button>
                    </div>

                    <div class="text-center pt-0.5">
                        <p class="text-[10px] text-slate-400 font-semibold flex items-center justify-center gap-1">
                            <i class="bi bi-shield-check text-emerald-500"></i> Certified School POS Financial Transaction
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
        row.className = 'fee-item-row flex items-center gap-1.5 animate-fade-in';
        row.innerHTML = `
            <input type="text" name="items[${index}][name]" value="${name}" placeholder="Fee Item Name" required
                   class="item-name-input flex-1 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-1.5 text-xs font-bold bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
                   oninput="recalculateFeeItemsTotal()">
            <div class="w-32 relative">
                <span class="absolute left-2.5 top-1.5 text-slate-400 text-xs font-bold">Rs.</span>
                <input type="number" step="0.01" name="items[${index}][amount]" value="${amount}" placeholder="0.00" required
                       class="item-amount-input w-full border border-slate-200 dark:border-slate-700 rounded-lg pl-8 pr-2.5 py-1.5 text-xs font-black bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-right focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
                       oninput="recalculateFeeItemsTotal()">
            </div>
            <button type="button" onclick="removeFeeItemRow(this)" title="Remove item" class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 hover:bg-rose-100 flex items-center justify-center text-xs transition">
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
            changeCard.className = 'rounded-xl p-3.5 border transition-all text-center space-y-1 bg-slate-50 dark:bg-slate-800/70 border-slate-200 dark:border-slate-700';
            changeLabel.className = 'text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block';
            changeLabel.textContent = 'CASH RETURN STATUS';
            changeDisplay.className = 'text-xl font-extrabold tracking-tight text-slate-700 dark:text-slate-300';
            changeDisplay.textContent = 'Rs. 0.00';
            changeMessage.textContent = 'Enter cash received to calculate return amount.';
            denomBreakdown.classList.add('hidden');
            return;
        }

        const change = tendered - payable;

        if (change > 0) {
            changeCard.className = 'rounded-xl p-3.5 border-2 transition-all text-center space-y-1 bg-emerald-50/70 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-700 shadow-xs';
            changeLabel.className = 'text-[10px] font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-wider block';
            changeLabel.textContent = 'CHANGE TO RETURN';
            changeDisplay.className = 'text-2xl font-black tracking-tight text-emerald-700 dark:text-emerald-300';
            changeDisplay.textContent = 'Rs. ' + change.toLocaleString('en-US', { minimumFractionDigits: 2 });
            changeMessage.textContent = 'Hand Rs. ' + change.toLocaleString() + ' change back to student / parent.';

            // Calculate Nepali Rupee Denomination Breakdown
            let rem = Math.floor(change);
            const notes = [1000, 500, 100, 50, 20, 10, 5];
            const list = [];
            notes.forEach(n => {
                const count = Math.floor(rem / n);
                if (count > 0) {
                    list.push(`${count} × Rs. ${n}`);
                    rem %= n;
                }
            });
            if (list.length > 0) {
                denomBreakdown.innerHTML = '<span class="font-bold text-emerald-800 dark:text-emerald-300">Suggested Notes:</span> ' + list.join(' · ');
                denomBreakdown.classList.remove('hidden');
            } else {
                denomBreakdown.classList.add('hidden');
            }

        } else if (change === 0) {
            changeCard.className = 'rounded-xl p-3.5 border-2 transition-all text-center space-y-1 bg-sky-50/70 dark:bg-sky-950/40 border-sky-300 dark:border-sky-700';
            changeLabel.className = 'text-[10px] font-bold text-sky-800 dark:text-sky-300 uppercase tracking-wider block';
            changeLabel.textContent = 'EXACT CASH RECEIVED';
            changeDisplay.className = 'text-2xl font-black tracking-tight text-sky-700 dark:text-sky-300';
            changeDisplay.textContent = 'Rs. 0.00';
            changeMessage.textContent = 'Exact cash received — zero change required.';
            denomBreakdown.classList.add('hidden');
        } else {
            const short = Math.abs(change);
            changeCard.className = 'rounded-xl p-3.5 border-2 transition-all text-center space-y-1 bg-amber-50/70 dark:bg-amber-950/40 border-amber-300 dark:border-amber-700';
            changeLabel.className = 'text-[10px] font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wider block';
            changeLabel.textContent = 'PARTIAL CASH TENDERED';
            changeDisplay.className = 'text-2xl font-black tracking-tight text-amber-700 dark:text-amber-300';
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
