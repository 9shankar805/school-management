@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight"><i class="bi bi-exclamation-triangle me-2 text-rose-500"></i>Attendance Shortage</h1>
                <p class="text-slate-400 text-sm mt-0.5">Students below the minimum attendance threshold</p>
            </div>
            <form method="GET" action="{{ route('attendance.shortage') }}" class="flex gap-2 items-end">
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Threshold (%)</label>
                    <input type="number" name="threshold" value="{{ $threshold }}" min="1" max="100"
                           class="border border-slate-200 rounded-lg px-3 py-1.5 text-sm w-24 focus:outline-none focus:ring-2 focus:ring-rose-400">
                </div>
                <button type="submit" class="px-4 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-sm font-medium transition">Filter</button>
            </form>
        </div>

        @if(session('status'))
        <div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>
        @endif

        {{-- Summary badge --}}
        <div class="mb-5">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium">
                <i class="bi bi-exclamation-triangle-fill"></i>
                {{ count($students) }} student(s) below {{ $threshold }}% attendance
            </span>
        </div>

        @if(empty($students))
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center text-slate-400">
            <i class="bi bi-check2-all text-5xl mb-3 block text-emerald-400"></i>
            <p class="text-sm">All students meet the {{ $threshold }}% attendance requirement.</p>
        </div>
        @else
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs text-slate-400 bg-slate-50">
                        <th class="px-5 py-3 font-medium">#</th>
                        <th class="px-4 py-3 font-medium">Student</th>
                        <th class="px-4 py-3 font-medium">Class</th>
                        <th class="px-4 py-3 font-medium text-center">Present</th>
                        <th class="px-4 py-3 font-medium text-center">Total</th>
                        <th class="px-4 py-3 font-medium text-center">Attendance %</th>
                        <th class="px-4 py-3 font-medium text-center">Action</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($students as $i => $s)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 text-slate-400">{{ $i + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <img src="{{ $s['student']?->avatar }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0" alt="">
                                    <div>
                                        <p class="font-medium text-slate-700">{{ $s['student']?->full_name ?? '—' }}</p>
                                        <p class="text-xs text-slate-400">{{ $s['student']?->email ?? '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $s['schoolClass']?->class_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-emerald-600">{{ $s['present'] }}</td>
                            <td class="px-4 py-3 text-center text-slate-500">{{ $s['total'] }}</td>
                            <td class="px-4 py-3 text-center">
                                @php $pct = $s['percentage']; @endphp
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold
                                    {{ $pct < 50 ? 'bg-rose-100 text-rose-700' : ($pct < 75 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                    {{ $pct }}%
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($s['student'])
                                <a href="{{ route('student.attendance.show', $s['student']->id) }}"
                                   class="text-xs text-indigo-500 hover:text-indigo-700 font-medium">
                                    <i class="bi bi-eye me-0.5"></i> View
                                </a>
                                @endif
                            </td>
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
