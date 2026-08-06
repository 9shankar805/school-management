@extends('layouts.app')
@section('title', 'Add Supplier')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-bold text-gray-800 mb-1">Add Supplier</h2>
            <p class="text-muted mb-0">Register a new vendor for procurement.</p>
        </div>
        <a href="{{ route('inventory.suppliers') }}" class="btn btn-outline-secondary shadow-sm"><i class="bi bi-arrow-left"></i> Back to Suppliers</a>
    </div>

    <!-- Form -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 p-md-5">
            <form method="POST" action="{{ route('inventory.suppliers.store') }}">
                @csrf
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label font-semibold">Supplier/Company Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g., Office Depot" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label font-semibold">Contact Person</label>
                        <input type="text" name="contact_person" class="form-control" placeholder="e.g., John Smith" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label font-semibold">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="john@example.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label font-semibold">Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="+1 234 567 8900">
                    </div>
                    <div class="col-12">
                        <label class="form-label font-semibold">Physical Address</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="123 Business Rd, City, State"></textarea>
                    </div>
                </div>
                
                <hr class="my-4">
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
