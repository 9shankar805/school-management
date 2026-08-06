@extends('layouts.app')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-4xl mx-auto">

    {{-- Page Header & Breadcrumbs --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-10 h-10 rounded-2xl bg-emerald-600/10 text-emerald-600 flex items-center justify-center font-bold text-xl shadow-xs">
                    <i class="bi bi-pencil-square"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Edit Section {{ $section->section_name }}</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Update section name and assigned room number</p>
                </div>
            </div>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800/80 px-3 py-1.5 rounded-xl">
                <li><a href="{{route('home')}}" class="hover:text-indigo-600 transition">Home</a></li>
                <li class="text-slate-300 dark:text-slate-600">/</li>
                <li><a href="{{url('/classes')}}" class="hover:text-indigo-600 transition">Classes</a></li>
                <li class="text-slate-300 dark:text-slate-600">/</li>
                <li class="font-semibold text-slate-800 dark:text-slate-200">Edit Section</li>
            </ol>
        </nav>
    </div>

    @include('session-messages')

    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
        <form action="{{route('school.section.update')}}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="session_id" value="{{$current_school_session_id}}">
            <input type="hidden" name="section_id" value="{{$section_id}}">

            <div>
                <label for="section_name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Section Name <span class="text-rose-500">*</span>
                </label>
                <input class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500/20 outline-none" id="section_name" name="section_name" type="text" required value="{{$section->section_name}}">
            </div>

            <div>
                <label for="room_no" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Room Number <span class="text-rose-500">*</span>
                </label>
                <input class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500/20 outline-none" id="room_no" name="room_no" type="text" required value="{{$section->room_no}}">
            </div>

            <div class="pt-4 flex items-center justify-between">
                <a href="{{url('/classes')}}" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-md transition flex items-center gap-1.5">
                    <i class="bi bi-check2 text-sm"></i> Save Section
                </button>
            </div>
        </form>
    </div>

    @include('layouts.footer')
</div>
@endsection