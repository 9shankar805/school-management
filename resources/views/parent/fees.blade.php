@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        @include('parent.partials.child-selector')
        @include('parent.partials.page-header', ['title' => 'Fees & Payments'])

        @include('session-messages')

        {{-- Outstanding balance --}}
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5 mb-6 flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-400 uppercase font-semibold tracking-wide">Total Outstanding</p>
                <p class="text-3xl font-bold {{ $totalOutstanding > 0 ? 'text-rose-600' : 'text-emerald-600' }} mt-1">
                    {{ number_format($totalOutstanding, 2) }}
                </p>
            </div>
            @if($totalOutstanding == 0)
            <span class="text-xs bg-emerald-100 text-emerald-700 px-3 py-1.5 rounded-full font-semibold">All Paid</span>
            @endif
        </div>

        {{-- Invoice list --}}
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden mb-6">
            <div class="px-5 py-3 border-b border-slate-100">
                <p class="text-sm font-semibold text-slate-700">Invoices</p>
            </div>
            @if($invoices->isEmpty())
            <p class="text-sm text-slate-400 text-center py-10">No invoices found.</p>
            @else
            <div class="divide-y divide-slate-50">
                @foreach($invoices as $inv)
                @php
                    $badge = match($inv->display_status) {
                        'paid'    => 'bg-emerald-100 text-emerald-700',
                        'overdue' => 'bg-rose-100 text-rose-700',
                        'partial' => 'bg-amber-100 text-amber-700',
                        default   => 'bg-slate-100 text-slate-600',
                    };
                @endphp
                <div class="px-5 py-4 flex items-center gap-4 flex-wrap">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-slate-800 text-sm">{{ $inv->title }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Amount: <span class="font-medium text-slate-600">{{ number_format($inv->amount, 2) }}</span>
                            @if($inv->due_date) · Due: {{ \Carbon\Carbon::parse($inv->due_date)->format('d M Y') }} @endif
                            @if($inv->remaining > 0) · Remaining: <span class="text-rose-600 font-medium">{{ number_format($inv->remaining, 2) }}</span> @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $badge }} capitalize">{{ $inv->display_status }}</span>
                        @if(in_array($inv->display_status, ['unpaid', 'overdue', 'partial']))
                        <a href="{{ route('parent.fees.pay', [$child->id, $inv->id]) }}"
                           class="text-xs px-3 py-1.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
                            Pay Now
                        </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Payment history --}}
        @if($paymentHistory->isNotEmpty())
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100">
                <p class="text-sm font-semibold text-slate-700">Payment History</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="text-left px-4 py-2 text-xs font-semibold text-slate-500">Invoice</th>
                            <th class="text-center px-4 py-2 text-xs font-semibold text-slate-500">Amount Paid</th>
                            <th class="text-center px-4 py-2 text-xs font-semibold text-slate-500">Date</th>
                            <th class="text-center px-4 py-2 text-xs font-semibold text-slate-500">Method</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($paymentHistory as $pay)
                        <tr>
                            <td class="px-4 py-2 text-slate-700">{{ $pay->invoice?->title ?? '—' }}</td>
                            <td class="px-4 py-2 text-center font-semibold text-emerald-700">{{ number_format($pay->amount_paid, 2) }}</td>
                            <td class="px-4 py-2 text-center text-slate-500">{{ \Carbon\Carbon::parse($pay->payment_date)->format('d M Y') }}</td>
                            <td class="px-4 py-2 text-center capitalize text-slate-500">{{ str_replace('_', ' ', $pay->payment_method ?? '—') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
