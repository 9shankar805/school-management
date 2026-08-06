@extends('layouts.app')
@section('title', 'Raise Purchase Order')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-bold text-gray-800 mb-1">Raise Purchase Order</h2>
            <p class="text-muted mb-0">Create a new PO draft to send to a supplier.</p>
        </div>
        <a href="{{ route('inventory.purchase-orders') }}" class="btn btn-outline-secondary shadow-sm"><i class="bi bi-arrow-left"></i> Back to Orders</a>
    </div>

    <!-- Form -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 p-md-5">
            <form method="POST" action="{{ route('inventory.purchase-orders.store') }}">
                @csrf
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label font-semibold">PO Number</label>
                        <input type="text" name="po_number" class="form-control" value="PO-{{ date('Ymd') }}-{{ rand(100,999) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label font-semibold">Supplier</label>
                        <select name="supplier_id" class="form-select" required>
                            <option value="">Select Supplier...</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label font-semibold">Order Date</label>
                        <input type="date" name="order_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label font-semibold">Total Estimated Amount (NPR)</label>
                        <input type="number" step="0.01" name="total_amount" class="form-control" placeholder="0.00" required>
                    </div>
                </div>
                
                <hr class="my-4">
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save as Draft</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
