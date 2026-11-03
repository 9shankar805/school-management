@extends('layouts.app')
@section('title', 'Edit Service Agreement - ' . $agreement->agreement_number)

@section('content')
<div class="px-6 py-6 max-w-5xl mx-auto space-y-6">

    {{-- Breadcrumb & Header --}}
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('super.agreements.index') }}" class="hover:text-emerald-600">Service Agreements</a>
                <span>&bull;</span>
                <a href="{{ route('super.agreements.show', $agreement->id) }}" class="hover:text-emerald-600">{{ $agreement->agreement_number }}</a>
                <span>&bull;</span>
                <span>Edit</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                <i class="bi bi-pencil-square text-emerald-600"></i>
                Edit Service Agreement (सम्झौता सम्पादन गर्नुहोस्)
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Agreement Ref: <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $agreement->agreement_number }}</span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('super.agreements.print', $agreement->id) }}" target="_blank"
                class="px-3.5 py-2 rounded-xl bg-emerald-50 text-emerald-700 text-xs font-bold hover:bg-emerald-100 flex items-center gap-1">
                <i class="bi bi-printer-fill"></i> View Print Layout
            </a>
            <a href="{{ route('super.agreements.show', $agreement->id) }}" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-sm font-semibold hover:bg-slate-50">
                Cancel
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill text-rose-600"></i>
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('super.agreements.update', $agreement->id) }}" method="POST" id="agreementForm" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- ── 1. AGREEMENT BASIC DETAILS & DATES ───────────────────── --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-6 shadow-xs space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 flex items-center gap-2 border-b border-slate-100 dark:border-slate-700 pb-3">
                <i class="bi bi-calendar-event"></i>
                १. सम्झौताको मिति तथा आधारभूत विवरण
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Agreement Number (सम्झौता नं.)</label>
                    <input type="text" name="agreement_number" value="{{ old('agreement_number', $agreement->agreement_number) }}" required
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white font-mono font-bold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Status (अवस्था) *</label>
                    <select name="status" required class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white">
                        <option value="active" {{ old('status', $agreement->status) === 'active' ? 'selected' : '' }}>Active (सक्रिय)</option>
                        <option value="draft" {{ old('status', $agreement->status) === 'draft' ? 'selected' : '' }}>Draft (मस्यौदा)</option>
                        <option value="renewed" {{ old('status', $agreement->status) === 'renewed' ? 'selected' : '' }}>Renewed (नवीकृत)</option>
                        <option value="expired" {{ old('status', $agreement->status) === 'expired' ? 'selected' : '' }}>Expired (समाप्त)</option>
                        <option value="terminated" {{ old('status', $agreement->status) === 'terminated' ? 'selected' : '' }}>Terminated (रद्द)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Contract Duration (अवधि) *</label>
                    <div class="flex items-center gap-2">
                        <input type="number" min="1" max="10" name="duration_years" value="{{ old('duration_years', $agreement->duration_years) }}"
                            class="w-24 px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white font-bold">
                        <span class="text-xs text-slate-500 font-medium">वर्ष</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-2">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        सम्झौता भएको नेपाली मिति (Nepali BS Date) *
                    </label>
                    <input type="text" name="agreement_date_bs" value="{{ old('agreement_date_bs', $agreement->agreement_date_bs) }}" required
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Effective Date (AD)</label>
                    <input type="date" name="effective_date" value="{{ old('effective_date', optional($agreement->effective_date)->format('Y-m-d')) }}"
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Expiry Date (AD)</label>
                    <input type="date" name="expiry_date" value="{{ old('expiry_date', optional($agreement->expiry_date)->format('Y-m-d')) }}"
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white">
                </div>
            </div>
        </div>

        {{-- ── 2. PARTIES TO THE AGREEMENT ────────────────────────────── --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Service Provider --}}
            <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-5 shadow-xs space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 border-b border-slate-100 dark:border-slate-700 pb-2.5 flex items-center gap-1.5">
                    <i class="bi bi-briefcase-fill"></i>
                    सेवा प्रदायक (Service Provider)
                </h3>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">संस्थाको नाम *</label>
                    <input type="text" name="provider_name" value="{{ old('provider_name', $agreement->provider_name) }}" required
                        class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 font-semibold">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">कम्पनी दर्ता नं. *</label>
                        <input type="text" name="provider_reg_no" value="{{ old('provider_reg_no', $agreement->provider_reg_no) }}" required
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">स्थायी लेखा नं. (प्यान/भ्याट) *</label>
                        <input type="text" name="provider_pan_vat" value="{{ old('provider_pan_vat', $agreement->provider_pan_vat) }}" required
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 font-mono">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">ठेगाना *</label>
                        <input type="text" name="provider_address" value="{{ old('provider_address', $agreement->provider_address) }}" required
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">फोन नं. *</label>
                        <input type="text" name="provider_phone" value="{{ old('provider_phone', $agreement->provider_phone) }}" required
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">इमेल *</label>
                        <input type="email" name="provider_email" value="{{ old('provider_email', $agreement->provider_email) }}" required
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">वेबसाइट *</label>
                        <input type="text" name="provider_website" value="{{ old('provider_website', $agreement->provider_website) }}" required
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-1">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">अधिकृत हस्ताक्षरकर्ता</label>
                        <input type="text" name="provider_representative" value="{{ old('provider_representative', $agreement->provider_representative) }}"
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">पद (Designation)</label>
                        <input type="text" name="provider_designation" value="{{ old('provider_designation', $agreement->provider_designation) }}"
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                    </div>
                </div>
            </div>

            {{-- Client School --}}
            <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-5 shadow-xs space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 border-b border-slate-100 dark:border-slate-700 pb-2.5 flex items-center gap-1.5">
                    <i class="bi bi-building"></i>
                    सेवा लिने विद्यालय (Client School)
                </h3>

                <input type="hidden" name="school_id" value="{{ old('school_id', $agreement->school_id) }}">

                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">विद्यालयको नाम *</label>
                    <input type="text" name="school_name" value="{{ old('school_name', $agreement->school_name) }}" required
                        class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 font-bold text-slate-900 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">ठेगाना *</label>
                    <input type="text" name="school_address" value="{{ old('school_address', $agreement->school_address) }}"
                        class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">कम्पनी दर्ता नं.</label>
                        <input type="text" name="school_reg_no" value="{{ old('school_reg_no', $agreement->school_reg_no) }}"
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">स्थायी लेखा नं. (प्यान)</label>
                        <input type="text" name="school_pan_no" value="{{ old('school_pan_no', $agreement->school_pan_no) }}"
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">अधिकृत प्रतिनिधि</label>
                        <input type="text" name="school_representative" value="{{ old('school_representative', $agreement->school_representative) }}"
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">पद (Designation)</label>
                        <input type="text" name="school_designation" value="{{ old('school_designation', $agreement->school_designation) }}"
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-1">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">फोन नं.</label>
                        <input type="text" name="school_phone" value="{{ old('school_phone', $agreement->school_phone) }}"
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">इमेल</label>
                        <input type="email" name="school_email" value="{{ old('school_email', $agreement->school_email) }}"
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── 3. STANDARD FEES ───────────────────────────────────────── --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-6 shadow-xs space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
                <span class="flex items-center gap-2">
                    <i class="bi bi-table text-indigo-600"></i>
                    २. शुल्क तथा मूल्य संरचना
                </span>
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                    <thead class="bg-slate-50 dark:bg-slate-900 text-xs font-bold uppercase text-slate-600 dark:text-slate-300">
                        <tr>
                            <th class="py-2.5 px-3 border-b border-r border-slate-200 dark:border-slate-700">सेवा वा वस्तुको विवरण</th>
                            <th class="py-2.5 px-3 border-b border-r border-slate-200 dark:border-slate-700 w-44">मूल्य (रु.)</th>
                            <th class="py-2.5 px-3 border-b border-slate-200 dark:border-slate-700 w-56">शुल्क लाग्ने आधार</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        <tr>
                            <td class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-700 font-medium">
                                पहिलो वर्षको प्रारम्भिक स्थापना तथा प्रणाली सञ्चालन शुल्क
                            </td>
                            <td class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-700">
                                <input type="number" step="0.01" name="setup_fee" id="setup_fee" value="{{ old('setup_fee', $agreement->setup_fee) }}" oninput="calcTotal()"
                                    class="w-full px-2.5 py-1 text-sm font-bold text-slate-900 dark:text-white rounded-lg border border-slate-200 dark:border-slate-700 font-mono">
                            </td>
                            <td class="py-2.5 px-3">
                                <input type="text" name="setup_fee_basis" value="{{ old('setup_fee_basis', $agreement->setup_fee_basis) }}"
                                    class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700">
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-700 font-medium">
                                वार्षिक नवीकरण शुल्क
                            </td>
                            <td class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-700">
                                <input type="number" step="0.01" name="renewal_fee" id="renewal_fee" value="{{ old('renewal_fee', $agreement->renewal_fee) }}" oninput="calcTotal()"
                                    class="w-full px-2.5 py-1 text-sm font-bold text-slate-900 dark:text-white rounded-lg border border-slate-200 dark:border-slate-700 font-mono">
                            </td>
                            <td class="py-2.5 px-3">
                                <input type="text" name="renewal_fee_basis" value="{{ old('renewal_fee_basis', $agreement->renewal_fee_basis) }}"
                                    class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700">
                            </td>
                        </tr>
                        <tr class="bg-slate-50/50 dark:bg-slate-900/30">
                            <td class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-700 font-medium">
                                विद्यालय व्यवस्थापन सफ्टवेयर, मोबाइल अनुप्रयोग तथा वेबसाइट
                            </td>
                            <td class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-700 font-bold text-emerald-600">
                                समावेश
                            </td>
                            <td class="py-2.5 px-3">
                                <input type="text" name="software_inclusion_text" value="{{ old('software_inclusion_text', $agreement->software_inclusion_text) }}"
                                    class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── 4. ADDITIONAL HARDWARE & SERVICES ───────────────────────── --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-6 shadow-xs space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-700 pb-3">
                <h3 class="text-sm font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 flex items-center gap-2">
                    <i class="bi bi-plus-square-fill"></i>
                    थप हार्डवेयर तथा सेवा सामग्रीहरू (Hardware &amp; Additional Services)
                </h3>
                <div class="flex items-center gap-2">
                    <select id="quickServiceSelect" onchange="addCatalogItem(this)"
                        class="text-xs px-3 py-1.5 rounded-xl border border-indigo-300 dark:border-indigo-700 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-900 dark:text-indigo-200 font-semibold">
                        <option value="">+ Add from Catalog...</option>
                        @foreach($services as $srv)
                            <option value="{{ $srv->id }}"
                                data-name="{{ $srv->name_np ?: $srv->name }}"
                                data-price="{{ $srv->default_price }}"
                                data-basis="{{ $srv->unit_name ?: 'एकपटक' }}">
                                {{ $srv->name }} (रु. {{ number_format($srv->default_price) }})
                            </option>
                        @endforeach
                    </select>
                    <button type="button" onclick="addCustomRow()"
                        class="px-3 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-semibold flex items-center gap-1">
                        <i class="bi bi-plus-lg"></i> Custom Row
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm" id="itemsTable">
                    <thead class="bg-slate-50 dark:bg-slate-900 text-xs font-bold uppercase text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="py-2.5 px-3">सेवा / सामग्रीको नाम</th>
                            <th class="py-2.5 px-3 w-32">मूल्य (Rate रु.)</th>
                            <th class="py-2.5 px-3 w-20">परिमाण (Qty)</th>
                            <th class="py-2.5 px-3 w-32">जम्मा (Total रु.)</th>
                            <th class="py-2.5 px-3 w-36">आधार (Basis)</th>
                            <th class="py-2.5 px-2 w-12 text-center"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody" class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @foreach($agreement->items as $index => $item)
                            <tr id="row_{{ $index }}" class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20">
                                <td class="py-2 px-3">
                                    <input type="hidden" name="items[{{ $index }}][service_id]" value="{{ $item->service_id }}">
                                    <input type="text" name="items[{{ $index }}][item_name]" value="{{ $item->item_name }}" required
                                        class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 font-semibold text-slate-800 dark:text-slate-100">
                                </td>
                                <td class="py-2 px-3">
                                    <input type="number" step="0.01" name="items[{{ $index }}][unit_price]" value="{{ $item->unit_price }}" oninput="calcRow({{ $index }})"
                                        class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 font-mono rate-input">
                                </td>
                                <td class="py-2 px-3">
                                    <input type="number" min="1" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}" oninput="calcRow({{ $index }})"
                                        class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 font-mono qty-input">
                                </td>
                                <td class="py-2 px-3 font-mono font-bold text-slate-800 dark:text-slate-100 text-xs row-total">
                                    रु. {{ number_format($item->total_price) }}
                                </td>
                                <td class="py-2 px-3">
                                    <input type="text" name="items[{{ $index }}][basis]" value="{{ $item->basis }}"
                                        class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                                </td>
                                <td class="py-2 px-2 text-center">
                                    <button type="button" onclick="removeRow({{ $index }})" class="p-1 text-rose-500 hover:text-rose-700">
                                        <i class="bi bi-x-circle text-base"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Summary & Grand Total --}}
            <div class="bg-slate-50 dark:bg-slate-900/60 rounded-xl p-4 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="text-xs text-slate-500 space-y-1">
                    <div>पहिलो वर्ष स्थापना: <strong class="text-slate-800 dark:text-slate-200 font-mono" id="dispSetup">रु. {{ number_format($agreement->setup_fee) }}</strong></div>
                    <div>वार्षिक नवीकरण: <strong class="text-slate-800 dark:text-slate-200 font-mono" id="dispRenewal">रु. {{ number_format($agreement->renewal_fee) }}</strong></div>
                    <div>थप सामग्री जम्मा: <strong class="text-indigo-600 dark:text-indigo-400 font-mono" id="dispAdditional">रु. {{ number_format($agreement->additional_services_total) }}</strong></div>
                </div>

                <div class="text-right">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">सहमति भएको कुल रकम (Grand Total)</span>
                    <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono" id="dispGrandTotal">
                        रु. {{ number_format($agreement->grand_total) }}/-
                    </span>
                </div>
            </div>
        </div>

        {{-- ── 5. WITNESSES ───────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-6 shadow-xs space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2 border-b border-slate-100 dark:border-slate-700 pb-3">
                <i class="bi bi-people-fill text-indigo-600"></i>
                साक्षीहरू (Witness Details)
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30 space-y-3">
                    <h4 class="text-xs font-bold text-indigo-600 uppercase">साक्षी १ (सेवा प्रदायक)</h4>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">साक्षीको नाम</label>
                        <input type="text" name="witness_1_name" value="{{ old('witness_1_name', $agreement->witness_1_name) }}"
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">हस्ताक्षर मिति</label>
                        <input type="text" name="witness_1_date" value="{{ old('witness_1_date', $agreement->witness_1_date) }}"
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                    </div>
                </div>

                <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30 space-y-3">
                    <h4 class="text-xs font-bold text-emerald-600 uppercase">साक्षी २ (विद्यालय)</h4>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">साक्षीको नाम</label>
                        <input type="text" name="witness_2_name" value="{{ old('witness_2_name', $agreement->witness_2_name) }}"
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">हस्ताक्षर मिति</label>
                        <input type="text" name="witness_2_date" value="{{ old('witness_2_date', $agreement->witness_2_date) }}"
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit Buttons --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('super.agreements.show', $agreement->id) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50">
                Cancel
            </a>
            <button type="submit" class="px-7 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-md shadow-emerald-200 flex items-center gap-2">
                <i class="bi bi-save"></i>
                Update Agreement
            </button>
        </div>
    </form>
</div>

<script>
let rowIndex = {{ $agreement->items->count() }};

function addCatalogItem(select) {
    const opt = select.options[select.selectedIndex];
    if (!opt || !opt.value) return;

    const srvId = opt.value;
    const name = opt.dataset.name || '';
    const price = parseFloat(opt.dataset.price) || 0;
    const basis = opt.dataset.basis || 'एकपटक';

    appendItemRow(srvId, name, price, 1, basis);
    select.selectedIndex = 0;
}

function addCustomRow() {
    appendItemRow('', '', 0, 1, 'एकपटक');
}

function appendItemRow(srvId, name, price, qty, basis) {
    const tbody = document.getElementById('itemsBody');
    const tr = document.createElement('tr');
    tr.id = 'row_' + rowIndex;
    tr.className = 'hover:bg-slate-50/50 dark:hover:bg-slate-700/20';

    tr.innerHTML = `
        <td class="py-2 px-3">
            <input type="hidden" name="items[${rowIndex}][service_id]" value="${srvId}">
            <input type="text" name="items[${rowIndex}][item_name]" value="${name}" required placeholder="उदा: थर्मल प्रिन्टर ८० एमएम / CCTV Camera"
                class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 font-semibold text-slate-800 dark:text-slate-100">
        </td>
        <td class="py-2 px-3">
            <input type="number" step="0.01" name="items[${rowIndex}][unit_price]" value="${price}" oninput="calcRow(${rowIndex})"
                class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 font-mono rate-input">
        </td>
        <td class="py-2 px-3">
            <input type="number" min="1" name="items[${rowIndex}][quantity]" value="${qty}" oninput="calcRow(${rowIndex})"
                class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 font-mono qty-input">
        </td>
        <td class="py-2 px-3 font-mono font-bold text-slate-800 dark:text-slate-100 text-xs row-total">
            रु. ${(price * qty).toLocaleString()}
        </td>
        <td class="py-2 px-3">
            <input type="text" name="items[${rowIndex}][basis]" value="${basis}" placeholder="एकपटक / प्रति वर्ष"
                class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
        </td>
        <td class="py-2 px-2 text-center">
            <button type="button" onclick="removeRow(${rowIndex})" class="p-1 text-rose-500 hover:text-rose-700">
                <i class="bi bi-x-circle text-base"></i>
            </button>
        </td>
    `;

    tbody.appendChild(tr);
    rowIndex++;
    calcTotal();
}

function removeRow(idx) {
    const row = document.getElementById('row_' + idx);
    if (row) row.remove();
    calcTotal();
}

function calcRow(idx) {
    const row = document.getElementById('row_' + idx);
    if (!row) return;
    const rate = parseFloat(row.querySelector('.rate-input').value) || 0;
    const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
    const tot = rate * qty;
    row.querySelector('.row-total').innerText = 'रु. ' + tot.toLocaleString();
    calcTotal();
}

function calcTotal() {
    const setup = parseFloat(document.getElementById('setup_fee').value) || 0;
    const renewal = parseFloat(document.getElementById('renewal_fee').value) || 0;

    let additional = 0;
    document.querySelectorAll('#itemsBody tr').forEach(row => {
        const rate = parseFloat(row.querySelector('.rate-input')?.value) || 0;
        const qty = parseFloat(row.querySelector('.qty-input')?.value) || 0;
        additional += (rate * qty);
    });

    const grand = setup + renewal + additional;

    document.getElementById('dispSetup').innerText = 'रु. ' + setup.toLocaleString();
    document.getElementById('dispRenewal').innerText = 'रु. ' + renewal.toLocaleString();
    document.getElementById('dispAdditional').innerText = 'रु. ' + additional.toLocaleString();
    document.getElementById('dispGrandTotal').innerText = 'रु. ' + grand.toLocaleString() + '/-';
}
</script>
@endsection

