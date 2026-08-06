@extends('layouts.app')
@section('content')
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        {{-- Top Title & Actions --}}
        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight"><i class="bi bi-building me-2 text-indigo-600"></i>Exam Halls & Bench Seating Manager</h1>
                <p class="text-slate-400 text-sm mt-0.5">Manage room capacities, bench layouts, and auto-allocate student seating plans</p>
            </div>
            @can('create exams')
            <div class="flex items-center gap-3">
                <a href="{{ route('exam.schedule.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-semibold transition flex items-center gap-1.5">
                    <i class="bi bi-calendar-event me-1"></i> Exam Schedule
                </a>
                <button onclick="document.getElementById('addHallModal').classList.remove('hidden')"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition flex items-center gap-1.5 shadow-sm">
                    <i class="bi bi-plus-lg"></i> Add New Room/Hall
                </button>
            </div>
            @endcan
        </div>

        @if(session('status'))
        <div class="mb-5 p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm flex items-center gap-2">
            <i class="bi bi-check-circle-fill"></i>{{ session('status') }}
        </div>
        @endif
        @include('session-messages')

        {{-- Active Examination Bench Seating Schedules --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 mb-8">
            <div class="flex justify-between items-center border-b border-slate-100 pb-4 mb-5">
                <div>
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="bi bi-grid-3x3-gap-fill text-indigo-600"></i> Examination Sittings & Bench Seating Plans
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Select an examination sitting to configure Columns, Benches & Auto-Allocate Roll Numbers</p>
                </div>
                <span class="text-xs bg-indigo-50 text-indigo-700 font-bold px-3 py-1 rounded-full">
                    {{ $schedules->count() }} Schedules
                </span>
            </div>

            @if($schedules->isEmpty())
                <div class="p-8 text-center text-sm text-slate-400 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                    No active exam schedules found. Create an exam schedule to start auto-allocating student bench seats.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($schedules as $sch)
                    @php
                        $allocCount = \App\Models\ExamSeatAllocation::where('schedule_id', $sch->id)->count();
                    @endphp
                    <div class="bg-slate-50/70 rounded-2xl border border-slate-200/70 p-5 hover:border-indigo-300 transition flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-[11px] font-bold text-indigo-600 bg-indigo-50 px-2.5 py-0.5 rounded-md uppercase">
                                    {{ $sch->exam?->schoolClass?->class_name ?? 'All Classes' }}
                                </span>
                                <span class="text-xs font-mono font-semibold px-2 py-0.5 rounded-full {{ $allocCount > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $allocCount > 0 ? $allocCount . ' Seated' : 'Not Seated' }}
                                </span>
                            </div>

                            <h3 class="text-base font-bold text-slate-800">{{ $sch->exam?->exam_name }}</h3>
                            <p class="text-xs text-slate-500 font-medium mt-1">
                                <i class="bi bi-building me-1 text-slate-400"></i>{{ $sch->hall?->hall_name ?? 'Unassigned Hall' }}
                            </p>
                            <p class="text-xs text-slate-400 mt-1">
                                <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($sch->exam_date)->format('d M Y') }} · {{ \Carbon\Carbon::parse($sch->start_time)->format('H:i') }}
                            </p>
                        </div>

                        <div class="pt-4 mt-4 border-t border-slate-200/60 flex items-center justify-between gap-2">
                            <a href="{{ route('exam.hall.seats', $sch->id) }}"
                               class="flex-1 py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold text-center transition flex items-center justify-center gap-1.5 shadow-sm">
                                <i class="bi bi-grid-3x3-gap"></i> Manage Bench Seating
                            </a>
                            <a href="{{ route('exam.hall.seats.print', $sch->id) }}" target="_blank"
                               class="p-2 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 rounded-xl text-xs font-bold transition" title="Print Door Chart">
                                <i class="bi bi-printer text-sm"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Examination Rooms & Halls List --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
            <div class="flex justify-between items-center border-b border-slate-100 pb-4 mb-5">
                <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class="bi bi-building text-slate-500"></i> Registered Examination Rooms & Halls
                </h2>
                <span class="text-xs text-slate-400 font-medium">{{ $halls->count() }} Halls Configured</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                @forelse($halls as $hall)
                <div class="bg-slate-50/50 rounded-2xl border border-slate-200/70 p-5">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <p class="font-bold text-slate-800 text-base">{{ $hall->hall_name }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">
                                {{ $hall->building ?? 'Main Campus' }}{{ $hall->floor ? ' · Floor ' . $hall->floor : '' }}
                            </p>
                        </div>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $hall->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}">
                            {{ $hall->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-2 bg-white p-3 rounded-xl border border-slate-100 my-3 text-center">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Cols</span>
                            <span class="text-sm font-bold text-slate-700">{{ $hall->columns ?? 4 }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">Benches</span>
                            <span class="text-sm font-bold text-slate-700">{{ $hall->benches_per_column ?? 5 }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-indigo-500 block">Seat/Bench</span>
                            <span class="text-sm font-bold text-indigo-700">{{ $hall->students_per_bench ?? 2 }}</span>
                        </div>
                    </div>

                    @if($hall->notes)<p class="text-xs text-slate-400 mt-1 line-clamp-1">{{ $hall->notes }}</p>@endif
                    @can('create exams')
                    <div class="flex gap-2 mt-4 pt-3 border-t border-slate-200/60">
                        <button onclick="editHall({{ $hall->id }}, '{{ addslashes($hall->hall_name) }}', '{{ addslashes($hall->building ?? '') }}', '{{ addslashes($hall->floor ?? '') }}', {{ $hall->capacity }}, '{{ addslashes($hall->notes ?? '') }}', {{ $hall->is_active ? 1 : 0 }})"
                                class="flex-1 text-center text-xs py-1.5 font-semibold rounded-lg bg-white border border-slate-200 hover:bg-indigo-50 text-slate-700 hover:text-indigo-600 transition">Edit Details</button>
                        <form method="POST" action="{{ route('exam.hall.destroy', $hall->id) }}" onsubmit="return confirm('Delete this hall?')" class="flex-1">
                            @csrf @method('DELETE')
                            <button class="w-full text-xs py-1.5 font-semibold rounded-lg bg-white border border-slate-200 hover:bg-rose-50 text-slate-600 hover:text-rose-600 transition">Delete</button>
                        </form>
                    </div>
                    @endcan
                </div>
                @empty
                <div class="col-span-3 bg-white rounded-2xl border border-slate-100 p-10 text-center text-slate-400">
                    <i class="bi bi-building text-5xl mb-3 block text-slate-300"></i>
                    <p class="text-sm">No exam halls configured yet. Click "Add New Room/Hall" above to register an exam hall.</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Add Hall Modal --}}
        @can('create exams')
        <div id="addHallModal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md">
                <h3 class="text-base font-bold text-slate-800 mb-4">Add Examination Hall</h3>
                <form method="POST" action="{{ route('exam.hall.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Hall / Room Name *</label>
                            <input type="text" name="hall_name" required placeholder="e.g. Main Auditorium Complex" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Building</label>
                            <input type="text" name="building" placeholder="e.g. Block A" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Floor</label>
                            <input type="text" name="floor" placeholder="e.g. 1st Floor" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Overall Seating Capacity *</label>
                            <input type="number" name="capacity" value="40" min="1" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Notes</label>
                            <textarea name="notes" rows="2" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea>
                        </div>
                    </div>
                    <div class="flex gap-2 justify-end pt-2">
                        <button type="button" onclick="document.getElementById('addHallModal').classList.add('hidden')" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition">Save Room/Hall</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Edit Hall Modal --}}
        <div id="editHallModal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md">
                <h3 class="text-base font-bold text-slate-800 mb-4">Edit Examination Hall</h3>
                <form id="editHallForm" method="POST" action="" class="space-y-4">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Hall / Room Name *</label>
                            <input type="text" id="edit_hall_name" name="hall_name" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Building</label>
                            <input type="text" id="edit_building" name="building" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Floor</label>
                            <input type="text" id="edit_floor" name="floor" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Capacity *</label>
                            <input type="number" id="edit_capacity" name="capacity" min="1" required class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Notes</label>
                            <textarea id="edit_notes" name="notes" rows="2" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea>
                        </div>
                    </div>
                    <div class="flex gap-2 justify-end pt-2">
                        <button type="button" onclick="document.getElementById('editHallModal').classList.add('hidden')" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
        @endcan

    </div>

    <script>
        function editHall(id, name, building, floor, capacity, notes, isActive) {
            document.getElementById('editHallForm').action = '/exam/halls/' + id;
            document.getElementById('edit_hall_name').value = name;
            document.getElementById('edit_building').value = building;
            document.getElementById('edit_floor').value = floor;
            document.getElementById('edit_capacity').value = capacity;
            document.getElementById('edit_notes').value = notes;
            document.getElementById('editHallModal').classList.remove('hidden');
        }
    </script>
@endsection
