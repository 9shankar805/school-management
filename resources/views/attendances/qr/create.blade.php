@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="mb-7">
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight"><i class="bi bi-qr-code me-2"></i>Generate QR Token</h1>
            <p class="text-slate-400 text-sm mt-0.5">Students scan this code to mark themselves present.</p>
        </div>

        @include('session-messages')

        <div class="max-w-lg">
            <form method="POST" action="{{ route('attendance.qr.store') }}" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
                @csrf

                {{-- Class --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Class <span class="text-rose-500">*</span></label>
                    <select name="class_id" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" id="classSelect">
                        <option value="">— Select class —</option>
                        @foreach($classesAndSections as $class)
                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->class_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Section --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Section <span class="text-slate-400 text-xs">(optional)</span></label>
                    <select name="section_id" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">— All sections —</option>
                        @foreach($classesAndSections as $class)
                            @foreach($class->sections as $section)
                                <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>{{ $class->class_name }} — {{ $section->section_name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>

                {{-- Course --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Course / Subject <span class="text-slate-400 text-xs">(optional)</span></label>
                    <select name="course_id" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">— All courses —</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>{{ $course->course_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- School start time --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">School Start Time <span class="text-rose-500">*</span></label>
                        <input type="time" name="school_start" value="{{ old('school_start', '08:00') }}" required
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <p class="text-xs text-slate-400 mt-1">Used to calculate late arrivals.</p>
                    </div>

                    {{-- Valid minutes --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">QR Valid For (minutes) <span class="text-rose-500">*</span></label>
                        <input type="number" name="valid_minutes" value="{{ old('valid_minutes', 30) }}" min="0" max="1440" required
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <p class="text-xs text-slate-400 mt-1">0 = unlimited (revoke manually).</p>
                    </div>
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">
                        <i class="bi bi-qr-code me-1"></i> Generate QR Code
                    </button>
                    <a href="{{ route('attendance.qr.index') }}" class="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-medium transition">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
