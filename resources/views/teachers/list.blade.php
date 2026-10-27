@extends('layouts.app')
@section('content')
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Teachers</h1>
                <p class="text-slate-400 text-sm mt-0.5">{{ $teachers->count() }} faculty members</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="openTeacherBulkIdModal()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 text-sm font-medium rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/60 transition cursor-pointer"><i class="bi bi-person-vcard"></i> Bulk ID Cards</button>
                <a href="{{ route('departments.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition text-slate-700 dark:text-slate-200"><i class="bi bi-diagram-3"></i> Departments</a>
                <a href="{{ route('teacher.attendance.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition text-slate-700 dark:text-slate-200"><i class="bi bi-calendar-check"></i> Attendance</a>
                <a href="{{ route('teacher.payroll.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition text-slate-700 dark:text-slate-200"><i class="bi bi-cash-stack"></i> Payroll</a>
                @can('create teachers')
                <a href="{{ route('teacher.create.show') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition"><i class="bi bi-person-plus"></i> Add Teacher</a>
                @endcan
            </div>
        </div>

        @if(session('status'))
        <div class="mb-5 p-3 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 rounded-xl text-sm"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>
        @endif

        {{-- Search & department filter --}}
        <form method="GET" action="{{ route('teacher.list.show') }}" class="flex flex-wrap gap-2 mb-6">
            <div class="relative flex-1 min-w-48">
                <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email…" class="w-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <select name="department_id" class="border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">All Departments</option>
                @foreach(\App\Models\Department::where('is_active',true)->get() as $dept)
                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-lg text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition flex items-center gap-1.5 cursor-pointer">
                <i class="bi bi-search"></i> Filter
            </button>
            @if(request('search') || request('department_id'))
            <a href="{{ route('teacher.list.show') }}" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 rounded-lg text-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition">Clear</a>
            @endif
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @forelse($teachers as $t)
            <div class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-xl hover:border-indigo-200 dark:hover:border-indigo-800/80 transition-all duration-300 flex flex-col justify-between overflow-hidden">
                
                {{-- Card Top Content --}}
                <div class="p-5 sm:p-6 flex-1">
                    {{-- Header: Avatar + Name + Role + Department --}}
                    <div class="flex items-start gap-3.5 mb-4">
                        <div class="relative flex-shrink-0">
                            <img src="{{ $t->avatar }}" 
                                 class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl object-cover ring-2 ring-slate-100 dark:ring-slate-800 shadow-sm group-hover:scale-105 transition-transform duration-200" 
                                 alt="{{ $t->full_name }}">
                            <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-emerald-500 border-2 border-white dark:border-slate-900 rounded-full" title="Active Staff"></span>
                        </div>
                        <div class="flex-1 min-w-0 pt-0.5">
                            <h3 class="font-bold text-slate-900 dark:text-slate-100 text-base leading-tight truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors" title="{{ $t->full_name }}">
                                {{ $t->full_name }}
                            </h3>
                            <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 capitalize tracking-wide">
                                    {{ str_replace('-',' ',$t->primary_role) }}
                                </span>
                                @if($t->departments->count())
                                    @foreach($t->departments->take(1) as $d)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 truncate max-w-[120px]">
                                        {{ $d->name }}
                                    </span>
                                    @endforeach
                                    @if($t->departments->count() > 1)
                                    <span class="text-[10px] text-slate-400 font-medium">+{{ $t->departments->count() - 1 }}</span>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Contact & Detail Badges --}}
                    <div class="space-y-2.5 pt-3 border-t border-slate-100 dark:border-slate-800/80">
                        {{-- Email Row --}}
                        <div class="flex items-center gap-2.5 text-xs text-slate-600 dark:text-slate-300">
                            <div class="w-6 h-6 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 flex-shrink-0">
                                <i class="bi bi-envelope text-xs"></i>
                            </div>
                            <span class="truncate font-medium" title="{{ $t->email }}">{{ $t->email }}</span>
                        </div>

                        {{-- Phone & Assigned Courses Row --}}
                        <div class="flex items-center justify-between gap-2 pt-0.5 text-xs">
                            <div class="flex items-center gap-2 text-slate-600 dark:text-slate-300 min-w-0">
                                <div class="w-6 h-6 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 flex-shrink-0">
                                    <i class="bi bi-telephone text-xs"></i>
                                </div>
                                <span class="truncate font-medium">{{ $t->phone ?: 'No phone added' }}</span>
                            </div>
                            
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 flex-shrink-0 border border-indigo-100/60 dark:border-indigo-900/40">
                                <i class="bi bi-journal-check text-xs"></i>
                                {{ $t->assignedCourses->count() }} {{ Str::plural('course', $t->assignedCourses->count()) }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Card Footer Action Buttons --}}
                <div class="px-5 py-3.5 bg-slate-50/70 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-800 flex items-center gap-2">
                    <a href="{{ route('teacher.profile.show', $t->id) }}" 
                       class="flex-1 inline-flex items-center justify-center gap-1.5 py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-sm hover:shadow transition">
                        <i class="bi bi-person"></i>
                        <span>Profile</span>
                    </a>
                    
                    <a href="{{ route('teacher.id-card', $t->id) }}" target="_blank" 
                       class="flex-1 inline-flex items-center justify-center gap-1.5 py-2 px-3 bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold transition" 
                       title="Print ID Card">
                        <i class="bi bi-person-vcard text-indigo-600 dark:text-indigo-400"></i>
                        <span>ID Card</span>
                    </a>

                    @can('manage roles')
                    <a href="{{ route('roles.user-roles', $t->id) }}" 
                       class="w-8 h-8 inline-flex items-center justify-center rounded-xl bg-white dark:bg-slate-800 hover:bg-emerald-50 hover:text-emerald-700 dark:hover:bg-emerald-950/60 dark:hover:text-emerald-300 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700 text-xs transition" 
                       title="Assign Roles">
                        <i class="bi bi-person-check"></i>
                    </a>
                    @endcan

                    @can('create teachers')
                    <a href="{{ route('teacher.edit.show', $t->id) }}" 
                       class="w-8 h-8 inline-flex items-center justify-center rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 border border-slate-200 dark:border-slate-700 text-xs transition" 
                       title="Edit Teacher">
                        <i class="bi bi-pencil"></i>
                    </a>
                    @endcan
                </div>

            </div>
            @empty
            <div class="col-span-full bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-12 text-center">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-3 text-slate-400">
                    <i class="bi bi-person-badge text-3xl"></i>
                </div>
                <h4 class="font-bold text-slate-800 dark:text-slate-200 text-base">No Teachers Found</h4>
                <p class="text-sm text-slate-400 mt-1">Try changing your search or department filter criteria.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ── Bulk Faculty ID Card Export Modal ─────────────────────────────── --}}
