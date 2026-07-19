@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Alumni Directory</h1>
                <p class="text-slate-400 text-sm mt-0.5">{{ $alumni->total() }} alumni registered</p>
            </div>
            <a href="{{ route('students.graduation.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 transition text-slate-700">
                <i class="bi bi-arrow-left"></i> Back to Status Management
            </a>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('students.alumni') }}" class="flex flex-wrap gap-3 mb-6">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search alumni…" class="flex-1 min-w-48 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <select name="batch" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">All Batches</option>
                @foreach($batches as $b)<option value="{{ $b }}" {{ $batch === $b ? 'selected' : '' }}>{{ $b }}</option>@endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Filter</button>
        </form>

        @if($alumni->count())
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
            @foreach($alumni as $a)
            @php $cs = $a->currentStatus; @endphp
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center hover:shadow-md transition">
                <img src="{{ $a->avatar }}" class="w-14 h-14 rounded-full mx-auto mb-3 object-cover border-2 border-indigo-100" alt="">
                <p class="text-sm font-semibold text-slate-700 truncate">{{ $a->full_name }}</p>
                @if($cs?->alumni_batch)
                <p class="text-xs text-indigo-600 mt-0.5">{{ $cs->alumni_batch }}</p>
                @endif
                <p class="text-xs text-slate-400 mt-0.5">{{ $cs?->effective_date?->format('Y') ?? '—' }}</p>
                <a href="{{ route('student.profile.show', $a->id) }}" class="mt-2 inline-block text-xs text-indigo-600 hover:underline">Profile</a>
            </div>
            @endforeach
        </div>
        {{ $alumni->withQueryString()->links() }}
        @else
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
            <i class="bi bi-mortarboard text-4xl text-slate-200"></i>
            <p class="mt-3 text-slate-400 text-sm">No alumni records found.</p>
        </div>
        @endif

    </div>
</div>
@endsection
