@extends('layouts.app')
@section('title', 'Service Agreement: ' . $agreement->agreement_number . ' - NepalSchoolHub')

@section('content')
<div class="px-6 py-6 max-w-6xl mx-auto space-y-6">

    {{-- Breadcrumb & Action Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('super.agreements.index') }}" class="hover:text-emerald-600">Service Agreements</a>
                <span>&bull;</span>
                <span class="font-mono">{{ $agreement->agreement_number }}</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                <i class="bi bi-file-earmark-check-fill text-emerald-600"></i>
                {{ $agreement->school_name }}
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Ref: <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $agreement->agreement_number }}</span>
                &bull; Date: <strong>{{ $agreement->agreement_date_bs }}</strong>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Print Official Agreement button --}}
            <a href="{{ route('super.agreements.print', $agreement->id) }}" target="_blank"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-sm shadow-emerald-200 transition-all">
                <i class="bi bi-printer-fill"></i>
                Print Agreement Paper (सम्झौता प्रिन्ट)
            </a>

            {{-- Generate SaaS Bill directly from this agreement --}}
            <a href="{{ route('super.billing.create', ['agreement_id' => $agreement->id, 'school_id' => $agreement->school_id]) }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-sm shadow-indigo-200 transition-all">
                <i class="bi bi-receipt"></i>
                Generate SaaS Bill (बिल जारी गर्नुहोस्)
            </a>

            {{-- Edit --}}
            <a href="{{ route('super.agreements.edit', $agreement->id) }}"
                class="px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-semibold text-sm hover:bg-slate-50">
                <i class="bi bi-pencil-square"></i> Edit
            </a>

            {{-- Delete --}}
            <form action="{{ route('super.agreements.destroy', $agreement->id) }}" method="POST"
                onsubmit="return confirm('Are you sure you want to delete this agreement?');" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="p-2.5 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-sm">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-2">
            <i class="bi bi-check-circle-fill text-emerald-600"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Key Summary Header Card --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-6 shadow-xs">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div>
                <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Status (स्थिति)</p>
                <div class="mt-1">
                    @if($agreement->status === 'active')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Active (सक्रिय)
                        </span>
                    @elseif($agreement->status === 'draft')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span> Draft (मस्यौदा)
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                            {{ ucfirst($agreement->status) }}
                        </span>
                    @endif
                </div>
            </div>

            <div>
                <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Duration (अवधि)</p>
                <p class="text-base font-bold text-slate-900 dark:text-white mt-1">
                    {{ $agreement->duration_years }} Year(s)
                </p>
                <p class="text-xs text-slate-500 mt-0.5">
                    @if($agreement->effective_date) {{ $agreement->effective_date->format('M d, Y') }} @endif
                    @if($agreement->expiry_date) &ndash; {{ $agreement->expiry_date->format('M d, Y') }} @endif
                </p>
            </div>

            <div>
                <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Setup &amp; Renewal</p>
                <p class="text-base font-bold text-slate-900 dark:text-white mt-1">
                    रु. {{ number_format($agreement->setup_fee) }} / रु. {{ number_format($agreement->renewal_fee) }}
                </p>
                <p class="text-xs text-slate-500 mt-0.5">1st Year / Yearly</p>
            </div>

            <div>
                <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Total Contract Value</p>
                <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5 font-mono">
                    रु. {{ number_format($agreement->grand_total) }}/-
                </p>
                <p class="text-xs text-indigo-600 dark:text-indigo-400 font-medium">
                    Includes {{ $agreement->items->count() }} additional item(s)
                </p>
            </div>
        </div>
    </div>

    {{-- Contract Details Box (Matching Official Sections) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Left 2 cols: Preview Document Card --}}
        <div class="md:col-span-2 bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-6 shadow-xs space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="bi bi-file-text text-emerald-600"></i>
                    Agreement Paper Summary (सम्झौता पत्र विवरण)
                </h3>
                <span class="text-xs text-slate-400">Section 1 to 13</span>
            </div>

            {{-- 1. Parties Overview --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200/60 dark:border-slate-700/60 space-y-1">
                    <p class="font-bold text-indigo-600 uppercase">सेवा प्रदायक (Service Provider):</p>
                    <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $agreement->provider_name }}</p>
                    <p class="text-slate-500">दर्ता नं: {{ $agreement->provider_reg_no }}</p>
                    <p class="text-slate-500">प्यान/भ्याट: {{ $agreement->provider_pan_vat }}</p>
                    <p class="text-slate-500">फोन: {{ $agreement->provider_phone }}</p>
                    <p class="text-slate-500">इमेल: {{ $agreement->provider_email }}</p>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200/60 dark:border-slate-700/60 space-y-1">
                    <p class="font-bold text-emerald-600 uppercase">सेवा लिने विद्यालय (Client School):</p>
                    <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $agreement->school_name }}</p>
                    <p class="text-slate-500">ठेगाना: {{ $agreement->school_address }}</p>
                    @if($agreement->school_reg_no)<p class="text-slate-500">दर्ता नं: {{ $agreement->school_reg_no }}</p>@endif
                    @if($agreement->school_pan_no)<p class="text-slate-500">प्यान: {{ $agreement->school_pan_no }}</p>@endif
                    <p class="text-slate-500">प्रतिनिधि: {{ $agreement->school_representative }} ({{ $agreement->school_designation }})</p>
                    <p class="text-slate-500">फोन: {{ $agreement->school_phone }}</p>
                </div>
            </div>

            {{-- 2. Fee Structure Table --}}
            <div>
                <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                    २. शुल्क तथा मूल्य संरचना (Agreed Price Schedule)
                </h4>
                <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-900 font-bold text-slate-600 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="py-2.5 px-3">सेवा वा वस्तुको विवरण</th>
                                <th class="py-2.5 px-3">मूल्य (रु.)</th>
                                <th class="py-2.5 px-3">शुल्क लाग्ने आधार</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            <tr>
                                <td class="py-2.5 px-3 font-medium">पहिलो वर्षको प्रारम्भिक स्थापना तथा प्रणाली सञ्चालन शुल्क</td>
                                <td class="py-2.5 px-3 font-bold font-mono">रु. {{ number_format($agreement->setup_fee) }}/-</td>
                                <td class="py-2.5 px-3 text-slate-500">{{ $agreement->setup_fee_basis }}</td>
                            </tr>
                            <tr>
                                <td class="py-2.5 px-3 font-medium">वार्षिक नवीकरण शुल्क</td>
                                <td class="py-2.5 px-3 font-bold font-mono">रु. {{ number_format($agreement->renewal_fee) }}/-</td>
                                <td class="py-2.5 px-3 text-slate-500">{{ $agreement->renewal_fee_basis }}</td>
                            </tr>
                            <tr>
                                <td class="py-2.5 px-3 font-medium">विद्यालय व्यवस्थापन सफ्टवेयर, मोबाइल अनुप्रयोग तथा वेबसाइट</td>
                                <td class="py-2.5 px-3 font-bold text-emerald-600">समावेश</td>
                                <td class="py-2.5 px-3 text-slate-500">{{ $agreement->software_inclusion_text }}</td>
                            </tr>
                            @foreach($agreement->items as $it)
                                <tr class="bg-indigo-50/30 dark:bg-indigo-950/20">
                                    <td class="py-2.5 px-3 font-medium text-indigo-950 dark:text-indigo-200">
                                        {{ $it->item_name }}
                                        @if($it->quantity > 1) <span class="font-normal text-slate-500">(Qty: {{ $it->quantity }})</span> @endif
                                    </td>
                                    <td class="py-2.5 px-3 font-bold font-mono text-indigo-900 dark:text-indigo-300">
                                        रु. {{ number_format($it->total_price) }}/-
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-500">{{ $it->basis }}</td>
                                </tr>
                            @endforeach
                            <tr class="bg-slate-50 dark:bg-slate-900/60 font-bold">
                                <td class="py-2.5 px-3">सहमति भएको कुल रकम (Grand Total)</td>
                                <td class="py-2.5 px-3 font-mono text-emerald-600 dark:text-emerald-400 text-sm">
                                    रु. {{ number_format($agreement->grand_total) }}/-
                                </td>
                                <td class="py-2.5 px-3 text-slate-400">पहिलो वर्षका लागि</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Signatures Box Summary --}}
            <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/20 space-y-3">
                <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                    १३. हस्ताक्षर तथा स्वीकृति (Signatories &amp; Witnesses)
                </h4>
                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <p class="font-bold text-indigo-600">सेवा प्रदायक तर्फ:</p>
                        <p class="text-slate-700 dark:text-slate-300 mt-0.5">{{ $agreement->provider_representative }} ({{ $agreement->provider_designation }})</p>
                        <p class="text-slate-400">साक्षी १: {{ $agreement->witness_1_name ?: 'साक्षी तोकिएको छैन' }}</p>
                    </div>
                    <div>
                        <p class="font-bold text-emerald-600">विद्यालय तर्फ:</p>
                        <p class="text-slate-700 dark:text-slate-300 mt-0.5">{{ $agreement->school_representative }} ({{ $agreement->school_designation }})</p>
                        <p class="text-slate-400">साक्षी २: {{ $agreement->witness_2_name ?: 'साक्षी तोकिएको छैन' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right 1 col: Linked SaaS Invoices & Quick Actions --}}
        <div class="space-y-6">

            {{-- Quick Actions Card --}}
            <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-5 shadow-xs space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">
                    Agreement Actions
                </h3>
                <a href="{{ route('super.agreements.print', $agreement->id) }}" target="_blank"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm">
                    <i class="bi bi-printer-fill"></i> Print Official Document (4 Pages)
                </a>
                <a href="{{ route('super.billing.create', ['agreement_id' => $agreement->id, 'school_id' => $agreement->school_id]) }}"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 font-bold text-xs">
                    <i class="bi bi-file-earmark-ruled"></i> Generate SaaS Invoice
                </a>
                <a href="{{ route('super.agreements.edit', $agreement->id) }}"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-semibold text-xs hover:bg-slate-50">
                    <i class="bi bi-pencil"></i> Edit Contract Details
                </a>
            </div>

            {{-- Linked SaaS Invoices --}}
            <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-5 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-1.5">
                        <i class="bi bi-receipt"></i>
                        Generated SaaS Invoices
                    </h3>
                    <span class="text-xs font-bold text-slate-400">{{ $agreement->invoices->count() }}</span>
                </div>

                @forelse($agreement->invoices as $inv)
                    <div class="p-3 rounded-xl border border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30 flex items-center justify-between">
                        <div>
                            <a href="{{ route('super.billing.show', $inv->id) }}" class="font-mono font-bold text-xs text-indigo-600 hover:underline">
                                {{ $inv->invoice_number }}
                            </a>
                            <div class="text-[11px] text-slate-400">{{ $inv->invoice_date->format('M d, Y') }}</div>
                        </div>
                        <div class="text-right">
                            <span class="font-mono font-bold text-xs text-slate-900 dark:text-white block">
                                रु. {{ number_format($inv->total_amount) }}
                            </span>
                            @if($inv->status === 'paid')
                                <span class="text-[10px] font-bold text-emerald-600">PAID</span>
                            @else
                                <span class="text-[10px] font-bold text-amber-600">{{ strtoupper($inv->status) }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-4">
                        No invoices generated yet for this agreement.
                    </p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

