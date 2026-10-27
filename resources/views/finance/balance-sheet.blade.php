@extends('layouts.app')
@section('title', 'Balance Sheet (वासलात) — ' . $periodLabel)

@section('content')
<div class="px-6 py-5 max-w-7xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-11 h-11 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-500 text-white shadow-lg shadow-indigo-500/20">
                    <i class="bi bi-clipboard2-data-fill text-xl"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        Balance Sheet (वासलात / वित्तीय स्थिति विवरण)
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-2">
                        <span class="inline-flex items-center gap-1 font-semibold text-indigo-700 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/40 px-2 py-0.5 rounded-md border border-indigo-200 dark:border-indigo-800">
                            <i class="bi bi-calendar-event"></i> {{ $periodLabel }}
                        </span>
                        <span>•</span>
                        <span>मूल्याङ्कन मिति: <strong>{{ $asOfLabel }}</strong></span>
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('finance.balance-sheet.pdf', ['period_type' => $periodType, 'fy' => $selectedFy, 'year' => $year]) }}"
               target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-md shadow-rose-600/20 transition-all">
                <i class="bi bi-file-earmark-pdf-fill text-sm"></i> Download Official PDF
            </a>
        </div>
    </div>

    {{-- Period Filter Toolbar --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <form method="GET" action="{{ route('finance.balance-sheet') }}" id="bsFilterForm" class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            
            {{-- Period Mode Switcher --}}
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Evaluation Period:</span>
                <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                    <button type="button" onclick="setBsPeriodMode('fy')"
                            class="px-3.5 py-1.5 text-xs font-extrabold rounded-lg transition-all {{ $periodType === 'fy' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">
                        🇳🇵 Nepali Fiscal Year (असार मसान्तसम्म)
                    </button>
                    <button type="button" onclick="setBsPeriodMode('ad')"
                            class="px-3.5 py-1.5 text-xs font-extrabold rounded-lg transition-all {{ $periodType === 'ad' ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">
                        🌐 Calendar Year (As of 31 Dec)
                    </button>
                </div>
                <input type="hidden" name="period_type" id="bsPeriodTypeInput" value="{{ $periodType }}">
            </div>

            {{-- Fiscal Year / AD Year Selector --}}
            <div class="flex items-center gap-3">
                @if($periodType === 'fy')
                    <label class="text-xs font-bold text-slate-600 dark:text-slate-300">आर्थिक वर्ष (Fiscal Year):</label>
                    <select name="fy" onchange="this.form.submit()"
                            class="text-xs font-bold bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                        @foreach($fyList as $item)
                            <option value="{{ $item['key'] }}" {{ $item['key'] === $selectedFy ? 'selected' : '' }}>
                                {{ $item['label'] }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <label class="text-xs font-bold text-slate-600 dark:text-slate-300">Calendar Year (AD):</label>
                    <select name="year" onchange="this.form.submit()"
                            class="text-xs font-bold bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500">
                        @for($y = now()->year + 1; $y >= now()->year - 6; $y--)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>Year {{ $y }}</option>
                        @endfor
                    </select>
                @endif
                <noscript>
                    <button type="submit" class="px-3 py-2 bg-indigo-600 text-white text-xs font-bold rounded-xl">Load</button>
                </noscript>
            </div>
        </form>
    </div>

    @include('session-messages')

    {{-- Equation & Reconciliation Alert --}}
    @if(abs($balanceCheck) <= 1)
        <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60 rounded-2xl flex items-center justify-between text-xs font-bold text-emerald-800 dark:text-emerald-300">
            <span class="flex items-center gap-2">
                <i class="bi bi-patch-check-fill text-emerald-600 text-base"></i>
                <span>सन्तुलित वासलात (Balanced Sheet): <strong>Assets (NPR {{ number_format($totalAssets, 2) }}) = Liabilities & Equity (NPR {{ number_format($totalLiabilities + $totalEquity, 2) }})</strong></span>
            </span>
            <span class="bg-emerald-100 dark:bg-emerald-900/60 px-2 py-0.5 rounded text-[10px] uppercase tracking-wider font-extrabold">Reconciled</span>
        </div>
    @else
        <div class="p-3.5 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 rounded-2xl flex items-center justify-between text-xs font-bold text-amber-800 dark:text-amber-300">
            <span class="flex items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill text-amber-500 text-base"></i>
                <span>विभेद रकम (Discrepancy): NPR {{ number_format(abs($balanceCheck), 2) }} — Difference between Assets and Liabilities + Equity.</span>
            </span>
            <span class="bg-amber-100 dark:bg-amber-900/60 px-2 py-0.5 rounded text-[10px] uppercase tracking-wider font-extrabold">Reconciliation Needed</span>
        </div>
    @endif

    {{-- Two Column Layout: Assets vs Liabilities & Equity --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- ── LEFT: ASSETS (सम्पत्ति) ─────────────────────────────────── --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col justify-between">
            <div>
                <div class="px-6 py-4 bg-emerald-50 dark:bg-emerald-950/30 border-b border-emerald-100 dark:border-emerald-900/50 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-shield-check text-emerald-600 text-lg"></i>
                        <h2 class="font-black text-emerald-900 dark:text-emerald-300 text-base tracking-tight">1. Assets (सम्पत्ति)</h2>
                    </div>
                    <span class="text-[11px] font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-900/60 px-2.5 py-0.5 rounded-full">
                        NPR {{ number_format($totalAssets, 2) }}
                    </span>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                    {{-- Current Assets --}}
                    <div class="px-6 py-4">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">चालु सम्पत्ति (Current Assets)</p>
                        
                        <div class="flex justify-between py-2">
                            <span class="text-slate-600 dark:text-slate-300 flex items-center gap-2">
                                <i class="bi bi-cash-stack text-emerald-500"></i> Cash & Bank Balance (नगद तथा बैंक मौज्दात)
                            </span>
                            <span class="font-bold text-slate-800 dark:text-slate-100">NPR {{ number_format($cashAndBank, 2) }}</span>
                        </div>

                        <div class="flex justify-between py-2">
                            <span class="text-slate-600 dark:text-slate-300 flex items-center gap-2">
                                <i class="bi bi-file-earmark-spreadsheet text-blue-500"></i> Accounts Receivable (उठ्न बाँकी शुल्क)
                            </span>
                            <span class="font-bold text-slate-800 dark:text-slate-100">NPR {{ number_format($receivables, 2) }}</span>
                        </div>
                    </div>

                    {{-- Non-Current Assets --}}
                    <div class="px-6 py-4">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">स्थिर सम्पत्ति (Fixed / Non-Current Assets)</p>
                        
                        <div class="flex justify-between py-2">
                            <span class="text-slate-600 dark:text-slate-300 flex items-center gap-2">
                                <i class="bi bi-buildings text-violet-500"></i> Fixed Assets at Book Cost (स्कूल भवन, फर्निचर, उपकरण)
                            </span>
                            <span class="font-bold text-slate-800 dark:text-slate-100">NPR {{ number_format($fixedAssets, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Total Assets Footer --}}
            <div class="px-6 py-4 bg-emerald-50/80 dark:bg-emerald-950/40 border-t border-emerald-200/80 dark:border-emerald-800">
                <div class="flex justify-between items-center text-sm font-black">
                    <span class="text-emerald-900 dark:text-emerald-300">कुल सम्पत्ति (Total Assets):</span>
                    <span class="text-emerald-700 dark:text-emerald-300 text-base">NPR {{ number_format($totalAssets, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- ── RIGHT: LIABILITIES + EQUITY (दायित्व र पुँजी) ───────────── --}}
        <div class="space-y-6">

            {{-- Liabilities --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-rose-50 dark:bg-rose-950/30 border-b border-rose-100 dark:border-rose-900/50 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-exclamation-circle text-rose-600 text-lg"></i>
                        <h2 class="font-black text-rose-900 dark:text-rose-300 text-base tracking-tight">2. Liabilities (दायित्व)</h2>
                    </div>
                    <span class="text-[11px] font-bold text-rose-700 dark:text-rose-400 bg-rose-100 dark:bg-rose-900/60 px-2.5 py-0.5 rounded-full">
                        NPR {{ number_format($totalLiabilities, 2) }}
                    </span>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs px-6 py-4">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">चालु दायित्व (Current Liabilities)</p>
                    
                    <div class="flex justify-between py-2">
                        <span class="text-slate-600 dark:text-slate-300 flex items-center gap-2">
                            <i class="bi bi-people text-amber-500"></i> Salaries Payable (दिन बाँकी शिक्षक/कर्मचारी तलब)
                        </span>
                        <span class="font-bold text-slate-800 dark:text-slate-100">NPR {{ number_format($salariesPayable + $teacherSalariesPayable, 2) }}</span>
                    </div>

                    <div class="flex justify-between py-2">
                        <span class="text-slate-600 dark:text-slate-300 flex items-center gap-2">
                            <i class="bi bi-receipt-cutoff text-rose-500"></i> TDS Payable (कट्टी गरिएको आयकर दायित्व - 15%)
                        </span>
                        <span class="font-bold text-slate-800 dark:text-slate-100">NPR {{ number_format($tdsPayable, 2) }}</span>
                    </div>
                </div>

                <div class="px-6 py-3 bg-rose-50/50 dark:bg-rose-950/20 border-t border-rose-100 dark:border-rose-900/40">
                    <div class="flex justify-between items-center text-xs font-black">
                        <span class="text-rose-900 dark:text-rose-300">कुल दायित्व (Total Liabilities):</span>
                        <span class="text-rose-700 dark:text-rose-300 font-bold">NPR {{ number_format($totalLiabilities, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Equity --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-indigo-50 dark:bg-indigo-950/30 border-b border-indigo-100 dark:border-indigo-900/50 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-bank text-indigo-600 text-lg"></i>
                        <h2 class="font-black text-indigo-900 dark:text-indigo-300 text-base tracking-tight">3. Equity & Reserves (पुँजी तथा कोष)</h2>
                    </div>
                    <span class="text-[11px] font-bold text-indigo-700 dark:text-indigo-400 bg-indigo-100 dark:bg-indigo-900/60 px-2.5 py-0.5 rounded-full">
                        NPR {{ number_format($totalEquity, 2) }}
                    </span>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs px-6 py-4">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">शेयर/स्वामित्व पुँजी (Owner's Equity)</p>
                    
                    <div class="flex justify-between py-2">
                        <span class="text-slate-600 dark:text-slate-300 flex items-center gap-2">
                            <i class="bi bi-graph-up text-indigo-500"></i> Cumulative Retained Earnings (संचित मुनाफा / कोष)
                        </span>
                        <span class="font-black {{ $retainedEarnings >= 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-amber-600 dark:text-amber-400' }}">
                            NPR {{ number_format($retainedEarnings, 2) }}
                        </span>
                    </div>
                </div>

                <div class="px-6 py-3 bg-indigo-50/50 dark:bg-indigo-950/20 border-t border-indigo-100 dark:border-indigo-900/40">
                    <div class="flex justify-between items-center text-xs font-black">
                        <span class="text-indigo-900 dark:text-indigo-300">कुल इक्विटी (Total Equity):</span>
                        <span class="text-indigo-700 dark:text-indigo-300 font-bold">NPR {{ number_format($totalEquity, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Grand Total: Liabilities + Equity --}}
            <div class="p-4 bg-slate-900 text-white rounded-2xl shadow-sm flex items-center justify-between text-sm font-black">
                <span class="flex items-center gap-2">
                    <i class="bi bi-check2-circle text-emerald-400 text-lg"></i>
                    <span>Total Liabilities & Equity (कुल दायित्व र पुँजी):</span>
                </span>
                <span class="text-base text-emerald-400">NPR {{ number_format($totalLiabilities + $totalEquity, 2) }}</span>
            </div>
        </div>
    </div>
</div>

<script>
function setBsPeriodMode(mode) {
    document.getElementById('bsPeriodTypeInput').value = mode;
    document.getElementById('bsFilterForm').submit();
}
</script>
@endsection
