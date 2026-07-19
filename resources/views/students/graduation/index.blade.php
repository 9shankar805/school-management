@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Student Status Management</h1>
                <p class="text-slate-400 text-sm mt-0.5">Graduation · Dropout · Alumni processing</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('students.alumni') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 transition text-slate-700">
                    <i class="bi bi-mortarboard"></i> Alumni Directory
                </a>
            </div>
        </div>

        @if(session('status'))
        <div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>
        @endif
        @if($errors->any())
        <div class="mb-5 p-3 bg-rose-50 border border-rose-200 rounded-xl text-sm text-rose-700">@foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>
        @endif

        {{-- Bulk graduation panel --}}
        @can('create students')
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-6">
            <p class="text-sm font-semibold text-slate-700 mb-4"><i class="bi bi-mortarboard-fill me-1 text-indigo-500"></i>Bulk Graduation — Process Entire Class</p>
            <form method="POST" action="{{ route('students.graduation.bulk') }}" class="flex flex-wrap gap-3 items-end">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Class</label>
                    <select name="class_id" required class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">— Select class —</option>
                        @foreach($classes as $c)<option value="{{ $c->id }}">{{ $c->class_name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Alumni Batch (e.g. Class of 2025)</label>
                    <input type="text" name="alumni_batch" value="Class of {{ now()->year }}" required class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Effective Date</label>
                    <input type="date" name="effective_date" value="{{ now()->toDateString() }}" required class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <button type="submit" onclick="return confirm('Graduate all students in this class?')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">
                    <i class="bi bi-mortarboard me-1"></i>Graduate Class
                </button>
            </form>
        </div>
        @endcan

        {{-- Status tabs --}}
        <div class="flex gap-1 bg-white rounded-xl border border-slate-100 shadow-sm p-1 mb-6 overflow-x-auto">
            @foreach(['eligible' => 'Active Students', 'graduated' => 'Graduated / Alumni', 'dropouts' => 'Dropouts / Withdrawn'] as $t => $l)
            <a href="{{ route('students.graduation.index', ['tab' => $t, 'search' => $search]) }}"
               class="flex-shrink-0 flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-medium transition whitespace-nowrap {{ $tab === $t ? 'bg-indigo-50 text-indigo-700' : 'text-slate-500 hover:bg-slate-50' }}">
                {{ $l }} <span class="ml-1 text-[10px] bg-slate-200 text-slate-600 px-1.5 py-0.5 rounded-full">
                    {{ $t === 'eligible' ? $counts['active'] : ($t === 'graduated' ? $counts['graduated'] : $counts['dropouts']) }}
                </span>
            </a>
            @endforeach
        </div>

        {{-- Search --}}
        <form method="GET" action="{{ route('students.graduation.index') }}" class="mb-4 flex gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search students…" class="flex-1 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <button type="submit" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-700 hover:bg-slate-50 transition">Search</button>
        </form>

        @php $students = $tab === 'eligible' ? $active : ($tab === 'graduated' ? $graduated : $dropouts); @endphp

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            @if($students->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-slate-400 bg-slate-50">
                            <th class="px-5 py-3 font-medium">Student</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium">Effective Date</th>
                            <th class="px-5 py-3 font-medium">Batch / Notes</th>
                            @can('create students')<th class="px-5 py-3 font-medium">Action</th>@endcan
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($students as $s)
                        @php $cs = $s->currentStatus; @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <img src="{{ $s->avatar }}" class="w-7 h-7 rounded-full object-cover flex-shrink-0" alt="">
                                    <div>
                                        <p class="font-medium text-slate-700">{{ $s->full_name }}</p>
                                        <p class="text-xs text-slate-400">{{ $s->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                @if($cs)
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $cs->status_badge }}">{{ \App\Models\StudentStatus::STATUSES[$cs->status] ?? $cs->status }}</span>
                                @else
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-emerald-100 text-emerald-700">Active</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-500">{{ $cs?->effective_date?->format('d M Y') ?? '—' }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $cs?->alumni_batch ?? $cs?->reason ?? '—' }}</td>
                            @can('create students')
                            <td class="px-5 py-3">
                                <button onclick="document.getElementById('status-modal-{{ $s->id }}').classList.remove('hidden')"
                                    class="text-xs px-3 py-1.5 bg-slate-100 hover:bg-indigo-50 hover:text-indigo-700 text-slate-600 rounded-lg transition">
                                    Change Status
                                </button>
                                {{-- Inline modal --}}
                                <div id="status-modal-{{ $s->id }}" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
                                    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                                        <p class="font-semibold text-slate-800 mb-4">Update Status — {{ $s->full_name }}</p>
                                        <form method="POST" action="{{ route('students.graduation.process', $s->id) }}" class="space-y-3">
                                            @csrf
                                            <div>
                                                <label class="block text-xs font-medium text-slate-500 mb-1">New Status</label>
                                                <select name="status" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                                    @foreach(\App\Models\StudentStatus::STATUSES as $v => $l)
                                                    <option value="{{ $v }}">{{ $l }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-slate-500 mb-1">Effective Date</label>
                                                <input type="date" name="effective_date" value="{{ now()->toDateString() }}" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                            </div>
                                            <input type="text" name="alumni_batch" placeholder="Alumni batch (e.g. Class of 2025)" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                            <textarea name="reason" rows="2" placeholder="Reason / notes (optional)" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea>
                                            <input type="text" name="destination_school" placeholder="Destination school (for transfers/dropouts)" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                            <div class="flex gap-2 pt-1">
                                                <button type="submit" class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">Save</button>
                                                <button type="button" onclick="document.getElementById('status-modal-{{ $s->id }}').classList.add('hidden')" class="flex-1 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm font-medium transition">Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                            @endcan
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-slate-50">{{ $students->links() }}</div>
            @else
            <p class="text-sm text-slate-400 text-center py-12">No students found.</p>
            @endif
        </div>
    </div>
</div>
@endsection
