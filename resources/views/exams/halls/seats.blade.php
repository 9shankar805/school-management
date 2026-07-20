@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex items-center gap-3 mb-7">
            <a href="{{ route('exam.schedule.index') }}" class="text-slate-400 hover:text-slate-600 transition"><i class="bi bi-arrow-left text-lg"></i></a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Seat Allocation</h1>
                <p class="text-slate-400 text-sm mt-0.5">
                    {{ $schedule->exam?->exam_name }} · {{ $schedule->exam_date->format('d M Y') }}
                    · {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}
                    @if($schedule->hall) · {{ $schedule->hall->hall_name }} (cap. {{ $schedule->hall->capacity }}) @endif
                </p>
            </div>
        </div>

        @if(session('status'))
        <div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>
        @endif
        @include('session-messages')

        @can('create exams')
        <div class="flex gap-3 mb-6">
            {{-- Auto-allocate --}}
            <form method="POST" action="{{ route('exam.hall.seats.auto', $schedule->id) }}">
                @csrf
                <input type="hidden" name="prefix" value="A">
                <button type="submit" onclick="return confirm('Auto-allocate seats? This will overwrite existing manual assignments.')"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition flex items-center gap-1.5">
                    <i class="bi bi-magic"></i> Auto-Allocate
                </button>
            </form>
            {{-- Clear all --}}
            <form method="POST" action="{{ route('exam.hall.seats.clear', $schedule->id) }}">
                @csrf @method('DELETE')
                <button type="submit" onclick="return confirm('Clear all seat allocations?')"
                        class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl text-sm font-medium transition flex items-center gap-1.5">
                    <i class="bi bi-trash"></i> Clear All
                </button>
            </form>
        </div>
        @endcan

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Current Allocations --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                    <p class="text-sm font-semibold text-slate-700">Allocated Seats</p>
                    <span class="text-xs bg-indigo-50 text-indigo-600 font-semibold px-2.5 py-0.5 rounded-full">{{ $allocations->count() }}</span>
                </div>
                @if($allocations->isEmpty())
                <div class="p-6 text-center text-sm text-slate-400">No seats allocated yet.</div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead><tr class="text-xs text-slate-400 bg-slate-50 text-left">
                            <th class="px-4 py-2.5 font-medium">Seat #</th>
                            <th class="px-4 py-2.5 font-medium">Student</th>
                        </tr></thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($allocations as $a)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-2.5">
                                    <span class="font-mono font-semibold text-indigo-600">{{ $a->seat_number }}</span>
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center gap-2">
                                        <img src="{{ $a->student?->avatar }}" class="w-6 h-6 rounded-full object-cover" alt="">
                                        <span class="text-slate-700">{{ $a->student?->full_name ?? '—' }}</span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            {{-- Manual allocation form --}}
            @can('create exams')
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-slate-700 mb-4">Manual Seat Assignment</p>
                <form method="POST" action="{{ route('exam.hall.seats.save', $schedule->id) }}" class="space-y-3">
                    @csrf
                    @foreach($students as $i => $promo)
                    <div class="flex items-center gap-3">
                        <input type="hidden" name="seats[{{ $i }}][student_id]" value="{{ $promo->student_id }}">
                        <span class="text-sm text-slate-700 flex-1">{{ $promo->student?->full_name }}</span>
                        <input type="text" name="seats[{{ $i }}][seat_number]" placeholder="e.g. A-01"
                               class="w-24 border border-slate-200 rounded-lg px-2 py-1.5 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-indigo-400">
                    </div>
                    @endforeach
                    @if($students->isEmpty())
                    <p class="text-sm text-slate-400">No students found for this class.</p>
                    @else
                    <button type="submit" class="mt-3 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">Save Assignments</button>
                    @endif
                </form>
            </div>
            @endcan
        </div>
    </div>
</div>
@endsection
