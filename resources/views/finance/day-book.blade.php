@extends('layouts.app')

@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">

    {{-- Breadcrumb & Header --}}
    <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
        <div>
            <nav class="text-xs text-slate-400 mb-1">
                <a href="{{ url('home') }}" class="hover:text-indigo-600">Dashboard</a>
                <span class="mx-1">/</span>
                <a href="{{ route('finance.ledger') }}" class="hover:text-indigo-600">Finance</a>
                <span class="mx-1">/</span>
                <span class="text-slate-600">Day Book</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
                <i class="bi bi-journal-bookmark-fill text-indigo-600"></i> Day Book (रोजनाम्चा / दैनिक खाता)
            </h1>
            <p class="text-slate-400 text-sm mt-0.5">
                Daily cash & bank collection, expense disbursements and balance reconciliation for 
                <span class="font-semibold text-slate-700">{{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</span>
                @if(function_exists('bsDate'))
                    <span class="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded text-xs ml-1">({{ bsDate($date) }})</span>
                @endif
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('finance.day-book.pdf', ['date' => $date]) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                <i class="bi bi-printer"></i> Print Day Book PDF
            </a>
        </div>
    </div>

    @include('session-messages')

    {{-- Date Picker Toolbar --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-6">
        <form method="GET" action="{{ route('finance.day-book') }}" class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <label class="text-xs font-semibold text-slate-600">Select Date:</label>
                <input type="date" name="date" value="{{ $date }}"
                       class="text-xs rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition inline-flex items-center gap-1.5">
                    <i class="bi bi-calendar-check"></i> View Day Book
                </button>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('finance.day-book', ['date' => today()->toDateString()]) }}" 
                   class="px-3 py-1.5 text-xs font-medium rounded-lg {{ $date === today()->toDateString() ? 'bg-indigo-50 text-indigo-600 font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Today
                </a>
                <a href="{{ route('finance.day-book', ['date' => today()->subDay()->toDateString()]) }}" 
                   class="px-3 py-1.5 text-xs font-medium rounded-lg {{ $date === today()->subDay()->toDateString() ? 'bg-indigo-50 text-indigo-600 font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Yesterday
                </a>
            </div>
        </form>
    </div>

    {{-- Balance Summary Bar --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Opening Balance (सुरु मौज्दात)</p>
            <h3 class="text-xl font-bold text-slate-700 mt-1">Rs. {{ number_format($openingBalance, 2) }}</h3>
            <p class="text-[11px] text-slate-400 mt-0.5">Net balance carried forward</p>
        </div>

        <div class="bg-white rounded-2xl border border-emerald-100 shadow-sm p-5 bg-emerald-50/20">
            <p class="text-xs text-emerald-600 font-medium uppercase tracking-wider">Total Inflows (जम्मा आम्दानी)</p>
            <h3 class="text-2xl font-bold text-emerald-700 mt-1">+ Rs. {{ number_format($totalInflow, 2) }}</h3>
            <p class="text-[11px] text-slate-500 mt-0.5">Cash: Rs. {{ number_format($cashInflow, 2) }} | Digital: Rs. {{ number_format($onlineInflow, 2) }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-rose-100 shadow-sm p-5 bg-rose-50/20">
            <p class="text-xs text-rose-600 font-medium uppercase tracking-wider">Total Outflows (जम्मा खर्च)</p>
            <h3 class="text-2xl font-bold text-rose-700 mt-1">- Rs. {{ number_format($totalOutflow, 2) }}</h3>
            <p class="text-[11px] text-slate-500 mt-0.5">Expenses: Rs. {{ number_format($expenseTotal, 2) }} | Payroll: Rs. {{ number_format($payrollTotal, 2) }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-indigo-100 shadow-sm p-5 bg-indigo-50/30">
            <p class="text-xs text-indigo-600 font-medium uppercase tracking-wider">Closing Balance (अन्तिम मौज्दात)</p>
            <h3 class="text-2xl font-bold text-indigo-900 mt-1">Rs. {{ number_format($closingBalance, 2) }}</h3>
            <p class="text-[11px] text-slate-500 mt-0.5">Day's Net: <span class="{{ $netDailyChange >= 0 ? 'text-emerald-600 font-semibold' : 'text-rose-600 font-semibold' }}">{{ $netDailyChange >= 0 ? '+' : '' }}Rs. {{ number_format($netDailyChange, 2) }}</span></p>
        </div>
    </div>

    {{-- Two Column Detail: Receipts vs Payments --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- LEFT COLUMN: CASH & ONLINE RECEIPTS (INFLOWS) --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="bg-emerald-50/80 px-6 py-4 border-b border-emerald-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-emerald-900 text-sm flex items-center gap-2">
                        <i class="bi bi-arrow-down-left-circle-fill text-emerald-600"></i> Receipts & Inflows (आम्दानी र असुली)
                    </h3>
                    <p class="text-[11px] text-emerald-700 mt-0.5">Student fees & miscellaneous revenues</p>
                </div>
                <span class="text-sm font-bold text-emerald-800 bg-white px-3 py-1 rounded-full border border-emerald-200">
                    Rs. {{ number_format($totalInflow, 2) }}
                </span>
            </div>

            <div class="p-4 overflow-x-auto max-h-[500px]">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="text-slate-400 font-semibold text-[10px] uppercase border-b border-slate-100">
                        <tr>
                            <th class="pb-2">Receipt / Description</th>
                            <th class="pb-2">Method</th>
                            <th class="pb-2 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($payments as $payment)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5">
                                <p class="font-bold text-slate-800">{{ $payment->invoice->student->full_name ?? 'Student Fee' }}</p>
                                <p class="text-[10px] text-slate-400 font-mono">
                                    Rec #{{ $payment->receipt_number ?? $payment->id }} | Class {{ $payment->invoice->schoolClass->name ?? '—' }}
                                </p>
                            </td>
                            <td class="py-2.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $payment->payment_method === 'cash' ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700' }}">
                                    {{ strtoupper($payment->payment_method ?? 'CASH') }}
                                </span>
                            </td>
                            <td class="py-2.5 text-right font-bold text-emerald-600">
                                Rs. {{ number_format($payment->amount, 2) }}
                            </td>
                        </tr>
                        @empty
                        @endforelse

                        @foreach($incomes as $inc)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5">
                                <p class="font-bold text-slate-800">{{ $inc->title }}</p>
                                <p class="text-[10px] text-slate-400">Other Income: {{ $inc->category ?? 'General' }}</p>
                            </td>
                            <td class="py-2.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-50 text-purple-700">
                                    {{ strtoupper($inc->payment_method ?? 'CASH') }}
                                </span>
                            </td>
                            <td class="py-2.5 text-right font-bold text-emerald-600">
                                Rs. {{ number_format($inc->amount, 2) }}
                            </td>
                        </tr>
                        @endforeach

                        @if($payments->isEmpty() && $incomes->isEmpty())
                        <tr>
                            <td colspan="3" class="py-8 text-center text-slate-400 text-xs">
                                No receipts recorded on this date.
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        {{-- RIGHT COLUMN: EXPENSES & PAYROLL (OUTFLOWS) --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="bg-rose-50/80 px-6 py-4 border-b border-rose-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-rose-900 text-sm flex items-center gap-2">
                        <i class="bi bi-arrow-up-right-circle-fill text-rose-600"></i> Payments & Outflows (खर्च र भुक्तानी)
                    </h3>
                    <p class="text-[11px] text-rose-700 mt-0.5">Operating expenses, vendor payments & payroll</p>
                </div>
                <span class="text-sm font-bold text-rose-800 bg-white px-3 py-1 rounded-full border border-rose-200">
                    Rs. {{ number_format($totalOutflow, 2) }}
                </span>
            </div>

            <div class="p-4 overflow-x-auto max-h-[500px]">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="text-slate-400 font-semibold text-[10px] uppercase border-b border-slate-100">
                        <tr>
                            <th class="pb-2">Voucher / Description</th>
                            <th class="pb-2">Category</th>
                            <th class="pb-2 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($expenses as $exp)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5">
                                <p class="font-bold text-slate-800">{{ $exp->title ?? $exp->description }}</p>
                                <p class="text-[10px] text-slate-400">Paid to: {{ $exp->paid_to ?? 'Vendor/Staff' }}</p>
                            </td>
                            <td class="py-2.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                    {{ $exp->category ?? 'Expense' }}
                                </span>
                            </td>
                            <td class="py-2.5 text-right font-bold text-rose-600">
                                Rs. {{ number_format($exp->amount, 2) }}
                            </td>
                        </tr>
                        @empty
                        @endforelse

                        @foreach($teacherPayrolls as $tp)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5">
                                <p class="font-bold text-slate-800">{{ $tp->teacher->full_name ?? 'Teacher Salary' }}</p>
                                <p class="text-[10px] text-slate-400">Teacher Payroll Disbursed</p>
                            </td>
                            <td class="py-2.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700">
                                    Salary
                                </span>
                            </td>
                            <td class="py-2.5 text-right font-bold text-rose-600">
                                Rs. {{ number_format($tp->net_salary, 2) }}
                            </td>
                        </tr>
                        @endforeach

                        @foreach($staffPayrolls as $sp)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5">
                                <p class="font-bold text-slate-800">{{ $sp->user->full_name ?? 'Staff Salary' }}</p>
                                <p class="text-[10px] text-slate-400">Staff Salary Slip #{{ $sp->id }}</p>
                            </td>
                            <td class="py-2.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700">
                                    Salary
                                </span>
                            </td>
                            <td class="py-2.5 text-right font-bold text-rose-600">
                                Rs. {{ number_format($sp->net_salary, 2) }}
                            </td>
                        </tr>
                        @endforeach

                        @if($expenses->isEmpty() && $teacherPayrolls->isEmpty() && $staffPayrolls->isEmpty())
                        <tr>
                            <td colspan="3" class="py-8 text-center text-slate-400 text-xs">
                                No expenses or salary disbursements on this date.
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
