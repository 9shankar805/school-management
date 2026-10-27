@extends('layouts.app')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-7xl mx-auto">

    {{-- Breadcrumbs & Top Bar --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-10 h-10 rounded-2xl bg-[#D3F8EE] text-[#0D7C66] flex items-center justify-center font-bold text-xl shadow-xs border border-[#A7F3D0]">
                    <i class="bi bi-person-vcard"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Student Fee Profile</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Complete student ledger, invoice tracking, payments, and fee structure statement</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            {{-- Student Selector Dropdown / Search Modal Trigger --}}
            <div class="relative">
                <button type="button" onclick="document.getElementById('studentSelectModal').classList.remove('hidden')"
                        class="px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-[#0D7C66] rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 shadow-xs flex items-center gap-2 transition">
                    <i class="bi bi-person-search text-[#0D7C66]"></i>
                    <span>{{ $selectedStudent ? $selectedStudent->full_name : 'Select Student' }}</span>
                    <i class="bi bi-chevron-down text-slate-400 text-[10px]"></i>
                </button>
            </div>

            <a href="{{ route('payments.create') }}" class="px-4 py-2.5 bg-[#00A884] hover:bg-[#0D7C66] text-white rounded-xl text-xs font-bold shadow-xs flex items-center gap-1.5 transition">
                <i class="bi bi-plus-circle"></i> Create Invoice
            </a>

            <a href="{{ route('finance.overview') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold shadow-xs flex items-center gap-1.5 transition">
                <i class="bi bi-graph-up"></i> Finance Overview
            </a>
        </div>
    </div>

    @include('session-messages')

    @if(!$selectedStudent)
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-12 text-center">
            <div class="w-16 h-16 bg-[#D3F8EE] text-[#0D7C66] rounded-3xl flex items-center justify-center text-3xl mx-auto mb-4 border border-[#A7F3D0]">
                <i class="bi bi-people"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100">No Student Selected</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-md mx-auto">Please select a student from the list or create students first to view fee profiles and statements.</p>
            <button onclick="document.getElementById('studentSelectModal').classList.remove('hidden')"
                    class="mt-4 px-5 py-2.5 bg-[#00A884] hover:bg-[#0D7C66] text-white rounded-xl text-xs font-bold shadow-xs inline-flex items-center gap-2">
                <i class="bi bi-person-search"></i> Choose Student
            </button>
        </div>
    @else
        @php
            $currentClass = $selectedStudent->schoolClass->class_name 
                ?? optional($selectedStudent->promotions->last())->schoolClass->class_name 
                ?? 'Class ' . ($selectedStudent->class_id ?? 'N/A');
            $sectionName = $selectedStudent->section->section_name ?? 'A';
            $enrolledDate = $selectedStudent->created_at ? $selectedStudent->created_at->format('d M Y') : '01 Apr 2026';
            $structureTitle = $activeStructures->first()->name ?? 'Fee Structure - ' . $currentClass;
        @endphp

        {{-- ── 1. Student Header Profile Card ── --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    {{-- Student Avatar --}}
                    <div class="relative">
                        @if($selectedStudent->avatar)
                            <img src="{{ asset('storage/' . $selectedStudent->avatar) }}" alt="{{ $selectedStudent->full_name }}" class="w-16 h-16 rounded-2xl object-cover ring-2 ring-[#0D7C66]/20 shadow-sm">
                        @else
                            <div class="w-16 h-16 rounded-2xl bg-[#D3F8EE] dark:bg-teal-950/60 text-[#0D7C66] dark:text-teal-300 font-bold text-xl flex items-center justify-center ring-2 ring-[#A7F3D0] shadow-sm">
                                {{ strtoupper(substr($selectedStudent->first_name, 0, 1) . substr($selectedStudent->last_name, 0, 1)) }}
                            </div>
                        @endif
                        <span class="absolute -bottom-1 -right-1 w-5 h-5 bg-emerald-500 rounded-full ring-2 ring-white dark:ring-slate-900 flex items-center justify-center text-[10px] text-white">
                            <i class="bi bi-check"></i>
                        </span>
                    </div>

                    <div>
                        <div class="flex items-center gap-2.5 flex-wrap mb-1">
                            <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100">{{ $selectedStudent->full_name }}</h2>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#D3F8EE] text-[#0D7C66] border border-[#A7F3D0]">
                                {{ $currentClass }} {{ $sectionName }}
                            </span>
                            @if($selectedStudent->admission_no)
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                Adm: #{{ $selectedStudent->admission_no }}
                            </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-2 flex-wrap">
                            <span><i class="bi bi-calendar-check me-1 text-[#0D7C66]"></i> Enrolled {{ $enrolledDate }}</span>
                            <span>•</span>
                            <span><i class="bi bi-file-earmark-ruled me-1 text-slate-400"></i> {{ $structureTitle }}</span>
                            <span>•</span>
                            <span class="text-slate-600 dark:text-slate-300 font-medium">Annual</span>
                        </p>
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="flex items-center gap-2">
                    <a href="{{ route('payments.create', ['student_id' => $selectedStudent->id]) }}" class="px-3.5 py-2 bg-[#D3F8EE] hover:bg-[#A7F3D0] text-[#0D7C66] rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                        <i class="bi bi-receipt"></i> Issue Invoice
                    </a>
                    <a href="{{ route('payments.student-ledger', $selectedStudent->id) }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold transition flex items-center gap-1.5">
                        <i class="bi bi-journal-text"></i> Full Ledger
                    </a>
                </div>
            </div>

            {{-- ── Tab Strip ── --}}
            <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center gap-1 overflow-x-auto no-scrollbar">
                <button type="button" onclick="switchProfileTab('overview')" id="tab-btn-overview"
                        class="profile-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-bold flex items-center gap-2 transition bg-[#D3F8EE] text-[#0D7C66] border border-[#A7F3D0]">
                    <i class="bi bi-grid-fill"></i> Overview
                </button>
                <button type="button" onclick="switchProfileTab('structures')" id="tab-btn-structures"
                        class="profile-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition flex items-center gap-2 border border-transparent">
                    <i class="bi bi-file-earmark-text"></i> Fee Structure
                </button>
                <button type="button" onclick="switchProfileTab('invoices')" id="tab-btn-invoices"
                        class="profile-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition flex items-center gap-2 border border-transparent">
                    <i class="bi bi-receipt"></i> Invoices <span class="px-1.5 py-0.2 bg-slate-200 dark:bg-slate-700 text-[10px] rounded-full">{{ $invoices->count() }}</span>
                </button>
                <button type="button" onclick="switchProfileTab('payments')" id="tab-btn-payments"
                        class="profile-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition flex items-center gap-2 border border-transparent">
                    <i class="bi bi-credit-card"></i> Payments <span class="px-1.5 py-0.2 bg-slate-200 dark:bg-slate-700 text-[10px] rounded-full">{{ $payments->count() }}</span>
                </button>
                <button type="button" onclick="switchProfileTab('receipts')" id="tab-btn-receipts"
                        class="profile-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition flex items-center gap-2 border border-transparent">
                    <i class="bi bi-receipt-cutoff"></i> Receipts <span class="px-1.5 py-0.2 bg-slate-200 dark:bg-slate-700 text-[10px] rounded-full">{{ $payments->count() }}</span>
                </button>
                <button type="button" onclick="switchProfileTab('discounts')" id="tab-btn-discounts"
                        class="profile-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition flex items-center gap-2 border border-transparent">
                    <i class="bi bi-percent"></i> Discounts <span class="px-1.5 py-0.2 bg-slate-200 dark:bg-slate-700 text-[10px] rounded-full">{{ $discountsCount }}</span>
                </button>
                <button type="button" onclick="switchProfileTab('ledger')" id="tab-btn-ledger"
                        class="profile-tab-btn px-3.5 py-1.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition flex items-center gap-2 border border-transparent">
                    <i class="bi bi-journal-check"></i> Ledger History
                </button>
            </div>
        </div>

        {{-- ── 2. Quick Action Icon Chips Row (Inkwelly Style) ── --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-5 mb-6">
            <div class="grid grid-cols-5 sm:grid-cols-10 gap-3 text-center">
                
                {{-- 1. Payments --}}
                <div class="flex flex-col items-center gap-1.5 group cursor-pointer" onclick="switchProfileTab('payments')">
                    <div class="relative w-12 h-12 rounded-full bg-[#D3F8EE] text-[#0D7C66] flex items-center justify-center text-lg shadow-xs group-hover:scale-105 transition">
                        <i class="bi bi-credit-card-2-front"></i>
                        <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-slate-800 text-white text-[9px] font-bold flex items-center justify-center">{{ $payments->count() }}</span>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Payments</span>
                </div>

                {{-- 2. Receipts --}}
                <div class="flex flex-col items-center gap-1.5 group cursor-pointer" onclick="switchProfileTab('receipts')">
                    <div class="relative w-12 h-12 rounded-full bg-sky-100 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 flex items-center justify-center text-lg shadow-xs group-hover:scale-105 transition">
                        <i class="bi bi-receipt"></i>
                        <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-slate-800 text-white text-[9px] font-bold flex items-center justify-center">{{ $payments->count() }}</span>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Receipts</span>
                </div>

                {{-- 3. Refunds --}}
                <div class="flex flex-col items-center gap-1.5 group cursor-pointer" onclick="switchProfileTab('ledger')">
                    <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center text-lg shadow-xs group-hover:scale-105 transition">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Refunds</span>
                </div>

                {{-- 4. Cheques --}}
                <div class="flex flex-col items-center gap-1.5 group cursor-pointer" onclick="switchProfileTab('payments')">
                    <div class="w-12 h-12 rounded-full bg-sky-100 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 flex items-center justify-center text-lg shadow-xs group-hover:scale-105 transition">
                        <i class="bi bi-bank"></i>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Cheques</span>
                </div>

                {{-- 5. Discounts --}}
                <div class="flex flex-col items-center gap-1.5 group cursor-pointer" onclick="switchProfileTab('discounts')">
                    <div class="w-12 h-12 rounded-full bg-[#D3F8EE] text-[#0D7C66] flex items-center justify-center text-lg shadow-xs group-hover:scale-105 transition">
                        <i class="bi bi-percent"></i>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Discounts</span>
                </div>

                {{-- 6. Scholarships --}}
                <div class="flex flex-col items-center gap-1.5 group cursor-pointer" onclick="switchProfileTab('discounts')">
                    <div class="w-12 h-12 rounded-full bg-[#D3F8EE] text-[#0D7C66] flex items-center justify-center text-lg shadow-xs group-hover:scale-105 transition">
                        <i class="bi bi-stars"></i>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Scholarships</span>
                </div>

                {{-- 7. Credits --}}
                <div class="flex flex-col items-center gap-1.5 group cursor-pointer" onclick="switchProfileTab('ledger')">
                    <div class="w-12 h-12 rounded-full bg-[#D3F8EE] text-[#0D7C66] flex items-center justify-center text-lg shadow-xs group-hover:scale-105 transition">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Credits</span>
                </div>

                {{-- 8. Waivers --}}
                <div class="flex flex-col items-center gap-1.5 group cursor-pointer" onclick="switchProfileTab('discounts')">
                    <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center text-lg shadow-xs group-hover:scale-105 transition">
                        <i class="bi bi-file-earmark-check"></i>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Waivers</span>
                </div>

                {{-- 9. Fines --}}
                <div class="flex flex-col items-center gap-1.5 group cursor-pointer" onclick="switchProfileTab('invoices')">
                    <div class="relative w-12 h-12 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-300 flex items-center justify-center text-lg shadow-xs group-hover:scale-105 transition">
                        <i class="bi bi-exclamation-triangle"></i>
                        @if($pendingInvoicesCount > 0)
                        <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-slate-800 text-white text-[9px] font-bold flex items-center justify-center">{{ $pendingInvoicesCount }}</span>
                        @endif
                    </div>
                    <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Fines</span>
                </div>

                {{-- 10. Carry-forward --}}
                <div class="flex flex-col items-center gap-1.5 group cursor-pointer" onclick="switchProfileTab('ledger')">
                    <div class="w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 flex items-center justify-center text-lg shadow-xs group-hover:scale-105 transition">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Carry-fwd</span>
                </div>

            </div>
        </div>

        {{-- ── 3. Main Two-Column Grid ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ── LEFT MAIN COLUMN (Tabs Panes) ── --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- TAB PANE 1: OVERVIEW & INVOICES --}}
                <div id="pane-overview" class="profile-tab-pane space-y-6">
                    
                    {{-- Invoices Section Card --}}
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Invoices</h3>
                                <p class="text-xs text-slate-400">{{ $invoices->count() }} invoices • {{ $pendingInvoicesCount }} pending generation/payment</p>
                            </div>
                            <button type="button" onclick="switchProfileTab('invoices')" class="text-xs font-bold text-[#0D7C66] hover:underline">View all</button>
                        </div>

                        <div class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($invoices as $inv)
                                @php
                                    $isPaid = $inv->status === 'paid';
                                    $isOverdue = $inv->due_date && \Carbon\Carbon::parse($inv->due_date)->isPast() && !$isPaid;
                                    $invPaidAmount = $inv->payments->sum('amount');
                                    $invDue = max(0, $inv->amount - $invPaidAmount);
                                @endphp
                                <div class="px-6 py-4 flex items-center justify-between gap-4 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition flex-wrap">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-mono text-xs font-bold text-slate-800 dark:text-slate-100">{{ $inv->invoice_number ?? 'INV-'.str_pad($inv->id, 6, '0', STR_PAD_LEFT) }}</span>
                                            @if($isPaid)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">Paid</span>
                                            @elseif($isOverdue)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300">Overdue</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">Pending</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ $inv->title }}</span>
                                            @if($inv->due_date)
                                                • Due {{ \Carbon\Carbon::parse($inv->due_date)->format('d M Y') }}
                                            @endif
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <div class="text-right">
                                            <div class="text-sm font-bold text-slate-800 dark:text-slate-100">
                                                Rs. {{ number_format($inv->amount, 2) }}
                                            </div>
                                            <div class="text-[11px] {{ $isPaid ? 'text-emerald-600 font-semibold' : 'text-rose-600 font-semibold' }}">
                                                {{ $isPaid ? 'paid in full' : 'unpaid (Rs. '.number_format($invDue, 2).' due)' }}
                                            </div>
                                        </div>

                                        @if(!$isPaid)
                                            <a href="{{ route('payments.pay', $inv->id) }}" class="px-4 py-2 bg-[#00A884] hover:bg-[#0D7C66] text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-1.5">
                                                <i class="bi bi-credit-card"></i> Pay
                                            </a>
                                        @else
                                            @if($inv->payments->isNotEmpty())
                                            <a href="{{ route('finance.receipt', $inv->payments->last()->id) }}" target="_blank" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-[#0D7C66] flex items-center justify-center text-xs transition" title="Print Receipt">
                                                <i class="bi bi-receipt"></i>
                                            </a>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="px-6 py-10 text-center text-slate-400 text-xs">
                                    <i class="bi bi-receipt text-3xl text-slate-300 dark:text-slate-700 block mb-2"></i>
                                    No invoices generated yet for this student.
                                </div>
                            @endforelse
                        </div>

                        {{-- Not Yet Generated Installment Section --}}
                        <div class="bg-slate-50/80 dark:bg-slate-800/40 px-6 py-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs font-bold text-slate-400 tracking-wider">
                            <span>UPCOMING INSTALLMENT</span>
                            <span>AUTO-BILLING</span>
                        </div>
                        <div class="px-6 py-4 flex items-center justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-xs text-slate-800 dark:text-slate-200">Next Monthly Cycle</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold">Installment {{ $invoices->count() + 1 }}</span>
                                </div>
                                <p class="text-xs text-slate-400 mt-0.5">Awaiting invoice • Scheduled on 1st of next month</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <a href="{{ route('payments.create', ['student_id' => $selectedStudent->id]) }}" class="px-3.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 hover:border-[#0D7C66] text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold shadow-xs transition inline-flex items-center gap-1.5">
                                    <i class="bi bi-plus-lg text-[#0D7C66]"></i> Generate
                                </a>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- TAB PANE 2: FEE STRUCTURE --}}
                <div id="pane-structures" class="profile-tab-pane hidden space-y-6">
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Assigned Fee Structures</h3>
                                <p class="text-xs text-slate-400">Class fee templates and components applicable to {{ $selectedStudent->full_name }}</p>
                            </div>
                            <a href="{{ route('finance.structures') }}" class="text-xs font-bold text-[#0D7C66] hover:underline">Manage All</a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs">
                                <thead class="bg-slate-50/50 dark:bg-slate-800/20 text-slate-400 font-bold border-b border-slate-100 dark:border-slate-800">
                                    <tr>
                                        <th class="py-3 px-4 text-left">Fee Head / Structure</th>
                                        <th class="py-3 px-4 text-left">Category</th>
                                        <th class="py-3 px-4 text-left">Frequency</th>
                                        <th class="py-3 px-4 text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @forelse($activeStructures as $struct)
                                    <tr>
                                        <td class="py-3 px-4 font-bold text-slate-800 dark:text-slate-200">{{ $struct->name }}</td>
                                        <td class="py-3 px-4 text-slate-500">{{ $struct->category->name ?? 'Tuition' }}</td>
                                        <td class="py-3 px-4 text-slate-500 capitalize">{{ $struct->frequency ?? 'Monthly' }}</td>
                                        <td class="py-3 px-4 text-right font-bold text-slate-800 dark:text-slate-100">Rs. {{ number_format($struct->amount, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-slate-400">No specific fee structure linked to this class.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- TAB PANE 3: ALL INVOICES --}}
                <div id="pane-invoices" class="profile-tab-pane hidden space-y-6">
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">All Issued Invoices</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs">
                                <thead class="bg-slate-50/50 dark:bg-slate-800/20 text-slate-400 font-bold border-b border-slate-100 dark:border-slate-800">
                                    <tr>
                                        <th class="py-3 px-4 text-left">Invoice #</th>
                                        <th class="py-3 px-4 text-left">Title</th>
                                        <th class="py-3 px-4 text-right">Amount</th>
                                        <th class="py-3 px-4 text-center">Status</th>
                                        <th class="py-3 px-4 text-left">Due Date</th>
                                        <th class="py-3 px-4 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @forelse($invoices as $inv)
                                    <tr>
                                        <td class="py-3 px-4 font-mono font-bold text-[#0D7C66]">{{ $inv->invoice_number ?? 'INV-'.$inv->id }}</td>
                                        <td class="py-3 px-4 text-slate-700 dark:text-slate-300">{{ $inv->title }}</td>
                                        <td class="py-3 px-4 text-right font-bold text-slate-800 dark:text-slate-100">Rs. {{ number_format($inv->amount, 2) }}</td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $inv->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                                {{ ucfirst($inv->status) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-slate-500">{{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d M Y') : '—' }}</td>
                                        <td class="py-3 px-4 text-right">
                                            @if($inv->status !== 'paid')
                                                <a href="{{ route('payments.pay', $inv->id) }}" class="px-3 py-1 bg-[#00A884] text-white rounded-lg font-bold hover:bg-[#0D7C66]">Pay</a>
                                            @else
                                                <span class="text-emerald-600 font-bold"><i class="bi bi-check-circle"></i> Paid</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="6" class="py-8 text-center text-slate-400">No invoices found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- TAB PANE 4: PAYMENTS & RECEIPTS --}}
                <div id="pane-payments" class="profile-tab-pane hidden space-y-6">
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">Payment History & Transactions</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs">
                                <thead class="bg-slate-50/50 dark:bg-slate-800/20 text-slate-400 font-bold border-b border-slate-100 dark:border-slate-800">
                                    <tr>
                                        <th class="py-3 px-4 text-left">Receipt #</th>
                                        <th class="py-3 px-4 text-left">Invoice</th>
                                        <th class="py-3 px-4 text-left">Method</th>
                                        <th class="py-3 px-4 text-right">Amount</th>
                                        <th class="py-3 px-4 text-left">Date</th>
                                        <th class="py-3 px-4 text-right">Receipt</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @forelse($payments as $pay)
                                    <tr>
                                        <td class="py-3 px-4 font-mono font-bold text-slate-700 dark:text-slate-300">{{ $pay->receipt_number ?? 'REC-'.$pay->id }}</td>
                                        <td class="py-3 px-4 text-slate-500">{{ $pay->invoice->title ?? '—' }}</td>
                                        <td class="py-3 px-4 uppercase text-slate-600 dark:text-slate-400">{{ $pay->payment_method ?? 'Cash' }}</td>
                                        <td class="py-3 px-4 text-right font-bold text-emerald-600">Rs. {{ number_format($pay->amount, 2) }}</td>
                                        <td class="py-3 px-4 text-slate-500">{{ \Carbon\Carbon::parse($pay->payment_date)->format('d M Y') }}</td>
                                        <td class="py-3 px-4 text-right">
                                            <a href="{{ route('finance.receipt', $pay->id) }}" target="_blank" class="px-3 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-[#D3F8EE] text-[#0D7C66] rounded-lg font-bold transition">
                                                <i class="bi bi-printer me-1"></i> Print
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="6" class="py-8 text-center text-slate-400">No payment records yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- TAB PANE 5: RECEIPTS --}}
                <div id="pane-receipts" class="profile-tab-pane hidden space-y-6">
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">Official Fee Receipts</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @forelse($payments as $p)
                            <div class="p-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20 flex items-center justify-between">
                                <div>
                                    <div class="font-mono text-xs font-bold text-[#0D7C66]">{{ $p->receipt_number ?? 'REC-'.$p->id }}</div>
                                    <div class="text-sm font-bold text-slate-800 dark:text-slate-100 mt-1">Rs. {{ number_format($p->amount, 2) }}</div>
                                    <div class="text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($p->payment_date)->format('d M Y') }} · {{ ucfirst($p->payment_method ?? 'Cash') }}</div>
                                </div>
                                <a href="{{ route('finance.receipt', $p->id) }}" target="_blank" class="px-3 py-2 bg-[#D3F8EE] text-[#0D7C66] rounded-xl text-xs font-bold hover:bg-[#A7F3D0] transition">
                                    <i class="bi bi-file-pdf me-1"></i> Download
                                </a>
                            </div>
                            @empty
                            <div class="col-span-2 py-8 text-center text-slate-400 text-xs">No receipts available.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- TAB PANE 6: DISCOUNTS --}}
                <div id="pane-discounts" class="profile-tab-pane hidden space-y-6">
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Discounts & Waivers</h3>
                                <p class="text-xs text-slate-400">Assigned scholarships, sibling concessions, or merit discounts</p>
                            </div>
                            <a href="{{ route('finance.discounts') }}" class="text-xs font-bold text-[#0D7C66] hover:underline">Manage Discounts</a>
                        </div>
                        <div class="p-6 text-center text-slate-400 text-xs">
                            <i class="bi bi-percent text-3xl text-[#0D7C66] block mb-2"></i>
                            Total discounts applied across invoices: <strong class="text-slate-700 dark:text-slate-200">Rs. {{ number_format($discountAmount, 2) }}</strong>
                        </div>
                    </div>
                </div>

                {{-- TAB PANE 7: LEDGER HISTORY --}}
                <div id="pane-ledger" class="profile-tab-pane hidden space-y-6">
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">Complete Ledger Statement</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs">
                                <thead class="bg-slate-50/50 dark:bg-slate-800/20 text-slate-400 font-bold border-b border-slate-100 dark:border-slate-800">
                                    <tr>
                                        <th class="py-3 px-4 text-left">Date</th>
                                        <th class="py-3 px-4 text-left">Type</th>
                                        <th class="py-3 px-4 text-left">Description</th>
                                        <th class="py-3 px-4 text-right">Debit</th>
                                        <th class="py-3 px-4 text-right">Credit</th>
                                        <th class="py-3 px-4 text-right">Balance</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @forelse($ledger as $entry)
                                    <tr>
                                        <td class="py-3 px-4 text-slate-500">{{ $entry->entry_date->format('d M Y') }}</td>
                                        <td class="py-3 px-4 uppercase text-[10px] font-bold text-slate-600 dark:text-slate-400">{{ $entry->type }}</td>
                                        <td class="py-3 px-4 text-slate-700 dark:text-slate-300">{{ $entry->description }}</td>
                                        <td class="py-3 px-4 text-right {{ $entry->debit > 0 ? 'text-rose-600 font-bold' : 'text-slate-400' }}">{{ $entry->debit > 0 ? 'Rs. '.number_format($entry->debit, 2) : '—' }}</td>
                                        <td class="py-3 px-4 text-right {{ $entry->credit > 0 ? 'text-emerald-600 font-bold' : 'text-slate-400' }}">{{ $entry->credit > 0 ? 'Rs. '.number_format($entry->credit, 2) : '—' }}</td>
                                        <td class="py-3 px-4 text-right font-bold text-slate-800 dark:text-slate-100">Rs. {{ number_format($entry->balance, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="6" class="py-8 text-center text-slate-400">No ledger entries.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── RIGHT SIDEBAR COLUMN (Fee Breakdown, Recent Activity, Alerts) ── --}}
            <div class="space-y-6">

                {{-- 1. Fee Breakdown Card (Exact Match to Inkwelly Reference) --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <span class="w-8 h-8 rounded-xl bg-[#D3F8EE] text-[#0D7C66] flex items-center justify-center text-sm font-bold border border-[#A7F3D0]">
                            <i class="bi bi-file-earmark-spreadsheet"></i>
                        </span>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Fee breakdown</h3>
                    </div>

                    <div class="space-y-3 text-xs mb-4">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">CHARGES</div>
                        
                        <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                            <span>Base fee</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">Rs. {{ number_format($baseFee, 2) }}</span>
                        </div>

                        <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                            <span>Late fee / Fines</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">+Rs. {{ number_format($fineAmount, 2) }}</span>
                        </div>

                        @if($discountAmount > 0)
                        <div class="flex items-center justify-between text-emerald-600">
                            <span>Discounts</span>
                            <span class="font-bold">-Rs. {{ number_format($discountAmount, 2) }}</span>
                        </div>
                        @endif

                        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-slate-700 dark:text-slate-300">
                            <span class="font-semibold">Net payable</span>
                            <span class="font-bold text-slate-900 dark:text-slate-100">Rs. {{ number_format($totalCharged, 2) }}</span>
                        </div>

                        <div class="flex items-center justify-between text-slate-500">
                            <span>Paid</span>
                            <span class="font-semibold text-emerald-600">-Rs. {{ number_format($totalPaid, 2) }}</span>
                        </div>
                    </div>

                    {{-- Outstanding Highlight Box --}}
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-1.5 text-xs font-bold text-slate-500">
                                <i class="bi bi-exclamation-circle text-rose-500"></i> Outstanding
                            </div>
                            <div class="text-2xl font-extrabold text-rose-600 tracking-tight">
                                Rs. {{ number_format($totalDue, 2) }}
                            </div>
                        </div>

                        {{-- Progress Bar --}}
                        <div class="mt-3">
                            <div class="flex items-center justify-between text-[11px] font-bold text-slate-400 mb-1.5">
                                <span>COLLECTED</span>
                                <span class="text-[#0D7C66]">{{ $collectionRate }}%</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-[#00A884] to-[#0D9488] rounded-full transition-all duration-500" style="width: {{ $collectionRate }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Recent Activity Card --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center text-xs font-bold">
                                <i class="bi bi-activity"></i>
                            </span>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Recent activity</h3>
                        </div>
                        <button type="button" onclick="switchProfileTab('payments')" class="text-xs font-bold text-[#0D7C66] hover:underline">View all &rarr;</button>
                    </div>

                    <div class="space-y-3">
                        @forelse($payments->take(4) as $pay)
                        <div class="flex items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-7 h-7 rounded-lg bg-[#D3F8EE] text-[#0D7C66] flex items-center justify-center text-xs shrink-0">
                                    <i class="bi bi-arrow-down-left"></i>
                                </span>
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-800 dark:text-slate-200 truncate">Payment - {{ $pay->receipt_number ?? 'FEE-PAY-'.$pay->id }}</div>
                                    <div class="text-[10px] text-slate-400 truncate">{{ \Carbon\Carbon::parse($pay->payment_date)->format('d M Y') }} • {{ $pay->invoice->title ?? 'Fee Invoice' }}</div>
                                </div>
                            </div>
                            <span class="font-bold text-emerald-600 shrink-0">+Rs. {{ number_format($pay->amount, 0) }}</span>
                        </div>
                        @empty
                        <div class="py-4 text-center text-slate-400 text-xs">No recent transactions.</div>
                        @endforelse
                    </div>
                </div>

                {{-- 3. At a Glance Alerts Card --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="bi bi-info-circle text-[#0D7C66]"></i>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">At a glance</h3>
                    </div>

                    <div class="space-y-2">
                        @if($pendingInvoicesCount > 0)
                        <div class="p-3 rounded-2xl bg-rose-50 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900/40 flex items-center justify-between cursor-pointer hover:bg-rose-100/50 transition" onclick="switchProfileTab('invoices')">
                            <div class="flex items-center gap-2 text-xs font-bold text-rose-700 dark:text-rose-300">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <span>{{ $pendingInvoicesCount }} open pending invoices</span>
                            </div>
                            <i class="bi bi-chevron-right text-rose-400 text-xs"></i>
                        </div>
                        @endif

                        <div class="p-3 rounded-2xl bg-[#D3F8EE]/60 dark:bg-teal-950/20 border border-[#A7F3D0]/60 flex items-center justify-between cursor-pointer hover:bg-[#D3F8EE] transition" onclick="switchProfileTab('structures')">
                            <div class="flex items-center gap-2 text-xs font-bold text-[#0D7C66] dark:text-teal-300">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>{{ $activeStructures->count() }} active fee components</span>
                            </div>
                            <i class="bi bi-chevron-right text-[#0D7C66] text-xs"></i>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    @endif

</div>

{{-- ── Student Selector Modal ── --}}
<div id="studentSelectModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-200 dark:border-slate-800 animate-in fade-in zoom-in-95 duration-150">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-[#D3F8EE] text-[#0D7C66] flex items-center justify-center font-bold">
                    <i class="bi bi-person-search"></i>
                </span>
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Select Student Fee Profile</h3>
            </div>
            <button type="button" onclick="document.getElementById('studentSelectModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 transition">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="p-4 border-b border-slate-100 dark:border-slate-800">
            <div class="relative">
                <i class="bi bi-search absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                <input type="text" id="studentSearchInput" placeholder="Search by name, admission # or class..."
                       class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:outline-hidden focus:border-[#0D7C66]"
                       onkeyup="filterStudentList()">
            </div>
        </div>

        <div class="max-h-80 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800" id="studentListContainer">
            @foreach($allStudents as $st)
            @php
                $stClass = $st->schoolClass->class_name ?? optional($st->promotions->last())->schoolClass->class_name ?? 'Class ' . ($st->class_id ?? '');
            @endphp
            <a href="{{ route('finance.fee-profiles', $st->id) }}" class="student-item-row px-6 py-3.5 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800/60 transition block">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-[#D3F8EE] text-[#0D7C66] font-bold text-xs flex items-center justify-center">
                        {{ strtoupper(substr($st->first_name, 0, 1) . substr($st->last_name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="text-xs font-bold text-slate-800 dark:text-slate-100 student-name">{{ $st->full_name }}</div>
                        <div class="text-[11px] text-slate-400 student-meta">Adm: #{{ $st->admission_no ?? '—' }} • {{ $stClass }}</div>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold {{ $selectedStudent && $selectedStudent->id === $st->id ? 'bg-[#00A884] text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">
                    {{ $selectedStudent && $selectedStudent->id === $st->id ? 'Active' : 'Select' }}
                </span>
            </a>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
    function switchProfileTab(tabName) {
        // Hide all panes
        document.querySelectorAll('.profile-tab-pane').forEach(p => p.classList.add('hidden'));
        
        // Show selected pane
        const targetPane = document.getElementById('pane-' + tabName);
        if (targetPane) targetPane.classList.remove('hidden');

        // Reset tab button styles
        document.querySelectorAll('.profile-tab-btn').forEach(btn => {
            btn.classList.remove('bg-[#D3F8EE]', 'text-[#0D7C66]', 'border-[#A7F3D0]');
            btn.classList.add('text-slate-600', 'dark:text-slate-400', 'border-transparent');
        });

        // Activate selected button
        const activeBtn = document.getElementById('tab-btn-' + tabName);
        if (activeBtn) {
            activeBtn.classList.remove('text-slate-600', 'dark:text-slate-400', 'border-transparent');
            activeBtn.classList.add('bg-[#D3F8EE]', 'text-[#0D7C66]', 'border-[#A7F3D0]');
        }
    }

    function filterStudentList() {
        const query = document.getElementById('studentSearchInput').value.toLowerCase();
        const rows = document.querySelectorAll('.student-item-row');
        rows.forEach(row => {
            const name = row.querySelector('.student-name')?.innerText.toLowerCase() || '';
            const meta = row.querySelector('.student-meta')?.innerText.toLowerCase() || '';
            if (name.includes(query) || meta.includes(query)) {
                row.style.display = 'flex';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>
@endpush
@endsection
