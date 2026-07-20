@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">
        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Hostel Rooms</h1>
                <div class="flex gap-4 mt-2">
                    <a href="{{ route('hostel.rooms.index') }}" class="text-sm font-semibold text-indigo-600 border-b-2 border-indigo-600 pb-1">Rooms</a>
                    <a href="{{ route('hostel.beds.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-800 pb-1">Beds</a>
                </div>
            </div>
        </div>
        @if(session('status'))<div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>@endif

        @can('manage hostel rooms')
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6">
            <p class="text-sm font-semibold text-slate-700 mb-4">Add Room</p>
            <form method="POST" action="{{ route('hostel.rooms.store') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                @csrf
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Hostel *</label>
                    <select name="hostel_id" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach($hostels as $h)<option value="{{ $h->id }}">{{ $h->name }}</option>@endforeach
                    </select>
                </div>
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Room No *</label><input type="text" name="room_number" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Type *</label>
                    <select name="room_type" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="Non-AC">Non-AC</option><option value="AC">AC</option>
                    </select>
                </div>
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Capacity *</label><input type="number" name="capacity" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></div>
                <div><label class="block text-xs font-medium text-slate-500 mb-1">Cost Per Bed *</label>
                    <div class="flex gap-2">
                        <input type="number" step="0.01" name="cost_per_bed" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">Add</button>
                    </div>
                </div>
            </form>
        </div>
        @endcan

        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Hostel</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Room No</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Type</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Capacity</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-700">Cost/Bed</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-700">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($rooms as $r)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-800 font-medium">{{ $r->hostel->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $r->room_number }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-1 rounded text-xs {{ $r->room_type=='AC' ? 'bg-sky-100 text-sky-700' : 'bg-slate-100 text-slate-700' }}">{{ $r->room_type }}</span></td>
                            <td class="px-4 py-3 text-slate-600">{{ $r->capacity }} beds</td>
                            <td class="px-4 py-3 text-slate-600">${{ number_format($r->cost_per_bed, 2) }}</td>
                            <td class="px-4 py-3 text-right">
                                @can('manage hostel rooms')
                                <form method="POST" action="{{ route('hostel.rooms.destroy', $r->id) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="text-rose-500 hover:text-rose-700" onclick="return confirm('Delete room?')"><i class="bi bi-trash"></i></button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">No rooms found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection