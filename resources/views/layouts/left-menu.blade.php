<div class="h-full flex flex-col py-4 px-3">

    {{-- User badge --}}
    <div class="flex items-center gap-3 px-2 py-3 mb-4 bg-slate-50 rounded-xl">
        <img src="{{ auth()->user()->avatar }}" class="w-9 h-9 rounded-full object-cover flex-shrink-0" alt="">
        <div class="min-w-0">
            <p class="text-sm font-semibold text-slate-700 truncate">{{ auth()->user()->full_name }}</p>
            <p class="text-[11px] text-slate-400 truncate capitalize">{{ str_replace('-', ' ', auth()->user()->primary_role) }}</p>
        </div>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 space-y-0.5 overflow-y-auto">

        {{-- Dashboard --}}
        <a href="{{ url('home') }}" class="nav-item {{ request()->is('home') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2 nav-icon"></i> Dashboard
        </a>

        {{-- ── STUDENT section ── --}}
        @canany(['view students', 'create students'])
        <p class="nav-section-label">Students</p>
        <a href="{{ route('student.list.show') }}" class="nav-item {{ request()->is('students/view*') ? 'active' : '' }}">
            <i class="bi bi-people nav-icon"></i> All Students
        </a>
        @can('create students')
        @unless(session()->has('browse_session_id'))
        <a href="{{ route('student.create.show') }}" class="nav-item {{ request()->is('students/add') ? 'active' : '' }}">
            <i class="bi bi-person-plus nav-icon"></i> Add Student
        </a>
        @endunless
        @endcan
        @endcanany

        {{-- Student self-view --}}
        @role('student')
        <p class="nav-section-label">My School</p>
        <a href="{{ route('student.attendance.show', auth()->id()) }}" class="nav-item {{ request()->routeIs('student.attendance.show') ? 'active' : '' }}">
            <i class="bi bi-calendar2-check nav-icon"></i> My Attendance
        </a>
        <a href="{{ route('course.student.list.show', auth()->id()) }}" class="nav-item {{ request()->routeIs('course.student.list.show') ? 'active' : '' }}">
            <i class="bi bi-journal-medical nav-icon"></i> My Courses
        </a>
        <a href="{{ route('homework.index') }}" class="nav-item {{ request()->is('homework*') ? 'active' : '' }}">
            <i class="bi bi-pencil-square nav-icon"></i> Homework
        </a>
        <a href="{{ route('projects.index') }}" class="nav-item {{ request()->is('projects*') ? 'active' : '' }}">
            <i class="bi bi-folder2-open nav-icon"></i> Projects
        </a>
        <a href="{{ route('study-notes.index') }}" class="nav-item {{ request()->is('study-notes*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text nav-icon"></i> Study Materials
        </a>
        <a href="{{ route('online-classes.index') }}" class="nav-item {{ request()->is('online-classes*') ? 'active' : '' }}">
            <i class="bi bi-camera-video nav-icon"></i> Online Classes
        </a>
        @if($student_routine_info = \App\Models\Promotion::where('student_id', auth()->id())->latest()->first())
        <a href="{{ route('section.routine.show', ['class_id' => $student_routine_info->class_id, 'section_id' => $student_routine_info->section_id]) }}" class="nav-item">
            <i class="bi bi-calendar4-range nav-icon"></i> Timetable
        </a>
        @endif
        @endrole

        {{-- ── TEACHER section ── --}}
        @canany(['view teachers', 'create teachers'])
        <p class="nav-section-label">Teachers</p>
        <a href="{{ route('teacher.list.show') }}" class="nav-item {{ request()->is('teachers/view/list') ? 'active' : '' }}">
            <i class="bi bi-person-badge nav-icon"></i> All Teachers
        </a>
        @can('create teachers')
        @unless(session()->has('browse_session_id'))
        <a href="{{ route('teacher.create.show') }}" class="nav-item">
            <i class="bi bi-person-plus nav-icon"></i> Add Teacher
        </a>
        @endunless
        @endcan
        @endcanany

        {{-- Teacher self-courses --}}
        @role('teacher|class-teacher')
        <p class="nav-section-label">Teaching</p>
        <a href="{{ route('course.teacher.list.show', ['teacher_id' => auth()->id()]) }}" class="nav-item {{ request()->is('courses/teacher*') ? 'active' : '' }}">
            <i class="bi bi-journal-medical nav-icon"></i> My Courses
        </a>
        <a href="{{ route('assignment.list.show') }}" class="nav-item {{ request()->is('courses/assignments*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text nav-icon"></i> Assignments
        </a>
        <a href="{{ route('lesson-plans.index') }}" class="nav-item {{ request()->is('lesson-plans*') ? 'active' : '' }}">
            <i class="bi bi-journal-plus nav-icon"></i> Lesson Plans
        </a>
        <a href="{{ route('homework.index') }}" class="nav-item {{ request()->is('homework*') ? 'active' : '' }}">
            <i class="bi bi-pencil-square nav-icon"></i> Homework
        </a>
        <a href="{{ route('projects.index') }}" class="nav-item {{ request()->is('projects*') ? 'active' : '' }}">
            <i class="bi bi-folder2-open nav-icon"></i> Projects
        </a>
        <a href="{{ route('study-notes.index') }}" class="nav-item {{ request()->is('study-notes*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text nav-icon"></i> Study Materials
        </a>
        <a href="{{ route('online-classes.index') }}" class="nav-item {{ request()->is('online-classes*') ? 'active' : '' }}">
            <i class="bi bi-camera-video nav-icon"></i> Online Classes
        </a>
        @endrole

        {{-- ── ACADEMIC section ── --}}
        @canany(['view classes', 'view courses', 'view routines', 'view syllabi', 'view academic settings'])
        <p class="nav-section-label">Academic</p>
        @can('view classes')
        <a href="{{ url('classes') }}" class="nav-item {{ request()->is('classes') ? 'active' : '' }}">
            <i class="bi bi-diagram-3 nav-icon"></i> Classes &amp; Sections
        </a>
        @endcan
        @can('view courses')
        <a href="{{ route('course.teacher.list.show', ['teacher_id' => auth()->id()]) }}" class="nav-item {{ request()->is('courses*') && !request()->is('courses/teacher*') ? 'active' : '' }}">
            <i class="bi bi-book nav-icon"></i> Courses
        </a>
        @endcan
        @can('view academic settings')
        <a href="{{ route('programs.index') }}" class="nav-item {{ request()->is('programs*') ? 'active' : '' }}">
            <i class="bi bi-mortarboard nav-icon"></i> Programs
        </a>
        <a href="{{ route('terms.index') }}" class="nav-item {{ request()->is('terms*') ? 'active' : '' }}">
            <i class="bi bi-calendar2-range nav-icon"></i> Terms
        </a>
        <a href="{{ route('curriculums.index') }}" class="nav-item {{ request()->is('curriculums*') ? 'active' : '' }}">
            <i class="bi bi-journal-richtext nav-icon"></i> Curriculum
        </a>
        @endcan
        @can('view routines')
        <a href="{{ route('routine.index') }}" class="nav-item {{ request()->is('routine*') ? 'active' : '' }}">
            <i class="bi bi-calendar4-range nav-icon"></i> Timetable
        </a>
        @endcan
        @can('view syllabi')
        <a href="{{ route('course.syllabus.index') }}" class="nav-item {{ request()->is('syllabus*') ? 'active' : '' }}">
            <i class="bi bi-journal-text nav-icon"></i> Syllabus
        </a>
        @endcan
        @endcanany

        {{-- ── TEACHING TOOLS (teacher + student) ── --}}
        @canany(['create lesson plans', 'view lesson plans'])
        <p class="nav-section-label">Teaching</p>
        <a href="{{ route('lesson-plans.index') }}" class="nav-item {{ request()->is('lesson-plans*') ? 'active' : '' }}">
            <i class="bi bi-journal-plus nav-icon"></i> Lesson Plans
        </a>
        @endcanany
        <a href="{{ route('homework.index') }}" class="nav-item {{ request()->is('homework*') ? 'active' : '' }}">
            <i class="bi bi-pencil-square nav-icon"></i> Homework
        </a>
        <a href="{{ route('projects.index') }}" class="nav-item {{ request()->is('projects*') ? 'active' : '' }}">
            <i class="bi bi-folder2-open nav-icon"></i> Projects
        </a>
        <a href="{{ route('study-notes.index') }}" class="nav-item {{ request()->is('study-notes*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text nav-icon"></i> Study Materials
        </a>
        <a href="{{ route('online-classes.index') }}" class="nav-item {{ request()->is('online-classes*') ? 'active' : '' }}">
            <i class="bi bi-camera-video nav-icon"></i> Online Classes
        </a>

        {{-- ── ATTENDANCE section ── --}}
        @canany(['take attendances', 'view attendances'])
        <p class="nav-section-label">Attendance</p>
        @can('take attendances')
        <a href="{{ route('attendance.create.show') }}" class="nav-item {{ request()->is('attendances/take') ? 'active' : '' }}">
            <i class="bi bi-check2-square nav-icon"></i> Take Attendance
        </a>
        @endcan
        @can('view attendances')
        <a href="{{ route('attendance.list.show') }}" class="nav-item {{ request()->is('attendances/view') ? 'active' : '' }}">
            <i class="bi bi-calendar-week nav-icon"></i> View Attendance
        </a>
        @endcan
        @endcanany

        {{-- ── EXAMS section ── --}}
        @canany(['view exams', 'create exams', 'view marks', 'save marks', 'view grading systems'])
        <p class="nav-section-label">Exams & Marks</p>
        @can('view exams')
        <a href="{{ route('exam.list.show') }}" class="nav-item {{ request()->is('exams/view') ? 'active' : '' }}">
            <i class="bi bi-file-text nav-icon"></i> Exams
        </a>
        @endcan
        @can('create exams')
        <a href="{{ route('exam.create.show') }}" class="nav-item">
            <i class="bi bi-file-plus nav-icon"></i> Create Exam
        </a>
        @endcan
        @can('save marks')
        <a href="{{ route('course.mark.create') }}" class="nav-item {{ request()->is('marks/create') ? 'active' : '' }}">
            <i class="bi bi-pencil-square nav-icon"></i> Enter Marks
        </a>
        @endcan
        @can('view marks')
        <a href="{{ route('course.mark.show') }}" class="nav-item {{ request()->is('marks/view') ? 'active' : '' }}">
            <i class="bi bi-clipboard-data nav-icon"></i> View Marks
        </a>
        @endcan
        @canany(['view grading systems', 'create grading systems'])
        <a href="{{ route('exam.grade.system.index') }}" class="nav-item {{ request()->is('exams/grade*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-steps nav-icon"></i> Grading
        </a>
        @endcanany
        @endcanany

        {{-- ── FINANCE section ── --}}
        @canany(['view invoices', 'create invoices', 'view payments', 'view own invoices'])
        <p class="nav-section-label">Finance</p>
        <a href="{{ route('payments.index') }}" class="nav-item {{ request()->is('payments*') ? 'active' : '' }}">
            <i class="bi bi-credit-card nav-icon"></i> Payments
        </a>
        @can('create invoices')
        <a href="{{ route('payments.create') }}" class="nav-item">
            <i class="bi bi-receipt nav-icon"></i> New Invoice
        </a>
        @endcan
        @endcanany

        {{-- ── LIBRARY section ── --}}
        @canany(['view books', 'create books', 'issue books'])
        <p class="nav-section-label">Library</p>
        <a href="{{ route('library.index') }}" class="nav-item {{ request()->is('library*') ? 'active' : '' }}">
            <i class="bi bi-journals nav-icon"></i> Books
        </a>
        @can('create books')
        <a href="{{ route('library.create') }}" class="nav-item">
            <i class="bi bi-plus-circle nav-icon"></i> Add Book
        </a>
        @endcan
        @endcanany

        {{-- ── STAFF / HR section ── --}}
        @canany(['view staff', 'create staff'])
        <p class="nav-section-label">HR & Staff</p>
        <a href="{{ route('staff.index') }}" class="nav-item {{ request()->is('staff*') ? 'active' : '' }}">
            <i class="bi bi-person-lines-fill nav-icon"></i> Staff
        </a>
        @can('create staff')
        <a href="{{ route('staff.create') }}" class="nav-item">
            <i class="bi bi-person-plus nav-icon"></i> Add Staff
        </a>
        @endcan
        @endcanany

        {{-- ── COMMUNICATION section ── --}}
        @canany(['view notices', 'create notices', 'view events'])
        <p class="nav-section-label">Communication</p>
        @can('create notices')
        <a href="{{ route('notice.create') }}" class="nav-item {{ request()->is('notice*') ? 'active' : '' }}">
            <i class="bi bi-megaphone nav-icon"></i> Notices
        </a>
        @endcan
        @can('view events')
        <a href="{{ route('events.show') }}" class="nav-item {{ request()->is('calendar-event*') ? 'active' : '' }}">
            <i class="bi bi-calendar-event nav-icon"></i> Events
        </a>
        @endcan
        <a href="{{ route('notifications.index') }}" class="nav-item {{ request()->is('notifications*') ? 'active' : '' }}">
            <i class="bi bi-bell nav-icon"></i> Notifications
            @php $unread = auth()->user()->unreadNotifications()->count(); @endphp
            @if($unread > 0)
            <span class="ml-auto text-[10px] bg-rose-500 text-white rounded-full w-4 h-4 flex items-center justify-center">{{ $unread > 9 ? '9+' : $unread }}</span>
            @endif
        </a>
        @endcanany

        {{-- ── SETTINGS / ADMIN TOOLS section ── --}}
        @canany(['view academic settings', 'manage roles', 'view audit logs'])
        <p class="nav-section-label">Administration</p>
        @can('view academic settings')
        <a href="{{ url('academics/settings') }}" class="nav-item {{ request()->is('academics/settings') ? 'active' : '' }}">
            <i class="bi bi-tools nav-icon"></i> Academic Settings
        </a>
        @endcan
        @unless(session()->has('browse_session_id'))
        @can('promote students')
        <a href="{{ url('promotions/index') }}" class="nav-item {{ request()->is('promotions*') ? 'active' : '' }}">
            <i class="bi bi-sort-numeric-up-alt nav-icon"></i> Promotions
        </a>
        @endcan
        @endunless
        @can('manage roles')
        <a href="{{ route('roles.index') }}" class="nav-item {{ request()->is('roles*') ? 'active' : '' }}">
            <i class="bi bi-shield-check nav-icon"></i> Roles & Permissions
        </a>
        @endcan
        @endcanany

    </nav>

    {{-- Bottom: files + profile --}}
    <div class="mt-4 pt-4 border-t border-slate-100 space-y-0.5">
        @can('upload files')
        <a href="{{ route('file.index') }}" class="nav-item {{ request()->is('files*') ? 'active' : '' }}">
            <i class="bi bi-folder2-open nav-icon"></i> File Manager
        </a>
        @endcan
        <a href="{{ route('two-factor.setup') }}" class="nav-item">
            <i class="bi bi-shield-lock nav-icon"></i> Security (2FA)
        </a>
        <a href="{{ route('password.change') }}" class="nav-item">
            <i class="bi bi-key nav-icon"></i> Change Password
        </a>
    </div>

</div>

<style>
.nav-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.4rem 0.625rem;
    border-radius: 0.5rem;
    font-size: 0.8125rem;
    font-weight: 500;
    color: #475569;
    text-decoration: none;
    transition: background 0.15s, color 0.15s;
}
.nav-item:hover { background: #f1f5f9; color: #1e293b; }
.nav-item.active { background: #eef2ff; color: #4f46e5; }
.nav-icon { font-size: 0.875rem; width: 1rem; flex-shrink: 0; }
.nav-section-label {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #94a3b8;
    padding: 0.75rem 0.625rem 0.25rem;
}
</style>
