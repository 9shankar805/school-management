@extends('layouts.app')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-5xl mx-auto">

    {{-- Header & Breadcrumbs --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-10 h-10 rounded-2xl bg-indigo-600/10 text-indigo-600 flex items-center justify-center font-bold text-xl shadow-xs">
                    <i class="bi bi-plus-circle"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Create New Class / Grade</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Add a new academic class to the current school session</p>
                </div>
            </div>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800/80 px-3 py-1.5 rounded-xl">
                <li><a href="{{route('home')}}" class="hover:text-indigo-600 transition">Home</a></li>
                <li class="text-slate-300 dark:text-slate-600">/</li>
                <li><a href="{{url('/classes')}}" class="hover:text-indigo-600 transition">Classes</a></li>
                <li class="text-slate-300 dark:text-slate-600">/</li>
                <li class="font-semibold text-slate-800 dark:text-slate-200">Create Class</li>
            </ol>
        </nav>
    </div>

    @include('session-messages')

    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6 md:p-8">
        <form action="{{ route('school.class.create') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">

            <div>
                <label for="class_name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">
                    Class / Grade Name <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="class_name" name="class_name" required placeholder="e.g. Grade 10 or ECD"
                       class="w-full text-sm px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition">
                <p class="mt-1.5 text-xs text-slate-400">Enter a descriptive name for this class or grade level.</p>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <a href="{{ url('/classes') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 text-xs font-semibold transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md transition flex items-center gap-2">
                    <i class="bi bi-check2-circle text-base"></i> Save New Class
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
