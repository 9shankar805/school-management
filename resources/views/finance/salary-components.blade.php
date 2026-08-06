@extends('layouts.app')
@section('content')
<div class="px-4 lg:px-8 py-6 max-w-5xl mx-auto">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100"><i class="bi bi-sliders mr-2"></i>Salary Components</h1>
        <p class="text-xs text-slate-500 mt-1">Define all earnings and deduction heads used in payroll. Each component can be fixed, percentage-based, or attendance-linked.</p>
    </div>

    @include('session-messages')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Add component --}}
        <div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-4">Add Component</h3>
                <form method="POST" action="{{ route('finance.salary.components.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" class="input-modern w-full" placeholder="e.g. House Rent Allowance" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Code <span class="text-rose-500">*</span></label>
                        <input type="text" name="code" class="input-modern w-full uppercase" placeholder="e.g. HRA" required>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Type</label>
                            <select name="type" class="select-modern w-full">
                                <option value="earning">Earning</option>
                                <option value="deduction">Deduction</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Calculation</label>
                            <select name="calculation" class="select-modern w-full">
                                <option value="fixed">Fixed</option>
                                <option value="percentage">% of Basic</option>
                                <option value="attendance">Attendance</option>
                                <option value="formula">Formula</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Default Value</label>
                        <input type="number" step="0.01" name="default_value" class="input-modern w-full" value="0" required>
                        <p class="text-xs text-slate-400 mt-0.5">For % type: enter the percentage (e.g. 10 = 10%). For fixed: the amount in Rs.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Formula <span class="text-slate-400">(formula type only)</span></label>
                        <input type="text" name="formula" class="input-modern w-full font-mono text-xs" placeholder="basic * 0.10">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_taxable" value="1" id="is_taxable_new" class="rounded border-slate-300">
                        <label for="is_taxable_new" class="text-xs text-slate-600 dark:text-slate-400">Included in taxable income</label>
                    </div>
                    <input type="hidden" name="sort_order" value="0">
                    <button type="submit" class="btn-primary w-full py-2.5 rounded-xl text-sm">
                        <i class="bi bi-plus-circle mr-1"></i> Add Component
                    </button>
                </form>
            </div>
        </div>

        {{-- Component list --}}
        <div class="lg:col-span-2">
            {{-- Earnings --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden mb-4">
                <div class="px-5 py-3 bg-emerald-50 dark:bg-emerald-900/20 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-emerald-700 dark:text-emerald-400"><i class="bi bi-arrow-up-circle mr-2"></i>Earnings</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="text-xs text-slate-400 bg-slate-50 dark:bg-slate-800/40">
                        <tr><th class="text-left px-5 py-2.5">Name</th><th class="text-left px-5 py-2.5">Code</th><th class="text-left px-5 py-2.5">Calculation</th><th class="text-right px-5 py-2.5">Default</th><th class="text-center px-5 py-2.5">Taxable</th><th class="text-center px-5 py-2.5">Status</th><th class="px-5 py-2.5"></th></tr>
                    </thead>
                    <tbody>
                        @forelse($components->where('type','earning') as $c)
                        <tr class="border-t border-slate-100 dark:border-slate-800">
                            <td class="px-5 py-3 font-medium text-slate-700 dark:text-slate-200">{{ $c->name }} @if($c->is_system)<span class="ml-1 text-xs text-slate-400">(system)</span>@endif</td>
                            <td class="px-5 py-3"><code class="text-xs bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded">{{ $c->code }}</code></td>
                            <td class="px-5 py-3 text-slate-500 text-xs">{{ ucfirst($c->calculation) }}{{ $c->calculation === 'percentage' ? ' of Basic' : '' }}</td>
                            <td class="px-5 py-3 text-right">{{ $c->calculation === 'percentage' ? $c->default_value.'%' : 'Rs. '.number_format($c->default_value,2) }}</td>
                            <td class="px-5 py-3 text-center">{{ $c->is_taxable ? '✓' : '—' }}</td>
                            <td class="px-5 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs {{ $c->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $c->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="px-5 py-3 text-right">
                                @if(!$c->is_system)
                                <button class="text-slate-400 hover:text-rose-600 transition" onclick="confirmDelete('{{ route('finance.salary.components.destroy', $c->id) }}')">
                                    <i class="bi bi-trash text-xs"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-5 py-6 text-center text-slate-400 text-xs">No earning components defined.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Deductions --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="px-5 py-3 bg-rose-50 dark:bg-rose-900/20 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-rose-700 dark:text-rose-400"><i class="bi bi-arrow-down-circle mr-2"></i>Deductions</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="text-xs text-slate-400 bg-slate-50 dark:bg-slate-800/40">
                        <tr><th class="text-left px-5 py-2.5">Name</th><th class="text-left px-5 py-2.5">Code</th><th class="text-left px-5 py-2.5">Calculation</th><th class="text-right px-5 py-2.5">Default</th><th class="text-center px-5 py-2.5">Status</th><th class="px-5 py-2.5"></th></tr>
                    </thead>
                    <tbody>
                        @forelse($components->where('type','deduction') as $c)
                        <tr class="border-t border-slate-100 dark:border-slate-800">
                            <td class="px-5 py-3 font-medium text-slate-700 dark:text-slate-200">{{ $c->name }} @if($c->is_system)<span class="ml-1 text-xs text-slate-400">(system)</span>@endif</td>
                            <td class="px-5 py-3"><code class="text-xs bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded">{{ $c->code }}</code></td>
                            <td class="px-5 py-3 text-slate-500 text-xs">{{ ucfirst($c->calculation) }}</td>
                            <td class="px-5 py-3 text-right">{{ $c->calculation === 'percentage' ? $c->default_value.'%' : 'Rs. '.number_format($c->default_value,2) }}</td>
                            <td class="px-5 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs {{ $c->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $c->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="px-5 py-3 text-right">
                                @if(!$c->is_system)
                                <button class="text-slate-400 hover:text-rose-600 transition" onclick="confirmDelete('{{ route('finance.salary.components.destroy', $c->id) }}')">
                                    <i class="bi bi-trash text-xs"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-5 py-6 text-center text-slate-400 text-xs">No deduction components defined.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(url) {
    if (!confirm('Delete this component?')) return;
    const form = document.createElement('form');
    form.method = 'POST'; form.action = url;
    form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE">';
    document.body.appendChild(form); form.submit();
}
</script>
@endsection
