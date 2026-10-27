@extends('layouts.app')
@section('title', 'Purchase Orders')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2">
                <i class="bi bi-receipt text-indigo-500"></i> Purchase Orders
            </h1>
            <p class="text-sm text-slate-400 mt-0.5">Manage stock requests and vendor orders.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('inventory.dashboard') }}"
               class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 transition flex items-center gap-1.5">
                <i class="bi bi-arrow-left"></i> Dashboard
            </a>
            <button onclick="document.getElementById('poModal').classList.remove('hidden');document.getElementById('poModal').classList.add('flex')"
                    class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition flex items-center gap-1.5">
                <i class="bi bi-plus-lg"></i> Raise PO
            </button>
        </div>
    </div>

    @include('session-messages')

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs text-slate-400 uppercase tracking-wider font-semibold border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="px-5 py-3">PO Number</th>
                        <th class="px-4 py-3">Supplier</th>
                        <th class="px-4 py-3">Order Date</th>
                        <th class="px-4 py-3">Total Amount</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                    @forelse($orders as $order)
                    @php
                        $badge = match($order->status ?? 'draft') {
                            'draft'              => 'bg-slate-100 text-slate-600',
                            'pending'            => 'bg-amber-100 text-amber-700',
                            'approved'           => 'bg-blue-100 text-blue-700',
                            'ordered'            => 'bg-indigo-100 text-indigo-700',
                            'partially_received' => 'bg-purple-100 text-purple-700',
                            'completed'          => 'bg-emerald-100 text-emerald-700',
                            'cancelled'          => 'bg-rose-100 text-rose-700',
                            default              => 'bg-slate-100 text-slate-600',
                        };
                    @endphp
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                        <td class="px-5 py-3 font-mono font-bold text-indigo-600">#{{ $order->po_number }}</td>
                        <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-200">{{ $order->supplier->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500 text-xs">{{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('M d, Y') : '—' }}</td>
                        <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-100">Rs. {{ number_format($order->total_amount ?? 0, 2) }}</td>
                        <td class="px-4 py-3">
                            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full {{ $badge }} capitalize">
                                {{ str_replace('_', ' ', $order->status ?? 'draft') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button class="p-1.5 text-slate-400 hover:text-indigo-600 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-950/30 transition" title="View">
                                <i class="bi bi-eye text-xs"></i>
                            </button>
                            <button class="p-1.5 text-slate-400 hover:text-emerald-600 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-950/30 transition" title="Mark Received">
                                <i class="bi bi-box-arrow-in-down text-xs"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-16">
                            <i class="bi bi-receipt text-5xl text-slate-200 dark:text-slate-700 block mb-3"></i>
                            <p class="font-semibold text-slate-500 dark:text-slate-400">No Purchase Orders</p>
                            <p class="text-xs text-slate-400 mt-1">Create a purchase order to restock your inventory.</p>
                            <button onclick="document.getElementById('poModal').classList.remove('hidden');document.getElementById('poModal').classList.add('flex')"
                                    class="mt-4 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl transition inline-flex items-center gap-1.5">
                                <i class="bi bi-plus-lg"></i> Raise First PO
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(isset($orders) && $orders->hasPages())
        <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Raise PO Modal --}}
<div id="poModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9999] items-center justify-center p-4"
     onclick="if(event.target===this)closePOModal()">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-lg border border-slate-200 dark:border-slate-800 overflow-hidden">
        {{-- Header --}}
        <div class="px-6 py-4 bg-indigo-600 flex items-center justify-between">
            <h3 class="text-white font-bold text-sm flex items-center gap-2">
                <i class="bi bi-receipt"></i> Raise Purchase Order
            </h3>
            <button onclick="closePOModal()"
                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/20 hover:bg-white/30 text-white font-bold transition">
                &times;
            </button>
        </div>
        {{-- Form --}}
        <form method="POST" action="{{ route('inventory.purchase-orders.store') }}" class="p-6 space-y-4 bg-white dark:bg-slate-900">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Supplier <span class="text-rose-500">*</span></label>
                    <select name="supplier_id" required
                            class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none">
                        <option value="">— Select Supplier —</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    @if($suppliers->isEmpty())
                    <p class="text-[11px] text-amber-600 mt-1"><i class="bi bi-exclamation-triangle me-1"></i>No suppliers found. <a href="{{ route('inventory.suppliers') }}" class="underline">Add one first.</a></p>
                    @endif
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">PO Number <span class="text-rose-500">*</span></label>
                    <input type="text" name="po_number" required
                           value="PO-{{ date('Ymd') }}-{{ str_pad(rand(1,999), 3, '0', STR_PAD_LEFT) }}"
                           class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Order Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="order_date" required value="{{ date('Y-m-d') }}"
                           class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Total Amount (Rs.) <span class="text-rose-500">*</span></label>
                    <input type="number" name="total_amount" required min="0" step="0.01" placeholder="0.00"
                           class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Expected Delivery</label>
                    <input type="date" name="expected_delivery_date"
                           class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Notes / Remarks</label>
                    <textarea name="notes" rows="2" placeholder="Optional notes about this order…"
                              class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none resize-none"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closePOModal()"
                        class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 transition">
                    Cancel
                </button>
                <button type="submit"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                    <i class="bi bi-save me-1"></i> Create PO
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function closePOModal() {
    const m = document.getElementById('poModal');
    m.classList.add('hidden');
    m.classList.remove('flex');
}
document.addEventListener('keydown', e => { if(e.key === 'Escape') closePOModal(); });

@if($errors->any())
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('poModal').classList.remove('hidden');
    document.getElementById('poModal').classList.add('flex');
});
@endif
</script>
@endpush
