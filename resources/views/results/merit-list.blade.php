@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="mb-7">
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight"><i class="bi bi-trophy me-2 text-amber-500"></i>Merit List</h1>
            <p class="text-slate-400 text-sm mt-0.5">
                {{ $class?->class_name }} · {{ $section?->section_name ?? 'All Sections' }} · {{ $semester?->semester_name }}
            </p>
        </div>

        @if($results->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center text-slate-400">
            <p class="text-sm">No results available for this selection.</p>
        </div>
        @else
        <div class="space-y-3">
            @foreach($results as $row)
            @php $rank = $row['rank']; @endphp
            <div class="bg-white rounded-2xl border {{ $rank <= 3 ? 'border-amber-200' : 'border-slate-100' }} shadow-sm p-4 flex items-center gap-4">
                {{-- Rank badge --}}
                <div class="w-12 h-12 rounded-xl flex-shrink-0 flex items-center justify-center font-bold text-lg
                    {{ $rank == 1 ? 'bg-amber-100 text-amber-600' : ($rank == 2 ? 'bg-slate-100 text-slate-600' : ($rank == 3 ? 'bg-orange-100 text-orange-600' : 'bg-slate-50 text-slate-400')) }}">
                    @if($rank == 1) 🥇 @elseif($rank == 2) 🥈 @elseif($rank == 3) 🥉 @else #{{ $rank }} @endif
                </div>
                {{-- Student info --}}
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <img src="{{ $row['student']?->avatar }}" class="w-10 h-10 rounded-full object-cover flex-shrink-0" alt="">
                    <div class="min-w-0">
                        <p class="font-semibold text-slate-800 truncate">{{ $row['student']?->full_name }}</p>
                        <p class="text-xs text-slate-400">{{ $class?->class_name }} · {{ $semester?->semester_name }}</p>
                    </div>
                </div>
                {{-- Stats --}}
                <div class="flex gap-6 flex-shrink-0 text-center">
                    <div>
                        <p class="text-xl font-bold text-indigo-600">{{ $row['gpa'] }}</p>
                        <p class="text-xs text-slate-400">GPA</p>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-slate-700">{{ round($row['totalMarks'], 1) }}</p>
                        <p class="text-xs text-slate-400">Total</p>
                    </div>
                    <div>
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold {{ $row['failed'] === 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                            {{ $row['failed'] === 0 ? 'PASS' : 'FAIL' }}
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
