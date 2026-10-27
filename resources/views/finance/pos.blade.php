@extends('layouts.app')

@section('content')
<style>
.pos-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
html.dark .pos-card,
[data-theme="dark"] .pos-card {
    background: #0f172a !important;
    border-color: #1e293b !important;
    box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.4) !important;
}
.pos-hero-box {
    background: linear-gradient(135deg, #0d7c66 0%, #00a884 60%, #059669 100%);
    border-radius: 18px;
    padding: 18px 20px;
    position: relative;
    overflow: hidden;
    color: #ffffff;
    box-shadow: 0 10px 25px -5px rgba(13, 124, 102, 0.4);
}
.pos-hero-box::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 140px;
    height: 140px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0) 70%);
    border-radius: 50%;
    pointer-events: none;
}
.pos-btn-preset {
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
.pos-btn-preset:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
    transform: translateY(-1px);
    color: #0f172a;
}
.pos-btn-preset:active {
    transform: scale(0.96);
}
html.dark .pos-btn-preset,
[data-theme="dark"] .pos-btn-preset {
    background: #1e293b;
    border-color: #334155;
    color: #cbd5e1;
}
html.dark .pos-btn-preset:hover,
[data-theme="dark"] .pos-btn-preset:hover {
    background: #27354f;
    border-color: #475569;
    color: #ffffff;
}
.pos-submit-btn {
    background: linear-gradient(135deg, #0d7c66 0%, #00a884 50%, #047857 100%);
    box-shadow: 0 8px 20px -4px rgba(13, 124, 102, 0.45);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.pos-submit-btn:hover {
    box-shadow: 0 12px 28px -4px rgba(13, 124, 102, 0.6);
    transform: translateY(-1.5px);
}
.pos-submit-btn:active {
    transform: scale(0.98);
}
.student-search-item {
    transition: all 0.15s ease;
}
.student-search-item:hover, .student-search-item.selected {
    background: #f0fdf4;
    border-color: #86efac;
}
html.dark .student-search-item:hover, html.dark .student-search-item.selected {
    background: #064e3b33;
    border-color: #047857;
}
</style>

<div class="flex-1 p-4 sm:p-6 lg:p-8 overflow-auto bg-slate-50/60 dark:bg-slate-950 min-h-screen">

    {{-- Top Counter Stats & Header Bar --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1 text-xs text-slate-400 font-medium">
                <a href="{{ route('finance.overview') }}" class="hover:text-[#0D7C66] dark:hover:text-teal-400 transition">Finance</a>
                <span>/</span>
                <span class="text-slate-600 dark:text-slate-300 font-semibold">Fast POS Billing & Collection</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-2xl bg-[#D3F8EE] text-[#0D7C66] flex items-center justify-center font-bold text-xl shadow-xs border border-[#A7F3D0]">
                    <i class="bi bi-terminal-dash"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-2">
                        Fee Collection POS Terminal
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                            Cashier Desk
                        </span>
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">High-speed fee collection, student wallet credit settlement & instant dual-copy receipt printer</p>
                </div>
            </div>
        </div>

        {{-- Action buttons & Shortcuts Help --}}
        <div class="flex items-center gap-2.5 flex-wrap">
            <span class="hidden xl:inline-flex items-center gap-1.5 px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-[11px] font-semibold text-slate-500 dark:text-slate-400 shadow-xs">
                <kbd class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 font-mono text-[10px] border border-slate-300 dark:border-slate-700">F2</kbd> Find Student
                <span class="text-slate-300 dark:text-slate-700 mx-1">|</span>
                <kbd class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 font-mono text-[10px] border border-slate-300 dark:border-slate-700">Ctrl+Enter</kbd> Settle
            </span>

            <a href="{{ route('finance.fee-profiles') }}" class="px-3.5 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold shadow-xs flex items-center gap-1.5 transition">
                <i class="bi bi-person-vcard"></i> Fee Profiles
            </a>

            <a href="{{ route('finance.day-book') }}" class="px-3.5 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold shadow-xs flex items-center gap-1.5 transition">
                <i class="bi bi-journal-text"></i> Day Book
            </a>
        </div>
    </div>

    {{-- Today's Counter Collections Summary Ticker --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 mb-6">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-3.5 flex items-center justify-between shadow-xs">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Today Total</span>
                <div class="text-lg font-black text-slate-800 dark:text-slate-100">Rs. {{ number_format($todayTotalCollected, 2) }}</div>
            </div>
            <span class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-[#0D7C66] dark:text-teal-400 flex items-center justify-center text-sm border border-emerald-200/60 dark:border-emerald-800/60">
                <i class="bi bi-cash-stack"></i>
            </span>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-3.5 flex items-center justify-between shadow-xs">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Cash in Drawer</span>
                <div class="text-lg font-black text-emerald-700 dark:text-emerald-400">Rs. {{ number_format($todayCashCollected, 2) }}</div>
            </div>
            <span class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center text-sm border border-emerald-200/60 dark:border-emerald-800/60">
                <i class="bi bi-wallet2"></i>
            </span>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-3.5 flex items-center justify-between shadow-xs">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Digital / Online</span>
                <div class="text-lg font-black text-indigo-600 dark:text-indigo-400">Rs. {{ number_format($todayDigitalCollected, 2) }}</div>
            </div>
            <span class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 flex items-center justify-center text-sm border border-indigo-200/60 dark:border-indigo-800/60">
                <i class="bi bi-qr-code-scan"></i>
            </span>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-3.5 flex items-center justify-between shadow-xs">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Receipts Issued</span>
                <div class="text-lg font-black text-slate-800 dark:text-slate-100">{{ $todayReceiptsCount }} <span class="text-xs font-normal text-slate-400">bills</span></div>
            </div>
            <span class="w-9 h-9 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 flex items-center justify-center text-sm border border-sky-200/60 dark:border-sky-800/60">
                <i class="bi bi-receipt"></i>
            </span>
        </div>
    </div>

    {{-- POS MAIN BILLING DESK --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- LEFT COLUMN: STUDENT SEARCH, DUES SELECTOR & FEE BUILDER (7 Cols) --}}
        <div class="lg:col-span-7 space-y-5">

            {{-- 1. FAST STUDENT SEARCH & LIVE LOOKUP CARD --}}
            <div class="pos-card p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-xl bg-[#0D7C66] text-white flex items-center justify-center text-xs font-black shadow-xs">
                            1
                        </span>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100">Student & Live Dues Lookup</h2>
                            <p class="text-[11px] text-slate-400">Search student by Name, Roll No, Admission No or Phone</p>
                        </div>
                    </div>
                    <span class="text-[11px] text-slate-400 font-mono">Shortcut: <kbd class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[10px]">F2</kbd></span>
                </div>

                {{-- Live Search Input with Dropdown Suggestion --}}
                <div class="relative">
                    <div class="relative">
                        <span class="absolute left-3.5 top-3 text-slate-400 text-sm">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="pos_student_search_input" placeholder="Type Student Name, Admission No (e.g. ADM-001) or Roll No..."
                               autocomplete="off"
                               class="w-full bg-slate-50/70 dark:bg-slate-800/80 border-2 border-slate-200 dark:border-slate-700 focus:border-[#0D7C66] dark:focus:border-teal-400 rounded-xl pl-10 pr-10 py-2.5 text-xs font-bold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#0D7C66]/20 transition">
                        <button type="button" id="btn_clear_search" onclick="clearStudentSearch()" class="hidden absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-sm">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>

                    {{-- Search Auto-complete Suggestion Box --}}
                    <div id="student_search_results_box" class="hidden absolute left-0 right-0 top-full mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl z-50 max-h-72 overflow-y-auto p-2 space-y-1">
                        {{-- Injected via JS --}}
                    </div>
                </div>

                {{-- SELECTED STUDENT PROFILE BADGE (Hidden until selected) --}}
                <div id="selected_student_profile_box" class="hidden rounded-2xl p-4 bg-gradient-to-r from-emerald-50/80 via-teal-50/40 to-slate-50/60 dark:from-slate-900 dark:via-teal-950/20 dark:to-slate-900 border border-emerald-200/80 dark:border-emerald-800/60 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-200 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200 flex items-center justify-center font-black text-lg shadow-xs overflow-hidden" id="student_avatar_box">
                                <span id="student_initials">ST</span>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-black text-slate-900 dark:text-slate-100" id="student_name_display">Student Name</h3>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-[#D3F8EE] text-[#0D7C66] border border-[#A7F3D0]" id="student_class_display">Class 10 - A</span>
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-2 mt-0.5">
                                    <span>Adm: <b class="text-slate-700 dark:text-slate-300" id="student_adm_display">ADM-2026</b></span>
                                    <span>•</span>
                                    <span>Roll: <b class="text-slate-700 dark:text-slate-300" id="student_roll_display">12</b></span>
                                    <span>•</span>
                                    <span>Parent: <span class="text-slate-700 dark:text-slate-300" id="student_guardian_display">Ram Sharma</span></span>
                                </div>
                            </div>
                        </div>

                        <button type="button" onclick="resetSelectedStudent()" class="text-xs text-slate-400 hover:text-rose-500 transition px-2 py-1 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40">
                            <i class="bi bi-x-lg"></i> Change
                        </button>
                    </div>

                    {{-- Student Wallet & Outstanding Dues Metrics Bar --}}
                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-emerald-100 dark:border-emerald-950/80">
                        <div class="bg-white/80 dark:bg-slate-800/80 rounded-xl p-2.5 border border-emerald-100 dark:border-slate-700 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Wallet Balance (Credit)</span>
                                <div class="text-xs font-black text-emerald-700 dark:text-emerald-400" id="student_wallet_display">Rs. 0.00</div>
                            </div>
                            <button type="button" onclick="applyWalletBalanceToPayment()" id="btn_apply_wallet" class="hidden text-[10px] font-black bg-emerald-100 hover:bg-emerald-200 text-emerald-800 px-2 py-1 rounded-lg transition">
                                Apply
                            </button>
                        </div>

                        <div class="bg-white/80 dark:bg-slate-800/80 rounded-xl p-2.5 border border-emerald-100 dark:border-slate-700 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Outstanding Dues</span>
                                <div class="text-xs font-black text-rose-600 dark:text-rose-400" id="student_dues_display">Rs. 0.00</div>
                            </div>
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-rose-50 text-rose-700 border border-rose-200" id="student_unpaid_count_pill">0 Invoices</span>
                        </div>
                    </div>
                </div>

            </div>

            {{-- 2. UNPAID INVOICES & MONTHS SELECTION --}}
            <div id="unpaid_invoices_section" class="pos-card p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-xl bg-[#0D7C66] text-white flex items-center justify-center text-xs font-black shadow-xs">
                            2
                        </span>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100">Select Unpaid Fees & Invoices</h2>
                            <p class="text-[11px] text-slate-400">Check the months/invoices being settled today</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="selectAllUnpaidInvoices(true)" class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 text-[#0D7C66] dark:text-teal-400 border border-emerald-200 dark:border-emerald-900/60 rounded-lg text-xs font-bold transition flex items-center gap-1">
                            <i class="bi bi-check2-all"></i> Select All
                        </button>
                        <button type="button" onclick="selectAllUnpaidInvoices(false)" class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-600 dark:text-slate-400 rounded-lg text-xs font-bold transition">
                            Clear
                        </button>
                    </div>
                </div>

                {{-- Invoices Grid --}}
                <div id="pos_invoices_list" class="space-y-2 max-h-64 overflow-y-auto pr-1">
                    <div class="text-center py-6 text-slate-400 text-xs">
                        <i class="bi bi-person-bounding-box text-2xl block mb-1 text-slate-300 dark:text-slate-700"></i>
                        Please select a student above to load outstanding dues and fee records.
                    </div>
                </div>
            </div>

            {{-- 3. CUSTOM FEE ITEM BUILDER / INSTANT CHARGES --}}
            <div class="pos-card p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-xl bg-[#0D7C66] text-white flex items-center justify-center text-xs font-black shadow-xs">
                            3
                        </span>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100">Fee Particulars & Custom Additions</h2>
                            <p class="text-[11px] text-slate-400">Add or modify fee breakdown items for this receipt</p>
                        </div>
                    </div>
                    <button type="button" onclick="addCustomFeeRow('', 0)" class="text-xs font-bold text-[#0D7C66] dark:text-teal-400 hover:underline flex items-center gap-1 bg-[#D3F8EE] dark:bg-teal-950/60 px-3 py-1.5 rounded-xl border border-[#A7F3D0] dark:border-teal-900 transition">
                        <i class="bi bi-plus-circle"></i> Add Item
                    </button>
                </div>

                {{-- Quick 1-Click Preset Chips for Private Schools --}}
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">⚡ Quick 1-Click Fee Presets</span>
                    <div class="flex flex-wrap gap-1.5">
                        <button type="button" onclick="addCustomFeeRow('Monthly Tuition Fee', 3500)" class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 text-[#0D7C66] dark:text-teal-300 border border-emerald-200 dark:border-emerald-900/50 rounded-lg text-xs font-bold transition">
                            + Tuition (3,500)
                        </button>
                        <button type="button" onclick="addCustomFeeRow('School Bus Transport', 1800)" class="px-2.5 py-1 bg-sky-50 dark:bg-sky-950/40 hover:bg-sky-100 text-sky-800 dark:text-sky-300 border border-sky-200 dark:border-sky-900/50 rounded-lg text-xs font-bold transition">
                            + Bus Transport (1,800)
                        </button>
                        <button type="button" onclick="addCustomFeeRow('Term Examination Fee', 1200)" class="px-2.5 py-1 bg-amber-50 dark:bg-amber-950/40 hover:bg-amber-100 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-900/50 rounded-lg text-xs font-bold transition">
                            + Exam Fee (1,200)
                        </button>
                        <button type="button" onclick="addCustomFeeRow('Hostel & Meal Charges', 4500)" class="px-2.5 py-1 bg-purple-50 dark:bg-purple-950/40 hover:bg-purple-100 text-purple-800 dark:text-purple-300 border border-purple-200 dark:border-purple-900/50 rounded-lg text-xs font-bold transition">
                            + Hostel / Meals (4,500)
                        </button>
                        <button type="button" onclick="addCustomFeeRow('Annual Admission / Dev Fee', 5000)" class="px-2.5 py-1 bg-indigo-50 dark:bg-indigo-950/40 hover:bg-indigo-100 text-indigo-800 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-900/50 rounded-lg text-xs font-bold transition">
                            + Annual Fee (5,000)
                        </button>
                        <button type="button" onclick="addCustomFeeRow('Late Payment Fine', 100)" class="px-2.5 py-1 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900/50 rounded-lg text-xs font-bold transition">
                            + Late Fine (100)
                        </button>
                    </div>
                </div>

                {{-- Fee Items Table --}}
                <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
                    <div class="bg-slate-50 dark:bg-slate-800/80 px-4 py-2 flex items-center justify-between text-[11px] font-bold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider">
                        <span class="flex-1">Fee Item Particulars</span>
                        <span class="w-36 text-right pr-9">Amount (Rs.)</span>
                    </div>
                    <div id="pos_items_container" class="divide-y divide-slate-100 dark:divide-slate-800 p-2 space-y-2 max-h-52 overflow-y-auto">
                        {{-- Default row --}}
                        <div class="pos-fee-item-row flex items-center gap-2">
                            <input type="text" name="items[0][name]" value="Monthly Tuition Fee" placeholder="Particulars Name" required
                                   class="fee-name-input flex-1 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs font-bold bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#0D7C66]/20 focus:border-[#0D7C66] transition"
                                   oninput="recomputePOSTotals()">
                            <div class="w-36 relative">
                                <span class="absolute left-3 top-2 text-slate-400 text-xs font-bold">Rs.</span>
                                <input type="number" step="0.01" name="items[0][amount]" value="3500" placeholder="0.00" required
                                       class="fee-amount-input w-full border border-slate-200 dark:border-slate-700 rounded-xl pl-9 pr-3 py-2 text-xs font-black bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-right focus:ring-2 focus:ring-[#0D7C66]/20 focus:border-[#0D7C66] transition"
                                       oninput="recomputePOSTotals()">
                            </div>
                            <button type="button" onclick="removeCustomFeeRow(this)" title="Remove item" class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 hover:bg-rose-100 flex items-center justify-center text-xs transition">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Fine Waiver / Concession Section (Accordion) --}}
                <div class="pt-2">
                    <button type="button" onclick="toggleFineWaiverBox()" class="text-xs font-bold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 flex items-center gap-1.5">
                        <i class="bi bi-tag text-emerald-600"></i> Apply Fee Concession / Fine Waiver <i class="bi bi-chevron-down text-[10px]"></i>
                    </button>
                    <div id="fine_waiver_box" class="hidden mt-3 p-3.5 bg-slate-50 dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800 rounded-xl space-y-2.5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Concession Amount (Rs.)</label>
                                <input type="number" step="0.01" id="input_fine_waiver" placeholder="0.00" oninput="recomputePOSTotals()"
                                       class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 text-xs font-bold text-slate-900 dark:text-slate-100">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Reason / Authorization Remark</label>
                                <input type="text" id="input_waiver_reason" placeholder="e.g. Principal approved 10% waiver"
                                       class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 text-xs font-medium text-slate-900 dark:text-slate-100">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        {{-- RIGHT COLUMN: FINTECH SETTLE & PAYMENT COUNTER STUDIO (5 Cols) --}}
        <div class="lg:col-span-5 lg:sticky lg:top-6 space-y-4">

            <div class="pos-card p-5 sm:p-6 space-y-4 relative overflow-hidden">

                {{-- Header Row --}}
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-xl bg-[#00A884] text-white flex items-center justify-center text-xs font-black shadow-xs">
                            4
                        </span>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-slate-100 tracking-tight">Settle & Print Receipt</h3>
                            <p class="text-[11px] text-slate-400">Record cashier collection and print bill</p>
                        </div>
                    </div>
                    <span class="text-[10px] bg-emerald-50 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 px-2.5 py-0.5 rounded-full font-black tracking-wider uppercase flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Ready
                    </span>
                </div>

                {{-- Settlement Mode (Invoices vs Advance Deposit) --}}
                <div class="grid grid-cols-2 gap-1.5 p-1 bg-slate-100 dark:bg-slate-800/80 rounded-xl border border-slate-200 dark:border-slate-700">
                    <label class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg cursor-pointer transition text-xs font-black text-slate-600 dark:text-slate-300 hover:text-slate-900 has-checked:bg-white dark:has-checked:bg-slate-700 has-checked:text-[#0D7C66] dark:has-checked:text-teal-400 has-checked:shadow-sm">
                        <input type="radio" name="pos_settle_mode" value="invoices" checked onchange="switchSettlementMode('invoices')" class="hidden">
                        <i class="bi bi-receipt text-emerald-600"></i> Settle Fees
                    </label>
                    <label class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg cursor-pointer transition text-xs font-black text-slate-600 dark:text-slate-300 hover:text-slate-900 has-checked:bg-white dark:has-checked:bg-slate-700 has-checked:text-indigo-600 dark:has-checked:text-indigo-400 has-checked:shadow-sm">
                        <input type="radio" name="pos_settle_mode" value="advance_deposit" onchange="switchSettlementMode('advance_deposit')" class="hidden">
                        <i class="bi bi-wallet-fill text-indigo-500"></i> Advance Deposit
                    </label>
                </div>

                {{-- Hero Amount Display Box --}}
                <div class="pos-hero-box text-center">
                    <span class="text-[10px] font-extrabold text-emerald-100 uppercase tracking-widest block mb-1" id="hero_amount_label">Total Payable Amount</span>
                    <div class="text-3xl sm:text-4xl font-black text-white tracking-tight" id="display_pos_payable">
                        Rs. 3,500.00
                    </div>
                    <div class="text-[10px] text-emerald-100/80 font-semibold mt-1" id="pos_items_summary_count">1 fee item selected</div>
                </div>

                {{-- Payment Method Selection Grid --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                        Payment Mode <span class="text-rose-500">*</span>
                    </label>
                    <select id="pos_payment_method" onchange="handlePaymentMethodChange(this.value)"
                            class="w-full bg-slate-50/70 dark:bg-slate-800 text-slate-900 dark:text-slate-100 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-[#0D7C66]/20 focus:border-[#0D7C66] transition cursor-pointer">
                        <option value="cash" selected>💵 Cash Counter (Physical Cash)</option>
                        <option value="fonepay">📲 Fonepay Direct QR Code</option>
                        <option value="esewa">🟢 eSewa Mobile Wallet</option>
                        <option value="khalti">🟣 Khalti Wallet</option>
                        <option value="connectips">🏦 ConnectIPS NetBanking</option>
                        <option value="bank">🏦 Bank Transfer / Deposit Slip</option>
                        <option value="cheque">🧾 Cheque</option>
                        <option value="stripe">💳 POS Card Swipe (Stripe)</option>
                        <option value="wallet_credit">🪙 Student Prepaid Wallet Balance</option>
                    </select>
                </div>

                {{-- Instant Counter Fonepay / Merchant QR & Multi-Bank Master Panel --}}
                <div id="pos_bank_qr_container" class="hidden space-y-3 p-3.5 bg-gradient-to-br from-teal-50/70 via-slate-50 to-emerald-50/50 dark:from-slate-800 dark:via-slate-800/90 dark:to-teal-950/40 rounded-2xl border-2 border-[#0D7C66]/30 dark:border-teal-500/30 transition-all">
                    
                    {{-- Bank Selector Header --}}
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-black text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                            <span class="w-6 h-6 rounded-lg bg-[#0D7C66] text-white flex items-center justify-center text-xs">
                                <i class="bi bi-bank2"></i>
                            </span>
                            <span>Receiving Bank &amp; Merchant QR</span>
                        </label>
                        <a href="{{ route('finance.bank-accounts.index') }}" target="_blank" class="text-[10px] font-bold text-[#0D7C66] dark:text-teal-400 hover:underline flex items-center gap-0.5">
                            <i class="bi bi-gear-fill text-[9px]"></i> Bank Master
                        </a>
                    </div>

                    {{-- Multi-Bank Select Dropdown --}}
                    <div class="space-y-1">
                        <select id="pos_selected_bank_id" onchange="handleSelectedBankChange(this.value)"
                                class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#0D7C66]/20 focus:border-[#0D7C66]">
                            @forelse($bankAccounts as $bank)
                            <option value="{{ $bank->id }}" 
                                    data-bank-name="{{ $bank->bank_name }}" 
                                    data-bank-code="{{ $bank->bank_code }}"
                                    data-acc-name="{{ $bank->account_name }}" 
                                    data-acc-no="{{ $bank->account_number }}" 
                                    data-branch="{{ $bank->branch_name }}"
                                    data-merchant-id="{{ $bank->fonepay_merchant_id }}"
                                    data-pan="{{ $bank->fonepay_pan }}"
                                    data-qr-image="{{ $bank->qr_image_url ?? '' }}"
                                    {{ ($defaultBank && $defaultBank->id === $bank->id) ? 'selected' : '' }}>
                                {{ $bank->bank_name }} — {{ $bank->account_number }} {{ $bank->is_default ? '★ (Default)' : '' }}
                            </option>
                            @empty
                            <option value="">No Active Bank Accounts Configured</option>
                            @endforelse
                        </select>
                    </div>

                    {{-- Dynamic Merchant Standee & QR Display Card --}}
                    <div class="bg-white dark:bg-slate-900 rounded-xl p-3 border border-slate-200 dark:border-slate-700/80 shadow-xs space-y-3">
                        <div class="flex items-center justify-between text-xs border-b border-slate-100 dark:border-slate-800 pb-2">
                            <div>
                                <span class="font-extrabold text-slate-800 dark:text-slate-100 block" id="pos_card_bank_name">{{ $defaultBank?->bank_name ?? 'Commercial Bank' }}</span>
                                <span class="text-[10px] text-slate-400 font-mono" id="pos_card_branch">{{ $defaultBank?->branch_name ?? 'Main Branch' }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-[#0D7C66] dark:bg-teal-950 dark:text-teal-300">
                                <i class="bi bi-qr-code-scan me-1"></i> Counter Fonepay / QR
                            </span>
                        </div>

                        {{-- QR Preview Visual Container --}}
                        <div class="flex flex-col sm:flex-row items-center gap-3">
                            <div class="relative bg-white p-2 rounded-xl border-2 border-slate-200 dark:border-slate-700 shadow-xs flex flex-col items-center justify-center shrink-0">
                                {{-- QR canvas / image target --}}
                                <div id="pos_qr_render_target" class="w-28 h-28 flex items-center justify-center overflow-hidden">
                                    <img id="pos_qr_img_fallback" src="https://api.qrserver.com/v1/create-qr-code/?size=112x112&data=SchoolPOS" class="w-full h-full object-contain" alt="QR Code">
                                </div>
                                <span class="mt-1 text-[9px] font-black text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-1.5 py-0.5 rounded" id="pos_qr_badge_amount">
                                    Rs. 0.00
                                </span>
                            </div>

                            <div class="flex-1 space-y-2 text-xs w-full">
                                <div class="bg-slate-50 dark:bg-slate-800/80 p-2 rounded-lg border border-slate-100 dark:border-slate-700/60">
                                    <div class="text-[10px] text-slate-400 font-medium">A/C Number:</div>
                                    <div class="flex items-center justify-between font-mono font-black text-slate-800 dark:text-slate-100">
                                        <span id="pos_card_acc_no">{{ $defaultBank?->account_number ?? '—' }}</span>
                                        <button type="button" onclick="copyBankAccountNumber()" class="px-1.5 py-0.5 bg-slate-200 dark:bg-slate-700 hover:bg-[#0D7C66] hover:text-white rounded text-[10px] font-bold transition" title="Copy Account Number">
                                            <i class="bi bi-clipboard"></i> Copy
                                        </button>
                                    </div>
                                    <div class="text-[10px] text-slate-500 font-medium truncate mt-0.5" id="pos_card_acc_name">
                                        {{ $defaultBank?->account_name ?? 'School General Account' }}
                                    </div>
                                </div>

                                {{-- Customer Display Trigger Button --}}
                                <button type="button" onclick="openCustomerFacingQrModal()" 
                                        class="w-full py-1.5 px-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-lg text-xs font-black flex items-center justify-center gap-1.5 shadow-xs transition cursor-pointer">
                                    <i class="bi bi-display"></i> ग्राहकलाई देखाउनुहोस् (Customer Display)
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Fonepay Trace ID / UTR / Reference ID Input --}}
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                            Fonepay Trace ID / Bank UTR No.
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-slate-400 text-xs">
                                <i class="bi bi-hash"></i>
                            </span>
                            <input type="text" id="pos_transaction_reference" placeholder="e.g. FP-892104 or UTR-391823"
                                   class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:border-[#0D7C66] dark:focus:border-teal-400 rounded-xl pl-8 pr-3 py-2 text-xs font-mono font-bold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#0D7C66]/20 transition">
                        </div>
                        <p class="text-[10px] text-slate-400">Cashier enters 6-digit trace ID from customer screen for daily bank counter audit.</p>
                    </div>

                </div>

                {{-- Cash Received / Tendered & Preset Grid (For Cash Counter) --}}
                <div id="cash_tendered_container" class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300">
                            Cash Tendered / Received
                        </label>
                        <span class="text-[10px] font-bold text-slate-400">Nepali Rupee (NPR)</span>
                    </div>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-slate-400 font-black text-xs">Rs.</span>
                        <input type="number" id="input_pos_cash_tendered" placeholder="0.00" step="10" oninput="calculatePOSChange()"
                               class="w-full bg-slate-50/60 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 focus:border-[#0D7C66] dark:focus:border-teal-400 rounded-xl pl-10 pr-3.5 py-2 text-base font-black text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-[#0D7C66]/20 transition">
                    </div>

                    {{-- 3x2 Currency Presets Grid --}}
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px;" class="pt-0.5">
                        <button type="button" onclick="setCashPresetExact()" class="pos-btn-preset">
                            <span class="font-black text-[#0D7C66] dark:text-teal-400 me-1">⚡</span> Exact
                        </button>
                        <button type="button" onclick="addCashPreset(500)" class="pos-btn-preset">
                            + Rs. 500
                        </button>
                        <button type="button" onclick="addCashPreset(1000)" class="pos-btn-preset">
                            + Rs. 1,000
                        </button>
                        <button type="button" onclick="addCashPreset(2000)" class="pos-btn-preset">
                            + Rs. 2,000
                        </button>
                        <button type="button" onclick="addCashPreset(5000)" class="pos-btn-preset">
                            + Rs. 5,000
                        </button>
                        <button type="button" onclick="clearCashPreset()" class="pos-btn-preset text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/50">
                            <i class="bi bi-x-circle me-1"></i> Clear
                        </button>
                    </div>
                </div>

                {{-- Dynamic Change Calculation & Denomination Breakdown Box --}}
                <div id="pos_change_result_card" class="rounded-xl p-3.5 border transition-all text-center space-y-1 bg-slate-50 dark:bg-slate-800/70 border-slate-200 dark:border-slate-700">
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block" id="pos_change_label">CASH RETURN STATUS</span>
                    <div class="text-xl font-black tracking-tight text-slate-700 dark:text-slate-300" id="pos_display_change">
                        Rs. 0.00
                    </div>
                    <p class="text-[11px] text-slate-400" id="pos_change_message">Enter cash tendered to compute change.</p>
                    <div id="pos_denomination_breakdown" class="text-xs font-semibold text-emerald-700 dark:text-teal-400 pt-1.5 border-t border-slate-200 dark:border-slate-700 hidden"></div>
                </div>

                {{-- Option: Deposit Change to Student Wallet --}}
                <div id="deposit_change_wallet_option" class="hidden p-2.5 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-200 dark:border-indigo-900/60">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-indigo-900 dark:text-indigo-200">
                        <input type="checkbox" id="chk_deposit_change" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500">
                        <span>📥 Deposit return change into Student Advance Wallet</span>
                    </label>
                </div>

                {{-- Cashier Notes --}}
                <div>
                    <input type="text" id="pos_cashier_notes" placeholder="Optional Cashier Remarks / Cheque No / Reference..."
                           class="w-full bg-slate-50/60 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 text-xs text-slate-800 dark:text-slate-200">
                </div>

                {{-- Settle Primary Button --}}
                <div class="pt-1">
                    <button type="button" id="btn_settle_pos" onclick="submitPOSCollection()"
                            class="pos-submit-btn w-full py-3.5 px-4 text-white rounded-xl text-sm font-black flex items-center justify-center gap-2 cursor-pointer">
                        <i class="bi bi-check2-circle text-lg"></i>
                        <span id="btn_settle_label">Settle & Issue Receipt</span>
                    </button>
                </div>

                <div class="text-center pt-0.5">
                    <p class="text-[10px] text-slate-400 font-semibold flex items-center justify-center gap-1">
                        <i class="bi bi-shield-check text-emerald-500"></i> Certified Private School Fee Ledger Transaction
                    </p>
                </div>

            </div>

        </div>

    </div>

    {{-- RECENT 5 TRANSACTIONS SETTLED TODAY (Quick reprint bar) --}}
    <div class="mt-8 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs">
        <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <i class="bi bi-clock-history text-[#0D7C66]"></i> Recent Receipts Settled Today
            </h3>
            <a href="{{ route('payments.index') }}" class="text-xs font-bold text-[#0D7C66] hover:underline">View All Invoices</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-100 dark:border-slate-800 font-bold uppercase text-[10px]">
                        <th class="pb-2">Receipt #</th>
                        <th class="pb-2">Student</th>
                        <th class="pb-2">Particulars / Invoice</th>
                        <th class="pb-2">Amount</th>
                        <th class="pb-2">Mode</th>
                        <th class="pb-2">Time</th>
                        <th class="pb-2 text-right">Reprint</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($recentCollections as $rc)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                        <td class="py-2.5 font-mono font-bold text-slate-700 dark:text-slate-300">#{{ $rc->receipt_number }}</td>
                        <td class="py-2.5 font-bold text-slate-900 dark:text-slate-100">{{ optional($rc->invoice)->student?->full_name ?? 'Student' }}</td>
                        <td class="py-2.5 text-slate-500">{{ \Illuminate\Support\Str::limit(optional($rc->invoice)->title ?? 'Fee Payment', 30) }}</td>
                        <td class="py-2.5 font-black text-emerald-700 dark:text-teal-400">Rs. {{ number_format($rc->amount, 2) }}</td>
                        <td class="py-2.5"><span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">{{ $rc->payment_method }}</span></td>
                        <td class="py-2.5 text-slate-400">{{ $rc->created_at->format('h:i A') }}</td>
                        <td class="py-2.5 text-right space-x-1">
                            <a href="{{ route('payments.receipt-download', ['paymentId' => $rc->id, 'format' => 'thermal']) }}" target="_blank" title="80mm Thermal Slip" class="px-2 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-emerald-100 text-slate-700 dark:text-slate-300 hover:text-emerald-800 rounded-lg text-xs font-bold transition inline-flex items-center gap-1">
                                <i class="bi bi-printer"></i> Thermal
                            </a>
                            <a href="{{ route('payments.receipt-download', ['paymentId' => $rc->id, 'format' => 'dual']) }}" target="_blank" title="A4 Dual Copy" class="px-2 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-emerald-100 text-slate-700 dark:text-slate-300 hover:text-emerald-800 rounded-lg text-xs font-bold transition inline-flex items-center gap-1">
                                <i class="bi bi-file-earmark-pdf"></i> Dual Copy
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-4 text-center text-slate-400">No collections recorded yet today.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- INSTANT SETTLEMENT SUCCESS & PRINT POPUP MODAL --}}
<div id="pos_receipt_modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 max-w-md w-full p-6 shadow-2xl space-y-5 animate-scale-in">
        
        {{-- Success Header --}}
        <div class="text-center space-y-2">
            <div class="w-14 h-14 rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400 mx-auto flex items-center justify-center text-2xl shadow-xs">
                <i class="bi bi-check-lg"></i>
            </div>
            <h3 class="text-xl font-black text-slate-900 dark:text-slate-100">Fee Payment Settled!</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400" id="modal_success_subtitle">Receipt generated cleanly and ledger posted.</p>
        </div>

        {{-- Receipt Details Card --}}
        <div class="bg-slate-50 dark:bg-slate-800/80 rounded-2xl p-4 border border-slate-200 dark:border-slate-700 space-y-2 text-xs">
            <div class="flex justify-between">
                <span class="text-slate-400">Receipt Number:</span>
                <span class="font-mono font-black text-slate-800 dark:text-slate-100" id="modal_receipt_no">RCP-2026-000000</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Student:</span>
                <span class="font-bold text-slate-800 dark:text-slate-100" id="modal_student_name">—</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Amount Settled:</span>
                <span class="font-black text-emerald-700 dark:text-teal-400 text-sm" id="modal_amount_paid">Rs. 0.00</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Payment Mode:</span>
                <span class="font-bold text-slate-800 dark:text-slate-100 uppercase" id="modal_payment_mode">CASH</span>
            </div>
            <div id="modal_bank_row" class="flex justify-between pt-1 border-t border-slate-200 dark:border-slate-700 hidden">
                <span class="text-slate-400">Receiving Bank:</span>
                <span class="font-bold text-slate-800 dark:text-slate-100" id="modal_bank_name">—</span>
            </div>
            <div id="modal_ref_row" class="flex justify-between pt-1 border-t border-slate-200 dark:border-slate-700 hidden">
                <span class="text-slate-400">Fonepay Trace / UTR:</span>
                <span class="font-mono font-bold text-[#0D7C66] dark:text-teal-400" id="modal_ref_no">—</span>
            </div>
            <div id="modal_change_row" class="flex justify-between pt-1 border-t border-slate-200 dark:border-slate-700 hidden">
                <span class="text-slate-400">Change Returned:</span>
                <span class="font-black text-emerald-600" id="modal_change_amount">Rs. 0.00</span>
            </div>
            <div id="modal_wallet_row" class="flex justify-between pt-1 border-t border-slate-200 dark:border-slate-700 hidden">
                <span class="text-slate-400">Wallet Credit Added:</span>
                <span class="font-black text-indigo-600" id="modal_wallet_deposited">Rs. 0.00</span>
            </div>
            <div class="flex justify-between pt-1 border-t border-slate-200 dark:border-slate-700">
                <span class="text-slate-400">Remaining Student Dues:</span>
                <span class="font-bold text-rose-600 dark:text-rose-400" id="modal_remaining_dues">Rs. 0.00</span>
            </div>
        </div>

        {{-- 1-Click Print Actions --}}
        <div class="space-y-2">
            <button type="button" onclick="printModalReceipt('thermal')" class="w-full py-3 bg-[#0D7C66] hover:bg-[#00A884] text-white rounded-xl text-xs font-black flex items-center justify-center gap-2 shadow-xs transition cursor-pointer">
                <i class="bi bi-printer-fill text-sm"></i> Print 80mm POS Thermal Slip (Instant)
            </button>
            
            <div class="grid grid-cols-2 gap-2">
                <button type="button" onclick="printModalReceipt('dual')" class="py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition">
                    <i class="bi bi-file-earmark-pdf"></i> A4 Dual Copy
                </button>
                <button type="button" onclick="copyWhatsAppReceiptLink()" class="py-2.5 bg-emerald-50 hover:bg-emerald-100 text-[#0D7C66] border border-emerald-200 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition">
                    <i class="bi bi-whatsapp"></i> Share WhatsApp
                </button>
            </div>
        </div>

        {{-- Collect Next Student Button --}}
        <div class="pt-1">
            <button type="button" onclick="closeModalAndNextStudent()" class="w-full py-2.5 bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 hover:opacity-90 rounded-xl text-xs font-black flex items-center justify-center gap-1.5 transition">
                <span>⚡ Collect Next Student (Space / Enter)</span>
            </button>
        </div>

    </div>
</div>

{{-- CUSTOMER FACING DUAL-SCREEN / TABLET QR CODE MODAL --}}
<div id="customerFacingQrModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/85 backdrop-blur-md flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border-2 border-[#0D7C66]/50 dark:border-teal-500/50 max-w-md w-full p-6 sm:p-7 shadow-2xl space-y-5 text-center animate-scale-in relative">
        
        <button type="button" onclick="closeCustomerFacingQrModal()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 flex items-center justify-center transition">
            <i class="bi bi-x-lg text-sm"></i>
        </button>

        {{-- School & Header --}}
        <div class="space-y-1">
            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-teal-50 text-[#0D7C66] dark:bg-teal-950 dark:text-teal-300 border border-teal-200 dark:border-teal-800 inline-flex items-center gap-1">
                <i class="bi bi-shield-check"></i> Fonepay / Merchant Counter QR
            </span>
            <h2 class="text-xl font-black text-slate-900 dark:text-slate-100 pt-1">
                Scan to Pay / शुल्क भुक्तानी
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Open Mobile Banking app, eSewa, Khalti, or Fonepay to scan.
            </p>
        </div>

        {{-- Big Bold Net Amount Banner --}}
        <div class="bg-gradient-to-r from-teal-600 via-emerald-600 to-[#0D7C66] text-white rounded-2xl p-3.5 shadow-md space-y-0.5">
            <span class="text-[10px] font-bold text-teal-100 uppercase tracking-widest block">PAYABLE AMOUNT (रकम)</span>
            <div class="text-3xl font-black tracking-tight" id="customer_modal_amount">
                Rs. 0.00
            </div>
            <div class="text-xs font-bold text-teal-100 truncate" id="customer_modal_student">
                Student: Not Selected
            </div>
        </div>

        {{-- High-Resolution QR Display --}}
        <div class="bg-white p-3 rounded-2xl border-2 border-slate-200 dark:border-slate-700 shadow-inner inline-block mx-auto">
            <div id="customer_qr_render_target" class="w-56 h-56 flex items-center justify-center overflow-hidden">
                <img id="customer_qr_img_fallback" src="https://api.qrserver.com/v1/create-qr-code/?size=224x224&data=SchoolPOS" class="w-full h-full object-contain" alt="Customer QR">
            </div>
        </div>

        {{-- Supported Payment Apps Badges --}}
        <div class="space-y-1.5">
            <div class="flex items-center justify-center flex-wrap gap-1.5 text-[11px] font-extrabold text-slate-700 dark:text-slate-300">
                <span class="px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-200">Fonepay</span>
                <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200">eSewa</span>
                <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 border border-purple-200">Khalti</span>
                <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-200">ConnectIPS</span>
                <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">All Commercial Banks</span>
            </div>
        </div>

        {{-- Bank Account Details Box --}}
        <div class="bg-slate-50 dark:bg-slate-800/80 rounded-xl p-3 text-xs border border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <div class="text-left">
                <span class="text-[10px] text-slate-400 font-bold block" id="customer_modal_bank_name">Commercial Bank</span>
                <span class="font-mono font-black text-slate-800 dark:text-slate-100" id="customer_modal_acc_no">A/C: —</span>
            </div>
            <button type="button" onclick="copyBankAccountNumber()" class="px-3 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg font-bold text-[#0D7C66] dark:text-teal-400 text-xs shadow-xs hover:bg-teal-50 transition cursor-pointer">
                <i class="bi bi-clipboard me-1"></i> Copy A/C
            </button>
        </div>

        <button type="button" onclick="closeCustomerFacingQrModal()" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 dark:bg-slate-100 dark:hover:bg-slate-200 text-white dark:text-slate-900 rounded-xl text-xs font-black transition cursor-pointer">
            Close Customer Display (सम्पन्न भयो / बन्द गर्नुहोस्)
        </button>

    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
(function() {
    // State
    let currentStudent = null;
    let loadedInvoices = [];
    let customRowCounter = 1;
    let lastSettledReceiptData = null;
    let searchTimeout = null;

    // DOM References
    const searchInput = document.getElementById('pos_student_search_input');
    const searchResultsBox = document.getElementById('student_search_results_box');
    const clearSearchBtn = document.getElementById('btn_clear_search');
    const profileBox = document.getElementById('selected_student_profile_box');
    const invoicesContainer = document.getElementById('pos_invoices_list');
    const itemsContainer = document.getElementById('pos_items_container');
    const inputCash = document.getElementById('input_pos_cash_tendered');
    const displayPayable = document.getElementById('display_pos_payable');
    const posChangeCard = document.getElementById('pos_change_result_card');
    const posChangeLabel = document.getElementById('pos_change_label');
    const posChangeDisplay = document.getElementById('pos_display_change');
    const posChangeMessage = document.getElementById('pos_change_message');
    const posDenomBreakdown = document.getElementById('pos_denomination_breakdown');
    const depositChangeOption = document.getElementById('deposit_change_wallet_option');
    const chkDepositChange = document.getElementById('chk_deposit_change');

    // ── 1. STUDENT SEARCH & LIVE LOOKUP ──

    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        clearSearchBtn.classList.toggle('hidden', query.length === 0);

        clearTimeout(searchTimeout);
        if (query.length === 0) {
            searchResultsBox.classList.add('hidden');
            return;
        }

        searchTimeout = setTimeout(() => {
            fetchStudents(query);
        }, 220);
    });

    searchInput.addEventListener('focus', function() {
        if (this.value.trim().length > 0) {
            searchResultsBox.classList.remove('hidden');
        }
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResultsBox.contains(e.target)) {
            searchResultsBox.classList.add('hidden');
        }
    });

    // Global Keyboard Shortcuts
    document.addEventListener('keydown', function(e) {
        // F2 -> focus student search
        if (e.key === 'F2') {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
        // Ctrl + Enter -> submit POS collection
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            submitPOSCollection();
        }
    });

    function fetchStudents(query) {
        searchResultsBox.innerHTML = '<div class="text-xs text-slate-400 p-3 text-center"><i class="bi bi-arrow-repeat animate-spin"></i> Searching students...</div>';
        searchResultsBox.classList.remove('hidden');

        fetch('{{ route('finance.pos.search-students') }}?q=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                if (!data.success || data.students.length === 0) {
                    searchResultsBox.innerHTML = '<div class="text-xs text-slate-400 p-3 text-center">No students found matching "' + query + '".</div>';
                    return;
                }

                searchResultsBox.innerHTML = '';
                data.students.forEach(st => {
                    const item = document.createElement('div');
                    item.className = 'student-search-item p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 cursor-pointer flex items-center justify-between gap-3';
                    item.innerHTML = `
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-lg bg-[#D3F8EE] text-[#0D7C66] font-bold text-xs flex items-center justify-center shrink-0">
                                ${st.name.charAt(0)}
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs font-black text-slate-900 dark:text-slate-100 truncate">${st.name}</div>
                                <div class="text-[10px] text-slate-400 truncate">Adm: ${st.admission_no} • Roll: ${st.roll_no} • ${st.class_name} ${st.section_name}</div>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="text-xs font-black ${st.total_due > 0 ? 'text-rose-600' : 'text-emerald-600'}">
                                ${st.total_due > 0 ? 'Due: Rs. ' + Number(st.total_due).toLocaleString() : 'Clear'}
                            </div>
                            ${st.wallet_credit > 0 ? `<div class="text-[10px] font-bold text-emerald-700">Wallet: Rs. ${Number(st.wallet_credit).toLocaleString()}</div>` : ''}
                        </div>
                    `;
                    item.addEventListener('click', () => {
                        selectStudent(st.id);
                        searchResultsBox.classList.add('hidden');
                        searchInput.value = st.name + ' (' + st.admission_no + ')';
                    });
                    searchResultsBox.appendChild(item);
                });
            })
            .catch(() => {
                searchResultsBox.innerHTML = '<div class="text-xs text-rose-500 p-3 text-center">Error fetching students.</div>';
            });
    }

    window.clearStudentSearch = function() {
        searchInput.value = '';
        clearSearchBtn.classList.add('hidden');
        searchResultsBox.classList.add('hidden');
        searchInput.focus();
    };

    window.selectStudent = function(studentId) {
        invoicesContainer.innerHTML = '<div class="text-center py-6 text-slate-400 text-xs"><i class="bi bi-arrow-repeat animate-spin text-lg block mb-1"></i> Fetching student fee record...</div>';

        fetch('/finance/pos/student/' + studentId + '/details')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    currentStudent = data.student;
                    currentStudent.wallet_credit = data.wallet_credit;
                    currentStudent.total_outstanding = data.total_outstanding;
                    loadedInvoices = data.invoices;

                    renderStudentProfile(data.student, data.wallet_credit, data.total_outstanding, data.invoices.length);
                    renderUnpaidInvoices(data.invoices);
                    currentStudent.full_name = data.student.name;
                    currentStudent.admission_number = data.student.admission_no;
                    currentStudent.roll_number = data.student.roll_no;
                    if (typeof renderPosQrCode === 'function') {
                        renderPosQrCode();
                    }
                }
            })
            .catch(() => {
                invoicesContainer.innerHTML = '<div class="text-center py-4 text-rose-500 text-xs">Failed to load student details.</div>';
            });
    };

    function renderStudentProfile(student, walletCredit, totalDue, unpaidCount) {
        profileBox.classList.remove('hidden');
        document.getElementById('student_name_display').textContent = student.name;
        document.getElementById('student_class_display').textContent = student.class_name + (student.section_name ? ' - ' + student.section_name : '');
        document.getElementById('student_adm_display').textContent = student.admission_no;
        document.getElementById('student_roll_display').textContent = student.roll_no;
        document.getElementById('student_guardian_display').textContent = student.guardian_name + (student.guardian_phone ? ' (' + student.guardian_phone + ')' : '');
        document.getElementById('student_initials').textContent = student.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();

        document.getElementById('student_wallet_display').textContent = 'Rs. ' + Number(walletCredit).toLocaleString('en-US', { minimumFractionDigits: 2 });
        document.getElementById('student_dues_display').textContent = 'Rs. ' + Number(totalDue).toLocaleString('en-US', { minimumFractionDigits: 2 });
        document.getElementById('student_unpaid_count_pill').textContent = unpaidCount + ' Unpaid Invoices';

        const btnApplyWallet = document.getElementById('btn_apply_wallet');
        btnApplyWallet.classList.toggle('hidden', walletCredit <= 0);
    }

    window.resetSelectedStudent = function() {
        currentStudent = null;
        loadedInvoices = [];
        profileBox.classList.add('hidden');
        searchInput.value = '';
        clearSearchBtn.classList.add('hidden');
        invoicesContainer.innerHTML = '<div class="text-center py-6 text-slate-400 text-xs"><i class="bi bi-person-bounding-box text-2xl block mb-1 text-slate-300 dark:text-slate-700"></i> Please select a student above to load outstanding dues and fee records.</div>';
        searchInput.focus();
    };

    // ── 2. INVOICES RENDERING & SELECTION ──

    function renderUnpaidInvoices(invoices) {
        invoicesContainer.innerHTML = '';

        if (!invoices || invoices.length === 0) {
            invoicesContainer.innerHTML = '<div class="text-center py-4 text-emerald-600 dark:text-teal-400 text-xs font-bold"><i class="bi bi-check2-circle text-base me-1"></i> No unpaid dues. Student is fully clear!</div>';
            return;
        }

        invoices.forEach(inv => {
            const card = document.createElement('div');
            card.className = 'p-3 rounded-2xl border border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-800 shadow-xs flex items-center justify-between gap-3 hover:border-[#0D7C66] transition';
            
            const itemPills = inv.items.map(i => `<span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-[10px] font-semibold">${i.name}</span>`).join(' ');

            card.innerHTML = `
                <div class="flex items-center gap-3 min-w-0">
                    <input type="checkbox" class="pos-invoice-cb w-4 h-4 rounded text-[#0D7C66] focus:ring-[#0D7C66]"
                           data-id="${inv.id}" data-due="${inv.due}" data-title="${inv.title}"
                           checked onchange="syncSelectedInvoicesToItems()">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-black text-slate-800 dark:text-slate-100 truncate">${inv.title}</span>
                            <span class="font-mono text-[10px] text-slate-400">#${inv.invoice_number}</span>
                            ${inv.is_overdue ? '<span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-rose-100 text-rose-700">Overdue</span>' : ''}
                        </div>
                        <div class="flex flex-wrap gap-1 mt-0.5">${itemPills}</div>
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <div class="text-xs font-black text-rose-600 dark:text-rose-400">Rs. ${Number(inv.due).toLocaleString('en-US', { minimumFractionDigits: 2 })}</div>
                    <div class="text-[10px] text-slate-400">Due: ${inv.due_date}</div>
                </div>
            `;
            invoicesContainer.appendChild(card);
        });

        syncSelectedInvoicesToItems();
    }

    window.selectAllUnpaidInvoices = function(check) {
        document.querySelectorAll('.pos-invoice-cb').forEach(cb => cb.checked = check);
        syncSelectedInvoicesToItems();
    };

    window.syncSelectedInvoicesToItems = function() {
        const checkedBoxes = document.querySelectorAll('.pos-invoice-cb:checked');
        itemsContainer.innerHTML = '';
        customRowCounter = 0;

        if (checkedBoxes.length === 0) {
            addCustomFeeRow('Monthly Tuition Fee', 3500);
            return;
        }

        checkedBoxes.forEach(cb => {
            const invId = cb.getAttribute('data-id');
            const inv = loadedInvoices.find(i => i.id == invId);
            if (inv && inv.items && inv.items.length > 0) {
                inv.items.forEach(it => {
                    addCustomFeeRow(it.name + ' (' + inv.title + ')', it.amount);
                });
            } else {
                addCustomFeeRow(cb.getAttribute('data-title'), cb.getAttribute('data-due'));
            }
        });
    };

    // ── 3. CUSTOM FEE LINE ITEMS ──

    window.addCustomFeeRow = function(name, amount) {
        const index = customRowCounter++;
        const row = document.createElement('div');
        row.className = 'pos-fee-item-row flex items-center gap-2 animate-fade-in';
        row.innerHTML = `
            <input type="text" name="items[${index}][name]" value="${name}" placeholder="Particulars Name" required
                   class="fee-name-input flex-1 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs font-bold bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#0D7C66]/20 focus:border-[#0D7C66] transition"
                   oninput="recomputePOSTotals()">
            <div class="w-36 relative">
                <span class="absolute left-3 top-2 text-slate-400 text-xs font-bold">Rs.</span>
                <input type="number" step="0.01" name="items[${index}][amount]" value="${amount}" placeholder="0.00" required
                       class="fee-amount-input w-full border border-slate-200 dark:border-slate-700 rounded-xl pl-9 pr-3 py-2 text-xs font-black bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 text-right focus:ring-2 focus:ring-[#0D7C66]/20 focus:border-[#0D7C66] transition"
                       oninput="recomputePOSTotals()">
            </div>
            <button type="button" onclick="removeCustomFeeRow(this)" title="Remove item" class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 hover:bg-rose-100 flex items-center justify-center text-xs transition">
                <i class="bi bi-trash-fill"></i>
            </button>
        `;
        itemsContainer.appendChild(row);
        recomputePOSTotals();
    };

    window.removeCustomFeeRow = function(btn) {
        if (itemsContainer.querySelectorAll('.pos-fee-item-row').length > 1) {
            btn.closest('.pos-fee-item-row').remove();
            recomputePOSTotals();
        } else {
            alert('At least one fee line item is required.');
        }
    };

    window.toggleFineWaiverBox = function() {
        const box = document.getElementById('fine_waiver_box');
        box.classList.toggle('hidden');
    };

    // ── 4. TOTALS, CHANGE & FINTECH CALCULATOR ──

    window.recomputePOSTotals = function() {
        const amountInputs = document.querySelectorAll('.fee-amount-input');
        let subtotal = 0;
        let count = 0;

        amountInputs.forEach(inp => {
            const val = parseFloat(inp.value) || 0;
            subtotal += val;
            count++;
        });

        const fineWaiver = parseFloat(document.getElementById('input_fine_waiver')?.value) || 0;
        const netPayable = Math.max(0, subtotal - fineWaiver);

        displayPayable.textContent = 'Rs. ' + netPayable.toLocaleString('en-US', { minimumFractionDigits: 2 });
        document.getElementById('pos_items_summary_count').textContent = count + ' fee item' + (count > 1 ? 's' : '') + (fineWaiver > 0 ? ' (Rs. ' + fineWaiver.toLocaleString() + ' concession applied)' : '');

        calculatePOSChange();

        if (typeof renderPosQrCode === 'function') {
            const method = document.getElementById('pos_payment_method')?.value;
            if (['fonepay', 'esewa', 'khalti', 'connectips', 'bank'].includes(method)) {
                renderPosQrCode();
            }
        }
    };

    function getNetPayable() {
        const amountInputs = document.querySelectorAll('.fee-amount-input');
        let subtotal = 0;
        amountInputs.forEach(inp => subtotal += (parseFloat(inp.value) || 0));
        const fineWaiver = parseFloat(document.getElementById('input_fine_waiver')?.value) || 0;
        return Math.max(0, subtotal - fineWaiver);
    }

    window.calculatePOSChange = function() {
        const payable = getNetPayable();
        const tendered = parseFloat(inputCash.value) || 0;

        if (!inputCash.value || tendered === 0) {
            posChangeCard.className = 'rounded-xl p-3.5 border transition-all text-center space-y-1 bg-slate-50 dark:bg-slate-800/70 border-slate-200 dark:border-slate-700';
            posChangeLabel.className = 'text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block';
            posChangeLabel.textContent = 'CASH RETURN STATUS';
            posChangeDisplay.className = 'text-xl font-extrabold tracking-tight text-slate-700 dark:text-slate-300';
            posChangeDisplay.textContent = 'Rs. 0.00';
            posChangeMessage.textContent = 'Enter cash tendered to compute change.';
            posDenomBreakdown.classList.add('hidden');
            depositChangeOption.classList.add('hidden');
            return;
        }

        const change = tendered - payable;

        if (change > 0) {
            posChangeCard.className = 'rounded-xl p-3.5 border-2 transition-all text-center space-y-1 bg-emerald-50/70 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-700 shadow-xs';
            posChangeLabel.className = 'text-[10px] font-bold text-emerald-800 dark:text-teal-300 uppercase tracking-wider block';
            posChangeLabel.textContent = 'CHANGE TO RETURN';
            posChangeDisplay.className = 'text-2xl font-black tracking-tight text-emerald-700 dark:text-teal-300';
            posChangeDisplay.textContent = 'Rs. ' + change.toLocaleString('en-US', { minimumFractionDigits: 2 });
            posChangeMessage.textContent = 'Hand Rs. ' + change.toLocaleString() + ' change back to student / parent.';

            // Nepali Rupee Notes Breakdown
            let rem = Math.floor(change);
            const notes = [1000, 500, 100, 50, 20, 10, 5];
            const list = [];
            notes.forEach(n => {
                const cnt = Math.floor(rem / n);
                if (cnt > 0) {
                    list.push(`${cnt} × Rs. ${n}`);
                    rem %= n;
                }
            });
            if (list.length > 0) {
                posDenomBreakdown.innerHTML = '<span class="font-bold text-emerald-800 dark:text-teal-300">Suggested Notes:</span> ' + list.join(' · ');
                posDenomBreakdown.classList.remove('hidden');
            } else {
                posDenomBreakdown.classList.add('hidden');
            }

            depositChangeOption.classList.remove('hidden');

        } else if (change === 0) {
            posChangeCard.className = 'rounded-xl p-3.5 border-2 transition-all text-center space-y-1 bg-sky-50/70 dark:bg-sky-950/40 border-sky-300 dark:border-sky-700';
            posChangeLabel.className = 'text-[10px] font-bold text-sky-800 dark:text-sky-300 uppercase tracking-wider block';
            posChangeLabel.textContent = 'EXACT CASH RECEIVED';
            posChangeDisplay.className = 'text-2xl font-black tracking-tight text-sky-700 dark:text-sky-300';
            posChangeDisplay.textContent = 'Rs. 0.00';
            posChangeMessage.textContent = 'Exact cash tendered — zero change required.';
            posDenomBreakdown.classList.add('hidden');
            depositChangeOption.classList.add('hidden');
        } else {
            const short = Math.abs(change);
            posChangeCard.className = 'rounded-xl p-3.5 border-2 transition-all text-center space-y-1 bg-amber-50/70 dark:bg-amber-950/40 border-amber-300 dark:border-amber-700';
            posChangeLabel.className = 'text-[10px] font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wider block';
            posChangeLabel.textContent = 'PARTIAL CASH TENDERED';
            posChangeDisplay.className = 'text-2xl font-black tracking-tight text-amber-700 dark:text-amber-300';
            posChangeDisplay.textContent = '- Rs. ' + short.toLocaleString('en-US', { minimumFractionDigits: 2 });
            posChangeMessage.textContent = 'Cash tendered is short by Rs. ' + short.toLocaleString() + '.';
            posDenomBreakdown.classList.add('hidden');
            depositChangeOption.classList.add('hidden');
        }
    };

    window.setCashPresetExact = function() {
        inputCash.value = getNetPayable();
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

    // ── MULTI-BANK & DYNAMIC COUNTER QR CONTROLS ──

    window.handlePaymentMethodChange = function(method) {
        const isCash = method === 'cash';
        const isDigital = ['fonepay', 'esewa', 'khalti', 'connectips', 'bank'].includes(method);

        document.getElementById('cash_tendered_container').classList.toggle('hidden', !isCash);
        posChangeCard.classList.toggle('hidden', !isCash);

        const qrContainer = document.getElementById('pos_bank_qr_container');
        if (qrContainer) {
            qrContainer.classList.toggle('hidden', !isDigital);
            if (isDigital) {
                renderPosQrCode();
            }
        }
    };

    window.renderPosQrCode = function() {
        const bankSelect = document.getElementById('pos_selected_bank_id');
        if (!bankSelect) return;

        const opt = bankSelect.selectedOptions[0];
        if (!opt) return;

        const bankName = opt.getAttribute('data-bank-name') || 'Commercial Bank';
        const accNo = opt.getAttribute('data-acc-no') || '';
        const accName = opt.getAttribute('data-acc-name') || '';
        const branch = opt.getAttribute('data-branch') || '';
        const merchantId = opt.getAttribute('data-merchant-id') || '';
        const qrImage = opt.getAttribute('data-qr-image') || '';

        // Update Card UI
        const cardBankName = document.getElementById('pos_card_bank_name');
        if (cardBankName) cardBankName.textContent = bankName;
        const cardBranch = document.getElementById('pos_card_branch');
        if (cardBranch) cardBranch.textContent = branch ? (branch + ' Branch') : '';
        const cardAccNo = document.getElementById('pos_card_acc_no');
        if (cardAccNo) cardAccNo.textContent = accNo || '—';
        const cardAccName = document.getElementById('pos_card_acc_name');
        if (cardAccName) cardAccName.textContent = accName || 'School Fee Collection A/C';

        // Update Customer Modal UI
        const custBankName = document.getElementById('customer_modal_bank_name');
        if (custBankName) custBankName.textContent = bankName;
        const custAccNo = document.getElementById('customer_modal_acc_no');
        if (custAccNo) custAccNo.textContent = 'A/C: ' + (accNo || '—');

        const netPayable = getNetPayable();
        const formattedAmt = 'Rs. ' + Number(netPayable).toLocaleString('en-US', { minimumFractionDigits: 2 });
        const posQrBadge = document.getElementById('pos_qr_badge_amount');
        if (posQrBadge) posQrBadge.textContent = formattedAmt;
        const custModalAmt = document.getElementById('customer_modal_amount');
        if (custModalAmt) custModalAmt.textContent = formattedAmt;

        const custModalStd = document.getElementById('customer_modal_student');
        if (custModalStd) {
            custModalStd.textContent = currentStudent
                ? ('Student: ' + (currentStudent.full_name || currentStudent.name) + ' (Adm: ' + (currentStudent.admission_number || currentStudent.admission_no || 'N/A') + ')')
                : 'Student: Not Selected';
        }

        const studentLabel = currentStudent ? ((currentStudent.full_name || currentStudent.name) + ' (' + (currentStudent.admission_number || currentStudent.admission_no || '') + ')') : 'Student Fee';
        const qrPayload = `fonepay://pay?merchant_id=${encodeURIComponent(merchantId || bankName)}&account=${encodeURIComponent(accNo)}&amount=${netPayable}&remarks=${encodeURIComponent(studentLabel)}`;

        // Render targets
        const renderTarget = document.getElementById('pos_qr_render_target');
        const custRenderTarget = document.getElementById('customer_qr_render_target');

        if (qrImage) {
            if (renderTarget) renderTarget.innerHTML = `<img src="${qrImage}" class="w-full h-full object-contain rounded-lg" alt="Standee QR">`;
            if (custRenderTarget) custRenderTarget.innerHTML = `<img src="${qrImage}" class="w-full h-full object-contain rounded-xl" alt="Standee QR">`;
        } else {
            const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=' + encodeURIComponent(qrPayload);
            if (typeof QRCode !== 'undefined') {
                try {
                    if (renderTarget) {
                        renderTarget.innerHTML = '';
                        new QRCode(renderTarget, {
                            text: qrPayload,
                            width: 112,
                            height: 112,
                            colorDark : "#064e3b",
                            colorLight : "#ffffff",
                            correctLevel : QRCode.CorrectLevel.M
                        });
                    }
                    if (custRenderTarget) {
                        custRenderTarget.innerHTML = '';
                        new QRCode(custRenderTarget, {
                            text: qrPayload,
                            width: 224,
                            height: 224,
                            colorDark : "#064e3b",
                            colorLight : "#ffffff",
                            correctLevel : QRCode.CorrectLevel.M
                        });
                    }
                    return;
                } catch(e) {
                    console.warn('QRCode JS error, using fallback image service', e);
                }
            }
            if (renderTarget) renderTarget.innerHTML = `<img src="${qrUrl}" class="w-full h-full object-contain" alt="QR Code">`;
            if (custRenderTarget) custRenderTarget.innerHTML = `<img src="${qrUrl}" class="w-full h-full object-contain" alt="Customer QR">`;
        }
    };

    window.handleSelectedBankChange = function(bankId) {
        renderPosQrCode();
    };

    window.copyBankAccountNumber = function() {
        const bankSelect = document.getElementById('pos_selected_bank_id');
        const opt = bankSelect ? bankSelect.selectedOptions[0] : null;
        const accNo = opt ? opt.getAttribute('data-acc-no') : document.getElementById('pos_card_acc_no')?.textContent;
        if (!accNo) return;

        navigator.clipboard.writeText(accNo.trim()).then(() => {
            alert('Bank A/C Number copied to clipboard: ' + accNo.trim());
        });
    };

    window.openCustomerFacingQrModal = function() {
        renderPosQrCode();
        document.getElementById('customerFacingQrModal').classList.remove('hidden');
    };

    window.closeCustomerFacingQrModal = function() {
        document.getElementById('customerFacingQrModal').classList.add('hidden');
    };

    // Close customer modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('customerFacingQrModal');
            if (modal && !modal.classList.contains('hidden')) {
                closeCustomerFacingQrModal();
            }
        }
    });

    window.applyWalletBalanceToPayment = function() {
        if (!currentStudent || currentStudent.wallet_credit <= 0) return;
        document.getElementById('pos_payment_method').value = 'wallet_credit';
        handlePaymentMethodChange('wallet_credit');
        alert('Payment mode switched to Student Prepaid Wallet (Available: Rs. ' + currentStudent.wallet_credit.toLocaleString() + ').');
    };

    window.switchSettlementMode = function(mode) {
        const heroLabel = document.getElementById('hero_amount_label');
        const invoicesSec = document.getElementById('unpaid_invoices_section');

        if (mode === 'advance_deposit') {
            heroLabel.textContent = 'Prepaid Wallet Deposit Amount';
            invoicesSec.classList.add('opacity-40', 'pointer-events-none');
            itemsContainer.innerHTML = '';
            addCustomFeeRow('Advance Fee Wallet Deposit', 5000);
        } else {
            heroLabel.textContent = 'Total Payable Amount';
            invoicesSec.classList.remove('opacity-40', 'pointer-events-none');
            syncSelectedInvoicesToItems();
        }
    };

    // ── 5. SUBMIT POS COLLECTION (AJAX SETTLEMENT) ──

    window.submitPOSCollection = function() {
        if (!currentStudent) {
            alert('Please select a student before settling payment.');
            searchInput.focus();
            return;
        }

        const netPayable = getNetPayable();
        if (netPayable <= 0) {
            alert('Payable amount must be greater than Rs. 0.');
            return;
        }

        const method = document.getElementById('pos_payment_method').value;
        const isCash = method === 'cash';
        const isDigital = ['fonepay', 'esewa', 'khalti', 'connectips', 'bank'].includes(method);
        const cashTendered = isCash ? (parseFloat(inputCash.value) || netPayable) : netPayable;
        const depositChangeToWallet = chkDepositChange.checked;
        const notes = document.getElementById('pos_cashier_notes').value;
        const fineWaiver = parseFloat(document.getElementById('input_fine_waiver')?.value) || 0;
        const waiverReason = document.getElementById('input_waiver_reason')?.value;

        const bankSelect = document.getElementById('pos_selected_bank_id');
        const bankAccountId = (isDigital && bankSelect && bankSelect.value) ? bankSelect.value : null;
        const transRef = document.getElementById('pos_transaction_reference')?.value?.trim() || null;

        // Collect fee items
        const nameInputs = document.querySelectorAll('.fee-name-input');
        const amountInputs = document.querySelectorAll('.fee-amount-input');
        const items = [];
        nameInputs.forEach((nameInp, idx) => {
            const nm = nameInp.value.trim();
            const amt = parseFloat(amountInputs[idx]?.value) || 0;
            if (nm && amt > 0) {
                items.push({ name: nm, amount: amt });
            }
        });

        // Collect selected invoice IDs
        const invoiceIds = [];
        document.querySelectorAll('.pos-invoice-cb:checked').forEach(cb => {
            invoiceIds.push(cb.getAttribute('data-id'));
        });

        const settleMode = document.querySelector('input[name="pos_settle_mode"]:checked')?.value || 'invoices';

        const payload = {
            student_id: currentStudent.id,
            settlement_mode: settleMode,
            invoice_ids: invoiceIds,
            items: items,
            invoice_title: items.length === 1 ? items[0].name : (items.slice(0, 2).map(i => i.name).join(' + ') + (items.length > 2 ? ' (+ ' + (items.length - 2) + ' more)' : '')),
            amount_paid: netPayable,
            payment_method: method,
            bank_account_id: bankAccountId,
            transaction_reference: transRef,
            cash_tendered: cashTendered,
            deposit_change_to_wallet: depositChangeToWallet,
            fine_waiver: fineWaiver,
            fine_waiver_reason: waiverReason,
            notes: notes,
            _token: '{{ csrf_token() }}'
        };

        const btnSettle = document.getElementById('btn_settle_pos');
        const originalText = document.getElementById('btn_settle_label').textContent;
        btnSettle.disabled = true;
        document.getElementById('btn_settle_label').textContent = 'Settling Transaction...';

        fetch('{{ route('finance.pos.collect') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            btnSettle.disabled = false;
            document.getElementById('btn_settle_label').textContent = originalText;

            if (data.success) {
                lastSettledReceiptData = data;
                openReceiptSuccessModal(data);
            } else {
                alert('Settlement failed: ' + (data.message || 'Unknown error.'));
            }
        })
        .catch(err => {
            btnSettle.disabled = false;
            document.getElementById('btn_settle_label').textContent = originalText;
            alert('Error recording payment. Please check network connection.');
        });
    };

    // ── 6. SUCCESS MODAL & INSTANT PRINTING ──

    function openReceiptSuccessModal(data) {
        document.getElementById('modal_receipt_no').textContent = '#' + data.receipt_number;
        document.getElementById('modal_student_name').textContent = data.student_name + ' (Adm: ' + data.admission_no + ')';
        document.getElementById('modal_amount_paid').textContent = 'Rs. ' + Number(data.amount_paid).toLocaleString('en-US', { minimumFractionDigits: 2 });
        document.getElementById('modal_payment_mode').textContent = data.payment_method;
        document.getElementById('modal_remaining_dues').textContent = 'Rs. ' + Number(data.total_outstanding).toLocaleString('en-US', { minimumFractionDigits: 2 });

        const bankRow = document.getElementById('modal_bank_row');
        if (data.bank_name) {
            document.getElementById('modal_bank_name').textContent = data.bank_name;
            bankRow.classList.remove('hidden');
        } else {
            bankRow.classList.add('hidden');
        }

        const refRow = document.getElementById('modal_ref_row');
        if (data.transaction_reference) {
            document.getElementById('modal_ref_no').textContent = data.transaction_reference;
            refRow.classList.remove('hidden');
        } else {
            refRow.classList.add('hidden');
        }

        const changeRow = document.getElementById('modal_change_row');
        if (data.change_returned > 0) {
            document.getElementById('modal_change_amount').textContent = 'Rs. ' + Number(data.change_returned).toLocaleString('en-US', { minimumFractionDigits: 2 });
            changeRow.classList.remove('hidden');
        } else {
            changeRow.classList.add('hidden');
        }

        const walletRow = document.getElementById('modal_wallet_row');
        if (data.wallet_deposited > 0) {
            document.getElementById('modal_wallet_deposited').textContent = 'Rs. ' + Number(data.wallet_deposited).toLocaleString('en-US', { minimumFractionDigits: 2 });
            walletRow.classList.remove('hidden');
        } else {
            walletRow.classList.add('hidden');
        }

        document.getElementById('pos_receipt_modal').classList.remove('hidden');
    }

    window.printModalReceipt = function(format) {
        if (!lastSettledReceiptData) return;
        const url = format === 'dual' ? lastSettledReceiptData.dual_receipt_url : lastSettledReceiptData.thermal_receipt_url;
        window.open(url, '_blank', 'width=800,height=900');
    };

    window.copyWhatsAppReceiptLink = function() {
        if (!lastSettledReceiptData) return;
        let text = `*FEE PAYMENT RECEIPT — ${lastSettledReceiptData.receipt_number}*\nStudent: ${lastSettledReceiptData.student_name}\nAmount Received: Rs. ${Number(lastSettledReceiptData.amount_paid).toLocaleString()}\nMode: ${lastSettledReceiptData.payment_method}`;
        if (lastSettledReceiptData.bank_name) {
            text += `\nBank: ${lastSettledReceiptData.bank_name}`;
        }
        if (lastSettledReceiptData.transaction_reference) {
            text += `\nTrace/Ref: ${lastSettledReceiptData.transaction_reference}`;
        }
        text += `\nRemaining Dues: Rs. ${Number(lastSettledReceiptData.total_outstanding).toLocaleString()}\nView Online Receipt: ${window.location.origin + lastSettledReceiptData.web_receipt_url}\nThank you!`;
        
        navigator.clipboard.writeText(text).then(() => {
            alert('Receipt message copied to clipboard! You can paste directly into WhatsApp to the parent.');
        });
    };

    window.closeModalAndNextStudent = function() {
        document.getElementById('pos_receipt_modal').classList.add('hidden');
        lastSettledReceiptData = null;
        resetSelectedStudent();
        window.location.reload();
    };

    // Auto-select student if passed in URL
    @if($selectedStudentId)
        selectStudent({{ $selectedStudentId }});
    @endif

})();
</script>
@endpush
