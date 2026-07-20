@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4" style="max-width:860px">

                    <h1 class="display-6 mb-1"><i class="bi bi-layout-text-sidebar"></i> New Fee Structure</h1>
                    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-3">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('finance.structures.index') }}">Fee Structures</a></li>
                        <li class="breadcrumb-item active">New</li>
                    </ol></nav>

                    @include('session-messages')

                    <form method="POST" action="{{ route('finance.structures.store') }}" id="structureForm">
                        @csrf
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white fw-semibold">Structure Details</div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" value="{{ old('name') }}"
                                               class="form-control @error('name') is-invalid @enderror"
                                               placeholder="e.g. Grade 10 — 2026 Term 1" required>
                                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Session</label>
                                        <select name="session_id" class="form-select">
                                            <option value="">— any —</option>
                                            @foreach($sessions as $s)
                                            <option value="{{ $s->id }}" @selected(old('session_id')==$s->id)>{{ $s->session_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Term</label>
                                        <select name="term_id" class="form-select">
                                            <option value="">— any —</option>
                                            @foreach($terms as $t)
                                            <option value="{{ $t->id }}" @selected(old('term_id')==$t->id)>{{ $t->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Class</label>
                                        <select name="class_id" class="form-select">
                                            <option value="">— all classes —</option>
                                            @foreach($classes as $c)
                                            <option value="{{ $c->id }}" @selected(old('class_id')==$c->id)>{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Program</label>
                                        <select name="program_id" class="form-select">
                                            <option value="">— any —</option>
                                            @foreach($programs as $p)
                                            <option value="{{ $p->id }}" @selected(old('program_id')==$p->id)>{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Notes</label>
                                        <input type="text" name="notes" value="{{ old('notes') }}" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Line items --}}
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                                <span class="fw-semibold">Fee Line Items</span>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRow()">
                                    <i class="bi bi-plus"></i> Add Row
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <table class="table align-middle mb-0" id="itemsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Category <span class="text-danger">*</span></th>
                                            <th class="text-end">Amount <span class="text-danger">*</span></th>
                                            <th class="text-center">Mandatory</th>
                                            <th>Notes</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsBody">
                                        <tr id="rowTemplate" class="d-none item-row">
                                            <td>
                                                <select name="items[__IDX__][fee_category_id]" class="form-select form-select-sm" required>
                                                    <option value="">— select —</option>
                                                    @foreach($categories as $cat)
                                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" name="items[__IDX__][amount]"
                                                           step="0.01" min="0" class="form-control item-amount"
                                                           oninput="calcTotal()" required>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" name="items[__IDX__][is_mandatory]"
                                                       value="1" checked class="form-check-input">
                                            </td>
                                            <td>
                                                <input type="text" name="items[__IDX__][notes]"
                                                       class="form-control form-control-sm" placeholder="optional">
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                        onclick="removeRow(this)">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-light">
                                            <td colspan="2" class="text-end fw-semibold">Total:</td>
                                            <td class="fw-bold text-primary" id="totalDisplay">$0.00</td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Create Fee Structure
                            </button>
                            <a href="{{ route('finance.structures.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
let rowIndex = 0;
const template = document.getElementById('rowTemplate');

function addRow() {
    const clone = template.cloneNode(true);
    clone.id = 'row_' + rowIndex;
    clone.classList.remove('d-none');
    clone.innerHTML = clone.innerHTML.replaceAll('__IDX__', rowIndex);
    document.getElementById('itemsBody').appendChild(clone);
    rowIndex++;
}

function removeRow(btn) {
    btn.closest('tr').remove();
    calcTotal();
}

function calcTotal() {
    let total = 0;
    document.querySelectorAll('.item-amount').forEach(inp => {
        total += parseFloat(inp.value) || 0;
    });
    document.getElementById('totalDisplay').textContent = '$' + total.toFixed(2);
}

// Start with one row
addRow();
</script>
@endpush
