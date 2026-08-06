@extends('layouts.app')
@section('title', 'Inventory Dashboard')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-7xl mx-auto">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-10 h-10 rounded-2xl bg-indigo-600/10 text-indigo-600 flex items-center justify-center font-bold text-xl shadow-xs">
                    <i class="bi bi-boxes"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Inventory & Assets</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Overview of consumables, fixed assets, and suppliers</p>
                </div>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('inventory.items') }}" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold transition flex items-center gap-2">
                <i class="bi bi-box-seam"></i> Consumables
            </a>
            <a href="{{ route('inventory.assets') }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md transition flex items-center gap-2">
                <i class="bi bi-pc-display"></i> Fixed Assets
            </a>
        </div>
    </div>

    @include('session-messages')

    {{-- Quick Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-4">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center">
                    <i class="bi bi-pc-display text-lg"></i>
                </span>
                <div>
                    <div class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $totalAssets ?? 0 }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">Total Fixed Assets</div>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-4">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center">
                    <i class="bi bi-box-seam text-lg"></i>
                </span>
                <div>
                    <div class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $totalItems ?? 0 }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">Consumable Items</div>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-4">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center">
                    <i class="bi bi-exclamation-triangle text-lg"></i>
                </span>
                <div>
                    <div class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $lowStockItems ?? 0 }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">Low Stock Alerts</div>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-4">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                    <i class="bi bi-check-circle text-lg"></i>
                </span>
                <div>
                    <div class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $availableAssets ?? 0 }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">Available Assets</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Area --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Low Stock Items Table --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                <span class="w-2 h-5 bg-amber-600 rounded-full"></span>
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Low Stock Alerts</h2>
            </div>
            @if(isset($lowStockList) && $lowStockList->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-semibold bg-slate-50/50 dark:bg-slate-800/20">
                            <tr>
                                <th class="py-3 px-4">Item Name</th>
                                <th class="py-3 px-4">Category</th>
                                <th class="py-3 px-4 text-right">Current Stock</th>
                                <th class="py-3 px-4 text-right">Threshold</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lowStockList as $item)
                            <tr class="border-b border-slate-50 dark:border-slate-800/50 hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-slate-800 dark:text-slate-100">{{ $item->name }}</div>
                                    <div class="text-xs text-slate-400">SKU: {{ $item->sku }}</div>
                                </td>
                                <td class="py-3 px-4">{{ $item->category->name ?? 'N/A' }}</td>
                                <td class="py-3 px-4 text-right">
                                    <span class="px-2 py-1 rounded-lg {{ $item->quantity_in_stock == 0 ? 'bg-rose-100 dark:bg-rose-900/30 text-rose-600' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-600' }} text-xs font-semibold">
                                        {{ $item->quantity_in_stock }} {{ $item->unit }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right text-slate-400">{{ $item->min_stock_level }} {{ $item->unit }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <div class="text-slate-300"><i class="bi bi-check-circle text-4xl"></i></div>
                    <h5 class="mt-3 text-slate-600 font-semibold">All Stock Levels Normal</h5>
                    <p class="text-sm text-slate-400">There are no items currently running low.</p>
                </div>
            @endif
        </div>

        {{-- Quick Links --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                <span class="w-2 h-5 bg-indigo-600 rounded-full"></span>
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Quick Actions</h2>
            </div>
            <div class="flex flex-col gap-3">
                <a href="{{ route('inventory.items.create') }}" class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <span class="w-10 h-10 rounded-lg bg-white dark:bg-slate-700 flex items-center justify-center shadow-sm">
                        <i class="bi bi-plus-circle text-blue-600"></i>
                    </span>
                    <div>
                        <div class="font-semibold text-slate-800 dark:text-slate-100 text-sm">Add Consumable</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">New inventory item</div>
                    </div>
                </a>
                <a href="{{ route('inventory.assets.create') }}" class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <span class="w-10 h-10 rounded-lg bg-white dark:bg-slate-700 flex items-center justify-center shadow-sm">
                        <i class="bi bi-pc-display text-indigo-600"></i>
                    </span>
                    <div>
                        <div class="font-semibold text-slate-800 dark:text-slate-100 text-sm">Add Fixed Asset</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Register equipment</div>
                    </div>
                </a>
                <a href="{{ route('inventory.purchase-orders.create') }}" class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <span class="w-10 h-10 rounded-lg bg-white dark:bg-slate-700 flex items-center justify-center shadow-sm">
                        <i class="bi bi-receipt text-emerald-600"></i>
                    </span>
                    <div>
                        <div class="font-semibold text-slate-800 dark:text-slate-100 text-sm">Purchase Order</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Create new PO</div>
                    </div>
                </a>
                <a href="{{ route('inventory.suppliers') }}" class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <span class="w-10 h-10 rounded-lg bg-white dark:bg-slate-700 flex items-center justify-center shadow-sm">
                        <i class="bi bi-truck text-purple-600"></i>
                    </span>
                    <div>
                        <div class="font-semibold text-slate-800 dark:text-slate-100 text-sm">Suppliers</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Manage vendors</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
