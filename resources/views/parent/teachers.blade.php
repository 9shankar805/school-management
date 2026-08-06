@extends('layouts.app')

@section('content')

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        @include('parent.partials.child-selector')
        @include('parent.partials.page-header', ['title' => 'Teachers Directory'])

        @include('session-messages')

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($teachers as $teacher)
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 flex flex-col items-center text-center hover:shadow-md transition-shadow">
                    @if($teacher->photo)
                        <img src="{{ Storage::url($teacher->photo) }}" class="w-20 h-20 rounded-full object-cover mb-4 border-2 border-indigo-100" alt="{{ $teacher->full_name }}">
                    @else
                        <div class="w-20 h-20 rounded-full bg-indigo-50 flex items-center justify-center mb-4 border-2 border-indigo-100 text-indigo-500 text-2xl font-bold">
                            {{ substr($teacher->first_name, 0, 1) }}{{ substr($teacher->last_name, 0, 1) }}
                        </div>
                    @endif
                    
                    <h3 class="font-bold text-slate-800 text-lg">{{ $teacher->full_name }}</h3>
                    <p class="text-sm text-slate-500 mb-4">{{ $teacher->email }}</p>
                    
                    <div class="mt-auto w-full">
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2">Subjects Taught</div>
                        <div class="flex flex-wrap justify-center gap-2">
                            @forelse($teacher->subjects_taught as $subject)
                                <span class="bg-indigo-50 text-indigo-700 text-xs px-2.5 py-1 rounded-md">{{ $subject }}</span>
                            @empty
                                <span class="text-slate-400 text-xs italic">Class Teacher</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-xl border border-slate-100 shadow-sm p-12 text-center">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-person-badge text-2xl text-slate-400"></i>
                    </div>
                    <h3 class="font-bold text-slate-700 mb-1">No Teachers Found</h3>
                    <p class="text-slate-500 text-sm">We couldn't find any assigned teachers for the current academic session.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
