{{-- NepalSchoolHub Sidebar Navigation --}}
<nav class="sidebar-nav">

@role('super-admin')
{{-- ══════════════════════════════════════
     SUPER-ADMIN SIDEBAR  (SaaS Platform Owner)
══════════════════════════════════════ --}}

    <p class="sidebar-section-label">Main</p>
    <a href="{{ url('home') }}" class="sidebar-link {{ request()->is('home') || request()->is('super*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-grid-fill"></i></span>
        <span class="sidebar-link-text">Dashboard</span>
    </a>
    <a href="{{ route('central_analytics.index') }}" class="sidebar-link {{ request()->is('analytics*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-pie-chart-fill"></i></span>
        <span class="sidebar-link-text">Analytics</span>
    </a>
    <a href="{{ route('notifications.index') }}" class="sidebar-link {{ request()->is('notifications*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-bell-fill"></i></span>
        <span class="sidebar-link-text">Notifications</span>
        @php try { $unread = auth()->user()->unreadNotifications()->count(); } catch(\Throwable $e) { $unread = 0; } @endphp
        @if($unread > 0)<span class="sidebar-badge">{{ $unread > 99 ? '99+' : $unread }}</span>@endif
    </a>

    <p class="sidebar-section-label">Schools</p>
    <a href="{{ route('super.schools.index') }}" class="sidebar-link {{ request()->is('super/schools*') && !request()->get('status') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-building-fill"></i></span>
        <span class="sidebar-link-text">All Schools</span>
        @php try { $saasTotal = \App\Models\School::count(); } catch(\Throwable $e) { $saasTotal = 0; } @endphp
        @if($saasTotal > 0)<span class="sidebar-badge">{{ $saasTotal }}</span>@endif
    </a>
    <a href="{{ route('super.schools.index') }}?status=pending" class="sidebar-link {{ request()->get('status') === 'pending' ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-hourglass-split"></i></span>
        <span class="sidebar-link-text">Pending Approvals</span>
        @php try { $saasPending = \App\Models\School::where('status','pending')->count(); } catch(\Throwable $e) { $saasPending = 0; } @endphp
        @if($saasPending > 0)<span class="sidebar-badge">{{ $saasPending }}</span>@endif
    </a>

    <p class="sidebar-section-label">Finance</p>
    <a href="{{ route('finance.overview') }}" class="sidebar-link {{ request()->is('finance') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-graph-up-arrow"></i></span>
        <span class="sidebar-link-text">Revenue Overview</span>
    </a>
    <a href="{{ route('payments.index') }}" class="sidebar-link {{ request()->is('payments*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-receipt-cutoff"></i></span>
        <span class="sidebar-link-text">Invoices & Payments</span>
    </a>
    <a href="{{ route('finance.expenses') }}" class="sidebar-link {{ request()->is('finance/expenses*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-arrow-up-circle-fill"></i></span>
        <span class="sidebar-link-text">Expenses</span>
    </a>

    <p class="sidebar-section-label">Communication</p>
    <a href="{{ route('super.contact-messages.index') }}" class="sidebar-link {{ request()->is('super/contact-messages*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-envelope-fill"></i></span>
        <span class="sidebar-link-text">Contact Inbox</span>
        @php try { $unreadMsgs = \App\Models\ContactMessage::where('status','unread')->count(); } catch(\Throwable $e) { $unreadMsgs = 0; } @endphp
        @if($unreadMsgs > 0)<span class="sidebar-badge">{{ $unreadMsgs }}</span>@endif
    </a>
    <a href="{{ route('communication.bulk') }}" class="sidebar-link {{ request()->is('communication/bulk*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-megaphone-fill"></i></span>
        <span class="sidebar-link-text">Broadcast</span>
    </a>
    <a href="{{ route('notice.create') }}" class="sidebar-link {{ request()->is('notice*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-pin-angle-fill"></i></span>
        <span class="sidebar-link-text">Notices</span>
    </a>

    <p class="sidebar-section-label">System</p>
    <a href="{{ route('roles.index') }}" class="sidebar-link {{ request()->is('roles') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-shield-fill-check"></i></span>
        <span class="sidebar-link-text">Roles & Permissions</span>
    </a>
    <a href="{{ route('two-factor.setup') }}" class="sidebar-link {{ request()->is('two-factor*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-shield-lock-fill"></i></span>
        <span class="sidebar-link-text">Security (2FA)</span>
    </a>
    <a href="{{ url('academics/settings') }}" class="sidebar-link {{ request()->is('academics/settings') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-gear-fill"></i></span>
        <span class="sidebar-link-text">Settings</span>
    </a>
    @if(class_exists(\Laravel\Horizon\Horizon::class))
    <a href="{{ url(config('horizon.path', 'horizon')) }}" target="_blank" class="sidebar-link {{ request()->is('horizon*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-speedometer2"></i></span>
        <span class="sidebar-link-text">Queue Monitor</span>
    </a>
    @endif

    @if(session('impersonating_as'))
    <p class="sidebar-section-label" style="color:#fbbf24">⚠ Impersonating</p>
    <a href="{{ route('super.impersonate.stop') }}" class="sidebar-link" style="color:#fbbf24">
        <span class="sidebar-link-icon"><i class="bi bi-person-x-fill"></i></span>
        <span class="sidebar-link-text">Stop Impersonating</span>
    </a>
    @endif

