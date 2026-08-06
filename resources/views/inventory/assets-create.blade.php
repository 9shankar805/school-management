@extends('layouts.app')
@section('title', 'Register Asset')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-3xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="bi bi-box-seam text-blue-600"></i> Register Asset
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Add a new asset to the school inventory</p>
        </div>
        <a href="{{ route('inventory.assets') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-blue-400 transition">
            <i class="bi bi-arrow-left"></i> Back to Assets
        </a>
    </div>

    @if(session('error'))
    <div class="mb-4 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-sm text-red-700 dark:text-red-300">
        <i class="bi bi-exclamation-circle mr-1"></i> {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-4 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-sm text-red-700 dark:text-red-300">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('inventory.assets.store') }}" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
        @csrf

        {{-- Basic Info --}}
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                <i class="bi bi-info-circle text-blue-500"></i> Basic Information
            </h3>
        </div>
        <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-2 gap-4 border-b border-slate-100 dark:border-slate-700">

            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Asset Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    placeholder="e.g. Dell Laptop, Projector, Chemistry Lab Table"
                    class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Asset Tag / Code <span class="text-red-500">*</span></label>
                <input type="text" name="asset_tag" value="{{ old('asset_tag') }}" required
                    placeholder="e.g. ASSET-001"
                    class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400">
                <p class="text-xs text-slate-400 mt-1">Unique identifier — printed on asset label</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Category <span class="text-red-500">*</span></label>
                @if($categories->isEmpty())
                <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 text-xs text-amber-700 dark:text-amber-300">
                    <i class="bi bi-exclamation-triangle mr-1"></i>
                    No asset categories yet. Go to
                    <a href="{{ route('inventory.dashboard') }}" class="underline font-semibold">Inventory</a>
                    and create a category first.
                </div>
                @else
                <select name="category_id" required
                    class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">— Select Category —</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
                @endif
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Brand</label>
                <input type="text" name="brand" value="{{ old('brand') }}"
                    placeholder="e.g. Dell, HP, Samsung"
                    class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Model Number</label>
                <input type="text" name="model_number" value="{{ old('model_number') }}"
                    placeholder="e.g. Latitude 5420"
                    class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Serial Number</label>
                <input type="text" name="serial_number" value="{{ old('serial_number') }}"
                    placeholder="Manufacturer serial no."
                    class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Status <span class="text-red-500">*</span></label>
                <select name="status" required
                    class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="available"   @selected(old('status','available')=='available')>Available</option>
                    <option value="assigned"    @selected(old('status')=='assigned')>Assigned</option>
                    <option value="maintenance" @selected(old('status')=='maintenance')>Under Maintenance</option>
                    <option value="disposed"    @selected(old('status')=='disposed')>Disposed</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Condition</label>
                <select name="condition"
                    class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">— Select —</option>
                    <option value="new"       @selected(old('condition')=='new')>New</option>
                    <option value="good"      @selected(old('condition')=='good')>Good</option>
                    <option value="fair"      @selected(old('condition')=='fair')>Fair</option>
                    <option value="poor"      @selected(old('condition')=='poor')>Poor</option>
                    <option value="damaged"   @selected(old('condition')=='damaged')>Damaged</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Location</label>
                <input type="text" name="location" value="{{ old('location') }}"
                    placeholder="e.g. Computer Lab, Library, Office"
                    class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
        </div>

        {{-- Purchase Info --}}
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                <i class="bi bi-receipt text-emerald-500"></i> Purchase Information
            </h3>
        </div>
        <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-3 gap-4 border-b border-slate-100 dark:border-slate-700">

            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Purchase Date</label>
                <input type="date" name="purchase_date" value="{{ old('purchase_date') }}"
                    class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Purchase Price (Rs.)</label>
                <input type="number" name="purchase_price" value="{{ old('purchase_price') }}"
                    placeholder="0.00" step="0.01" min="0"
                    class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Warranty Expiry</label>
                <input type="date" name="warranty_expiry" value="{{ old('warranty_expiry') }}"
                    class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Supplier</label>
                <select name="supplier_id"
                    class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">— None —</option>
                    @foreach($suppliers as $sup)
                    <option value="{{ $sup->id }}" @selected(old('supplier_id')==$sup->id)>{{ $sup->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Notes --}}
        <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700">
            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Description / Notes</label>
            <textarea name="description" rows="3"
                placeholder="Any additional notes about this asset..."
                class="w-full border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400">{{ old('description') }}</textarea>
        </div>

        {{-- Actions --}}
        <div class="px-6 py-4 flex items-center justify-end gap-3 bg-slate-50 dark:bg-slate-900/40">
            <a href="{{ route('inventory.assets') }}"
               class="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 hover:bg-slate-100 dark:hover:bg-slate-600 transition">
                Cancel
            </a>
            <button type="submit"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition">
                <i class="bi bi-check-circle"></i> Register Asset
            </button>
        </div>
    </form>
</div>
@endsection
