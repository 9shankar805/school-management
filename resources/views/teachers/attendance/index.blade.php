@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Teacher Attendance</h1>
                <p class="text-slate-400 text-sm mt-0.5">Mark and track daily attendance</p>
            </div>
        </div>

        @if(session('status'))
        <div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>
        @endif

        {{-- Date picker --}}
        <form method="GET" action="{{ route('teacher.attendance.index') }}" class="flex gap-2 mb-6">
            <input type="date" name="date" value="{{ $date }}" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Load</button>
        </form>

        {{-- Summary --}}
        <div class="grid grid-cols-4 gap-3 mb-6">
            @foreach(['present'=>['emerald','Present'],'absent'=>['rose','Absent'],'late'=>['amber','Late'],'on_leave'=>['violet','On Leave']] as $s=>[$c,$l])
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
                <p class="text-2xl font-bold text-{{ $c }}-600">{{ $summary[$s] ?? 0 }}</p>
                <p class="text-xs text-slate-400 mt-0.5">{{ $l }}</p>
            </div>
            @endforeach
        </div>

        {{-- Bulk mark form --}}
        @can('create teachers')
        <form method="POST" action="{{ route('teacher.attendance.store') }}">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                    <p class="text-sm font-semibold text-slate-700">{{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}</p>
                    <button type="submit" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-medium transition">Save Attendance</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm"><thead><tr class="text-left text-xs text-slate-400 bg-slate-50">
                        <th class="px-5 py-2.5 font-medium">Teacher</th>
                        @foreach(\App\Models\TeacherAttendance::STATUSES as $v=>$l)
                        <th class="px-3 py-2.5 font-medium text-center">{{ $l }}</th>
                        @endforeach
                    </tr></thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($teachers as $t)
                        @php $current = $t->teacherAttendance->first()?->status ?? 'present'; @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <img src="{{ $t->avatar }}" class="w-7 h-7 rounded-full object-cover flex-shrink-0" alt="">
                                    <span class="font-medium text-slate-700">{{ $t->full_name }}</span>
                                </div>
                            </td>
                            @foreach(\App\Models\TeacherAttendance::STATUSES as $v=>$l)
                            <td class="px-3 py-3 text-center">
                                <input type="radio" name="attendance[{{ $t->id }}]" value="{{ $v }}" {{ $current === $v ? 'checked' : '' }} class="accent-indigo-600">
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody></table>
                </div>
            </div>
        </form>
        @endcan
    </div>
</div>
@endsection
