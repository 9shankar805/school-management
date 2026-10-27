@extends('layouts.app')
@section('content')
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('routine.index') }}" class="hover:text-indigo-600">Timetable</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">Edit Slot</span>
        </nav>

        <h1 class="text-2xl font-bold text-slate-800 mb-6">Edit Timetable Slot</h1>

        @include('session-messages')

        {{-- Conflict alert --}}
        @if($errors->has('conflict'))
        <div class="mb-5 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-sm flex items-start gap-2">
            <i class="bi bi-exclamation-triangle-fill mt-0.5"></i>
            <div>
                <p class="font-semibold">Scheduling Conflict Detected</p>
                <p>{{ $errors->first('conflict') }}</p>
            </div>
        </div>
        @endif

        <div class="max-w-2xl bg-white rounded-2xl border border-slate-100 shadow-sm p-6">

            {{-- Read-only context info --}}
            <div class="mb-5 p-4 bg-slate-50 rounded-xl border border-slate-100 text-sm text-slate-600 grid grid-cols-2 gap-3">
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Class</p>
                    <p class="font-medium">{{ optional($routine->schoolClass)->class_name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Section</p>
                    <p class="font-medium">{{ optional($routine->section)->section_name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Course</p>
                    <p class="font-medium">{{ optional($routine->course)->course_name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Teacher</p>
                    <p class="font-medium">{{ optional($routine->teacher)->full_name ?? '— None —' }}</p>
                </div>
            </div>

            <form action="{{ route('routine.update', $routine->id) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                {{-- Subject / Course selection --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300 block mb-1">
                            Subject / Course <span class="text-rose-500">*</span>
                        </label>
                        <select name="course_id" required
                                class="w-full border border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            @foreach($courses as $c)
                            <option value="{{ $c->id }}" {{ old('course_id', $routine->course_id) == $c->id ? 'selected' : '' }}>
                                {{ $c->course_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300 block mb-1">
                            Assigned Teacher
                        </label>
                        <select name="teacher_id"
                                class="w-full border border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">— No Teacher Assigned —</option>
                            @foreach($teachers as $t)
                            <option value="{{ $t->id }}" {{ old('teacher_id', $routine->teacher_id) == $t->id ? 'selected' : '' }}>
                                {{ $t->full_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Day of week --}}
                <div>
                    <label class="text-xs font-semibold text-slate-700 dark:text-slate-300 block mb-1">
                        Day of Week <span class="text-rose-500">*</span>
                    </label>
                    <select name="weekday" required
                            class="w-full border border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach([1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday'] as $num => $name)
                        <option value="{{ $num }}" {{ old('weekday', $routine->weekday) == $num ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Time range --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">
                            Start Time <span class="text-rose-400">*</span>
                        </label>
                        <input type="time" name="start" required
                               value="{{ old('start', $routine->start) }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">
                            End Time <span class="text-rose-400">*</span>
                        </label>
                        <input type="time" name="end" required
                               value="{{ old('end', $routine->end) }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                </div>

                {{-- Room --}}
                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Room / Venue</label>
                    <input type="text" name="room" placeholder="e.g. Room 101"
                           value="{{ old('room', $routine->room) }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>

                {{-- Color --}}
                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Slot Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="color"
                               value="{{ old('color', $routine->color ?? '#6366f1') }}"
                               class="h-9 w-14 rounded-lg border border-slate-200 cursor-pointer p-1">
                        <span class="text-xs text-slate-400">Timetable display color</span>
                    </div>
                </div>

                {{-- Conflict info banner --}}
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-700 flex items-start gap-2">
                    <i class="bi bi-shield-check mt-0.5"></i>
                    <p>Conflict detection is active. The system will block the update if this section already has an overlapping slot on the selected day.</p>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">
                        <i class="bi bi-check2 me-1"></i> Update Slot
                    </button>
                    <a href="{{ route('routine.index') }}"
                       class="px-6 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-medium hover:bg-slate-50 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
