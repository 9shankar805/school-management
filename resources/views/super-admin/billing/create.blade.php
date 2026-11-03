@extends('layouts.app')
@section('title', 'Create SaaS Invoice - Super Admin')

@section('content')
<div class="px-6 py-6 max-w-5xl mx-auto space-y-6">

    {{-- Breadcrumb & Header --}}
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('super.billing.index') }}" class="hover:text-indigo-600">SaaS Billing</a>
                <span>&bull;</span>
                <span>Create Invoice</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                <i class="bi bi-file-earmark-plus-fill text-indigo-600"></i>
                Create SaaS Invoice / Bill (विद्यालयको लागि बिजक तयार गर्नुहोस्)
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Bill partner schools for software setup, annual subscription, or hardware (Thermal printers, CCTV, Computers)
            </p>
        </div>
        <a href="{{ route('super.billing.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50">
            Cancel
        </a>
    </div>

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill text-rose-600"></i>
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('super.billing.store') }}" method="POST" id="invoiceForm" class="space-y-6">
        @csrf

        {{-- ── 1. INVOICE META & CLIENT SCHOOL ──────────────────────── --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
                <h3 class="text-sm font-bold uppercase tracking-wider text-indigo-600 flex items-center gap-2">
                    <i class="bi bi-info-circle-fill"></i>
                    Invoice Details &amp; School Information
                </h3>

                {{-- Quick Link Agreement or School --}}
                <div class="flex items-center gap-3">
                    <div class="w-56">
                        <select id="agreementSelect" onchange="loadAgreement(this)"
                            class="w-full text-xs px-2.5 py-1.5 rounded-xl border border-emerald-300 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-200 font-medium">
                            <option value="">-- Load from Agreement --</option>
                            @foreach($agreements as $agr)
                                <option value="{{ $agr->id }}"
                                    data-school-id="{{ $agr->school_id }}"
                                    data-school-name="{{ $agr->school_name }}"
                                    data-school-address="{{ $agr->school_address }}"
                                    data-school-pan="{{ $agr->school_pan_no }}"
                                    data-school-phone="{{ $agr->school_phone }}"
                                    data-school-email="{{ $agr->school_email }}"
                                    data-setup-fee="{{ $agr->setup_fee }}"
                                    data-renewal-fee="{{ $agr->renewal_fee }}"
                                    data-items="{{ json_encode($agr->items) }}"
                                    {{ (old('agreement_id', optional($selectedAgreement)->id) == $agr->id) ? 'selected' : '' }}>
                                    {{ $agr->agreement_number }} - {{ $agr->school_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <input type="hidden" name="agreement_id" id="agreement_id_input" value="{{ old('agreement_id', optional($selectedAgreement)->id) }}">
            <input type="hidden" name="school_id" id="school_id_input" value="{{ old('school_id', optional($selectedSchool)->id) }}">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Invoice Number *</label>
                    <input type="text" name="invoice_number" value="{{ old('invoice_number', $nextInvoiceNumber) }}" required
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white font-mono font-bold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Invoice Date (AD) *</label>
                    <input type="date" name="invoice_date" value="{{ old('invoice_date', date('Y-m-d')) }}" required
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Due Date (AD)</label>
                    <input type="date" name="due_date" value="{{ old('due_date', date('Y-m-d', strtotime('+15 days'))) }}"
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Invoice Date (BS नेपाली मिति)</label>
                    <input type="text" name="invoice_date_bs" value="{{ old('invoice_date_bs', '२०८३/०१/१५') }}"
                        placeholder="२०८३/०१/१५"
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">School Name (विद्यालयको नाम) *</label>
                    <input type="text" id="school_name" name="school_name" required
                        value="{{ old('school_name', optional($selectedSchool)->name ?? optional($selectedAgreement)->school_name) }}"
                        placeholder="e.g. Happy Land English Boarding School Siraha"
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white font-bold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">School PAN / VAT No.</label>
                    <input type="text" id="school_pan" name="school_pan"
                        value="{{ old('school_pan', optional($selectedSchool)->iemis_code ?? optional($selectedAgreement)->school_pan_no) }}"
                        placeholder="६०२६६६३९८"
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">School Address</label>
                    <input type="text" id="school_address" name="school_address"
                        value="{{ old('school_address', optional($selectedSchool)->address ?? optional($selectedAgreement)->school_address) }}"
                        class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Contact Phone</label>
                    <input type="text" id="school_phone" name="school_phone"
                        value="{{ old('school_phone', optional($selectedSchool)->phone ?? optional($selectedAgreement)->school_phone) }}"
                        class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Billing Email</label>
                    <input type="email" id="school_email" name="school_email"
                        value="{{ old('school_email', optional($selectedSchool)->email ?? optional($selectedAgreement)->school_email) }}"
                        class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                </div>
            </div>
        </div>

        {{-- ── 2. INVOICE LINE ITEMS ────────────────────────────────── --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-6 shadow-xs space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-700 pb-3">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="bi bi-list-check text-indigo-600"></i>
                        Bill Line Items (वस्तु तथा सेवा विवरण)
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Select from hardware and services catalog or type customized line items
                    </p>
                </div>
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
                        {{-- Rows injected by JS --}}
                    </tbody>
                </table>
            </div>

            {{-- Financial Calculations --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-100 dark:border-slate-700">
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Notes / Remarks for School</label>
                        <textarea name="notes" rows="3" placeholder="Thank you for partnering with NepalSchoolHub! Please deposit fees in our official bank account."
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Bank Payment Instructions</label>
                        <input type="text" name="terms" value="Bank: Rastriya Banijya Bank / Nabil Bank | A/C: Tech Pro Code Pvt. Ltd. | ConnectIPS / eSewa / Khalti accepted"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900">
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-slate-900/60 rounded-xl p-4 space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-medium">Subtotal (उप-जम्मा):</span>
                        <span class="font-bold font-mono text-slate-800 dark:text-slate-100" id="dispSubtotal">रु. ०.००</span>
                    </div>

                    <div class="flex items-center justify-between gap-3 text-xs">
                        <span class="text-slate-500 font-medium">Advance Paid (अग्रिम रकम):</span>
                        <div class="w-48 flex items-center gap-1.5">
                            <input type="number" step="0.01" min="0" name="advance_amount" id="advance_amount" value="{{ old('advance_amount', 0) }}" oninput="calcInvoiceTotals()"
                                placeholder="0.00"
                                class="w-28 px-2.5 py-1 text-xs font-mono text-right rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                            <select name="payment_method" class="text-[11px] px-1.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank</option>
                                <option value="esewa">eSewa</option>
                                <option value="khalti">Khalti</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3 text-xs">
                        <span class="text-slate-500 font-medium">Discount (छुट रकम):</span>
                        <div class="w-36">
                            <input type="number" step="0.01" min="0" name="discount_amount" id="discount_amount" value="{{ old('discount_amount', 0) }}" oninput="calcInvoiceTotals()"
                                class="w-full px-2.5 py-1 text-xs font-mono text-right rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-1.5 text-slate-500 font-medium">
                            <span>Tax / VAT (कर %):</span>
                            <select name="tax_percent" id="tax_percent" onchange="calcInvoiceTotals()"
                                class="text-[11px] px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                                <option value="0">0% (PAN / Exempt)</option>
                                <option value="13">13% (VAT Bill)</option>
                            </select>
                        </div>
                        <span class="font-bold font-mono text-slate-800 dark:text-slate-100" id="dispTax">रु. ०.००</span>
                    </div>

                    <div class="pt-2 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Grand Total (कुल रकम):</span>
                        <span class="text-lg font-black text-slate-900 dark:text-white font-mono" id="dispGrandTotal">
                            रु. ०.००
                        </span>
                    </div>

                    <div class="flex items-center justify-between pt-1 border-t border-dashed border-slate-200 dark:border-slate-700">
                        <span class="text-xs font-black text-rose-600 dark:text-rose-400 uppercase tracking-wider">Balance Due (बाँकी भुक्तानी):</span>
                        <span class="text-xl font-black text-rose-600 dark:text-rose-400 font-mono" id="dispDue">
                            रु. ०.००
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit Buttons --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('super.billing.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50">
                Cancel
            </a>
            <button type="submit" class="px-7 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold shadow-md shadow-indigo-200 flex items-center gap-2">
                <i class="bi bi-file-earmark-check"></i>
                Generate Invoice &amp; Save
            </button>
        </div>
    </form>
</div>

<script>
let invRowIndex = 0;

function loadAgreement(select) {
    const opt = select.options[select.selectedIndex];
    if (!opt || !opt.value) return;

    document.getElementById('agreement_id_input').value = opt.value;
    if (opt.dataset.schoolId) document.getElementById('school_id_input').value = opt.dataset.schoolId;
    if (opt.dataset.schoolName) document.getElementById('school_name').value = opt.dataset.schoolName;
    if (opt.dataset.schoolAddress) document.getElementById('school_address').value = opt.dataset.schoolAddress;
    if (opt.dataset.schoolPan) document.getElementById('school_pan').value = opt.dataset.schoolPan;
    if (opt.dataset.schoolPhone) document.getElementById('school_phone').value = opt.dataset.schoolPhone;
    if (opt.dataset.schoolEmail) document.getElementById('school_email').value = opt.dataset.schoolEmail;

    // Clear current rows and load agreement items
    document.getElementById('invoiceBody').innerHTML = '';
    invRowIndex = 0;

    const setupFee = parseFloat(opt.dataset.setupFee) || 0;
    const renewalFee = parseFloat(opt.dataset.renewalFee) || 0;

    if (setupFee > 0) {
        appendInvoiceRow('', 'पहिलो वर्षको प्रारम्भिक स्थापना तथा सफ्टवेयर सञ्चालन शुल्क (Initial ERP Setup Fee)', 'One-time onboarding & deployment', setupFee, 1);
    }
    if (renewalFee > 0) {
        appendInvoiceRow('', 'वार्षिक सफ्टवेयर नवीकरण तथा प्राविधिक सहयोग (Annual Software Renewal & Maintenance)', 'Yearly software license & cloud hosting', renewalFee, 1);
    }

    try {
        const extraItems = JSON.parse(opt.dataset.items || '[]');
        extraItems.forEach(it => {
            appendInvoiceRow(it.service_id, it.item_name, it.remarks || '', parseFloat(it.unit_price) || 0, it.quantity || 1);
        });
    } catch(e) {
        console.error('Error parsing agreement items:', e);
    }

    calcInvoiceTotals();
}

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
            <input type="text" name="items[${invRowIndex}][title]" value="${title}" required placeholder="Item title (e.g. 80mm Thermal Printer / Annual Subscription)"
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

// Add a default row if empty
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelectorAll('#invoiceBody tr').length === 0) {
        @if($selectedAgreement)
            // auto-trigger agreement load
            document.getElementById('agreementSelect').dispatchEvent(new Event('change'));
        @else
            addInvoiceCustomRow();
        @endif
    }
});
</script>
@endsection

