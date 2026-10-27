@extends('layouts.app')
@section('content')
<div class="px-4 lg:px-8 py-6 max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-2">
                <i class="bi bi-megaphone text-indigo-500"></i> Job Postings
            </h1>
            <nav class="flex items-center gap-1.5 text-xs text-slate-400 mt-1">
                <a href="{{ route('home') }}" class="hover:text-indigo-600 transition">Home</a>
                <i class="bi bi-chevron-right text-[10px]"></i>
                <a href="{{ route('staff.index') }}" class="hover:text-indigo-600 transition">HR & Staff</a>
                <i class="bi bi-chevron-right text-[10px]"></i>
                <span class="text-slate-600 dark:text-slate-300 font-medium">Recruitment</span>
            </nav>
        </div>
        <button onclick="openPostingModal()"
                class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition flex items-center gap-1.5 self-start">
            <i class="bi bi-plus-circle"></i> New Posting
        </button>
    </div>

    @include('session-messages')

    {{-- Filter --}}
    <form method="GET" action="{{ route('staff.recruitment.postings') }}" class="mb-5 flex gap-2">
        <select name="status" onchange="this.form.submit()"
                class="text-xs px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 outline-none focus:border-indigo-500">
            <option value="">All Status</option>
            <option value="draft"     @selected(request('status')==='draft')>Draft</option>
            <option value="published" @selected(request('status')==='published')>Published</option>
            <option value="closed"    @selected(request('status')==='closed')>Closed</option>
        </select>
    </form>

    {{-- Job Posting Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        @forelse($postings as $p)
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-5 flex flex-col gap-3">
            <div class="flex items-start justify-between gap-2">
                <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm leading-tight">{{ $p->title }}</h3>
                {!! $p->status_badge !!}
            </div>
            @if($p->department)
            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 self-start">{{ $p->department }}</span>
            @endif
            <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                <span><i class="bi bi-people me-1"></i>{{ $p->vacancies }} vacancies</span>
                <span><i class="bi bi-file-earmark-text me-1"></i>{{ $p->applications_count ?? 0 }} applications</span>
                @if($p->salary_min || $p->salary_max)
                <span><i class="bi bi-cash me-1"></i>Rs. {{ number_format($p->salary_min ?? 0, 0) }} – Rs. {{ number_format($p->salary_max ?? 0, 0) }}</span>
                @endif
                @if($p->application_deadline)
                <span class="{{ $p->application_deadline->isPast() ? 'text-rose-500' : '' }}">
                    <i class="bi bi-calendar me-1"></i>Deadline: {{ $p->application_deadline->format('d M Y') }}
                </span>
                @endif
            </div>
            <div class="flex gap-2 pt-2 border-t border-slate-100 dark:border-slate-800 mt-auto">
                <a href="{{ route('staff.recruitment.applications', $p->id) }}"
                   class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400 hover:bg-indigo-100 transition flex items-center gap-1">
                    <i class="bi bi-people"></i> Applications
                </a>
                <button onclick='openEditModal(@json($p))'
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 transition flex items-center gap-1">
                    <i class="bi bi-pencil"></i> Edit
                </button>
                <form method="POST" action="{{ route('staff.recruitment.postings.destroy', $p->id) }}"
                      onsubmit="return confirm('Delete this posting?')" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 hover:bg-rose-100 transition flex items-center gap-1">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-12 text-center">
            <i class="bi bi-megaphone text-5xl text-slate-200 dark:text-slate-700 block mb-3"></i>
            <p class="font-semibold text-slate-500 dark:text-slate-400">No job postings yet.</p>
            <p class="text-xs text-slate-400 mt-1">Create a new posting to start receiving applications.</p>
            <button onclick="openPostingModal()"
                    class="mt-4 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl transition inline-flex items-center gap-1.5">
                <i class="bi bi-plus-circle"></i> Create First Posting
            </button>
        </div>
        @endforelse
    </div>

    <div>{{ $postings->links() }}</div>

    @include('layouts.footer')
</div>

{{-- Create / Edit Posting Modal --}}
<div id="postingModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9999] items-center justify-center p-4 overflow-y-auto"
     onclick="if(event.target===this)closePostingModal()">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-2xl border border-slate-200 dark:border-slate-800 my-8">
        {{-- Header --}}
        <div class="px-6 py-4 bg-indigo-600 flex items-center justify-between rounded-t-2xl">
            <h3 id="postingModalTitle" class="text-white font-bold text-sm flex items-center gap-2">
                <i class="bi bi-plus-circle"></i> Create Job Posting
            </h3>
            <button onclick="closePostingModal()"
                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/20 hover:bg-white/30 text-white font-bold transition">
                &times;
            </button>
        </div>
        {{-- Form --}}
        <form id="postingForm" method="POST" action="{{ route('staff.recruitment.postings.store') }}" class="p-6 bg-white dark:bg-slate-900 rounded-b-2xl">
            @csrf
            <div id="postingMethodField"></div>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Job Title <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" id="pTitle" required placeholder="e.g., Mathematics Teacher"
                           class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Department</label>
                    <input type="text" name="department" id="pDept" placeholder="e.g., Science"
                           class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Vacancies</label>
                    <input type="number" name="vacancies" id="pVacancies" value="1" min="1"
                           class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Status</label>
                    <select name="status" id="pStatus"
                            class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 outline-none">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Application Deadline</label>
                    <input type="date" name="application_deadline" id="pDeadline"
                           class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Salary Min (Rs.)</label>
                    <input type="number" name="salary_min" id="pSalaryMin" step="100" placeholder="0"
                           class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Salary Max (Rs.)</label>
                    <input type="number" name="salary_max" id="pSalaryMax" step="100" placeholder="0"
                           class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Description <span class="text-rose-500">*</span></label>
                    <textarea name="description" id="pDesc" rows="3" required placeholder="Describe the role and responsibilities…"
                              class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none resize-none"></textarea>
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Requirements</label>
                    <textarea name="requirements" id="pReqs" rows="3" placeholder="List qualifications, experience, skills…"
                              class="w-full text-xs px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none resize-none"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800 mt-4">
                <button type="button" onclick="closePostingModal()"
                        class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-lg border border-slate-200 dark:border-slate-700 transition">
                    Cancel
                </button>
                <button type="submit"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                    <i class="bi bi-check-circle me-1"></i> Save Posting
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openPostingModal() {
    document.getElementById('postingModalTitle').innerHTML = '<i class="bi bi-plus-circle"></i> Create Job Posting';
    document.getElementById('postingForm').action = '{{ route("staff.recruitment.postings.store") }}';
    document.getElementById('postingMethodField').innerHTML = '';
    document.getElementById('postingForm').reset();
    const m = document.getElementById('postingModal');
    m.classList.remove('hidden');
    m.classList.add('flex');
}
function closePostingModal() {
    const m = document.getElementById('postingModal');
    m.classList.add('hidden');
    m.classList.remove('flex');
}
function openEditModal(p) {
    document.getElementById('postingModalTitle').innerHTML = '<i class="bi bi-pencil"></i> Edit Job Posting';
    document.getElementById('postingForm').action = '/staff/recruitment/postings/' + p.id;
    document.getElementById('postingMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('pTitle').value      = p.title || '';
    document.getElementById('pDept').value       = p.department || '';
    document.getElementById('pVacancies').value  = p.vacancies || 1;
    document.getElementById('pStatus').value     = p.status || 'draft';
    document.getElementById('pDeadline').value   = p.application_deadline ? p.application_deadline.substring(0,10) : '';
    document.getElementById('pSalaryMin').value  = p.salary_min || '';
    document.getElementById('pSalaryMax').value  = p.salary_max || '';
    document.getElementById('pDesc').value       = p.description || '';
    document.getElementById('pReqs').value       = p.requirements || '';
    const m = document.getElementById('postingModal');
    m.classList.remove('hidden');
    m.classList.add('flex');
}
document.addEventListener('keydown', e => { if(e.key === 'Escape') closePostingModal(); });

@if($errors->any())
document.addEventListener('DOMContentLoaded', () => openPostingModal());
@endif
</script>
@endpush
