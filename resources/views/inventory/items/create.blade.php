@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4" style="max-width:800px">

                    <h1 class="display-6 mb-1"><i class="bi bi-box-seam"></i> Add Consumable Item</h1>
                    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-3">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('inventory.items.index') }}">Stock Items</a></li>
                        <li class="breadcrumb-item active">New</li>
                    </ol></nav>

                    @include('session-messages')

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form method="POST" action="{{ route('inventory.items.store') }}">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Item Code <span class="text-danger">*</span></label>
                                        <input type="text" name="item_code" value="{{ old('item_code', $nextCode) }}"
                                               class="form-control @error('item_code') is-invalid @enderror" required>
                                        @error('item_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Item Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" value="{{ old('name') }}"
                                               class="form-control @error('name') is-invalid @enderror" required>
                                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                        <select name="status" class="form-select" required>
                                            <option value="active"   @selected(old('status','active')=='active')>Active</option>
                                            <option value="inactive" @selected(old('status')=='inactive')>Inactive</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                                        <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                            <option value="">— select —</option>
                                            @foreach($categories as $k => $v)
                                            <option value="{{ $k }}" @selected(old('category')==$k)>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Unit <span class="text-danger">*</span></label>
                                        <select name="unit" class="form-select @error('unit') is-invalid @enderror" required>
                                            <option value="">—</option>
                                            @foreach($units as $k => $v)
                                            <option value="{{ $k }}" @selected(old('unit')==$k)>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Opening Stock <span class="text-danger">*</span></label>
                                        <input type="number" name="quantity_in_stock" min="0"
                                               value="{{ old('quantity_in_stock', 0) }}"
                                               class="form-control @error('quantity_in_stock') is-invalid @enderror" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Reorder Level <span class="text-danger">*</span></label>
                                        <input type="number" name="reorder_level" min="0"
                                               value="{{ old('reorder_level', 5) }}"
                                               class="form-control @error('reorder_level') is-invalid @enderror" required>
                                        <div class="form-text">Alert triggered when stock &le; this value.</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Unit Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" name="unit_price" step="0.01" min="0"
                                                   value="{{ old('unit_price') }}" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Store / Warehouse</label>
                                        <select name="warehouse_id" class="form-select">
                                            <option value="">— none —</option>
                                            @foreach($warehouses as $wid => $wname)
                                            <option value="{{ $wid }}" @selected(old('warehouse_id')==$wid)>{{ $wname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Default Supplier</label>
                                        <select name="supplier_id" class="form-select">
                                            <option value="">— none —</option>
                                            @foreach($suppliers as $sid => $sname)
                                            <option value="{{ $sid }}" @selected(old('supplier_id')==$sid)>{{ $sname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Description</label>
                                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                                    </div>
                                </div>
                                <hr class="my-4">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Item</button>
                                    <a href="{{ route('inventory.items.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
