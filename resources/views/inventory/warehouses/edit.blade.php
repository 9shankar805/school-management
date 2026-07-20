@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4" style="max-width:720px">

                    <h1 class="display-6 mb-1"><i class="bi bi-building"></i> Edit Store / Warehouse</h1>
                    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-3">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('inventory.warehouses.index') }}">Warehouses</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol></nav>

                    @include('session-messages')

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form method="POST" action="{{ route('inventory.warehouses.update', $warehouse->id) }}">
                                @csrf @method('PUT')
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" value="{{ old('name', $warehouse->name) }}"
                                               class="form-control @error('name') is-invalid @enderror" required>
                                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                                        <input type="text" name="code" value="{{ old('code', $warehouse->code) }}"
                                               class="form-control @error('code') is-invalid @enderror" required>
                                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                                        <select name="type" class="form-select" required>
                                            @foreach($types as $k => $v)
                                            <option value="{{ $k }}" @selected(old('type',$warehouse->type)==$k)>{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                        <select name="status" class="form-select" required>
                                            <option value="active"   @selected(old('status',$warehouse->status)=='active')>Active</option>
                                            <option value="inactive" @selected(old('status',$warehouse->status)=='inactive')>Inactive</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Location</label>
                                        <input type="text" name="location" value="{{ old('location', $warehouse->location) }}"
                                               class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Manager Name</label>
                                        <input type="text" name="manager_name"
                                               value="{{ old('manager_name', $warehouse->manager_name) }}"
                                               class="form-control">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-semibold">Phone</label>
                                        <input type="text" name="phone" value="{{ old('phone', $warehouse->phone) }}"
                                               class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Description</label>
                                        <textarea name="description" class="form-control" rows="2">{{ old('description', $warehouse->description) }}</textarea>
                                    </div>
                                </div>
                                <hr class="my-4">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update</button>
                                    <a href="{{ route('inventory.warehouses.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
