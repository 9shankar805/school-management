@extends('layouts.app')
@section('title', 'Add Consumable Item')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-bold text-gray-800 mb-1">Add Consumable Item</h2>
            <p class="text-muted mb-0">Register a new inventory item to track stock.</p>
        </div>
        <a href="{{ route('inventory.items') }}" class="btn btn-outline-secondary shadow-sm"><i class="bi bi-arrow-left"></i> Back to Items</a>
    </div>

    <!-- Form -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 p-md-5">
            <form method="POST" action="{{ route('inventory.items.store') }}">
                @csrf
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label font-semibold">Item Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g., A4 Printer Paper" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label font-semibold">SKU / Code</label>
                        <input type="text" name="sku" class="form-control" placeholder="e.g., PAP-A4-500">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label font-semibold">Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category...</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label font-semibold">Unit of Measurement</label>
                        <select name="unit" class="form-select" required>
                            <option value="pcs">Pieces (pcs)</option>
                            <option value="boxes">Boxes</option>
                            <option value="reams">Reams</option>
                            <option value="bottles">Bottles</option>
                            <option value="kg">Kilograms (kg)</option>
                            <option value="liters">Liters (L)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label font-semibold">Initial Stock Quantity</label>
                        <input type="number" name="quantity_in_stock" class="form-control" value="0" min="0" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label font-semibold">Minimum Stock Threshold (Alert Level)</label>
                        <input type="number" name="reorder_level" class="form-control" value="10" min="0" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label font-semibold">Unit Price (Rs.)</label>
                        <input type="number" name="unit_price" class="form-control" value="0" min="0" step="0.01">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label font-semibold">Location / Storage Room</label>
                        <input type="text" name="location" class="form-control" placeholder="e.g., Storeroom A">
                    </div>
                </div>
                
                <hr class="my-4">
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Item</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
