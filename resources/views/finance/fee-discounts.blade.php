@extends('layouts.app')
@section('title', 'Fee Discounts')

@section('content')
<div class="px-6 py-5 max-w-7xl mx-auto">
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2 tracking-tight">
                <i class="bi bi-percent text-purple-500"></i> Fee Discounts
            </h1>
            <nav class="flex text-xs text-slate-500 dark:text-slate-400 mt-1" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-2">
                    <li class="inline-flex items-center">
                        <a href="{{ route('home') }}" class="hover:text-blue-600">Home</a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <span class="mx-2">/</span>
                            <a href="{{ route('finance.overview') }}" class="hover:text-blue-600">Finance</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <span class="mx-2">/</span>
                            <span class="text-slate-700 dark:text-slate-300 font-medium">Discounts</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    @if(session('status'))
    <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 flex items-center gap-2">
        <i class="bi bi-check-circle text-emerald-600"></i>
        <span class="text-sm text-emerald-800 dark:text-emerald-200">{{ session('status') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Forms Side --}}
        <div class="lg:col-span-1 space-y-6">
            
            {{-- Define discount --}}
            <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/60">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="bi bi-plus-circle text-purple-500"></i> Define Discount
                    </h3>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ route('finance.discounts.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-purple-400 focus:outline-none" required placeholder="e.g. Sibling Discount">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Type</label>
                            <select name="type" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-purple-400 focus:outline-none">
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount (NPR)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Value <span class="text-red-500">*</span></label>
                            <input type="number" name="value" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-purple-400 focus:outline-none" step="0.01" min="0" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Description</label>
                            <textarea name="description" rows="2" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-purple-400 focus:outline-none"></textarea>
                        </div>
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-purple-600 hover:bg-purple-700 transition">
                            <i class="bi bi-plus-circle"></i> Create Discount
                        </button>
                    </form>
                </div>
            </div>

            {{-- Assign discount --}}
            <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/60">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="bi bi-person-check text-emerald-500"></i> Assign to Student
                    </h3>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ route('finance.discounts.assign') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Student <span class="text-red-500">*</span></label>
                            <select name="student_id" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
                                <option value="">— Select —</option>
                                @foreach($students as $s)<option value="{{ $s->id }}">{{ $s->first_name }} {{ $s->last_name }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Discount <span class="text-red-500">*</span></label>
                            <select name="discount_id" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-400 focus:outline-none" required>
                                <option value="">— Select —</option>
                                @foreach($discounts->where('is_active',true) as $d)<option value="{{ $d->id }}">{{ $d->name }} ({{ $d->display_value }})</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Session</label>
                            <select name="session_id" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                                <option value="">All Sessions</option>
                                @foreach($sessions as $ss)<option value="{{ $ss->id }}">{{ $ss->session_name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Valid From</label>
                                <input type="date" name="valid_from" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Valid To</label>
                                <input type="date" name="valid_to" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                            </div>
                        </div>
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition">
                            <i class="bi bi-person-check"></i> Assign Discount
                        </button>
                    </form>
                </div>
            </div>

        </div>

        {{-- Tables Side --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Discount types --}}
            <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/60">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        Available Discounts
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Name</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Value</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Students</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse($discounts as $d)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                                <td class="px-4 py-3 text-sm font-semibold text-slate-900 dark:text-white">{{ $d->name }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">{{ ucfirst($d->type) }}</td>
                                <td class="px-4 py-3 text-sm font-bold text-purple-600 dark:text-purple-400">{{ $d->display_value }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center justify-center px-2 py-1 rounded text-xs font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                        {{ $d->student_discounts_count }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $d->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300' }}">
                                        {{ $d->is_active ? 'Active' : 'Off' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <form method="POST" action="{{ route('finance.discounts.destroy', $d->id) }}" class="inline" onsubmit="return confirm('Delete this discount?')">@csrf @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium text-red-600 hover:bg-red-50 transition">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400 text-sm">
                                    No discounts defined yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Assigned discounts --}}
            <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/60">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        Student Assignments
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Student</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Discount</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Session</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Valid</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse($studentDiscounts as $sd)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                                <td class="px-4 py-3 text-sm font-semibold text-slate-900 dark:text-white">{{ $sd->student->first_name }} {{ $sd->student->last_name }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400">{{ $sd->discount->name }} <span class="font-bold text-purple-600 dark:text-purple-400">({{ $sd->discount->display_value }})</span></td>
                                <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">{{ $sd->session?->session_name ?? 'All' }}</td>
                                <td class="px-4 py-3 text-[10px] text-slate-500 dark:text-slate-400 whitespace-nowrap">{{ $sd->valid_from?->format('d M Y') ?? '—' }} <br>to<br> {{ $sd->valid_to?->format('d M Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <form method="POST" action="{{ route('finance.discounts.revoke', $sd->id) }}" class="inline" onsubmit="return confirm('Revoke this discount?')">@csrf @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium text-amber-600 hover:bg-amber-50 transition" title="Revoke">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400 text-sm">
                                    No student assignments.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
