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
                            <h1 class="display-6 mb-0"><i class="bi bi-calendar-range"></i> Installment Plans</h1>
                            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item active">Installments</li>
                            </ol></nav>
                        </div>
                        @can('create invoices')
                        <a href="{{ route('finance.installments.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> New Plan
                        </a>
                        @endcan
                    </div>

                    @include('session-messages')

                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Plan Name</th><th>Student</th><th>Installments</th>
                                        <th class="text-end">Total</th><th class="text-end">Paid</th>
                                        <th>Progress</th><th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($plans as $plan)
                                    @php
                                        $paid    = $plan->items->where('status','paid')->sum('amount');
                                        $total   = $plan->total_amount;
                                        $pct     = $total > 0 ? min(100, round($paid/$total*100)) : 0;
                                    @endphp
                                    <tr>
                                        <td class="fw-semibold">{{ $plan->name }}</td>
                                        <td>{{ optional($plan->student)->full_name }}</td>
                                        <td>
                                            <span class="text-success">{{ $plan->items->where('status','paid')->count() }}</span>
                                            / {{ $plan->num_installments }}
                                        </td>
                                        <td class="text-end">${{ number_format($total, 2) }}</td>
                                        <td class="text-end text-success">${{ number_format($paid, 2) }}</td>
                                        <td style="min-width:120px">
                                            <div class="progress" style="height:8px">
                                                <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
                                            </div>
                                            <small class="text-muted">{{ $pct }}%</small>
                                        </td>
                                        <td>
                                            <a href="{{ route('finance.installments.show', $plan->id) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                            @can('create invoices')
                                            <form method="POST" action="{{ route('finance.installments.destroy', $plan->id) }}"
                                                  class="d-inline" onsubmit="return confirm('Delete this plan?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                            </form>
                                            @endcan
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="7" class="text-center text-muted py-4">No installment plans yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="mt-3">{{ $plans->links() }}</div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
