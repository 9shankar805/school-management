@extends('layouts.app')
@section('title', 'Expenses')

@section('content')
<div class="px-6 py-5 max-w-7xl mx-auto">
    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2 tracking-tight">
                <i class="bi bi-arrow-up-circle text-red-500"></i> Expenses
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
                            <span class="text-slate-700 dark:text-slate-300 font-medium">Expenses</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
        <div>
            <div class="bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 px-4 py-2 rounded-xl text-sm font-semibold border border-red-100 dark:border-red-900">
                This Month: Rs. {{ number_format($thisMonthTotal, 2) }}
            </div>
        </div>
    </div>

    @if(session('status'))
    <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 flex items-center gap-2">
        <i class="bi bi-check-circle text-emerald-600"></i>
        <span class="text-sm text-emerald-800 dark:text-emerald-200">{{ session('status') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Add form --}}
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/60">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="bi bi-plus-circle text-red-500"></i> Record Expense
                    </h3>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ route('finance.expenses.store') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Title <span class="text-red-500">*</span></label>
                            <input type="text" name="title" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-400 focus:outline-none" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Category <span class="text-red-500">*</span></label>
                            <input type="text" name="category" list="expenseCats" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-400 focus:outline-none" required placeholder="Utilities, Salaries…">
                            <datalist id="expenseCats">@foreach($categories as $c)<option value="{{ $c }}">@endforeach</datalist>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Amount (NPR) <span class="text-red-500">*</span></label>
                            <input type="number" name="amount" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-400 focus:outline-none" step="0.01" min="0.01" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Date <span class="text-red-500">*</span></label>
                            <input type="date" name="expense_date" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-400 focus:outline-none" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Payment Method</label>
                            <select name="payment_method" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                                <option value="cash">Cash</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Reference / Cheque No.</label>
                            <input type="text" name="reference" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Notes</label>
                            <textarea name="notes" rows="2" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-400 focus:outline-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Attachment</label>
                            <input type="file" name="attachment" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition">
                            <i class="bi bi-plus-circle"></i> Record Expense
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            {{-- Filter --}}
            <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-sm p-4">
                <form method="GET" action="{{ route('finance.expenses') }}" class="flex flex-wrap items-end gap-3">
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Search</label>
                        <input type="text" name="search" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-900/50" placeholder="Title..." value="{{ request('search') }}">
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Category</label>
                        <select name="category" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-900/50">
                            <option value="">All Categories</option>
                            @foreach($categories as $c)<option value="{{ $c }}" @selected(request('category')===$c)>{{ $c }}</option>@endforeach
                        </select>
                    </div>
                    <div class="w-32">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">From</label>
                        <input type="date" name="date_from" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-900/50" value="{{ request('date_from') }}">
                    </div>
                    <div class="w-32">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">To</label>
                        <input type="date" name="date_to" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-slate-50 dark:bg-slate-900/50" value="{{ request('date_to') }}">
                    </div>
                    <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition border border-slate-200">
                        <i class="bi bi-search"></i>
                    </button>
                    @if(request()->anyFilled(['search','category','date_from','date_to']))
                    <a href="{{ route('finance.expenses') }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-500 bg-slate-50 hover:bg-slate-100 transition border border-slate-200">
                        Clear
                    </a>
                    @endif
                </form>
            </div>

            <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Title & Category</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Method</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">By</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse($expenses as $exp)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-400 whitespace-nowrap">{{ $exp->expense_date->format('d M Y') }}</td>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ $exp->title }}</div>
                                    <span class="inline-flex items-center px-2 py-0.5 mt-1 rounded text-[10px] font-medium bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                        {{ $exp->category }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm font-bold text-red-600 dark:text-red-400 whitespace-nowrap">Rs. {{ number_format($exp->amount,2) }}</td>
                                <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">
                                    {{ ucfirst($exp->payment_method) }}
                                    @if($exp->reference)<div class="text-[10px] text-slate-400">Ref: {{ $exp->reference }}</div>@endif
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">{{ $exp->recordedBy->first_name ?? '—' }}</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    @if($exp->attachment)
                                    <a href="{{ Storage::url($exp->attachment) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium text-slate-600 hover:bg-slate-100 transition mr-1">
                                        <i class="bi bi-paperclip"></i>
                                    </a>
                                    @endif
                                    <button onclick="document.getElementById('editExp{{ $exp->id }}').classList.remove('hidden')" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium text-blue-600 hover:bg-blue-50 transition">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('finance.expenses.destroy', $exp->id) }}" class="inline" onsubmit="return confirm('Delete this expense?')">@csrf @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium text-red-600 hover:bg-red-50 transition">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">
                                    <i class="bi bi-inbox text-4xl mb-2 block text-slate-300 dark:text-slate-600"></i>
                                    <span class="text-sm">No expenses found.</span>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700">
                    {{ $expenses->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Modals --}}
    @foreach($expenses as $exp)
    <div id="editExp{{ $exp->id }}" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="document.getElementById('editExp{{ $exp->id }}').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="relative inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 dark:border-slate-700">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Edit Expense</h3>
                    <button type="button" class="text-slate-400 hover:text-slate-500" onclick="document.getElementById('editExp{{ $exp->id }}').classList.add('hidden')"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ route('finance.expenses.update', $exp->id) }}" class="space-y-4">
                        @csrf @method('PUT')
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Title</label>
                                <input type="text" name="title" value="{{ $exp->title }}" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 focus:ring-2 focus:ring-blue-400" required>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Amount (NPR)</label>
                                <input type="number" name="amount" value="{{ $exp->amount }}" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 focus:ring-2 focus:ring-blue-400" step="0.01" required>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Category</label>
                                <input type="text" name="category" value="{{ $exp->category }}" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 focus:ring-2 focus:ring-blue-400" required>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Date</label>
                                <input type="date" name="expense_date" value="{{ $exp->expense_date->format('Y-m-d') }}" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 focus:ring-2 focus:ring-blue-400" required>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Method</label>
                                <select name="payment_method" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 focus:ring-2 focus:ring-blue-400">
                                    <option value="cash" @selected($exp->payment_method==='cash')>Cash</option>
                                    <option value="bank" @selected($exp->payment_method==='bank')>Bank</option>
                                    <option value="cheque" @selected($exp->payment_method==='cheque')>Cheque</option>
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Reference</label>
                                <input type="text" name="reference" value="{{ $exp->reference }}" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 focus:ring-2 focus:ring-blue-400">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Notes</label>
                                <textarea name="notes" rows="2" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 focus:ring-2 focus:ring-blue-400">{{ $exp->notes }}</textarea>
                            </div>
                        </div>
                        <div class="pt-4 flex justify-end gap-3 border-t border-slate-100 dark:border-slate-700/60 mt-4">
                            <button type="button" onclick="document.getElementById('editExp{{ $exp->id }}').classList.add('hidden')" class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition">Cancel</button>
                            <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach

</div>
@endsection
