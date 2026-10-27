@extends('layouts.app')

@section('content')
<div class="flex-1 p-4 lg:p-7 overflow-auto bg-slate-50/50 min-h-screen">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight leading-none">Attendance Management</h1>
            <p class="text-xs text-slate-400 mt-1">Take student attendance, inspect records, and review analytics</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('attendance.create.show') }}" 
               class="px-5 py-2.5 bg-[#1E1B4B] hover:bg-[#312E81] text-white rounded-xl text-xs font-semibold transition shadow-sm flex items-center gap-2">
                <i class="bi bi-calendar2-check"></i> Take Attendance
            </a>
        </div>
    </div>

    @include('session-messages')

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach ($classes_and_sections['school_classes'] as $school_class)
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 font-bold flex items-center justify-center text-sm">
                        <i class="bi bi-mortarboard"></i>
                    </span>
                    <h2 class="text-base font-bold text-slate-800">{{ $school_class->class_name }}</h2>
                </div>
            </div>

            <div class="space-y-2 mt-4 pt-3 border-t border-slate-100">
                @if ($academic_setting->attendance_type == 'course')
                    @foreach ($courses as $course)
                        @if ($course->class_id == $school_class->id)
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-xs font-semibold text-slate-700">{{ $course->course_name }}</span>
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('attendance.create.show', ['class_id' => $school_class->id, 'course_id' => $course->id]) }}" 
                                   class="px-3 py-1 bg-indigo-600 text-white rounded-lg text-[11px] font-semibold hover:bg-indigo-700 transition">
                                    Take
                                </a>
                            </div>
                        </div>
                        @endif
                    @endforeach
                @else
                    @foreach ($classes_and_sections['school_sections'] as $school_section)
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-xs font-semibold text-slate-700">Section {{ $school_section->section_name }}</span>
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('attendance.create.show', ['class_id' => $school_class->id, 'section_id' => $school_section->id]) }}" 
                               class="px-3 py-1 bg-[#1E1B4B] text-white rounded-lg text-[11px] font-semibold hover:bg-[#312E81] transition">
                                Take
                            </a>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
        @endforeach
    </div>

</div>
@endsection
