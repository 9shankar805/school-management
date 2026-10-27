@extends('layouts.app')
@section('title', 'Suppliers')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2">
                <i class="bi bi-truck text-indigo-500"></i> Suppliers
            </h1>
            <p class="text-sm text-slate-400 mt-0.5">Manage vendor contact details and catalogs.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('inventory.dashboard') }}"
               class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 transition flex items-center gap-1.5">
                <i class="bi bi-arrow-left"></i> Dashboard
            </a>
            <button onclick="document.getElementById('supplierModal').classList.remove('hidden');document.getElementById('supplierModal').classList.add('flex')"
                    class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition flex items-center gap-1.5">
                <i class="bi bi-plus-lg"></i> Add Supplier
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
                        <th class="px-5 py-3">Supplier Name</th>
                        <th class="px-4 py-3">Contact Person</th>
                        <th class="px-4 py-3">Email / Phone</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                    @forelse($suppliers as $supplier)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 font-bold flex items-center justify-center text-sm flex-shrink-0">
                                    {{ strtoupper(substr($supplier->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $supplier->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $supplier->address ?? 'No address on file' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-300">{{ $supplier->contact_person ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <p class="text-xs text-slate-600 dark:text-slate-400"><i class="bi bi-envelope me-1"></i>{{ $supplier->email ?? 'N/A' }}</p>
                            <p class="text-xs text-slate-600 dark:text-slate-400"><i class="bi bi-telephone me-1"></i>{{ $supplier->phone ?? 'N/A' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full {{ ($supplier->status ?? 'active') === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                {{ ($supplier->status ?? 'active') === 'active' ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button onclick='openEditSupplier(@json($supplier))'
                                    class="p-1.5 text-slate-400 hover:text-indigo-600 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-950/30 transition" title="Edit">
                                <i class="bi bi-pencil text-xs"></i>
                            </button>
                            <form method="POST" action="{{ route('inventory.suppliers.destroy', $supplier->id) }}" class="inline"
                                  onsubmit="return confirm('Delete {{ addslashes($supplier->name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/30 transition" title="Delete">
                                    <i class="bi bi-trash text-xs"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-16">
                            <i class="bi bi-truck text-5xl text-slate-200 dark:text-slate-700 block mb-3"></i>
                            <p class="font-semibold text-slate-500 dark:text-slate-400">No Suppliers Found</p>
                            <p class="text-xs text-slate-400 mt-1">Add a supplier to start issuing purchase orders.</p>
                            <button onclick="document.getElementById('supplierModal').classList.remove('hidden');document.getElementById('supplierModal').classList.add('flex')"
                                    class="mt-4 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl transition inline-flex items-center gap-1.5">
                                <i class="bi bi-plus-lg"></i> Add First Supplier
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(isset($suppliers) && $suppliers->hasPages())
        <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800">
            {{ $suppliers->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Add / Edit Supplier Modal --}}
<div id="supplierModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9999] items-center justify-center p-4"
     onclick="if(event.target===this)closeSupplierModal()">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-lg border border-slate-200 dark:border-slate-800 overflow-hidden">
        {{-- Header --}}
        <div class="px-6 py-4 bg-indigo-600 flex items-center justify-between">
            <h3 id="supplierModalTitle" class="text-white font-bold text-sm flex items-center gap-2">
                <i class="bi bi-truck"></i> Add New Supplier
            </h3>
            <button onclick="closeSupplierModal()"
                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/20 hover:bg-white/30 text-white font-bold transition">
                &times;
            </button>
        </div>
        {{-- Form --}}
        <form id="supplierForm" method="POST" action="{{ route('inventory.suppliers.store') }}" class="p-6 space-y-4 bg-white dark:bg-slate-900">
            @csrf
            <div id="methodField"></div>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Supplier / Company Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" id="sName" required placeholder="e.g., Office Depot Nepal"
                           class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Contact Person</label>
                    <input type="text" name="contact_person" id="sContact" placeholder="e.g., Ram Sharma"
                           class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Phone Number</label>
                    <input type="text" name="phone" id="sPhone" placeholder="98XXXXXXXX"
                           class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Email Address</label>
                    <input type="email" name="email" id="sEmail" placeholder="supplier@example.com"
                           class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Physical Address</label>
                    <textarea name="address" id="sAddress" rows="2" placeholder="e.g., New Road, Kathmandu"
                              class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none resize-none"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeSupplierModal()"
                        class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 transition">
                    Cancel
                </button>
                <button type="submit"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                    <i class="bi bi-save me-1"></i> Save Supplier
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function closeSupplierModal() {
    const m = document.getElementById('supplierModal');
    m.classList.add('hidden');
    m.classList.remove('flex');
    // Reset form
    document.getElementById('supplierForm').reset();
    document.getElementById('methodField').innerHTML = '';
    document.getElementById('supplierForm').action = '{{ route("inventory.suppliers.store") }}';
    document.getElementById('supplierModalTitle').innerHTML = '<i class="bi bi-truck"></i> Add New Supplier';
}

function openEditSupplier(supplier) {
    document.getElementById('sName').value    = supplier.name || '';
    document.getElementById('sContact').value = supplier.contact_person || '';
    document.getElementById('sPhone').value   = supplier.phone || '';
    document.getElementById('sEmail').value   = supplier.email || '';
    document.getElementById('sAddress').value = supplier.address || '';
    document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('supplierForm').action = '/inventory/suppliers/' + supplier.id;
    document.getElementById('supplierModalTitle').innerHTML = '<i class="bi bi-pencil"></i> Edit Supplier';
    const m = document.getElementById('supplierModal');
    m.classList.remove('hidden');
    m.classList.add('flex');
}

document.addEventListener('keydown', e => { if(e.key === 'Escape') closeSupplierModal(); });

// Auto-open modal if validation failed (old input present)
@if($errors->any())
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('supplierModal').classList.remove('hidden');
    document.getElementById('supplierModal').classList.add('flex');
});
@endif
</script>
@endpush
@endsection
