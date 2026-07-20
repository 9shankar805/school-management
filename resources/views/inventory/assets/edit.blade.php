@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4" style="max-width:860px">

                    <h1 class="display-6 mb-1"><i class="bi bi-clipboard-check"></i> Edit Asset</h1>
                    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-3">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('inventory.assets.index') }}">Assets</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('inventory.assets.show', $asset->id) }}">{{ $asset->asset_code }}</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol></nav>

                    @include('session-messages')

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form method="POST" action="{{ route('inventory.assets.update', $asset->id) }}" enctype="multipart/form-data">
                                @csrf @method('PUT')
                                <h6 class="text-uppercase text-muted fw-bold mb-3" style="font-size:.7rem;letter-spacing:.06em">Basic Information</h6>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Asset Code <span class="text-danger">*</span></label>
                                        <input type="text" name="asset_code" value="{{ old('asset_code', $asset->asset_code) }}"
                                               class="form-control @error('asset_code') is-invalid @enderror" required>
                                        @error('asset_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Asset Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" value="{{ old('name', $asset->name) }}"
                                               class="form-control @error('name') is-invalid @enderror" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                                        <select name="category" class="form-select" required>
                                            <option value="">— select —</option>
                                            @foreach($categories as $k => $v)
                                            <option value="{{ $k }}" @selected(old('category',$asset->category)==$k)>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Brand</label>
                                        <input type="text" name="brand" value="{{ old('brand', $asset->brand) }}" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Model</label>
                                        <input type="text" name="model" value="{{ old('model', $asset->model) }}" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Serial Number</label>
                                        <input type="text" name="serial_number" value="{{ old('serial_number', $asset->serial_number) }}" class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Description</label>
                                        <textarea name="description" class="form-control" rows="2">{{ old('description', $asset->description) }}</textarea>
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
                                                   value="{{ old('purchase_price', $asset->purchase_price) }}" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Purchase Date</label>
                                        <input type="date" name="purchase_date"
                                               value="{{ old('purchase_date', $asset->purchase_date?->toDateString()) }}" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Warranty Expiry</label>
                                        <input type="date" name="warranty_expiry"
                                               value="{{ old('warranty_expiry', $asset->warranty_expiry?->toDateString()) }}" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Current Value</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" name="current_value" step="0.01" min="0"
                                                   value="{{ old('current_value', $asset->current_value) }}" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Supplier</label>
                                        <select name="supplier_id" class="form-select">
                                            <option value="">— none —</option>
                                            @foreach($suppliers as $sid => $sname)
                                            <option value="{{ $sid }}" @selected(old('supplier_id',$asset->supplier_id)==$sid)>{{ $sname }}</option>
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
                                            <option value="{{ $k }}" @selected(old('condition',$asset->condition)==$k)>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                        <select name="status" class="form-select" required>
                                            @foreach($statuses as $k => $v)
                                            <option value="{{ $k }}" @selected(old('status',$asset->status)==$k)>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Store / Warehouse</label>
                                        <select name="warehouse_id" class="form-select">
                                            <option value="">— none —</option>
                                            @foreach($warehouses as $wid => $wname)
                                            <option value="{{ $wid }}" @selected(old('warehouse_id',$asset->warehouse_id)==$wid)>{{ $wname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Location</label>
                                        <input type="text" name="location" value="{{ old('location', $asset->location) }}" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Assigned To</label>
                                        <input type="text" name="assigned_to" value="{{ old('assigned_to', $asset->assigned_to) }}" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Replace Photo</label>
                                        @if($asset->image_path)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/'.$asset->image_path) }}"
                                                 class="img-thumbnail" style="max-height:80px" alt="current">
                                        </div>
                                        @endif
                                        <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                                    </div>
                                </div>

                                <hr class="my-4">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update Asset</button>
                                    <a href="{{ route('inventory.assets.show', $asset->id) }}" class="btn btn-outline-secondary">Cancel</a>
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
