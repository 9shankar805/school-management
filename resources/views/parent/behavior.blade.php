@extends('layouts.app')

@section('content')

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        @include('parent.partials.child-selector')
        @include('parent.partials.page-header', ['title' => 'Behavior & Conduct'])

        @include('session-messages')

        <div class="space-y-6">
            
            {{-- Active Incidents --}}
            <div class="bg-white rounded-xl border border-rose-200 shadow-sm overflow-hidden">
                <div class="bg-rose-50 border-b border-rose-100 px-6 py-4 flex items-center justify-between">
                    <h3 class="font-bold text-rose-900 flex items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill"></i> Active Incidents
                    </h3>
                    <span class="bg-rose-100 text-rose-700 text-xs font-bold px-2.5 py-1 rounded-full">{{ count($active) }}</span>
                </div>
                <div class="p-0">
                    @forelse($active as $incident)
                        <div class="p-6 border-b border-slate-100 last:border-0 flex flex-col sm:flex-row gap-4 justify-between items-start">
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="px-2.5 py-1 rounded-md text-xs font-bold uppercase {{ $incident->severity_badge }}">
                                        {{ $incident->severity }}
                                    </span>
                                    <h4 class="font-bold text-slate-800">{{ $incident->incident_type }}</h4>
                                </div>
                                <p class="text-sm text-slate-600 mb-2">{{ $incident->description }}</p>
                                <p class="text-xs text-slate-400">
                                    <i class="bi bi-clock"></i> {{ \Carbon\Carbon::parse($incident->incident_date)->format('M d, Y') }}
                                    <span class="mx-2">•</span>
                                    <i class="bi bi-person"></i> Reported by {{ $incident->reporter->full_name ?? 'Staff' }}
                                </p>
                            </div>
                            @if($incident->action_taken)
                            <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 w-full sm:w-1/3 shrink-0">
                                <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Action Taken</p>
                                <p class="text-sm text-slate-700">{{ $incident->action_taken }}</p>
                            </div>
                            @endif
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-500">
                            <i class="bi bi-check-circle text-emerald-500 text-2xl block mb-2"></i>
                            No active behavioral incidents.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- History --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i class="bi bi-clock-history"></i> Incident History
                    </h3>
                </div>
                <div class="p-0">
                    @forelse($history as $incident)
                        <div class="p-6 border-b border-slate-100 last:border-0 hover:bg-slate-50 transition-colors">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h4 class="font-bold text-slate-700">{{ $incident->incident_type }}</h4>
                                    <p class="text-xs text-slate-400 mt-1">
                                        {{ \Carbon\Carbon::parse($incident->incident_date)->format('M d, Y') }}
                                    </p>
                                </div>
                                <span class="bg-slate-200 text-slate-600 text-xs font-bold px-2 py-1 rounded uppercase">Resolved</span>
                            </div>
                            <p class="text-sm text-slate-500">{{ Str::limit($incident->description, 100) }}</p>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-500">
                            No past incidents recorded.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
@endsection
