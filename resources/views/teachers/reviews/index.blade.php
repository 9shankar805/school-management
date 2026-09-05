@extends('layouts.app')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-7xl mx-auto">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-10 h-10 rounded-2xl bg-indigo-600/10 text-indigo-600 flex items-center justify-center font-bold text-xl shadow-xs">
                    <i class="bi bi-star flex"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Teacher Performance Reviews</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Evaluate, track, and manage teacher performance evaluations</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" class="flex items-center gap-2">
                <select name="period" class="text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100">
                    <option value="">All Review Periods</option>
                    @foreach($periods as $p)
                        <option value="{{ $p }}" {{ $period == $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs">Filter</button>
            </form>
        </div>
    </div>

    @include('session-messages')

    {{-- Reviews Directory Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <span class="w-2 h-5 bg-indigo-600 rounded-full"></span>
                Performance Review Records ({{ $reviews->total() }})
            </h2>
        </div>

        @if($reviews->isEmpty())
            <div class="p-12 text-center">
                <i class="bi bi-journal-x text-4xl text-slate-300 dark:text-slate-600 mb-2 block"></i>
                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">No performance reviews found</p>
                <p class="text-xs text-slate-400 mt-1">Evaluations submitted by management will appear here.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50 text-xs font-semibold text-slate-500 dark:text-slate-400 border-b border-slate-100 dark:border-slate-800">
                            <th class="px-6 py-3.5">Teacher</th>
                            <th class="px-6 py-3.5">Period</th>
                            <th class="px-6 py-3.5">Review Date</th>
                            <th class="px-6 py-3.5">Reviewer</th>
                            <th class="px-6 py-3.5">Overall Rating</th>
                            <th class="px-6 py-3.5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                        @foreach($reviews as $rev)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                                <td class="px-6 py-4 font-semibold text-slate-800 dark:text-slate-100">
                                    {{ $rev->teacher->first_name ?? '' }} {{ $rev->teacher->last_name ?? '' }}
                                </td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $rev->review_period }}</td>
                                <td class="px-6 py-4 text-slate-500 dark:text-slate-400">{{ \Carbon\Carbon::parse($rev->review_date)->format('M d, Y') }}</td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $rev->reviewer->first_name ?? '' }} {{ $rev->reviewer->last_name ?? '' }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1 font-bold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/30 px-2.5 py-1 rounded-full text-xs">
                                        <i class="bi bi-star-fill text-amber-500"></i> {{ number_format($rev->overall_rating, 1) }} / 5.0
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold capitalize bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                        {{ $rev->status ?? 'submitted' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
