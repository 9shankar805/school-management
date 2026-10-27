@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50 dark:bg-slate-950 p-4 sm:p-6 lg:p-8">

    {{-- Breadcrumb & Title --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400 mb-1">
                <span>Finance & Accounts</span>
                <i class="bi bi-chevron-right text-[10px] text-slate-400"></i>
                <span class="text-slate-500 dark:text-slate-400">Accounts Receivable (AR)</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
                <span>Class-Wise Dues & Defaulters Recovery</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                    <i class="bi bi-shield-lock-fill me-1 text-amber-600"></i> Veda Fee Recovery Engine
                </span>
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Class-level fee collection monitoring, automated parent reminders, and exam admit card feature barring.
            </p>
        </div>

        {{-- Top Action Group --}}
        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Go to POS Counter --}}
            <a href="{{ route('finance.pos') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-black text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-sm transition active:scale-[0.98]">
                <i class="bi bi-terminal-dash"></i>
                <span>Open Fee POS Counter</span>
            </a>

            {{-- Export CSV --}}
            <a href="{{ route('finance.dues.export.csv', request()->query()) }}"
                class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/60 shadow-sm transition">
                <i class="bi bi-file-earmark-spreadsheet text-emerald-600"></i>
                <span>Export CSV</span>
            </a>

            {{-- Print PDF Report --}}
            <a href="{{ route('finance.dues.export.pdf', request()->query()) }}" target="_blank"
                class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/60 shadow-sm transition">
                <i class="bi bi-printer text-indigo-600"></i>
                <span>Print Dues Sheet</span>
            </a>
        </div>
    </div>

    {{-- Session Alerts --}}
    @if(session('status'))
    <div class="mb-6 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 flex items-center justify-between shadow-sm animate-fade-in">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold">
                <i class="bi bi-check-lg"></i>
            </div>
            <p class="font-semibold text-sm">{{ session('status') }}</p>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    @endif

    {{-- 4 Big Executive KPI Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Card 1: Total Outstanding Dues --}}
        <div class="relative overflow-hidden bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Total Outstanding Dues</span>
                <span class="p-2 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 border border-rose-100 dark:border-rose-800">
                    <i class="bi bi-exclamation-triangle-fill text-lg"></i>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-rose-600 dark:text-rose-400 tracking-tight mb-1">
                Rs. {{ number_format($totalOutstandingAll, 2) }}
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400">
                Cumulative unpaid arrears across filtered students
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-rose-500 to-amber-500"></div>
        </div>

        {{-- Card 2: Defaulters Count --}}
        <div class="relative overflow-hidden bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Students with Dues</span>
                <span class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 border border-amber-100 dark:border-amber-800">
                    <i class="bi bi-people-fill text-lg"></i>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mb-1">
                {{ count($defaulters) }} <span class="text-xs font-semibold text-slate-400">Students</span>
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400">
                {{ $clearedCountAll }} students have zero balance (cleared)
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-yellow-400"></div>
        </div>

        {{-- Card 3: Overall Collection Rate --}}
        <div class="relative overflow-hidden bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Collection Recovery</span>
                <span class="text-xs font-extrabold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                    {{ $collectionRate }}%
                </span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight mb-2">
                Rs. {{ number_format($totalCollectedAll, 0) }} <span class="text-xs font-normal text-slate-400">/ Rs. {{ number_format($totalInvoicedAll, 0) }}</span>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                <div class="bg-gradient-to-r from-teal-500 to-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ min(100, $collectionRate) }}%"></div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-teal-500 to-emerald-500"></div>
        </div>

        {{-- Card 4: Admit Cards Barred / Locked --}}
        <div class="relative overflow-hidden bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Admit Cards Barred</span>
                <span class="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 border border-indigo-100 dark:border-indigo-800">
                    <i class="bi bi-lock-fill text-lg"></i>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-indigo-600 dark:text-indigo-400 tracking-tight mb-1">
                {{ $lockedCountAll }} <span class="text-xs font-semibold text-slate-400">Locked</span>
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400">
                Exam hall entry / report card downloads restricted
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-purple-500"></div>
        </div>
    </div>

    {{-- Filter Ribbon --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-4 mb-6 shadow-sm">
        <form method="GET" action="{{ route('finance.dues.index') }}" id="duesFilterForm" class="flex flex-wrap items-center justify-between gap-4">
            <input type="hidden" name="filter_status" value="{{ $status }}">

            <div class="flex flex-wrap items-center gap-3">
                {{-- Session Filter --}}
                <div class="relative">
                    <select name="session_id" onchange="this.form.submit()"
                        class="appearance-none bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 pr-8 text-xs font-bold text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($sessions as $sess)
                        <option value="{{ $sess->id }}" {{ $sessionId == $sess->id ? 'selected' : '' }}>
                            Session: {{ $sess->session_name }}
                        </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400">
                        <i class="bi bi-chevron-down text-xs"></i>
                    </div>
                </div>

                {{-- Class Filter --}}
                <div class="relative">
                    <select name="class_id" onchange="this.form.submit()"
                        class="appearance-none bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 pr-8 text-xs font-bold text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="all">All Classes</option>
                        @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>
                            {{ $c->class_name }}
                        </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400">
                        <i class="bi bi-chevron-down text-xs"></i>
                    </div>
                </div>

                {{-- Section Filter --}}
                @if($classId !== 'all')
                <div class="relative">
                    <select name="section_id" onchange="this.form.submit()"
                        class="appearance-none bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 pr-8 text-xs font-bold text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="all">All Sections</option>
                        @foreach($sections as $sec)
                        <option value="{{ $sec->id }}" {{ $sectionId == $sec->id ? 'selected' : '' }}>
                            Section {{ $sec->section_name }}
                        </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400">
                        <i class="bi bi-chevron-down text-xs"></i>
                    </div>
                </div>
                @endif

                {{-- Minimum Due Filter --}}
                <div class="relative">
                    <select name="min_due" onchange="this.form.submit()"
                        class="appearance-none bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 pr-8 text-xs font-bold text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="0" {{ $minDue == 0 ? 'selected' : '' }}>Min Due: Any Balance</option>
                        <option value="1" {{ $minDue == 1 ? 'selected' : '' }}>Min Due: > Rs. 0 (Defaulters)</option>
                        <option value="1000" {{ $minDue == 1000 ? 'selected' : '' }}>Min Due: > Rs. 1,000</option>
                        <option value="5000" {{ $minDue == 5000 ? 'selected' : '' }}>Min Due: > Rs. 5,000</option>
                        <option value="10000" {{ $minDue == 10000 ? 'selected' : '' }}>Min Due: > Rs. 10,000</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400">
                        <i class="bi bi-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            {{-- Search Bar --}}
            <div class="relative w-full md:w-64">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search student, roll, phone..."
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl pl-9 pr-4 py-2 text-xs font-medium text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <i class="bi bi-search text-xs"></i>
                </div>
                @if($search)
                <a href="{{ route('finance.dues.index', ['session_id' => $sessionId, 'class_id' => $classId, 'section_id' => $sectionId, 'min_due' => $minDue, 'filter_status' => $status]) }}"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600">
                    <i class="bi bi-x-circle-fill text-xs"></i>
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Filter Status Tabs --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
        <div class="inline-flex rounded-2xl p-1.5 bg-slate-200/70 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 text-xs font-semibold">
            <a href="{{ route('finance.dues.index', array_merge(request()->query(), ['filter_status' => 'dues_only'])) }}"
                class="px-4 py-2 rounded-xl transition-all {{ $status === 'dues_only' ? 'bg-white dark:bg-slate-900 text-rose-700 dark:text-rose-400 shadow-sm font-bold' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900' }}">
                <i class="bi bi-exclamation-circle me-1"></i> Dues Pending ({{ count($defaulters) }})
            </a>
            <a href="{{ route('finance.dues.index', array_merge(request()->query(), ['filter_status' => 'locked_only'])) }}"
                class="px-4 py-2 rounded-xl transition-all {{ $status === 'locked_only' ? 'bg-white dark:bg-slate-900 text-indigo-700 dark:text-indigo-400 shadow-sm font-bold' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900' }}">
                <i class="bi bi-lock-fill me-1"></i> Admit Cards Locked ({{ $lockedCountAll }})
            </a>
            <a href="{{ route('finance.dues.index', array_merge(request()->query(), ['filter_status' => 'cleared_only'])) }}"
                class="px-4 py-2 rounded-xl transition-all {{ $status === 'cleared_only' ? 'bg-white dark:bg-slate-900 text-emerald-700 dark:text-emerald-400 shadow-sm font-bold' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900' }}">
                <i class="bi bi-check-circle-fill me-1"></i> Fully Cleared ({{ $clearedCountAll }})
            </a>
            <a href="{{ route('finance.dues.index', array_merge(request()->query(), ['filter_status' => 'all'])) }}"
                class="px-4 py-2 rounded-xl transition-all {{ $status === 'all' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-sm font-bold' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900' }}">
                All Records
            </a>
        </div>

        <div class="text-xs text-slate-500 dark:text-slate-400">
            Showing <strong class="text-slate-800 dark:text-slate-200">{{ count($defaulters) }}</strong> student records in this view
        </div>
    </div>

    {{-- Floating Multi-Select Bulk Action Ribbon --}}
    <div id="bulkDuesBar" class="hidden mb-4 p-3 bg-slate-900 text-white rounded-2xl shadow-xl flex flex-wrap items-center justify-between gap-4 animate-scale-up">
        <div class="flex items-center gap-3">
            <span class="w-7 h-7 rounded-lg bg-amber-500 text-white flex items-center justify-center font-bold text-xs" id="selectedDuesBadge">0</span>
            <span class="text-sm font-medium">Students selected for action</span>
        </div>
        <div class="flex items-center gap-2">
            {{-- Bulk Remind --}}
            <button type="button" onclick="openBulkRemindModal()"
                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition flex items-center gap-1.5">
                <i class="bi bi-chat-dots-fill"></i> Send Fee Reminders
            </button>

            {{-- Bulk Lock Admit Card --}}
            <button type="button" onclick="openBulkLockModal('lock')"
                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white transition flex items-center gap-1.5">
                <i class="bi bi-lock-fill"></i> Bar / Lock Admit Cards
            </button>

            {{-- Bulk Unlock Admit Card --}}
            <button type="button" onclick="openBulkLockModal('unlock')"
                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white transition flex items-center gap-1.5">
                <i class="bi bi-unlock-fill"></i> Unlock Cards
            </button>

            <button type="button" onclick="deselectAllDues()" class="px-2.5 py-1.5 rounded-xl text-xs font-semibold text-slate-300 hover:text-white">
                Deselect
            </button>
        </div>
    </div>

    {{-- Defaulters Data Table Card --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        @if(count($defaulters) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 bg-slate-50/75 dark:bg-slate-800/50">
                        <th class="py-4 px-4 w-10 text-center">
                            <input type="checkbox" id="masterDuesCheckbox" onclick="toggleSelectAllDues(this)"
                                class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800">
                        </th>
                        <th class="py-4 px-4">Student & Class</th>
                        <th class="py-4 px-4">Guardian & Contact</th>
                        <th class="py-4 px-4 text-right">Total Invoiced</th>
                        <th class="py-4 px-4 text-right">Total Paid</th>
                        <th class="py-4 px-4 text-right font-extrabold text-slate-700 dark:text-slate-200">Due Balance</th>
                        <th class="py-4 px-4">Overdue Months</th>
                        <th class="py-4 px-4 text-center">Admit Card Status</th>
                        <th class="py-4 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                    @foreach($defaulters as $d)
                    <tr class="hover:bg-slate-50/75 dark:hover:bg-slate-800/40 transition-colors group">
                        {{-- Row Checkbox --}}
                        <td class="py-3 px-4 text-center">
                            <input type="checkbox" value="{{ $d['student_id'] }}" name="selected_students[]" onchange="updateDuesBulkBar()"
                                data-name="{{ $d['full_name'] }}"
                                data-due="{{ $d['due_balance'] }}"
                                data-phone="{{ $d['guardian_phone'] }}"
                                class="due-checkbox w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800">
                        </td>

                        {{-- Student Profile --}}
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full overflow-hidden bg-indigo-100 dark:bg-indigo-950 flex-shrink-0 flex items-center justify-center font-bold text-indigo-700 dark:text-indigo-300 text-xs border border-indigo-200 dark:border-indigo-800">
                                    {{ strtoupper(substr($d['full_name'], 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800 dark:text-slate-100 group-hover:text-indigo-600 transition flex items-center gap-1.5">
                                        <span>{{ $d['full_name'] }}</span>
                                        <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-800 text-slate-500">
                                            Roll: {{ $d['roll_no'] }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        {{ $d['class_name'] }} (Sec {{ $d['section_name'] }}) · {{ $d['admission_no'] }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Guardian & Contact --}}
                        <td class="py-3 px-4">
                            <div class="font-semibold text-slate-700 dark:text-slate-300">{{ $d['guardian_name'] }}</div>
                            <div class="text-[11px] text-slate-400 flex items-center gap-1.5 mt-0.5">
                                @if($d['guardian_phone'])
                                <span><i class="bi bi-telephone text-[10px]"></i> {{ $d['guardian_phone'] }}</span>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $d['guardian_phone']) }}?text={{ urlencode('Dear Parent, gentle reminder regarding outstanding fee balance of Rs. ' . number_format($d['due_balance'], 2) . ' for ' . $d['full_name'] . '. Please clear at counter or via eSewa/Khalti. Thank you!') }}"
                                    target="_blank" class="text-emerald-600 hover:text-emerald-700 font-bold" title="Send WhatsApp Message">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                                @else
                                <span class="italic text-slate-400">No phone recorded</span>
                                @endif
                            </div>
                        </td>

                        {{-- Invoiced --}}
                        <td class="py-3 px-4 text-right text-slate-600 dark:text-slate-400 font-medium">
                            Rs. {{ number_format($d['total_invoiced'], 2) }}
                        </td>

                        {{-- Paid --}}
                        <td class="py-3 px-4 text-right text-emerald-600 dark:text-emerald-400 font-medium">
                            Rs. {{ number_format($d['total_paid'], 2) }}
                        </td>

                        {{-- Due Balance --}}
                        <td class="py-3 px-4 text-right">
                            @if($d['due_balance'] > 0)
                            <span class="inline-block px-2.5 py-1 rounded-xl font-mono text-xs font-extrabold bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                Rs. {{ number_format($d['due_balance'], 2) }}
                            </span>
                            @else
                            <span class="inline-block px-2.5 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200">
                                Cleared (0.00)
                            </span>
                            @endif
                        </td>

                        {{-- Overdue Months --}}
                        <td class="py-3 px-4">
                            @if(count($d['overdue_months']) > 0)
                            <div class="flex flex-wrap gap-1 max-w-[200px]">
                                @foreach(array_slice($d['overdue_months'], 0, 3) as $m)
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300 border border-amber-200">
                                    {{ $m }}
                                </span>
                                @endforeach
                                @if(count($d['overdue_months']) > 3)
                                <span class="text-[10px] text-slate-400 font-medium">+{{ count($d['overdue_months']) - 3 }} more</span>
                                @endif
                            </div>
                            @else
                            <span class="text-slate-400 text-[11px]">—</span>
                            @endif
                        </td>

                        {{-- Admit Card Status --}}
                        <td class="py-3 px-4 text-center">
                            @if($d['is_admit_card_locked'])
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border border-rose-200"
                                title="{{ $d['admit_card_lock_reason'] ?: 'Admit card barred' }}">
                                <i class="bi bi-lock-fill text-rose-600"></i> BARRED
                            </span>
                            @elseif($d['due_balance'] > 0)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-200"
                                title="Auto-locked due to fee arrears">
                                <i class="bi bi-shield-exclamation text-amber-500"></i> DUE LOCK
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200">
                                <i class="bi bi-check-circle-fill text-emerald-500"></i> CLEARED
                            </span>
                            @endif
                        </td>

                        {{-- Row Action Buttons --}}
                        <td class="py-3 px-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                {{-- ⚡ POS Collect --}}
                                <a href="{{ route('finance.pos') }}?student_id={{ $d['student_id'] }}"
                                    class="px-2 py-1 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition flex items-center gap-1"
                                    title="Collect fee at POS counter">
                                    <i class="bi bi-terminal text-[11px]"></i> Pay
                                </a>

                                {{-- Student Ledger --}}
                                <a href="{{ route('payments.student-ledger', $d['student_id']) }}" target="_blank"
                                    class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                    title="View Student Ledger Statement">
                                    <i class="bi bi-journal-text text-sm"></i>
                                </a>

                                {{-- Quick Lock Toggle --}}
                                <form method="POST" action="{{ route('finance.dues.toggle-lock', $d['student_id']) }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950 transition"
                                        title="{{ $d['is_admit_card_locked'] ? 'Unlock Admit Card' : 'Lock Admit Card' }}">
                                        <i class="bi {{ $d['is_admit_card_locked'] ? 'bi-unlock text-emerald-600' : 'bi-lock' }} text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-16 px-4">
            <div class="w-16 h-16 rounded-3xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-500 mx-auto flex items-center justify-center text-2xl mb-4 border border-emerald-100 dark:border-emerald-800">
                <i class="bi bi-shield-check"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-1">No Fee Defaulters Found</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto mb-5">
                All students in the selected class and session have cleared their dues or meet the minimum threshold criteria.
            </p>
        </div>
        @endif
    </div>

</div>

{{-- MODAL 1: Bulk Reminder Modal --}}
<div id="bulkRemindModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-lg w-full overflow-hidden animate-scale-up">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-lg font-bold">
                    <i class="bi bi-chat-dots-fill"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Send Fee Reminders</h3>
                    <p class="text-xs text-slate-500" id="remindCountLabel">Targeting selected defaulters</p>
                </div>
            </div>
            <button type="button" onclick="closeDuesModal('bulkRemindModal')" class="text-slate-400 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('finance.dues.bulk-remind') }}" class="p-6 space-y-4">
            @csrf
            <div id="remindHiddenIds"></div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Notification Channel *</label>
                <div class="grid grid-cols-3 gap-2">
                    <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold text-slate-800 dark:text-slate-200">
                        <input type="radio" name="channel" value="sms" checked class="text-indigo-600">
                        <span>SMS Portal</span>
                    </label>
                    <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold text-slate-800 dark:text-slate-200">
                        <input type="radio" name="channel" value="whatsapp" class="text-indigo-600">
                        <span>WhatsApp</span>
                    </label>
                    <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold text-slate-800 dark:text-slate-200">
                        <input type="radio" name="channel" value="portal" class="text-indigo-600">
                        <span>Mobile App</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Reminder Message Template</label>
                <textarea name="message" rows="4"
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-3 text-xs font-medium text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500"
                    placeholder="Dear Parent, gentle reminder regarding outstanding fee balance of {due_amount} for {student_name}. Kindly clear at the counter or via eSewa/Khalti. Thank you! - {school_name}"></textarea>
                <p class="text-[11px] text-slate-400 mt-1">Available placeholders: <code>{student_name}</code>, <code>{due_amount}</code>, <code>{school_name}</code></p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" onclick="closeDuesModal('bulkRemindModal')"
                    class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-emerald-200 dark:shadow-none transition flex items-center gap-1.5">
                    <i class="bi bi-send-fill"></i> Dispatch Reminders
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: Bulk Admit Card Lock Modal --}}
<div id="bulkLockModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl max-w-md w-full overflow-hidden animate-scale-up">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-rose-600 text-white flex items-center justify-center text-lg font-bold" id="lockIconBox">
                    <i class="bi bi-lock-fill"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white" id="lockModalTitle">Bar Admit Cards</h3>
                    <p class="text-xs text-slate-500" id="lockCountLabel">Locking selected students</p>
                </div>
            </div>
            <button type="button" onclick="closeDuesModal('bulkLockModal')" class="text-slate-400 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('finance.dues.bulk-lock') }}" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="action" id="bulkLockAction" value="lock">
            <div id="lockHiddenIds"></div>

            <div id="lockReasonBlock">
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Administrative Lock Reason</label>
                <input type="text" name="lock_reason" value="Examination admit card held due to outstanding fee balance."
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                <p class="text-[11px] text-slate-400 mt-1">This explanation will display to parents and students if they attempt to download their admit card.</p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" onclick="closeDuesModal('bulkLockModal')"
                    class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Cancel
                </button>
                <button type="submit" id="lockSubmitBtn"
                    class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-md shadow-rose-200 dark:shadow-none transition flex items-center gap-1.5">
                    <i class="bi bi-shield-lock"></i> Apply Lock
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function closeDuesModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('hidden');
}

function openDuesModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('hidden');
}

// Multi-select actions
function toggleSelectAllDues(master) {
    const checkboxes = document.querySelectorAll('.due-checkbox');
    checkboxes.forEach(cb => cb.checked = master.checked);
    updateDuesBulkBar();
}

function deselectAllDues() {
    const master = document.getElementById('masterDuesCheckbox');
    if (master) master.checked = false;
    toggleSelectAllDues({ checked: false });
}

function updateDuesBulkBar() {
    const checked = document.querySelectorAll('.due-checkbox:checked');
    const bar = document.getElementById('bulkDuesBar');
    const badge = document.getElementById('selectedDuesBadge');
    if (!bar) return;

    if (checked.length > 0) {
        bar.classList.remove('hidden');
        if (badge) badge.textContent = checked.length;
    } else {
        bar.classList.add('hidden');
    }
}

function openBulkRemindModal() {
    const checked = document.querySelectorAll('.due-checkbox:checked');
    if (checked.length === 0) {
        alert('Please select at least one student.');
        return;
    }

    const container = document.getElementById('remindHiddenIds');
    container.innerHTML = '';
    checked.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'student_ids[]';
        input.value = cb.value;
        container.appendChild(input);
    });

    document.getElementById('remindCountLabel').textContent = `Targeting ${checked.length} selected students`;
    openDuesModal('bulkRemindModal');
}

