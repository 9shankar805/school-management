@extends('layouts.app')
@section('title', 'Class In-Charge Command Center')

@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">

    {{-- Breadcrumb --}}
    <nav class="text-xs text-slate-400 mb-5 flex items-center gap-1.5">
        <a href="{{ route('home') }}" class="hover:text-indigo-600">Home</a>
        <i class="bi bi-chevron-right text-[9px]"></i>
        <span class="text-slate-600 font-medium">Class In-Charge</span>
        <i class="bi bi-chevron-right text-[9px]"></i>
        <span class="text-slate-800 font-bold">{{ $assignment->schoolClass?->class_name }} — Section {{ preg_replace('/^Section\s+/i', '', $assignment->section?->section_name ?? 'A') }}</span>
    </nav>

    @if(session('status'))
    <div class="mb-5 flex items-center gap-2 p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl text-sm shadow-sm">
        <i class="bi bi-check-circle-fill text-emerald-500"></i> {{ session('status') }}
    </div>
    @endif

    {{-- ── Hero Section ── --}}
    <div class="bg-gradient-to-br from-amber-600 via-orange-600 to-amber-700 rounded-3xl p-6 lg:p-8 mb-7 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 opacity-10 text-white pointer-events-none">
            <i class="bi bi-star-fill text-[240px]"></i>
        </div>
        <div class="relative z-10 flex flex-wrap items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 rounded-2xl bg-white/20 border border-white/30 backdrop-blur flex items-center justify-center text-3xl font-black shadow-inner">
                    <i class="bi bi-person-workspace"></i>
                </div>
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-400/30 text-amber-100 text-xs font-extrabold uppercase tracking-wider mb-1.5 border border-amber-300/30">
                        <i class="bi bi-star-fill text-amber-300"></i> Class In-Charge (कक्षा शिक्षक)
                    </span>
                    <h1 class="text-2xl lg:text-3xl font-extrabold tracking-tight">
                        {{ $assignment->schoolClass?->class_name }} — Section {{ preg_replace('/^Section\s+/i', '', $assignment->section?->section_name ?? 'A') }}
                    </h1>
                    <p class="text-amber-100 text-xs mt-1 flex items-center gap-3">
                        <span><i class="bi bi-people-fill me-1"></i>{{ $students->count() }} Enrolled Students</span>
                        <span><i class="bi bi-journal-bookmark-fill me-1"></i>{{ $subjectTeachers->count() }} Subject Teachers</span>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('attendance.create.show', ['class_id' => $assignment->class_id, 'section_id' => $assignment->section_id]) }}" class="px-4 py-2.5 bg-white text-amber-800 hover:bg-amber-50 rounded-xl text-xs font-bold transition shadow-md flex items-center gap-1.5">
                    <i class="bi bi-check2-square text-sm"></i> Take Morning Roll-Call
                </a>
                <a href="{{ route('class-incharge.gradebook') }}" class="px-4 py-2.5 bg-white/20 hover:bg-white/30 border border-white/30 text-white rounded-xl text-xs font-bold transition backdrop-blur flex items-center gap-1.5">
                    <i class="bi bi-card-checklist text-sm"></i> Terminal Marksheet & Remarks
                </a>
                <button type="button" onclick="document.getElementById('diaryModal').classList.remove('hidden')" class="px-4 py-2.5 bg-white/20 hover:bg-white/30 border border-white/30 text-white rounded-xl text-xs font-bold transition backdrop-blur flex items-center gap-1.5">
                    <i class="bi bi-megaphone-fill text-sm"></i> Post Diary / Circular
                </button>
            </div>
        </div>
    </div>

    {{-- ── Stats KPI Row ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Enrolled</span>
                <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold"><i class="bi bi-people"></i></span>
            </div>
            <p class="text-2xl font-extrabold text-slate-800 dark:text-slate-100">{{ $students->count() }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Active students in section</p>
        </div>
        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Today's Present</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold"><i class="bi bi-person-check-fill"></i></span>
            </div>
            <p class="text-2xl font-extrabold text-emerald-600">{{ $presentCount }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">{{ $students->count() > 0 ? round(($presentCount / max($students->count(), 1)) * 100) : 0 }}% attendance rate today</p>
        </div>
        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Today's Absent</span>
                <span class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-sm font-bold"><i class="bi bi-person-x-fill"></i></span>
            </div>
            <p class="text-2xl font-extrabold text-rose-600">{{ $absentCount }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Automated SMS sent to parents</p>
        </div>
        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pending Leaves</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-bold"><i class="bi bi-envelope-open-heart"></i></span>
            </div>
            <p class="text-2xl font-extrabold text-amber-600">{{ $pendingLeaves->count() }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Parent leave applications</p>
        </div>
    </div>

    {{-- ── 2-Column Main Workspace ── --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Left 2 Columns: Student Roster & Quick Actions --}}
        <div class="xl:col-span-2 space-y-6">

            {{-- Student Roster Table --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                            <i class="bi bi-people-fill text-indigo-500"></i> Students & Roll Numbers
                        </h3>
                        <p class="text-[11px] text-slate-400">Section {{ preg_replace('/^Section\s+/i', '', $assignment->section?->section_name ?? 'A') }} official register</p>
                    </div>
                    <a href="{{ route('class-incharge.students') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                        View Full Dossier & Parent Contacts →
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-400 uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3 text-left font-bold w-12">Roll</th>
                                <th class="px-4 py-3 text-left font-bold">Student Name</th>
                                <th class="px-4 py-3 text-left font-bold">Admission No</th>
                                <th class="px-4 py-3 text-left font-bold">Gender</th>
                                <th class="px-4 py-3 text-left font-bold">Parent Contact</th>
                                <th class="px-4 py-3 text-right font-bold">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse($students->take(12) as $p)
                            @php $stu = $p->student; @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40 transition">
                                <td class="px-4 py-2.5 font-bold text-slate-700 dark:text-slate-200">#{{ $p->roll_no ?? '—' }}</td>
                                <td class="px-4 py-2.5 font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                    <img src="{{ $stu?->avatar }}" alt="" class="w-7 h-7 rounded-full object-cover border border-slate-200">
                                    <span>{{ $stu?->full_name }}</span>
                                </td>
                                <td class="px-4 py-2.5 font-mono text-slate-500">{{ $stu?->academic_info?->admission_no ?? $p->id_card_number ?? '—' }}</td>
                                <td class="px-4 py-2.5 capitalize text-slate-600 dark:text-slate-300">{{ $stu?->gender ?? '—' }}</td>
                                <td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">
                                    @if($stu?->phone || $stu?->parent?->phone)
                                    <a href="tel:{{ $stu?->phone ?? $stu?->parent?->phone }}" class="text-indigo-600 hover:underline">
                                        <i class="bi bi-telephone me-0.5"></i>{{ $stu?->phone ?? $stu?->parent?->phone }}
                                    </a>
                                    @else
                                    <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-right">
                                    <a href="{{ route('student.profile.show', $stu?->id) }}" class="text-indigo-600 font-bold hover:underline">
                                        Profile →
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-400">No students enrolled in this section.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pending Leave Applications --}}
            @if($pendingLeaves->count())
            <div class="bg-amber-50 dark:bg-amber-950/30 rounded-2xl border border-amber-200 dark:border-amber-800 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-extrabold text-amber-900 dark:text-amber-200 flex items-center gap-2">
                        <i class="bi bi-clock-history text-amber-600"></i> Pending Student Leave Applications ({{ $pendingLeaves->count() }})
                    </h3>
                </div>
                <div class="space-y-3">
                    @foreach($pendingLeaves as $leave)
                    <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-amber-200/70 dark:border-slate-700 flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-100">{{ $leave->user?->full_name }}</p>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                {{ $leave->start_date?->format('d M Y') }} to {{ $leave->end_date?->format('d M Y') }} &middot; 
                                <span class="font-medium text-amber-700 dark:text-amber-400">{{ $leave->reason }}</span>
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('class-incharge.leaves.action', $leave->id) }}">
                                @csrf
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-sm transition">
                                    Approve
                                </button>
                            </form>
                            <form method="POST" action="{{ route('class-incharge.leaves.action', $leave->id) }}">
                                @csrf
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg text-xs font-bold transition">
                                    Reject
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        {{-- Right Column: Subject Teachers & Class Diary --}}
        <div class="space-y-6">

            {{-- Subject Teachers Roster --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-5">
                <h3 class="text-xs font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="bi bi-mortarboard-fill text-indigo-500"></i> Subject Teachers
                </h3>
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($subjectTeachers as $st)
                    <div class="py-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <img src="{{ $st->teacher?->avatar }}" alt="" class="w-8 h-8 rounded-xl object-cover border border-slate-200 flex-shrink-0">
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate">{{ $st->teacher?->full_name }}</p>
                                <p class="text-[10px] text-slate-400 truncate">{{ $st->course?->course_name ?? 'Subject' }}</p>
                            </div>
                        </div>
                        @if($st->is_class_teacher)
                        <span class="text-[10px] bg-amber-100 text-amber-800 font-extrabold px-2 py-0.5 rounded-full flex-shrink-0">In-Charge</span>
                        @endif
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 text-center py-4">No subject teachers assigned yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Class Diary Announcements --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xs font-extrabold text-slate-800 dark:text-slate-100 uppercase tracking-wider flex items-center gap-2">
                        <i class="bi bi-book-half text-amber-500"></i> Class Diary Circulars
                    </h3>
                </div>
                <div class="space-y-3">
                    @forelse($classNotices as $cn)
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-100 dark:border-slate-600">
                        <p class="text-xs font-bold text-slate-800 dark:text-slate-100">{{ $cn->title ?? 'Announcement' }}</p>
                        <p class="text-[11px] text-slate-500 mt-1 line-clamp-2">{{ $cn->notice ?? $cn->description }}</p>
                        <p class="text-[10px] text-slate-400 mt-2"><i class="bi bi-calendar3 me-1"></i>{{ $cn->created_at?->format('M d, Y') ?? $cn->notice_date }}</p>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 text-center py-4">No announcements posted yet.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>

{{-- ── Post Diary Modal ── --}}
<div id="diaryModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-150">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between bg-gradient-to-r from-amber-50 to-orange-50 dark:from-slate-800 dark:to-slate-800">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="bi bi-megaphone-fill text-amber-500"></i> Post Class Diary / Circular
            </h3>
            <button type="button" onclick="document.getElementById('diaryModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('class-incharge.diary') }}" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5 uppercase">Title / Heading</label>
                <input type="text" name="title" required placeholder="e.g., Tomorrow's PT Dress & Science Project Submission" class="w-full text-xs bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-amber-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 mb-1.5 uppercase">Circular Content / Instructions</label>
                <textarea name="description" rows="4" required placeholder="Write detailed instructions for parents and students..." class="w-full text-xs bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700">
                <button type="button" onclick="document.getElementById('diaryModal').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-amber-500 hover:bg-amber-600 text-white rounded-xl shadow-md transition">
                    Publish to Parents
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
