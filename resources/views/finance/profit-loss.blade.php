@extends('layouts.app')
@section('title', 'Profit & Loss Report')

@section('content')
<div class="px-6 py-5 max-w-7xl mx-auto">
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2 tracking-tight">
                <i class="bi bi-graph-up-arrow text-emerald-500"></i> Profit & Loss Report
            </h1>
            <nav class="flex text-xs text-slate-500 dark:text-slate-400 mt-1" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2">
                    <li class="inline-flex items-center">
                        <a href="{{ route('home') }}" class="hover:text-blue-600">Home</a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <span class="mx-2">/</span>
                            <a href="{{ route('finance.overview') }}" class="hover:text-blue-600">Finance</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <span class="mx-2">/</span>
                            <span class="text-slate-700 dark:text-slate-300 font-medium">P&L Report</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Controls --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6 bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-sm p-4">
        <form method="GET" class="flex items-end gap-3">
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Financial Year</label>
                <input type="number" name="year" value="{{ $year }}" class="w-32 border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-900/50 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-400 focus:outline-none transition-all">
            </div>
            <button class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition shadow-sm">
                Load Data
            </button>
        </form>
        <a href="{{ route('finance.profit-loss.pdf', ['year' => $year]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold text-red-700 bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50 transition">
            <i class="bi bi-file-pdf"></i> Export PDF
        </a>
    </div>

    {{-- Net Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl p-5 border border-emerald-100 dark:border-emerald-800/50 relative overflow-hidden">
            <div class="absolute -right-4 -top-4 opacity-10">
                <i class="bi bi-arrow-down-left-circle text-8xl text-emerald-500"></i>
            </div>
            <div class="relative z-10">
                <div class="text-sm font-bold text-emerald-800/70 dark:text-emerald-300 uppercase tracking-wide mb-1">Total Income</div>
                <div class="text-3xl font-black text-emerald-600 dark:text-emerald-400 mb-2">Rs. {{ number_format($totalIncome, 2) }}</div>
                <div class="text-xs font-semibold text-emerald-700 dark:text-emerald-500">
                    Fees: Rs. {{ number_format($income,2) }} &nbsp;|&nbsp; Other: Rs. {{ number_format($otherInc,2) }}
                </div>
            </div>
        </div>

        <div class="bg-red-50 dark:bg-red-900/20 rounded-2xl p-5 border border-red-100 dark:border-red-800/50 relative overflow-hidden">
            <div class="absolute -right-4 -top-4 opacity-10">
                <i class="bi bi-arrow-up-right-circle text-8xl text-red-500"></i>
            </div>
            <div class="relative z-10">
                <div class="text-sm font-bold text-red-800/70 dark:text-red-300 uppercase tracking-wide mb-1">Total Expenses</div>
                <div class="text-3xl font-black text-red-600 dark:text-red-400 mb-2">Rs. {{ number_format($totalExpenses, 2) }}</div>
                <div class="text-xs font-semibold text-red-700 dark:text-red-500">
                    Ops: Rs. {{ number_format($expenses,2) }} &nbsp;|&nbsp; Payroll: Rs. {{ number_format($payroll+$staffPay,2) }}
                </div>
            </div>
        </div>

        <div class="rounded-2xl p-5 border relative overflow-hidden {{ $netProfit >= 0 ? 'bg-blue-50 border-blue-100 dark:bg-blue-900/20 dark:border-blue-800/50' : 'bg-orange-50 border-orange-100 dark:bg-orange-900/20 dark:border-orange-800/50' }}">
            <div class="absolute -right-4 -top-4 opacity-10">
                <i class="bi {{ $netProfit >= 0 ? 'bi-graph-up-arrow text-blue-500' : 'bi-graph-down-arrow text-orange-500' }} text-8xl"></i>
            </div>
            <div class="relative z-10">
                <div class="text-sm font-bold uppercase tracking-wide mb-1 {{ $netProfit >= 0 ? 'text-blue-800/70 dark:text-blue-300' : 'text-orange-800/70 dark:text-orange-300' }}">
                    Net {{ $netProfit >= 0 ? 'Profit' : 'Loss' }}
                </div>
                <div class="text-3xl font-black mb-2 {{ $netProfit >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-orange-600 dark:text-orange-400' }}">
                    Rs. {{ number_format(abs($netProfit), 2) }}
                </div>
            </div>
        </div>
    </div>

    {{-- Monthly Chart --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-sm mb-6 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/60">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Monthly Income vs Expenses — {{ $year }}</h3>
        </div>
        <div class="p-4">
            <div id="plChart" style="min-height:300px"></div>
        </div>
    </div>

    {{-- Detailed Breakdown --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/60 flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                <h3 class="text-sm font-bold text-emerald-600 dark:text-emerald-400">Income Breakdown</h3>
            </div>
            <div class="p-0">
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        <tr>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">Fee Collections</td>
                            <td class="px-6 py-4 text-right font-semibold text-slate-800 dark:text-slate-200">Rs. {{ number_format($income,2) }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">Other Income</td>
                            <td class="px-6 py-4 text-right font-semibold text-slate-800 dark:text-slate-200">Rs. {{ number_format($otherInc,2) }}</td>
                        </tr>
                        <tr class="bg-emerald-50 dark:bg-emerald-900/10">
                            <td class="px-6 py-4 font-bold text-emerald-800 dark:text-emerald-400">Total Income</td>
                            <td class="px-6 py-4 text-right font-black text-emerald-600 dark:text-emerald-400">Rs. {{ number_format($totalIncome,2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/60 flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-red-500"></div>
                <h3 class="text-sm font-bold text-red-600 dark:text-red-400">Expense Breakdown</h3>
            </div>
            <div class="p-0">
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        <tr>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">Operational Expenses</td>
                            <td class="px-6 py-4 text-right font-semibold text-slate-800 dark:text-slate-200">Rs. {{ number_format($expenses,2) }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">Teacher Payroll</td>
                            <td class="px-6 py-4 text-right font-semibold text-slate-800 dark:text-slate-200">Rs. {{ number_format($payroll,2) }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">Staff Payroll</td>
                            <td class="px-6 py-4 text-right font-semibold text-slate-800 dark:text-slate-200">Rs. {{ number_format($staffPay,2) }}</td>
                        </tr>
                        <tr class="bg-red-50 dark:bg-red-900/10">
                            <td class="px-6 py-4 font-bold text-red-800 dark:text-red-400">Total Expenses</td>
                            <td class="px-6 py-4 text-right font-black text-red-600 dark:text-red-400">Rs. {{ number_format($totalExpenses,2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@latest/dist/apexcharts.min.js"></script>
<script>
const monthlyData = @json($monthlyData);
new ApexCharts(document.querySelector('#plChart'), {
    chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'inherit', background: 'transparent' },
    theme: { mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light' },
    series: [
        { name: 'Income',   data: monthlyData.map(d => parseFloat(d.income))   },
        { name: 'Expenses', data: monthlyData.map(d => parseFloat(d.expenses)) },
    ],
    xaxis: { 
        categories: monthlyData.map(d => d.month),
        labels: { style: { colors: document.documentElement.classList.contains('dark') ? '#94a3b8' : '#64748b' } },
        axisBorder: { color: document.documentElement.classList.contains('dark') ? '#334155' : '#e2e8f0' },
        axisTicks: { color: document.documentElement.classList.contains('dark') ? '#334155' : '#e2e8f0' }
    },
    yaxis: {
        labels: { 
            formatter: v => 'Rs. ' + v.toLocaleString(),
            style: { colors: document.documentElement.classList.contains('dark') ? '#94a3b8' : '#64748b' }
        }
    },
    colors: ['#10b981', '#ef4444'],
    plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
    dataLabels: { enabled: false },
    tooltip: { 
        theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
        y: { formatter: v => 'Rs. ' + v.toLocaleString() } 
    },
    grid: { borderColor: document.documentElement.classList.contains('dark') ? '#334155' : '#e2e8f0', strokeDashArray: 4 }
}).render();
</script>
@endpush
@endsection
