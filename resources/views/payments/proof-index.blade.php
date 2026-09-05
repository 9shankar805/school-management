@extends('layouts.app')
@section('title', 'Payment Proof Review')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-6xl mx-auto">

    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="w-12 h-12 rounded-2xl bg-indigo-600/10 text-indigo-600 flex items-center justify-center text-2xl">
                <i class="bi bi-shield-check"></i>
            </span>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    Payment Proof Review
                    @if($pendingCount > 0)
                    <span class="text-sm bg-rose-500 text-white px-2.5 py-0.5 rounded-full font-bold">{{ $pendingCount }} pending</span>
                    @endif
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Review QR payment screenshots submitted by parents and approve or reject them.</p>
            </div>
        </div>
    </div>

    @include('session-messages')

    {{-- Status filter tabs --}}
    <div class="flex gap-2 mb-6 flex-wrap">
        @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $s => $label)
        <a href="{{ route('payment-proofs.index', ['status' => $s]) }}"
           class="px-4 py-2 rounded-xl text-sm font-semibold transition border
           {{ $status === $s
                ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm'
                : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-indigo-400' }}">
            {{ $label }}
            @if($s === 'pending' && $pendingCount > 0)
            <span class="ml-1 bg-white/25 {{ $status === $s ? 'text-white' : 'bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400' }} text-xs px-1.5 py-0.5 rounded-full font-bold">{{ $pendingCount }}</span>
            @endif
        </a>
        @endforeach
    </div>

    @if($proofs->isEmpty())
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-12 text-center shadow-sm">
        <i class="bi bi-{{ $status === 'pending' ? 'hourglass' : ($status === 'approved' ? 'check-circle' : 'x-circle') }} text-5xl text-slate-200 dark:text-slate-700 block mb-3"></i>
        <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">No {{ $status }} proofs found.</p>
    </div>
    @else
    <div class="space-y-4">
        @foreach($proofs as $proof)
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="flex flex-col lg:flex-row">

                {{-- Screenshot --}}
                <div class="lg:w-48 flex-shrink-0 bg-slate-50 dark:bg-slate-800/50 flex items-center justify-center p-4 border-b lg:border-b-0 lg:border-r border-slate-100 dark:border-slate-800">
                    @if($proof->screenshot_url)
                    <a href="{{ $proof->screenshot_url }}" target="_blank">
                        <img src="{{ $proof->screenshot_url }}"
                             alt="Payment screenshot"
                             class="max-h-40 max-w-full rounded-xl border border-slate-200 dark:border-slate-700 object-contain bg-white shadow-sm hover:opacity-90 transition">
                    </a>
                    @else
                    <div class="w-32 h-32 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                        <i class="bi bi-image text-3xl text-slate-300 dark:text-slate-500"></i>
                    </div>
                    @endif
                </div>

                {{-- Details --}}
                <div class="flex-1 p-5 flex flex-col justify-between gap-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                <span class="text-sm font-bold text-white px-2.5 py-0.5 rounded-full"
                                      style="background: {{ $proof->gateway_color }}">
                                    {{ $proof->gateway_label }}
                                </span>
                                <span class="text-sm font-bold text-slate-800 dark:text-slate-100">
                                    NPR {{ number_format($proof->amount, 2) }}
                                </span>
                                @if($proof->status === 'pending')
                                <span class="text-xs bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 px-2.5 py-0.5 rounded-full font-semibold">
                                    <i class="bi bi-hourglass-split me-1"></i>Pending
                                </span>
                                @elseif($proof->status === 'approved')
                                <span class="text-xs bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 px-2.5 py-0.5 rounded-full font-semibold">
                                    <i class="bi bi-check-circle-fill me-1"></i>Approved
                                </span>
                                @else
                                <span class="text-xs bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400 px-2.5 py-0.5 rounded-full font-semibold">
                                    <i class="bi bi-x-circle-fill me-1"></i>Rejected
                                </span>
                                @endif
                            </div>

                            <p class="text-xs text-slate-500 dark:text-slate-400 space-x-2">
                                <span><i class="bi bi-person me-1"></i>
                                    <strong>{{ $proof->student?->full_name ?? '—' }}</strong>
                                    ({{ $proof->invoice?->title ?? '—' }})
                                </span>
                            </p>
                            @if($proof->transaction_id)
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                <i class="bi bi-hash me-1"></i>Transaction ID: <code class="bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-xs">{{ $proof->transaction_id }}</code>
                            </p>
                            @endif
                            @if($proof->notes)
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                <i class="bi bi-chat-text me-1"></i>{{ $proof->notes }}
                            </p>
                            @endif
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                                Submitted by <strong>{{ $proof->submittedBy?->full_name ?? '—' }}</strong>
                                · {{ $proof->created_at->format('d M Y, h:i A') }}
                            </p>
                            @if($proof->rejection_reason)
                            <p class="text-xs text-rose-600 dark:text-rose-400 mt-1">
                                <i class="bi bi-exclamation-circle me-1"></i>Rejection reason: {{ $proof->rejection_reason }}
                            </p>
                            @endif
                        </div>

                        {{-- Action buttons (pending only) --}}
                        @if($proof->status === 'pending')
                        <div class="flex flex-col sm:flex-row gap-2 flex-shrink-0">

                            {{-- Approve --}}
                            <form method="POST" action="{{ route('payment-proofs.approve', $proof->id) }}"
                                  onsubmit="return confirm('Approve this payment? This will record NPR {{ number_format($proof->amount,2) }} and mark the invoice as paid.')">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition shadow-sm">
                                    <i class="bi bi-check-lg"></i> Approve
                                </button>
                            </form>

                            {{-- Reject --}}
                            <button type="button"
                                    onclick="document.getElementById('reject-form-{{ $proof->id }}').classList.toggle('hidden')"
                                    class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-sm font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-100 dark:hover:bg-rose-900/40 border border-rose-200 dark:border-rose-800 transition">
                                <i class="bi bi-x-lg"></i> Reject
                            </button>
                        </div>
                        @endif
                    </div>

                    {{-- Reject form (hidden until button clicked) --}}
                    @if($proof->status === 'pending')
                    <form id="reject-form-{{ $proof->id }}" method="POST"
                          action="{{ route('payment-proofs.reject', $proof->id) }}"
                          class="hidden border-t border-slate-100 dark:border-slate-800 pt-4">
                        @csrf
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">
                            Reason for rejection <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex gap-2">
                            <input type="text" name="rejection_reason" required
                                   class="flex-1 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-sm bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-rose-400 focus:outline-none"
                                   placeholder="e.g. Screenshot unclear, wrong amount, transaction ID missing...">
                            <button type="submit"
                                    class="px-5 py-2 rounded-xl text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 transition flex-shrink-0">
                                Confirm Reject
                            </button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $proofs->links() }}
    </div>
    @endif

</div>
@endsection
