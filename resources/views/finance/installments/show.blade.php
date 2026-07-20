@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4" style="max-width:820px">

                    <h1 class="display-6 mb-1"><i class="bi bi-calendar-range"></i> Installment Plan</h1>
                    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-3">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('finance.installments.index') }}">Installments</a></li>
                        <li class="breadcrumb-item active">{{ $plan->name }}</li>
                    </ol></nav>

                    @include('session-messages')

                    {{-- Summary card --}}
                    <div class="card mb-4 border-0 bg-light rounded-3">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-6 col-md-3">
                                    <p class="text-muted small mb-0">Student</p>
                                    <strong>{{ optional($plan->student)->full_name }}</strong>
                                </div>
                                <div class="col-6 col-md-3">
                                    <p class="text-muted small mb-0">Total Amount</p>
                                    <strong>${{ number_format($plan->total_amount, 2) }}</strong>
                                </div>
                                <div class="col-6 col-md-3">
                                    <p class="text-muted small mb-0">Paid</p>
                                    <strong class="text-success">${{ number_format($plan->paid_amount, 2) }}</strong>
                                </div>
                                <div class="col-6 col-md-3">
                                    <p class="text-muted small mb-0">Remaining</p>
                                    <strong class="text-danger">${{ number_format($plan->pending_amount, 2) }}</strong>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="progress" style="height:10px">
                                    <div class="progress-bar bg-success" style="width:{{ $plan->progress_percent }}%"></div>
                                </div>
                                <small class="text-muted">{{ $plan->progress_percent }}% paid</small>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-white fw-semibold">Installment Schedule</div>
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th><th>Due Date</th><th class="text-end">Amount</th>
                                        <th>Status</th><th>Paid Date</th><th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($plan->items as $item)
                                    <tr>
                                        <td>{{ $item->installment_no }}</td>
                                        <td>{{ $item->due_date->format('d M Y') }}
                                            @if($item->status === 'overdue')
                                            <span class="badge bg-danger ms-1">Overdue</span>
                                            @endif
                                        </td>
                                        <td class="text-end">${{ number_format($item->amount, 2) }}</td>
                                        <td><span class="badge {{ $item->status_badge }}">{{ ucfirst($item->status) }}</span></td>
                                        <td>{{ $item->paid_date?->format('d M Y') ?? '—' }}</td>
                                        <td>
                                            @if(in_array($item->status, ['pending','overdue']))
                                            @can('create invoices')
                                            <button class="btn btn-sm btn-success"
                                                    onclick="openPay({{ $item->id }})">
                                                <i class="bi bi-cash"></i> Mark Paid
                                            </button>
                                            @endcan
                                            @elseif($item->status === 'paid' && $item->payment)
                                            <a href="{{ route('payments.receipt', $item->payment_id) }}"
                                               class="btn btn-sm btn-outline-secondary" target="_blank">
                                                <i class="bi bi-printer"></i> Receipt
                                            </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>

{{-- Mark paid modal --}}
<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="payForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Record Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select" required>
                            @foreach(\App\Models\Payment::PAYMENT_METHODS as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Transaction Reference</label>
                        <input type="text" name="transaction_reference" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Paid Date</label>
                        <input type="date" name="paid_date" class="form-control" value="{{ now()->toDateString() }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Confirm Payment</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openPay(itemId) {
    document.getElementById('payForm').action = '/finance/installments/items/' + itemId + '/paid';
    new bootstrap.Modal(document.getElementById('payModal')).show();
}
</script>
@endpush
