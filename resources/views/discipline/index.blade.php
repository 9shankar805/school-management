@extends('layouts.app')
@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">

    {{-- Header --}}
    <div class="flex flex-wrap justify-between items-start mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Conduct / Discipline Log</h1>
            <p class="text-slate-400 text-sm mt-0.5">Student incident records for your classes</p>
        </div>
    </div>

    @include('session-messages')

    {{-- Filter --}}
    <form method="GET" action="{{ route('student.discipline.index') }}"
          class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[180px]">
            <label class="text-xs font-medium text-slate-600 block mb-1">Filter by Class</label>
            <select name="class_id" onchange="this.form.submit()"
                    class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">All my classes</option>
                @foreach($classes as $cls)
                <option value="{{ $cls->id }}" {{ $classId == $cls->id ? 'selected' : '' }}>{{ $cls->class_name }}</option>
                @endforeach
            </select>
        </div>
        @if($classId)
        <a href="{{ route('student.discipline.index') }}"
           class="px-4 py-2 bg-white border border-slate-200 text-slate-600 text-sm rounded-lg hover:bg-slate-50 transition">
            Clear filter
        </a>
        @endif
    </form>

    {{-- Stats row --}}
    @php
        $open  = $records->getCollection()->where('resolved', false)->count();
        $minor = $records->getCollection()->where('severity', 'minor')->count();
        $mod   = $records->getCollection()->where('severity', 'moderate')->count();
        $major = $records->getCollection()->where('severity', 'major')->count();
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-slate-800">{{ $records->total() }}</p>
            <p class="text-xs text-slate-400 mt-1">Total Records</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-rose-600">{{ $open }}</p>
            <p class="text-xs text-slate-400 mt-1">Open (unresolved)</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-orange-500">{{ $mod }}</p>
            <p class="text-xs text-slate-400 mt-1">Moderate</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-rose-700">{{ $major }}</p>
            <p class="text-xs text-slate-400 mt-1">Major</p>
        </div>
    </div>

    {{-- Records table --}}
    @if($records->isEmpty())
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-14 text-center">
        <i class="bi bi-shield-check text-5xl text-slate-200 block mb-3"></i>
        <p class="text-slate-500 font-medium">No disciplinary records found.</p>
        <p class="text-slate-400 text-sm mt-1">Records are added via a student's profile page.</p>
    </div>
    @else
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Student</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase hidden sm:table-cell">Date</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Type</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase hidden md:table-cell">Severity</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase hidden lg:table-cell">Description</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Status</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($records as $record)
                <tr class="hover:bg-slate-50 transition {{ $record->resolved ? 'opacity-60' : '' }}">
                    <td class="px-5 py-3">
                        <a href="{{ route('student.profile.show', $record->student_id) }}"
                           class="flex items-center gap-2 group">
                            <img src="{{ $record->student->avatar }}" class="w-7 h-7 rounded-full object-cover flex-shrink-0" alt="">
                            <span class="font-medium text-slate-700 group-hover:text-indigo-600 transition">
                                {{ $record->student->full_name }}
                            </span>
                        </a>
                    </td>
                    <td class="px-5 py-3 text-slate-500 text-xs hidden sm:table-cell">
                        {{ $record->incident_date->format('M d, Y') }}
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $record->incident_type }}</td>
                    <td class="px-5 py-3 hidden md:table-cell">
                        <span class="text-[11px] px-2 py-0.5 rounded-full font-medium {{ $record->severity_badge }}">
                            {{ ucfirst($record->severity) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-slate-500 text-xs max-w-xs hidden lg:table-cell">
                        <p class="line-clamp-2">{{ $record->description }}</p>
                    </td>
                    <td class="px-5 py-3">
                        @if($record->resolved)
                        <span class="text-[11px] px-2 py-0.5 rounded-full font-medium bg-emerald-100 text-emerald-700">Resolved</span>
                        @else
                        <span class="text-[11px] px-2 py-0.5 rounded-full font-medium bg-rose-100 text-rose-700">Open</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-1 justify-end">
                            {{-- Resolve toggle --}}
                            @can('create students')
                            <form action="{{ route('student.discipline.resolve', $record->id) }}" method="POST">
                                @csrf
                                <button title="{{ $record->resolved ? 'Mark unresolved' : 'Mark resolved' }}"
                                        class="p-1.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition text-sm">
                                    <i class="bi bi-{{ $record->resolved ? 'arrow-counterclockwise' : 'check-circle' }}"></i>
                                </button>
                            </form>
                            {{-- Delete --}}
                            <form action="{{ route('student.discipline.destroy', $record->id) }}" method="POST"
                                  onsubmit="return confirm('Delete this record permanently?')">
                                @csrf @method('DELETE')
                                <button title="Delete"
                                        class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition text-sm">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-5 py-4 border-t border-slate-100">
            {{ $records->links() }}
        </div>
    </div>
    @endif

</div>
</div>
@endsection
