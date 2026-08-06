@extends('layouts.app')
@section('title', 'Instalment Plan — ' . $invoice->invoice_number)

@section('content')
<div class="px-6 py-5 max-w-5xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="bi bi-calendar2-range text-blue-600"></i> Instalment Plan
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $invoice->invoice_number }} &mdash; {{ $invoice->student->full_name ?? 'Unknown' }}</p>
        </div>
        <a href="{{ route('payments.show', $invoice->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-blue-400 transition">
            <i class="bi bi-arrow-left"></i> Back to Invoice
        </a>
    </div>

    @include('layouts.session-messages')

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-4">
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mb-1">Invoice Total</p>
            <p class="text-2xl font-extrabold text-slate-900 dark:text-white">Rs. {{ number_format($invoice->amount, 2) }}</p>
        </div>
        <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl p-4">
            <p class="text-xs text-emerald-700 dark:text-emerald-400 font-medium mb-1">Total Paid</p>
            <p class="text-2xl font-extrabold text-emerald-700 dark:text-emerald-400">Rs. {{ number_format($totalPaid, 2) }}</p>
        </div>
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-4">
            <p class="text-xs text-amber-700 dark:text-amber-400 font-medium mb-1">Outstanding</p>
            <p class="text-2xl font-extrabold text-amber-700 dark:text-amber-400">Rs. {{ number_format($invoice->amount - $totalPaid, 2) }}</p>
        </div>
    </div>

    {{-- Existing Instalments --}}
    @if($installments->isNotEmpty())
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="bi bi-list-check text-blue-500"></i> Scheduled Instalments
            </h3>
            <form method="POST" action="{{ route('finance.installments.destroy', $invoice->id) }}"
                  onsubmit="return confirm('Delete this instalment plan? (Only allowed if none are paid)')">
                @csrf @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 border border-red-200 dark:border-red-800 transition">
                    <i class="bi bi-trash"></i> Delete Plan
                </button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Due Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Paid Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach($installments as $inst)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40 transition">
                        <td class="px-4 py-3 text-sm font-bold text-slate-800 dark:text-white">{{ $inst->installment_number }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-slate-900 dark:text-white">Rs. {{ number_format($inst->amount, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $inst->due_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $inst->paid_date?->format('d M Y') ?? '—' }}</td>
                        <td class="px-4 py-3">{!! $inst->status_badge !!}</td>
                        <td class="px-4 py-3 text-right">
                            @if($inst->status === 'pending')
                            <button onclick="openPayModal({{ $inst->id }}, '{{ $inst->installment_number }}', '{{ $inst->amount }}')"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition">
                                <i class="bi bi-check-circle"></i> Mark Paid
                            </button>
                            @else
                            <span class="text-xs text-emerald-600 font-medium"><i class="bi bi-check2-all"></i> Paid</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Create / Re-create Plan --}}
    @if($installments->where('status','paid')->count() === 0)
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="bi bi-plus-circle text-blue-500"></i>
                {{ $installments->isNotEmpty() ? 'Recreate' : 'Create' }} Instalment Plan
            </h3>
            {{-- Auto-split helper --}}
            <div class="flex items-center gap-2">
                <input type="number" id="autoSplitCount" value="3" min="2" max="12"
                    class="w-16 border border-slate-200 dark:border-slate-600 rounded-lg px-2 py-1 text-xs bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400">
                <input type="date" id="autoSplitStart" value="{{ today()->format('Y-m-d') }}"
                    class="border border-slate-200 dark:border-slate-600 rounded-lg px-2 py-1 text-xs bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400">
                <button type="button" onclick="autoSplit()"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 transition">
                    <i class="bi bi-magic"></i> Auto Split
                </button>
            </div>
        </div>
        <div class="px-6 py-4">
            <form method="POST" action="{{ route('finance.installments.store', $invoice->id) }}" id="installmentForm">
                @csrf
                <div id="installmentRows" class="space-y-3 mb-4">
                    {{-- Rows injected by JS --}}
                </div>

                @error('installments')
                <p class="text-xs text-red-600 mb-3">{{ $message }}</p>
                @enderror

                <div class="flex items-center justify-between">
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Sum must equal <strong>Rs. {{ number_format($invoice->amount, 2) }}</strong>
                    </p>
                    <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition">
                        <i class="bi bi-save"></i> Save Plan
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>

