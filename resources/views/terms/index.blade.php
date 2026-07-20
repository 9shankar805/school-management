@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Terms</h1>
                <p class="text-slate-400 text-sm mt-0.5">Academic terms within semesters</p>
            </div>
        </div>

        @include('session-messages')

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            {{-- Term list --}}
            <div class="xl:col-span-2">
                @if($terms->isEmpty())
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
                    <i class="bi bi-calendar2-range text-5xl text-slate-200"></i>
                    <p class="mt-3 text-slate-400">No terms yet for this session.</p>
                </div>
                @else
                <div class="space-y-3">
                    @foreach($terms as $term)
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
                        <div class="flex flex-wrap justify-between items-start gap-3">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="font-semibold text-slate-800">{{ $term->name }}</h3>
                                    <span class="text-[11px] px-2 py-0.5 rounded-full font-medium
                                        {{ $term->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $term->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap gap-4 mt-1 text-xs text-slate-500">
                                    <span><i class="bi bi-collection me-1"></i>{{ $term->semester->semester_name ?? '—' }}</span>
                                    <span><i class="bi bi-calendar-range me-1"></i>
                                        {{ $term->start_date->format('M d, Y') }} – {{ $term->end_date->format('M d, Y') }}
                                    </span>
                                    <span><i class="bi bi-clock me-1"></i>{{ $term->start_date->diffInDays($term->end_date) }} days</span>
                                </div>
                                @if($term->description)
                                <p class="mt-1 text-xs text-slate-400">{{ $term->description }}</p>
                                @endif
                            </div>
                            @can('view academic settings')
                            <div class="flex items-center gap-1">
                                {{-- Inline edit modal trigger --}}
                                <button onclick="openEditTerm({{ $term->id }}, '{{ $term->name }}', '{{ $term->semester_id }}', '{{ $term->start_date->format('Y-m-d') }}', '{{ $term->end_date->format('Y-m-d') }}', {{ $term->is_active ? 'true' : 'false' }}, '{{ addslashes($term->description ?? '') }}')"
                                        class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                                    <i class="bi bi-pencil text-sm"></i>
                                </button>
                                <form action="{{ route('terms.destroy', $term->id) }}" method="POST" onsubmit="return confirm('Delete this term?')">
                                    @csrf @method('DELETE')
                                    <button class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                        <i class="bi bi-trash text-sm"></i>
                                    </button>
                                </form>
                            </div>
                            @endcan
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Create form --}}
            @can('view academic settings')
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <h2 class="text-base font-semibold text-slate-700 mb-4"><i class="bi bi-plus-circle me-1 text-indigo-500"></i>New Term</h2>
                <form action="{{ route('terms.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="hidden" name="session_id" value="{{ $session_id }}">
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Name <span class="text-rose-400">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. First Term"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Semester <span class="text-rose-400">*</span></label>
                        <select name="semester_id" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="">Select semester…</option>
                            @foreach($semesters as $sem)
                            <option value="{{ $sem->id }}" {{ old('semester_id') == $sem->id ? 'selected' : '' }}>{{ $sem->semester_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Start Date <span class="text-rose-400">*</span></label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" required
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">End Date <span class="text-rose-400">*</span></label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" required
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-slate-600 block mb-1">Description</label>
                        <textarea name="description" rows="2" placeholder="Optional..."
                                  class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('description') }}</textarea>
                    </div>
                    <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">
                        Create Term
                    </button>
                </form>
            </div>
            @endcan

        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div id="editTermModal" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
        <h2 class="text-base font-semibold text-slate-700 mb-4">Edit Term</h2>
        <form id="editTermForm" method="POST" class="space-y-3">
            @csrf @method('PUT')
            <div>
                <label class="text-xs font-medium text-slate-600 block mb-1">Name</label>
                <input type="text" id="editTermName" name="name" required
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <div>
                <label class="text-xs font-medium text-slate-600 block mb-1">Semester</label>
                <select id="editTermSemester" name="semester_id" required
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    @foreach($semesters as $sem)
                    <option value="{{ $sem->id }}">{{ $sem->semester_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">Start Date</label>
                    <input type="date" id="editTermStart" name="start_date" required
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="text-xs font-medium text-slate-600 block mb-1">End Date</label>
                    <input type="date" id="editTermEnd" name="end_date" required
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
            </div>
            <div>
                <label class="text-xs font-medium text-slate-600 block mb-1">Description</label>
                <textarea id="editTermDesc" name="description" rows="2"
                          class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="editTermActive" name="is_active" value="1" class="rounded text-indigo-600">
                <label for="editTermActive" class="text-sm text-slate-600">Active</label>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">Save</button>
                <button type="button" onclick="document.getElementById('editTermModal').classList.add('hidden')"
                        class="flex-1 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditTerm(id, name, semId, start, end, isActive, desc) {
    document.getElementById('editTermForm').action = '/terms/' + id;
    document.getElementById('editTermName').value  = name;
    document.getElementById('editTermStart').value = start;
    document.getElementById('editTermEnd').value   = end;
    document.getElementById('editTermDesc').value  = desc;
    document.getElementById('editTermActive').checked = isActive;
    document.getElementById('editTermSemester').value = semId;
    document.getElementById('editTermModal').classList.remove('hidden');
}
</script>
@endsection
