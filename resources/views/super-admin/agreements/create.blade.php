@extends('layouts.app')
@section('title', 'Generate Service Agreement - Super Admin')

@section('content')
<div class="px-6 py-6 max-w-5xl mx-auto space-y-6">

    {{-- Breadcrumb & Header --}}
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('super.agreements.index') }}" class="hover:text-emerald-600">Service Agreements</a>
                <span>&bull;</span>
                <span>New Agreement</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                <i class="bi bi-file-earmark-plus-fill text-emerald-600"></i>
                Generate Service Agreement (विद्यालय सेवा सम्झौता तयार गर्नुहोस्)
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Official bilateral contract between Tech Pro Code Pvt. Ltd. and client school with hardware &amp; service inclusions
            </p>
        </div>
        <a href="{{ route('super.agreements.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-sm font-semibold hover:bg-slate-50">
            Cancel
        </a>
    </div>

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill text-rose-600"></i>
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('super.agreements.store') }}" method="POST" id="agreementForm" class="space-y-6">
        @csrf

        {{-- ── 1. AGREEMENT BASIC DETAILS & DATES ───────────────────── --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-6 shadow-xs space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 flex items-center gap-2 border-b border-slate-100 dark:border-slate-700 pb-3">
                <i class="bi bi-calendar-event"></i>
                १. सम्झौताको मिति तथा आधारभूत विवरण (Agreement Details &amp; Period)
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Agreement Number (सम्झौता नं.)</label>
                    <input type="text" name="agreement_number" value="{{ old('agreement_number', $nextNumber) }}"
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white font-mono font-bold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Status (अवस्था) *</label>
                    <select name="status" required class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white">
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active (सक्रिय - सिधै लागू)</option>
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft (मस्यौदा)</option>
                        <option value="renewed" {{ old('status') === 'renewed' ? 'selected' : '' }}>Renewed (नवीकृत)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Contract Duration (अवधि) *</label>
                    <div class="flex items-center gap-2">
                        <input type="number" min="1" max="10" name="duration_years" value="{{ old('duration_years', 1) }}"
                            class="w-24 px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white font-bold">
                        <span class="text-xs text-slate-500 font-medium">वर्ष (१ वर्षसम्म कायम रहने)</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-2">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        सम्झौता भएको नेपाली मिति (Nepali BS Date in words) *
                    </label>
                    <input type="text" name="agreement_date_bs" value="{{ old('agreement_date_bs', '२०८३ साल बैशाख १५ गते') }}" required
                        placeholder="जस्तै: २०८३ साल बैशाख १५ गते"
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white font-semibold">
                    <p class="text-[11px] text-slate-400 mt-1">यसले सम्झौताको पहिलो पृष्ठको "मिति २०८३ साल ... महिना ... गते" भर्नेछ।</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Effective Date (AD)</label>
                    <input type="date" name="effective_date" value="{{ old('effective_date', date('Y-m-d')) }}"
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Expiry Date (AD)</label>
                    <input type="date" name="expiry_date" value="{{ old('expiry_date', date('Y-m-d', strtotime('+1 year'))) }}"
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white">
                </div>
            </div>
        </div>

        {{-- ── 2. PARTIES TO THE AGREEMENT (सेवा प्रदायक र सेवा लिने विद्यालय) ───── --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Service Provider (सेवा प्रदायक) Details --}}
            <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-5 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-2.5">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5">
                        <i class="bi bi-briefcase-fill"></i>
                        सेवा प्रदायक (Service Provider)
                    </h3>
                    <span class="text-[11px] text-slate-400 font-mono">Tech Pro Code</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">संस्थाको नाम *</label>
                    <input type="text" name="provider_name" value="{{ old('provider_name', 'टेक प्रोकोड प्रा. लि. (नेपालस्कुलहब)') }}" required
                        class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 font-semibold">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">कम्पनी दर्ता नं. *</label>
                        <input type="text" name="provider_reg_no" value="{{ old('provider_reg_no', '३९१५१२/८२/८३') }}" required
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">स्थायी लेखा नं. (प्यान/भ्याट) *</label>
                        <input type="text" name="provider_pan_vat" value="{{ old('provider_pan_vat', '६२३६०८२७२') }}" required
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 font-mono">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">ठेगाना *</label>
                        <input type="text" name="provider_address" value="{{ old('provider_address', 'सिरहा / काठमाडौँ, नेपाल') }}" required
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">फोन नं. *</label>
                        <input type="text" name="provider_phone" value="{{ old('provider_phone', '+९७७ ९८०५९१६५९८') }}" required
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">इमेल *</label>
                        <input type="email" name="provider_email" value="{{ old('provider_email', 'support@techprocod.com.np') }}" required
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">वेबसाइट *</label>
                        <input type="text" name="provider_website" value="{{ old('provider_website', 'www.techprocod.com.np') }}" required
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-1">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">अधिकृत हस्ताक्षरकर्ता (Signatory)</label>
                        <input type="text" name="provider_representative" value="{{ old('provider_representative', 'अधिकृत प्रतिनिधि') }}"
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">पद (Designation)</label>
                        <input type="text" name="provider_designation" value="{{ old('provider_designation', 'प्रबन्ध निर्देशक') }}"
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                    </div>
                </div>
            </div>

            {{-- Client School (सेवा लिने विद्यालय) Details --}}
            <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-5 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-2.5">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                        <i class="bi bi-building"></i>
                        सेवा लिने विद्यालय (Client School)
                    </h3>
                    <div class="w-48">
                        <select id="schoolSelector" onchange="autofillSchool(this)"
                            class="w-full text-xs px-2 py-1 rounded-lg border border-emerald-300 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-200 font-medium">
                            <option value="">-- Quick Select School --</option>
                            @foreach($schools as $sch)
                                <option value="{{ $sch->id }}"
                                    data-name="{{ $sch->name }}"
                                    data-address="{{ $sch->address ? ($sch->address . ', ' . $sch->city . ', ' . $sch->district) : '' }}"
                                    data-phone="{{ $sch->phone }}"
                                    data-email="{{ $sch->email }}"
                                    data-pan="{{ $sch->iemis_code ?? '' }}"
                                    data-contact="{{ $sch->contact_person }}"
                                    {{ (old('school_id', optional($selectedSchool)->id) == $sch->id) ? 'selected' : '' }}>
                                    {{ $sch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <input type="hidden" name="school_id" id="school_id_input" value="{{ old('school_id', optional($selectedSchool)->id) }}">

                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">विद्यालयको नाम *</label>
                    <input type="text" id="school_name" name="school_name" required
                        value="{{ old('school_name', optional($selectedSchool)->name ?? 'ह्यापी ल्याण्ड इङ्ग्लिस बोर्डिङ स्कुल सिरहा प्रा. लि.') }}"
                        placeholder="जस्तै: ह्यापी ल्याण्ड इङ्ग्लिस बोर्डिङ स्कुल सिरहा प्रा. लि."
                        class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 font-bold text-slate-900 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">ठेगाना *</label>
                    <input type="text" id="school_address" name="school_address"
                        value="{{ old('school_address', optional($selectedSchool)->address ?? 'सिरहा नगरपालिका–०१, सिरहा, मधेश प्रदेश, नेपाल') }}"
                        placeholder="जस्तै: सिरहा नगरपालिका–०१, सिरहा, मधेश प्रदेश, नेपाल"
                        class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">कम्पनी दर्ता नं.</label>
                        <input type="text" id="school_reg_no" name="school_reg_no"
                            value="{{ old('school_reg_no', '११४३८३/०७०/०७१') }}"
                            placeholder="११४३८३/०७०/०७१"
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">स्थायी लेखा नं. (प्यान)</label>
                        <input type="text" id="school_pan_no" name="school_pan_no"
                            value="{{ old('school_pan_no', '६०२६६६३९८') }}"
                            placeholder="६०२६६६३९८"
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">अधिकृत प्रतिनिधि (Representative)</label>
                        <input type="text" id="school_representative" name="school_representative"
                            value="{{ old('school_representative', optional($selectedSchool)->contact_person ?? '') }}"
                            placeholder="प्रतिनिधिको नाम"
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">पद (Designation)</label>
                        <input type="text" id="school_designation" name="school_designation"
                            value="{{ old('school_designation', 'प्रिन्सिपल') }}"
                            placeholder="प्रिन्सिपल / प्रबन्ध निर्देशक"
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-1">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">फोन नं.</label>
                        <input type="text" id="school_phone" name="school_phone"
                            value="{{ old('school_phone', optional($selectedSchool)->phone ?? '') }}"
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">इमेल</label>
                        <input type="email" id="school_email" name="school_email"
                            value="{{ old('school_email', optional($selectedSchool)->email ?? '') }}"
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── 3. SECTION 2: शुल्क तथा मूल्य संरचना (STANDARD FEES) ──────────────── --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-6 shadow-xs space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
                <span class="flex items-center gap-2">
                    <i class="bi bi-table text-indigo-600"></i>
                    २. शुल्क तथा मूल्य संरचना (Standard Software Fees - Section 2)
                </span>
                <span class="text-xs text-slate-400 font-normal">अग्रिम सम्झौता बमोजिम</span>
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
                                <input type="number" step="0.01" name="setup_fee" id="setup_fee" value="{{ old('setup_fee', 15000) }}" oninput="calcTotal()"
                                    class="w-full px-2.5 py-1 text-sm font-bold text-slate-900 dark:text-white rounded-lg border border-slate-200 dark:border-slate-700 font-mono">
                            </td>
                            <td class="py-2.5 px-3">
                                <input type="text" name="setup_fee_basis" value="{{ old('setup_fee_basis', 'पहिलो वर्षका लागि एकपटक') }}"
                                    class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700">
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-700 font-medium">
                                वार्षिक नवीकरण शुल्क
                            </td>
                            <td class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-700">
                                <input type="number" step="0.01" name="renewal_fee" id="renewal_fee" value="{{ old('renewal_fee', 10000) }}" oninput="calcTotal()"
                                    class="w-full px-2.5 py-1 text-sm font-bold text-slate-900 dark:text-white rounded-lg border border-slate-200 dark:border-slate-700 font-mono">
                            </td>
                            <td class="py-2.5 px-3">
                                <input type="text" name="renewal_fee_basis" value="{{ old('renewal_fee_basis', 'दोस्रो वर्षदेखि प्रतिवर्ष') }}"
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
                                <input type="text" name="software_inclusion_text" value="{{ old('software_inclusion_text', 'माथि उल्लिखित शुल्कभित्रै') }}"
                                    class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── 4. ADDITIONAL HARDWARE & SERVICES (Thermal Printer, CCTV, PC Setup, etc.) ── --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-6 shadow-xs space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-700 pb-3">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 flex items-center gap-2">
                        <i class="bi bi-plus-square-fill"></i>
                        थप हार्डवेयर तथा सेवा सामग्रीहरू (Hardware &amp; Additional Services Provided)
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Add Thermal Printers, CCTV Cameras, Full Computer Setups, Biometrics, or other equipment provided in this agreement.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <select id="quickServiceSelect" onchange="addCatalogItem(this)"
                        class="text-xs px-3 py-1.5 rounded-xl border border-indigo-300 dark:border-indigo-700 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-900 dark:text-indigo-200 font-semibold">
                        <option value="">+ Quick Add from Catalog...</option>
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
                        class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-semibold flex items-center gap-1">
                        <i class="bi bi-plus-lg"></i> Custom Row
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm" id="itemsTable">
                    <thead class="bg-slate-50 dark:bg-slate-900 text-xs font-bold uppercase text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="py-2.5 px-3">सेवा / सामग्रीको नाम (Item / Description)</th>
                            <th class="py-2.5 px-3 w-32">मूल्य (Rate रु.)</th>
                            <th class="py-2.5 px-3 w-20">परिमाण (Qty)</th>
                            <th class="py-2.5 px-3 w-32">जम्मा (Total रु.)</th>
                            <th class="py-2.5 px-3 w-36">आधार (Basis)</th>
                            <th class="py-2.5 px-2 w-12 text-center"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody" class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        {{-- Rows appended by JavaScript --}}
                    </tbody>
                </table>
            </div>

            {{-- Summary & Grand Total --}}
            <div class="bg-slate-50 dark:bg-slate-900/60 rounded-xl p-4 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="text-xs text-slate-500 space-y-1">
                    <div>पहिलो वर्ष स्थापना शुल्क: <strong class="text-slate-800 dark:text-slate-200 font-mono" id="dispSetup">रु. १५,०००</strong></div>
                    <div>वार्षिक नवीकरण शुल्क: <strong class="text-slate-800 dark:text-slate-200 font-mono" id="dispRenewal">रु. १०,०००</strong></div>
                    <div>थप हार्डवेयर/सेवा जम्मा: <strong class="text-indigo-600 dark:text-indigo-400 font-mono" id="dispAdditional">रु. ०</strong></div>
                </div>

                <div class="text-right">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">सहमति भएको कुल रकम (Grand Total)</span>
                    <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono" id="dispGrandTotal">
                        रु. २५,०००/-
                    </span>
                </div>
            </div>
        </div>

        {{-- ── 5. WITNESSES (साक्षीहरू) ───────────────────────────────── --}}
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
                        <input type="text" name="witness_1_name" value="{{ old('witness_1_name') }}" placeholder="सेवा प्रदायक साक्षीको नाम"
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">हस्ताक्षर मिति</label>
                        <input type="text" name="witness_1_date" value="{{ old('witness_1_date') }}" placeholder="२०८३/..."
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                    </div>
                </div>

                <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30 space-y-3">
                    <h4 class="text-xs font-bold text-emerald-600 uppercase">साक्षी २ (विद्यालय)</h4>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">साक्षीको नाम</label>
                        <input type="text" name="witness_2_name" value="{{ old('witness_2_name') }}" placeholder="विद्यालय तर्फको साक्षीको नाम"
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">हस्ताक्षर मिति</label>
                        <input type="text" name="witness_2_date" value="{{ old('witness_2_date') }}" placeholder="२०८३/..."
                            class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit Buttons --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('super.agreements.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50">
                Cancel
            </a>
            <button type="submit" class="px-7 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-md shadow-emerald-200 dark:shadow-none flex items-center gap-2">
                <i class="bi bi-file-earmark-check"></i>
                Generate Official Agreement Paper
            </button>
        </div>
    </form>
</div>

<script>
let rowIndex = 0;

function autofillSchool(select) {
    const opt = select.options[select.selectedIndex];
    if (!opt || !opt.value) return;

    document.getElementById('school_id_input').value = opt.value;
    if (opt.dataset.name) document.getElementById('school_name').value = opt.dataset.name;
    if (opt.dataset.address) document.getElementById('school_address').value = opt.dataset.address;
    if (opt.dataset.phone) document.getElementById('school_phone').value = opt.dataset.phone;
    if (opt.dataset.email) document.getElementById('school_email').value = opt.dataset.email;
    if (opt.dataset.pan) document.getElementById('school_pan_no').value = opt.dataset.pan;
    if (opt.dataset.contact) document.getElementById('school_representative').value = opt.dataset.contact;
}

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

// Initial calculation
document.addEventListener('DOMContentLoaded', function() {
    calcTotal();
});
</script>
@endsection

