@extends('layouts.app')

@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">

    {{-- Breadcrumb & Header --}}
    <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
        <div>
            <nav class="text-xs text-slate-400 mb-1">
                <a href="{{ url('home') }}" class="hover:text-indigo-600">Dashboard</a>
                <span class="mx-1">/</span>
                <span class="text-slate-600">Front Desk & Visitor Management</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
                <i class="bi bi-person-badge text-indigo-600"></i> Front Desk / Visitor Log (भ्रमणकर्ता व्यवस्थापन)
            </h1>
            <p class="text-slate-400 text-sm mt-0.5">Track gate entries, visitor badges, meeting appointments & security check-outs</p>
        </div>

        <button onclick="document.getElementById('checkInModal').classList.remove('hidden')"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
            <i class="bi bi-plus-circle"></i> Check-In New Visitor
        </button>
    </div>

    @include('session-messages')

    {{-- Metric Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="bi bi-people"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Today's Total</p>
                <h3 class="text-2xl font-bold text-slate-800">{{ $stats['today_total'] }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="bi bi-geo-alt"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Currently on Campus</p>
                <h3 class="text-2xl font-bold text-amber-600">{{ $stats['active_now'] }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="bi bi-check2-circle"></i>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Checked Out Today</p>
                <h3 class="text-2xl font-bold text-emerald-600">{{ $stats['checked_out'] }}</h3>
            </div>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-6">
        <form method="GET" action="{{ route('visitors.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Status</label>
                <select name="status" class="w-full text-xs rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Currently Active (On Campus)</option>
                    <option value="checked_out" {{ $status === 'checked_out' ? 'selected' : '' }}>Checked Out</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Date</label>
                <input type="date" name="date" value="{{ $date }}"
                       class="w-full text-xs rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Search</label>
                <div class="relative">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Name, phone, badge, person..."
                           class="w-full text-xs rounded-lg border-slate-200 pl-8 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-lg transition flex items-center justify-center gap-1.5">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('visitors.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-lg transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Visitors Table --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-400 uppercase font-semibold text-[10px] tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Badge / Pass #</th>
                        <th class="py-3 px-4">Visitor Info</th>
                        <th class="py-3 px-4">Purpose</th>
                        <th class="py-3 px-4">Person to Meet</th>
                        <th class="py-3 px-4">Check-In</th>
                        <th class="py-3 px-4">Check-Out</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($visitors as $visitor)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 font-mono font-bold text-indigo-600">
                            {{ $visitor->badge_number }}
                        </td>
                        <td class="py-3 px-4">
                            <p class="font-bold text-slate-800">{{ $visitor->name }}</p>
                            <p class="text-[11px] text-slate-400"><i class="bi bi-telephone me-1"></i>{{ $visitor->phone }}</p>
                            @if($visitor->organization)
                                <p class="text-[10px] text-slate-400"><i class="bi bi-building me-1"></i>{{ $visitor->organization }}</p>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-700">
                                {{ ucwords(str_replace('_', ' ', $visitor->purpose)) }}
                            </span>
                            @if($visitor->notes)
                                <p class="text-[10px] text-slate-400 mt-1 line-clamp-1">{{ $visitor->notes }}</p>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-medium text-slate-700">
                            <i class="bi bi-person me-1 text-slate-400"></i>{{ $visitor->person_to_meet }}
                        </td>
                        <td class="py-3 px-4">
                            <p class="font-medium text-slate-700">{{ $visitor->check_in_at->format('h:i A') }}</p>
                            <p class="text-[10px] text-slate-400">{{ $visitor->check_in_at->format('M d, Y') }}</p>
                        </td>
                        <td class="py-3 px-4">
                            @if($visitor->check_out_at)
                                <p class="font-medium text-slate-700">{{ $visitor->check_out_at->format('h:i A') }}</p>
                                <p class="text-[10px] text-slate-400">{{ $visitor->check_out_at->format('M d, Y') }}</p>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @if($visitor->status === 'active')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> On Campus
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                    <i class="bi bi-check2"></i> Checked Out
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right space-x-1 whitespace-nowrap">
                            <a href="{{ route('visitors.badge', $visitor->id) }}" target="_blank"
                               class="inline-flex items-center gap-1 px-2 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-medium rounded text-[11px] transition" title="Print Visitor Pass">
                                <i class="bi bi-printer"></i> Pass
                            </a>

                            @if($visitor->status === 'active')
                            <form action="{{ route('visitors.checkout', $visitor->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white font-medium rounded text-[11px] transition">
                                    <i class="bi bi-box-arrow-right"></i> Check Out
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-12 text-slate-400">
                            <i class="bi bi-person-x text-4xl block mb-2 text-slate-300"></i>
                            No visitor records found for this criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($visitors->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $visitors->links() }}
        </div>
        @endif
    </div>

</div>

{{-- Check-In Visitor Modal --}}
<div id="checkInModal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <div class="px-6 py-4 bg-indigo-600 text-white flex justify-between items-center">
            <h3 class="font-bold text-base flex items-center gap-2">
                <i class="bi bi-person-plus"></i> Check-In New Visitor (नयाँ भ्रमणकर्ता)
            </h3>
            <button onclick="document.getElementById('checkInModal').classList.add('hidden')" class="text-white/80 hover:text-white text-xl">
                &times;
            </button>
        </div>

        <form action="{{ route('visitors.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2 sm:col-span-1">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Full Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Ram Bahadur Thapa"
                           class="w-full text-xs rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Phone Number *</label>
                    <input type="text" name="phone" required placeholder="98XXXXXXXX"
                           class="w-full text-xs rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">ID Type & Number</label>
                    <div class="flex gap-2">
                        <select name="id_type" class="w-1/2 text-xs rounded-lg border-slate-200">
                            <option value="citizenship">Citizenship</option>
                            <option value="national_id">National ID</option>
                            <option value="license">Driving License</option>
                            <option value="other">Other</option>
                        </select>
                        <input type="text" name="id_number" placeholder="ID #" class="w-1/2 text-xs rounded-lg border-slate-200">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Organization / Company</label>
                    <input type="text" name="organization" placeholder="e.g. Apex Suppliers / Parent"
                           class="w-full text-xs rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Purpose of Visit *</label>
                    <select name="purpose" required class="w-full text-xs rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="admission_inquiry">Admission Inquiry</option>
                        <option value="fee_payment">Fee Payment / Accounts</option>
                        <option value="meeting_principal">Meeting with Principal</option>
                        <option value="meeting_teacher">Meeting with Teacher</option>
                        <option value="vendor_supply">Vendor / Supply Delivery</option>
                        <option value="maintenance">Maintenance / Repair</option>
                        <option value="other">Other Official</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Person to Meet *</label>
                    <input type="text" name="person_to_meet" required placeholder="e.g. Principal / Mr. Sharma / Aayush (Roll 5)"
                           class="w-full text-xs rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Remarks / Items Carried</label>
                <textarea name="notes" rows="2" placeholder="e.g. Carried laptop / document folder"
                          class="w-full text-xs rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('checkInModal').classList.add('hidden')"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-lg transition">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition shadow-sm">
                    Complete Check-In & Issue Pass
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
