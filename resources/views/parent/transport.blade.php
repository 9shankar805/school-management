@extends('layouts.app')
@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">

    <nav class="text-xs text-slate-400 mb-4">
        <a href="{{ url('home') }}" class="hover:text-indigo-600">Dashboard</a>
        <span class="mx-1">/</span>
        <span class="text-slate-600">{{ $child->full_name }} — Transport</span>
    </nav>

    <div class="flex flex-wrap justify-between items-start mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Transport Info</h1>
            <p class="text-slate-400 text-sm mt-0.5">Bus route and stop details for {{ $child->first_name }}</p>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <img src="{{ $child->avatar }}" class="w-8 h-8 rounded-full object-cover border border-indigo-100" alt="">
            <span class="text-sm font-medium text-slate-700">{{ $child->full_name }}</span>
        </div>
    </div>

    @include('session-messages')

    @if(!$transport)
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-14 text-center">
        <i class="bi bi-bus-front text-5xl text-slate-200 block mb-3"></i>
        <p class="text-slate-500 font-medium">No transport assigned for {{ $child->first_name }}.</p>
        <p class="text-slate-400 text-sm mt-1">Contact school administration to arrange transport.</p>
    </div>
    @else

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Route & vehicle card --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
                <p class="text-sm font-semibold text-slate-700"><i class="bi bi-map-fill me-1 text-indigo-500"></i>Route Details</p>
            </div>
            <div class="px-5 py-4 space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-400 font-medium">Route</span>
                    <span class="text-slate-700 font-semibold">{{ $transport->route->name ?? '—' }}</span>
                </div>
                @if($transport->route->code ?? null)
                <div class="flex justify-between text-sm">
                    <span class="text-slate-400 font-medium">Route Code</span>
                    <span class="text-slate-700">{{ $transport->route->code }}</span>
                </div>
                @endif
                <div class="flex justify-between text-sm">
                    <span class="text-slate-400 font-medium">Assigned Stop</span>
                    <span class="text-slate-700 font-semibold text-indigo-600">{{ $transport->stop->name ?? '—' }}</span>
                </div>
                @if($transport->stop->morning_pickup ?? null)
                <div class="flex justify-between text-sm">
                    <span class="text-slate-400 font-medium">Morning Pickup</span>
                    <span class="text-slate-700">{{ \Carbon\Carbon::parse($transport->stop->morning_pickup)->format('H:i') }}</span>
                </div>
                @endif
                @if($transport->stop->afternoon_dropoff ?? null)
                <div class="flex justify-between text-sm">
                    <span class="text-slate-400 font-medium">Afternoon Drop</span>
                    <span class="text-slate-700">{{ \Carbon\Carbon::parse($transport->stop->afternoon_dropoff)->format('H:i') }}</span>
                </div>
                @endif
                <div class="flex justify-between text-sm">
                    <span class="text-slate-400 font-medium">Start Date</span>
                    <span class="text-slate-700">{{ $transport->start_date ? \Carbon\Carbon::parse($transport->start_date)->format('M d, Y') : '—' }}</span>
                </div>
            </div>
        </div>

        {{-- Vehicle & driver card --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
                <p class="text-sm font-semibold text-slate-700"><i class="bi bi-bus-front-fill me-1 text-emerald-500"></i>Vehicle & Driver</p>
            </div>
            <div class="px-5 py-4 space-y-3">
                @php $vehicle = $transport->route->vehicle ?? null; $driver = $transport->route->driver ?? null; @endphp
                @if($vehicle)
                <div class="flex justify-between text-sm">
                    <span class="text-slate-400 font-medium">Vehicle</span>
                    <span class="text-slate-700">{{ $vehicle->make ?? '' }} {{ $vehicle->model ?? '' }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-400 font-medium">Plate No.</span>
                    <span class="text-slate-700 font-mono">{{ $vehicle->plate_number ?? '—' }}</span>
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-4">No vehicle assigned.</p>
                @endif
                @if($driver)
                <div class="flex justify-between text-sm">
                    <span class="text-slate-400 font-medium">Driver</span>
                    <span class="text-slate-700">{{ $driver->name ?? '—' }}</span>
                </div>
                @if($driver->phone ?? null)
                <div class="flex justify-between text-sm">
                    <span class="text-slate-400 font-medium">Driver Phone</span>
                    <a href="tel:{{ $driver->phone }}" class="text-indigo-600 hover:underline">{{ $driver->phone }}</a>
                </div>
                @endif
                @endif
            </div>
        </div>
    </div>

    {{-- All stops on this route --}}
    @if(($transport->route->stops ?? collect())->count())
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
            <p class="text-sm font-semibold text-slate-700"><i class="bi bi-geo-alt-fill me-1 text-rose-500"></i>All Stops on Route</p>
        </div>
        <div class="divide-y divide-slate-50">
            @foreach($transport->route->stops->sortBy('stop_order') as $stop)
            <div class="px-5 py-3 flex items-center gap-4 {{ $stop->id === ($transport->stop_id ?? null) ? 'bg-indigo-50' : '' }}">
                <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center flex-shrink-0 text-[10px] font-bold
                    {{ $stop->id === ($transport->stop_id ?? null) ? 'border-indigo-500 bg-indigo-500 text-white' : 'border-slate-300 text-slate-500' }}">
                    {{ $stop->stop_order }}
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium {{ $stop->id === ($transport->stop_id ?? null) ? 'text-indigo-700' : 'text-slate-700' }}">
                        {{ $stop->name }}
                        @if($stop->id === ($transport->stop_id ?? null))
                        <span class="ml-1 text-[10px] bg-indigo-100 text-indigo-600 px-1.5 py-0.5 rounded-full">Your stop</span>
                        @endif
                    </p>
                </div>
                <div class="text-xs text-slate-400 flex gap-4 flex-shrink-0">
                    @if($stop->morning_pickup)
                    <span><i class="bi bi-sunrise me-0.5"></i>{{ \Carbon\Carbon::parse($stop->morning_pickup)->format('H:i') }}</span>
                    @endif
                    @if($stop->afternoon_dropoff)
                    <span><i class="bi bi-sunset me-0.5"></i>{{ \Carbon\Carbon::parse($stop->afternoon_dropoff)->format('H:i') }}</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @endif

</div>
</div>
@endsection
