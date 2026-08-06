@extends('layouts.app')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-7xl mx-auto">

    {{-- Page Header & Breadcrumbs --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-10 h-10 rounded-2xl bg-indigo-600/10 text-indigo-600 flex items-center justify-center font-bold text-xl shadow-xs">
                    <i class="bi bi-pencil-square"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Edit Class: {{ $schoolClass->class_name }}</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Update class name, manage sections, rooms, and subject mappings</p>
                </div>
            </div>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800/80 px-3 py-1.5 rounded-xl">
                <li><a href="{{route('home')}}" class="hover:text-indigo-600 transition">Home</a></li>
                <li class="text-slate-300 dark:text-slate-600">/</li>
                <li><a href="{{url('/classes')}}" class="hover:text-indigo-600 transition">Classes</a></li>
                <li class="text-slate-300 dark:text-slate-600">/</li>
                <li class="font-semibold text-slate-800 dark:text-slate-200">Edit Class</li>
            </ol>
        </nav>
    </div>

    @include('session-messages')

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        {{-- Left Column: General Class Settings --}}
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
                <div class="flex items-center gap-2 mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <span class="w-2 h-5 bg-indigo-600 rounded-full"></span>
                    <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">General Information</h2>
                </div>

                <form action="{{route('school.class.update')}}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="session_id" value="{{$current_school_session_id}}">
                    <input type="hidden" name="class_id" value="{{$class_id}}">

                    <div>
                        <label for="class_name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Class / Grade Name <span class="text-rose-500">*</span>
                        </label>
                        <input class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none" id="class_name" name="class_name" type="text" required value="{{$schoolClass->class_name}}">
                    </div>

                    <div class="pt-2 flex items-center justify-between">
                        <a href="{{url('/classes')}}" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 text-xs font-semibold transition">
                            Back to Classes
                        </a>
                        <button type="submit" class="px-6 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md transition flex items-center gap-1.5">
                            <i class="bi bi-check2 text-sm"></i> Save Class Name
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Right Column: Sections & Subjects --}}
        <div class="lg:col-span-7 space-y-6">
            
            {{-- Class Sections Card --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-5 bg-emerald-500 rounded-full"></span>
                        <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Assigned Sections ({{ count($sections ?? []) }})</h2>
                    </div>
                </div>

                {{-- Add Section Inline Form --}}
                <form action="{{ route('school.section.create') }}" method="POST" class="mb-6 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/70 dark:border-slate-700">
                    @csrf
                    <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">
                    <input type="hidden" name="class_id" value="{{ $class_id }}">
                    
                    <p class="text-xs font-bold text-slate-700 dark:text-slate-200 mb-3"><i class="bi bi-plus-circle me-1 text-emerald-500"></i> Add New Section to {{ $schoolClass->class_name }}</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <input type="text" name="section_name" placeholder="Section Name (e.g. A, B, Red)" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500/20 outline-none">
                        </div>
                        <div>
                            <input type="text" name="room_no" placeholder="Room Number (e.g. 102)" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500/20 outline-none">
                        </div>
                    </div>
                    <div class="mt-3 text-right">
                        <button type="submit" class="px-4 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition">
                            + Add Section
                        </button>
                    </div>
                </form>

                {{-- Section List Table --}}
                @if(isset($sections) && count($sections) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-semibold">
                                <th class="py-2.5 px-3">Section Name</th>
                                <th class="py-2.5 px-3">Room Number</th>
                                <th class="py-2.5 px-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($sections as $sec)
                            <tr>
                                <td class="py-3 px-3 font-bold text-slate-800 dark:text-slate-100">
                                    Section {{ $sec->section_name }}
                                </td>
                                <td class="py-3 px-3 text-slate-500">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        <i class="bi bi-geo-alt me-1 text-slate-400"></i> Room {{ $sec->room_no }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('section.edit', ['id' => $sec->id]) }}" class="px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-100 text-[11px] font-medium transition">
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </a>
                                        <form action="{{ route('school.section.delete', ['id' => $sec->id]) }}" method="POST" onsubmit="return confirm('Delete Section {{ $sec->section_name }}?');">
                                            @csrf
                                            <button type="submit" class="p-1 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition">
                                                <i class="bi bi-trash3 text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-xs text-slate-400 py-6 text-center">No sections configured for this class yet.</p>
                @endif

            </div>

        </div>

    </div>

    @include('layouts.footer')
</div>
@endsection