@extends('layouts.app')
@section('title', 'Google Education Workspace Plugin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    {{-- Header Banner --}}
    <div class="bg-gradient-to-r from-indigo-900 via-indigo-800 to-slate-900 rounded-3xl p-6 md:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute right-0 top-0 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-indigo-200 backdrop-blur-md">
                    <i class="bi bi-google"></i> Google Workspace for Education
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">
                    Google Education Plugin Hub
                </h1>
                <p class="text-sm text-indigo-200 max-w-xl">
                    Unified integration syncing your classes, subjects, students, and teachers directly with Google Classroom and Google Workspace.
                </p>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                @if($config && $config->connection_status === 'connected')
                    <a href="https://classroom.google.com" target="_blank" class="px-4 py-2.5 rounded-2xl bg-white text-indigo-900 hover:bg-indigo-50 font-bold text-xs shadow-md transition flex items-center gap-2">
                        <i class="bi bi-box-arrow-up-right"></i> Open Google Classroom
                    </a>
                    <form method="POST" action="{{ route('school.google.sync-rosters') }}">
                        @csrf
                        <button type="submit" class="px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md transition flex items-center gap-2">
                            <i class="bi bi-arrow-repeat"></i> Sync Rosters Now
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    @include('session-messages')

    @if(!$config || !$config->hasValidCredentials())
    {{-- Not Configured Banner --}}
    <div class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 rounded-3xl p-6 text-amber-900 dark:text-amber-200">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-2xl bg-amber-100 dark:bg-amber-900/50 text-amber-600 flex items-center justify-center shrink-0">
                <i class="bi bi-exclamation-triangle-fill text-xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-sm">Google Workspace Integration Not Configured</h3>
                <p class="text-xs text-amber-700 dark:text-amber-300 mt-1">
                    Your school has not yet linked a Google Workspace for Education domain or credentials.
                    Please contact the Super Administrator to configure your school's Google Service Account key and domain.
                </p>
            </div>
        </div>
    </div>
    @else

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Classrooms</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 flex items-center justify-center">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ $stats['total_classrooms'] }}</div>
            <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium">Active courses</span>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Students Enrolled</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 flex items-center justify-center">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ $stats['total_students'] }}</div>
            <span class="text-[11px] text-slate-400 font-medium">In Google Classrooms</span>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Teachers Assigned</span>
                <div class="w-8 h-8 rounded-xl bg-violet-50 dark:bg-violet-950/40 text-violet-600 flex items-center justify-center">
                    <i class="bi bi-person-badge-fill"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ $stats['total_teachers'] }}</div>
            <span class="text-[11px] text-slate-400 font-medium">Co-teachers &amp; owners</span>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-800 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Domain Status</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 flex items-center justify-center">
                    <i class="bi bi-shield-check"></i>
                </div>
            </div>
            <div class="text-xs font-bold text-slate-800 dark:text-slate-100 font-mono truncate">{{ $config->workspace_domain }}</div>
            <span class="text-[11px] text-slate-400 font-medium">Last sync: {{ $config->last_synced_at?->diffForHumans() ?? 'Never' }}</span>
        </div>
    </div>

    {{-- Main Active Classrooms Grid --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6 space-y-4">
        <div class="flex items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <span class="w-2.5 h-6 bg-indigo-600 rounded-full inline-block"></span>
                    <i class="bi bi-mortarboard text-indigo-500"></i> Synchronized Google Classrooms
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Click any classroom card to open it directly in Google Classroom
                </p>
            </div>

            <form method="POST" action="{{ route('school.google.test') }}">
                @csrf
                <button type="submit" class="text-xs px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-1.5 transition">
                    <i class="bi bi-activity text-emerald-500"></i> Test Connection Health
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($classrooms as $cls)
            <div class="p-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 hover:border-indigo-300 dark:hover:border-indigo-700 transition flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300">
                            {{ $cls->schoolClass?->class_name ?? 'Class' }} - Sec {{ $cls->section_name ?: 'A' }}
                        </span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    </div>

                    <h4 class="font-bold text-sm text-slate-800 dark:text-slate-100 line-clamp-1 mb-1">
                        {{ $cls->course?->course_name ?? $cls->name }}
                    </h4>
                    <p class="text-xs text-slate-400 line-clamp-1 mb-3">
                        Teacher: {{ $cls->teacherRosters->first()?->user?->name ?? 'Assigned Teacher' }}
                    </p>
                </div>

                <div class="pt-3 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between text-xs">
                    @if($cls->enrollment_code)
                    <div class="font-mono text-[11px] text-slate-600 dark:text-slate-400">
                        Code: <strong class="text-slate-800 dark:text-slate-200 select-all">{{ $cls->enrollment_code }}</strong>
                    </div>
                    @else
                    <span class="text-slate-400 text-[11px]">Synced</span>
                    @endif

                    <div class="flex items-center gap-1.5">
                        <button type="button" onclick="loadClassroomStream('{{ $cls->google_course_id }}', '{{ addslashes($cls->name) }}')" class="inline-flex items-center gap-1 text-[11px] px-2.5 py-1 bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 text-indigo-700 dark:text-indigo-300 rounded-lg font-semibold transition" title="Load live assignments & stream directly from Google Classroom">
                            <i class="bi bi-cloud-arrow-down-fill text-indigo-500"></i> Load Stream
                        </button>
                        @if($cls->alternate_link)
                        <a href="{{ $cls->alternate_link }}" target="_blank" class="p-1 text-slate-400 hover:text-indigo-600 rounded-lg transition" title="Open Google Classroom in new tab">
                            <i class="bi bi-box-arrow-up-right text-xs"></i>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full py-12 text-center text-slate-400">
                <i class="bi bi-mortarboard text-4xl text-slate-300 mb-2 block"></i>
                <p class="font-semibold text-sm">No Google Classrooms provisioned yet.</p>
                <p class="text-xs text-slate-400 mt-1">Run "Sync Rosters Now" to create and map your subjects.</p>
            </div>
            @endforelse
        </div>

        @if($classrooms->hasPages())
        <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
            {{ $classrooms->links() }}
        </div>
        @endif
    </div>

    @endif

    {{-- Live Google Classroom Stream Modal --}}
    <div id="googleStreamModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-2xl w-full max-h-[85vh] shadow-2xl border border-slate-200 dark:border-slate-800 flex flex-col overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm font-bold">
                        <i class="bi bi-google"></i>
                    </span>
                    <div>
                        <h3 id="modalStreamTitle" class="text-sm font-bold text-slate-800 dark:text-slate-100">Live Google Stream</h3>
                        <p class="text-[11px] text-slate-400">Loading live data directly from Google Workspace Cloud</p>
                    </div>
                </div>
                <button type="button" onclick="closeStreamModal()" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-700 flex items-center justify-center text-sm">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div id="modalStreamContent" class="p-6 overflow-y-auto space-y-4 flex-1">
                <div class="flex items-center justify-center py-12">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                </div>
            </div>

            <div class="px-6 py-3 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center text-xs">
                <span class="text-slate-400 inline-flex items-center gap-1">
                    <i class="bi bi-shield-check text-emerald-500"></i> Synced via Google Classroom API
                </span>
                <button type="button" onclick="closeStreamModal()" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-700 dark:text-slate-200 font-semibold rounded-xl transition">
                    Close
                </button>
            </div>
        </div>
    </div>

</div>

<script>
function closeStreamModal() {
    document.getElementById('googleStreamModal').classList.add('hidden');
}

function loadClassroomStream(googleCourseId, courseName) {
    const modal = document.getElementById('googleStreamModal');
    const title = document.getElementById('modalStreamTitle');
    const container = document.getElementById('modalStreamContent');

    title.innerText = courseName || 'Google Classroom Stream';
    container.innerHTML = `
        <div class="flex flex-col items-center justify-center py-12 text-slate-400">
            <div class="animate-spin rounded-full h-8 w-8 border-2 border-indigo-600 border-t-transparent mb-3"></div>
            <p class="text-xs font-semibold">Connecting to Google Workspace Cloud API...</p>
        </div>
    `;
    modal.classList.remove('hidden');

    fetch(`/school/google-workspace/classroom/${encodeURIComponent(googleCourseId)}/stream`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (!data || (!data.coursework?.length && !data.announcements?.length)) {
            container.innerHTML = `
                <div class="text-center py-10 text-slate-400">
                    <i class="bi bi-folder2-open text-4xl mb-2 block text-slate-300"></i>
                    <p class="font-bold text-sm text-slate-600 dark:text-slate-300">No Assignments or Coursework Posted Yet</p>
                    <p class="text-xs text-slate-400 mt-1">Teachers can create assignments in Google Classroom; they will instantly load here in real time.</p>
                </div>
            `;
            return;
        }

        let html = '';

        if (data.coursework?.length) {
            html += `<h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Assignments & CourseWork (${data.coursework.length})</h4><div class="space-y-3">`;
            data.coursework.forEach(cw => {
                html += `
                    <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-xs">
                        <div class="flex items-start justify-between gap-2">
                            <h5 class="text-sm font-bold text-slate-800 dark:text-slate-100">${cw.title}</h5>
                            ${cw.due_date ? `<span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">Due: ${cw.due_date}</span>` : ''}
                        </div>
                        ${cw.description ? `<p class="text-xs text-slate-500 mt-1 line-clamp-2">${cw.description}</p>` : ''}
                        <div class="mt-3 flex items-center justify-between text-xs pt-2 border-t border-slate-100 dark:border-slate-700">
                            <span class="text-[11px] text-slate-400">${cw.max_points ? cw.max_points + ' points' : 'Ungraded'}</span>
                            <a href="${cw.alternate_link}" target="_blank" class="text-indigo-600 font-semibold hover:underline inline-flex items-center gap-1 text-[11px]">
                                View Submissions on Google <i class="bi bi-box-arrow-up-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                `;
            });
            html += `</div>`;
        }

        if (data.announcements?.length) {
            html += `<h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-6 mb-2">Class Announcements (${data.announcements.length})</h4><div class="space-y-2">`;
            data.announcements.forEach(ann => {
                html += `
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/60 text-xs">
                        <p class="text-slate-700 dark:text-slate-300 whitespace-pre-line">${ann.text}</p>
                    </div>
                `;
            });
            html += `</div>`;
        }

        container.innerHTML = html;
    })
    .catch(err => {
        container.innerHTML = `
            <div class="text-center py-8 text-rose-500">
                <i class="bi bi-exclamation-triangle text-3xl mb-2 block"></i>
                <p class="text-xs font-semibold">Failed to fetch data from Google Classroom API.</p>
                <p class="text-[11px] text-slate-400 mt-1">${err.message || 'Please check your internet connection or Google credentials.'}</p>
            </div>
        `;
    });
}
</script>
@endsection

