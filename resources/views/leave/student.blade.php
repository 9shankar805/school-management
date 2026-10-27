@extends('layouts.app')

@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">

    {{-- Page Header --}}
    <div class="mb-7 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight"><i class="bi bi-calendar-x-fill me-2 text-violet-600"></i>Leave Requests</h1>
            <p class="text-slate-400 text-sm mt-0.5">Apply for student leave and view your leave application status</p>
        </div>
        <button onclick="document.getElementById('modalApplyLeave').classList.remove('hidden')"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-xs transition flex items-center gap-2 self-start md:self-auto">
            <i class="bi bi-plus-circle"></i> Apply for Leave
        </button>
    </div>
    @include('session-messages')

    {{-- Auto-open modal if coming from dashboard apply link --}}
    @if(request()->query('open') === 'apply')
    <script>document.addEventListener('DOMContentLoaded', () => document.getElementById('modalApplyLeave').classList.remove('hidden'));</script>
    @endif

    {{-- Leave Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Applied</p>
                <p class="text-3xl font-bold text-slate-800 mt-1">{{ $applications->total() }}</p>
            </div>
            <span class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-file-earmark-text"></i>
            </span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Approved</p>
                <p class="text-3xl font-bold text-emerald-600 mt-1">{{ $applications->where('status', 'approved')->count() }}</p>
            </div>
            <span class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-check-circle-fill"></i>
            </span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Pending Review</p>
                <p class="text-3xl font-bold text-amber-600 mt-1">{{ $applications->where('status', 'pending')->count() }}</p>
            </div>
            <span class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                <i class="bi bi-hourglass-split"></i>
            </span>
        </div>
    </div>

    {{-- Applications List --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-sm font-semibold text-slate-800">My Leave Applications</h2>
        </div>

        @if($applications->isEmpty())
            <div class="p-10 text-center text-slate-400 text-sm">
                <i class="bi bi-calendar-x text-4xl text-slate-200 block mb-2"></i>
                No leave applications submitted yet.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50/80 text-xs text-slate-500 font-semibold uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3">Leave Type</th>
                            <th class="px-4 py-3">Duration</th>
                            <th class="px-4 py-3 text-center">Total Days</th>
                            <th class="px-5 py-3">Reason</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-slate-700">
                        @foreach($applications as $app)
                        @php
                            $badgeClass = match($app->status) {
                                'approved'  => 'bg-emerald-100 text-emerald-700',
                                'rejected'  => 'bg-rose-100 text-rose-700',
                                'cancelled' => 'bg-slate-100 text-slate-500',
                                default     => 'bg-amber-100 text-amber-700',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-5 py-3.5 font-semibold text-slate-800">{{ $app->leaveType?->name ?? 'General Leave' }}</td>
                            <td class="px-4 py-3.5 text-xs text-slate-600">
                                {{ \Carbon\Carbon::parse($app->from_date)->format('M d, Y') }} — {{ \Carbon\Carbon::parse($app->to_date)->format('M d, Y') }}
                            </td>
                            <td class="px-4 py-3.5 text-center font-semibold text-slate-700">{{ $app->total_days }} day(s)</td>
                            <td class="px-5 py-3.5 text-xs text-slate-600 max-w-xs truncate">{{ $app->reason }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="text-[10px] px-2.5 py-1 rounded-full font-semibold {{ $badgeClass }} capitalize">
                                    {{ $app->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if($app->status === 'pending')
                                    <form method="POST" action="{{ route('leave.cancel', $app->id) }}" onsubmit="return confirm('Cancel this leave application?')">
                                        @csrf
                                        <button type="submit" class="text-xs text-rose-600 hover:underline font-medium">Cancel</button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-slate-100">
                {{ $applications->links() }}
            </div>
        @endif
    </div>

</div>

{{-- Apply Leave Modal --}}
<div id="modalApplyLeave" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl relative">
        <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
            <h3 class="font-bold text-slate-800 text-base">Apply for Leave</h3>
            <button onclick="document.getElementById('modalApplyLeave').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('leave.apply.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Leave Type <span class="text-rose-500">*</span></label>
                <select name="leave_type_id" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="">— Select leave type —</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}">{{ $type->name }} ({{ $type->days_allowed }} days max)</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">From Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="from_date" required min="{{ date('Y-m-d') }}" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">To Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="to_date" required min="{{ date('Y-m-d') }}" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Reason <span class="text-rose-500">*</span></label>
                <textarea name="reason" rows="3" required placeholder="Explain reason for leave request…" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Attachment (Optional)</label>
                <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-xs text-slate-500">
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modalApplyLeave').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold transition">Submit Request</button>
            </div>
        </form>
    </div>
</div>
@endsection
