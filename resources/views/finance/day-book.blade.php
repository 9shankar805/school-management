@extends('layouts.app')
@section('title', 'Day Book (रोजनाम्चा) — ' . $bsDateNp)

@section('content')
<div class="px-6 py-5 max-w-7xl mx-auto space-y-6">

    {{-- Breadcrumb & Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-11 h-11 rounded-2xl bg-gradient-to-tr from-indigo-600 to-blue-500 text-white shadow-lg shadow-indigo-500/20">
                    <i class="bi bi-journal-bookmark-fill text-xl"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        Day Book (रोजनाम्चा / दैनिक खाता)
                    </h1>
                    <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-slate-500 dark:text-slate-400">
                        <span class="inline-flex items-center gap-1.5 font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/40 px-2.5 py-0.5 rounded-lg border border-indigo-200 dark:border-indigo-800">
                            <i class="bi bi-calendar2-day-fill"></i> {{ $bsDateNp }} ({{ $bsDateEn }} BS)
                        </span>
                        <span class="inline-flex items-center gap-1 font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-lg">
                            {{ $dayOfWeekNp }}
                        </span>
                        <span class="inline-flex items-center gap-1 font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-lg">
                            {{ $currentFyNp }}
                        </span>
                        <span class="text-slate-400">|</span>
                        <span>{{ \Carbon\Carbon::parse($date)->format('F j, Y (l)') }} AD</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if($isLocked)
                <a href="{{ route('finance.day-book.slip', ['date' => $date]) }}" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md transition">
                    <i class="bi bi-receipt-cutoff text-sm"></i> Print Cash Slip (A5)
                </a>
            @endif
            <a href="{{ route('finance.day-book.pdf', ['date' => $date]) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-md transition">
                <i class="bi bi-printer-fill text-sm"></i> Print Day Book PDF
            </a>
        </div>
    </div>

    @include('session-messages')

    {{-- Day-End Closing & Lock Status Banner --}}
    @if($isLocked)
        <div class="p-4 bg-emerald-50/90 dark:bg-emerald-950/40 border-2 border-emerald-300 dark:border-emerald-700/80 rounded-2xl shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center flex-shrink-0 shadow-md">
                    <i class="bi bi-lock-fill text-xl"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-black text-emerald-900 dark:text-emerald-200">
                            दिनको हिसाब बन्द तथा लक गरिएको छ (Day Book Closed & Locked)
                        </h3>
                        <span class="bg-emerald-200 dark:bg-emerald-800 text-emerald-800 dark:text-emerald-200 text-[10px] font-extrabold px-2 py-0.5 rounded-full uppercase">
                            Reconciled
                        </span>
                    </div>
                    <p class="text-xs text-emerald-800/80 dark:text-emerald-300 mt-1">
                        बन्द गर्ने अधिकृत: <strong>{{ $closing->closedByUser->full_name ?? 'Authorized Accountant' }}</strong>
                        &nbsp;•&nbsp; 
                        मिति/समय: <strong>{{ $closing->closed_at ? $closing->closed_at->format('Y-m-d h:i A') : '—' }}</strong>
                        &nbsp;•&nbsp; 
                        भौतिक नगद (Actual Counted): <strong>Rs. {{ number_format($closing->actual_cash, 2) }}</strong>
                        &nbsp;•&nbsp; 
                        फरक (Variance): 
                        <strong class="{{ $closing->difference == 0 ? 'text-emerald-700' : ($closing->difference > 0 ? 'text-blue-700' : 'text-rose-700') }}">
                            {{ $closing->difference >= 0 ? '+' : '' }}Rs. {{ number_format($closing->difference, 2) }}
                        </strong>
                    </p>
                    @if($closing->notes)
                        <p class="text-[11px] text-emerald-700 dark:text-emerald-400 mt-1 italic">
                            कैफियत (Note): "{{ $closing->notes }}"
                        </p>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('finance.day-book.unlock') }}" onsubmit="return confirm('के तपाईं यो दिनको हिसाब अनलक गर्न चाहनुहुन्छ? हिसाब फेरि सच्याउन सकिनेछ।');">
                    @csrf
                    <input type="hidden" name="date" value="{{ $date }}">
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-sm transition">
                        <i class="bi bi-unlock-fill"></i> हिसाब अनलक गर्नुहोस् (Unlock)
                    </button>
                </form>
            </div>
        </div>
    @else
        <div class="p-3.5 bg-blue-50/70 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800/60 rounded-2xl flex items-center justify-between text-xs text-blue-800 dark:text-blue-300 font-semibold">
            <span class="flex items-center gap-2">
                <i class="bi bi-info-circle-fill text-blue-600 text-base"></i>
                <span>दिनको कारोबार खुला छ (Day Book Open). दिनको कारोबार समाप्त भएपछि तलको नगद दरबन्दी गणना गरी हिसाब लक गर्नुहोस्।</span>
            </span>
            <span class="bg-blue-100 dark:bg-blue-900/60 px-2 py-0.5 rounded text-[10px] uppercase font-bold text-blue-800 dark:text-blue-200">
                Open for Transactions
            </span>
        </div>
    @endif

    {{-- Date Picker & Quick Navigation Toolbar --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <form method="GET" action="{{ route('finance.day-book') }}" class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <label class="text-xs font-bold text-slate-600 dark:text-slate-300">कारोबार मिति (Transaction Date):</label>
                <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()"
                       class="text-xs font-semibold rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition inline-flex items-center gap-1.5 shadow-sm">
                    <i class="bi bi-calendar-check"></i> View Day Book
                </button>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('finance.day-book', ['date' => today()->toDateString()]) }}" 
                   class="px-3 py-1.5 text-xs font-bold rounded-xl transition {{ $date === today()->toDateString() ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    आज (Today)
                </a>
                <a href="{{ route('finance.day-book', ['date' => today()->subDay()->toDateString()]) }}" 
                   class="px-3 py-1.5 text-xs font-bold rounded-xl transition {{ $date === today()->subDay()->toDateString() ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    हिजो (Yesterday)
                </a>
                <a href="{{ route('finance.day-book', ['date' => today()->subDays(2)->toDateString()]) }}" 
                   class="px-3 py-1.5 text-xs font-bold rounded-xl transition {{ $date === today()->subDays(2)->toDateString() ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    अस्ति (2 Days Ago)
                </a>
            </div>
        </form>
    </div>

    {{-- Daily Cash Balance Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Opening Balance --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-5">
            <p class="text-xs text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">Opening Balance (सुरु मौज्दात)</p>
            <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1">Rs. {{ number_format($openingBalance, 2) }}</h3>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Opening Cash in Hand: <strong>Rs. {{ number_format($openingCash, 2) }}</strong></p>
        </div>

        {{-- Total Inflows --}}
        <div class="bg-emerald-50/60 dark:bg-emerald-950/20 rounded-2xl border border-emerald-200 dark:border-emerald-800/60 shadow-sm p-5">
            <p class="text-xs text-emerald-800 dark:text-emerald-300 font-bold uppercase tracking-wider">Total Inflows (जम्मा आम्दानी)</p>
            <h3 class="text-2xl font-black text-emerald-700 dark:text-emerald-300 mt-1">+ Rs. {{ number_format($totalInflow, 2) }}</h3>
            <p class="text-[11px] text-emerald-800/70 dark:text-emerald-400 mt-0.5">Cash: Rs. {{ number_format($cashInflow, 2) }} | Digital: Rs. {{ number_format($onlineInflow, 2) }}</p>
        </div>

        {{-- Total Outflows --}}
        <div class="bg-rose-50/60 dark:bg-rose-950/20 rounded-2xl border border-rose-200 dark:border-rose-800/60 shadow-sm p-5">
            <p class="text-xs text-rose-800 dark:text-rose-300 font-bold uppercase tracking-wider">Total Outflows (जम्मा खर्च)</p>
            <h3 class="text-2xl font-black text-rose-700 dark:text-rose-300 mt-1">- Rs. {{ number_format($totalOutflow, 2) }}</h3>
            <p class="text-[11px] text-rose-800/70 dark:text-rose-400 mt-0.5">Cash: Rs. {{ number_format($cashOutflow, 2) }} | Bank: Rs. {{ number_format($onlineOutflow, 2) }}</p>
        </div>

        {{-- Closing Balance --}}
        <div class="bg-indigo-50/60 dark:bg-indigo-950/30 rounded-2xl border border-indigo-200 dark:border-indigo-800/60 shadow-sm p-5">
            <p class="text-xs text-indigo-800 dark:text-indigo-300 font-bold uppercase tracking-wider">Expected Cash in Hand (अपेक्षित नगद)</p>
            <h3 class="text-2xl font-black text-indigo-900 dark:text-indigo-300 mt-1">Rs. {{ number_format($expectedCash, 2) }}</h3>
            <p class="text-[11px] text-slate-500 mt-0.5">Total Day's Net: <strong class="{{ $netDailyChange >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $netDailyChange >= 0 ? '+' : '' }}Rs. {{ number_format($netDailyChange, 2) }}</strong></p>
        </div>
    </div>

    {{-- CASH DENOMINATION COUNTER & DAY-END CLOSING LOCK FORM --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-black flex items-center gap-2">
                    <i class="bi bi-cash-coin text-amber-400"></i> दैनिक भौतिक नगद दरबन्दी गणना (Cash Denomination Counter)
                </h3>
                <p class="text-xs text-indigo-200 mt-0.5">
                    Physical banknote counting, reconciliation, and day-end closing lock.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold bg-white/10 px-3 py-1 rounded-xl border border-white/20">
                    Expected Cash: <strong class="text-amber-300">Rs. {{ number_format($expectedCash, 2) }}</strong>
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('finance.day-book.close') }}" id="denominationForm" class="p-6">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- Column 1 & 2: Banknote Denomination Inputs (2 Columns Grid) --}}
                <div class="lg:col-span-2 space-y-4">
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        Banknotes & Coins Count (नोट तथा सिक्का संख्या)
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @php
                            $notesList = [
                                '1000' => ['label' => 'रू १००० (Rs. 1,000)', 'color' => 'bg-amber-500/10 border-amber-300 dark:border-amber-700 text-amber-700 dark:text-amber-400'],
                                '500'  => ['label' => 'रू ५०० (Rs. 500)',   'color' => 'bg-teal-500/10 border-teal-300 dark:border-teal-700 text-teal-700 dark:text-teal-400'],
                                '100'  => ['label' => 'रू १०० (Rs. 100)',   'color' => 'bg-emerald-500/10 border-emerald-300 dark:border-emerald-700 text-emerald-700 dark:text-emerald-400'],
                                '50'   => ['label' => 'रू ५० (Rs. 50)',     'color' => 'bg-sky-500/10 border-sky-300 dark:border-sky-700 text-sky-700 dark:text-sky-400'],
                                '20'   => ['label' => 'रू २० (Rs. 20)',     'color' => 'bg-indigo-500/10 border-indigo-300 dark:border-indigo-700 text-indigo-700 dark:text-indigo-400'],
                                '10'   => ['label' => 'रू १० (Rs. 10)',     'color' => 'bg-violet-500/10 border-violet-300 dark:border-violet-700 text-violet-700 dark:text-violet-400'],
                                '5'    => ['label' => 'रू ५ (Rs. 5)',       'color' => 'bg-rose-500/10 border-rose-300 dark:border-rose-700 text-rose-700 dark:text-rose-400'],
                                '2'    => ['label' => 'रू २ (Rs. 2)',       'color' => 'bg-slate-500/10 border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-400'],
                                '1'    => ['label' => 'रू १ (Rs. 1)',       'color' => 'bg-slate-500/10 border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-400'],
                            ];
                        @endphp

                        @foreach($notesList as $denomValue => $meta)
                            <div class="flex items-center justify-between p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
                                <div class="w-32">
                                    <span class="text-xs font-black {{ $meta['color'] }} px-2 py-0.5 rounded-md border inline-block">
                                        {{ $meta['label'] }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-slate-400 font-bold">×</span>
                                    <input type="number" min="0" step="1"
                                           name="denominations[{{ $denomValue }}]"
                                           id="denom_{{ $denomValue }}"
                                           value="{{ $denominations[$denomValue] ?? 0 }}"
                                           data-val="{{ $denomValue }}"
                                           oninput="calcDenominations()"
                                           {{ $isLocked ? 'disabled' : '' }}
                                           class="w-20 text-center font-bold text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 py-1.5 focus:ring-2 focus:ring-indigo-500">
                                    <span class="text-xs font-black text-slate-700 dark:text-slate-300 w-24 text-right" id="subtotal_{{ $denomValue }}">
                                        Rs. {{ number_format(($denominations[$denomValue] ?? 0) * (int)$denomValue, 2) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach

                        {{-- Coins / Loose Change --}}
                        <div class="flex items-center justify-between p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40">
                            <div class="w-32">
                                <span class="text-xs font-black bg-amber-500/10 border-amber-300 dark:border-amber-700 text-amber-700 dark:text-amber-400 px-2 py-0.5 rounded-md border inline-block">
                                    सिक्का / खुद्रा (Coins)
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-slate-400 font-bold">=</span>
                                <input type="number" min="0" step="0.5"
                                       name="denominations[coins]"
                                       id="denom_coins"
                                       value="{{ $denominations['coins'] ?? 0 }}"
                                       oninput="calcDenominations()"
                                       {{ $isLocked ? 'disabled' : '' }}
                                       class="w-24 text-right font-bold text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 py-1.5 focus:ring-2 focus:ring-indigo-500">
                                <span class="text-xs font-black text-slate-700 dark:text-slate-300 w-20 text-right" id="subtotal_coins">
                                    Rs. {{ number_format((float)($denominations['coins'] ?? 0), 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Column 3: Live Reconciliation Summary & Action Box --}}
                <div class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-200 dark:border-slate-700/80 p-5 flex flex-col justify-between space-y-4">
                    <div>
                        <h4 class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                            <i class="bi bi-calculator-fill text-indigo-600"></i> भौतिक नगद मिलान (Reconciliation)
                        </h4>

                        <div class="space-y-3 text-xs">
                            <div class="flex justify-between py-1.5 border-b border-slate-200/60 dark:border-slate-700">
                                <span class="text-slate-500 dark:text-slate-400">अपेक्षित नगद (Expected Cash):</span>
                                <span class="font-bold text-slate-800 dark:text-slate-100">Rs. {{ number_format($expectedCash, 2) }}</span>
                            </div>

                            <div class="flex justify-between py-1.5 border-b border-slate-200/60 dark:border-slate-700">
                                <span class="text-slate-500 dark:text-slate-400">गणना गरिएको नगद (Counted Cash):</span>
                                <span class="font-black text-indigo-600 dark:text-indigo-400 text-sm" id="countedCashDisplay">
                                    Rs. {{ number_format($closing ? $closing->actual_cash : 0, 2) }}
                                </span>
                            </div>

                            <div class="flex justify-between py-2 rounded-xl px-3" id="diffBox" style="background:#f1f5f9;">
                                <span class="font-bold text-slate-700 dark:text-slate-300">फरक रकम (Variance):</span>
                                <span class="font-black text-sm" id="diffDisplay">Rs. 0.00</span>
                            </div>

                            <div class="p-2.5 rounded-xl text-xs font-bold text-center" id="statusBadgeBox">
                                <span id="statusBadgeText">गणना सुरु गर्नुहोस्...</span>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1">
                                    कैफियत / टिप्पणी (Closing Remarks / Notes):
                                </label>
                                <textarea name="notes" rows="2"
                                          placeholder="कुनै फरक भएमा कारण उल्लेख गर्नुहोस्..."
                                          {{ $isLocked ? 'disabled' : '' }}
                                          class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-2.5 focus:ring-2 focus:ring-indigo-500">{{ $closing->notes ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div>
                        @if(!$isLocked)
                            <button type="submit"
                                    class="w-full py-3 px-4 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-xs font-black rounded-xl shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-2">
                                <i class="bi bi-shield-lock-fill text-sm"></i> दिनको हिसाब बन्द र लक गर्नुहोस् (Confirm & Lock Day Book)
                            </button>
                            <p class="text-[10px] text-slate-400 text-center mt-2">
                                लक गरेपछि हिसाब आधिकारिक रुपमा सुरक्षित हुन्छ र आवश्यक परेमा अनलक गर्न सकिन्छ।
                            </p>
                        @else
                            <div class="text-center py-2">
                                <span class="inline-flex items-center gap-1.5 text-xs font-extrabold text-emerald-700 dark:text-emerald-400">
                                    <i class="bi bi-check-circle-fill"></i> यो मितिको हिसाब बन्द र लक गरिएको छ
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </form>
    </div>

    {{-- Two Column Detail: Receipts vs Disbursements --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- LEFT COLUMN: RECEIPTS & INFLOWS --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col justify-between">
            <div>
                <div class="bg-emerald-50 dark:bg-emerald-950/30 px-6 py-4 border-b border-emerald-100 dark:border-emerald-900/50 flex items-center justify-between">
                    <div>
                        <h3 class="font-black text-emerald-900 dark:text-emerald-300 text-sm flex items-center gap-2">
                            <i class="bi bi-arrow-down-left-circle-fill text-emerald-600"></i> Receipts & Inflows (आम्दानी र असुली)
                        </h3>
                        <p class="text-[11px] text-emerald-700 dark:text-emerald-400 mt-0.5">Student fee receipts & miscellaneous earnings</p>
                    </div>
                    <span class="text-xs font-black text-emerald-800 dark:text-emerald-300 bg-white dark:bg-slate-800 px-3 py-1 rounded-full border border-emerald-200 dark:border-emerald-800">
                        Rs. {{ number_format($totalInflow, 2) }}
                    </span>
                </div>

                <div class="p-4 overflow-x-auto max-h-[520px]">
                    <table class="w-full text-left text-xs">
                        <thead class="text-slate-400 font-black text-[10px] uppercase border-b border-slate-100 dark:border-slate-800">
                            <tr>
                                <th class="pb-2.5">Receipt / Particulars</th>
                                <th class="pb-2.5">Method</th>
                                <th class="pb-2.5 text-right">Amount (रकम)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                            @forelse($payments as $payment)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3">
                                    <p class="font-bold text-slate-800 dark:text-slate-100">{{ $payment->invoice->student->full_name ?? 'Student Fee' }}</p>
                                    <p class="text-[10px] text-slate-400 font-mono">
                                        Receipt #{{ $payment->receipt_number ?? $payment->id }} | Class {{ $payment->invoice->schoolClass->name ?? '—' }}
                                    </p>
                                </td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $payment->payment_method === 'cash' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300' }}">
                                        {{ strtoupper($payment->payment_method ?? 'CASH') }}
                                    </span>
                                </td>
                                <td class="py-3 text-right font-black text-emerald-600 dark:text-emerald-400">
                                    Rs. {{ number_format($payment->amount, 2) }}
                                </td>
                            </tr>
                            @empty
                            @endforelse

                            @foreach($incomes as $inc)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3">
                                    <p class="font-bold text-slate-800 dark:text-slate-100">{{ $inc->title }}</p>
                                    <p class="text-[10px] text-slate-400">Other Income: {{ $inc->category ?? 'General' }}</p>
                                </td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300">
                                        {{ strtoupper($inc->payment_method ?? 'CASH') }}
                                    </span>
                                </td>
                                <td class="py-3 text-right font-black text-emerald-600 dark:text-emerald-400">
                                    Rs. {{ number_format($inc->amount, 2) }}
                                </td>
                            </tr>
                            @endforeach

                            @if($payments->isEmpty() && $incomes->isEmpty())
                            <tr>
                                <td colspan="3" class="py-12 text-center text-slate-400 text-xs">
                                    <i class="bi bi-inbox text-2xl block mb-1"></i>
                                    No receipt transactions recorded on this date.
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="px-6 py-3 bg-emerald-50/50 dark:bg-emerald-950/30 border-t border-emerald-100 dark:border-emerald-900/40 flex justify-between items-center text-xs font-bold text-emerald-900 dark:text-emerald-300">
                <span>Total Receipts (जम्मा आम्दानी):</span>
                <span class="text-sm font-black text-emerald-700 dark:text-emerald-300">Rs. {{ number_format($totalInflow, 2) }}</span>
            </div>
        </div>

        {{-- RIGHT COLUMN: PAYMENTS & OUTFLOWS --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col justify-between">
            <div>
                <div class="bg-rose-50 dark:bg-rose-950/30 px-6 py-4 border-b border-rose-100 dark:border-rose-900/50 flex items-center justify-between">
                    <div>
                        <h3 class="font-black text-rose-900 dark:text-rose-300 text-sm flex items-center gap-2">
                            <i class="bi bi-arrow-up-right-circle-fill text-rose-600"></i> Payments & Outflows (खर्च र भुक्तानी)
                        </h3>
                        <p class="text-[11px] text-rose-700 dark:text-rose-400 mt-0.5">Operating expenditures & payroll disbursements</p>
                    </div>
                    <span class="text-xs font-black text-rose-800 dark:text-rose-300 bg-white dark:bg-slate-800 px-3 py-1 rounded-full border border-rose-200 dark:border-rose-800">
                        Rs. {{ number_format($totalOutflow, 2) }}
                    </span>
                </div>

                <div class="p-4 overflow-x-auto max-h-[520px]">
                    <table class="w-full text-left text-xs">
                        <thead class="text-slate-400 font-black text-[10px] uppercase border-b border-slate-100 dark:border-slate-800">
                            <tr>
                                <th class="pb-2.5">Voucher / Particulars</th>
                                <th class="pb-2.5">Category</th>
                                <th class="pb-2.5 text-right">Amount (रकम)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                            @forelse($expenses as $exp)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3">
                                    <p class="font-bold text-slate-800 dark:text-slate-100">{{ $exp->title ?? $exp->description }}</p>
                                    <p class="text-[10px] text-slate-400">Paid to: {{ $exp->paid_to ?? 'Vendor/Staff' }}</p>
                                </td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        {{ $exp->category ?? 'Operational' }}
                                    </span>
                                </td>
                                <td class="py-3 text-right font-black text-rose-600 dark:text-rose-400">
                                    Rs. {{ number_format($exp->amount, 2) }}
                                </td>
                            </tr>
                            @empty
                            @endforelse

                            @foreach($teacherPayrolls as $tp)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3">
                                    <p class="font-bold text-slate-800 dark:text-slate-100">{{ $tp->teacher->full_name ?? 'Teacher Salary' }}</p>
                                    <p class="text-[10px] text-slate-400">Teacher Payroll Disbursed</p>
                                </td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                        Faculty Salary
                                    </span>
                                </td>
                                <td class="py-3 text-right font-black text-rose-600 dark:text-rose-400">
                                    Rs. {{ number_format($tp->net_salary, 2) }}
                                </td>
                            </tr>
                            @endforeach

                            @foreach($staffPayrolls as $sp)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3">
                                    <p class="font-bold text-slate-800 dark:text-slate-100">{{ $sp->user->full_name ?? 'Staff Salary' }}</p>
                                    <p class="text-[10px] text-slate-400">Staff Salary Slip #{{ $sp->id }}</p>
                                </td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                        Staff Salary
                                    </span>
                                </td>
                                <td class="py-3 text-right font-black text-rose-600 dark:text-rose-400">
                                    Rs. {{ number_format($sp->net_salary, 2) }}
                                </td>
                            </tr>
                            @endforeach

                            @if($expenses->isEmpty() && $teacherPayrolls->isEmpty() && $staffPayrolls->isEmpty())
                            <tr>
                                <td colspan="3" class="py-12 text-center text-slate-400 text-xs">
                                    <i class="bi bi-inbox text-2xl block mb-1"></i>
                                    No disbursements recorded on this date.
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="px-6 py-3 bg-rose-50/50 dark:bg-rose-950/30 border-t border-rose-100 dark:border-rose-900/40 flex justify-between items-center text-xs font-bold text-rose-900 dark:text-rose-300">
                <span>Total Disbursements (जम्मा खर्च):</span>
                <span class="text-sm font-black text-rose-700 dark:text-rose-300">Rs. {{ number_format($totalOutflow, 2) }}</span>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
const expectedCash = {{ (float) $expectedCash }};

function calcDenominations() {
    let total = 0;
    const denoms = ['1000', '500', '100', '50', '20', '10', '5', '2', '1'];

    denoms.forEach(d => {
        const input = document.getElementById('denom_' + d);
        const subtotalEl = document.getElementById('subtotal_' + d);
        const qty = input ? parseInt(input.value) || 0 : 0;
        const sub = qty * parseInt(d);
        total += sub;
        if (subtotalEl) {
            subtotalEl.innerText = 'Rs. ' + sub.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
    });

    // Coins
    const coinsInput = document.getElementById('denom_coins');
    const coinsSubtotal = document.getElementById('subtotal_coins');
    const coinsVal = coinsInput ? parseFloat(coinsInput.value) || 0 : 0;
    total += coinsVal;
    if (coinsSubtotal) {
        coinsSubtotal.innerText = 'Rs. ' + coinsVal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    const countedDisplay = document.getElementById('countedCashDisplay');
    if (countedDisplay) {
        countedDisplay.innerText = 'Rs. ' + total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    const diff = total - expectedCash;
    const diffDisplay = document.getElementById('diffDisplay');
    const diffBox = document.getElementById('diffBox');
    const statusBadgeBox = document.getElementById('statusBadgeBox');
    const statusBadgeText = document.getElementById('statusBadgeText');

    if (diffDisplay) {
        diffDisplay.innerText = (diff >= 0 ? '+' : '') + 'Rs. ' + diff.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    if (Math.abs(diff) < 0.01) {
        if (diffBox) diffBox.className = 'flex justify-between py-2 rounded-xl px-3 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 font-black';
        if (statusBadgeBox) statusBadgeBox.className = 'p-2.5 rounded-xl text-xs font-bold text-center bg-emerald-50 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700';
        if (statusBadgeText) statusBadgeText.innerHTML = '✓ शून्य फरक: भौतिक नगद र खाताको हिसाब बराबर मिल्यो (Exact Match)';
    } else if (diff > 0) {
        if (diffBox) diffBox.className = 'flex justify-between py-2 rounded-xl px-3 bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 font-black';
        if (statusBadgeBox) statusBadgeBox.className = 'p-2.5 rounded-xl text-xs font-bold text-center bg-blue-50 text-blue-800 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-300 dark:border-blue-700';
        if (statusBadgeText) statusBadgeText.innerHTML = '⚠ अधिशेष (Surplus Cash in Hand): अपेक्षित भन्दा Rs. ' + diff.toFixed(2) + ' बढी छ';
    } else {
        if (diffBox) diffBox.className = 'flex justify-between py-2 rounded-xl px-3 bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 font-black';
        if (statusBadgeBox) statusBadgeBox.className = 'p-2.5 rounded-xl text-xs font-bold text-center bg-rose-50 text-rose-800 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-300 dark:border-rose-700';
        if (statusBadgeText) statusBadgeText.innerHTML = '⚠ न्यून (Cash Shortage): अपेक्षित भन्दा Rs. ' + Math.abs(diff).toFixed(2) + ' कम छ';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    calcDenominations();
});
</script>
@endpush
@endsection
