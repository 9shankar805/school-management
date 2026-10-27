@extends('layouts.app')

@section('title', 'Staff Contracts - NepalSchoolHub')

@section('content')
<div class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto space-y-6 bg-slate-50/60 dark:bg-slate-950 min-h-screen">

    {{-- Top Status Notifications --}}
    @if(session('status'))
    <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-200 rounded-2xl text-xs sm:text-sm font-semibold flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-2">
            <i class="bi bi-check-circle-fill text-emerald-600 dark:text-emerald-400"></i>
            <span>{{ session('status') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 dark:hover:text-emerald-300">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-600 text-white flex items-center justify-center shadow-lg shadow-indigo-500/25 flex-shrink-0 text-lg font-bold">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">Staff Contracts & Agreements</h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Manage faculty employment terms, renewal lifecycles, salaries, and agreement documents</p>
                </div>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" onclick="openNewContractModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm shadow-indigo-600/25 transition active:scale-95 cursor-pointer">
                <i class="bi bi-plus-circle-fill"></i> Add Contract
            </button>
            <a href="{{ route('staff.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <i class="bi bi-people"></i> Staff Directory
            </a>
        </div>
    </div>

    {{-- Expiry Alert Banner --}}
    @if(isset($expiryAlerts) && count($expiryAlerts) > 0)
    <div class="p-4 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 rounded-2xl flex items-start gap-3 shadow-xs">
        <div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 flex items-center justify-center flex-shrink-0 font-bold">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </div>
        <div class="flex-1 min-w-0">
            <h4 class="text-xs sm:text-sm font-bold text-amber-900 dark:text-amber-200">
                {{ count($expiryAlerts) }} Contract(s) Expiring Within 30 Days
            </h4>
            <div class="flex flex-wrap gap-2 mt-1.5">
                @foreach($expiryAlerts as $alert)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-white/80 dark:bg-slate-900/80 text-amber-800 dark:text-amber-300 border border-amber-300/60 dark:border-amber-700/60">
                    <span class="font-bold">{{ $alert->user->first_name ?? '' }} {{ $alert->user->last_name ?? '' }}</span>
                    <span class="text-[11px] text-amber-600 dark:text-amber-400">({{ $alert->end_date ? $alert->end_date->format('M d, Y') : 'N/A' }})</span>
                </span>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Search & Filter Controls --}}
    <form method="GET" action="{{ route('staff.contracts.index') }}" class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200/80 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search by staff name, position, or department…"
                   class="w-full pl-9 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
        </div>
        <div class="w-full sm:w-48">
            <select name="status" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                <option value="">All Statuses</option>
                <option value="active" @selected(request('status') === 'active')>🟢 Active</option>
                <option value="expired" @selected(request('status') === 'expired')>🔴 Expired</option>
                <option value="terminated" @selected(request('status') === 'terminated')>⚪ Terminated</option>
            </select>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs sm:text-sm font-bold shadow-sm shadow-indigo-600/20 transition active:scale-95 cursor-pointer">
                <i class="bi bi-funnel me-1"></i> Filter
            </button>
            @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('staff.contracts.index') }}" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs sm:text-sm font-semibold transition">
                Clear
            </a>
            @endif
        </div>
    </form>

    {{-- Contracts Data Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
        @if($contracts->count())
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-bold tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Staff Member</th>
                        <th class="px-5 py-3.5">Contract Type</th>
                        <th class="px-5 py-3.5">Position & Dept</th>
                        <th class="px-5 py-3.5">Gross Salary</th>
                        <th class="px-5 py-3.5">Duration</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($contracts as $c)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors {{ $c->is_expiring_soon ? 'bg-amber-50/40 dark:bg-amber-950/20' : '' }}">
                        <td class="px-5 py-3.5">
                            <div class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm">{{ $c->user->first_name ?? '' }} {{ $c->user->last_name ?? '' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $c->user->email ?? 'No email' }}</div>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                {{ $c->contract_type }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $c->position ?? 'General Staff' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $c->department ?? 'General' }}</div>
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap font-mono font-bold text-slate-900 dark:text-white">
                            Rs. {{ number_format($c->gross_salary, 2) }}
                            @if($c->allowances > 0)
                            <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-normal">+ Rs. {{ number_format($c->allowances, 2) }} allow.</div>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <div class="font-medium text-slate-700 dark:text-slate-300">
                                {{ $c->start_date ? $c->start_date->format('M d, Y') : '—' }} &rarr; 
                                <span class="{{ $c->is_expired ? 'text-rose-600 font-bold' : '' }}">
                                    {{ $c->end_date ? $c->end_date->format('M d, Y') : 'Permanent' }}
                                </span>
                            </div>
                            @if($c->is_expiring_soon)
                            <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 flex items-center gap-1 mt-0.5">
                                <i class="bi bi-clock-history"></i> Expiring Soon
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 whitespace-nowrap text-center">
                            @if($c->status === 'active')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/80">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                            </span>
                            @elseif($c->status === 'expired')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/80">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Expired
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                Terminated
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-1.5">
                            @if($c->document_path)
                            <a href="{{ Storage::url($c->document_path) }}" target="_blank" class="px-2.5 py-1 text-xs font-semibold text-sky-600 dark:text-sky-400 hover:text-white bg-sky-50 hover:bg-sky-600 dark:bg-sky-950/50 dark:hover:bg-sky-600 border border-sky-200 dark:border-sky-800 rounded-lg transition" title="View Document">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </a>
                            @endif
                            <button type="button" onclick="openEditContractModal({{ $c->id }})" class="px-2.5 py-1 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-white bg-indigo-50 hover:bg-indigo-600 dark:bg-indigo-950/50 dark:hover:bg-indigo-600 border border-indigo-200 dark:border-indigo-800 rounded-lg transition cursor-pointer" title="Edit Contract">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="{{ route('staff.contracts.destroy', $c->id) }}" class="inline" onsubmit="return confirm('Delete this contract record?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2.5 py-1 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:text-white bg-rose-50 hover:bg-rose-600 dark:bg-rose-950/50 dark:hover:bg-rose-600 border border-rose-200 dark:border-rose-800 rounded-lg transition cursor-pointer" title="Delete Contract">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    {{-- Edit Modal for each contract --}}
                    <div id="editContractModal{{ $c->id }}" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4 hidden">
                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-xl w-full p-6 shadow-2xl space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                                        <i class="bi bi-pencil-square"></i>
                                    </div>
                                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Edit Contract &bull; {{ $c->user->first_name ?? '' }} {{ $c->user->last_name ?? '' }}</h3>
                                </div>
                                <button type="button" onclick="closeEditContractModal({{ $c->id }})" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>

                            <form method="POST" action="{{ route('staff.contracts.update', $c->id) }}" class="space-y-3.5">
                                @csrf
                                @method('PUT')
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Contract Type</label>
                                        <input type="text" name="contract_type" value="{{ $c->contract_type }}" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white outline-hidden focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Status</label>
                                        <select name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white outline-hidden focus:ring-2 focus:ring-indigo-500">
                                            <option value="active" @selected($c->status === 'active')>Active</option>
                                            <option value="expired" @selected($c->status === 'expired')>Expired</option>
                                            <option value="terminated" @selected($c->status === 'terminated')>Terminated</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Position / Role</label>
                                        <input type="text" name="position" value="{{ $c->position }}" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white outline-hidden focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Department</label>
                                        <input type="text" name="department" value="{{ $c->department }}" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white outline-hidden focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Start Date</label>
                                        <input type="date" name="start_date" value="{{ $c->start_date ? $c->start_date->format('Y-m-d') : '' }}" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white outline-hidden focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">End Date</label>
                                        <input type="date" name="end_date" value="{{ $c->end_date ? $c->end_date->format('Y-m-d') : '' }}" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white outline-hidden focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Base Salary (Rs.)</label>
                                        <input type="number" step="0.01" min="0" name="salary" value="{{ $c->salary }}" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white outline-hidden focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Allowances (Rs.)</label>
                                        <input type="number" step="0.01" min="0" name="allowances" value="{{ $c->allowances }}" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white outline-hidden focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                </div>

                                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                                    <button type="button" onclick="closeEditContractModal({{ $c->id }})" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition cursor-pointer">
                                        Cancel
                                    </button>
                                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-sm transition cursor-pointer">
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($contracts->hasPages())
        <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            {{ $contracts->links() }}
        </div>
        @endif
        @else
        <div class="text-center py-16 px-4">
            <div class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mx-auto mb-3 text-2xl font-bold">
                <i class="bi bi-file-earmark-person"></i>
            </div>
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">No Staff Contracts Found</h3>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 max-w-sm mx-auto">Create and record employee agreements to track contract durations, salaries, and renewals.</p>
            <button type="button" onclick="openNewContractModal()" class="mt-4 inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-sm transition cursor-pointer">
                <i class="bi bi-plus-circle-fill"></i> Add First Contract
            </button>
        </div>
        @endif
    </div>

</div>

{{-- Add Contract Modal --}}
<div id="newContractModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4 hidden transition-opacity duration-200" onclick="if(event.target === this) closeNewContractModal()">
    <div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-3xl max-w-xl w-full p-6 sm:p-7 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-600 text-white flex items-center justify-center text-lg font-bold shadow-md shadow-indigo-500/25">
                    <i class="bi bi-file-earmark-plus"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight">Create Staff Contract</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Record terms of employment, compensation, and duration</p>
                </div>
            </div>
            <button type="button" onclick="closeNewContractModal()" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('staff.contracts.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                    Staff Member <span class="text-rose-500">*</span>
                </label>
                <select name="user_id" required class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800 text-slate-900 dark:text-white outline-hidden focus:ring-2 focus:ring-indigo-500">
                    <option value="">— Select Staff Member —</option>
                    @foreach(\App\Models\User::whereHas('roles', fn($q) => $q->whereNotIn('name', ['student', 'parent']))->orderBy('first_name')->get() as $u)
                        <option value="{{ $u->id }}">{{ $u->first_name }} {{ $u->last_name }} ({{ $u->email }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        Contract Type <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="contract_type" required placeholder="e.g. Permanent, Probation, Contractual" 
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800 text-slate-900 dark:text-white outline-hidden focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Position / Job Title</label>
                    <input type="text" name="position" placeholder="e.g. Senior Secondary Teacher" 
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800 text-slate-900 dark:text-white outline-hidden focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Department</label>
                    <input type="text" name="department" placeholder="e.g. Science / Administration" 
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800 text-slate-900 dark:text-white outline-hidden focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        Base Salary (Rs.) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" step="0.01" min="0" name="salary" required placeholder="e.g. 45000" 
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800 text-slate-900 dark:text-white outline-hidden focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        Start Date <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="start_date" required 
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800 text-slate-900 dark:text-white outline-hidden focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">End Date <span class="text-slate-400 font-normal">(Leave empty if permanent)</span></label>
                    <input type="date" name="end_date" 
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800 text-slate-900 dark:text-white outline-hidden focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Allowances / Benefits (Rs.)</label>
                    <input type="number" step="0.01" min="0" name="allowances" value="0" 
                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800 text-slate-900 dark:text-white outline-hidden focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Agreement Document (PDF)</label>
                    <input type="file" name="document" accept=".pdf" 
                        class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800 text-slate-900 dark:text-white file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeNewContractModal()" class="px-4 py-2.5 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 rounded-xl shadow-md shadow-indigo-600/30 transition cursor-pointer">
                    <i class="bi bi-check-circle-fill"></i> Save Contract
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openNewContractModal() {
    const modal = document.getElementById('newContractModal');
    if (modal) modal.classList.remove('hidden');
}
function closeNewContractModal() {
    const modal = document.getElementById('newContractModal');
    if (modal) modal.classList.add('hidden');
}
function openEditContractModal(id) {
    const modal = document.getElementById('editContractModal' + id);
    if (modal) modal.classList.remove('hidden');
}
function closeEditContractModal(id) {
    const modal = document.getElementById('editContractModal' + id);
    if (modal) modal.classList.add('hidden');
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeNewContractModal();
        document.querySelectorAll('[id^="editContractModal"]').forEach(m => m.classList.add('hidden'));
    }
});
</script>
@endsection