<div id="teacherBulkIdModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden transform transition-all">
        
        {{-- Modal Header --}}
        <div class="bg-gradient-to-r from-slate-900 to-indigo-950 p-6 text-white flex items-center justify-between border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center text-indigo-400">
                    <i class="bi bi-person-vcard text-xl"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-lg tracking-tight">Bulk Faculty ID Cards</h3>
                    <p class="text-xs text-slate-400">Export teacher & staff identification cards</p>
                </div>
            </div>
            <button type="button" onclick="closeTeacherBulkIdModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition cursor-pointer">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>

        {{-- Modal Body / Form --}}
        <form id="teacherBulkIdForm" action="{{ route('teacher.id-cards.bulk') }}" method="GET" target="_blank" class="p-6 space-y-5">
            
            {{-- Format Selector (PDF vs Word) --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">
                    1. Select Export Format
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="t-format-card relative flex flex-col items-center p-4 rounded-2xl border-2 border-indigo-600 bg-indigo-50/50 dark:bg-indigo-950/30 cursor-pointer transition">
                        <input type="radio" name="format" value="pdf" checked class="sr-only" onchange="updateTeacherFormat(this)">
                        <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center mb-2">
                            <i class="bi bi-file-earmark-pdf-fill text-xl"></i>
                        </div>
                        <span class="font-bold text-sm text-slate-800 dark:text-slate-100">PDF Document</span>
                        <span class="text-[11px] text-slate-400 text-center mt-0.5">Printable A4 grid</span>
                        <div class="t-active-badge absolute top-2 right-2 w-4 h-4 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[10px]">
                            <i class="bi bi-check"></i>
                        </div>
                    </label>

                    <label class="t-format-card relative flex flex-col items-center p-4 rounded-2xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 cursor-pointer transition">
                        <input type="radio" name="format" value="docx" class="sr-only" onchange="updateTeacherFormat(this)">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center mb-2">
                            <i class="bi bi-file-earmark-word-fill text-xl"></i>
                        </div>
                        <span class="font-bold text-sm text-slate-800 dark:text-slate-100">Word (.docx)</span>
                        <span class="text-[11px] text-slate-400 text-center mt-0.5">Editable Microsoft Word</span>
                        <div class="t-active-badge hidden absolute top-2 right-2 w-4 h-4 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[10px]">
                            <i class="bi bi-check"></i>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Layout Selector --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">
                    2. Choose Card Layout
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="t-layout-card flex items-center gap-3 p-3 rounded-xl border-2 border-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/20 cursor-pointer transition">
                        <input type="radio" name="layout" value="dualsided" checked class="sr-only" onchange="updateTeacherLayout(this)">
                        <i class="bi bi-front text-indigo-600 text-lg"></i>
                        <div>
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200">Dual Sided</p>
                            <p class="text-[10px] text-slate-400">Front + Back Pair</p>
                        </div>
                    </label>
                    <label class="t-layout-card flex items-center gap-3 p-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 cursor-pointer transition">
                        <input type="radio" name="layout" value="front" class="sr-only" onchange="updateTeacherLayout(this)">
                        <i class="bi bi-grid-fill text-slate-500 text-lg"></i>
                        <div>
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200">Front Only Grid</p>
                            <p class="text-[10px] text-slate-400">8 cards per A4 page</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Department Filter --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Department</label>
                    <select name="department_id" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">All Departments</option>
                        @foreach(\App\Models\Department::where('is_active',true)->get() as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Max Count</label>
                    <select name="limit" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="50" selected>50 Faculty</option>
                        <option value="100">100 Faculty</option>
                        <option value="200">200 Faculty</option>
                    </select>
                </div>
            </div>

            {{-- Modal Action Buttons --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="button" onclick="submitTeacherBulk('docx')" class="flex-1 py-2.5 px-4 bg-blue-50 dark:bg-blue-950/60 hover:bg-blue-100 dark:hover:bg-blue-900/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition cursor-pointer">
                    <i class="bi bi-file-earmark-word text-sm"></i> Download Word
                </button>
                <button type="button" onclick="submitTeacherBulk('pdf')" class="flex-1 py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/30 transition cursor-pointer">
                    <i class="bi bi-file-earmark-pdf text-sm"></i> Generate PDF
                </button>
            </div>

        </form>
    </div>
</div>

@push('scripts')
<script>
function openTeacherBulkIdModal() {
    var modal = document.getElementById('teacherBulkIdModal');
    if (modal) modal.classList.remove('hidden');
}

function closeTeacherBulkIdModal() {
    var modal = document.getElementById('teacherBulkIdModal');
    if (modal) modal.classList.add('hidden');
}

function updateTeacherFormat(radio) {
    document.querySelectorAll('.t-format-card').forEach(function(card) {
        card.classList.remove('border-indigo-600', 'bg-indigo-50/50', 'dark:bg-indigo-950/30');
        card.classList.add('border-slate-200', 'dark:border-slate-700', 'bg-white', 'dark:bg-slate-800');
        var b = card.querySelector('.t-active-badge');
        if (b) b.classList.add('hidden');
    });
    var parent = radio.closest('.t-format-card');
    if (parent) {
        parent.classList.add('border-indigo-600', 'bg-indigo-50/50', 'dark:bg-indigo-950/30');
        parent.classList.remove('border-slate-200', 'dark:border-slate-700', 'bg-white', 'dark:bg-slate-800');
        var b = parent.querySelector('.t-active-badge');
        if (b) b.classList.remove('hidden');
    }
}

function updateTeacherLayout(radio) {
    document.querySelectorAll('.t-layout-card').forEach(function(card) {
        card.classList.remove('border-indigo-600', 'bg-indigo-50/30', 'dark:bg-indigo-950/20');
        card.classList.add('border-slate-200', 'dark:border-slate-700', 'bg-white', 'dark:bg-slate-800');
    });
    var parent = radio.closest('.t-layout-card');
    if (parent) {
        parent.classList.add('border-indigo-600', 'bg-indigo-50/30', 'dark:bg-indigo-950/20');
        parent.classList.remove('border-slate-200', 'dark:border-slate-700', 'bg-white', 'dark:bg-slate-800');
    }
}

function submitTeacherBulk(format) {
    var form = document.getElementById('teacherBulkIdForm');
    if (form) {
        var radio = form.querySelector('input[name="format"][value="' + format + '"]');
        if (radio) {
            radio.checked = true;
            updateTeacherFormat(radio);
        }
        form.submit();
        setTimeout(closeTeacherBulkIdModal, 600);
    }
}
</script>
@endpush
@endsection
