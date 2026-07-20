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
                            <h1 class="display-6 mb-0">
                                <i class="bi bi-cart3"></i> {{ $order->po_number }}
                                <span class="badge {{ $order->status_badge }} ms-2" style="font-size:.6em">
                                    {{ \App\Models\PurchaseOrder::STATUSES[$order->status] ?? $order->status }}
                                </span>
                            </h1>
                            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('inventory.purchase-orders.index') }}">Purchase Orders</a></li>
                                <li class="breadcrumb-item active">{{ $order->po_number }}</li>
                            </ol></nav>
                        </div>
                        <div class="d-flex gap-2">
                            @can('create purchase orders')
                            @if(in_array($order->status, ['draft','submitted']))
                            <a href="{{ route('inventory.purchase-orders.edit', $order->id) }}"
                               class="btn btn-outline-secondary"><i class="bi bi-pencil"></i> Edit</a>
                            @endif
                            @endcan
                        </div>
                    </div>

                    @include('session-messages')

                    <div class="row g-4">
                        {{-- Order meta --}}
                        <div class="col-md-5">
                            <div class="card shadow-sm">
                                <div class="card-header fw-semibold">Order Info</div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr><th class="text-muted" style="width:40%">PO Number</th><td><strong>{{ $order->po_number }}</strong></td></tr>
                                        <tr><th class="text-muted">Supplier</th><td>{{ $order->supplier?->name ?? '—' }}</td></tr>
                                        <tr><th class="text-muted">Order Date</th><td>{{ $order->order_date->format('d M Y') }}</td></tr>
                                        <tr><th class="text-muted">Expected</th><td>{{ $order->expected_delivery?->format('d M Y') ?? '—' }}</td></tr>
                                        <tr><th class="text-muted">Delivered</th><td>{{ $order->delivered_date?->format('d M Y') ?? '—' }}</td></tr>
                                        <tr><th class="text-muted">Payment</th><td>{{ \App\Models\PurchaseOrder::PAYMENT_METHODS[$order->payment_method] ?? $order->payment_method ?? '—' }}</td></tr>
                                        <tr><th class="text-muted">Reference</th><td>{{ $order->reference_no ?? '—' }}</td></tr>
                                        <tr><th class="text-muted">Created By</th><td>{{ $order->createdBy?->full_name ?? '—' }}</td></tr>
                                        <tr><th class="text-muted">Approved By</th><td>{{ $order->approvedBy?->full_name ?? '—' }}</td></tr>
                                        <tr><th class="text-muted">Total</th>
                                            <td class="fw-bold text-success fs-5">${{ number_format($order->total_amount, 2) }}</td></tr>
                                    </table>
                                    @if($order->notes)
                                    <p class="text-muted small mt-3 mb-0"><strong>Notes:</strong> {{ $order->notes }}</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Status workflow --}}
                            @can('manage inventory')
                            @if(!in_array($order->status, ['received', 'cancelled']))
                            <div class="card shadow-sm mt-3">
                                <div class="card-header fw-semibold">Update Status</div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('inventory.purchase-orders.status', $order->id) }}">
                                        @csrf @method('PATCH')
                                        <div class="row g-2 align-items-end">
                                            <div class="col">
                                                <label class="form-label fw-semibold">New Status</label>
                                                <select name="status" class="form-select form-select-sm" required>
                                                    @if($order->status === 'draft')
                                                        <option value="submitted">Submit</option>
                                                        <option value="cancelled">Cancel</option>
                                                    @elseif($order->status === 'submitted')
                                                        <option value="approved">Approve</option>
                                                        <option value="cancelled">Cancel</option>
                                                    @elseif($order->status === 'approved')
                                                        <option value="received">Mark Received</option>
                                                        <option value="cancelled">Cancel</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="col">
                                                <label class="form-label fw-semibold">Delivered Date</label>
                                                <input type="date" name="delivered_date" class="form-control form-control-sm"
                                                       value="{{ now()->toDateString() }}">
                                            </div>
                                            <div class="col-auto">
                                                <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                            </div>
                                        </div>
                                        <div class="form-text mt-1">
                                            Marking as <strong>Received</strong> will automatically add consumable quantities to stock.
                                        </div>
                                    </form>
                                </div>
                            </div>
                            @endif
                            @endcan
                        </div>

                        {{-- Line items --}}
                        <div class="col-md-7">
                            <div class="card shadow-sm">
                                <div class="card-header fw-semibold">Line Items</div>
                                <div class="card-body p-0">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Item</th>
                                                <th>Type</th>
                                                <th class="text-center">Qty</th>
                                                <th class="text-end">Unit Price</th>
                                                <th class="text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($order->items as $i => $line)
                                            <tr>
                                                <td class="text-muted">{{ $i + 1 }}</td>
                                                <td>
                                                    {{ $line->item_name }}
                                                    @if($line->inventoryItem)
                                                    <br><small class="text-muted">{{ $line->inventoryItem->item_code }}</small>
                                                    @endif
                                                </td>
                                                <td><span class="badge bg-light text-dark">{{ ucfirst($line->item_type) }}</span></td>
                                                <td class="text-center">{{ $line->quantity }}</td>
                                                <td class="text-end">${{ number_format($line->unit_price, 2) }}</td>
                                                <td class="text-end fw-semibold">${{ number_format($line->line_total, 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-light">
                                                <td colspan="5" class="text-end fw-bold">Total</td>
                                                <td class="text-end fw-bold text-success">
                                                    ${{ number_format($order->total_amount, 2) }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
