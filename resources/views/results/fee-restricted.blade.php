@extends('layouts.app')

@section('content')
<div class="px-4 lg:px-8 py-8 max-w-3xl mx-auto">

    @include('session-messages')

    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm p-8 text-center">
        {{-- Shield Icon --}}
        <div class="w-20 h-20 rounded-3xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-600 dark:text-amber-400 flex items-center justify-center mx-auto mb-6 text-3xl shadow-inner">
            <i class="bi bi-shield-lock-fill"></i>
        </div>

        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 mb-3">
            <i class="bi bi-exclamation-octagon"></i> Institutional Fee Clearance Policy
        </span>

        <h1 class="text-2xl font-bold text-slate-800 dark:text-white mb-2">
            Mark Sheet & Report Card Withheld
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 max-w-lg mx-auto mb-6">
            In accordance with school policy, terminal examination marksheets and report cards are locked until outstanding academic dues are cleared.
        </p>

        {{-- Outstanding Balance Card --}}
        <div class="bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 max-w-md mx-auto mb-8 text-left">
            <div class="flex justify-between items-center pb-3 border-b border-slate-200 dark:border-slate-700">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Student</span>
                <span class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $user->full_name }}</span>
            </div>
            <div class="flex justify-between items-center py-3 border-b border-slate-200 dark:border-slate-700">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Term / Semester</span>
                <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                    {{ $semesters->firstWhere('id', $semesterId)?->semester_name ?? 'Current Term' }}
                </span>
            </div>
            <div class="flex justify-between items-center pt-3">
                <span class="text-xs font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wide">Outstanding Dues</span>
                <span class="text-xl font-extrabold text-rose-600 dark:text-rose-400">
                    Rs. {{ number_format($dueBalance, 2) }}
                </span>
            </div>
        </div>

        {{-- Payment Actions --}}
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 max-w-md mx-auto">
            @if(isset($latestInvoice) && $latestInvoice)
            <a href="{{ route('payments.pay', $latestInvoice->id) }}"
               class="w-full sm:w-auto flex-1 inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md shadow-emerald-600/20 transition">
                <i class="bi bi-wallet2 text-base"></i> Pay Dues Online
            </a>
            <a href="{{ route('payments.invoice-detail', $latestInvoice->id) }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-sm font-semibold transition">
                View Bill
            </a>
            @else
            <a href="{{ route('payments.student-index') }}"
               class="w-full sm:w-auto flex-1 inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md shadow-emerald-600/20 transition">
                <i class="bi bi-receipt text-base"></i> View My Invoices
            </a>
            @endif
        </div>

        <p class="text-xs text-slate-400 dark:text-slate-500 mt-6">
            If you have already paid recently or believe this is in error, please contact the School Account Section with your payment voucher.
        </p>
    </div>
</div>
@endsection
