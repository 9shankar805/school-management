@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h1 class="display-6 mb-0"><i class="bi bi-cart3"></i> Purchase Orders</h1>
                            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item active">Purchase Orders</li>
                            </ol></nav>
                        </div>
                        @can('create purchase orders')
                        <a href="{{ route('inventory.purchase-orders.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> New PO
                        </a>
                        @endcan
                    </div>

                    @include('session-messages')

                    <div class="alert alert-info d-flex align-items-center gap-2 py-2 mb-3">
                        <i class="bi bi-cart-check"></i>
                        This month's PO value: <strong class="ms-1">${{ number_format($monthTotal, 2) }}</strong>
                    </div>

                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-sm-3">
                            <select name="supplier_id" class="form-select form-select-sm">
                                <option value="">All Suppliers</option>
                                @foreach($suppliers as $sid => $sname)
                                <option value="{{ $sid }}" @selected(request('supplier_id')==$sid)>{{ $sname }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Statuses</option>
                                @foreach($statuses as $k => $v)
                                <option value="{{ $k }}" @selected(request('status')==$k)>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-sm-2">
                            <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-sm-auto">
                            <button class="btn btn-sm btn-secondary">Filter</button>
                            <a href="{{ route('inventory.purchase-orders.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                        </div>
                    </form>

                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>PO #</th>
                                        <th>Supplier</th>
                                        <th>Order Date</th>
                                        <th>Expected</th>
                                        <th class="text-end">Total</th>
                                        <th>Status</th>
                                        <th>Created By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($orders as $po)
                                    <tr>
                                        <td>
                                            <a href="{{ route('inventory.purchase-orders.show', $po->id) }}"
                                               class="fw-semibold text-decoration-none">{{ $po->po_number }}</a>
                                        </td>
                                        <td>{{ $po->supplier?->name ?? '—' }}</td>
                                        <td>{{ $po->order_date->format('d M Y') }}</td>
                                        <td>{{ $po->expected_delivery?->format('d M Y') ?? '—' }}</td>
                                        <td class="text-end fw-semibold">${{ number_format($po->total_amount, 2) }}</td>
                                        <td><span class="badge {{ $po->status_badge }}">{{ \App\Models\PurchaseOrder::STATUSES[$po->status] ?? $po->status }}</span></td>
                                        <td>{{ $po->createdBy?->first_name ?? '—' }}</td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('inventory.purchase-orders.show', $po->id) }}"
                                                   class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                                @canany(['create purchase orders', 'manage inventory'])
                                                @if(in_array($po->status, ['draft','submitted']))
                                                <a href="{{ route('inventory.purchase-orders.edit', $po->id) }}"
                                                   class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                                @endif
                                                @if($po->status !== 'received')
                                                <form method="POST" action="{{ route('inventory.purchase-orders.destroy', $po->id) }}"
                                                      class="d-inline" onsubmit="return confirm('Delete this PO?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                </form>
                                                @endif
                                                @endcanany
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="8" class="text-center text-muted py-4">No purchase orders found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="mt-3">{{ $orders->links() }}</div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
