@extends('layouts.app')
@section('title', 'Fee Structures')

@section('content')
<div class="px-6 py-5 max-w-7xl mx-auto">

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2 tracking-tight">
                <i class="bi bi-table text-blue-600"></i> Fee Structures
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Manage fee amounts per class and session</p>
        </div>
        <a href="{{ route('finance.overview') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-blue-400 transition">
            <i class="bi bi-arrow-left"></i> Back to Finance
        </a>
    </div>

    @if(session('status'))
    <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800">
        <div class="flex items-center gap-2">
            <i class="bi bi-check-circle text-emerald-600"></i>
            <span class="text-sm text-emerald-800 dark:text-emerald-200">{{ session('status') }}</span>
        </div>
    </div>
    @endif

    {{-- Add Form --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="bi bi-plus-circle text-blue-500"></i> Add Fee Structure
            </h3>
            <button type="button" id="toggleAddForm" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 transition">
                <i class="bi bi-plus-lg"></i> Add
            </button>
        </div>
        <div id="addForm" class="hidden px-6 py-4 border-b border-slate-100 dark:border-slate-700/60">
            <form method="POST" action="{{ route('finance.structures.store') }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Category <span class="text-red-500">*</span></label>
                        <select name="category_id" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                            <option value="">— Select —</option>
                            @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Amount (NPR) <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400" step="0.01" min="0" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Frequency</label>
                        <select name="frequency" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400">
                            @foreach(['once'=>'One-time','monthly'=>'Monthly','quarterly'=>'Quarterly','half_yearly'=>'Half-Yearly','yearly'=>'Yearly'] as $v=>$l)
                                <option value="{{ $v }}">{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Session</label>
                        <select name="session_id" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <option value="">All Sessions</option>
                            @foreach($sessions as $s)<option value="{{ $s->id }}">{{ $s->session_name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Class</label>
                        <select name="class_id" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <option value="">All Classes</option>
                            @foreach($classes as $c)<option value="{{ $c->id }}">{{ $c->class_name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Due Date</label>
                        <input type="date" name="due_date" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 transition">
                            <i class="bi bi-check-circle"></i> Save
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('toggleAddForm').addEventListener('click', function() {
            var form = document.getElementById('addForm');
            form.classList.toggle('hidden');
        });
    </script>

    {{-- Filter --}}
    <form method="GET" action="{{ route('finance.structures') }}" class="mb-6 flex flex-wrap gap-3">
        <div class="flex-1 min-w-[200px]">
            <select name="session_id" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400" onchange="this.form.submit()">
                <option value="">All Sessions</option>
                @foreach($sessions as $s)<option value="{{ $s->id }}" @selected(request('session_id')==$s->id)>{{ $s->session_name }}</option>@endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[200px]">
            <select name="class_id" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400" onchange="this.form.submit()">
                <option value="">All Classes</option>
                @foreach($classes as $c)<option value="{{ $c->id }}" @selected(request('class_id')==$c->id)>{{ $c->class_name }}</option>@endforeach
            </select>
        </div>
        @if(request()->hasAny(['session_id','class_id']))
        <a href="{{ route('finance.structures') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-blue-400 transition">
            <i class="bi bi-x-circle"></i> Clear
        </a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Session</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Class</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Frequency</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($structures as $s)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                        <td class="px-6 py-4 text-sm font-semibold text-slate-900 dark:text-white">{{ $s->name }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $s->category?->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">{{ $s->session?->session_name ?? 'All' }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">{{ $s->schoolClass?->class_name ?? 'All' }}</td>
                        <td class="px-6 py-4 text-sm font-semibold text-slate-900 dark:text-white">Rs. {{ number_format($s->amount,2) }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $s->frequency_label }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $s->due_date?->format('d M Y') ?? '—' }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $s->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300' }}">
                                {{ $s->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button onclick="document.getElementById('editStr{{ $s->id }}').classList.remove('hidden')" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <form method="POST" action="{{ route('finance.structures.destroy', $s->id) }}" class="inline" onsubmit="return confirm('Delete this fee structure?')">@csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                            <i class="bi bi-inbox text-4xl mb-2 block text-slate-300 dark:text-slate-600"></i>
                            <span class="text-sm">No fee structures yet.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
            {{ $structures->links() }}
        </div>
    </div>

    {{-- Edit Modal --}}
    @forelse($structures as $s)
    <div id="editStr{{ $s->id }}" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="document.getElementById('editStr{{ $s->id }}').classList.add('hidden')"></div>
            <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-xl max-w-2xl w-full p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Edit: {{ $s->name }}</h3>
                    <button onclick="document.getElementById('editStr{{ $s->id }}').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i class="bi bi-x-lg text-xl"></i>
                    </button>
                </div>
                <form method="POST" action="{{ route('finance.structures.update', $s->id) }}">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Name</label>
                            <input type="text" name="name" value="{{ $s->name }}" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Category</label>
                            <select name="category_id" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                                @foreach($categories as $c)<option value="{{ $c->id }}" @selected($s->category_id==$c->id)>{{ $c->name }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Amount (NPR)</label>
                            <input type="number" name="amount" value="{{ $s->amount }}" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400" step="0.01" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Frequency</label>
                            <select name="frequency" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                @foreach(['once'=>'One-time','monthly'=>'Monthly','quarterly'=>'Quarterly','half_yearly'=>'Half-Yearly','yearly'=>'Yearly'] as $v=>$l)
                                    <option value="{{ $v }}" @selected($s->frequency===$v)>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Due Date</label>
                            <input type="date" name="due_date" value="{{ $s->due_date?->format('Y-m-d') }}" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Session</label>
                            <select name="session_id" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <option value="">All</option>
                                @foreach($sessions as $ss)<option value="{{ $ss->id }}" @selected($s->session_id==$ss->id)>{{ $ss->session_name }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Class</label>
                            <select name="class_id" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <option value="">All</option>
                                @foreach($classes as $c)<option value="{{ $c->id }}" @selected($s->class_id==$c->id)>{{ $c->class_name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="flex items-center pt-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" @checked($s->is_active) class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-slate-700 dark:text-slate-300">Active</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-slate-200 dark:border-slate-700">
                        <button type="button" onclick="document.getElementById('editStr{{ $s->id }}').classList.add('hidden')" class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 transition">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @empty
    @endforelse

</div>
@endsection
