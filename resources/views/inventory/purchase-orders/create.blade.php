@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <h1 class="display-6 mb-1"><i class="bi bi-cart-plus"></i> New Purchase Order</h1>
                    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-3">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('inventory.purchase-orders.index') }}">Purchase Orders</a></li>
                        <li class="breadcrumb-item active">New</li>
                    </ol></nav>

                    @include('session-messages')

                    <form method="POST" action="{{ route('inventory.purchase-orders.store') }}" id="poForm">
                        @csrf
                        <div class="card shadow-sm mb-4">
                            <div class="card-header fw-semibold">Order Details</div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">PO Number <span class="text-danger">*</span></label>
                                        <input type="text" name="po_number" value="{{ old('po_number', $nextPo) }}"
                                               class="form-control @error('po_number') is-invalid @enderror" required>
                                        @error('po_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Supplier</label>
                                        <select name="supplier_id" class="form-select">
                                            <option value="">— none —</option>
                                            @foreach($suppliers as $sid => $sname)
                                            <option value="{{ $sid }}" @selected(old('supplier_id')==$sid)>{{ $sname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Order Date <span class="text-danger">*</span></label>
                                        <input type="date" name="order_date"
                                               value="{{ old('order_date', now()->toDateString()) }}"
                                               class="form-control" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Expected Delivery</label>
                                        <input type="date" name="expected_delivery"
                                               value="{{ old('expected_delivery') }}"
                                               class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Payment Method</label>
                                        <select name="payment_method" class="form-select">
                                            <option value="">— select —</option>
                                            @foreach($paymentMethods as $k => $v)
                                            <option value="{{ $k }}" @selected(old('payment_method')==$k)>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Reference No.</label>
                                        <input type="text" name="reference_no" value="{{ old('reference_no') }}" class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Notes</label>
                                        <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Line Items --}}
                        <div class="card shadow-sm mb-4">
                            <div class="card-header d-flex align-items-center justify-content-between fw-semibold">
                                <span>Line Items</span>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addLineBtn">
                                    <i class="bi bi-plus"></i> Add Line
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <table class="table align-middle mb-0" id="linesTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:30%">Item Name <span class="text-danger">*</span></th>
                                            <th style="width:12%">Type</th>
                                            <th style="width:20%">Link to Stock Item</th>
                                            <th style="width:8%">Qty <span class="text-danger">*</span></th>
                                            <th style="width:10%">Unit Price <span class="text-danger">*</span></th>
                                            <th style="width:10%">Total</th>
                                            <th style="width:5%"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="linesBody">
                                        {{-- rows injected by JS --}}
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-light">
                                            <td colspan="5" class="text-end fw-bold">Grand Total</td>
                                            <td class="fw-bold" id="grandTotal">$0.00</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Create Order</button>
                            <a href="{{ route('inventory.purchase-orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const inventoryItems = @json($inventoryItems);

function buildRow(index) {
    const opts = inventoryItems.map(i =>
        `<option value="${i.id}" data-price="${i.unit_price ?? 0}">[${i.item_code}] ${i.name}</option>`
    ).join('');

    return `
    <tr class="line-row">
        <td><input type="text" name="items[${index}][item_name]" class="form-control form-control-sm" required></td>
        <td>
            <select name="items[${index}][item_type]" class="form-select form-select-sm item-type">
                <option value="consumable">Consumable</option>
                <option value="asset">Asset</option>
            </select>
        </td>
        <td>
            <select name="items[${index}][inventory_item_id]" class="form-select form-select-sm stock-link">
                <option value="">— none —</option>
                ${opts}
            </select>
        </td>
        <td><input type="number" name="items[${index}][quantity]" class="form-control form-control-sm line-qty" min="1" value="1" required></td>
        <td><input type="number" name="items[${index}][unit_price]" class="form-control form-control-sm line-price" min="0" step="0.01" value="0.00" required></td>
        <td class="line-total fw-semibold">$0.00</td>
        <td>
            <button type="button" class="btn btn-sm btn-link text-danger remove-line p-0">
                <i class="bi bi-x-circle"></i>
            </button>
        </td>
    </tr>`;
}

let rowIndex = 0;

document.getElementById('addLineBtn').addEventListener('click', function () {
    document.getElementById('linesBody').insertAdjacentHTML('beforeend', buildRow(rowIndex++));
    attachListeners();
});

function attachListeners() {
    document.querySelectorAll('.remove-line').forEach(btn => {
        btn.onclick = function () { this.closest('tr').remove(); recalcTotal(); };
    });
    document.querySelectorAll('.line-qty, .line-price').forEach(inp => {
        inp.oninput = function () { recalcRow(this.closest('tr')); recalcTotal(); };
    });
    document.querySelectorAll('.stock-link').forEach(sel => {
        sel.onchange = function () {
            const opt = this.options[this.selectedIndex];
            const price = opt.dataset.price ?? 0;
            const row = this.closest('tr');
            row.querySelector('.line-price').value = parseFloat(price).toFixed(2);
            // also fill item name if blank
            const nameInput = row.querySelector('[name$="[item_name]"]');
            if (!nameInput.value && opt.value) nameInput.value = opt.textContent.trim().replace(/^\[.*?\]\s*/, '');
            recalcRow(row); recalcTotal();
        };
    });
}

function recalcRow(row) {
    const qty   = parseFloat(row.querySelector('.line-qty').value) || 0;
    const price = parseFloat(row.querySelector('.line-price').value) || 0;
    row.querySelector('.line-total').textContent = '$' + (qty * price).toFixed(2);
}

function recalcTotal() {
    let total = 0;
    document.querySelectorAll('.line-total').forEach(cell => {
        total += parseFloat(cell.textContent.replace('$','')) || 0;
    });
    document.getElementById('grandTotal').textContent = '$' + total.toFixed(2);
}

// Start with one row
document.getElementById('addLineBtn').click();
</script>
@endpush