{{-- Pay Modal --}}
<div id="payModal" class="hidden fixed inset-0 z-50 overflow-y-auto" style="background:rgba(15,23,42,0.55);backdrop-filter:blur(4px)">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white" id="payModalTitle">Mark Instalment Paid</h3>
                <button onclick="closePayModal()" class="text-slate-400 hover:text-slate-600"><i class="bi bi-x-lg text-xl"></i></button>
            </div>
            <form method="POST" id="payModalForm">
                @csrf
                <p class="text-sm text-slate-600 dark:text-slate-300 mb-4">
                    Amount: <strong id="payModalAmount" class="text-slate-900 dark:text-white"></strong>
                </p>
                <div class="mb-4">
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Payment Method</label>
                    <select name="payment_method" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cheque">Cheque</option>
                        <option value="esewa">eSewa</option>
                        <option value="khalti">Khalti</option>
                        <option value="stripe">Stripe</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Payment Date</label>
                    <input type="date" name="payment_date" value="{{ today()->format('Y-m-d') }}" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                </div>
                <div class="mb-6">
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Transaction ID <span class="text-slate-400">(optional)</span></label>
                    <input type="text" name="gateway_transaction_id" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="e.g. ESW-000123">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closePayModal()" class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 transition">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition">Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
const invoiceAmount = {{ $invoice->amount }};
const autoSplitUrl  = "{{ route('finance.installments.auto-split', $invoice->id) }}";
const payBaseUrl    = "{{ route('finance.installments.pay', ':id') }}";

function renderRows(data) {
    const container = document.getElementById('installmentRows');
    container.innerHTML = '';
    data.forEach((row, i) => {
        container.innerHTML += `
        <div class="grid grid-cols-3 gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Instalment ${row.installment_number}</label>
                <input type="number" name="installments[${i}][amount]" value="${row.amount}"
                    class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    step="0.01" min="1" required>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Due Date</label>
                <input type="date" name="installments[${i}][due_date]" value="${row.due_date}"
                    class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    required>
            </div>
            <div>
                <button type="button" onclick="removeRow(this)" class="w-full inline-flex items-center justify-center gap-1 px-3 py-2 rounded-lg text-xs font-semibold text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 border border-red-200 dark:border-red-800 transition">
                    <i class="bi bi-trash"></i> Remove
                </button>
            </div>
        </div>`;
    });
}

function autoSplit() {
    const count     = document.getElementById('autoSplitCount').value;
    const startDate = document.getElementById('autoSplitStart').value;
    fetch(`${autoSplitUrl}?count=${count}&start_date=${startDate}`)
        .then(r => r.json())
        .then(data => renderRows(data));
}

function removeRow(btn) {
    btn.closest('.grid').remove();
    reNumberRows();
}

function reNumberRows() {
    document.querySelectorAll('#installmentRows > .grid').forEach((row, i) => {
        row.querySelector('label').textContent = `Instalment ${i + 1}`;
        row.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replace(/\[\d+\]/, `[${i}]`);
        });
    });
}

function openPayModal(id, num, amount) {
    document.getElementById('payModalTitle').textContent  = `Mark Instalment #${num} Paid`;
    document.getElementById('payModalAmount').textContent = `Rs. ${parseFloat(amount).toLocaleString('en-IN', {minimumFractionDigits:2})}`;
    document.getElementById('payModalForm').action = payBaseUrl.replace(':id', id);
    document.getElementById('payModal').classList.remove('hidden');
}

function closePayModal() {
    document.getElementById('payModal').classList.add('hidden');
}

// Auto-load equal 3-instalment split on page load if no plan exists
@if($installments->isEmpty())
autoSplit();
@endif
</script>
@endpush
@endsection
