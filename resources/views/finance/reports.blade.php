@extends('layouts.app')
@section('title', 'Finance Reports')

@section('content')
<div class="px-6 py-5 max-w-7xl mx-auto">
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2 tracking-tight">
                <i class="bi bi-file-earmark-spreadsheet text-indigo-500"></i> Finance Reports
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
                            <span class="text-slate-700 dark:text-slate-300 font-medium">Reports</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        {{-- Generate Report --}}
        <div>
            <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-sm overflow-hidden relative">
                <div class="absolute top-0 right-0 p-8 opacity-5">
                    <i class="bi bi-file-earmark-bar-graph text-9xl"></i>
                </div>
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700/60 relative z-10">
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        Generate Report
                    </h3>
                </div>
                <div class="p-6 relative z-10">
                    <form action="{{ route('finance.reports.export') }}" method="GET" class="space-y-5">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Report Type <span class="text-red-500">*</span></label>
                            <select name="report_type" class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-3 text-sm bg-slate-50 dark:bg-slate-900/50 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-400 focus:bg-white focus:outline-none transition-all" required>
                                <option value="fee_collection">Fee Collection</option>
                                <option value="outstanding">Outstanding / Overdue</option>
                                <option value="expenses">Expenses</option>
                                <option value="income">Other Income</option>
                                <option value="ledger">Summary Ledger</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Date From</label>
                                <input type="date" name="date_from" class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-3 text-sm bg-slate-50 dark:bg-slate-900/50 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-400 focus:bg-white focus:outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Date To</label>
                                <input type="date" name="date_to" class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-4 py-3 text-sm bg-slate-50 dark:bg-slate-900/50 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-400 focus:bg-white focus:outline-none transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-2">Format</label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-indigo-400 transition-colors flex-1">
                                    <input type="radio" name="format" value="pdf" class="w-4 h-4 text-indigo-600 border-slate-300 focus:ring-indigo-500" checked>
                                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300 flex items-center gap-1.5"><i class="bi bi-file-earmark-pdf text-red-500"></i> PDF</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-indigo-400 transition-colors flex-1">
                                    <input type="radio" name="format" value="excel" class="w-4 h-4 text-indigo-600 border-slate-300 focus:ring-indigo-500">
                                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300 flex items-center gap-1.5"><i class="bi bi-file-earmark-spreadsheet text-emerald-500"></i> Excel</span>
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition shadow-lg shadow-indigo-600/20">
                            <i class="bi bi-download"></i> Generate & Download
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Quick Reports --}}
        <div>
            <h6 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4">Quick Reports</h6>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="{{ route('finance.reports.export', ['report_type'=>'outstanding','format'=>'pdf']) }}" class="flex items-center gap-3 p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-red-400 hover:shadow-md transition group">
                    <div class="w-10 h-10 rounded-lg bg-red-50 dark:bg-red-900/20 flex items-center justify-center text-red-500 group-hover:scale-110 transition-transform">
                        <i class="bi bi-file-earmark-pdf text-lg"></i>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-slate-800 dark:text-slate-200">Outstanding Fees</div>
                        <div class="text-xs text-slate-500">Download as PDF</div>
                    </div>
                </a>
                
                <a href="{{ route('finance.reports.export', ['report_type'=>'fee_collection','format'=>'excel']) }}" class="flex items-center gap-3 p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-emerald-400 hover:shadow-md transition group">
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-500 group-hover:scale-110 transition-transform">
                        <i class="bi bi-file-earmark-spreadsheet text-lg"></i>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-slate-800 dark:text-slate-200">Fee Collections</div>
                        <div class="text-xs text-slate-500">Download as Excel</div>
                    </div>
                </a>
                
                <a href="{{ route('finance.reports.export', ['report_type'=>'expenses','format'=>'excel']) }}" class="flex items-center gap-3 p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-amber-400 hover:shadow-md transition group">
                    <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-500 group-hover:scale-110 transition-transform">
                        <i class="bi bi-file-earmark-spreadsheet text-lg"></i>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-slate-800 dark:text-slate-200">Total Expenses</div>
                        <div class="text-xs text-slate-500">Download as Excel</div>
                    </div>
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
