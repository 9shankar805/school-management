@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4" style="max-width:860px">

                    <h1 class="display-6 mb-1"><i class="bi bi-clipboard-plus"></i> Register Asset</h1>
                    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-3">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('inventory.assets.index') }}">Assets</a></li>
                        <li class="breadcrumb-item active">New</li>
                    </ol></nav>

                    @include('session-messages')

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form method="POST" action="{{ route('inventory.assets.store') }}" enctype="multipart/form-data">
                                @csrf
                                <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size:.7rem;letter-spacing:.06em">Basic Information</h6>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Asset Code <span class="text-danger">*</span></label>
                                        <input type="text" name="asset_code" value="{{ old('asset_code', $nextCode) }}"
                                               class="form-control @error('asset_code') is-invalid @enderror" required>
                                        @error('asset_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Asset Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" value="{{ old('name') }}"
                                               class="form-control @error('name') is-invalid @enderror" required>
                                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                                        <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                            <option value="">— select —</option>
                                            @foreach($categories as $k => $v)
                                            <option value="{{ $k }}" @selected(old('category')==$k)>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Brand</label>
                                        <input type="text" name="brand" value="{{ old('brand') }}" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Model</label>
                                        <input type="text" name="model" value="{{ old('model') }}" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Serial Number</label>
                                        <input type="text" name="serial_number" value="{{ old('serial_number') }}" class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Description</label>
                                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                                    </div>
                                </div>

                                <hr class="my-4">
                                <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size:.7rem;letter-spacing:.06em">Purchase &amp; Value</h6>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Purchase Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" name="purchase_price" step="0.01" min="0"
                                                   value="{{ old('purchase_price') }}" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Purchase Date</label>
                                        <input type="date" name="purchase_date" value="{{ old('purchase_date') }}" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Warranty Expiry</label>
                                        <input type="date" name="warranty_expiry" value="{{ old('warranty_expiry') }}" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Current Value</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" name="current_value" step="0.01" min="0"
                                                   value="{{ old('current_value') }}" class="form-control">
                                        </div>
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
                                </div>

                                <hr class="my-4">
                                <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size:.7rem;letter-spacing:.06em">Location &amp; Status</h6>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Condition <span class="text-danger">*</span></label>
                                        <select name="condition" class="form-select" required>
                                            @foreach($conditions as $k => $v)
                                            <option value="{{ $k }}" @selected(old('condition','new')==$k)>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                        <select name="status" class="form-select" required>
                                            @foreach($statuses as $k => $v)
                                            <option value="{{ $k }}" @selected(old('status','available')==$k)>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Store / Warehouse</label>
                                        <select name="warehouse_id" class="form-select">
                                            <option value="">— none —</option>
                                            @foreach($warehouses as $wid => $wname)
                                            <option value="{{ $wid }}" @selected(old('warehouse_id')==$wid)>{{ $wname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Location</label>
                                        <input type="text" name="location" value="{{ old('location') }}"
                                               class="form-control" placeholder="Room / Block">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Assigned To</label>
                                        <input type="text" name="assigned_to" value="{{ old('assigned_to') }}"
                                               class="form-control" placeholder="Department or person name">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Photo</label>
                                        <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                                        <div class="form-text">Max 3 MB</div>
                                    </div>
                                </div>

                                <hr class="my-4">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Register Asset</button>
                                    <a href="{{ route('inventory.assets.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
