@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4" style="max-width:860px">

                    <h1 class="display-6 mb-1"><i class="bi bi-pencil-square"></i> Edit Fee Structure</h1>
                    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-3">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('finance.structures.index') }}">Fee Structures</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol></nav>

                    @include('session-messages')

                    <form method="POST" action="{{ route('finance.structures.update', $structure->id) }}">
                        @csrf @method('PUT')

                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white fw-semibold">Structure Details</div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" value="{{ old('name', $structure->name) }}"
                                               class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Session</label>
                                        <select name="session_id" class="form-select">
                                            <option value="">— any —</option>
                                            @foreach($sessions as $s)
                                            <option value="{{ $s->id }}" @selected(old('session_id', $structure->session_id)==$s->id)>{{ $s->session_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Term</label>
                                        <select name="term_id" class="form-select">
                                            <option value="">— any —</option>
                                            @foreach($terms as $t)
                                            <option value="{{ $t->id }}" @selected(old('term_id', $structure->term_id)==$t->id)>{{ $t->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Class</label>
                                        <select name="class_id" class="form-select">
                                            <option value="">— all —</option>
                                            @foreach($classes as $c)
                                            <option value="{{ $c->id }}" @selected(old('class_id', $structure->class_id)==$c->id)>{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Program</label>
                                        <select name="program_id" class="form-select">
                                            <option value="">— any —</option>
                                            @foreach($programs as $p)
                                            <option value="{{ $p->id }}" @selected(old('program_id', $structure->program_id)==$p->id)>{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Notes</label>
                                        <input type="text" name="notes" value="{{ old('notes', $structure->notes) }}" class="form-control">
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <div class="form-check mb-2">
                                            <input type="checkbox" name="is_active" value="1"
                                                   class="form-check-input"
                                                   @checked(old('is_active', $structure->is_active)) id="is_active">
                                            <label class="form-check-label" for="is_active">Active</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                                <span class="fw-semibold">Fee Line Items</span>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRow()">
                                    <i class="bi bi-plus"></i> Add Row
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <table class="table align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Category</th><th class="text-end">Amount</th>
                                            <th class="text-center">Mandatory</th><th>Notes</th><th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsBody">
                                        @foreach($structure->items as $i => $item)
                                        <tr class="item-row">
                                            <td>
                                                <select name="items[{{ $i }}][fee_category_id]" class="form-select form-select-sm" required>
                                                    <option value="">— select —</option>
                                                    @foreach($categories as $cat)
                                                    <option value="{{ $cat->id }}" @selected($cat->id == $item->fee_category_id)>{{ $cat->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" name="items[{{ $i }}][amount]"
                                                           step="0.01" min="0" value="{{ $item->amount }}"
                                                           class="form-control item-amount" oninput="calcTotal()" required>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" name="items[{{ $i }}][is_mandatory]"
                                                       value="1" class="form-check-input"
                                                       @checked($item->is_mandatory)>
                                            </td>
                                            <td>
                                                <input type="text" name="items[{{ $i }}][notes]"
                                                       value="{{ $item->notes }}"
                                                       class="form-control form-control-sm">
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                        onclick="removeRow(this)">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-light">
                                            <td colspan="2" class="text-end fw-semibold">Total:</td>
                                            <td class="fw-bold text-primary" id="totalDisplay">${{ number_format($structure->total_amount, 2) }}</td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Changes</button>
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
let rowIndex = {{ $structure->items->count() }};

// Template category options
const catOptions = `@foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach`;

function addRow() {
    const tbody = document.getElementById('itemsBody');
    const tr    = document.createElement('tr');
    tr.className = 'item-row';
    tr.innerHTML = `
        <td><select name="items[${rowIndex}][fee_category_id]" class="form-select form-select-sm" required>
            <option value="">— select —</option>${catOptions}
        </select></td>
        <td><div class="input-group input-group-sm">
            <span class="input-group-text">$</span>
            <input type="number" name="items[${rowIndex}][amount]" step="0.01" min="0"
                   class="form-control item-amount" oninput="calcTotal()" required>
        </div></td>
        <td class="text-center"><input type="checkbox" name="items[${rowIndex}][is_mandatory]" value="1" checked class="form-check-input"></td>
        <td><input type="text" name="items[${rowIndex}][notes]" class="form-control form-control-sm"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)"><i class="bi bi-x"></i></button></td>`;
    tbody.appendChild(tr);
    rowIndex++;
}

function removeRow(btn) { btn.closest('tr').remove(); calcTotal(); }

function calcTotal() {
    let t = 0;
    document.querySelectorAll('.item-amount').forEach(i => t += parseFloat(i.value) || 0);
    document.getElementById('totalDisplay').textContent = '$' + t.toFixed(2);
}
</script>
@endpush
