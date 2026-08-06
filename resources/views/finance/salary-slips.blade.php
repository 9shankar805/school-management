@extends('layouts.app')
@section('title', 'Staff Salary Slips')

@section('content')
<div class="px-6 py-5 max-w-7xl mx-auto">
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2 tracking-tight">
                <i class="bi bi-receipt text-indigo-500"></i> Staff Salary Slips
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
                            <span class="text-slate-700 dark:text-slate-300 font-medium">Salary Slips</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="document.getElementById('addSlipModal').classList.remove('hidden')" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition shadow-sm">
                <i class="bi bi-plus-lg"></i> Generate Slip
            </button>
        </div>
    </div>

    @if(session('status'))
    <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 flex items-center gap-2">
        <i class="bi bi-check-circle text-emerald-600"></i>
        <span class="text-sm text-emerald-800 dark:text-emerald-200">{{ session('status') }}</span>
    </div>
    @endif

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl p-5 border border-emerald-100 dark:border-emerald-800/50 flex flex-col items-center justify-center text-center">
            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">Rs. {{ number_format($totalPaid, 2) }}</div>
            <div class="text-xs font-semibold text-emerald-800/70 dark:text-emerald-300 uppercase tracking-wide mt-1">Total Paid</div>
        </div>
        <div class="bg-amber-50 dark:bg-amber-900/20 rounded-2xl p-5 border border-amber-100 dark:border-amber-800/50 flex flex-col items-center justify-center text-center">
            <div class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ $slips->where('status','draft')->count() }}</div>
            <div class="text-xs font-semibold text-amber-800/70 dark:text-amber-300 uppercase tracking-wide mt-1">Draft Slips</div>
        </div>
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-2xl p-5 border border-blue-100 dark:border-blue-800/50 flex flex-col items-center justify-center text-center">
            <div class="text-2xl font-black text-blue-600 dark:text-blue-400">{{ $slips->count() }}</div>
            <div class="text-xs font-semibold text-blue-800/70 dark:text-blue-300 uppercase tracking-wide mt-1">Total Slips</div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-sm p-4 mb-6">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div class="w-40">
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Month</label>
                <select name="month" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-900/50 text-slate-800 dark:text-slate-100">
                    @for($m=1;$m<=12;$m++)
                        <option value="{{ $m }}" @selected($m==$month)>{{ Carbon\Carbon::create()->month($m)->format('F') }}</option>
                    @endfor
                </select>
            </div>
            <div class="w-32">
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Year</label>
                <input type="number" name="year" value="{{ $year }}" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-900/50 text-slate-800 dark:text-slate-100">
            </div>
            <button class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition border border-slate-200">
                <i class="bi bi-funnel"></i> Filter
            </button>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Staff Member</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Gross</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Deductions</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Net Salary</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($slips as $slip)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ $slip->staff->fullName }}</div>
                            <div class="text-xs text-slate-500">{{ ucfirst($slip->staff->primaryRole) }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-right text-slate-600 dark:text-slate-400">Rs. {{ number_format($slip->gross_salary, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-right text-red-500 font-medium">
                            -Rs. {{ number_format($slip->tax_deduction + $slip->provident_fund + $slip->other_deductions, 2) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-right font-bold text-slate-900 dark:text-white">Rs. {{ number_format($slip->net_salary, 2) }}</td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $badgeClass = match($slip->status) {
                                    'paid' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
                                    'draft' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
                                    default => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $badgeClass }}">
                                {{ ucfirst($slip->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <a href="{{ route('finance.salary.pdf', $slip->id) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium text-slate-600 hover:bg-slate-100 transition" title="PDF">
                                <i class="bi bi-file-pdf"></i>
                            </a>
                            
                            @if($slip->status === 'draft')
                            <form method="POST" action="{{ route('finance.salary.approve', $slip->id) }}" class="inline-block">
                                @csrf @method('PATCH')
                                <button type="submit" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium text-blue-600 hover:bg-blue-50 transition" title="Approve">
                                    <i class="bi bi-check-circle"></i> Approve
                                </button>
                            </form>
                            @endif
                            
                            @if($slip->status !== 'paid')
                            <form method="POST" action="{{ route('finance.salary.paid', $slip->id) }}" class="inline-block">
                                @csrf @method('PATCH')
                                <button type="submit" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium text-emerald-600 hover:bg-emerald-50 transition" title="Mark Paid">
                                    <i class="bi bi-cash"></i> Pay
                                </button>
                            </form>
                            @endif
                            
                            <form method="POST" action="{{ route('finance.salary.destroy', $slip->id) }}" class="inline-block" onsubmit="return confirm('Delete this slip?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium text-red-600 hover:bg-red-50 transition">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                            <i class="bi bi-receipt text-4xl mb-2 block text-slate-300 dark:text-slate-600"></i>
                            <span class="text-sm">No salary slips for this period.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Slip Modal --}}
<div id="addSlipModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="document.getElementById('addSlipModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="relative inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-slate-200 dark:border-slate-700">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Generate Salary Slip</h3>
                <button type="button" class="text-slate-400 hover:text-slate-500" onclick="document.getElementById('addSlipModal').classList.add('hidden')"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="p-6">
                <form method="POST" action="{{ route('finance.salary.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-6">
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Staff Member <span class="text-red-500">*</span></label>
                            <select name="staff_id" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-900/50 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-400 focus:bg-white focus:outline-none transition-all" required>
                                <option value="">-- Select Staff --</option>
                                @foreach($staff as $s)
                                    <option value="{{ $s->id }}">{{ $s->fullName }} ({{ ucfirst($s->primaryRole) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Month <span class="text-red-500">*</span></label>
                            <select name="month" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-900/50 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-400 focus:bg-white focus:outline-none transition-all" required>
                                @for($m=1;$m<=12;$m++)
                                    <option value="{{ $m }}" @selected($m==$month)>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Year <span class="text-red-500">*</span></label>
                            <input type="number" name="year" value="{{ $year }}" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-900/50 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-400 focus:bg-white focus:outline-none transition-all" required>
                        </div>
                        
                        <div class="md:col-span-12 my-2 border-b border-slate-200 dark:border-slate-700"></div>
                        
                        <div class="md:col-span-4">
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Basic Salary <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500 text-sm">$</span>
                                <input type="number" name="basic_salary" step="0.01" min="0" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg pl-8 pr-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-400 focus:outline-none" required>
                            </div>
                        </div>
                        <div class="md:col-span-4">
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Allowances</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500 text-sm">$</span>
                                <input type="number" name="allowances" step="0.01" min="0" value="0" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg pl-8 pr-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                            </div>
                        </div>
                        <div class="md:col-span-4">
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Overtime</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500 text-sm">$</span>
                                <input type="number" name="overtime" step="0.01" min="0" value="0" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg pl-8 pr-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                            </div>
                        </div>
                        <div class="md:col-span-4">
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Tax Deduction</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500 text-sm">$</span>
                                <input type="number" name="tax_deduction" step="0.01" min="0" value="0" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg pl-8 pr-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-red-400 focus:outline-none border-red-200 dark:border-red-900/50">
                            </div>
                        </div>
                        <div class="md:col-span-4">
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Provident Fund</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500 text-sm">$</span>
                                <input type="number" name="provident_fund" step="0.01" min="0" value="0" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg pl-8 pr-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-red-400 focus:outline-none border-red-200 dark:border-red-900/50">
                            </div>
                        </div>
                        <div class="md:col-span-4">
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Other Deductions</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500 text-sm">$</span>
                                <input type="number" name="other_deductions" step="0.01" min="0" value="0" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg pl-8 pr-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-red-400 focus:outline-none border-red-200 dark:border-red-900/50">
                            </div>
                        </div>
                        
                        <div class="md:col-span-12 my-2 border-b border-slate-200 dark:border-slate-700"></div>

                        <div class="md:col-span-6">
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Payment Method</label>
                            <select name="payment_method" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-900/50 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-400 focus:bg-white focus:outline-none transition-all">
                                <option value="">-- Select --</option>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                        <div class="md:col-span-12">
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Notes</label>
                            <textarea name="notes" rows="2" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-900/50 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-400 focus:bg-white focus:outline-none transition-all"></textarea>
                        </div>
                    </div>
                    <div class="pt-4 flex justify-end gap-3 border-t border-slate-100 dark:border-slate-700/60 mt-6">
                        <button type="button" onclick="document.getElementById('addSlipModal').classList.add('hidden')" class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition shadow-sm">Save Salary Slip</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
