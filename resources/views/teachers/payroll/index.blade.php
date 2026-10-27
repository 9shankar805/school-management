@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50 dark:bg-slate-950 p-4 sm:p-6 lg:p-8">

    {{-- Breadcrumb & Top Bar --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mb-1">
                <span>Faculty Administration</span>
                <i class="bi bi-chevron-right text-[10px] text-slate-400"></i>
                <span class="text-slate-500 dark:text-slate-400">Compensation & Payroll</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
                <span>Faculty Payroll Hub</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                    <i class="bi bi-patch-check-fill me-1 text-indigo-500"></i> Veda Enterprise
                </span>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Manage monthly teacher disbursements, automated TDS/CIT/SSF statutory deductions, and banking advice.
            </p>
        </div>

        {{-- Top Action Group --}}
        <div class="flex flex-wrap items-center gap-2.5">
            @if($isPrivileged)
            {{-- Bulk Generate Trigger --}}
            <button type="button" onclick="openModal('bulkGenerateModal')"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 shadow-sm shadow-indigo-200 dark:shadow-none transition-all active:scale-[0.98]">
                <i class="bi bi-lightning-charge-fill text-amber-300"></i>
                <span>Auto-Generate Payroll</span>
            </button>

            {{-- New / Adjust Slip --}}
            <button type="button" onclick="openCreateSlipModal()"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/60 shadow-sm transition-all active:scale-[0.98]">
                <i class="bi bi-plus-circle text-indigo-600 dark:text-indigo-400"></i>
                <span>Adjust / New Slip</span>
            </button>

            {{-- Export Bank Advice CSV --}}
            <a href="{{ route('teacher.payroll.export.csv', ['month' => $month, 'year' => $year]) }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 shadow-sm transition-all">
                <i class="bi bi-file-earmark-spreadsheet text-emerald-600 dark:text-emerald-400"></i>
                <span>Bank Advice (CSV)</span>
            </a>
            @endif

            {{-- Print Button --}}
            <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/60 shadow-sm transition-all">
                <i class="bi bi-printer"></i>
                <span class="hidden sm:inline">Print Ledger</span>
            </button>
        </div>
    </div>

    {{-- Status Alert --}}
    @if(session('status'))
    <div class="mb-6 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 flex items-center justify-between shadow-sm animate-fade-in">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold">
                <i class="bi bi-check-lg"></i>
            </div>
            <div>
                <p class="font-semibold text-sm">{{ session('status') }}</p>
            </div>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-400">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    @endif

    {{-- Period Filter Ribbon --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-4 mb-6 shadow-sm">
        <form method="GET" action="{{ route('teacher.payroll.index') }}" id="periodFilterForm" class="flex flex-wrap items-center justify-between gap-4">
            <input type="hidden" name="status" value="{{ $statusFilter }}">

            <div class="flex flex-wrap items-center gap-3">
                {{-- Calendar Mode Selector (BS vs AD) --}}
                <div class="inline-flex rounded-xl p-1 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                    <button type="button" onclick="setCalendarMode('bs')" id="btnCalBs"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $calendarType === 'bs' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-indigo-600' }}">
                        BS (Nepali)
                    </button>
                    <button type="button" onclick="setCalendarMode('ad')" id="btnCalAd"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $calendarType === 'ad' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-indigo-600' }}">
                        AD (Gregorian)
                    </button>
                </div>
                <input type="hidden" name="cal" id="calModeInput" value="{{ $calendarType }}">

                {{-- Month Selector --}}
                <div class="relative">
                    <select name="month" onchange="this.form.submit()"
                        class="appearance-none bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 pr-9 text-sm font-semibold text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @if($calendarType === 'bs')
                            @foreach($nepaliMonths as $mNum => $mLabel)
                            <option value="{{ $mNum }}" {{ $month == $mNum ? 'selected' : '' }}>
                                {{ $mLabel }} (महिना {{ $mNum }})
                            </option>
                            @endforeach
                        @else
                            @foreach($englishMonths as $mNum => $mLabel)
                            <option value="{{ $mNum }}" {{ $month == $mNum ? 'selected' : '' }}>
                                {{ $mLabel }}
                            </option>
                            @endforeach
                        @endif
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                        <i class="bi bi-chevron-down text-xs"></i>
                    </div>
                </div>

                {{-- Year Input --}}
                <div class="flex items-center gap-1.5">
                    <input type="number" name="year" value="{{ $year }}" min="2000" max="2100"
                        class="w-24 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm font-semibold text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-center">
                    <button type="submit" class="px-3.5 py-2 bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 rounded-xl text-sm font-semibold transition-all">
                        <i class="bi bi-arrow-clockwise me-1"></i> Update
                    </button>
                </div>

                @if($isPrivileged && $departments->count())
                {{-- Department Filter --}}
                <div class="relative">
                    <select name="department_id" onchange="this.form.submit()"
                        class="appearance-none bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 pr-8 text-xs font-semibold text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="all">All Departments</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $deptFilter == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400">
                        <i class="bi bi-chevron-down text-xs"></i>
                    </div>
                </div>
                @endif
            </div>

            {{-- Search Bar --}}
            <div class="relative w-full md:w-64">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search teacher, EMP ID..."
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl pl-9 pr-4 py-2 text-xs font-medium text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <i class="bi bi-search text-xs"></i>
                </div>
                @if($search)
                <a href="{{ route('teacher.payroll.index', ['month' => $month, 'year' => $year, 'cal' => $calendarType, 'status' => $statusFilter]) }}"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600">
                    <i class="bi bi-x-circle-fill text-xs"></i>
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Veda-Style 4 Big Executive Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Card 1: Total Net Payable / Disbursed --}}
        <div class="relative overflow-hidden bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Net Disbursement</span>
                <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800">
                    <i class="bi bi-cash-stack text-lg"></i>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mb-1">
                Rs. {{ number_format($totalNet, 0) }}
            </div>
            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                <span class="inline-flex items-center text-emerald-600 font-semibold">
                    <i class="bi bi-check2-circle me-1"></i> Rs. {{ number_format($totalPaid, 0) }} Paid
                </span>
                <span>•</span>
                <span class="text-amber-600 dark:text-amber-400 font-medium">
                    Rs. {{ number_format($totalPending, 0) }} Pending
                </span>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-400"></div>
        </div>

        {{-- Card 2: Total Gross Payroll --}}
        <div class="relative overflow-hidden bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Total Gross Payroll</span>
                <span class="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800">
                    <i class="bi bi-briefcase-fill text-lg"></i>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mb-1">
                Rs. {{ number_format($totalGross, 0) }}
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400">
                Basic salaries, allowances, and bonuses
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-purple-500"></div>
        </div>

        {{-- Card 3: Statutory Deductions & Taxes --}}
        <div class="relative overflow-hidden bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Taxes & Deductions</span>
                <span class="p-2 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-800">
                    <i class="bi bi-shield-check text-lg"></i>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-rose-600 dark:text-rose-400 tracking-tight mb-1">
                Rs. {{ number_format($totalDeductions, 0) }}
            </div>
            <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                <span>TDS: Rs. {{ number_format($totalTds, 0) }}</span>
                <span>•</span>
                <span>CIT/SSF: Rs. {{ number_format($totalCitSsf, 0) }}</span>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-500 to-amber-500"></div>
        </div>

        {{-- Card 4: Disbursement Progress --}}
        <div class="relative overflow-hidden bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Faculty Coverage</span>
                <span class="text-xs font-extrabold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300">
                    {{ $disbursedPercent }}% Done
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mb-2">
                {{ $paidCount }} <span class="text-sm font-medium text-slate-400">/ {{ $totalSlips }} Slips</span>
            </div>
            {{-- Progress bar --}}
            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden mb-1">
                <div class="bg-gradient-to-r from-blue-500 to-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ $disbursedPercent }}%"></div>
            </div>
            <div class="flex justify-between items-center text-[11px] text-slate-400">
                <span>{{ $approvedCount }} Approved</span>
                <span>{{ $draftCount }} Drafts</span>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-emerald-500"></div>
        </div>
    </div>

    {{-- Filter Status Tabs --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
        <div class="inline-flex rounded-2xl p-1.5 bg-slate-200/70 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 text-xs font-semibold">
            <a href="{{ route('teacher.payroll.index', array_merge(request()->query(), ['status' => 'all'])) }}"
                class="px-4 py-2 rounded-xl transition-all {{ $statusFilter === 'all' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-sm font-bold' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900' }}">
                All Slips ({{ $totalSlips }})
            </a>
            <a href="{{ route('teacher.payroll.index', array_merge(request()->query(), ['status' => 'draft'])) }}"
                class="px-4 py-2 rounded-xl transition-all {{ $statusFilter === 'draft' ? 'bg-white dark:bg-slate-900 text-amber-700 dark:text-amber-400 shadow-sm font-bold' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900' }}">
                Draft / Unapproved ({{ $draftCount }})
            </a>
            <a href="{{ route('teacher.payroll.index', array_merge(request()->query(), ['status' => 'approved'])) }}"
                class="px-4 py-2 rounded-xl transition-all {{ $statusFilter === 'approved' ? 'bg-white dark:bg-slate-900 text-blue-700 dark:text-blue-400 shadow-sm font-bold' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900' }}">
                Approved ({{ $approvedCount }})
            </a>
            <a href="{{ route('teacher.payroll.index', array_merge(request()->query(), ['status' => 'paid'])) }}"
                class="px-4 py-2 rounded-xl transition-all {{ $statusFilter === 'paid' ? 'bg-white dark:bg-slate-900 text-emerald-700 dark:text-emerald-400 shadow-sm font-bold' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900' }}">
                Disbursed / Paid ({{ $paidCount }})
            </a>
        </div>

        <div class="text-xs text-slate-500 dark:text-slate-400">
            Showing <strong class="text-slate-800 dark:text-slate-200">{{ $payrolls->count() }}</strong> faculty records for
            <span class="font-semibold text-indigo-600 dark:text-indigo-400">
                {{ $calendarType === 'bs' ? ($nepaliMonths[$month] ?? $month) : ($englishMonths[$month] ?? $month) }} {{ $year }}
            </span>
        </div>
    </div>

    {{-- Floating Bulk Actions Ribbon (Appears when checkboxes are selected) --}}
    @if($isPrivileged)
    <div id="bulkActionBar" class="hidden mb-4 p-3 bg-slate-900 text-white rounded-2xl shadow-xl flex flex-wrap items-center justify-between gap-4 animate-bounce-short">
        <div class="flex items-center gap-3">
            <span class="w-7 h-7 rounded-lg bg-indigo-500 text-white flex items-center justify-center font-bold text-xs" id="selectedCountBadge">0</span>
            <span class="text-sm font-medium">Faculty slips selected</span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openBulkPayModal('bulk_approve')"
                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white transition">
                <i class="bi bi-check-all me-1"></i> Approve Selected
            </button>
            <button type="button" onclick="openBulkPayModal('mark_paid')"
                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition">
                <i class="bi bi-wallet2 me-1"></i> Mark Selected as Paid
            </button>
            <button type="button" onclick="deselectAllSlips()" class="px-2.5 py-1.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white">
                Deselect
            </button>
        </div>
    </div>
    @endif

    {{-- Main Payroll Table Card --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        @if($payrolls->count())
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 bg-slate-50/75 dark:bg-slate-800/50">
                        @if($isPrivileged)
                        <th class="py-4 px-4 w-10 text-center">
                            <input type="checkbox" id="masterCheckbox" onclick="toggleSelectAll(this)"
                                class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800">
                        </th>
                        @endif
                        <th class="py-4 px-4">Faculty Member</th>
                        <th class="py-4 px-4">Attendance</th>
                        <th class="py-4 px-4 text-right">Basic (NPR)</th>
                        <th class="py-4 px-4 text-right">Gross (NPR)</th>
                        <th class="py-4 px-4 text-right">Deductions (NPR)</th>
                        <th class="py-4 px-4 text-right font-extrabold text-slate-700 dark:text-slate-200">Net Payable</th>
                        <th class="py-4 px-4 text-center">Status</th>
                        <th class="py-4 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                    @foreach($payrolls as $p)
                    <tr class="hover:bg-slate-50/75 dark:hover:bg-slate-800/40 transition-colors group">
                        @if($isPrivileged)
                        <td class="py-3 px-4 text-center">
                            <input type="checkbox" value="{{ $p->id }}" name="slip_ids[]" onchange="updateBulkBar()"
                                class="slip-checkbox w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800">
                        </td>
                        @endif

                        {{-- Faculty Profile & Bank Details --}}
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                <div class="relative w-9 h-9 rounded-full overflow-hidden bg-indigo-100 dark:bg-indigo-950 flex-shrink-0 flex items-center justify-center font-bold text-indigo-700 dark:text-indigo-300 text-xs border border-indigo-200 dark:border-indigo-800">
                                    @if($p->teacher?->avatar && !str_contains($p->teacher->avatar, 'default'))
                                    <img src="{{ $p->teacher->avatar }}" class="w-full h-full object-cover" alt="">
                                    @else
                                    {{ strtoupper(substr($p->teacher?->first_name ?? 'T', 0, 1) . substr($p->teacher?->last_name ?? 'F', 0, 1)) }}
                                    @endif
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800 dark:text-slate-100 group-hover:text-indigo-600 transition flex items-center gap-1.5">
                                        <span>{{ $p->teacher?->full_name ?? 'Unknown Teacher' }}</span>
                                        <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-800 text-slate-500">
                                            EMP-{{ $p->teacher_id }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-slate-400 dark:text-slate-500 flex items-center gap-2 mt-0.5">
                                        <span>{{ $p->teacher?->departments?->pluck('name')->join(', ') ?: 'General' }}</span>
                                        @if($p->teacher?->bank_name)
                                        <span>•</span>
                                        <span class="text-slate-500 dark:text-slate-400" title="Account: {{ $p->teacher->bank_account_no }}">
                                            <i class="bi bi-bank me-0.5"></i> {{ $p->teacher->bank_name }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Attendance Days --}}
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-1 text-[11px]">
                                <span class="px-2 py-0.5 rounded-md font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                                    {{ $p->present_days }}P
                                </span>
                                <span class="text-slate-300">/</span>
                                <span class="text-slate-600 dark:text-slate-400 font-medium">
                                    {{ $p->working_days }} Days
                                </span>
                            </div>
                            @if($p->absent_days > 0 || $p->leave_days > 0)
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                @if($p->absent_days > 0)<span class="text-rose-500">{{ $p->absent_days }} Absent</span>@endif
                                @if($p->absent_days > 0 && $p->leave_days > 0) • @endif
                                @if($p->leave_days > 0)<span class="text-blue-500">{{ $p->leave_days }} Leave</span>@endif
                            </div>
                            @endif
                        </td>

                        {{-- Basic Salary --}}
                        <td class="py-3 px-4 text-right font-medium text-slate-700 dark:text-slate-300">
                            Rs. {{ number_format($p->basic_salary, 2) }}
                        </td>

                        {{-- Gross Salary --}}
                        <td class="py-3 px-4 text-right">
                            <div class="font-bold text-slate-800 dark:text-slate-200">
                                Rs. {{ number_format($p->gross_salary, 2) }}
                            </div>
                            @if($p->allowances > 0 || $p->bonus > 0 || $p->overtime > 0)
                            <div class="text-[10px] text-slate-400">
                                +{{ number_format($p->allowances + $p->bonus + $p->overtime, 0) }} add.
                            </div>
                            @endif
                        </td>

                        {{-- Deductions --}}
                        <td class="py-3 px-4 text-right">
                            <div class="font-bold text-rose-600 dark:text-rose-400">
                                - Rs. {{ number_format($p->total_deductions, 2) }}
                            </div>
                            <div class="text-[10px] text-slate-400" title="TDS: {{ $p->tax_deduction ?: $p->tds_payable }} | SSF: {{ $p->ssf_deduction }} | CIT: {{ $p->cit_deduction }}">
                                TDS+SSF/CIT
                            </div>
                        </td>

                        {{-- Net Salary (Prominent) --}}
                        <td class="py-3 px-4 text-right font-mono">
                            <div class="text-sm font-extrabold text-emerald-700 dark:text-emerald-400 bg-emerald-50/80 dark:bg-emerald-950/40 px-2.5 py-1 rounded-xl inline-block border border-emerald-200/60 dark:border-emerald-800/60">
                                Rs. {{ number_format($p->net_salary, 2) }}
                            </div>
                        </td>

                        {{-- Status Badge --}}
                        <td class="py-3 px-4 text-center">
                            @if($p->status === 'paid')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                <i class="bi bi-check-circle-fill text-emerald-600"></i> Disbursed
                            </span>
                            @if($p->payment_date)
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                {{ $p->payment_date->format('d M, Y') }}
                            </div>
                            @endif
                            @elseif($p->is_approved)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                <i class="bi bi-shield-check text-blue-600"></i> Approved
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                <i class="bi bi-clock-history text-amber-600"></i> Pending Draft
                            </span>
                            @endif
                        </td>

                        {{-- Action Buttons --}}
                        <td class="py-3 px-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                {{-- Slip PDF --}}
                                <a href="{{ route('teacher.payroll.slip', $p->id) }}" target="_blank"
                                    class="p-1.5 rounded-lg text-slate-600 dark:text-slate-300 hover:text-indigo-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                    title="View & Download PDF Slip">
                                    <i class="bi bi-file-earmark-pdf text-sm text-rose-500"></i>
                                </a>

                                @if($isPrivileged)
                                    {{-- Edit / Adjust Modal Trigger --}}
                                    <button type="button" onclick='openEditSlipModal(@json($p))'
                                        class="p-1.5 rounded-lg text-slate-600 dark:text-slate-300 hover:text-indigo-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                        title="Adjust Earnings & Deductions">
                                        <i class="bi bi-sliders text-sm"></i>
                                    </button>

                                    @if($p->status !== 'paid')
                                        @if(!$p->is_approved)
                                        {{-- Approve Button --}}
                                        <form method="POST" action="{{ route('teacher.payroll.approve', $p->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950 transition" title="Approve Slip">
                                                <i class="bi bi-check2-circle text-sm"></i>
                                            </button>
                                        </form>
                                        @endif

                                        {{-- Mark Paid Button --}}
                                        <button type="button" onclick="openSinglePayModal({{ $p->id }}, '{{ addslashes($p->teacher?->full_name ?? '') }}', {{ $p->net_salary }})"
                                            class="px-2 py-1 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition flex items-center gap-1"
                                            title="Mark Disbursed">
                                            <i class="bi bi-wallet2 text-[10px]"></i> Pay
                                        </button>
                                    @endif

                                    {{-- Delete Button --}}
                                    <form method="POST" action="{{ route('teacher.payroll.destroy', $p->id) }}" onsubmit="return confirm('Delete this salary slip?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/50 transition" title="Delete Slip">
                                            <i class="bi bi-trash text-sm"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-16 px-4">
            <div class="w-16 h-16 rounded-3xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-500 mx-auto flex items-center justify-center text-2xl mb-4 border border-indigo-100 dark:border-indigo-800">
                <i class="bi bi-receipt-cutoff"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-1">No Payroll Slips for this Period</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto mb-5">
                No salary records were found for {{ $calendarType === 'bs' ? ($nepaliMonths[$month] ?? $month) : ($englishMonths[$month] ?? $month) }} {{ $year }}. You can automatically generate slips for all faculty members in 1-click.
            </p>
            @if($isPrivileged)
            <div class="flex items-center justify-center gap-3">
                <button type="button" onclick="openModal('bulkGenerateModal')"
                    class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                    <i class="bi bi-lightning-charge-fill me-1 text-amber-300"></i> Auto-Generate for All Teachers
                </button>
                <button type="button" onclick="openCreateSlipModal()"
                    class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 transition">
                    <i class="bi bi-plus-lg me-1"></i> Create Single Slip
                </button>
            </div>
            @endif
        </div>
        @endif
    </div>

</div>

{{-- MODAL 1: 1-Click Auto Bulk Generate --}}
@if($isPrivileged)
<div id="bulkGenerateModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-md w-full overflow-hidden animate-scale-up">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-lg font-bold">
                    <i class="bi bi-lightning-charge-fill"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Auto-Generate Faculty Payroll</h3>
                    <p class="text-xs text-slate-500">1-Click Batch Compute from Contracts & Attendance</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('bulkGenerateModal')" class="text-slate-400 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('teacher.payroll.bulk-generate') }}" class="p-6">
            @csrf
            <div class="mb-4 p-4 rounded-2xl bg-indigo-50/80 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-800/80 text-xs text-indigo-900 dark:text-indigo-300 space-y-2">
                <p class="font-semibold flex items-center gap-1.5">
                    <i class="bi bi-info-circle-fill text-indigo-600"></i> How Auto-Generation Works:
                </p>
                <ul class="list-disc pl-5 space-y-1 text-slate-600 dark:text-slate-400">
                    <li>Iterates through all active faculty members ({{ $teachers->count() }} teachers).</li>
                    <li>Fetches basic salary from active contracts or faculty profile.</li>
                    <li>Synchronizes biometric attendance days and approved leave days for the month.</li>
                    <li>Calculates statutory TDS tax and deductions automatically.</li>
                    <li>Existing slips will be safely preserved and not overwritten.</li>
                </ul>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-6">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Pay Month *</label>
                    <select name="month" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm font-semibold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                        @foreach($nepaliMonths as $mNum => $mLabel)
                        <option value="{{ $mNum }}" {{ $month == $mNum ? 'selected' : '' }}>{{ $mLabel }} (M{{ $mNum }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Pay Year *</label>
                    <input type="number" name="year" value="{{ $year }}" required
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm font-semibold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('bulkGenerateModal')"
                    class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-200 dark:shadow-none transition flex items-center gap-1.5">
                    <i class="bi bi-play-circle-fill"></i> Run Generation
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- MODAL 2: Interactive Real-Time Live Calculation Slip Modal (Add / Edit) --}}
@if($isPrivileged)
<div id="slipModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-3xl w-full my-8 overflow-hidden animate-scale-up">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-lg font-bold">
                    <i class="bi bi-calculator"></i>
                </div>
                <div>
                    <h3 id="slipModalTitle" class="text-base font-bold text-slate-900 dark:text-white">Adjust Faculty Pay Slip</h3>
                    <p class="text-xs text-slate-500">Real-time dynamic calculation of earnings, statutory taxes & deductions</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('slipModal')" class="text-slate-400 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('teacher.payroll.store') }}" id="slipForm" class="p-5 sm:p-6 space-y-5">
            @csrf
            {{-- Period & Teacher Selection --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Select Faculty Member *</label>
                    <select name="teacher_id" id="calcTeacherId" required onchange="onTeacherSelectChange()"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                        <option value="">— Choose Faculty —</option>
                        @foreach($teachers as $t)
                        @php
                            $contract = $t->teacherContracts?->first();
                            $defaultSalary = $contract?->basic_salary ?: ($t->basic_salary ?: 0);
                        @endphp
                        <option value="{{ $t->id }}"
                            data-salary="{{ $defaultSalary }}"
                            data-bank="{{ $t->bank_name }}"
                            data-acc="{{ $t->bank_account_no }}">
                            {{ $t->full_name }} (EMP-{{ $t->id }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Pay Month *</label>
                    <select name="month" id="calcMonth" required
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                        @foreach($nepaliMonths as $mNum => $mLabel)
                        <option value="{{ $mNum }}" {{ $month == $mNum ? 'selected' : '' }}>{{ $mLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Pay Year *</label>
                    <input type="number" name="year" id="calcYear" value="{{ $year }}" required
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            {{-- LIVE SUMMARY BANNER --}}
            <div class="p-4 rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 text-white shadow-md grid grid-cols-3 gap-3 text-center">
                <div class="border-r border-indigo-800/50 pr-2">
                    <span class="block text-[10px] uppercase tracking-wider text-indigo-300 font-semibold">Gross Salary</span>
                    <span class="text-base sm:text-xl font-black text-white" id="liveGross">Rs. 0.00</span>
                </div>
                <div class="border-r border-indigo-800/50 px-2">
                    <span class="block text-[10px] uppercase tracking-wider text-rose-300 font-semibold">Total Deductions</span>
                    <span class="text-base sm:text-xl font-black text-rose-400" id="liveDeductions">Rs. 0.00</span>
                </div>
                <div class="pl-2">
                    <span class="block text-[10px] uppercase tracking-wider text-emerald-300 font-semibold">Net Payable</span>
                    <span class="text-base sm:text-xl font-black text-emerald-400" id="liveNet">Rs. 0.00</span>
                </div>
            </div>

            {{-- Attendance Metrics --}}
            <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-200/80 dark:border-slate-700/80">
                <p class="text-xs font-bold text-slate-700 dark:text-slate-200 mb-2 flex items-center gap-1.5">
                    <i class="bi bi-calendar-check text-indigo-500"></i> Attendance Days for Month
                </p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">Total Days</label>
                        <input type="number" name="working_days" id="calcWorkingDays" value="30" min="0" max="31"
                            class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">Present Days</label>
                        <input type="number" name="present_days" id="calcPresentDays" value="30" min="0" max="31"
                            class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">Absent Days</label>
                        <input type="number" name="absent_days" id="calcAbsentDays" value="0" min="0" max="31"
                            class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">Approved Leaves</label>
                        <input type="number" name="leave_days" id="calcLeaveDays" value="0" min="0" max="31"
                            class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs font-semibold focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            {{-- 2-Column Math Breakdown: Earnings vs Deductions --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Column 1: Earnings --}}
                <div class="p-4 bg-emerald-50/50 dark:bg-emerald-950/20 rounded-2xl border border-emerald-100 dark:border-emerald-900/40 space-y-3">
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300 flex items-center justify-between">
                        <span><i class="bi bi-plus-circle me-1"></i> Earnings & Allowances</span>
                        <span class="text-[10px] text-emerald-600 font-semibold" id="colGrossTotal">Rs. 0</span>
                    </p>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Basic Salary *</label>
                        <input type="number" step="0.01" name="basic_salary" id="calcBasic" value="0" required oninput="recalcMath()"
                            class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 text-xs font-bold focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-medium text-slate-500 mb-1">Allowances</label>
                            <input type="number" step="0.01" name="allowances" id="calcAllowances" value="0" oninput="recalcMath()"
                                class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 text-xs font-medium focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-slate-500 mb-1">Overtime Pay</label>
                            <input type="number" step="0.01" name="overtime" id="calcOvertime" value="0" oninput="recalcMath()"
                                class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 text-xs font-medium focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">Performance Bonus / Dashain</label>
                        <input type="number" step="0.01" name="bonus" id="calcBonus" value="0" oninput="recalcMath()"
                            class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 text-xs font-medium focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                {{-- Column 2: Deductions --}}
                <div class="p-4 bg-rose-50/50 dark:bg-rose-950/20 rounded-2xl border border-rose-100 dark:border-rose-900/40 space-y-3">
                    <p class="text-xs font-bold uppercase tracking-wider text-rose-800 dark:text-rose-300 flex items-center justify-between">
                        <span><i class="bi bi-dash-circle me-1"></i> Deductions & Taxes</span>
                        <span class="text-[10px] text-rose-600 font-semibold" id="colDeductTotal">Rs. 0</span>
                    </p>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">TDS / Income Tax</label>
                            <input type="number" step="0.01" name="tax_deduction" id="calcTds" value="0" oninput="recalcMath()"
                                class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 text-xs font-medium focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-slate-500 mb-1">SSF (Social Security)</label>
                            <input type="number" step="0.01" name="ssf_deduction" id="calcSsf" value="0" oninput="recalcMath()"
                                class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 text-xs font-medium focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-medium text-slate-500 mb-1">CIT Deduction</label>
                            <input type="number" step="0.01" name="cit_deduction" id="calcCit" value="0" oninput="recalcMath()"
                                class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 text-xs font-medium focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-slate-500 mb-1">Late Attendance Fine</label>
                            <input type="number" step="0.01" name="late_fine" id="calcLate" value="0" oninput="recalcMath()"
                                class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 text-xs font-medium focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">Loan / Advance / Other Deductions</label>
                        <input type="number" step="0.01" name="other_deductions" id="calcOther" value="0" oninput="recalcMath()"
                            class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-1.5 text-xs font-medium focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            {{-- Payment & Notes --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Payment Method</label>
                    <select name="payment_method" id="calcPaymentMethod"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                        <option value="bank_transfer">Bank Transfer (Direct Advice)</option>
                        <option value="cash">Cash in Hand</option>
                        <option value="cheque">Account Payee Cheque</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Remarks / Slip Note</label>
                    <input type="text" name="notes" id="calcNotes" placeholder="e.g. Regular monthly disbursement, special incentives included"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800">
                <span class="text-xs text-slate-400">Status will be saved as <span class="font-semibold text-amber-600">Draft</span> for review.</span>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="closeModal('slipModal')"
                        class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-200 dark:shadow-none transition flex items-center gap-1.5">
                        <i class="bi bi-save"></i> Save Payroll Slip
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

{{-- MODAL 3: Single Mark as Paid Modal --}}
@if($isPrivileged)
<div id="singlePayModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-md w-full overflow-hidden animate-scale-up">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-500 text-white flex items-center justify-center text-lg font-bold">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Disburse Salary</h3>
                    <p class="text-xs text-slate-500" id="singlePayFacultyName">Faculty Member</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('singlePayModal')" class="text-slate-400 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form method="POST" id="singlePayForm" class="p-6">
            @csrf
            <div class="mb-4 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-800 text-center">
                <span class="text-xs text-emerald-800 dark:text-emerald-300 font-semibold uppercase tracking-wider block">Net Disbursement Amount</span>
                <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400" id="singlePayAmount">Rs. 0.00</span>
            </div>

            <div class="space-y-4 mb-6">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Disbursement Method *</label>
                    <select name="payment_method" required
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                        <option value="bank_transfer">Direct Bank Transfer</option>
                        <option value="cash">Cash Payment</option>
                        <option value="cheque">Bank Cheque</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Payment Date *</label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('singlePayModal')"
                    class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-emerald-200 dark:shadow-none transition flex items-center gap-1.5">
                    <i class="bi bi-check2-circle"></i> Confirm Disbursement
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- MODAL 4: Multi-Select Bulk Pay / Approve Modal --}}
@if($isPrivileged)
<div id="bulkPayModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-md w-full overflow-hidden animate-scale-up">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-lg font-bold">
                    <i class="bi bi-stack"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white" id="bulkPayModalTitle">Bulk Action</h3>
                    <p class="text-xs text-slate-500" id="bulkPayCountLabel">Processing selected slips</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('bulkPayModal')" class="text-slate-400 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('teacher.payroll.bulk-pay') }}" id="bulkActionForm" class="p-6">
            @csrf
            <input type="hidden" name="action" id="bulkActionType" value="mark_paid">
            <div id="bulkHiddenIdsContainer"></div>

            <div id="bulkPaymentFields" class="space-y-4 mb-6">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Disbursement Method *</label>
                    <select name="payment_method"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                        <option value="bank_transfer">Direct Bank Transfer</option>
                        <option value="cash">Cash Payment</option>
                        <option value="cheque">Account Payee Cheque</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Disbursement Date *</label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('bulkPayModal')"
                    class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Cancel
                </button>
                <button type="submit" id="bulkSubmitBtn"
                    class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-200 dark:shadow-none transition flex items-center gap-1.5">
                    <i class="bi bi-check2-all"></i> Apply Bulk Update
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Inline JavaScript for Dynamic Live Math, Modals, and Bulk Actions --}}
<script>
function openModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('hidden');
}

function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('hidden');
}

function setCalendarMode(mode) {
    document.getElementById('calModeInput').value = mode;
    document.getElementById('periodFilterForm').submit();
}

// Live calculation formulas
function recalcMath() {
    const basic    = parseFloat(document.getElementById('calcBasic')?.value) || 0;
    const allow    = parseFloat(document.getElementById('calcAllowances')?.value) || 0;
    const overtime = parseFloat(document.getElementById('calcOvertime')?.value) || 0;
    const bonus    = parseFloat(document.getElementById('calcBonus')?.value) || 0;

    const tds      = parseFloat(document.getElementById('calcTds')?.value) || 0;
    const ssf      = parseFloat(document.getElementById('calcSsf')?.value) || 0;
    const cit      = parseFloat(document.getElementById('calcCit')?.value) || 0;
    const late     = parseFloat(document.getElementById('calcLate')?.value) || 0;
    const other    = parseFloat(document.getElementById('calcOther')?.value) || 0;

    const gross = basic + allow + overtime + bonus;
    const deductions = tds + ssf + cit + late + other;
    const net = Math.max(0, gross - deductions);

    const fmt = (num) => 'Rs. ' + num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    const liveGross = document.getElementById('liveGross');
    const liveDeductions = document.getElementById('liveDeductions');
    const liveNet = document.getElementById('liveNet');
    const colGrossTotal = document.getElementById('colGrossTotal');
    const colDeductTotal = document.getElementById('colDeductTotal');

    if (liveGross) liveGross.textContent = fmt(gross);
    if (liveDeductions) liveDeductions.textContent = fmt(deductions);
    if (liveNet) liveNet.textContent = fmt(net);
    if (colGrossTotal) colGrossTotal.textContent = fmt(gross);
    if (colDeductTotal) colDeductTotal.textContent = fmt(deductions);
}

function onTeacherSelectChange() {
    const sel = document.getElementById('calcTeacherId');
    const opt = sel.options[sel.selectedIndex];
    if (!opt) return;

    const salary = parseFloat(opt.getAttribute('data-salary')) || 0;
    const basicInput = document.getElementById('calcBasic');
    if (basicInput && salary > 0) {
        basicInput.value = salary;
        recalcMath();
    }
}

function openCreateSlipModal() {
    document.getElementById('slipModalTitle').textContent = 'Create / Adjust Faculty Pay Slip';
    document.getElementById('calcTeacherId').disabled = false;
    document.getElementById('calcTeacherId').value = '';
    document.getElementById('calcBasic').value = 0;
    document.getElementById('calcAllowances').value = 0;
    document.getElementById('calcOvertime').value = 0;
    document.getElementById('calcBonus').value = 0;
    document.getElementById('calcTds').value = 0;
    document.getElementById('calcSsf').value = 0;
    document.getElementById('calcCit').value = 0;
    document.getElementById('calcLate').value = 0;
    document.getElementById('calcOther').value = 0;
    document.getElementById('calcNotes').value = '';
    recalcMath();
    openModal('slipModal');
}

function openEditSlipModal(data) {
    document.getElementById('slipModalTitle').textContent = 'Adjust Salary Slip - ' + (data.teacher ? (data.teacher.first_name + ' ' + (data.teacher.last_name || '')) : 'Faculty');
    const teacherSel = document.getElementById('calcTeacherId');
    teacherSel.value = data.teacher_id;
    document.getElementById('calcMonth').value = data.month;
    document.getElementById('calcYear').value = data.year;

    document.getElementById('calcBasic').value = data.basic_salary || 0;
    document.getElementById('calcAllowances').value = data.allowances || 0;
    document.getElementById('calcOvertime').value = data.overtime || 0;
    document.getElementById('calcBonus').value = data.bonus || 0;
    document.getElementById('calcTds').value = data.tax_deduction || (data.tds_payable || 0);
    document.getElementById('calcSsf').value = data.ssf_deduction || 0;
    document.getElementById('calcCit').value = data.cit_deduction || 0;
    document.getElementById('calcLate').value = data.late_fine || 0;
    document.getElementById('calcOther').value = data.other_deductions || 0;
    document.getElementById('calcWorkingDays').value = data.working_days || 30;
    document.getElementById('calcPresentDays').value = data.present_days || 0;
    document.getElementById('calcAbsentDays').value = data.absent_days || 0;
    document.getElementById('calcLeaveDays').value = data.leave_days || 0;
    document.getElementById('calcPaymentMethod').value = data.payment_method || 'bank_transfer';
    document.getElementById('calcNotes').value = data.notes || '';

    recalcMath();
    openModal('slipModal');
}

function openSinglePayModal(id, teacherName, amount) {
    document.getElementById('singlePayFacultyName').textContent = teacherName;
    document.getElementById('singlePayAmount').textContent = 'Rs. ' + parseFloat(amount).toLocaleString('en-IN', { minimumFractionDigits: 2 });
    document.getElementById('singlePayForm').action = "{{ url('teacher/payroll') }}/" + id + "/paid";
    openModal('singlePayModal');
}

// Bulk Selection Actions
function toggleSelectAll(master) {
    const checkboxes = document.querySelectorAll('.slip-checkbox');
    checkboxes.forEach(cb => cb.checked = master.checked);
    updateBulkBar();
}

function deselectAllSlips() {
    const master = document.getElementById('masterCheckbox');
    if (master) master.checked = false;
    toggleSelectAll({ checked: false });
}

function updateBulkBar() {
    const checked = document.querySelectorAll('.slip-checkbox:checked');
    const bar = document.getElementById('bulkActionBar');
    const countBadge = document.getElementById('selectedCountBadge');
    if (!bar) return;

    if (checked.length > 0) {
        bar.classList.remove('hidden');
        if (countBadge) countBadge.textContent = checked.length;
    } else {
        bar.classList.add('hidden');
    }
}

function openBulkPayModal(action) {
    const checked = document.querySelectorAll('.slip-checkbox:checked');
    if (checked.length === 0) {
        alert('Please select at least one faculty payroll record.');
        return;
    }

    const typeInput = document.getElementById('bulkActionType');
    const container = document.getElementById('bulkHiddenIdsContainer');
    const paymentFields = document.getElementById('bulkPaymentFields');
    const title = document.getElementById('bulkPayModalTitle');
    const countLabel = document.getElementById('bulkPayCountLabel');
    const submitBtn = document.getElementById('bulkSubmitBtn');

    typeInput.value = action;
    container.innerHTML = '';
    checked.forEach(cb => {
        const hid = document.createElement('input');
        hid.type = 'hidden';
        hid.name = 'ids[]';
        hid.value = cb.value;
        container.appendChild(hid);
    });

    if (action === 'bulk_approve') {
        title.textContent = 'Bulk Approve Slips';
        countLabel.textContent = `Approving ${checked.length} selected salary slips`;
        paymentFields.classList.add('hidden');
        submitBtn.className = 'px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-200 dark:shadow-none transition flex items-center gap-1.5';
        submitBtn.innerHTML = '<i class="bi bi-shield-check"></i> Approve All Selected';
    } else {
        title.textContent = 'Bulk Disburse & Mark Paid';
        countLabel.textContent = `Disbursing ${checked.length} selected faculty pay slips`;
        paymentFields.classList.remove('hidden');
        submitBtn.className = 'px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-emerald-200 dark:shadow-none transition flex items-center gap-1.5';
        submitBtn.innerHTML = '<i class="bi bi-wallet2"></i> Mark Selected as Paid';
    }

    openModal('bulkPayModal');
}
</script>

<style>
@keyframes scaleUp {
    from { opacity: 0; transform: scale(0.96); }
    to { opacity: 1; transform: scale(1); }
}
.animate-scale-up {
    animation: scaleUp 0.18s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@media print {
    body { background: #fff !important; color: #000 !important; }
    #bulkActionBar, form, button, .pointer-events-none { display: none !important; }
}
</style>
@endsection
