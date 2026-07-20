@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight"><i class="bi bi-building me-2"></i>Exam Halls</h1>
                <p class="text-slate-400 text-sm mt-0.5">Manage rooms and seating capacity</p>
            </div>
            @can('create exams')
            <button onclick="document.getElementById('addHallModal').classList.remove('hidden')"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition flex items-center gap-1.5">
                <i class="bi bi-plus-lg"></i> Add Hall
            </button>
            @endcan
        </div>

        @if(session('status'))
        <div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>
        @endif
        @include('session-messages')

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @forelse($halls as $hall)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <p class="font-semibold text-slate-800">{{ $hall->hall_name }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $hall->building ?? '' }}{{ $hall->floor ? ' · Floor ' . $hall->floor : '' }}
                        </p>
                    </div>
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $hall->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}">
                        {{ $hall->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="flex items-center gap-1.5 text-2xl font-bold text-indigo-600 mb-1">
                    {{ $hall->capacity }}
                    <span class="text-sm font-normal text-slate-400">seats</span>
                </div>
                @if($hall->notes)<p class="text-xs text-slate-400 mt-1 line-clamp-1">{{ $hall->notes }}</p>@endif
                @can('create exams')
                <div class="flex gap-2 mt-4 pt-3 border-t border-slate-50">
                    <button onclick="editHall({{ $hall->id }}, '{{ addslashes($hall->hall_name) }}', '{{ addslashes($hall->building ?? '') }}', '{{ addslashes($hall->floor ?? '') }}', {{ $hall->capacity }}, '{{ addslashes($hall->notes ?? '') }}', {{ $hall->is_active ? 1 : 0 }})"
                            class="flex-1 text-center text-xs py-1.5 rounded-lg bg-slate-50 hover:bg-indigo-50 text-slate-600 hover:text-indigo-600 transition">Edit</button>
                    <form method="POST" action="{{ route('exam.hall.destroy', $hall->id) }}" onsubmit="return confirm('Delete this hall?')" class="flex-1">
                        @csrf @method('DELETE')
                        <button class="w-full text-xs py-1.5 rounded-lg bg-slate-50 hover:bg-rose-50 text-slate-600 hover:text-rose-600 transition">Delete</button>
                    </form>
                </div>
                @endcan
            </div>
            @empty
            <div class="col-span-3 bg-white rounded-2xl border border-slate-100 p-10 text-center text-slate-400">
                <i class="bi bi-building text-5xl mb-3 block"></i>
                <p class="text-sm">No halls yet. Add one to start scheduling exams.</p>
            </div>
            @endforelse
        </div>

        {{-- Add Hall Modal --}}
        @can('create exams')
        <div id="addHallModal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md">
                <h3 class="text-base font-semibold text-slate-800 mb-4">Add Exam Hall</h3>
                <form method="POST" action="{{ route('exam.hall.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-slate-600 mb-1">Hall Name *</label>
                            <input type="text" name="hall_name" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Building</label>
                            <input type="text" name="building" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Floor</label>
                            <input type="text" name="floor" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-slate-600 mb-1">Capacity *</label>
                            <input type="number" name="capacity" value="30" min="1" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-slate-600 mb-1">Notes</label>
                            <textarea name="notes" rows="2" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea>
                        </div>
                    </div>
                    <div class="flex gap-2 justify-end">
                        <button type="button" onclick="document.getElementById('addHallModal').classList.add('hidden')" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">Save Hall</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Edit Hall Modal --}}
        <div id="editHallModal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md">
                <h3 class="text-base font-semibold text-slate-800 mb-4">Edit Hall</h3>
                <form id="editHallForm" method="POST" class="space-y-4">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-slate-600 mb-1">Hall Name *</label>
                            <input type="text" id="editHallName" name="hall_name" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Building</label>
                            <input type="text" id="editBuilding" name="building" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Floor</label>
                            <input type="text" id="editFloor" name="floor" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-slate-600 mb-1">Capacity *</label>
                            <input type="number" id="editCapacity" name="capacity" min="1" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>
                        <div class="col-span-2 flex items-center gap-2">
                            <input type="checkbox" id="editActive" name="is_active" value="1" class="accent-indigo-600">
                            <label for="editActive" class="text-sm text-slate-700">Active</label>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-slate-600 mb-1">Notes</label>
                            <textarea id="editNotes" name="notes" rows="2" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea>
                        </div>
                    </div>
                    <div class="flex gap-2 justify-end">
                        <button type="button" onclick="document.getElementById('editHallModal').classList.add('hidden')" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">Update</button>
                    </div>
                </form>
            </div>
        </div>
        @endcan
    </div>
</div>
@push('scripts')
<script>
function editHall(id, name, building, floor, capacity, notes, active) {
    document.getElementById('editHallForm').action = `/exam/halls/${id}`;
    document.getElementById('editHallName').value  = name;
    document.getElementById('editBuilding').value   = building;
    document.getElementById('editFloor').value      = floor;
    document.getElementById('editCapacity').value   = capacity;
    document.getElementById('editNotes').value      = notes;
    document.getElementById('editActive').checked   = !!active;
    document.getElementById('editHallModal').classList.remove('hidden');
}
</script>
@endpush
@endsection
