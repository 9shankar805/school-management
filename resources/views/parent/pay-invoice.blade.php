@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        @include('parent.partials.child-selector')

        <div class="mb-6">
            <a href="{{ route('parent.fees', $child->id) }}" class="text-xs text-indigo-600 hover:underline">
                <i class="bi bi-arrow-left me-1"></i>Back to Fees
            </a>
            <h1 class="text-xl font-bold text-slate-800 mt-2">Pay Invoice</h1>
        </div>

        @include('session-messages')

        <div class="max-w-lg">
            {{-- Invoice summary --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5 mb-5">
                <p class="text-xs text-slate-400 uppercase font-semibold tracking-wide mb-3">Invoice Summary</p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Invoice</span>
                        <span class="font-semibold text-slate-800">{{ $invoice->title }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Total Amount</span>
                        <span class="font-semibold text-slate-800">{{ number_format($invoice->amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Already Paid</span>
                        <span class="font-semibold text-emerald-600">{{ number_format($invoice->amount - $remaining, 2) }}</span>
                    </div>
                    <div class="flex justify-between border-t border-slate-100 pt-2 mt-2">
                        <span class="text-slate-700 font-semibold">Remaining Balance</span>
                        <span class="font-bold text-rose-600 text-base">{{ number_format($remaining, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Payment form --}}
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-slate-700 mb-4">Enter Payment Details</p>
                <form method="POST" action="{{ route('parent.fees.process', [$child->id, $invoice->id]) }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Amount to Pay <span class="text-rose-500">*</span></label>
                            <input type="number" name="amount_paid" step="0.01" min="0.01" max="{{ $remaining }}"
                                   value="{{ old('amount_paid', $remaining) }}"
                                   class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300 @error('amount_paid') border-rose-400 @enderror">
                            @error('amount_paid')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Payment Method <span class="text-rose-500">*</span></label>
                            <select name="payment_method"
                                    class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300 @error('payment_method') border-rose-400 @enderror">
                                <option value="">— Select —</option>
                                <option value="cash"          {{ old('payment_method') === 'cash'          ? 'selected' : '' }}>Cash</option>
                                <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="online"        {{ old('payment_method') === 'online'        ? 'selected' : '' }}>Online</option>
                                <option value="cheque"        {{ old('payment_method') === 'cheque'        ? 'selected' : '' }}>Cheque</option>
                            </select>
                            @error('payment_method')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex gap-3 mt-5">
                        <button type="submit" class="flex-1 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition">
                            <i class="bi bi-credit-card me-1"></i>Submit Payment
                        </button>
                        <a href="{{ route('parent.fees', $child->id) }}"
                           class="px-4 py-2.5 bg-slate-100 text-slate-600 text-sm rounded-lg hover:bg-slate-200 transition">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
