@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">
        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Hostel Beds</h1>
                <div class="flex gap-4 mt-2">
                    <a href="{{ route('hostel.rooms.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-800 pb-1">Rooms</a>
                    <a href="{{ route('hostel.beds.index') }}" class="text-sm font-semibold text-indigo-600 border-b-2 border-indigo-600 pb-1">Beds</a>
                </div>
            </div>
        </div>
        @if(session('status'))<div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>@endif

        @can('manage hostel rooms')
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6">
            <p class="text-sm font-semibold text-slate-700 mb-4">Add Bed</p>
            <form method="POST" action="{{ route('hostel.beds.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                @csrf
                <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-500 mb-1">Room *</label>
                    <select name="hostel_room_id" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach(\App\Models\HostelRoom::with('hostel')->get() as $r)<option value="{{ $r->id }}">{{ $r->hostel->name }} - {{ $r->room_number }}</option>@endforeach
                    </select>
                </div>
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Bed Name/No *</label><input type="text" name="name" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                <div class="flex gap-2">
                    <input type="hidden" name="status" value="Available">
                    <button type="submit" class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">Add Bed</button>
                </div>
            </form>
        </div>
        @endcan

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @forelse($beds as $b)
            <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col items-center justify-center text-center shadow-sm relative group">
                @can('manage hostel rooms')
                <form method="POST" action="{{ route('hostel.beds.destroy', $b->id) }}" class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition">
                    @csrf @method('DELETE')
                    <button class="text-rose-400 hover:text-rose-600"><i class="bi bi-x-circle-fill"></i></button>
                </form>
                @endcan
                <i class="bi bi-square-half text-3xl mb-2 {{ $b->status=='Available' ? 'text-emerald-500' : ($b->status=='Occupied' ? 'text-blue-500' : 'text-rose-500') }}"></i>
                <h4 class="font-bold text-slate-700 text-lg">{{ $b->name }}</h4>
                <p class="text-[10px] text-slate-400 mb-2 uppercase">{{ $b->room->hostel->name }} / {{ $b->room->room_number }}</p>
                <span class="text-[10px] px-2 py-0.5 rounded-full font-medium {{ $b->status=='Available' ? 'bg-emerald-100 text-emerald-700' : ($b->status=='Occupied' ? 'bg-blue-100 text-blue-700' : 'bg-rose-100 text-rose-700') }}">{{ $b->status }}</span>
            </div>
            @empty
            <div class="col-span-full py-12 text-center text-slate-400 bg-white border border-slate-100 rounded-xl">No beds found.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection