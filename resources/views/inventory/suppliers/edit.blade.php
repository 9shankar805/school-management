@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4" style="max-width:720px">

                    <h1 class="display-6 mb-1"><i class="bi bi-truck"></i> Edit Supplier</h1>
                    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-3">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('inventory.suppliers.index') }}">Suppliers</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol></nav>

                    @include('session-messages')

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form method="POST" action="{{ route('inventory.suppliers.update', $supplier->id) }}">
                                @csrf @method('PUT')
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label fw-semibold">Supplier Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" value="{{ old('name', $supplier->name) }}"
                                               class="form-control @error('name') is-invalid @enderror" required>
                                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                        <select name="status" class="form-select" required>
                                            <option value="active"   @selected(old('status',$supplier->status)=='active')>Active</option>
                                            <option value="inactive" @selected(old('status',$supplier->status)=='inactive')>Inactive</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Contact Person</label>
                                        <input type="text" name="contact_person"
                                               value="{{ old('contact_person', $supplier->contact_person) }}"
                                               class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Phone</label>
                                        <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}"
                                               class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Email</label>
                                        <input type="email" name="email" value="{{ old('email', $supplier->email) }}"
                                               class="form-control @error('email') is-invalid @enderror">
                                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Tax / VAT Number</label>
                                        <input type="text" name="tax_number"
                                               value="{{ old('tax_number', $supplier->tax_number) }}"
                                               class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Address</label>
                                        <textarea name="address" class="form-control" rows="2">{{ old('address', $supplier->address) }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Bank Account</label>
                                        <input type="text" name="bank_account"
                                               value="{{ old('bank_account', $supplier->bank_account) }}"
                                               class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Notes</label>
                                        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $supplier->notes) }}</textarea>
                                    </div>
                                </div>
                                <hr class="my-4">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update Supplier</button>
                                    <a href="{{ route('inventory.suppliers.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
