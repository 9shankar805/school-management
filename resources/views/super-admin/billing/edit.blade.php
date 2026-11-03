@extends('layouts.app')
@section('title', 'Edit SaaS Invoice - ' . $invoice->invoice_number)

@section('content')
<div class="px-6 py-6 max-w-5xl mx-auto space-y-6">

    {{-- Breadcrumb & Header --}}
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('super.billing.index') }}" class="hover:text-indigo-600">SaaS Billing</a>
                <span>&bull;</span>
                <a href="{{ route('super.billing.show', $invoice->id) }}" class="hover:text-indigo-600">{{ $invoice->invoice_number }}</a>
                <span>&bull;</span>
                <span>Edit</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                <i class="bi bi-pencil-square text-indigo-600"></i>
                Edit SaaS Invoice (बिजक सम्पादन गर्नुहोस्)
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Invoice Ref: <span class="font-mono font-bold">{{ $invoice->invoice_number }}</span> &bull; {{ $invoice->school_name }}
            </p>
        </div>
        <a href="{{ route('super.billing.show', $invoice->id) }}" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50">
            Cancel
        </a>
    </div>

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill text-rose-600"></i>
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('super.billing.update', $invoice->id) }}" method="POST" id="invoiceForm" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- ── 1. INVOICE META & CLIENT SCHOOL ──────────────────────── --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-6 shadow-xs space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-indigo-600 flex items-center gap-2 border-b border-slate-100 dark:border-slate-700 pb-3">
                <i class="bi bi-info-circle-fill"></i>
                Invoice Details &amp; School Information
            </h3>

            <input type="hidden" name="agreement_id" value="{{ old('agreement_id', $invoice->agreement_id) }}">
            <input type="hidden" name="school_id" value="{{ old('school_id', $invoice->school_id) }}">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Invoice Number *</label>
                    <input type="text" name="invoice_number" value="{{ old('invoice_number', $invoice->invoice_number) }}" required
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white font-mono font-bold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Invoice Date (AD) *</label>
                    <input type="date" name="invoice_date" value="{{ old('invoice_date', optional($invoice->invoice_date)->format('Y-m-d')) }}" required
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Due Date (AD)</label>
                    <input type="date" name="due_date" value="{{ old('due_date', optional($invoice->due_date)->format('Y-m-d')) }}"
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Invoice Date (BS नेपाली मिति)</label>
                    <input type="text" name="invoice_date_bs" value="{{ old('invoice_date_bs', $invoice->invoice_date_bs) }}"
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">School Name *</label>
                    <input type="text" name="school_name" value="{{ old('school_name', $invoice->school_name) }}" required
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white font-bold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">School PAN / VAT No.</label>
                    <input type="text" name="school_pan" value="{{ old('school_pan', $invoice->school_pan) }}"
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">School Address</label>
                    <input type="text" name="school_address" value="{{ old('school_address', $invoice->school_address) }}"
                        class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Contact Phone</label>
                    <input type="text" name="school_phone" value="{{ old('school_phone', $invoice->school_phone) }}"
                        class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Billing Email</label>
                    <input type="email" name="school_email" value="{{ old('school_email', $invoice->school_email) }}"
                        class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                </div>
            </div>
        </div>

        {{-- ── 2. INVOICE LINE ITEMS ────────────────────────────────── --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-6 shadow-xs space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-700 pb-3">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="bi bi-list-check text-indigo-600"></i>
                    Bill Line Items (वस्तु तथा सेवा विवरण)
                </h3>
                <div class="flex items-center gap-2">
                    <select id="catalogSelect" onchange="addInvoiceCatalogItem(this)"
                        class="text-xs px-3 py-1.5 rounded-xl border border-indigo-300 dark:border-indigo-700 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-900 dark:text-indigo-200 font-semibold">
                        <option value="">+ Add Service / Hardware...</option>
                        @foreach($services as $srv)
                            <option value="{{ $srv->id }}"
                                data-title="{{ $srv->name }}"
                                data-desc="{{ $srv->name_np ?: $srv->description }}"
                                data-price="{{ $srv->default_price }}">
                                {{ $srv->name }} (रु. {{ number_format($srv->default_price) }})
                            </option>
                        @endforeach
                    </select>
                    <button type="button" onclick="addInvoiceCustomRow()"
                        class="px-3 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-semibold flex items-center gap-1">
                        <i class="bi bi-plus-lg"></i> Custom Item
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm" id="invoiceTable">
                    <thead class="bg-slate-50 dark:bg-slate-900 text-xs font-bold uppercase text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="py-2.5 px-3">विवरण / Title &amp; Description</th>
                            <th class="py-2.5 px-3 w-28">Qty</th>
                            <th class="py-2.5 px-3 w-36">Rate (रु.)</th>
                            <th class="py-2.5 px-3 w-36">Total (रु.)</th>
                            <th class="py-2.5 px-2 w-12 text-center"></th>
                        </tr>
                    </thead>
                    <tbody id="invoiceBody" class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @foreach($invoice->items as $idx => $it)
                            <tr id="inv_row_{{ $idx }}" class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20">
                                <td class="py-2.5 px-3">
                                    <input type="hidden" name="items[{{ $idx }}][service_id]" value="{{ $it->service_id }}">
                                    <input type="text" name="items[{{ $idx }}][title]" value="{{ $it->title }}" required
                                        class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 font-semibold text-slate-800 dark:text-slate-100">
                                    <input type="text" name="items[{{ $idx }}][description]" value="{{ $it->description }}"
                                        class="w-full px-2.5 py-0.5 text-[11px] rounded-lg border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 mt-1">
                                </td>
                                <td class="py-2.5 px-3">
                                    <input type="number" min="0.01" step="any" name="items[{{ $idx }}][quantity]" value="{{ $it->quantity }}" oninput="calcInvRow({{ $idx }})"
                                        class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 font-mono inv-qty">
                                </td>
                                <td class="py-2.5 px-3">
                                    <input type="number" step="0.01" name="items[{{ $idx }}][unit_price]" value="{{ $it->unit_price }}" oninput="calcInvRow({{ $idx }})"
                                        class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 font-mono inv-rate">
                                </td>
                                <td class="py-2.5 px-3 font-mono font-bold text-slate-800 dark:text-slate-100 text-xs inv-line-total">
                                    रु. {{ number_format($it->total_amount, 2) }}
                                </td>
                                <td class="py-2.5 px-2 text-center">
                                    <button type="button" onclick="removeInvRow({{ $idx }})" class="p-1 text-rose-500 hover:text-rose-700">
                                        <i class="bi bi-x-circle text-base"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Financial Calculations --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-100 dark:border-slate-700">
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Notes / Remarks for School</label>
                        <textarea name="notes" rows="3"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">{{ old('notes', $invoice->notes) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Bank Payment Instructions</label>
                        <input type="text" name="terms" value="{{ old('terms', $invoice->terms) }}"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-slate-900/60 rounded-xl p-4 space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-medium">Subtotal (उप-जम्मा):</span>
                        <span class="font-bold font-mono text-slate-800 dark:text-slate-100" id="dispSubtotal">रु. {{ number_format($invoice->subtotal, 2) }}</span>
                    </div>

                    <div class="flex items-center justify-between gap-3 text-xs">
                        <span class="text-slate-500 font-medium">Advance / Paid (अग्रिम रकम):</span>
                        <div class="w-36">
                            <input type="number" step="0.01" min="0" name="advance_amount" id="advance_amount" value="{{ old('advance_amount', $invoice->paid_amount) }}" oninput="calcInvoiceTotals()"
                                class="w-full px-2.5 py-1 text-xs font-mono text-right rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800"
                                {{ $invoice->payments()->count() > 1 ? 'readonly title="Multiple payment records exist. Use Payment module to manage."' : '' }}>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3 text-xs">
                        <span class="text-slate-500 font-medium">Discount (छुट रकम):</span>
                        <div class="w-36">
                            <input type="number" step="0.01" min="0" name="discount_amount" id="discount_amount" value="{{ old('discount_amount', $invoice->discount_amount) }}" oninput="calcInvoiceTotals()"
                                class="w-full px-2.5 py-1 text-xs font-mono text-right rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-1.5 text-slate-500 font-medium">
                            <span>Tax / VAT (कर %):</span>
                            <select name="tax_percent" id="tax_percent" onchange="calcInvoiceTotals()"
                                class="text-[11px] px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                                <option value="0" {{ old('tax_percent', $invoice->tax_percent) == 0 ? 'selected' : '' }}>0% (PAN / Exempt)</option>
                                <option value="13" {{ old('tax_percent', $invoice->tax_percent) == 13 ? 'selected' : '' }}>13% (VAT Bill)</option>
                            </select>
                        </div>
                        <span class="font-bold font-mono text-slate-800 dark:text-slate-100" id="dispTax">रु. {{ number_format($invoice->tax_amount, 2) }}</span>
                    </div>

                    <div class="pt-2 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Grand Total (कुल रकम):</span>
                        <span class="text-lg font-black text-slate-900 dark:text-white font-mono" id="dispGrandTotal">
                            रु. {{ number_format($invoice->total_amount, 2) }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between pt-1 border-t border-dashed border-slate-200 dark:border-slate-700">
                        <span class="text-xs font-black text-rose-600 dark:text-rose-400 uppercase tracking-wider">Balance Due (बाँकी भुक्तानी):</span>
                        <span class="text-xl font-black text-rose-600 dark:text-rose-400 font-mono" id="dispDue">
                            रु. {{ number_format($invoice->due_amount, 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit Buttons --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('super.billing.show', $invoice->id) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50">
                Cancel
            </a>
            <button type="submit" class="px-7 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold shadow-md shadow-indigo-200 flex items-center gap-2">
                <i class="bi bi-save"></i>
                Update Invoice
            </button>
        </div>
    </form>
</div>

<script>
let invRowIndex = {{ $invoice->items->count() }};

function addInvoiceCatalogItem(select) {
    const opt = select.options[select.selectedIndex];
    if (!opt || !opt.value) return;

    appendInvoiceRow(opt.value, opt.dataset.title, opt.dataset.desc, parseFloat(opt.dataset.price) || 0, 1);
    select.selectedIndex = 0;
}

function addInvoiceCustomRow() {
    appendInvoiceRow('', '', '', 0, 1);
}

function appendInvoiceRow(srvId, title, desc, price, qty) {
    const tbody = document.getElementById('invoiceBody');
    const tr = document.createElement('tr');
    tr.id = 'inv_row_' + invRowIndex;
    tr.className = 'hover:bg-slate-50/50 dark:hover:bg-slate-700/20';

    tr.innerHTML = `
        <td class="py-2.5 px-3">
            <input type="hidden" name="items[${invRowIndex}][service_id]" value="${srvId}">
            <input type="text" name="items[${invRowIndex}][title]" value="${title}" required placeholder="Item title"
                class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 font-semibold text-slate-800 dark:text-slate-100">
            <input type="text" name="items[${invRowIndex}][description]" value="${desc}" placeholder="Optional details / specification"
                class="w-full px-2.5 py-0.5 text-[11px] rounded-lg border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 mt-1">
        </td>
        <td class="py-2.5 px-3">
            <input type="number" min="0.01" step="any" name="items[${invRowIndex}][quantity]" value="${qty}" oninput="calcInvRow(${invRowIndex})"
                class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 font-mono inv-qty">
        </td>
        <td class="py-2.5 px-3">
            <input type="number" step="0.01" name="items[${invRowIndex}][unit_price]" value="${price}" oninput="calcInvRow(${invRowIndex})"
                class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 font-mono inv-rate">
        </td>
        <td class="py-2.5 px-3 font-mono font-bold text-slate-800 dark:text-slate-100 text-xs inv-line-total">
            रु. ${(price * qty).toLocaleString()}
        </td>
        <td class="py-2.5 px-2 text-center">
            <button type="button" onclick="removeInvRow(${invRowIndex})" class="p-1 text-rose-500 hover:text-rose-700">
                <i class="bi bi-x-circle text-base"></i>
            </button>
        </td>
    `;

    tbody.appendChild(tr);
    invRowIndex++;
    calcInvoiceTotals();
}

function removeInvRow(idx) {
    const row = document.getElementById('inv_row_' + idx);
    if (row) row.remove();
    calcInvoiceTotals();
}

function calcInvRow(idx) {
    const row = document.getElementById('inv_row_' + idx);
    if (!row) return;
    const qty = parseFloat(row.querySelector('.inv-qty').value) || 0;
    const rate = parseFloat(row.querySelector('.inv-rate').value) || 0;
    const tot = qty * rate;
    row.querySelector('.inv-line-total').innerText = 'रु. ' + tot.toLocaleString();
    calcInvoiceTotals();
}

function calcInvoiceTotals() {
    let subtotal = 0;
    document.querySelectorAll('#invoiceBody tr').forEach(row => {
        const qty = parseFloat(row.querySelector('.inv-qty')?.value) || 0;
        const rate = parseFloat(row.querySelector('.inv-rate')?.value) || 0;
        subtotal += (qty * rate);
    });

    const discount = parseFloat(document.getElementById('discount_amount').value) || 0;
    const advance = parseFloat(document.getElementById('advance_amount')?.value) || 0;
    const afterDiscount = Math.max(0, subtotal - discount);
    const taxPercent = parseFloat(document.getElementById('tax_percent').value) || 0;
    const taxAmount = (afterDiscount * taxPercent) / 100;
    const grandTotal = afterDiscount + taxAmount;
    const due = Math.max(0, grandTotal - advance);

    document.getElementById('dispSubtotal').innerText = 'रु. ' + subtotal.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('dispTax').innerText = 'रु. ' + taxAmount.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('dispGrandTotal').innerText = 'रु. ' + grandTotal.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('dispDue').innerText = 'रु. ' + due.toLocaleString('en-IN', {minimumFractionDigits: 2});
}
</script>
@endsection

