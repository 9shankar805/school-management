@extends('layouts.app')
@section('title', 'Other Income')

@section('content')
<div class="px-6 py-5 max-w-7xl mx-auto">
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2 tracking-tight">
                <i class="bi bi-arrow-down-circle text-emerald-500"></i> Other Income
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
                            <span class="text-slate-700 dark:text-slate-300 font-medium">Income</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
        <div>
            <div class="bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 px-4 py-2 rounded-xl text-sm font-semibold border border-emerald-100 dark:border-emerald-900">
                This Month: Rs. {{ number_format($thisMonthTotal, 2) }}
            </div>
        </div>
    </div>

    @if(session('status'))
    <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 flex items-center gap-2">
        <i class="bi bi-check-circle text-emerald-600"></i>
        <span class="text-sm text-emerald-800 dark:text-emerald-200">{{ session('status') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Add form --}}
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/60">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="bi bi-plus-circle text-emerald-500"></i> Record Income
                    </h3>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ route('finance.income.store') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Title <span class="text-red-500">*</span></label>
                            <input type="text" name="title" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-400 focus:outline-none" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Category <span class="text-red-500">*</span></label>
                            <input type="text" name="category" list="incomeCats" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-400 focus:outline-none" required placeholder="Donations, Grants, Events…">
                            <datalist id="incomeCats">@foreach($categories as $c)<option value="{{ $c }}">@endforeach</datalist>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Amount (NPR) <span class="text-red-500">*</span></label>
                            <input type="number" name="amount" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-400 focus:outline-none" step="0.01" min="0.01" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Date <span class="text-red-500">*</span></label>
                            <input type="date" name="income_date" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-400 focus:outline-none" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Payment Method</label>
                            <select name="payment_method" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                                <option value="cash">Cash</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Reference</label>
                            <input type="text" name="reference" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Notes</label>
                            <textarea name="notes" rows="2" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-400 focus:outline-none"></textarea>
                        </div>
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition">
                            <i class="bi bi-plus-circle"></i> Record Income
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            {{-- Filter --}}
            <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-sm p-4">
                <form method="GET" action="{{ route('finance.income') }}" class="flex flex-wrap items-end gap-3">
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Search</label>
                        <input type="text" name="search" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-900/50" placeholder="Title..." value="{{ request('search') }}">
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Category</label>
                        <select name="category" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-900/50">
                            <option value="">All Categories</option>
                            @foreach($categories as $c)<option value="{{ $c }}" @selected(request('category')===$c)>{{ $c }}</option>@endforeach
                        </select>
                    </div>
                    <div class="w-32">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">From</label>
                        <input type="date" name="date_from" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-900/50" value="{{ request('date_from') }}">
                    </div>
                    <div class="w-32">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">To</label>
                        <input type="date" name="date_to" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-900/50" value="{{ request('date_to') }}">
                    </div>
                    <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition border border-slate-200">
                        <i class="bi bi-search"></i>
                    </button>
                    @if(request()->anyFilled(['search','category','date_from','date_to']))
                    <a href="{{ route('finance.income') }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-500 bg-slate-50 hover:bg-slate-100 transition border border-slate-200">
                        Clear
                    </a>
                    @endif
                </form>
            </div>

            <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Title & Category</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Method</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse($records as $rec)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400 whitespace-nowrap">{{ $rec->income_date->format('d M Y') }}</td>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ $rec->title }}</div>
                                    <span class="inline-flex items-center px-2 py-0.5 mt-1 rounded text-[10px] font-medium bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                        {{ $rec->category }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">Rs. {{ number_format($rec->amount,2) }}</td>
                                <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">
                                    {{ ucfirst($rec->payment_method) }}
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <form method="POST" action="{{ route('finance.income.destroy', $rec->id) }}" class="inline" onsubmit="return confirm('Delete this income record?')">@csrf @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium text-red-600 hover:bg-red-50 transition">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">
                                    <i class="bi bi-inbox text-4xl mb-2 block text-slate-300 dark:text-slate-600"></i>
                                    <span class="text-sm">No income records found.</span>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700">
                    {{ $records->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
