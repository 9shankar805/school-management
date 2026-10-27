@extends('layouts.app')
@section('content')
<div class="px-4 lg:px-8 py-6 w-full mx-auto">

    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100"><i class="bi bi-list-ul mr-2"></i>Fee Types</h1>
            <p class="text-xs text-slate-500 mt-1">Master list of all fee heads. Triggers control when invoices are auto-generated.</p>
        </div>
        <a href="{{ route('finance.structures') }}" class="btn-secondary text-sm px-4 py-2 rounded-xl">
            <i class="bi bi-table mr-1"></i> Fee Structures
        </a>
    </div>

    @include('session-messages')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Add form --}}
        <div>
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-4">Add Fee Type</h3>
                <form method="POST" action="{{ route('finance.fee-types.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" class="input-modern w-full" placeholder="e.g. Tuition Fee" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Code</label>
                        <input type="text" name="code" class="input-modern w-full uppercase" placeholder="e.g. TUT">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Category</label>
                        <select name="category_id" class="select-modern w-full">
                            <option value="">— None —</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Frequency</label>
                            <select name="frequency" class="select-modern w-full text-xs">
                                <option value="monthly">Monthly</option>
                                <option value="once">One-time</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="half_yearly">Half-Yearly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Auto Trigger</label>
                            <select name="trigger" class="select-modern w-full text-xs">
                                <option value="manual">Manual</option>
                                <option value="monthly">Monthly (1st)</option>
                                <option value="admission">On Admission</option>
                                <option value="exam_publish">Exam Publish</option>
                                <option value="annual">Annual</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_taxable" value="1" id="ft_taxable" class="rounded border-slate-300"
                                   onchange="document.getElementById('ft_taxrate').classList.toggle('hidden', !this.checked)">
                            <label for="ft_taxable" class="text-xs text-slate-600 dark:text-slate-400">Taxable (VAT)</label>
                        </div>
                    </div>
                    <div id="ft_taxrate" class="hidden">
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">VAT Rate (%)</label>
                        <input type="number" step="0.01" name="tax_rate" class="input-modern w-full" value="13" placeholder="13">
                    </div>
                    <button type="submit" class="btn-primary w-full py-2.5 rounded-xl text-sm">
                        <i class="bi bi-plus-circle mr-1"></i> Add Fee Type
                    </button>
                </form>
            </div>

            {{-- Nepal legal heads reference --}}
            <div class="mt-4 bg-blue-50/80 dark:bg-blue-950/40 rounded-2xl border border-blue-200/80 dark:border-blue-900/60 p-4 shadow-sm">
                <div class="text-xs font-bold text-blue-700 dark:text-blue-300 mb-2 flex items-center gap-1.5">
                    <i class="bi bi-info-circle"></i> Nepal's 11 Legal Fee Heads
                </div>
                <div class="text-xs text-blue-600 dark:text-blue-300/90 space-y-1">
                    @foreach(\App\Models\FeeType::LEGAL_HEADS as $head)
                    <div class="flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 dark:bg-blue-400 flex-shrink-0"></span>
                        <span>{{ $head }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Fee type list --}}
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-xs text-slate-500 dark:text-slate-400">
                            <tr>
                                <th class="text-left px-4 py-3 whitespace-nowrap">Name</th>
                                <th class="text-left px-4 py-3 whitespace-nowrap">Code</th>
                                <th class="text-left px-4 py-3 whitespace-nowrap">Frequency</th>
                                <th class="text-left px-4 py-3 whitespace-nowrap">Trigger</th>
                                <th class="text-center px-4 py-3 whitespace-nowrap">VAT</th>
                                <th class="text-center px-4 py-3 whitespace-nowrap">Status</th>
                                <th class="px-4 py-3 whitespace-nowrap text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($feeTypes as $ft)
                            <tr class="border-t border-slate-100 dark:border-slate-800 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-100 whitespace-nowrap">{{ $ft->name }}</td>
                                <td class="px-4 py-3 whitespace-nowrap"><code class="text-xs bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-slate-700 dark:text-slate-300">{{ $ft->code ?? '—' }}</code></td>
                                <td class="px-4 py-3 text-slate-500 dark:text-slate-400 text-xs whitespace-nowrap">{{ $ft->frequency_label }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                        {{ match($ft->trigger) {
                                            'monthly'      => 'bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/50',
                                            'admission'    => 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50',
                                            'exam_publish' => 'bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800/50',
                                            'annual'       => 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/50',
                                            default        => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700',
                                        } }}">
                                        {{ $ft->trigger_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                    {{ $ft->is_taxable ? $ft->tax_rate.'%' : '—' }}
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $ft->is_active ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-700' }}">
                                        {{ $ft->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <div class="flex items-center gap-1 justify-end">
                                        <button type="button" class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition text-xs cursor-pointer border-0 bg-transparent shadow-none"
                                                onclick="openEditFT({{ $ft->id }}, '{{ addslashes($ft->name) }}', '{{ $ft->frequency }}', '{{ $ft->trigger }}', {{ $ft->is_taxable ? 1 : 0 }}, {{ $ft->tax_rate }}, {{ $ft->is_active ? 1 : 0 }})"
                                                title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="POST" action="{{ route('finance.fee-types.destroy', $ft->id) }}" class="inline"
                                              onsubmit="return confirm('Delete this fee type?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="w-7 h-7 rounded-lg inline-flex items-center justify-center text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition text-xs cursor-pointer border-0 bg-transparent shadow-none"
                                                    title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">No fee types defined yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($feeTypes->hasPages())
                <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-800">{{ $feeTypes->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Edit modal --}}
<div id="editFTModal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-sm p-6 border border-slate-200 dark:border-slate-800">
        <h2 class="text-base font-bold text-slate-800 dark:text-slate-100 mb-4">Edit Fee Type</h2>
        <form id="editFTForm" method="POST" class="space-y-3">
            @csrf @method('PUT')
            <input type="text" name="name" id="edit_ft_name" class="input-modern w-full" required>
            <div class="grid grid-cols-2 gap-2">
                <select name="frequency" id="edit_ft_freq" class="select-modern text-xs">
                    <option value="monthly">Monthly</option>
                    <option value="once">One-time</option>
                    <option value="quarterly">Quarterly</option>
                    <option value="half_yearly">Half-Yearly</option>
                    <option value="yearly">Yearly</option>
                </select>
                <select name="trigger" id="edit_ft_trigger" class="select-modern text-xs">
                    <option value="manual">Manual</option>
                    <option value="monthly">Monthly (1st)</option>
                    <option value="admission">On Admission</option>
                    <option value="exam_publish">Exam Publish</option>
                    <option value="annual">Annual</option>
                </select>
            </div>
            <div class="flex items-center gap-3">
                <label class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                    <input type="checkbox" name="is_taxable" value="1" id="edit_ft_taxable" class="rounded border-slate-300">
                    Taxable
                </label>
                <input type="number" step="0.01" name="tax_rate" id="edit_ft_taxrate" class="input-modern w-24 text-sm" placeholder="13">
                <label class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                    <input type="checkbox" name="is_active" value="1" id="edit_ft_active" class="rounded border-slate-300">
                    Active
                </label>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="submit" class="btn-primary flex-1 py-2 text-sm rounded-xl">Save</button>
                <button type="button" onclick="document.getElementById('editFTModal').classList.add('hidden')"
                        class="btn-secondary px-5 text-sm rounded-xl">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditFT(id, name, freq, trigger, taxable, taxRate, active) {
    const baseUrl = '{{ url("finance/fee-types") }}';
    document.getElementById('editFTForm').action = baseUrl + '/' + id;
    document.getElementById('edit_ft_name').value = name;
    document.getElementById('edit_ft_freq').value = freq;
    document.getElementById('edit_ft_trigger').value = trigger;
    document.getElementById('edit_ft_taxable').checked = !!taxable;
    document.getElementById('edit_ft_taxrate').value = taxRate;
    document.getElementById('edit_ft_active').checked = !!active;
    document.getElementById('editFTModal').classList.remove('hidden');
}
</script>
@endsection
