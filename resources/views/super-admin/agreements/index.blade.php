@extends('layouts.app')
@section('title', 'Service Agreements (सेवा सम्झौता) - Super Admin')

@section('content')
<div class="px-6 py-6 max-w-7xl mx-auto space-y-6">

    {{-- Top Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">
                    Legal &amp; Contracts
                </span>
                <span class="text-xs text-slate-400">&bull; विद्यालय व्यवस्थापन प्रणालीसम्बन्धी सेवा सम्झौता</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1 flex items-center gap-2.5">
                <i class="bi bi-file-earmark-check-fill text-emerald-600"></i>
                Service Agreements (सेवा सम्झौता व्यवस्थापन)
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Formal legal agreements between Tech Pro Code Pvt. Ltd. (NepalSchoolHub) and partner schools across Nepal
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('super.agreements.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-sm shadow-emerald-200 dark:shadow-none transition-all">
                <i class="bi bi-file-earmark-plus-fill"></i>
                Generate New Agreement (नयाँ सम्झौता तयार गर्नुहोस्)
            </a>
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
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium">Total Agreements</p>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white">{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg">
                    <i class="bi bi-patch-check-fill"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium">Active Contracts</p>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white">{{ $stats['active'] }}</h3>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg">
                    <i class="bi bi-pencil-square"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium">Draft Agreements</p>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white">{{ $stats['draft'] }}</h3>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center text-lg">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium">Total Contract Value</p>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white">रु. {{ number_format($stats['total_value']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
        <form method="GET" action="{{ route('super.agreements.index') }}" class="flex flex-col md:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search by Agreement #, School Name, PAN No, Representative..."
                    class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-hidden focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="w-full md:w-56">
                <select name="school_id" onchange="this.form.submit()"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                    <option value="">All Schools</option>
                    @foreach($schools as $sch)
                        <option value="{{ $sch->id }}" {{ request('school_id') == $sch->id ? 'selected' : '' }}>{{ $sch->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-44">
                <select name="status" onchange="this.form.submit()"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active (सक्रिय)</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft (मस्यौदा)</option>
                    <option value="renewed" {{ request('status') === 'renewed' ? 'selected' : '' }}>Renewed (नवीकृत)</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired (समाप्त)</option>
                </select>
            </div>

            <button type="submit" class="w-full md:w-auto px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-sm font-semibold">
                Filter
            </button>
            @if(request()->anyFilled(['search', 'school_id', 'status']))
                <a href="{{ route('super.agreements.index') }}" class="px-3 py-2 rounded-xl text-slate-500 hover:text-slate-700 text-sm font-medium">
                    Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Agreements Table --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-900/60 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="py-3.5 px-4">Agreement Ref &amp; Date</th>
                        <th class="py-3.5 px-4">School (सेवा लिने विद्यालय)</th>
                        <th class="py-3.5 px-4">Setup &amp; Renewal Fee</th>
                        <th class="py-3.5 px-4">Hardware &amp; Extra Services</th>
                        <th class="py-3.5 px-4">Total Contract</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($agreements as $agr)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="py-3.5 px-4">
                                <a href="{{ route('super.agreements.show', $agr->id) }}" class="font-mono font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                                    {{ $agr->agreement_number }}
                                </a>
                                <div class="text-xs text-slate-700 dark:text-slate-300 font-medium mt-0.5">
                                    {{ $agr->agreement_date_bs }}
                                </div>
                                <div class="text-[11px] text-slate-400">
                                    Duration: {{ $agr->duration_years }} Year(s)
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 dark:text-white">
                                    {{ $agr->school_name }}
                                </div>
                                @if($agr->school_address)
                                    <div class="text-xs text-slate-500 dark:text-slate-400">
                                        <i class="bi bi-geo-alt"></i> {{ $agr->school_address }}
                                    </div>
                                @endif
                                <div class="text-[11px] text-slate-400 flex items-center gap-2 mt-0.5">
                                    @if($agr->school_pan_no)
                                        <span>PAN: <strong class="text-slate-600 dark:text-slate-300 font-mono">{{ $agr->school_pan_no }}</strong></span>
                                    @endif
                                    @if($agr->school_representative)
                                        <span>&bull; Rep: {{ $agr->school_representative }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="text-xs">
                                    <span class="text-slate-400">Setup:</span>
                                    <strong class="text-slate-800 dark:text-slate-100 font-bold">रु. {{ number_format($agr->setup_fee) }}</strong>
                                </div>
                                <div class="text-xs mt-0.5">
                                    <span class="text-slate-400">Renewal:</span>
                                    <strong class="text-slate-800 dark:text-slate-100 font-bold">रु. {{ number_format($agr->renewal_fee) }}</strong>/yr
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($agr->items->count() > 0)
                                    <div class="flex flex-wrap gap-1 max-w-xs">
                                        @foreach($agr->items as $it)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                                {{ $it->item_name }} (x{{ $it->quantity }})
                                            </span>
                                        @endforeach
                                    </div>
                                    <div class="text-[11px] text-indigo-600 dark:text-indigo-400 font-semibold mt-1">
                                        + रु. {{ number_format($agr->additional_services_total) }} extra
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">Standard ERP Package</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-extrabold text-slate-900 dark:text-white text-base">
                                    रु. {{ number_format($agr->grand_total) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($agr->status === 'active')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                        <i class="bi bi-check-circle-fill text-[10px]"></i> Active (सक्रिय)
                                    </span>
                                @elseif($agr->status === 'draft')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                        <i class="bi bi-pencil-fill text-[10px]"></i> Draft (मस्यौदा)
                                    </span>
                                @elseif($agr->status === 'renewed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                        <i class="bi bi-arrow-repeat text-[10px]"></i> Renewed
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                        {{ ucfirst($agr->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    {{-- Print Official Agreement button --}}
                                    <a href="{{ route('super.agreements.print', $agr->id) }}" target="_blank"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:hover:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 font-semibold text-xs transition-colors"
                                        title="Print Agreement Paper (Same to Same PDF format)">
                                        <i class="bi bi-printer-fill"></i>
                                        Print Paper
                                    </a>

                                    {{-- View Details --}}
                                    <a href="{{ route('super.agreements.show', $agr->id) }}"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                                        title="View Details">
                                        <i class="bi bi-eye-fill text-base"></i>
                                    </a>

                                    {{-- Edit --}}
                                    <a href="{{ route('super.agreements.edit', $agr->id) }}"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                                        title="Edit Agreement">
                                        <i class="bi bi-pencil-square text-base"></i>
                                    </a>

                                    {{-- Delete --}}
                                    <form action="{{ route('super.agreements.destroy', $agr->id) }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this agreement?');" class="inline">
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
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="bi bi-file-earmark-text text-4xl block mb-2 text-slate-300 dark:text-slate-600"></i>
                                No agreements found. Click "Generate New Agreement" above to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($agreements->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-700">
                {{ $agreements->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

