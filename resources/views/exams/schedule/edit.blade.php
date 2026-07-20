@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('exam.schedule.index') }}" class="hover:text-indigo-600">Exam Schedule</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">Edit Entry</span>
        </nav>

        <div class="mb-7">
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight"><i class="bi bi-calendar-check me-2"></i>Edit Schedule Entry</h1>
            <p class="text-slate-400 text-sm mt-0.5">
                {{ $schedule->exam->exam_name ?? '—' }}
                @if($schedule->exam?->course) · {{ $schedule->exam->course->course_name }}@endif
            </p>
        </div>

        @include('session-messages')

        <div class="max-w-2xl">
            <form method="POST" action="{{ route('exam.schedule.update', $schedule->id) }}"
                  class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
                @csrf @method('PUT')

                {{-- Exam (read-only display) --}}
                <div class="p-3 bg-slate-50 rounded-xl text-sm text-slate-600">
                    <span class="font-medium">Exam:</span>
                    {{ $schedule->exam->exam_name ?? '—' }}
                    @if($schedule->exam?->course)
                        — {{ $schedule->exam->course->course_name }}
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Exam Date <span class="text-rose-500">*</span></label>
                        <input type="date" name="exam_date" required
                               value="{{ old('exam_date', $schedule->exam_date->format('Y-m-d')) }}"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Start Time <span class="text-rose-500">*</span></label>
                            <input type="time" name="start_time" required
                                   value="{{ old('start_time', substr($schedule->start_time, 0, 5)) }}"
                                   class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">End Time <span class="text-rose-500">*</span></label>
                            <input type="time" name="end_time" required
                                   value="{{ old('end_time', substr($schedule->end_time, 0, 5)) }}"
                                   class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Hall</label>
                        <select name="hall_id" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">— None —</option>
                            @foreach($halls as $h)
                            <option value="{{ $h->id }}" {{ old('hall_id', $schedule->hall_id) == $h->id ? 'selected' : '' }}>
                                {{ $h->hall_name }} (cap. {{ $h->capacity }})
                            </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-amber-600 mt-1"><i class="bi bi-exclamation-triangle me-1"></i>Conflict detection runs on save.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Invigilator</label>
                        <select name="invigilator_id" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">— None —</option>
                            @foreach($invigilators as $inv)
                            <option value="{{ $inv->id }}" {{ old('invigilator_id', $schedule->invigilator_id) == $inv->id ? 'selected' : '' }}>
                                {{ $inv->full_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Notes</label>
                    <textarea name="notes" rows="2"
                              class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                              placeholder="Optional notes…">{{ old('notes', $schedule->notes) }}</textarea>
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">
                        <i class="bi bi-check2 me-1"></i> Save Changes
                    </button>
                    <a href="{{ route('exam.schedule.index') }}" class="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-medium transition">Cancel</a>
                    <form action="{{ route('exam.schedule.destroy', $schedule->id) }}" method="POST"
                          class="ml-auto" onsubmit="return confirm('Delete this schedule entry?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-xl text-sm font-medium transition">
                            <i class="bi bi-trash me-1"></i> Delete
                        </button>
                    </form>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
