@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight"><i class="bi bi-calendar3 me-2"></i>Exam Timetable</h1>
                <p class="text-slate-400 text-sm mt-0.5">All scheduled exam sittings for this session</p>
            </div>
            @can('create exams')
            <a href="{{ route('exam.schedule.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition flex items-center gap-1.5">
                <i class="bi bi-plus-lg"></i> Add Schedule Entry
            </a>
            @endcan
        </div>

        @if(session('status'))
        <div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>
        @endif

        {{-- Filters --}}
        <form method="GET" action="{{ route('exam.schedule.index') }}" class="flex flex-wrap gap-3 mb-6">
            <select name="class_id" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="0">All Classes</option>
                @foreach($classes as $cls)
                <option value="{{ $cls->id }}" {{ $classId == $cls->id ? 'selected' : '' }}>{{ $cls->class_name }}</option>
                @endforeach
            </select>
            <select name="semester_id" class="border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="0">All Semesters</option>
                @foreach($semesters as $sem)
                <option value="{{ $sem->id }}" {{ $semesterId == $sem->id ? 'selected' : '' }}>{{ $sem->semester_name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-slate-700 hover:bg-slate-800 text-white rounded-xl text-sm font-medium transition">Filter</button>
        </form>

        @if($schedules->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center text-slate-400">
            <i class="bi bi-calendar3 text-5xl mb-3 block"></i>
            <p class="text-sm">No exam schedule entries found. <a href="{{ route('exam.schedule.create') }}" class="text-indigo-500 hover:underline">Add one now.</a></p>
        </div>
        @else
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs text-slate-400 bg-slate-50">
                        <th class="px-5 py-3 font-medium">Date</th>
                        <th class="px-4 py-3 font-medium">Exam</th>
                        <th class="px-4 py-3 font-medium">Course</th>
                        <th class="px-4 py-3 font-medium">Class</th>
                        <th class="px-4 py-3 font-medium">Time</th>
                        <th class="px-4 py-3 font-medium">Hall</th>
                        <th class="px-4 py-3 font-medium">Invigilator</th>
                        <th class="px-4 py-3 font-medium text-center">Seats</th>
                        <th class="px-4 py-3 font-medium">Actions</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($schedules as $s)
                        @php $today = $s->exam_date->isToday(); $past = $s->exam_date->isPast() && !$today; @endphp
                        <tr class="hover:bg-slate-50 {{ $past ? 'opacity-60' : '' }}">
                            <td class="px-5 py-3">
                                <p class="font-semibold text-slate-700 {{ $today ? 'text-indigo-600' : '' }}">{{ $s->exam_date->format('d M Y') }}</p>
                                <p class="text-xs text-slate-400">{{ $s->exam_date->format('l') }}</p>
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-700">{{ $s->exam?->exam_name }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $s->exam?->course?->course_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $s->exam?->schoolClass?->class_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-500">
                                {{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }} –
                                {{ \Carbon\Carbon::parse($s->end_time)->format('H:i') }}
                                <span class="text-xs text-slate-400">({{ $s->duration_label }})</span>
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $s->hall?->hall_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $s->invigilator?->full_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('exam.hall.seats', $s->id) }}" class="text-xs text-indigo-500 hover:underline">{{ $s->seat_status }}</a>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    @can('create exams')
                                    <a href="{{ route('exam.schedule.edit', $s->id) }}" class="text-xs px-2 py-1 rounded-lg bg-slate-50 hover:bg-indigo-50 text-slate-600 hover:text-indigo-600 transition">Edit</a>
                                    <form method="POST" action="{{ route('exam.schedule.destroy', $s->id) }}" onsubmit="return confirm('Delete this schedule entry?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs px-2 py-1 rounded-lg bg-slate-50 hover:bg-rose-50 text-slate-600 hover:text-rose-600 transition">Delete</button>
                                    </form>
                                    @endcan
                                </div>
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