function openBulkLockModal(action) {
    const checked = document.querySelectorAll('.due-checkbox:checked');
    if (checked.length === 0) {
        alert('Please select at least one student.');
        return;
    }

    const container = document.getElementById('lockHiddenIds');
    container.innerHTML = '';
    checked.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'student_ids[]';
        input.value = cb.value;
        container.appendChild(input);
    });

    const isLock = action === 'lock';
    document.getElementById('bulkLockAction').value = action;
    document.getElementById('lockModalTitle').textContent = isLock ? 'Bar / Lock Admit Cards' : 'Unlock Admit Cards';
    document.getElementById('lockCountLabel').textContent = `${isLock ? 'Locking' : 'Unlocking'} ${checked.length} selected students`;

    const reasonBlock = document.getElementById('lockReasonBlock');
    const submitBtn = document.getElementById('lockSubmitBtn');

    if (isLock) {
        reasonBlock.classList.remove('hidden');
        submitBtn.className = 'px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-md shadow-rose-200 dark:shadow-none transition flex items-center gap-1.5';
        submitBtn.innerHTML = '<i class="bi bi-shield-lock"></i> Apply Barring Lock';
    } else {
        reasonBlock.classList.add('hidden');
        submitBtn.className = 'px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-200 dark:shadow-none transition flex items-center gap-1.5';
        submitBtn.innerHTML = '<i class="bi bi-unlock"></i> Unlock & Clear Cards';
    }

    openDuesModal('bulkLockModal');
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
</style>
@endsection
