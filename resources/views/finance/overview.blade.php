@extends('layouts.app')
@section('content')
<div class="px-4 lg:px-8 py-6 max-w-7xl mx-auto">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-10 h-10 rounded-2xl bg-emerald-600/10 text-emerald-600 flex items-center justify-center font-bold text-xl shadow-xs">
                    <i class="bi bi-cash-stack"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Finance Overview</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Track revenue, expenses, and financial performance</p>
                </div>
            </div>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800/80 px-3 py-1.5 rounded-xl">
                <li><a href="{{ route('home') }}" class="hover:text-emerald-600 transition">Home</a></li>
                <li class="text-slate-300 dark:text-slate-600">/</li>
                <li class="font-semibold text-slate-800 dark:text-slate-200">Finance</li>
            </ol>
        </nav>
    </div>

    @include('session-messages')

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
        @php $kpis = [
            ['val'=>'Rs. '.number_format($totalInvoiced,2),  'label'=>'Total Invoiced',  'icon'=>'bi-file-earmark-text', 'color'=>'emerald'],
            ['val'=>'Rs. '.number_format($totalCollected,2), 'label'=>'Collected',       'icon'=>'bi-check-circle',      'color'=>'blue'],
            ['val'=>'Rs. '.number_format($totalDue,2),       'label'=>'Outstanding',     'icon'=>'bi-exclamation-circle','color'=>'amber'],
            ['val'=>$overdueCount,                           'label'=>'Overdue Invoices','icon'=>'bi-x-circle',          'color'=>'rose'],
            ['val'=>'Rs. '.number_format($totalIncome,2),    'label'=>'Income (month)',  'icon'=>'bi-arrow-down-circle', 'color'=>'indigo'],
            ['val'=>'Rs. '.number_format($totalExpense,2),   'label'=>'Expenses (month)','icon'=>'bi-arrow-up-circle',   'color'=>'slate'],
        ]; @endphp
        @foreach($kpis as $k)
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-4">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-{{ $k['color'] }}-500/10 text-{{ $k['color'] }}-600 flex items-center justify-center">
                    <i class="bi {{ $k['icon'] }} text-lg"></i>
                </span>
                <div>
                    <div class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $k['val'] }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ $k['label'] }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
        {{-- Monthly collection chart --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                <span class="w-2 h-5 bg-emerald-600 rounded-full"></span>
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Monthly Fee Collection (Last 12 Months)</h2>
            </div>
            <div id="collectionChart"></div>
        </div>
        {{-- Expense breakdown --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                <span class="w-2 h-5 bg-rose-600 rounded-full"></span>
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Expense Breakdown (Last 30 Days)</h2>
            </div>
            <div id="expenseChart"></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
        {{-- Overdue invoices --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                <span class="w-2 h-5 bg-rose-600 rounded-full"></span>
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Overdue Invoices</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-semibold bg-slate-50/50 dark:bg-slate-800/20">
                        <tr>
                            <th class="py-3 px-4">Student</th>
                            <th class="py-3 px-4">Title</th>
                            <th class="py-3 px-4">Amount</th>
                            <th class="py-3 px-4">Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($overdueInvoices as $inv)
                        <tr class="border-b border-slate-50 dark:border-slate-800/50 hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                            <td class="py-3 px-4">{{ $inv->student->first_name ?? '—' }} {{ $inv->student->last_name ?? '' }}</td>
                            <td class="py-3 px-4">{{ $inv->title }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-100">Rs. {{ number_format($inv->amount,2) }}</td>
                            <td class="py-3 px-4 text-rose-600">{{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d M Y') : '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-slate-400 py-6">No overdue invoices.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        {{-- Recent payments --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                <span class="w-2 h-5 bg-emerald-600 rounded-full"></span>
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Recent Payments</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-semibold bg-slate-50/50 dark:bg-slate-800/20">
                        <tr>
                            <th class="py-3 px-4">Student</th>
                            <th class="py-3 px-4">Amount</th>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPayments as $pay)
                        <tr class="border-b border-slate-50 dark:border-slate-800/50 hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                            <td class="py-3 px-4">{{ $pay->invoice->student->first_name ?? '—' }} {{ $pay->invoice->student->last_name ?? '' }}</td>
                            <td class="py-3 px-4 font-semibold text-emerald-600">Rs. {{ number_format($pay->amount,2) }}</td>
                            <td class="py-3 px-4">{{ \Carbon\Carbon::parse($pay->payment_date)->format('d M Y') }}</td>
                            <td class="py-3 px-4">
                                <a href="{{ route('finance.receipt', $pay->id) }}" class="text-slate-400 hover:text-emerald-600 transition" title="Receipt">
                                    <i class="bi bi-receipt"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-slate-400 py-6">No payments yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Monthly collection chart
    const collectionCtx = document.getElementById('collectionChart');
    if (collectionCtx) {
        new Chart(collectionCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($monthlyCollection->keys()) !!},
                datasets: [{
                    label: 'Collection',
                    data: {!! json_encode($monthlyCollection->values()) !!},
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    // Expense breakdown chart
    const expenseCtx = document.getElementById('expenseChart');
    if (expenseCtx) {
        new Chart(expenseCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($expenseByCategory->keys()) !!},
                datasets: [{
                    data: {!! json_encode($expenseByCategory->values()) !!},
                    backgroundColor: ['#f43f5e', '#f97316', '#eab308', '#22c55e', '#3b82f6', '#8b5cf6']
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }
</script>
@endpush
@endsection
