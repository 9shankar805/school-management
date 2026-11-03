@extends('layouts.app')
@section('title', 'Services & Hardware Catalog - Super Admin')

@section('content')
<div class="px-6 py-6 max-w-7xl mx-auto space-y-6">

    {{-- Top Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">
                    SaaS Platform
                </span>
                <span class="text-xs text-slate-400">&bull; Hardware &amp; Services</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1 flex items-center gap-2.5">
                <i class="bi bi-box-seam text-indigo-600"></i>
                Services &amp; Hardware Catalog (प्रणाली तथा सेवा सामग्रीहरू)
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Manage hardware, software licenses, setup packages, and add-on services provided to partner schools (Thermal Printers, CCTV, PC Setups, etc.)
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" onclick="openAddModal()"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-sm shadow-indigo-200 dark:shadow-none transition-all">
                <i class="bi bi-plus-lg"></i>
                Add New Service / Hardware
            </button>
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
                    <i class="bi bi-boxes"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium">Total Offerings</p>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white">{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg">
                    <i class="bi bi-printer-fill"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium">Hardware &amp; Devices</p>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white">{{ $stats['hardware'] }}</h3>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg">
                    <i class="bi bi-laptop-fill"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium">Software &amp; Digital</p>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white">{{ $stats['software'] }}</h3>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                    <i class="bi bi-tools"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium">Services &amp; Setup</p>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white">{{ $stats['services'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
        <form method="GET" action="{{ route('super.services.index') }}" class="flex flex-col md:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search by service name, Nepali title, code..."
                    class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-hidden focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="w-full md:w-52">
                <select name="category" onchange="this.form.submit()"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                    <option value="">All Categories</option>
                    @foreach($categories as $key => $lbl)
                        <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-40">
                <select name="status" onchange="this.form.submit()"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <button type="submit" class="w-full md:w-auto px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-sm font-semibold">
                Filter
            </button>
            @if(request()->anyFilled(['search', 'category', 'status']))
                <a href="{{ route('super.services.index') }}" class="px-3 py-2 rounded-xl text-slate-500 hover:text-slate-700 text-sm font-medium">
                    Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Services Table --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-900/60 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="py-3.5 px-4">Offering / Service</th>
                        <th class="py-3.5 px-4">Category</th>
                        <th class="py-3.5 px-4">Default Rate (रु.)</th>
                        <th class="py-3.5 px-4">Billing Basis</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($services as $item)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 text-base
                                        @if($item->category === 'hardware') bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300
                                        @elseif($item->category === 'software') bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300
                                        @elseif($item->category === 'setup') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300
                                        @else bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300
                                        @endif">
                                        @if(str_contains(strtolower($item->name), 'printer'))
                                            <i class="bi bi-printer-fill"></i>
                                        @elseif(str_contains(strtolower($item->name), 'cctv') || str_contains(strtolower($item->name), 'camera'))
                                            <i class="bi bi-camera-video-fill"></i>
                                        @elseif(str_contains(strtolower($item->name), 'computer') || str_contains(strtolower($item->name), 'pc'))
                                            <i class="bi bi-pc-display"></i>
                                        @elseif(str_contains(strtolower($item->name), 'biometric') || str_contains(strtolower($item->name), 'rfid'))
                                            <i class="bi bi-fingerprint"></i>
                                        @elseif(str_contains(strtolower($item->name), 'sms'))
                                            <i class="bi bi-chat-dots-fill"></i>
                                        @elseif(str_contains(strtolower($item->name), 'setup') || str_contains(strtolower($item->name), 'स्थापना'))
                                            <i class="bi bi-gear-wide-connected"></i>
                                        @else
                                            <i class="bi bi-box-seam"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                            {{ $item->name }}
                                            @if($item->is_featured)
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">Popular</span>
                                            @endif
                                        </div>
                                        @if($item->name_np)
                                            <p class="text-xs text-indigo-600 dark:text-indigo-400 font-medium">{{ $item->name_np }}</p>
                                        @endif
                                        <p class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $item->code }}</p>
                                        @if($item->description)
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-1 max-w-md">{{ $item->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                                    @if($item->category === 'hardware') bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-900/20 dark:border-amber-800 dark:text-amber-300
                                    @elseif($item->category === 'software') bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-300
                                    @elseif($item->category === 'setup') bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-300
                                    @else bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-900/20 dark:border-purple-800 dark:text-purple-300
                                    @endif">
                                    {{ $categories[$item->category] ?? ucfirst($item->category) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-extrabold text-slate-900 dark:text-white text-base">
                                    रु. {{ number_format($item->default_price, 2) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="text-xs text-slate-600 dark:text-slate-300 font-medium">
                                    {{ $billingBases[$item->billing_basis] ?? $item->billing_basis }}
                                </span>
                                @if($item->unit_name)
                                    <div class="text-[11px] text-slate-400">({{ $item->unit_name }})</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($item->is_active)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" onclick="openEditModal({{ json_encode($item) }})"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                                        title="Edit Service">
                                        <i class="bi bi-pencil-square text-base"></i>
                                    </button>
                                    <form action="{{ route('super.services.destroy', $item->id) }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this service item?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30 transition-colors" title="Delete">
                                            <i class="bi bi-trash text-base"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <i class="bi bi-box-seam text-4xl block mb-2 text-slate-300 dark:text-slate-600"></i>
                                No services found. Click "Add New Service / Hardware" above to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($services->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-700">
                {{ $services->links() }}
            </div>
        @endif
    </div>
</div>

{{-- ── ADD SERVICE MODAL ────────────────────────────────────────── --}}
<div id="addModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 overflow-y-auto max-h-[90vh]">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700">
            <h3 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i class="bi bi-plus-circle-fill text-indigo-600"></i>
                Add Offering / Hardware / Service
            </h3>
            <button type="button" onclick="closeAddModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>

        <form action="{{ route('super.services.store') }}" method="POST" class="space-y-4 mt-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Service / Hardware Name (English) *</label>
                    <input type="text" name="name" required placeholder="e.g. 80mm Thermal Receipt Printer"
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Name in Nepali (नेपाली नाम)</label>
                    <input type="text" name="name_np" placeholder="जस्तै: थर्मल बिल प्रिन्टर"
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Category *</label>
                    <select name="category" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white">
                        <option value="hardware">Hardware / Device</option>
                        <option value="software">Software / Platform</option>
                        <option value="service">Service / Technical</option>
                        <option value="setup">Setup &amp; Installation</option>
                        <option value="package">Package / Bundle</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Default Rate (रु.) *</label>
                    <input type="number" step="0.01" name="default_price" required placeholder="12500"
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Billing Basis *</label>
                    <select name="billing_basis" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white">
                        <option value="one_time">एकपटक (One-Time)</option>
                        <option value="annual">प्रतिवर्ष (Annual)</option>
                        <option value="monthly">प्रतिमहिना (Monthly)</option>
                        <option value="per_unit">प्रति थान/विद्यार्थी (Per Unit)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Item Code / SKU (Optional)</label>
                    <input type="text" name="code" placeholder="Leave empty for auto-generated code"
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Unit Label (जस्तै: थान, सेट, वर्ष)</label>
                    <input type="text" name="unit_name" value="थान (Unit)"
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Description / विवरण</label>
                <textarea name="description" rows="2" placeholder="Brief explanation of the device/service..."
                    class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white"></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Technical Specifications (Optional)</label>
                <textarea name="specifications" rows="2" placeholder="Model, dimensions, interface, warranty, accessories..."
                    class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white"></textarea>
            </div>

            <div class="flex items-center gap-6 pt-2">
                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 text-indigo-600 rounded">
                    Active (Available for billing &amp; agreements)
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="is_featured" value="1" class="w-4 h-4 text-indigo-600 rounded">
                    Mark as Popular / Featured
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                <button type="button" onclick="closeAddModal()" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 text-sm font-semibold">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold">Save Offering</button>
            </div>
        </form>
    </div>
</div>

{{-- ── EDIT SERVICE MODAL ───────────────────────────────────────── --}}
<div id="editModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 overflow-y-auto max-h-[90vh]">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700">
            <h3 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i class="bi bi-pencil-fill text-indigo-600"></i>
                Edit Service / Hardware
            </h3>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>

        <form id="editForm" method="POST" class="space-y-4 mt-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Service / Hardware Name *</label>
                    <input type="text" id="edit_name" name="name" required
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Name in Nepali (नेपाली नाम)</label>
                    <input type="text" id="edit_name_np" name="name_np"
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Category *</label>
                    <select id="edit_category" name="category" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white">
                        <option value="hardware">Hardware / Device</option>
                        <option value="software">Software / Platform</option>
                        <option value="service">Service / Technical</option>
                        <option value="setup">Setup &amp; Installation</option>
                        <option value="package">Package / Bundle</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Default Rate (रु.) *</label>
                    <input type="number" step="0.01" id="edit_default_price" name="default_price" required
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Billing Basis *</label>
                    <select id="edit_billing_basis" name="billing_basis" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white">
                        <option value="one_time">एकपटक (One-Time)</option>
                        <option value="annual">प्रतिवर्ष (Annual)</option>
                        <option value="monthly">प्रतिमहिना (Monthly)</option>
                        <option value="per_unit">प्रति थान/विद्यार्थी (Per Unit)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Code / SKU *</label>
                    <input type="text" id="edit_code" name="code" required
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Unit Label</label>
                    <input type="text" id="edit_unit_name" name="unit_name"
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Description</label>
                <textarea id="edit_description" name="description" rows="2"
                    class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white"></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Technical Specifications</label>
                <textarea id="edit_specifications" name="specifications" rows="2"
                    class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white"></textarea>
            </div>

            <div class="flex items-center gap-6 pt-2">
                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-300">
                    <input type="checkbox" id="edit_is_active" name="is_active" value="1" class="w-4 h-4 text-indigo-600 rounded">
                    Active
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-300">
                    <input type="checkbox" id="edit_is_featured" name="is_featured" value="1" class="w-4 h-4 text-indigo-600 rounded">
                    Popular / Featured
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 text-sm font-semibold">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold">Update Offering</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('addModal').classList.remove('hidden');
}
function closeAddModal() {
    document.getElementById('addModal').classList.add('hidden');
}
function openEditModal(item) {
    document.getElementById('editForm').action = '/super/services/' + item.id;
    document.getElementById('edit_name').value = item.name || '';
    document.getElementById('edit_name_np').value = item.name_np || '';
    document.getElementById('edit_code').value = item.code || '';
    document.getElementById('edit_category').value = item.category || 'hardware';
    document.getElementById('edit_default_price').value = item.default_price || 0;
    document.getElementById('edit_billing_basis').value = item.billing_basis || 'one_time';
    document.getElementById('edit_unit_name').value = item.unit_name || '';
    document.getElementById('edit_description').value = item.description || '';
    document.getElementById('edit_specifications').value = item.specifications || '';
    document.getElementById('edit_is_active').checked = !!item.is_active;
    document.getElementById('edit_is_featured').checked = !!item.is_featured;
    document.getElementById('editModal').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>
@endsection

