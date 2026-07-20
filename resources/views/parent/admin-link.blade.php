@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <div class="flex-1 p-6 lg:p-8 overflow-auto">
        <nav class="text-xs text-slate-400 mb-4">
            <a href="{{ route('home') }}" class="hover:text-indigo-600">Home</a>
            <span class="mx-1">/</span>
            <span class="text-slate-600">Parent–Student Links</span>
        </nav>

        <h1 class="text-xl font-bold text-slate-800 mb-6 tracking-tight">Parent–Student Account Linking</h1>

        @include('session-messages')

        {{-- Search parents --}}
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5 mb-6">
            <p class="text-sm font-semibold text-slate-700 mb-3">Search Parent User</p>
            <div class="flex gap-3">
                <input type="text" id="parentSearch" placeholder="Search by name or email…"
                       class="flex-1 text-sm border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <button onclick="searchParents()"
                        class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">
                    Search
                </button>
            </div>
            <div id="parentResults" class="mt-3 space-y-2 hidden"></div>
        </div>

        {{-- Link form (shown when parent selected) --}}
        <div id="linkFormContainer" class="hidden">
            <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5 mb-6">
                <p class="text-sm font-semibold text-slate-700 mb-4">Link Student to Parent: <span id="selectedParentName" class="text-indigo-600"></span></p>
                <form id="linkForm" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @csrf
                    <input type="hidden" name="parent_id_hidden" id="selectedParentId">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Student Name / Email</label>
                        <input type="text" id="studentSearch" placeholder="Start typing student name…"
                               class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <input type="hidden" name="student_id" id="selectedStudentId">
                        <div id="studentResults" class="mt-1 space-y-1 hidden"></div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Relationship</label>
                        <select name="relationship" class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                            <option value="guardian">Guardian</option>
                            <option value="father">Father</option>
                            <option value="mother">Mother</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit"
                                class="w-full px-4 py-2 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700 font-medium">
                            <i class="bi bi-link-45deg me-1"></i>Link Student
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- All parent users with children --}}
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100">
                <p class="text-sm font-semibold text-slate-700">All Parent Accounts</p>
            </div>
            @php
                $allParents = \App\Models\User::role('parent')->with('children')->get();
            @endphp
            @if($allParents->isEmpty())
            <p class="text-sm text-slate-400 text-center py-10">No parent accounts found.</p>
            @else
            <div class="divide-y divide-slate-50">
                @foreach($allParents as $p)
                <div class="px-5 py-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <img src="{{ $p->avatar }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0" alt="">
                            <div>
                                <p class="font-semibold text-slate-800 text-sm">{{ $p->full_name }}</p>
                                <p class="text-xs text-slate-400">{{ $p->email }}</p>
                            </div>
                        </div>
                        <button onclick="selectParent({{ $p->id }}, '{{ $p->full_name }}')"
                                class="text-xs px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 font-medium flex-shrink-0">
                            + Link Student
                        </button>
                    </div>
                    @if($p->children->isNotEmpty())
                    <div class="mt-3 flex flex-wrap gap-2 ml-11">
                        @foreach($p->children as $child)
                        <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5">
                            <img src="{{ $child->avatar }}" class="w-5 h-5 rounded-full object-cover" alt="">
                            <span class="text-xs text-slate-700 font-medium">{{ $child->full_name }}</span>
                            <span class="text-[10px] text-slate-400 capitalize">({{ $child->pivot->relationship }})</span>
                            <form method="POST" action="{{ route('admin.parents.unlink', [$p->id, $child->id]) }}">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="return confirm('Remove this link?')"
                                        class="text-[10px] text-rose-400 hover:text-rose-600 ml-1">
                                    <i class="bi bi-x"></i>
                                </button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-xs text-slate-400 ml-11 mt-1">No children linked yet.</p>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function selectParent(id, name) {
    document.getElementById('selectedParentId').value = id;
    document.getElementById('selectedParentName').textContent = name;
    document.getElementById('linkFormContainer').classList.remove('hidden');
    document.getElementById('linkForm').action = '/admin/parents/' + id + '/link';
    document.getElementById('linkFormContainer').scrollIntoView({ behavior: 'smooth' });
}

async function searchParents() {
    const q = document.getElementById('parentSearch').value;
    if (!q) return;
    const res = await fetch('/admin/parents/students/search?q=' + encodeURIComponent(q) + '&role=parent');
    // reuse same endpoint but filter by parent role — for now just list all parents via PHP above
}

let studentSearchTimer;
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('studentSearch');
    if (!input) return;
    input.addEventListener('input', function () {
        clearTimeout(studentSearchTimer);
        const q = this.value;
        if (q.length < 2) { document.getElementById('studentResults').classList.add('hidden'); return; }
        studentSearchTimer = setTimeout(async () => {
            const res = await fetch('/admin/parents/students/search?q=' + encodeURIComponent(q));
            const data = await res.json();
            const container = document.getElementById('studentResults');
            container.innerHTML = '';
            data.forEach(s => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'w-full text-left text-xs px-3 py-2 bg-slate-50 hover:bg-indigo-50 rounded-lg border border-slate-200';
                btn.textContent = s.text;
                btn.onclick = () => {
                    document.getElementById('selectedStudentId').value = s.id;
                    document.getElementById('studentSearch').value = s.text;
                    container.classList.add('hidden');
                };
                container.appendChild(btn);
            });
            container.classList.toggle('hidden', data.length === 0);
        }, 300);
    });
});
</script>
@endpush
