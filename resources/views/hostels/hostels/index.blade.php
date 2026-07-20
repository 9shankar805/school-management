@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">
        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div><h1 class="text-2xl font-bold text-slate-800 tracking-tight">Hostels</h1></div>
        </div>
        @if(session('status'))<div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>@endif

        @can('create hostel')
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6">
            <p class="text-sm font-semibold text-slate-700 mb-4">Create Hostel</p>
            <form method="POST" action="{{ route('hostel.hostels.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @csrf
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Name *</label><input type="text" name="name" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Type *</label>
                    <select name="type" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="Boys">Boys</option>
                        <option value="Girls">Girls</option>
                        <option value="Mixed">Mixed</option>
                    </select>
                </div>
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Intake Capacity *</label><input type="number" name="intake_capacity" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Warden</label>
                    <select name="warden_id" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">— None —</option>
                        @foreach($wardens as $w)<option value="{{ $w->id }}">{{ $w->full_name }}</option>@endforeach
                    </select>
                </div>
                <div class="md:col-span-2"><label class="block text-xs font-medium text-slate-500 mb-1">Address / Description</label><input type="text" name="address" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                <div class="flex items-end"><button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">Create</button></div>
            </form>
        </div>
        @endcan

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @forelse($hostels as $h)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 relative">
                @can('delete hostel')
                <form method="POST" action="{{ route('hostel.hostels.destroy', $h->id) }}" class="absolute top-4 right-4">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-slate-400 hover:text-rose-500 transition"><i class="bi bi-trash"></i></button>
                </form>
                @endcan
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl"><i class="bi bi-building"></i></div>
                    <div>
                        <h3 class="font-bold text-slate-800">{{ $h->name }}</h3>
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-full {{ $h->type == 'Boys' ? 'bg-blue-100 text-blue-700' : ($h->type == 'Girls' ? 'bg-pink-100 text-pink-700' : 'bg-purple-100 text-purple-700') }}">{{ $h->type }}</span>
                    </div>
                </div>
                <p class="text-sm text-slate-600 mb-2"><i class="bi bi-people me-2"></i>Capacity: <strong>{{ $h->intake_capacity }}</strong></p>
                <p class="text-sm text-slate-600 mb-2"><i class="bi bi-geo-alt me-2"></i>{{ $h->address ?? 'No address' }}</p>
                <hr class="my-3 border-slate-100">
                <p class="text-xs text-slate-500"><i class="bi bi-person-badge me-2"></i>Warden: <strong>{{ $h->warden ? $h->warden->full_name : 'Unassigned' }}</strong></p>
            </div>
            @empty
            <div class="col-span-3 bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
                <i class="bi bi-building text-4xl text-slate-200"></i>
                <p class="mt-3 text-slate-400">No hostels configured yet.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection