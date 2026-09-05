@extends('layouts.app')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-5xl mx-auto">

    {{-- Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-10 h-10 rounded-2xl bg-indigo-600/10 text-indigo-600 flex items-center justify-center font-bold text-xl shadow-xs">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Inventory & Asset Reports</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Generate and export comprehensive inventory, asset, supplier, and transaction reports</p>
                </div>
            </div>
        </div>
        <a href="{{ route('inventory.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 text-xs font-semibold transition self-start md:self-auto">
            Back to Inventory
        </a>
    </div>

    @include('session-messages')

    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6 md:p-8">
        <form action="{{ route('inventory.reports.export') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Report Type --}}
                <div>
                    <label for="report_type" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        Report Type <span class="text-rose-500">*</span>
                    </label>
                    <select name="report_type" id="report_type" required
                            class="w-full text-xs px-3.5 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                        <option value="assets">Fixed Assets Directory</option>
                        <option value="stock">Inventory Stock Items</option>
                        <option value="low_stock">Low Stock & Out of Stock Items</option>
                        <option value="suppliers">Suppliers List & Aggregates</option>
                        <option value="transactions">Stock Audit Transactions</option>
                        <option value="purchase_orders">Purchase Orders Summary</option>
                    </select>
                </div>

                {{-- Export Format --}}
                <div>
                    <label for="format" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        Export Format <span class="text-rose-500">*</span>
                    </label>
                    <select name="format" id="format" required
                            class="w-full text-xs px-3.5 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                        <option value="pdf">PDF Document (.pdf)</option>
                        <option value="excel">Excel Spreadsheet (.xlsx)</option>
                    </select>
                </div>

                {{-- Category Filter --}}
                <div>
                    <label for="category_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        Filter by Category (Optional)
                    </label>
                    <select name="category_id" id="category_id"
                            class="w-full text-xs px-3.5 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }} ({{ ucfirst($cat->type) }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Date Range Filters --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="date_from" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Date From</label>
                        <input type="date" name="date_from" id="date_from"
                               class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none">
                    </div>
                    <div>
                        <label for="date_to" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Date To</label>
                        <input type="date" name="date_to" id="date_to"
                               class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none">
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md transition flex items-center gap-2">
                    <i class="bi bi-download"></i> Generate & Download Report
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