@else
{{-- ══════════════════════════════════════
     SCHOOL SIDEBAR — all other roles
══════════════════════════════════════ --}}

    <p class="sidebar-section-label">Main</p>

    <a href="{{ url('home') }}" class="sidebar-link {{ request()->is('home') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-grid-fill"></i></span>
        <span class="sidebar-link-text">Dashboard</span>
    </a>
    @role('admin|principal')
    <a href="{{ route('school.profile.edit') }}" class="sidebar-link {{ request()->is('school/profile*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-building-fill"></i></span>
        <span class="sidebar-link-text">School Profile</span>
    </a>
    @endrole
    @role('admin|principal')
    <a href="{{ route('central_analytics.index') }}" class="sidebar-link {{ request()->is('analytics*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-pie-chart-fill"></i></span>
        <span class="sidebar-link-text">Analytics</span>
    </a>
    <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->is('reports*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-file-earmark-bar-graph-fill"></i></span>
        <span class="sidebar-link-text">Reports</span>
    </a>
    @endrole
    <a href="{{ route('notifications.index') }}" class="sidebar-link {{ request()->is('notifications*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-bell-fill"></i></span>
        <span class="sidebar-link-text">Notifications</span>
        @php try { $unread = auth()->user()->unreadNotifications()->count(); } catch(\Throwable $e) { $unread = 0; } @endphp
        @if($unread > 0)
        <span class="sidebar-badge">{{ $unread > 99 ? '99+' : $unread }}</span>
        @endif
    </a>

    {{-- Students --}}
    @unlessrole('student|parent')
    @canany(['view students','create students'])
    <p class="sidebar-section-label">Students & Admissions</p>
    <a href="{{ route('student.list.show') }}" class="sidebar-link {{ request()->is('students/view*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-people-fill"></i></span>
        <span class="sidebar-link-text">All Students</span>
    </a>
    <a href="{{ route('student.id-cards.bulk') }}" target="_blank" class="sidebar-link {{ request()->is('students/id-cards*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-person-vcard-fill text-indigo-400"></i></span>
        <span class="sidebar-link-text">Student ID Cards</span>
    </a>
    @can('create students')
    @unless(session()->has('browse_session_id'))
    <a href="{{ route('student.create.show') }}" class="sidebar-link {{ request()->is('students/add') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-person-plus-fill"></i></span>
        <span class="sidebar-link-text">Add Student</span>
    </a>
    @endunless
    @endcan
    <a href="{{ route('admissions.index') }}" class="sidebar-link {{ request()->is('admissions*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-person-vcard-fill"></i></span>
        <span class="sidebar-link-text">Admissions & Enquiries</span>
    </a>
    <a href="{{ route('student.transfers.index') }}" class="sidebar-link {{ request()->is('transfers*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-arrow-left-right"></i></span>
        <span class="sidebar-link-text">Student Transfers</span>
    </a>
    @endcanany
    @endunlessrole

    {{-- Parents --}}
    @unlessrole('student|parent|teacher|class-teacher')
    <p class="sidebar-section-label">Parents</p>
    <a href="{{ route('parents.index') }}" class="sidebar-link {{ request()->is('parents*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-people"></i></span>
        <span class="sidebar-link-text">Manage Parents</span>
    </a>
    @endunlessrole

    {{-- Student self --}}
    @role('student')
    <p class="sidebar-section-label">My School</p>
    <a href="{{ route('student.attendance.show', auth()->id()) }}" class="sidebar-link {{ request()->is('students/view/attendance*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-calendar-check-fill"></i></span>
        <span class="sidebar-link-text">My Attendance</span>
    </a>
    <a href="{{ route('results.index') }}" class="sidebar-link {{ request()->is('results*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-award-fill"></i></span>
        <span class="sidebar-link-text">My Results</span>
    </a>
    <a href="{{ route('homework.index') }}" class="sidebar-link {{ request()->is('homework*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-pencil-fill"></i></span>
        <span class="sidebar-link-text">Homework</span>
        @php
            try {
                $sessionId = app(\App\Interfaces\SchoolSessionInterface::class)->getLatestSession()?->id;
                $studentPromo = \App\Models\Promotion::where('student_id', auth()->id())->where('session_id', $sessionId)->latest()->first();
                $hwDue = $studentPromo
                    ? \App\Models\Homework::where('class_id', $studentPromo->class_id)
                        ->where('section_id', $studentPromo->section_id)
                        ->where('session_id', $sessionId)
                        ->where('status', 'active')
                        ->where('due_date', '>=', now()->toDateString())
                        ->whereDoesntHave('submissions', fn($q) => $q->where('student_id', auth()->id()))
                        ->count()
                    : 0;
            } catch(\Throwable $e) { $hwDue = 0; }
        @endphp
        @if($hwDue > 0)
        <span class="sidebar-badge">{{ $hwDue }}</span>
        @endif
    </a>
    <a href="{{ route('projects.index') }}" class="sidebar-link {{ request()->is('projects*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-folder-fill"></i></span>
        <span class="sidebar-link-text">Projects</span>
    </a>
    <a href="{{ route('online-exams.student.index') }}" class="sidebar-link {{ request()->is('online-exams/my-exams*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-laptop-fill"></i></span>
        <span class="sidebar-link-text">Online Exams</span>
    </a>
    <a href="{{ route('study-notes.index') }}" class="sidebar-link {{ request()->is('study-notes*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-file-earmark-text-fill"></i></span>
        <span class="sidebar-link-text">Study Materials</span>
    </a>
    <a href="{{ route('online-classes.index') }}" class="sidebar-link {{ request()->is('online-classes*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-camera-video-fill"></i></span>
        <span class="sidebar-link-text">Online Classes</span>
    </a>
    <a href="{{ route('section.routine.show') }}" class="sidebar-link {{ request()->is('routine/view*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-calendar4-week"></i></span>
        <span class="sidebar-link-text">My Timetable</span>
    </a>
    <a href="{{ route('library.index') }}" class="sidebar-link {{ request()->is('library*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-journals"></i></span>
        <span class="sidebar-link-text">Library Catalog</span>
    </a>

    <p class="sidebar-section-label">Exams & Results</p>
    <a href="{{ route('re-exam.create') }}" class="sidebar-link {{ request()->is('re-exam/apply*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-arrow-repeat"></i></span>
        <span class="sidebar-link-text">Apply Re-Exam</span>
    </a>
    <a href="{{ route('achievements.leaderboard') }}" class="sidebar-link {{ request()->is('achievements*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-trophy-fill"></i></span>
        <span class="sidebar-link-text">Leaderboard</span>
    </a>

    <p class="sidebar-section-label">My Account</p>
    <a href="{{ route('payments.index') }}" class="sidebar-link {{ request()->is('payments*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-receipt-cutoff"></i></span>
        <span class="sidebar-link-text">Fees & Invoices</span>
    </a>
    <a href="{{ route('leave.index') }}" class="sidebar-link {{ request()->is('leave*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-calendar-x-fill"></i></span>
        <span class="sidebar-link-text">Leave Requests</span>
        @php
            try {
                $myStudentPendingLeaves = \App\Models\LeaveApplication::where('user_id', auth()->id())->where('status','pending')->count();
            } catch(\Throwable $e) { $myStudentPendingLeaves = 0; }
        @endphp
        @if($myStudentPendingLeaves > 0)
        <span class="sidebar-badge">{{ $myStudentPendingLeaves }}</span>
        @endif
    </a>
    @endrole

    {{-- Parent --}}
    @role('parent')
    @php
        try { $parentChildren = auth()->user()->children()->get(); } catch(\Throwable $e) { $parentChildren = collect(); }
    @endphp
    <p class="sidebar-section-label">My Children</p>
    @forelse($parentChildren as $__child)
    <p class="px-3 pt-2 pb-0.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest truncate">{{ $__child->first_name }}</p>
    <a href="{{ route('parent.attendance',  $__child->id) }}" class="sidebar-link {{ request()->is('parent/'.$__child->id.'/attendance*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-calendar-check-fill"></i></span>
        <span class="sidebar-link-text">Attendance</span>
    </a>
    <a href="{{ route('parent.results',     $__child->id) }}" class="sidebar-link {{ request()->is('parent/'.$__child->id.'/results*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-award-fill"></i></span>
        <span class="sidebar-link-text">Results</span>
    </a>
    <a href="{{ route('parent.homework',    $__child->id) }}" class="sidebar-link {{ request()->is('parent/'.$__child->id.'/homework*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-pencil-fill"></i></span>
        <span class="sidebar-link-text">Homework</span>
    </a>
    <a href="{{ route('parent.assignments', $__child->id) }}" class="sidebar-link {{ request()->is('parent/'.$__child->id.'/assignments*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-file-earmark-check-fill"></i></span>
        <span class="sidebar-link-text">Assignments</span>
    </a>
    <a href="{{ route('parent.timetable',   $__child->id) }}" class="sidebar-link {{ request()->is('parent/'.$__child->id.'/timetable*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-calendar4-week"></i></span>
        <span class="sidebar-link-text">Timetable</span>
    </a>
    <a href="{{ route('parent.fees',        $__child->id) }}" class="sidebar-link {{ request()->is('parent/'.$__child->id.'/fees*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-receipt-cutoff"></i></span>
        <span class="sidebar-link-text">Fees</span>
    </a>
    <a href="{{ route('parent.transport',   $__child->id) }}" class="sidebar-link {{ request()->is('parent/'.$__child->id.'/transport*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-bus-front-fill"></i></span>
        <span class="sidebar-link-text">Transport</span>
    </a>
    <a href="{{ route('parent.leave',       $__child->id) }}" class="sidebar-link {{ request()->is('parent/'.$__child->id.'/leave*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-calendar-x-fill"></i></span>
        <span class="sidebar-link-text">Leave</span>
    </a>
    <a href="{{ route('parent.performance', $__child->id) }}" class="sidebar-link {{ request()->is('parent/'.$__child->id.'/performance*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-graph-up"></i></span>
        <span class="sidebar-link-text">Performance</span>
    </a>
    <a href="{{ route('parent.teachers', $__child->id) }}" class="sidebar-link {{ request()->is('parent/'.$__child->id.'/teachers*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-person-badge"></i></span>
        <span class="sidebar-link-text">Teachers</span>
    </a>
    <a href="{{ route('parent.meetings', $__child->id) }}" class="sidebar-link {{ request()->is('parent/'.$__child->id.'/meetings*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-calendar-event"></i></span>
        <span class="sidebar-link-text">Meetings</span>
    </a>
    <a href="{{ route('parent.events', $__child->id) }}" class="sidebar-link {{ request()->is('parent/'.$__child->id.'/events*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-calendar-star"></i></span>
        <span class="sidebar-link-text">Events</span>
    </a>
    <a href="{{ route('parent.health', $__child->id) }}" class="sidebar-link {{ request()->is('parent/'.$__child->id.'/health*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-heart-pulse"></i></span>
        <span class="sidebar-link-text">Health</span>
    </a>
    <a href="{{ route('parent.behavior', $__child->id) }}" class="sidebar-link {{ request()->is('parent/'.$__child->id.'/behavior*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-shield-exclamation"></i></span>
        <span class="sidebar-link-text">Behavior</span>
    </a>
    <a href="{{ route('parent.documents', $__child->id) }}" class="sidebar-link {{ request()->is('parent/'.$__child->id.'/documents*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-folder2-open"></i></span>
        <span class="sidebar-link-text">Documents</span>
    </a>
    @empty
    @endforelse

    <p class="sidebar-section-label">Communication</p>
    <a href="{{ route('parent.conversations') }}" class="sidebar-link {{ request()->is('parent/conversations*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-chat-dots-fill"></i></span>
        <span class="sidebar-link-text">Messages</span>
        @php
            try {
                $parentUnread = \App\Models\Conversation::where('parent_id', auth()->id())
                    ->withCount(['messages as um' => fn($q) => $q->where('sender_id', '!=', auth()->id())->whereNull('read_at')])
                    ->get()->sum('um');
            } catch(\Throwable $e) { $parentUnread = 0; }
        @endphp
        @if($parentUnread > 0)
        <span class="sidebar-badge">{{ $parentUnread > 99 ? '99+' : $parentUnread }}</span>
        @endif
    </a>
    <a href="{{ route('events.show') }}" class="sidebar-link {{ request()->is('calendar-event*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-calendar-event-fill"></i></span>
        <span class="sidebar-link-text">School Calendar</span>
    </a>
    @endrole

    {{-- Teachers --}}
    @unlessrole('student|parent')
    @canany(['view teachers','create teachers'])
    <p class="sidebar-section-label">Teachers</p>
    <a href="{{ route('teacher.list.show') }}" class="sidebar-link {{ request()->is('teachers/view/list') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-person-badge-fill"></i></span>
        <span class="sidebar-link-text">All Teachers</span>
    </a>
    <a href="{{ route('teacher.id-cards.bulk') }}" target="_blank" class="sidebar-link {{ request()->is('teachers/id-cards*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-person-vcard text-blue-400"></i></span>
        <span class="sidebar-link-text">Teacher ID Cards</span>
    </a>
    @can('create teachers')
    @unless(session()->has('browse_session_id'))
    <a href="{{ route('teacher.create.show') }}" class="sidebar-link">
        <span class="sidebar-link-icon"><i class="bi bi-person-plus-fill"></i></span>
        <span class="sidebar-link-text">Add Teacher</span>
    </a>
    @endunless
    @endcan
    @endcanany
    @endunlessrole

    {{-- Teacher self --}}
    @role('teacher|class-teacher')
    <p class="sidebar-section-label">Teaching</p>
    <a href="{{ route('course.teacher.list.show', ['teacher_id' => auth()->id()]) }}" class="sidebar-link {{ request()->is('courses/teacher*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-journal-richtext"></i></span>
        <span class="sidebar-link-text">My Courses</span>
    </a>
    <a href="{{ route('routine.teacher-timetable') }}" class="sidebar-link {{ request()->is('routine/teacher-timetable*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-calendar4-week"></i></span>
        <span class="sidebar-link-text">My Timetable</span>
    </a>
    <a href="{{ route('lesson-plans.index') }}" class="sidebar-link {{ request()->is('lesson-plans*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-journal-plus"></i></span>
        <span class="sidebar-link-text">Lesson Plans</span>
    </a>
    <a href="{{ route('homework.index') }}" class="sidebar-link {{ request()->is('homework*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-pencil-fill"></i></span>
        <span class="sidebar-link-text">Homework</span>
    </a>
    <a href="{{ route('assignment.teacher.index') }}" class="sidebar-link {{ request()->is('assignments*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-file-earmark-check-fill"></i></span>
        <span class="sidebar-link-text">Assignments</span>
    </a>
    <a href="{{ route('projects.index') }}" class="sidebar-link {{ request()->is('projects*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-folder-fill"></i></span>
        <span class="sidebar-link-text">Projects</span>
    </a>
    <a href="{{ route('study-notes.index') }}" class="sidebar-link {{ request()->is('study-notes*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-file-earmark-text-fill"></i></span>
        <span class="sidebar-link-text">Study Materials</span>
    </a>
    <a href="{{ route('class.syllabus.create') }}" class="sidebar-link {{ request()->is('syllabus*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-journal-bookmark-fill"></i></span>
        <span class="sidebar-link-text">Syllabus</span>
    </a>
    <a href="{{ route('online-classes.index') }}" class="sidebar-link {{ request()->is('online-classes*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-camera-video-fill"></i></span>
        <span class="sidebar-link-text">Online Classes</span>
    </a>

    <p class="sidebar-section-label">Assessments</p>
    <a href="{{ route('online-exams.index') }}" class="sidebar-link {{ request()->is('online-exams*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-laptop-fill"></i></span>
        <span class="sidebar-link-text">Online Exams</span>
    </a>
    <a href="{{ route('practical-exams.index') }}" class="sidebar-link {{ request()->is('practical-exams*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-nut-fill"></i></span>
        <span class="sidebar-link-text">Practical Exams</span>
    </a>
    <a href="{{ route('exam.schedule.index') }}" class="sidebar-link {{ request()->is('exam/schedule*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-calendar2-week-fill"></i></span>
        <span class="sidebar-link-text">Exam Schedule</span>
    </a>
    <a href="{{ route('attendance.qr.index') }}" class="sidebar-link {{ request()->is('attendance/qr*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-qr-code"></i></span>
        <span class="sidebar-link-text">QR Attendance</span>
    </a>

    <p class="sidebar-section-label">Resources</p>
    <a href="{{ route('gradebook.index') }}" class="sidebar-link {{ request()->is('gradebook*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-table"></i></span>
        <span class="sidebar-link-text">Gradebook</span>
    </a>
    <a href="{{ route('student.discipline.index') }}" class="sidebar-link {{ request()->is('discipline*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-shield-exclamation"></i></span>
        <span class="sidebar-link-text">Conduct Log</span>
    </a>
    <a href="{{ route('library.ebooks.index') }}" class="sidebar-link {{ request()->is('library/ebooks*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-book-half"></i></span>
        <span class="sidebar-link-text">E-books</span>
    </a>
    <a href="{{ route('events.show') }}" class="sidebar-link {{ request()->is('calendar-event*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-calendar-event-fill"></i></span>
        <span class="sidebar-link-text">School Calendar</span>
    </a>
    @role('admin|principal|teacher|class-teacher')
    <a href="{{ route('ai_features.web.index') }}" class="sidebar-link {{ request()->is('ai-features') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-robot"></i></span>
        <span class="sidebar-link-text">AI Tools</span>
        <span class="ml-1 text-[9px] bg-indigo-500 text-white px-1.5 py-0.5 rounded-full">Beta</span>
    </a>
    @endrole

    <p class="sidebar-section-label">My Work</p>
    <a href="{{ route('teacher.messages') }}" class="sidebar-link {{ request()->is('teacher/messages*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-chat-dots-fill"></i></span>
        <span class="sidebar-link-text">Parent Messages</span>
        @php
            try {
                $teacherUnreadMsgs = \App\Models\Conversation::where('teacher_id', auth()->id())
                    ->withCount(['messages as um' => fn($q) => $q->where('sender_id', '!=', auth()->id())->whereNull('read_at')])
                    ->get()->sum('um');
            } catch(\Throwable $e) { $teacherUnreadMsgs = 0; }
        @endphp
        @if($teacherUnreadMsgs > 0)
        <span class="sidebar-badge">{{ $teacherUnreadMsgs > 99 ? '99+' : $teacherUnreadMsgs }}</span>
        @endif
    </a>
    @can('create notices')
    <a href="{{ route('notice.create') }}" class="sidebar-link {{ request()->is('notice*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-megaphone-fill"></i></span>
        <span class="sidebar-link-text">Post Notice</span>
    </a>
    @endcan
    <a href="{{ route('leave.index') }}" class="sidebar-link {{ request()->is('leave*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-calendar-x-fill"></i></span>
        <span class="sidebar-link-text">Leave Requests</span>
        @php
            try {
                $myPendingLeaves = \App\Models\LeaveApplication::where('user_id', auth()->id())->where('status','pending')->count();
            } catch(\Throwable $e) { $myPendingLeaves = 0; }
        @endphp
        @if($myPendingLeaves > 0)
        <span class="sidebar-badge">{{ $myPendingLeaves }}</span>
        @endif
    </a>
    <a href="{{ route('teacher.payroll.index') }}" class="sidebar-link {{ request()->is('teacher/payroll*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-cash-stack"></i></span>
        <span class="sidebar-link-text">My Payslips</span>
    </a>
    <a href="{{ route('teacher.profile.show', auth()->id()) }}" class="sidebar-link {{ request()->is('teachers/view/profile/'.auth()->id()) ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-person-circle"></i></span>
        <span class="sidebar-link-text">My Profile</span>
    </a>
    @endrole

    {{-- Academic — hide for teacher/class-teacher/student/parent --}}
    @unlessrole('teacher|class-teacher|student|parent')
    @canany(['view classes','view courses','view routines','view syllabi','view academic settings'])
    <p class="sidebar-section-label">Academic</p>
    @can('view classes')
    <a href="{{ url('classes') }}" class="sidebar-link {{ request()->is('classes') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-diagram-3-fill"></i></span>
        <span class="sidebar-link-text">Classes & Sections</span>
    </a>
    @endcan
    @can('view routines')
    <a href="{{ route('routine.index') }}" class="sidebar-link {{ request()->is('routine*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-calendar4-range"></i></span>
        <span class="sidebar-link-text">Timetable</span>
    </a>
    @endcan
    @can('view academic settings')
    <a href="{{ route('programs.index') }}" class="sidebar-link {{ request()->is('programs*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-mortarboard-fill"></i></span>
        <span class="sidebar-link-text">Academic Programs</span>
    </a>
    <a href="{{ route('departments.index') }}" class="sidebar-link {{ request()->is('departments*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-building"></i></span>
        <span class="sidebar-link-text">Departments</span>
    </a>
    <a href="{{ route('terms.index') }}" class="sidebar-link {{ request()->is('terms*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-calendar-range"></i></span>
        <span class="sidebar-link-text">Academic Terms</span>
    </a>
    <a href="{{ route('curriculums.index') }}" class="sidebar-link {{ request()->is('curriculums*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-journal-richtext"></i></span>
        <span class="sidebar-link-text">Curriculum</span>
    </a>
    <a href="{{ route('course.syllabus.index') }}" class="sidebar-link {{ request()->is('syllabus*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-journal-bookmark-fill"></i></span>
        <span class="sidebar-link-text">Syllabus</span>
    </a>
    <a href="{{ route('lesson-plans.index') }}" class="sidebar-link {{ request()->is('lesson-plans*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-journal-plus"></i></span>
        <span class="sidebar-link-text">Lesson Plans</span>
    </a>
    <a href="{{ route('online-classes.index') }}" class="sidebar-link {{ request()->is('online-classes*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-camera-video-fill"></i></span>
        <span class="sidebar-link-text">Online Classes</span>
    </a>
    <a href="{{ route('promotions.index') }}" class="sidebar-link {{ request()->is('promotions*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-graph-up-arrow"></i></span>
        <span class="sidebar-link-text">Promotions</span>
    </a>
    @endcan
    @endcanany
    @endunlessrole

    {{-- Attendance — hide for teacher/class-teacher/student/parent --}}
    @unlessrole('teacher|class-teacher|student|parent')
    @canany(['take attendances','view attendances'])
    <p class="sidebar-section-label">Attendance</p>
    @can('take attendances')
    <a href="{{ route('attendance.create.show') }}" class="sidebar-link {{ request()->is('attendances/take') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-check2-square"></i></span>
        <span class="sidebar-link-text">Take Attendance</span>
    </a>
    @endcan
    @can('view attendances')
    <a href="{{ route('attendance.list.show') }}" class="sidebar-link {{ request()->is('attendances/view') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-calendar-week-fill"></i></span>
        <span class="sidebar-link-text">View Attendance</span>
    </a>
    <a href="{{ route('attendance.analytics') }}" class="sidebar-link {{ request()->is('attendances/analytics') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-bar-chart-fill"></i></span>
        <span class="sidebar-link-text">Analytics</span>
    </a>
    <a href="{{ route('rfid.scans') }}" class="sidebar-link {{ request()->is('rfid*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-qr-code-scan"></i></span>
        <span class="sidebar-link-text">RFID Gate Scans</span>
    </a>
    @endcan
    @endcanany
    @endunlessrole

    {{-- Exams & Marks — hide for teacher/class-teacher/student/parent --}}
    @unlessrole('teacher|class-teacher|student|parent')
    @canany(['view exams','create exams','save marks','view marks'])
    <p class="sidebar-section-label">Exams & Marks</p>
    @can('view exams')
    <a href="{{ route('exam.list.show') }}" class="sidebar-link {{ request()->is('exams/view') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-file-earmark-text-fill"></i></span>
        <span class="sidebar-link-text">Exams</span>
    </a>
    @endcan
    @can('save marks')
    <a href="{{ route('course.mark.create') }}" class="sidebar-link {{ request()->is('marks/create') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-pencil-square"></i></span>
        <span class="sidebar-link-text">Enter Marks</span>
    </a>
    @endcan
    @can('view marks')
    <a href="{{ route('results.index') }}" class="sidebar-link {{ request()->is('results') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-award-fill"></i></span>
        <span class="sidebar-link-text">Results</span>
    </a>
    @endcan
    @canany(['view exams','create exams'])
    <a href="{{ route('exam.grade.system.index') }}" class="sidebar-link {{ request()->is('exams/grade*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-award-fill"></i></span>
        <span class="sidebar-link-text">Grading Systems</span>
    </a>
    <a href="{{ route('online-exams.index') }}" class="sidebar-link {{ request()->is('online-exams*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-pc-display"></i></span>
        <span class="sidebar-link-text">Online Exams</span>
    </a>
    <a href="{{ route('practical-exams.index') }}" class="sidebar-link {{ request()->is('practical-exams*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-nut-fill"></i></span>
        <span class="sidebar-link-text">Practical Exams</span>
    </a>
    <a href="{{ route('exam.schedule.index') }}" class="sidebar-link {{ request()->is('exam/schedule*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-calendar2-week-fill"></i></span>
        <span class="sidebar-link-text">Exam Schedule</span>
    </a>
    <a href="{{ route('exam.hall.index') }}" class="sidebar-link {{ request()->is('exam/halls*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-grid-3x3-gap-fill"></i></span>
        <span class="sidebar-link-text">Halls & Seat Planning</span>
    </a>
    <a href="{{ route('question-bank.index') }}" class="sidebar-link {{ request()->is('question-bank*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-journal-code"></i></span>
        <span class="sidebar-link-text">Question Bank</span>
    </a>
    <a href="{{ route('question-paper-templates.index') }}" class="sidebar-link {{ request()->is('question-paper-templates*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-file-earmark-diff"></i></span>
        <span class="sidebar-link-text">Paper Blueprints</span>
    </a>
    <a href="{{ route('question-papers.index') }}" class="sidebar-link {{ request()->is('question-papers*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-file-earmark-richtext-fill"></i></span>
        <span class="sidebar-link-text">Question Papers</span>
        @if(isset($pendingPapers) && $pendingPapers > 0)
        <span class="sidebar-badge">{{ $pendingPapers }}</span>
        @endif
    </a>
    @endcanany
    @endcanany
    @endunlessrole

    {{-- Finance --}}
    @unlessrole('student|parent')
    @canany(['manage finance','view invoices','create invoices','view payments','view own invoices'])
    <p class="sidebar-section-label">Finance</p>
    @can('manage finance')
    <a href="{{ route('finance.overview') }}" class="sidebar-link {{ request()->is('finance') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-graph-up-arrow"></i></span>
        <span class="sidebar-link-text">Overview</span>
    </a>
    <a href="{{ route('finance.fee-types.index') }}" class="sidebar-link {{ request()->is('finance/fee-types*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-list-ul"></i></span>
        <span class="sidebar-link-text">Fee Types</span>
    </a>
    <a href="{{ route('finance.categories.index') }}" class="sidebar-link {{ request()->is('finance/categories*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-tags"></i></span>
        <span class="sidebar-link-text">Fee Categories</span>
    </a>
    <a href="{{ route('finance.structures') }}" class="sidebar-link {{ request()->is('finance/structures*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-table"></i></span>
        <span class="sidebar-link-text">Fee Structures</span>
    </a>
    <a href="{{ route('finance.discounts') }}" class="sidebar-link {{ request()->is('finance/discounts*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-percent"></i></span>
        <span class="sidebar-link-text">Discounts</span>
    </a>
    @endcan
    <a href="{{ route('payments.index') }}" class="sidebar-link {{ request()->is('payments*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-receipt-cutoff"></i></span>
        <span class="sidebar-link-text">Invoices & Payments</span>
    </a>
    @can('manage finance')
    <a href="{{ route('finance.expenses') }}" class="sidebar-link {{ request()->is('finance/expenses*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-arrow-up-circle-fill"></i></span>
        <span class="sidebar-link-text">Expenses</span>
    </a>
    <a href="{{ route('finance.income') }}" class="sidebar-link {{ request()->is('finance/income*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-arrow-down-circle-fill"></i></span>
        <span class="sidebar-link-text">Income</span>
    </a>
    <a href="{{ route('finance.salary.index') }}" class="sidebar-link {{ request()->is('finance/salary*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-cash-stack"></i></span>
        <span class="sidebar-link-text">Salary Slips</span>
    </a>
    <a href="{{ route('finance.salary.components') }}" class="sidebar-link {{ request()->is('finance/salary/components*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-sliders"></i></span>
        <span class="sidebar-link-text">Salary Components</span>
    </a>
    <a href="{{ route('finance.tax.index') }}" class="sidebar-link {{ request()->is('finance/tax*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-calculator-fill"></i></span>
        <span class="sidebar-link-text">Tax Rates</span>
    </a>
    <a href="{{ route('finance.ledger') }}" class="sidebar-link {{ request()->is('finance/ledger*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-journal-bookmark-fill"></i></span>
        <span class="sidebar-link-text">Accounting Ledger</span>
    </a>
    <a href="{{ route('finance.profit-loss') }}" class="sidebar-link {{ request()->is('finance/profit-loss*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-bar-chart-fill"></i></span>
        <span class="sidebar-link-text">Profit & Loss</span>
    </a>
    <a href="{{ url('finance/reports') }}" class="sidebar-link {{ request()->is('finance/reports*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-file-earmark-bar-graph-fill"></i></span>
        <span class="sidebar-link-text">Finance Reports</span>
    </a>
    @endcan
    @endcanany
    @endunlessrole

    {{-- Library --}}
    @unlessrole('student|parent')
    @canany(['view books','create books','issue books','return books'])
    <p class="sidebar-section-label">Library</p>
    <a href="{{ route('library.index') }}" class="sidebar-link {{ request()->is('library') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-journals"></i></span>
        <span class="sidebar-link-text">Book Catalog</span>
    </a>
    @canany(['issue books','return books'])
    <a href="{{ route('library.issues.index') }}" class="sidebar-link {{ request()->is('library/issues*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-arrow-left-right"></i></span>
        <span class="sidebar-link-text">Issue / Return</span>
    </a>
    @endcanany
    @can('manage library members')
    <a href="{{ route('library.members.index') }}" class="sidebar-link {{ request()->is('library/members*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-person-lines-fill"></i></span>
        <span class="sidebar-link-text">Members</span>
    </a>
    @endcan
    @endcanany
    @endunlessrole

    {{-- Hostel --}}
    @unlessrole('student|parent')
    <p class="sidebar-section-label">Hostel Management</p>
    <a href="{{ route('hostel.hostels.index') }}" class="sidebar-link {{ request()->is('hostel/hostels*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-building-fill"></i></span>
        <span class="sidebar-link-text">Hostel Buildings</span>
    </a>
    <a href="{{ route('hostel.rooms.index') }}" class="sidebar-link {{ request()->is('hostel/rooms*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-door-closed-fill"></i></span>
        <span class="sidebar-link-text">Hostel Rooms</span>
    </a>
    <a href="{{ route('hostel.beds.index') }}" class="sidebar-link {{ request()->is('hostel/beds*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-lamp-fill"></i></span>
        <span class="sidebar-link-text">Beds Inventory</span>
    </a>
    <a href="{{ route('hostel.allocations.index') }}" class="sidebar-link {{ request()->is('hostel/allocations*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-person-badge-fill"></i></span>
        <span class="sidebar-link-text">Student Allocations</span>
    </a>
    <a href="{{ route('hostel.attendances.index') }}" class="sidebar-link {{ request()->is('hostel/attendances*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-clipboard-check-fill"></i></span>
        <span class="sidebar-link-text">Night Roll Call</span>
    </a>
    <a href="{{ route('hostel.visitors.index') }}" class="sidebar-link {{ request()->is('hostel/visitors*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-person-lines-fill"></i></span>
        <span class="sidebar-link-text">Visitor Log</span>
    </a>
    <a href="{{ route('hostel.maintenance.index') }}" class="sidebar-link {{ request()->is('hostel/maintenance*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-tools"></i></span>
        <span class="sidebar-link-text">Maintenance Requests</span>
    </a>
    @endunlessrole

    {{-- Transport --}}
    @unlessrole('student|parent')
    <p class="sidebar-section-label">Transport Management</p>
    <a href="{{ route('transport.index') }}" class="sidebar-link {{ request()->is('transport') && !request()->is('transport/*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-speedometer2"></i></span>
        <span class="sidebar-link-text">Transport Overview</span>
    </a>
    <a href="{{ route('transport.vehicles.index') }}" class="sidebar-link {{ request()->is('transport/vehicles*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-bus-front-fill"></i></span>
        <span class="sidebar-link-text">Fleet Vehicles</span>
    </a>
    <a href="{{ route('transport.drivers.index') }}" class="sidebar-link {{ request()->is('transport/drivers*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-person-badge-fill"></i></span>
        <span class="sidebar-link-text">Drivers Directory</span>
    </a>
    <a href="{{ route('transport.routes.index') }}" class="sidebar-link {{ request()->is('transport/routes*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-map-fill"></i></span>
        <span class="sidebar-link-text">Routes & Bus Stops</span>
    </a>
    <a href="{{ route('transport.students.index') }}" class="sidebar-link {{ request()->is('transport/students*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-person-workspace"></i></span>
        <span class="sidebar-link-text">Student Transport</span>
    </a>
    <a href="{{ route('transport.attendance.index') }}" class="sidebar-link {{ request()->is('transport/attendance*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-clipboard-check-fill"></i></span>
        <span class="sidebar-link-text">Bus Attendance</span>
    </a>
    <a href="{{ route('transport.analytics') }}" class="sidebar-link {{ request()->is('transport/analytics*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-graph-up-arrow"></i></span>
        <span class="sidebar-link-text">Analytics & Fuel</span>
    </a>
    <a href="{{ route('transport.reports.form') }}" class="sidebar-link {{ request()->is('transport/reports*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-file-earmark-bar-graph-fill"></i></span>
        <span class="sidebar-link-text">Transport Reports</span>
    </a>
    @endunlessrole

    {{-- Certificates --}}
    @unlessrole('student|parent')
    @canany(['view students','create students'])
    <p class="sidebar-section-label">Certificates &amp; ID Cards</p>
    <a href="{{ route('certificates.index') }}" class="sidebar-link {{ request()->is('certificates') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-award-fill"></i></span>
        <span class="sidebar-link-text">Cert. Templates</span>
    </a>
    <a href="{{ route('certificates.issued') }}" class="sidebar-link {{ request()->is('certificates/issued*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-journal-text"></i></span>
        <span class="sidebar-link-text">Issued Ledger</span>
    </a>
    <a href="{{ route('certificates.bulk') }}" class="sidebar-link {{ request()->is('certificates/bulk*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-people-fill"></i></span>
        <span class="sidebar-link-text">Bulk Issue</span>
    </a>
    @role('admin|principal')
    <a href="{{ route('id-card-templates.index') }}" class="sidebar-link {{ request()->is('id-card-templates*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-credit-card-2-front-fill"></i></span>
        <span class="sidebar-link-text">ID Card Designer</span>
    </a>
    @endrole
    @endcanany
    @endunlessrole

    {{-- HR --}}
    @unlessrole('student|parent|teacher|class-teacher')
    @canany(['view staff','create staff'])
    <p class="sidebar-section-label">HR & Staff</p>
    <a href="{{ route('hr.dashboard') }}" class="sidebar-link {{ request()->is('hr') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-people-fill"></i></span>
        <span class="sidebar-link-text">HR Dashboard</span>
    </a>
    <a href="{{ route('staff.attendance.index') }}" class="sidebar-link {{ request()->is('staff/attendance*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-calendar-check-fill"></i></span>
        <span class="sidebar-link-text">Staff Attendance</span>
    </a>
    <a href="{{ route('hr.leaves') }}" class="sidebar-link {{ request()->is('hr/leaves') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-calendar-x-fill"></i></span>
        <span class="sidebar-link-text">Leave Approvals</span>
    </a>
    <a href="{{ route('hr.payroll') }}" class="sidebar-link {{ request()->is('hr/payroll') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-cash-stack"></i></span>
        <span class="sidebar-link-text">Payroll</span>
    </a>
    <a href="{{ route('staff.recruitment.postings') }}" class="sidebar-link {{ request()->is('staff/recruitment*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-briefcase-fill"></i></span>
        <span class="sidebar-link-text">Recruitment & Postings</span>
    </a>
    <a href="{{ route('staff.contracts.index') }}" class="sidebar-link {{ request()->is('staff/contracts*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-file-earmark-text-fill"></i></span>
        <span class="sidebar-link-text">Staff Contracts</span>
    </a>
    <a href="{{ route('staff.training.index') }}" class="sidebar-link {{ request()->is('staff/training*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-award-fill"></i></span>
        <span class="sidebar-link-text">Staff Training</span>
    </a>
    <a href="{{ route('staff.exit.index') }}" class="sidebar-link {{ request()->is('staff/exit*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-person-x-fill"></i></span>
        <span class="sidebar-link-text">Staff Offboarding</span>
    </a>
    @endcanany
    @endunlessrole

    {{-- Inventory --}}
    @unlessrole('student|parent|teacher|class-teacher')
    @canany(['view inventory','manage inventory'])
    <p class="sidebar-section-label">Inventory</p>
    <a href="{{ route('inventory.dashboard') }}" class="sidebar-link {{ request()->is('inventory') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-boxes"></i></span>
        <span class="sidebar-link-text">Inventory</span>
    </a>
    @endcanany
    @endunlessrole

    {{-- Communication --}}
    @unlessrole('student|parent')
    @canany(['view notices','create notices','view events'])
    <p class="sidebar-section-label">Communication</p>
    @can('create notices')
    <a href="{{ route('notice.create') }}" class="sidebar-link {{ request()->is('notice*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-megaphone-fill"></i></span>
        <span class="sidebar-link-text">Notices</span>
    </a>
    <a href="{{ route('communication.bulk') }}" class="sidebar-link {{ request()->is('communication/bulk*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-whatsapp"></i></span>
        <span class="sidebar-link-text">WhatsApp & Bulk Alerts</span>
    </a>
    <a href="{{ route('communication.individual') }}" class="sidebar-link {{ request()->is('communication/individual*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-person-lines-fill"></i></span>
        <span class="sidebar-link-text">Direct WhatsApp</span>
    </a>
    <a href="{{ route('communication.index') }}" class="sidebar-link {{ request()->is('communication') && !request()->is('communication/*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-chat-left-text-fill"></i></span>
        <span class="sidebar-link-text">Communication Hub</span>
    </a>
    <a href="{{ route('communication.settings') }}" class="sidebar-link {{ request()->is('communication/settings*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-sliders"></i></span>
        <span class="sidebar-link-text">Gateway Settings</span>
    </a>
    @endcan
    @endcanany
    @endunlessrole

    {{-- AI (for admin/principal only - teachers have it in their own section) --}}
    @role('admin|principal')
    <p class="sidebar-section-label">AI Tools <span class="ml-1 text-[9px] bg-indigo-500 text-white px-1.5 py-0.5 rounded-full">Beta</span></p>
    <a href="{{ route('ai_features.web.index') }}" class="sidebar-link {{ request()->is('ai-features') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-robot"></i></span>
        <span class="sidebar-link-text">AI Dashboard</span>
    </a>
    @endrole

    {{-- Administration --}}
    @unlessrole('student|parent|teacher|class-teacher')
    @canany(['view academic settings','manage roles','promote students'])
    <p class="sidebar-section-label">Administration</p>
    @can('view academic settings')
    <a href="{{ url('academics/settings') }}" class="sidebar-link {{ request()->is('academics/settings') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-gear-fill"></i></span>
        <span class="sidebar-link-text">Settings</span>
    </a>
    @endcan
    @can('manage roles')
    <a href="{{ route('roles.index') }}" class="sidebar-link {{ request()->is('roles') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-shield-fill-check"></i></span>
        <span class="sidebar-link-text">Roles & Permissions</span>
    </a>
    <a href="{{ route('roles.assignable-users') }}" class="sidebar-link {{ request()->is('roles/users*') ? 'active' : '' }} ps-10" style="font-size:0.82rem">
        <span class="sidebar-link-icon"><i class="bi bi-person-check"></i></span>
        <span class="sidebar-link-text">Assign Roles</span>
    </a>
    <a href="{{ route('roles.matrix') }}" class="sidebar-link {{ request()->is('roles/matrix') ? 'active' : '' }} ps-10" style="font-size:0.82rem">
        <span class="sidebar-link-icon"><i class="bi bi-grid-3x3"></i></span>
        <span class="sidebar-link-text">Permission Matrix</span>
    </a>
    @endcan
    @endcanany
    @endunlessrole

@endrole

</nav>

{{-- Sidebar footer --}}
<div class="sidebar-footer">
    <a href="{{ route('two-factor.setup') }}" class="sidebar-link {{ request()->is('two-factor*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-shield-lock-fill"></i></span>
        <span class="sidebar-link-text">Security (2FA)</span>
    </a>
    <a href="{{ route('password.change') }}" class="sidebar-link">
        <span class="sidebar-link-icon"><i class="bi bi-key-fill"></i></span>
        <span class="sidebar-link-text">Change Password</span>
    </a>
</div>
