@extends('layouts.app')
@section('title', 'Profit & Loss Report — ' . $periodLabel)

@section('content')
<div class="px-6 py-5 max-w-7xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-11 h-11 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white shadow-lg shadow-emerald-500/20">
                    <i class="bi bi-graph-up-arrow text-xl"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        Profit & Loss Statement (नाफा नोक्सान हिसाब)
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-2">
                        <span class="inline-flex items-center gap-1 font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-md border border-emerald-200 dark:border-emerald-800">
                            <i class="bi bi-calendar3"></i> {{ $periodLabel }}
                        </span>
                        <span>•</span>
                        <span>{{ $periodSub }}</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('finance.profit-loss.pdf', ['period_type' => $periodType, 'fy' => $selectedFy, 'year' => $year]) }}"
               target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-md shadow-rose-600/20 transition-all">
                <i class="bi bi-file-earmark-pdf-fill text-sm"></i> Download Official PDF
            </a>
        </div>
    </div>

    {{-- Period Filter Toolbar --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm">
        <form method="GET" action="{{ route('finance.profit-loss') }}" id="filterForm" class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            
            {{-- Period Mode Switcher --}}
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Accounting Standard:</span>
                <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                    <button type="button" onclick="setPeriodMode('fy')"
                            class="px-3.5 py-1.5 text-xs font-extrabold rounded-lg transition-all {{ $periodType === 'fy' ? 'bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">
                        🇳🇵 Nepali Fiscal Year (साउन–असार)
                    </button>
                    <button type="button" onclick="setPeriodMode('ad')"
                            class="px-3.5 py-1.5 text-xs font-extrabold rounded-lg transition-all {{ $periodType === 'ad' ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">
                        🌐 Gregorian Year (Jan–Dec)
                    </button>
                </div>
                <input type="hidden" name="period_type" id="periodTypeInput" value="{{ $periodType }}">
            </div>

            {{-- Fiscal Year / AD Year Selector --}}
            <div class="flex items-center gap-3">
                @if($periodType === 'fy')
                    <label class="text-xs font-bold text-slate-600 dark:text-slate-300">आर्थिक वर्ष (Fiscal Year):</label>
                    <select name="fy" onchange="this.form.submit()"
                            class="text-xs font-bold bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500">
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
                    <button type="submit" class="px-3 py-2 bg-emerald-600 text-white text-xs font-bold rounded-xl">Load</button>
                </noscript>
            </div>
        </form>
    </div>

    {{-- Top Net Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        {{-- Total Income --}}
        <div class="bg-gradient-to-br from-emerald-500/10 via-emerald-500/5 to-transparent dark:from-emerald-950/40 rounded-2xl p-5 border border-emerald-200 dark:border-emerald-800/60 relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-black text-emerald-800 dark:text-emerald-300 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="bi bi-arrow-down-left-circle-fill text-emerald-600"></i> Total Revenue / Income (कुल आम्दानी)
                </span>
                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 dark:bg-emerald-900/60 px-2 py-0.5 rounded-full">Inflows</span>
            </div>
            <div class="text-3xl font-black text-emerald-700 dark:text-emerald-300 mb-2 tracking-tight">
                Rs. {{ number_format($totalIncome, 2) }}
            </div>
            <div class="text-xs font-semibold text-emerald-800/80 dark:text-emerald-400 flex items-center justify-between border-t border-emerald-200/60 dark:border-emerald-800/60 pt-2">
                <span>Fee Collections: <strong>Rs. {{ number_format($income, 2) }}</strong></span>
                <span>Other: <strong>Rs. {{ number_format($otherInc, 2) }}</strong></span>
            </div>
        </div>

        {{-- Total Expenses --}}
        <div class="bg-gradient-to-br from-rose-500/10 via-rose-500/5 to-transparent dark:from-rose-950/40 rounded-2xl p-5 border border-rose-200 dark:border-rose-800/60 relative overflow-hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-black text-rose-800 dark:text-rose-300 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="bi bi-arrow-up-right-circle-fill text-rose-600"></i> Total Expenditure (कुल खर्च)
                </span>
                <span class="text-[10px] font-bold text-rose-700 bg-rose-100 dark:bg-rose-900/60 px-2 py-0.5 rounded-full">Outflows</span>
            </div>
            <div class="text-3xl font-black text-rose-700 dark:text-rose-300 mb-2 tracking-tight">
                Rs. {{ number_format($totalExpenses, 2) }}
            </div>
            <div class="text-xs font-semibold text-rose-800/80 dark:text-rose-400 flex items-center justify-between border-t border-rose-200/60 dark:border-rose-800/60 pt-2">
                <span>Operational: <strong>Rs. {{ number_format($expenses, 2) }}</strong></span>
                <span>Payroll: <strong>Rs. {{ number_format($payroll + $staffPay, 2) }}</strong></span>
            </div>
        </div>

        {{-- Net Surplus / Deficit --}}
        <div class="rounded-2xl p-5 border relative overflow-hidden {{ $netProfit >= 0 ? 'bg-gradient-to-br from-indigo-500/10 via-blue-500/5 to-transparent border-indigo-200 dark:border-indigo-800/60 dark:from-indigo-950/40' : 'bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-transparent border-amber-200 dark:border-amber-800/60 dark:from-amber-950/40' }}">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-black uppercase tracking-wider flex items-center gap-1.5 {{ $netProfit >= 0 ? 'text-indigo-800 dark:text-indigo-300' : 'text-amber-800 dark:text-amber-300' }}">
                    <i class="bi {{ $netProfit >= 0 ? 'bi-trophy-fill text-indigo-600' : 'bi-exclamation-triangle-fill text-amber-600' }}"></i>
                    Net {{ $netProfit >= 0 ? 'Surplus / Profit (खुद नाफा)' : 'Deficit / Loss (खुद नोक्सान)' }}
                </span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $netProfit >= 0 ? 'text-indigo-700 bg-indigo-100 dark:bg-indigo-900/60' : 'text-amber-700 bg-amber-100 dark:bg-amber-900/60' }}">
                    Margin: {{ $totalIncome > 0 ? number_format(($netProfit / $totalIncome) * 100, 1) : 0 }}%
                </span>
            </div>
            <div class="text-3xl font-black mb-2 tracking-tight {{ $netProfit >= 0 ? 'text-indigo-700 dark:text-indigo-300' : 'text-amber-700 dark:text-amber-400' }}">
                Rs. {{ number_format(abs($netProfit), 2) }}
            </div>
            <div class="text-xs font-semibold flex items-center justify-between border-t pt-2 {{ $netProfit >= 0 ? 'text-indigo-800/80 dark:text-indigo-400 border-indigo-200/60 dark:border-indigo-800/60' : 'text-amber-800/80 dark:text-amber-400 border-amber-200/60 dark:border-amber-800/60' }}">
                <span>Formula: Income - Expenses</span>
                <span>Status: <strong>{{ $netProfit >= 0 ? 'Profitable' : 'Deficit' }}</strong></span>
            </div>
        </div>
    </div>

    {{-- Quarterly Performance Breakdown --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($quarterlyData as $qKey => $qInfo)
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-4 shadow-sm">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-black text-slate-700 dark:text-slate-300">{{ $qInfo['name'] }}</span>
                    <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded {{ $qInfo['net'] >= 0 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' }}">
                        {{ $qInfo['net'] >= 0 ? '+ Surplus' : '- Deficit' }}
                    </span>
                </div>
                <div class="text-sm font-black {{ $qInfo['net'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                    Rs. {{ number_format($qInfo['net'], 2) }}
                </div>
                <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 space-y-0.5 border-t border-slate-100 dark:border-slate-800 pt-1.5">
                    <div class="flex justify-between">
                        <span>Revenue:</span>
                        <span class="font-bold text-emerald-600">Rs. {{ number_format($qInfo['income'], 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Expenses:</span>
                        <span class="font-bold text-rose-600">Rs. {{ number_format($qInfo['expenses'], 2) }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Monthly Trends Bar Chart --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="bi bi-bar-chart-line text-emerald-600"></i> Monthly Financial Trajectory — {{ $periodLabel }}
            </h3>
            <span class="text-xs text-slate-400 font-medium">Income vs Expenses (12 Months)</span>
        </div>
        <div class="p-5">
            <div id="plChart" style="min-height:300px"></div>
        </div>
    </div>

    {{-- 12-Month Detailed Ledger Breakdown Table --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="bi bi-table text-indigo-600"></i> 12-Month Chronological Breakdown (महिनावारी हिसाब)
            </h3>
            <span class="text-xs text-slate-400">All figures in Nepalese Rupees (NPR)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 uppercase font-black tracking-wider border-b border-slate-200/80 dark:border-slate-800">
                    <tr>
                        <th class="px-5 py-3">Month (महिना)</th>
                        <th class="px-5 py-3">Quarter</th>
                        <th class="px-5 py-3 text-right">Income (आम्दानी)</th>
                        <th class="px-5 py-3 text-right">Expenses (खर्च)</th>
                        <th class="px-5 py-3 text-right">Net Profit / Loss (खुद)</th>
                        <th class="px-5 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                    @foreach($monthlyData as $row)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                            <td class="px-5 py-3.5 font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $row['net'] >= 0 ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                {{ $row['month'] }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">
                                <span class="bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded text-[10px] font-bold">{{ $row['quarter'] }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right font-bold text-emerald-600 dark:text-emerald-400">
                                Rs. {{ number_format($row['income'], 2) }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-bold text-rose-600 dark:text-rose-400">
                                Rs. {{ number_format($row['expenses'], 2) }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-black {{ $row['net'] >= 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-amber-600 dark:text-amber-400' }}">
                                {{ $row['net'] >= 0 ? '+' : '' }}Rs. {{ number_format($row['net'], 2) }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if($row['net'] > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">Surplus</span>
                                @elseif($row['net'] < 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">Deficit</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">Even</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-100/80 dark:bg-slate-800 text-slate-800 dark:text-slate-100 font-black border-t-2 border-slate-300 dark:border-slate-700">
                    <tr>
                        <td class="px-5 py-3.5 text-sm" colspan="2">Fiscal Period Total (कुल जम्मा):</td>
                        <td class="px-5 py-3.5 text-right text-sm text-emerald-700 dark:text-emerald-400">Rs. {{ number_format($totalIncome, 2) }}</td>
                        <td class="px-5 py-3.5 text-right text-sm text-rose-700 dark:text-rose-400">Rs. {{ number_format($totalExpenses, 2) }}</td>
                        <td class="px-5 py-3.5 text-right text-sm {{ $netProfit >= 0 ? 'text-indigo-700 dark:text-indigo-300' : 'text-amber-700 dark:text-amber-400' }}">
                            {{ $netProfit >= 0 ? '+' : '' }}Rs. {{ number_format($netProfit, 2) }}
                        </td>
                        <td class="px-5 py-3.5 text-center text-xs">
                            {{ $netProfit >= 0 ? 'SURPLUS' : 'DEFICIT' }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Categorical Income & Expense Breakdowns --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Income Breakdown --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 bg-emerald-50/50 dark:bg-emerald-950/20 border-b border-emerald-100 dark:border-emerald-900/40 flex items-center gap-2">
                <i class="bi bi-wallet2 text-emerald-600 text-base"></i>
                <h3 class="text-sm font-black text-emerald-800 dark:text-emerald-300">Revenue Stream Breakdown (आम्दानी शीर्षक)</h3>
            </div>
            <table class="w-full text-xs">
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr>
                        <td class="px-6 py-3.5 text-slate-600 dark:text-slate-300 font-medium">Student Fee Collections (मासिक/वार्षिक शुल्क)</td>
                        <td class="px-6 py-3.5 text-right font-bold text-slate-800 dark:text-slate-200">Rs. {{ number_format($income, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-3.5 text-slate-600 dark:text-slate-300 font-medium">Miscellaneous & Other Revenue (अन्य आम्दानी)</td>
                        <td class="px-6 py-3.5 text-right font-bold text-slate-800 dark:text-slate-200">Rs. {{ number_format($otherInc, 2) }}</td>
                    </tr>
                    <tr class="bg-emerald-50/60 dark:bg-emerald-950/40">
                        <td class="px-6 py-3.5 font-black text-emerald-900 dark:text-emerald-300">Total Inflows (जम्मा आम्दानी)</td>
                        <td class="px-6 py-3.5 text-right font-black text-emerald-700 dark:text-emerald-300 text-sm">Rs. {{ number_format($totalIncome, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Expense Breakdown --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 bg-rose-50/50 dark:bg-rose-950/20 border-b border-rose-100 dark:border-rose-900/40 flex items-center gap-2">
                <i class="bi bi-receipt text-rose-600 text-base"></i>
                <h3 class="text-sm font-black text-rose-800 dark:text-rose-300">Expense Stream Breakdown (खर्च शीर्षक)</h3>
            </div>
            <table class="w-full text-xs">
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr>
                        <td class="px-6 py-3.5 text-slate-600 dark:text-slate-300 font-medium">Operational & Office Expenses (दैनिक प्रशासनिक खर्च)</td>
                        <td class="px-6 py-3.5 text-right font-bold text-slate-800 dark:text-slate-200">Rs. {{ number_format($expenses, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-3.5 text-slate-600 dark:text-slate-300 font-medium">Teacher Payroll Disbursed (शिक्षक तलब)</td>
                        <td class="px-6 py-3.5 text-right font-bold text-slate-800 dark:text-slate-200">Rs. {{ number_format($payroll, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-3.5 text-slate-600 dark:text-slate-300 font-medium">Administrative Staff Payroll (कर्मचारी तलब)</td>
                        <td class="px-6 py-3.5 text-right font-bold text-slate-800 dark:text-slate-200">Rs. {{ number_format($staffPay, 2) }}</td>
                    </tr>
                    <tr class="bg-rose-50/60 dark:bg-rose-950/40">
                        <td class="px-6 py-3.5 font-black text-rose-900 dark:text-rose-300">Total Outflows (जम्मा खर्च)</td>
                        <td class="px-6 py-3.5 text-right font-black text-rose-700 dark:text-rose-300 text-sm">Rs. {{ number_format($totalExpenses, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@latest/dist/apexcharts.min.js"></script>
<script>
function setPeriodMode(mode) {
    document.getElementById('periodTypeInput').value = mode;
    document.getElementById('filterForm').submit();
}

const monthlyData = @json($monthlyData);
new ApexCharts(document.querySelector('#plChart'), {
    chart: { type: 'bar', height: 320, toolbar: { show: false }, fontFamily: 'inherit', background: 'transparent' },
    theme: { mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light' },
    series: [
        { name: 'Income (आम्दानी)', data: monthlyData.map(d => parseFloat(d.income)) },
        { name: 'Expenses (खर्च)',   data: monthlyData.map(d => parseFloat(d.expenses)) },
    ],
    xaxis: { 
        categories: monthlyData.map(d => d.month_short || d.month),
        labels: { 
            style: { 
                colors: document.documentElement.classList.contains('dark') ? '#94a3b8' : '#64748b',
                fontSize: '11px',
                fontWeight: 600
            } 
        },
        axisBorder: { color: document.documentElement.classList.contains('dark') ? '#334155' : '#e2e8f0' },
        axisTicks: { color: document.documentElement.classList.contains('dark') ? '#334155' : '#e2e8f0' }
    },
    yaxis: {
        labels: { 
            formatter: v => 'Rs. ' + Number(v).toLocaleString(),
            style: { colors: document.documentElement.classList.contains('dark') ? '#94a3b8' : '#64748b', fontSize: '11px' }
        }
    },
    colors: ['#10b981', '#ef4444'],
    plotOptions: { 
        bar: { 
            borderRadius: 5, 
            columnWidth: '55%',
            dataLabels: { position: 'top' }
        } 
    },
    dataLabels: { enabled: false },
    tooltip: { 
        theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
        y: { formatter: v => 'Rs. ' + Number(v).toLocaleString() } 
    },
    grid: { 
        borderColor: document.documentElement.classList.contains('dark') ? '#334155' : '#f1f5f9', 
        strokeDashArray: 4 
    },
    legend: {
        position: 'top',
        horizontalAlign: 'right',
        labels: { colors: document.documentElement.classList.contains('dark') ? '#cbd5e1' : '#475569' }
    }
}).render();
</script>
@endpush
@endsection
