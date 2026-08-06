@extends('layouts.app')

@section('content')

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        @include('parent.partials.child-selector')
        @include('parent.partials.page-header', ['title' => 'School Events'])

        @include('session-messages')

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($events as $event)
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden hover:shadow-md transition-shadow flex flex-col">
                    <div class="h-32 {{ $event->color ?? 'bg-indigo-500' }} relative">
                        <div class="absolute inset-0 bg-black/20"></div>
                        <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-lg text-center shadow-sm">
                            <p class="text-xs font-bold text-slate-500 uppercase">{{ \Carbon\Carbon::parse($event->start_date)->format('M') }}</p>
                            <p class="text-xl font-black text-slate-800 leading-tight">{{ \Carbon\Carbon::parse($event->start_date)->format('d') }}</p>
                        </div>
                    </div>
                    
                    <div class="p-6 flex-1 flex flex-col">
                        <h3 class="font-bold text-slate-800 text-lg mb-2">{{ $event->title }}</h3>
                        <div class="flex items-center gap-2 text-xs text-slate-500 mb-4">
                            <i class="bi bi-clock"></i>
                            <span>{{ \Carbon\Carbon::parse($event->start_date)->format('g:i A') }} - {{ \Carbon\Carbon::parse($event->end_date)->format('g:i A') }}</span>
                        </div>
                        
                        <p class="text-sm text-slate-600 mb-6 flex-1">{{ Str::limit($event->description, 120) }}</p>
                        
                        <div class="mt-auto pt-4 border-t border-slate-100 flex justify-between items-center">
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-md bg-slate-100 text-slate-600">
                                {{ $event->type ?? 'General' }}
                            </span>
                            <button class="text-indigo-600 hover:text-indigo-800 font-semibold text-sm transition-colors" onclick="alert('RSVP feature is in development.')">
                                RSVP Now <i class="bi bi-arrow-right ml-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-xl border border-slate-100 shadow-sm p-12 text-center">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-calendar-x text-2xl text-slate-400"></i>
                    </div>
                    <h3 class="font-bold text-slate-700 mb-1">No Upcoming Events</h3>
                    <p class="text-slate-500 text-sm">There are no school events scheduled for the near future.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
