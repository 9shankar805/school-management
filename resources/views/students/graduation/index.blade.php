@extends('layouts.app')
@section('content')
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
        <div class="flex gap-1 bg-white rounded-xl border border-slate-200 shadow-sm p-1 mb-6 overflow-x-auto">
            @foreach(['eligible' => 'Active Students', 'graduated' => 'Graduated / Alumni', 'dropouts' => 'Dropouts / Withdrawn'] as $t => $l)
            <a href="{{ route('students.graduation.index', ['tab' => $t, 'search' => $search]) }}"
               class="flex-shrink-0 flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-semibold transition whitespace-nowrap
               {{ $tab === $t ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">
                {{ $l }}
                <span class="ml-1 text-[10px] px-1.5 py-0.5 rounded-full font-bold
                    {{ $tab === $t ? 'bg-white/30 text-white' : 'bg-slate-200 text-slate-600' }}">
                    {{ $t === 'eligible' ? $counts['active'] : ($t === 'graduated' ? $counts['graduated'] : $counts['dropouts']) }}
                </span>
            </a>
            @endforeach
        </div>

        {{-- Search --}}
        <form method="GET" action="{{ route('students.graduation.index') }}" class="mb-4 flex gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="relative flex-1">
                <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search students…" class="w-full border border-slate-200 rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <button type="submit" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-700 hover:bg-slate-50 transition flex items-center gap-1.5">
                <i class="bi bi-search"></i> Search
            </button>
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
                            <td class="px-5 py-3 text-slate-500">{{ $cs?->alumni_batch ?? $cs?->reason ?? '—' }}</td>
                            @can('create students')
                            <td class="px-5 py-3">
                                <button type="button"
                                    onclick="openStatusModal({{ $s->id }}, '{{ addslashes($s->full_name) }}', '{{ $cs?->status ?? 'active' }}', '{{ addslashes($cs?->alumni_batch ?? '') }}', '{{ $cs?->effective_date?->format('Y-m-d') ?? now()->toDateString() }}', '{{ addslashes($cs?->reason ?? '') }}', '{{ addslashes($cs?->destination_school ?? '') }}', '{{ addslashes($cs?->graduation_certificate_no ?? '') }}')"
                                    class="text-xs px-3 py-1.5 bg-slate-100 hover:bg-indigo-50 hover:text-indigo-700 text-slate-700 font-semibold rounded-lg border border-slate-200 transition">
                                    Change Status
                                </button>
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

@push('modals')
{{-- Global Status Update Modal (Placed outside table container to prevent clipping) --}}
@can('create students')
<div id="statusModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-[9999] items-center justify-center p-4 print-hide" onclick="if(event.target===this)closeStatusModal()">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-slate-200 animate-in fade-in zoom-in duration-150">
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
            <h3 class="font-bold text-sm flex items-center gap-2">
                <i class="bi bi-person-gear text-indigo-400"></i> Update Student Status
            </h3>
            <button type="button" onclick="closeStatusModal()" class="text-slate-400 hover:text-white text-base">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form id="statusModalForm" method="POST" action="" class="p-6 space-y-4">
            @csrf
            <div>
                <p class="text-xs text-slate-400 font-medium mb-0.5">Student</p>
                <p id="modalStudentName" class="font-bold text-slate-800 text-base"></p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">New Status *</label>
                <select name="status" id="modalStatusSelect" required onchange="toggleStatusFields(this.value)"
                        class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-xs text-slate-800 bg-white focus:ring-2 focus:ring-indigo-500 font-medium">
                    @foreach(\App\Models\StudentStatus::STATUSES as $v => $l)
                    <option value="{{ $v }}">{{ $l }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Effective Date *</label>
                <input type="date" name="effective_date" id="modalEffectiveDate" value="{{ now()->toDateString() }}" required
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 bg-white focus:ring-2 focus:ring-indigo-500 font-medium">
            </div>

            <div id="fieldAlumniBatch">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Alumni Batch (e.g. Class of 2026)</label>
                <input type="text" name="alumni_batch" id="modalAlumniBatch" placeholder="e.g. Class of 2026"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 bg-white focus:ring-2 focus:ring-indigo-500">
            </div>

            <div id="fieldCertNo">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Graduation Certificate No. (optional)</label>
                <input type="text" name="graduation_certificate_no" id="modalCertNo" placeholder="e.g. CERT-2026-001"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 bg-white focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Reason / Remarks (optional)</label>
                <textarea name="reason" id="modalReason" rows="2" placeholder="Administrative notes or reason for status change"
                          class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 bg-white focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
            </div>

            <div id="fieldDestSchool">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Destination School (for transfers / dropouts)</label>
                <input type="text" name="destination_school" id="modalDestSchool" placeholder="e.g. Kathmandu Model College"
                       class="w-full border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 bg-white focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="flex gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeStatusModal()"
                        class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold transition shadow-xs">
                    Save Status
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openStatusModal(studentId, studentName, status, batch, effectiveDate, reason, destinationSchool, certNo) {
    document.getElementById('modalStudentName').textContent = studentName;
    document.getElementById('modalStatusSelect').value = status || 'active';
    document.getElementById('modalEffectiveDate').value = effectiveDate || '{{ now()->toDateString() }}';
    document.getElementById('modalAlumniBatch').value = batch || '';
    document.getElementById('modalReason').value = reason || '';
    document.getElementById('modalDestSchool').value = destinationSchool || '';
    document.getElementById('modalCertNo').value = certNo || '';
    document.getElementById('statusModalForm').action = `/graduation/${studentId}/process`;

    toggleStatusFields(status || 'active');

    const modal = document.getElementById('statusModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeStatusModal() {
    const modal = document.getElementById('statusModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function toggleStatusFields(status) {
    const isGraduated = (status === 'graduated' || status === 'alumni');
    const isTransferOrDropout = (status === 'dropped_out' || status === 'withdrawn' || status === 'transferred');

    const batchField = document.getElementById('fieldAlumniBatch');
    const certField = document.getElementById('fieldCertNo');
    const destField = document.getElementById('fieldDestSchool');

    if (batchField) batchField.style.display = isGraduated ? 'block' : 'none';
    if (certField) certField.style.display = isGraduated ? 'block' : 'none';
    if (destField) destField.style.display = isTransferOrDropout ? 'block' : 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeStatusModal();
    }
});
</script>
@endcan
@endpush
