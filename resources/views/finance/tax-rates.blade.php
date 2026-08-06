@extends('layouts.app')
@section('title', 'Tax Rate Management')

@section('content')
<div class="px-6 py-5 max-w-7xl mx-auto">
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2 tracking-tight">
                <i class="bi bi-percent text-sky-500"></i> Tax & Deduction Rates
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
                            <span class="text-slate-700 dark:text-slate-300 font-medium">Tax Rates</span>
                        </div>
                    </li>
                </ol>
            </nav>
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
                        <i class="bi bi-plus-circle text-sky-500"></i> Add Rate
                    </h3>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ route('finance.tax.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-sky-400 focus:outline-none" required placeholder="e.g. Income Tax 10%">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Type</label>
                            <select name="type" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-sky-400 focus:outline-none">
                                <option value="income_tax">Income Tax</option>
                                <option value="provident_fund">Provident Fund</option>
                                <option value="vat">VAT</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Rate (%) <span class="text-red-500">*</span></label>
                            <input type="number" name="rate" step="0.0001" min="0" max="100" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-sky-400 focus:outline-none" required>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Min Salary</label>
                                <input type="number" name="min_salary" step="0.01" min="0" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-sky-400 focus:outline-none" value="0">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Max Salary</label>
                                <input type="number" name="max_salary" step="0.01" min="0" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-sky-400 focus:outline-none" placeholder="Unlimited">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Description</label>
                            <textarea name="description" rows="2" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-sky-400 focus:outline-none"></textarea>
                        </div>
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-sky-600 hover:bg-sky-700 transition">
                            <i class="bi bi-plus-circle"></i> Add Rate
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- List --}}
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Name</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Type</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Rate %</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Salary Range</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Active</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse($rates as $rate)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                                <td class="px-4 py-3 text-sm font-semibold text-slate-900 dark:text-white">{{ $rate->name }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300">
                                        {{ str_replace('_',' ',ucfirst($rate->type)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm font-bold text-sky-600 dark:text-sky-400 text-right">{{ $rate->rate }}%</td>
                                <td class="px-4 py-3 text-[10px] text-slate-500 dark:text-slate-400">
                                    Rs. {{ number_format($rate->min_salary,2) }} <br>to<br> {{ $rate->max_salary ? 'Rs. '.number_format($rate->max_salary,2) : '∞' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $rate->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' }}">
                                        {{ $rate->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <form method="POST" action="{{ route('finance.tax.destroy', $rate->id) }}" class="inline" onsubmit="return confirm('Delete this rate?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium text-red-600 hover:bg-red-50 transition">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">
                                    <i class="bi bi-percent text-4xl mb-2 block text-slate-300 dark:text-slate-600"></i>
                                    <span class="text-sm">No tax rates configured.</span>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
