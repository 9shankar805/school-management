@extends('layouts.app')
@section('title', 'SaaS Billing & Invoices - Super Admin')

@section('content')
<div class="px-6 py-6 max-w-7xl mx-auto space-y-6">

    {{-- Top Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">
                    SaaS Platform
                </span>
                <span class="text-xs text-slate-400">&bull; बिलिङ तथा बिजक व्यवस्थापन</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1 flex items-center gap-2.5">
                <i class="bi bi-receipt-cutoff text-indigo-600"></i>
                SaaS Billing &amp; Invoices (विद्यालय बिलिङ प्रणाली)
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Generate and track subscription fees, hardware sales (Thermal Printers, CCTV, Computers), and service invoices for schools
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('super.billing.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-sm shadow-indigo-200 dark:shadow-none transition-all">
                <i class="bi bi-file-earmark-plus-fill"></i>
                Create New SaaS Invoice (नयाँ बिजक जारी)
            </a>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm flex items-center gap-2">
            <i class="bi bi-check-circle-fill text-emerald-600 text-base"></i>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-sm flex items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill text-rose-600 text-base"></i>
            {{ session('error') }}
        </div>
    @endif

    {{-- Metric Stat Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg">
                    <i class="bi bi-receipt"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium">Total Invoiced (कुल बिल)</p>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white font-mono">रु. {{ number_format($stats['total_billed']) }}</h3>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                    <i class="bi bi-check2-circle"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium">Collected (प्राप्त रकम)</p>
                    <h3 class="text-xl font-black text-emerald-600 dark:text-emerald-400 font-mono">रु. {{ number_format($stats['total_collected']) }}</h3>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium">Outstanding (बाँकी रकम)</p>
                    <h3 class="text-xl font-black text-rose-600 dark:text-rose-400 font-mono">रु. {{ number_format($stats['total_outstanding']) }}</h3>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium">Unpaid Bills (बाँकी बिल)</p>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white">{{ $stats['unpaid_count'] }} <span class="text-xs font-normal text-slate-400">/ {{ $stats['total_invoices'] }}</span></h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
        <form method="GET" action="{{ route('super.billing.index') }}" class="flex flex-col md:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search by Invoice #, School Name, PAN..."
                    class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-hidden focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="w-full md:w-56">
                <select name="school_id" onchange="this.form.submit()"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                    <option value="">All Schools</option>
                    @foreach($schools as $sch)
                        <option value="{{ $sch->id }}" {{ request('school_id') == $sch->id ? 'selected' : '' }}>{{ $sch->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-44">
                <select name="status" onchange="this.form.submit()"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                    <option value="">All Statuses</option>
                    <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                </select>
            </div>

            <button type="submit" class="w-full md:w-auto px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold">
                Filter
            </button>
            @if(request()->anyFilled(['search', 'school_id', 'status']))
                <a href="{{ route('super.billing.index') }}" class="px-3 py-2 rounded-xl text-slate-500 hover:text-slate-700 text-sm font-medium">
                    Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Invoices Table --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-900/60 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="py-3.5 px-4">Invoice # &amp; Date</th>
                        <th class="py-3.5 px-4">School (सेवा लिने विद्यालय)</th>
                        <th class="py-3.5 px-4">Agreement Ref</th>
                        <th class="py-3.5 px-4">Total Amount</th>
                        <th class="py-3.5 px-4">Paid / Due</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="py-3.5 px-4">
                                <a href="{{ route('super.billing.show', $inv->id) }}" class="font-mono font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                    {{ $inv->invoice_number }}
                                </a>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    {{ $inv->invoice_date->format('M d, Y') }}
                                    @if($inv->invoice_date_bs) <span class="text-slate-400">({{ $inv->invoice_date_bs }})</span> @endif
                                </div>
                                @if($inv->due_date)
                                    <div class="text-[11px] text-slate-400">Due: {{ $inv->due_date->format('M d, Y') }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 dark:text-white">
                                    {{ $inv->school_name }}
                                </div>
                                <div class="text-xs text-slate-400">
                                    @if($inv->school_pan) PAN: <span class="font-mono">{{ $inv->school_pan }}</span> @endif
                                    @if($inv->school_phone) &bull; {{ $inv->school_phone }} @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($inv->agreement)
                                    <a href="{{ route('super.agreements.show', $inv->agreement->id) }}"
                                        class="inline-flex items-center gap-1 text-xs font-mono font-semibold text-emerald-600 hover:underline">
                                        <i class="bi bi-file-earmark-check"></i>
                                        {{ $inv->agreement->agreement_number }}
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400">Direct Invoice</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-extrabold text-slate-900 dark:text-white text-base font-mono">
                                    रु. {{ number_format($inv->total_amount, 2) }}
                                </span>
                                @if($inv->tax_amount > 0)
                                    <div class="text-[11px] text-slate-400">Incl. {{ $inv->tax_percent }}% Tax</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="text-xs text-emerald-600 dark:text-emerald-400 font-bold font-mono">
                                    Paid: रु. {{ number_format($inv->paid_amount, 2) }}
                                </div>
                                <div class="text-xs text-rose-600 dark:text-rose-400 font-bold font-mono mt-0.5">
                                    Due: रु. {{ number_format($inv->due_amount, 2) }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($inv->status === 'paid')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                        <i class="bi bi-check-circle-fill text-[10px]"></i> Paid (भुक्तान भइसकेको)
                                    </span>
                                @elseif($inv->status === 'partial')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                        <i class="bi bi-pie-chart-fill text-[10px]"></i> Partial (आंशिक)
                                    </span>
                                @elseif($inv->status === 'overdue')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                                        <i class="bi bi-exclamation-circle-fill text-[10px]"></i> Overdue (म्याद नाघेको)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                        <i class="bi bi-clock-fill text-[10px]"></i> Unpaid (बाँकी)
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('super.billing.print', $inv->id) }}" target="_blank"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 font-semibold text-xs"
                                        title="Print Invoice / Bill">
                                        <i class="bi bi-printer-fill"></i> Print Bill
                                    </a>

                                    <a href="{{ route('super.billing.show', $inv->id) }}"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-slate-100 transition-colors"
                                        title="View &amp; Record Payment">
                                        <i class="bi bi-eye-fill text-base"></i>
                                    </a>

                                    <a href="{{ route('super.billing.edit', $inv->id) }}"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-slate-100 transition-colors"
                                        title="Edit Invoice">
                                        <i class="bi bi-pencil-square text-base"></i>
                                    </a>

                                    <form action="{{ route('super.billing.destroy', $inv->id) }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this invoice?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Delete">
                                            <i class="bi bi-trash text-base"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="bi bi-receipt text-4xl block mb-2 text-slate-300 dark:text-slate-600"></i>
                                No invoices found. Click "Create New SaaS Invoice" to bill a school.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-700">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

