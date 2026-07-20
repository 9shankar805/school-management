@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="mb-6">
            <a href="{{ route('parent.conversations') }}" class="text-xs text-indigo-600 hover:underline">
                <i class="bi bi-arrow-left me-1"></i>Back to Messages
            </a>
            <h1 class="text-xl font-bold text-slate-800 mt-2">New Conversation</h1>
        </div>

        @include('session-messages')

        <div class="max-w-xl">
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5">
                <form method="POST" action="{{ route('parent.conversation.store') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Child <span class="text-rose-500">*</span></label>
                            <select name="student_id"
                                    class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300 @error('student_id') border-rose-400 @enderror">
                                <option value="">— Select child —</option>
                                @foreach($children as $child)
                                <option value="{{ $child->id }}" {{ old('student_id') == $child->id ? 'selected' : '' }}>
                                    {{ $child->full_name }}
                                </option>
                                @endforeach
                            </select>
                            @error('student_id')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Teacher <span class="text-rose-500">*</span></label>
                            <select name="teacher_id"
                                    class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300 @error('teacher_id') border-rose-400 @enderror">
                                <option value="">— Select teacher —</option>
                                @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->full_name }}
                                </option>
                                @endforeach
                            </select>
                            @error('teacher_id')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Subject <span class="text-rose-500">*</span></label>
                            <input type="text" name="subject" value="{{ old('subject') }}"
                                   placeholder="e.g. Question about homework…"
                                   maxlength="150"
                                   class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300 @error('subject') border-rose-400 @enderror">
                            @error('subject')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Message <span class="text-rose-500">*</span></label>
                            <textarea name="body" rows="5"
                                      placeholder="Write your message here…"
                                      maxlength="2000"
                                      class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300 @error('body') border-rose-400 @enderror">{{ old('body') }}</textarea>
                            @error('body')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex gap-3 mt-5">
                        <button type="submit"
                                class="flex-1 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition">
                            <i class="bi bi-send me-1"></i>Send Message
                        </button>
                        <a href="{{ route('parent.conversations') }}"
                           class="px-4 py-2.5 bg-slate-100 text-slate-600 text-sm rounded-lg hover:bg-slate-200 transition">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
