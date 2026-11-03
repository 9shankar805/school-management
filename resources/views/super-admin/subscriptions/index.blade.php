@extends('layouts.app')
@section('title', 'School Plans & Subscriptions - Super Admin')

@section('content')
<div class="px-6 py-6 max-w-7xl mx-auto space-y-6">

    {{-- Top Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">
                    SaaS Finance
                </span>
                <span class="text-xs text-slate-400">&bull; योजना तथा सदस्यता व्यवस्थापन</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1 flex items-center gap-2.5">
                <i class="bi bi-award-fill text-amber-500"></i>
                School Plans &amp; Subscriptions (विद्यालय योजना तथा सदस्यता)
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Monitor school plans, activate paid packages, extend expiration dates, and track plan payments across Nepal.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('super.billing.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-sm shadow-indigo-200 dark:shadow-none transition-all">
                <i class="bi bi-receipt-cutoff"></i>
                Create Plan Invoice (नयाँ बिल जारी)
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
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg">
                    <i class="bi bi-building"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium">Total Schools</p>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white">{{ $stats['total_schools'] }}</h3>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                    <i class="bi bi-award-fill"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium">Paid Subscriptions</p>
                    <h3 class="text-xl font-black text-emerald-600 dark:text-emerald-400">{{ $stats['paid_plans'] }}</h3>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center text-lg">
                    <i class="bi bi-gift-fill"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium">Free / Trial Plans</p>
                    <h3 class="text-xl font-black text-slate-700 dark:text-slate-200">{{ $stats['free_plans'] }}</h3>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium">Expiring Soon (30d)</p>
                    <h3 class="text-xl font-black text-amber-600 dark:text-amber-400">{{ $stats['expiring_soon'] }}</h3>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium">SaaS Collected</p>
                    <h3 class="text-base font-black text-indigo-600 dark:text-indigo-400 font-mono">रु. {{ number_format($stats['total_saas_collected']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
        <form action="{{ route('super.subscriptions.index') }}" method="GET" class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('super.subscriptions.index') }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ !request()->has('filter') && !request()->has('plan') ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    All Schools
                </a>
                <a href="{{ route('super.subscriptions.index', ['filter' => 'paid']) }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ request('filter') === 'paid' ? 'bg-emerald-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    <i class="bi bi-check-circle-fill me-1 text-[10px]"></i> Paid Subscriptions
                </a>
                <a href="{{ route('super.subscriptions.index', ['filter' => 'free']) }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ request('filter') === 'free' ? 'bg-slate-800 text-white dark:bg-slate-600' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    Free / Trial
                </a>
                <a href="{{ route('super.subscriptions.index', ['filter' => 'expiring']) }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ request('filter') === 'expiring' ? 'bg-amber-500 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    <i class="bi bi-exclamation-triangle-fill me-1 text-[10px]"></i> Expiring Soon
                </a>
                <a href="{{ route('super.subscriptions.index', ['filter' => 'expired']) }}"
                    class="px-3 py-1.5 rounded-xl text-xs font-semibold transition {{ request('filter') === 'expired' ? 'bg-rose-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                    Expired
                </a>
            </div>

            <div class="flex items-center gap-2">
                <div class="relative w-64">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search school name, email..."
                        class="w-full pl-8 pr-3 py-1.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
                </div>
                <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold">
                    Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Subscriptions Table --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 dark:bg-slate-900/40 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200/80 dark:border-slate-700/80">
                    <tr>
                        <th class="px-5 py-3.5">School Name</th>
                        <th class="px-4 py-3.5">Current Plan</th>
                        <th class="px-4 py-3.5">Plan Expiry &amp; Status</th>
                        <th class="px-4 py-3.5">Quotas (Std / Tch / AI)</th>
                        <th class="px-4 py-3.5">SaaS Billing Status</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-medium">
                    @forelse($schools as $school)
                        @php
                            $billing = $billingTotals->get($school->id);
                            $isExpired = $school->plan_expires_at && $school->plan_expires_at->isPast();
                            $isExpiringSoon = $school->plan_expires_at && !$isExpired && $school->plan_expires_at->diffInDays(now()) <= 30;
                        @endphp
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-750 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $school->logo_url }}" class="w-9 h-9 rounded-lg object-cover flex-shrink-0 border border-slate-200 dark:border-slate-700" alt="">
                                    <div>
                                        <a href="{{ route('super.schools.show', $school) }}" class="font-bold text-slate-900 dark:text-white text-sm hover:text-indigo-600 transition">
                                            {{ $school->name }}
                                        </a>
                                        <p class="text-[11px] text-slate-400 font-mono">{{ $school->slug }}.nepalschoolhub.com</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if($school->plan === 'enterprise')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                        <i class="bi bi-star-fill text-purple-500 text-[9px]"></i> Enterprise
                                    </span>
                                @elseif($school->plan === 'growth')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                        <i class="bi bi-graph-up-arrow text-blue-500 text-[9px]"></i> Growth
                                    </span>
                                @elseif($school->plan === 'starter')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        <i class="bi bi-rocket-takeoff-fill text-emerald-500 text-[9px]"></i> Starter
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                        Free Tier
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if($school->plan_expires_at)
                                    <div>
                                        <span class="text-xs font-semibold {{ $isExpired ? 'text-rose-600 font-bold' : ($isExpiringSoon ? 'text-amber-600 font-bold' : 'text-slate-700 dark:text-slate-200') }}">
                                            {{ $school->plan_expires_at->format('M d, Y') }}
                                        </span>
                                        @if($isExpired)
                                            <span class="ml-1 px-1.5 py-0.5 rounded text-[9px] bg-rose-100 text-rose-700 font-bold">Expired</span>
                                        @elseif($isExpiringSoon)
                                            <span class="ml-1 px-1.5 py-0.5 rounded text-[9px] bg-amber-100 text-amber-800 font-bold">
                                                {{ $school->plan_expires_at->diffInDays(now()) }}d left
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">No Expiration (Lifetime / Trial)</span>
                                @endif
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap text-[11px] text-slate-600 dark:text-slate-300">
                                <div>
                                    <span><i class="bi bi-people text-slate-400 me-1"></i>Max {{ number_format($school->max_students) }} Students</span>
                                    <span class="mx-1 text-slate-300">&bull;</span>
                                    <span>{{ $school->max_teachers }} Tch</span>
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    <span>AI Tools:</span>
                                    @if($school->ai_features_enabled)
                                        <span class="text-emerald-600 font-semibold">Enabled</span>
                                    @else
                                        <span class="text-slate-400">Disabled</span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap font-mono text-xs">
                                @if($billing && $billing->total_billed > 0)
                                    <div>
                                        <span class="text-slate-400 text-[10px]">Paid:</span>
                                        <span class="text-emerald-600 font-bold">रु. {{ number_format($billing->total_paid) }}</span>
                                    </div>
                                    @if($billing->total_due > 0)
                                        <div>
                                            <span class="text-slate-400 text-[10px]">Due:</span>
                                            <span class="text-rose-600 font-bold">रु. {{ number_format($billing->total_due) }}</span>
                                        </div>
                                    @else
                                        <span class="text-[10px] text-emerald-600 font-bold"><i class="bi bi-check2"></i> Fully Paid</span>
                                    @endif
                                @else
                                    <span class="text-slate-400 text-[11px] italic">No SaaS bills issued</span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button"
                                        onclick="openStartPlanModal({{ $school->id }}, '{{ addslashes($school->name) }}', '{{ $school->plan }}', '{{ $school->plan_expires_at ? $school->plan_expires_at->format('Y-m-d') : '' }}', {{ $school->max_students }}, {{ $school->max_teachers }}, {{ $school->max_staff }}, {{ $school->ai_features_enabled ? 'true' : 'false' }})"
                                        class="px-2.5 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/40 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 font-bold text-xs transition flex items-center gap-1">
                                        <i class="bi bi-play-circle-fill"></i> Start / Update Plan
                                    </button>

                                    <a href="{{ route('super.billing.create', ['school_id' => $school->id]) }}"
                                        class="px-2 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs transition"
                                        title="Create Invoice for School">
                                        <i class="bi bi-receipt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <i class="bi bi-building text-3xl mb-2 block opacity-50"></i>
                                No schools match the selected subscription filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($schools->hasPages())
            <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30">
                {{ $schools->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Start / Update Plan Modal --}}
<div id="planModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl w-full max-w-lg shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white" id="modalSchoolName">
                    Start / Update School Plan
                </h3>
                <p class="text-xs text-slate-400">Configure subscription tier, duration and limits</p>
            </div>
            <button onclick="closePlanModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-lg">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="planForm" method="POST" action="">
            @csrf
            @method('PATCH')

            <div class="space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Select Plan (योजना छनौट) <span class="text-rose-500">*</span>
                    </label>
                    <select name="plan" id="modalPlanSelect" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white font-semibold">
                        <option value="free">Free Tier (आधारभूत निःशुल्क - सम्म २०० विद्यार्थी)</option>
                        <option value="starter">Starter Plan (प्रारम्भिक योजना - सम्म ५०० विद्यार्थी)</option>
                        <option value="growth">Growth Plan (मध्यम योजना - सम्म १५०० विद्यार्थी)</option>
                        <option value="enterprise">Enterprise Plan (असीमित / ठूला विद्यालय)</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Add Validity (म्याद थप्नुहोस्)
                        </label>
                        <select name="duration_months" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
                            <option value="">No change / Custom date below</option>
                            <option value="1">+1 Month (१ महिना)</option>
                            <option value="3">+3 Months (३ महिना)</option>
                            <option value="6">+6 Months (६ महिना)</option>
                            <option value="12" selected>+1 Year (१ वर्ष - वार्षिक)</option>
                            <option value="24">+2 Years (२ वर्ष)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Specific Expiry Date
                        </label>
                        <input type="date" name="plan_expires_at" id="modalPlanExpires"
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Max Students</label>
                        <input type="number" name="max_students" id="modalMaxStudents" min="1"
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs dark:text-white">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Max Teachers</label>
                        <input type="number" name="max_teachers" id="modalMaxTeachers" min="1"
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs dark:text-white">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Max Staff</label>
                        <input type="number" name="max_staff" id="modalMaxStaff" min="1"
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs dark:text-white">
                    </div>
                </div>

                <div class="pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="ai_features_enabled" id="modalAiEnabled" value="1"
                            class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                        <span class="font-semibold text-slate-700 dark:text-slate-300">Enable AI Question Paper &amp; Assistant Tools</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700 mt-4">
                <button type="button" onclick="closePlanModal()"
                    class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition">
                    Save Subscription Plan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openStartPlanModal(schoolId, schoolName, plan, expiresAt, maxStd, maxTch, maxStf, aiEnabled) {
    document.getElementById('planForm').action = '/super/subscriptions/' + schoolId;
    document.getElementById('modalSchoolName').innerText = 'Start / Update Plan: ' + schoolName;
    document.getElementById('modalPlanSelect').value = plan;
    document.getElementById('modalPlanExpires').value = expiresAt;
    document.getElementById('modalMaxStudents').value = maxStd;
    document.getElementById('modalMaxTeachers').value = maxTch;
    document.getElementById('modalMaxStaff').value = maxStf;
    document.getElementById('modalAiEnabled').checked = aiEnabled;

    document.getElementById('planModal').classList.remove('hidden');
}

function closePlanModal() {
    document.getElementById('planModal').classList.add('hidden');
}
</script>
@endsection
