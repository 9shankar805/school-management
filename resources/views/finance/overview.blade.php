@extends('layouts.app')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-7xl mx-auto">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-10 h-10 rounded-2xl bg-[#D3F8EE] text-[#0D7C66] flex items-center justify-center font-bold text-xl shadow-xs border border-[#A7F3D0]">
                    <i class="bi bi-cash-stack"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Finance Dashboard</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Institutional revenue streams, fee collections, expense auditing, and cash flow</p>
                </div>
            </div>
        </div>

        {{-- Action Bar --}}
        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('finance.pos') }}" class="px-4 py-2.5 bg-[#0D7C66] hover:bg-[#00A884] text-white rounded-xl text-xs font-black shadow-xs flex items-center gap-1.5 transition">
                <i class="bi bi-terminal-dash"></i> ⚡ Fee POS Counter
            </a>

            <a href="{{ route('finance.dues.index') }}" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-black shadow-xs flex items-center gap-1.5 transition">
                <i class="bi bi-exclamation-octagon-fill text-amber-200"></i> Dues & Defaulters
            </a>

            <a href="{{ route('finance.day-book') }}" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black shadow-xs flex items-center gap-1.5 transition">
                <i class="bi bi-journal-bookmark-fill text-indigo-200"></i> Day Book & Cash Counter
            </a>

            <a href="{{ route('finance.bank-accounts.index') }}" class="px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-black shadow-xs flex items-center gap-1.5 transition">
                <i class="bi bi-bank2"></i> Bank Accounts & QR
            </a>

            <a href="{{ route('finance.fee-profiles') }}" class="px-4 py-2.5 bg-[#D3F8EE] hover:bg-[#A7F3D0] text-[#0D7C66] rounded-xl text-xs font-bold shadow-xs flex items-center gap-1.5 transition">
                <i class="bi bi-person-vcard"></i> Fee Profiles
            </a>

            <a href="{{ route('payments.create') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold shadow-xs flex items-center gap-1.5 transition">
                <i class="bi bi-plus-circle"></i> Create Invoice
            </a>

            <a href="{{ route('finance.reports') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold shadow-xs flex items-center gap-1.5 transition">
                <i class="bi bi-file-earmark-bar-graph"></i> Reports
            </a>
        </div>
    </div>

    @include('session-messages')

    {{-- KPI Cards (Sleek SaaS Aesthetics) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
        
        {{-- Total Invoiced --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Invoiced</span>
                <span class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center text-sm">
                    <i class="bi bi-file-earmark-text"></i>
                </span>
            </div>
            <div>
                <div class="text-lg font-bold text-slate-800 dark:text-slate-100">Rs. {{ number_format($totalInvoiced, 2) }}</div>
                <div class="text-[10px] text-slate-400">Cumulative billings</div>
            </div>
        </div>

        {{-- Total Collected --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-[#0D7C66] uppercase tracking-wider">Collected</span>
                <span class="w-8 h-8 rounded-xl bg-[#D3F8EE] text-[#0D7C66] flex items-center justify-center text-sm border border-[#A7F3D0]">
                    <i class="bi bi-check2-circle"></i>
                </span>
            </div>
            <div>
                <div class="text-lg font-bold text-[#0D7C66] dark:text-teal-400">Rs. {{ number_format($totalCollected, 2) }}</div>
                <div class="text-[10px] text-slate-400">Verified receipts</div>
            </div>
        </div>

        {{-- Outstanding Due --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-rose-500 uppercase tracking-wider">Outstanding</span>
                <span class="w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 flex items-center justify-center text-sm">
                    <i class="bi bi-exclamation-circle"></i>
                </span>
            </div>
            <div>
                <div class="text-lg font-bold text-rose-600 dark:text-rose-400">Rs. {{ number_format($totalDue, 2) }}</div>
                <div class="text-[10px] text-slate-400">Pending collections</div>
            </div>
        </div>

        {{-- Overdue Invoices --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-amber-500 uppercase tracking-wider">Overdue</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 flex items-center justify-center text-sm">
                    <i class="bi bi-clock-history"></i>
                </span>
            </div>
            <div>
                <div class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $overdueCount }} <span class="text-xs font-normal text-slate-400">invoices</span></div>
                <div class="text-[10px] text-rose-500 font-semibold">Requires follow-up</div>
            </div>
        </div>

        {{-- Monthly Income --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Income (Month)</span>
                <span class="w-8 h-8 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 flex items-center justify-center text-sm">
                    <i class="bi bi-arrow-down-left"></i>
                </span>
            </div>
            <div>
                <div class="text-lg font-bold text-slate-800 dark:text-slate-100">Rs. {{ number_format($totalIncome, 2) }}</div>
                <div class="text-[10px] text-slate-400">Non-fee / other sources</div>
            </div>
        </div>

        {{-- Monthly Expenses --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-4 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Expenses (Month)</span>
                <span class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 flex items-center justify-center text-sm">
                    <i class="bi bi-arrow-up-right"></i>
                </span>
            </div>
            <div>
                <div class="text-lg font-bold text-slate-800 dark:text-slate-100">Rs. {{ number_format($totalExpense, 2) }}</div>
                <div class="text-[10px] text-slate-400">Operations & overhead</div>
            </div>
        </div>

    </div>

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        
        {{-- Monthly Fee Collection Chart --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#00A884]"></span>
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Monthly Fee Collection (Last 12 Months)</h2>
                </div>
                <span class="text-xs text-slate-400 font-semibold">Trend Analysis</span>
            </div>
            <div id="collectionChart" style="min-height: 260px;"></div>
        </div>

        {{-- Expense Breakdown Chart --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Expense Breakdown (30 Days)</h2>
                </div>
                <a href="{{ route('finance.expenses') }}" class="text-xs font-bold text-[#0D7C66] hover:underline">View All</a>
            </div>
            <div id="expenseChart" style="min-height: 260px;"></div>
        </div>

    </div>

    {{-- Tables: Overdue Invoices & Recent Collections --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        
        {{-- Overdue Invoices --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 flex items-center justify-center text-sm font-bold">
                        <i class="bi bi-exclamation-triangle"></i>
                    </span>
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Overdue Invoices</h2>
                </div>
                <a href="{{ route('payments.index', ['status' => 'unpaid']) }}" class="text-xs font-bold text-rose-600 hover:underline">View all</a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold bg-slate-50/50 dark:bg-slate-800/20">
                        <tr>
                            <th class="py-3 px-4">Student</th>
                            <th class="py-3 px-4">Invoice Title</th>
                            <th class="py-3 px-4 text-right">Amount</th>
                            <th class="py-3 px-4 text-center">Due Date</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($overdueInvoices as $inv)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                            <td class="py-3 px-4">
                                <a href="{{ route('finance.fee-profiles', $inv->student_id) }}" class="font-bold text-slate-800 dark:text-slate-200 hover:text-[#0D7C66] transition">
                                    {{ $inv->student->first_name ?? '—' }} {{ $inv->student->last_name ?? '' }}
                                </a>
                            </td>
                            <td class="py-3 px-4 text-slate-500">{{ $inv->title }}</td>
                            <td class="py-3 px-4 text-right font-bold text-slate-800 dark:text-slate-100">Rs. {{ number_format($inv->amount, 2) }}</td>
                            <td class="py-3 px-4 text-center text-rose-600 font-semibold">
                                {{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d M Y') : '—' }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('payments.pay', $inv->id) }}" class="px-2.5 py-1 bg-[#00A884] hover:bg-[#0D7C66] text-white rounded-lg text-xs font-bold transition">
                                    Pay
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-slate-400 py-8">No overdue invoices.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Collections --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-[#D3F8EE] text-[#0D7C66] flex items-center justify-center text-sm font-bold border border-[#A7F3D0]">
                        <i class="bi bi-receipt"></i>
                    </span>
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Recent Collections</h2>
                </div>
                <a href="{{ route('payments.index', ['status' => 'paid']) }}" class="text-xs font-bold text-[#0D7C66] hover:underline">View all</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold bg-slate-50/50 dark:bg-slate-800/20">
                        <tr>
                            <th class="py-3 px-4">Student</th>
                            <th class="py-3 px-4 text-right">Amount</th>
                            <th class="py-3 px-4 text-center">Date</th>
                            <th class="py-3 px-4 text-right">Receipt</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($recentPayments as $pay)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                            <td class="py-3 px-4">
                                <a href="{{ route('finance.fee-profiles', $pay->invoice->student_id ?? 0) }}" class="font-bold text-slate-800 dark:text-slate-200 hover:text-[#0D7C66] transition">
                                    {{ $pay->invoice->student->first_name ?? '—' }} {{ $pay->invoice->student->last_name ?? '' }}
                                </a>
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-emerald-600">Rs. {{ number_format($pay->amount, 2) }}</td>
                            <td class="py-3 px-4 text-center text-slate-500">{{ \Carbon\Carbon::parse($pay->payment_date)->format('d M Y') }}</td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('finance.receipt', $pay->id) }}" target="_blank" class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-[#D3F8EE] text-[#0D7C66] rounded-lg text-xs font-bold transition" title="Print Receipt">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-slate-400 py-8">No payments recorded yet.</td></tr>
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
                    label: 'Collection (NPR)',
                    data: {!! json_encode($monthlyCollection->values()) !!},
                    borderColor: '#00A884',
                    backgroundColor: 'rgba(0, 168, 132, 0.08)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#00A884',
                    pointRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { 
                    y: { 
                        beginAtZero: true,
                        grid: { color: 'rgba(226, 232, 240, 0.4)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
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
                    backgroundColor: ['#00A884', '#0D9488', '#0284C7', '#F59E0B', '#EF4444', '#8B5CF6', '#64748B']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { 
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 11 } }
                    } 
                }
            }
        });
    }
</script>
@endpush
@endsection
