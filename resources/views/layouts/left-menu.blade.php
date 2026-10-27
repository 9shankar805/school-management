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
    <a href="{{ route('school.setup') }}" class="sidebar-link {{ (request()->is('school/setup*') || request()->is('academics/settings*')) ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-gear-wide-connected text-amber-500"></i></span>
        <span class="sidebar-link-text">School Setup</span>
    </a>
    <a href="{{ route('central_analytics.index') }}" class="sidebar-link {{ request()->is('analytics*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-pie-chart-fill"></i></span>
        <span class="sidebar-link-text">Analytics</span>
    </a>
    <a href="{{ route('live-quiz.index') }}" class="sidebar-link {{ request()->is('live-quiz*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-trophy-fill text-warning"></i></span>
        <span class="sidebar-link-text">Live Quiz Arena</span>
    </a>
    <a href="{{ route('events.show') }}" class="sidebar-link {{ request()->is('calendar-event*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-calendar2-heart-fill text-rose-500"></i></span>
        <span class="sidebar-link-text">Academic Calendar (पात्रो)</span>
        <span class="sidebar-badge" style="background:#fee2e2;color:#dc2626;font-size:9px;font-weight:700;">BS &amp; AD</span>
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
    <a href="{{ route('profile.edit') }}" class="sidebar-link {{ request()->is('profile*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-person-gear"></i></span>
        <span class="sidebar-link-text">My Profile</span>
    </a>
    <a href="{{ route('roles.index') }}" class="sidebar-link {{ request()->is('roles') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-shield-fill-check"></i></span>
        <span class="sidebar-link-text">Roles & Permissions</span>
    </a>
    <a href="{{ route('two-factor.setup') }}" class="sidebar-link {{ request()->is('two-factor*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-shield-lock-fill"></i></span>
        <span class="sidebar-link-text">Security (2FA)</span>
    </a>
    <a href="{{ route('school.setup') }}" class="sidebar-link {{ (request()->is('academics/settings*') || request()->is('school/setup*')) ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-gear-wide-connected"></i></span>
        <span class="sidebar-link-text">School Setup</span>
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
    <a href="{{ route('school.setup') }}" class="sidebar-link {{ (request()->is('school/setup*') || request()->is('academics/settings*')) ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-gear-wide-connected text-amber-500"></i></span>
        <span class="sidebar-link-text">School Setup</span>
    </a>
    @role('admin|principal')
    <a href="{{ route('school.profile.edit') }}" class="sidebar-link {{ request()->is('school/profile') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-building-fill"></i></span>
        <span class="sidebar-link-text">School Profile</span>
    </a>
    <a href="{{ route('school.profile.payment-qr') }}" class="sidebar-link {{ request()->is('school/profile/payment-qr*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-qr-code-scan"></i></span>
        <span class="sidebar-link-text">Payment QR Codes</span>
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
    @can('view students')
    <a href="{{ route('iemis.export') }}" class="sidebar-link {{ request()->is('iemis/export*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-file-earmark-spreadsheet-fill"></i></span>
        <span class="sidebar-link-text">IEMIS Export</span>
    </a>
    @endcan
    @endrole
    <a href="{{ route('notifications.index') }}" class="sidebar-link {{ request()->is('notifications*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-bell-fill"></i></span>
        <span class="sidebar-link-text">Notifications</span>
        @php try { $unread = auth()->user()->unreadNotifications()->count(); } catch(\Throwable $e) { $unread = 0; } @endphp
        @if($unread > 0)
        <span class="sidebar-badge">{{ $unread > 99 ? '99+' : $unread }}</span>
        @endif
    </a>

    {{-- Students (Admin / Admissions only) --}}
    @unlessrole('student|parent|teacher|class-teacher')
    @canany(['view students','create students'])
    <p class="sidebar-section-label">Students & Admissions</p>
    <a href="{{ route('student.list.show') }}" class="sidebar-link {{ request()->is('students/view*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-people-fill"></i></span>
        <span class="sidebar-link-text">All Students</span>
    </a>
    <a href="{{ route('student.id-cards.bulk') }}" class="sidebar-link {{ request()->is('students/id-cards*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-person-vcard-fill text-indigo-400"></i></span>
        <span class="sidebar-link-text">Student ID Cards</span>
    </a>
    @can('create students')
    @unless(session()->has('browse_session_id'))
    <a href="{{ route('student.create.show') }}" class="sidebar-link {{ request()->is('students/add') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-person-plus-fill"></i></span>
        <span class="sidebar-link-text">Add Student</span>
    </a>
    <a href="{{ route('students.import.index') }}" class="sidebar-link {{ request()->is('students/bulk-import*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-file-earmark-spreadsheet-fill text-emerald-500"></i></span>
        <span class="sidebar-link-text">Bulk Import</span>
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
    <a href="{{ route('students.graduation.index') }}" class="sidebar-link {{ request()->is('graduation*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-mortarboard-fill"></i></span>
        <span class="sidebar-link-text">Graduation & Promotion</span>
    </a>
    <a href="{{ route('students.alumni') }}" class="sidebar-link {{ request()->is('alumni*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-person-check-fill"></i></span>
        <span class="sidebar-link-text">Alumni Directory</span>
    </a>
    <a href="{{ route('achievements.leaderboard') }}" class="sidebar-link {{ request()->is('achievements/leaderboard*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-trophy-fill text-amber-500"></i></span>
        <span class="sidebar-link-text">Student Leaderboard</span>
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
    <a href="{{ route('live-quiz.player.join') }}" target="_blank" class="sidebar-link">
        <span class="sidebar-link-icon"><i class="bi bi-broadcast text-warning"></i></span>
        <span class="sidebar-link-text">Join Live Quiz</span>
    </a>
    <a href="{{ route('study-notes.index') }}" class="sidebar-link {{ request()->is('study-notes*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-file-earmark-text-fill"></i></span>
        <span class="sidebar-link-text">Study Materials</span>
    </a>
    <a href="{{ route('online-classes.index') }}" class="sidebar-link {{ request()->is('online-classes*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-camera-video-fill"></i></span>
        <span class="sidebar-link-text">Online Classes</span>
    </a>
    <a href="{{ route('forum.index') }}" class="sidebar-link {{ request()->is('forum*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-chat-square-text-fill text-info"></i></span>
        <span class="sidebar-link-text">Discussion Forum</span>
    </a>
    <a href="{{ route('section.routine.show') }}" class="sidebar-link {{ request()->is('routine/view*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-calendar4-week"></i></span>
        <span class="sidebar-link-text">My Timetable</span>
    </a>
    <a href="{{ route('events.show') }}" class="sidebar-link {{ request()->is('calendar-event*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-calendar2-heart-fill text-rose-500"></i></span>
        <span class="sidebar-link-text">School Calendar (पात्रो)</span>
        <span class="sidebar-badge" style="background:#fee2e2;color:#dc2626;font-size:9px;font-weight:700;">BS &amp; AD</span>
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
    <a href="{{ route('feedback.my') }}" class="sidebar-link {{ request()->is('feedback*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-chat-heart-fill"></i></span>
        <span class="sidebar-link-text">Feedback & Help</span>
    </a>
    @endrole

    {{-- Parent --}}
    @role('parent')
    @php
        try { $parentChildren = auth()->user()->children()->get(); } catch(\Throwable $e) { $parentChildren = collect(); }
        $routeStudentId = request()->route('studentId');
        $activeChildId = $routeStudentId ?? request('child_id') ?? session('active_child_id') ?? $parentChildren->first()?->id;
        $activeChildObj = $parentChildren->firstWhere('id', $activeChildId) ?? $parentChildren->first();
        if ($activeChildObj) {
            session(['active_child_id' => $activeChildObj->id]);
            $activeChildId = $activeChildObj->id;
        }
    @endphp
    <p class="sidebar-section-label">My Children</p>

    @if($parentChildren->count() > 0 && $activeChildObj)
        {{-- Child Switcher Card with Avatar Picture in Sidebar --}}
        <div class="px-3 py-1.5 mb-2">
            <div style="background-color: #1e293b; border: 1px solid #334155; border-radius: 0.875rem; padding: 0.5rem 0.75rem; display: flex; align-items: center; justify-content: space-between; gap: 0.625rem; position: relative;">
                <div style="display: flex; align-items: center; gap: 0.625rem; min-width: 0; flex-grow: 1;">
                    <img src="{{ $activeChildObj->avatar }}" style="width: 2.125rem; height: 2.125rem; border-radius: 9999px; object-fit: cover; border: 2px solid #6366f1; flex-shrink: 0;" alt="{{ $activeChildObj->full_name }}">
                    <div style="min-width: 0; flex-grow: 1;">
                        <span style="color: #ffffff; font-size: 0.8125rem; font-weight: 800; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.2;">
                            {{ $activeChildObj->full_name }}
                        </span>
                    </div>
                </div>

                <div style="position: relative; flex-shrink: 0; display: flex; align-items: center;">
                    <select onchange="window.location.href='{{ route('home') }}?child_id=' + this.value"
                            style="background-color: #0f172a !important; color: #ffffff !important; border: 1px solid #334155 !important; font-weight: 700 !important; border-radius: 0.5rem !important; padding: 0.2rem 1.5rem 0.2rem 0.5rem !important; font-size: 0.75rem !important; cursor: pointer !important; appearance: none !important; -webkit-appearance: none !important;">
                        @foreach($parentChildren as $__c)
                        <option value="{{ $__c->id }}" {{ $activeChildId == $__c->id ? 'selected' : '' }} style="background-color: #0f172a !important; color: #ffffff !important;">
                            {{ $__c->full_name }}
                        </option>
                        @endforeach
                    </select>
                    <div style="position: absolute; right: 0.4rem; pointer-events: none; color: #94a3b8; font-size: 0.6875rem;">
                        <i class="bi bi-chevron-down"></i>
                    </div>
                </div>
            </div>
        </div>

        @if($activeChildId)
        <a href="{{ route('parent.attendance',  $activeChildId) }}" class="sidebar-link {{ request()->is('parent/'.$activeChildId.'/attendance*') ? 'active' : '' }}">
            <span class="sidebar-link-icon"><i class="bi bi-calendar-check-fill"></i></span>
            <span class="sidebar-link-text">Attendance</span>
        </a>
        <a href="{{ route('parent.results',     $activeChildId) }}" class="sidebar-link {{ request()->is('parent/'.$activeChildId.'/results*') ? 'active' : '' }}">
            <span class="sidebar-link-icon"><i class="bi bi-award-fill"></i></span>
            <span class="sidebar-link-text">Results</span>
        </a>
        <a href="{{ route('parent.homework',    $activeChildId) }}" class="sidebar-link {{ request()->is('parent/'.$activeChildId.'/homework*') ? 'active' : '' }}">
            <span class="sidebar-link-icon"><i class="bi bi-pencil-fill"></i></span>
            <span class="sidebar-link-text">Homework</span>
        </a>
        <a href="{{ route('parent.assignments', $activeChildId) }}" class="sidebar-link {{ request()->is('parent/'.$activeChildId.'/assignments*') ? 'active' : '' }}">
            <span class="sidebar-link-icon"><i class="bi bi-file-earmark-check-fill"></i></span>
            <span class="sidebar-link-text">Assignments</span>
        </a>
        <a href="{{ route('parent.timetable',   $activeChildId) }}" class="sidebar-link {{ request()->is('parent/'.$activeChildId.'/timetable*') ? 'active' : '' }}">
            <span class="sidebar-link-icon"><i class="bi bi-calendar4-week"></i></span>
            <span class="sidebar-link-text">Timetable</span>
        </a>
        <a href="{{ route('parent.online-classes', $activeChildId) }}" class="sidebar-link {{ request()->is('parent/'.$activeChildId.'/online-classes*') ? 'active' : '' }}">
            <span class="sidebar-link-icon"><i class="bi bi-camera-video-fill text-danger"></i></span>
            <span class="sidebar-link-text">Live Classes (Zoom)</span>
        </a>
        <a href="{{ route('parent.fees',        $activeChildId) }}" class="sidebar-link {{ request()->is('parent/'.$activeChildId.'/fees*') ? 'active' : '' }}">
            <span class="sidebar-link-icon"><i class="bi bi-receipt-cutoff"></i></span>
            <span class="sidebar-link-text">Fees</span>
        </a>
        <a href="{{ route('parent.transport',   $activeChildId) }}" class="sidebar-link {{ request()->is('parent/'.$activeChildId.'/transport*') ? 'active' : '' }}">
            <span class="sidebar-link-icon"><i class="bi bi-bus-front-fill"></i></span>
            <span class="sidebar-link-text">Transport</span>
        </a>
        <a href="{{ route('parent.leave',       $activeChildId) }}" class="sidebar-link {{ request()->is('parent/'.$activeChildId.'/leave*') ? 'active' : '' }}">
            <span class="sidebar-link-icon"><i class="bi bi-calendar-x-fill"></i></span>
            <span class="sidebar-link-text">Leave</span>
        </a>
        <a href="{{ route('parent.performance', $activeChildId) }}" class="sidebar-link {{ request()->is('parent/'.$activeChildId.'/performance*') ? 'active' : '' }}">
            <span class="sidebar-link-icon"><i class="bi bi-graph-up"></i></span>
            <span class="sidebar-link-text">Performance</span>
        </a>
        <a href="{{ route('parent.teachers',    $activeChildId) }}" class="sidebar-link {{ request()->is('parent/'.$activeChildId.'/teachers*') ? 'active' : '' }}">
            <span class="sidebar-link-icon"><i class="bi bi-person-badge"></i></span>
            <span class="sidebar-link-text">Teachers</span>
        </a>
        <a href="{{ route('parent.meetings',    $activeChildId) }}" class="sidebar-link {{ request()->is('parent/'.$activeChildId.'/meetings*') ? 'active' : '' }}">
            <span class="sidebar-link-icon"><i class="bi bi-calendar-event"></i></span>
            <span class="sidebar-link-text">Meetings</span>
        </a>
        <a href="{{ route('parent.events',      $activeChildId) }}" class="sidebar-link {{ request()->is('parent/'.$activeChildId.'/events*') ? 'active' : '' }}">
            <span class="sidebar-link-icon"><i class="bi bi-calendar-star"></i></span>
            <span class="sidebar-link-text">Events</span>
        </a>
        <a href="{{ route('events.show') }}" class="sidebar-link {{ request()->is('calendar-event*') ? 'active' : '' }}">
            <span class="sidebar-link-icon"><i class="bi bi-calendar2-heart-fill text-rose-500"></i></span>
            <span class="sidebar-link-text">School Calendar (पात्रो)</span>
            <span class="sidebar-badge" style="background:#fee2e2;color:#dc2626;font-size:9px;font-weight:700;">BS &amp; AD</span>
        </a>
        <a href="{{ route('parent.health',      $activeChildId) }}" class="sidebar-link {{ request()->is('parent/'.$activeChildId.'/health*') ? 'active' : '' }}">
            <span class="sidebar-link-icon"><i class="bi bi-heart-pulse"></i></span>
            <span class="sidebar-link-text">Health</span>
        </a>
        <a href="{{ route('parent.notices',     $activeChildId) }}" class="sidebar-link {{ request()->is('parent/'.$activeChildId.'/notices*') ? 'active' : '' }}">
            <span class="sidebar-link-icon"><i class="bi bi-megaphone-fill"></i></span>
            <span class="sidebar-link-text">Notice Board</span>
        </a>
        <a href="{{ route('parent.behavior',    $activeChildId) }}" class="sidebar-link {{ request()->is('parent/'.$activeChildId.'/behavior*') ? 'active' : '' }}">
            <span class="sidebar-link-icon"><i class="bi bi-shield-exclamation"></i></span>
            <span class="sidebar-link-text">Behavior</span>
        </a>
        <a href="{{ route('parent.documents',   $activeChildId) }}" class="sidebar-link {{ request()->is('parent/'.$activeChildId.'/documents*') ? 'active' : '' }}">
            <span class="sidebar-link-icon"><i class="bi bi-folder2-open"></i></span>
            <span class="sidebar-link-text">Documents</span>
        </a>
        @endif
    @endif

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
    <a href="{{ route('feedback.my') }}" class="sidebar-link {{ request()->is('feedback*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-chat-heart-fill"></i></span>
        <span class="sidebar-link-text">Feedback & Grievance</span>
    </a>
    @endrole

    {{-- Teachers (Admin / HR only) --}}
    @unlessrole('student|parent|teacher|class-teacher')
    @canany(['view teachers','create teachers'])
    <p class="sidebar-section-label">Teachers</p>
    <a href="{{ route('teacher.list.show') }}" class="sidebar-link {{ request()->is('teachers/view/list') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-person-badge-fill"></i></span>
        <span class="sidebar-link-text">All Teachers</span>
    </a>
    <a href="{{ route('teacher.id-cards.bulk') }}" class="sidebar-link {{ request()->is('teachers/id-cards*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-person-vcard text-blue-400"></i></span>
        <span class="sidebar-link-text">Teacher ID Cards</span>
    </a>
    @can('create teachers')
    @unless(session()->has('browse_session_id'))
    <a href="{{ route('teacher.create.show') }}" class="sidebar-link">
        <span class="sidebar-link-icon"><i class="bi bi-person-plus-fill"></i></span>
        <span class="sidebar-link-text">Add Teacher</span>
    </a>
    <a href="{{ route('teachers.import.index') }}" class="sidebar-link {{ request()->is('teachers/bulk-import*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-file-earmark-person-fill text-emerald-500"></i></span>
        <span class="sidebar-link-text">Bulk Import</span>
    </a>
    @endunless
    @endcan
    @endcanany
    @endunlessrole

    {{-- Teacher self --}}
    @role('teacher|class-teacher')
    <p class="sidebar-section-label">Teaching & Classes</p>
    @if(auth()->user()->isClassTeacher() || auth()->user()->hasRole(['super-admin', 'admin', 'principal']))
    <a href="{{ route('class-incharge.dashboard') }}" class="sidebar-link {{ request()->is('class-incharge*') ? 'active' : '' }}" style="background: rgba(245, 158, 11, 0.12); border-left: 3px solid #f59e0b;">
        <span class="sidebar-link-icon text-amber-500"><i class="bi bi-star-fill"></i></span>
        <span class="sidebar-link-text font-bold text-amber-700 dark:text-amber-400">Class In-Charge</span>
    </a>
    @endif
    <a href="{{ route('course.teacher.list.show', ['teacher_id' => auth()->id()]) }}" class="sidebar-link {{ request()->is('courses/teacher*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-journal-richtext text-indigo-500"></i></span>
        <span class="sidebar-link-text">My Courses</span>
    </a>
    <a href="{{ route('routine.teacher-timetable') }}" class="sidebar-link {{ request()->is('routine/teacher-timetable*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-calendar4-week text-sky-500"></i></span>
        <span class="sidebar-link-text">My Timetable</span>
    </a>
    <a href="{{ route('lesson-plans.index') }}" class="sidebar-link {{ request()->is('lesson-plans*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-journal-plus text-emerald-500"></i></span>
        <span class="sidebar-link-text">Lesson Plans</span>
    </a>
    <a href="{{ route('homework.index') }}" class="sidebar-link {{ request()->is('homework*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-pencil-fill text-amber-500"></i></span>
        <span class="sidebar-link-text">Homework</span>
    </a>
    <a href="{{ route('assignment.teacher.index') }}" class="sidebar-link {{ request()->is('assignments*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-file-earmark-check-fill text-teal-500"></i></span>
        <span class="sidebar-link-text">Assignments</span>
    </a>
    <a href="{{ route('projects.index') }}" class="sidebar-link {{ request()->is('projects*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-folder-fill text-purple-500"></i></span>
        <span class="sidebar-link-text">Projects</span>
    </a>
    <a href="{{ route('study-notes.index') }}" class="sidebar-link {{ request()->is('study-notes*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-file-earmark-text-fill text-blue-500"></i></span>
        <span class="sidebar-link-text">Study Materials</span>
    </a>
    <a href="{{ route('question-papers.index') }}" class="sidebar-link {{ request()->is('question-papers*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-file-earmark-richtext-fill text-amber-500"></i></span>
        <span class="sidebar-link-text">Question Studio (AI)</span>
    </a>
    <a href="{{ route('class.syllabus.create') }}" class="sidebar-link {{ request()->is('syllabus*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-journal-bookmark-fill text-pink-500"></i></span>
        <span class="sidebar-link-text">Syllabus</span>
    </a>
    <a href="{{ route('online-classes.index') }}" class="sidebar-link {{ request()->is('online-classes*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-camera-video-fill text-rose-500"></i></span>
        <span class="sidebar-link-text">Online Classes</span>
    </a>
    <a href="{{ route('forum.index') }}" class="sidebar-link {{ request()->is('forum*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-chat-square-text-fill text-info"></i></span>
        <span class="sidebar-link-text">Discussion Forum</span>
    </a>

    <p class="sidebar-section-label">Attendance</p>
    <a href="{{ route('attendance.create.show') }}" class="sidebar-link {{ request()->is('attendances/take') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-check2-square text-emerald-500"></i></span>
        <span class="sidebar-link-text">Take Attendance</span>
    </a>
    <a href="{{ route('attendance.list.show') }}" class="sidebar-link {{ request()->is('attendances/view') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-calendar-week-fill text-indigo-500"></i></span>
        <span class="sidebar-link-text">View Attendance</span>
    </a>
    <a href="{{ route('attendance.qr.index') }}" class="sidebar-link {{ request()->is('attendance/qr*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-qr-code text-violet-500"></i></span>
        <span class="sidebar-link-text">QR Attendance</span>
    </a>

    <p class="sidebar-section-label">Assessments & Marks</p>
    <a href="{{ route('course.mark.create') }}" class="sidebar-link {{ request()->is('marks/create*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-pencil-square text-indigo-500"></i></span>
        <span class="sidebar-link-text">Enter Marks</span>
    </a>
    <a href="{{ route('online-exams.index') }}" class="sidebar-link {{ request()->is('online-exams*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-laptop-fill text-blue-500"></i></span>
        <span class="sidebar-link-text">Online Exams</span>
    </a>
    <a href="{{ route('live-quiz.index') }}" class="sidebar-link {{ request()->is('live-quiz*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-trophy-fill text-warning"></i></span>
        <span class="sidebar-link-text">Live Quiz Arena</span>
    </a>
    <a href="{{ route('practical-exams.index') }}" class="sidebar-link {{ request()->is('practical-exams*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-nut-fill text-orange-500"></i></span>
        <span class="sidebar-link-text">Practical Exams</span>
    </a>
    <a href="{{ route('exam.schedule.index') }}" class="sidebar-link {{ request()->is('exam/schedule*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-calendar2-week-fill text-slate-500"></i></span>
        <span class="sidebar-link-text">Exam Schedule</span>
    </a>
    <a href="{{ route('gradebook.index') }}" class="sidebar-link {{ request()->is('gradebook*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-table text-cyan-500"></i></span>
        <span class="sidebar-link-text">Gradebook</span>
    </a>

    <p class="sidebar-section-label">My Work & Resources</p>
    <a href="{{ route('teacher.messages') }}" class="sidebar-link {{ request()->is('teacher/messages*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-chat-dots-fill text-blue-500"></i></span>
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
    <a href="{{ route('teacher.payroll.index') }}" class="sidebar-link {{ request()->is('teacher/payroll*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-cash-stack text-emerald-500"></i></span>
        <span class="sidebar-link-text">My Payslips</span>
    </a>
    <a href="{{ route('leave.index') }}" class="sidebar-link {{ request()->is('leave*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-calendar-x-fill text-rose-500"></i></span>
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
    <a href="{{ route('student.discipline.index') }}" class="sidebar-link {{ request()->is('discipline*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-shield-exclamation text-amber-500"></i></span>
        <span class="sidebar-link-text">Conduct Log</span>
    </a>
    <a href="{{ route('library.ebooks.index') }}" class="sidebar-link {{ request()->is('library/ebooks*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-book-half text-teal-500"></i></span>
        <span class="sidebar-link-text">E-books</span>
    </a>
    <a href="{{ route('events.show') }}" class="sidebar-link {{ request()->is('calendar-event*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-calendar-event-fill text-red-500"></i></span>
        <span class="sidebar-link-text">School Calendar</span>
    </a>
    <a href="{{ route('ai_features.web.index') }}" class="sidebar-link {{ request()->is('ai-features') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-robot text-purple-500"></i></span>
        <span class="sidebar-link-text">AI Tools</span>
        <span class="ml-1 text-[9px] bg-indigo-500 text-white px-1.5 py-0.5 rounded-full font-bold">Free</span>
    </a>
    <a href="{{ route('teacher.profile.show', auth()->id()) }}" class="sidebar-link {{ request()->is('teachers/view/profile/'.auth()->id()) ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-person-circle text-slate-500"></i></span>
        <span class="sidebar-link-text">My Profile</span>
    </a>
    <a href="{{ route('feedback.my') }}" class="sidebar-link {{ request()->is('feedback*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-chat-heart-fill text-rose-400"></i></span>
        <span class="sidebar-link-text">Feedback & Grievance</span>
    </a>
    @endrole

    {{-- Academic — hide for teacher/class-teacher/student/parent --}}
    @unlessrole('teacher|class-teacher|student|parent')
    @canany(['view classes','view courses','view routines','view syllabi','view academic settings'])
    <p class="sidebar-section-label">Academic</p>
    <a href="{{ route('events.show') }}" class="sidebar-link {{ request()->is('calendar-event*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-calendar2-heart-fill text-rose-500"></i></span>
        <span class="sidebar-link-text">Academic Calendar (पात्रो)</span>
        <span class="sidebar-badge" style="background:#fee2e2;color:#dc2626;font-size:9px;font-weight:700;">BS &amp; AD</span>
    </a>
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
    <a href="{{ route('roll-numbers.index') }}" class="sidebar-link {{ request()->is('roll-numbers*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-card-heading"></i></span>
        <span class="sidebar-link-text">Roll Numbers</span>
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
    <a href="{{ route('live-quiz.index') }}" class="sidebar-link {{ request()->is('live-quiz*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-trophy-fill text-warning"></i></span>
        <span class="sidebar-link-text">Live Quiz Arena</span>
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
    @unlessrole('student|parent|teacher|class-teacher')
    @canany(['manage finance','view invoices','create invoices','view payments','view own invoices'])
    <p class="sidebar-section-label">Finance</p>
    @can('manage finance')
    <a href="{{ route('finance.overview') }}" class="sidebar-link {{ request()->is('finance') && !request()->is('finance/pos*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-graph-up-arrow"></i></span>
        <span class="sidebar-link-text">Overview</span>
    </a>
    <a href="{{ route('finance.pos') }}" class="sidebar-link {{ request()->is('finance/pos*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-terminal"></i></span>
        <span class="sidebar-link-text font-bold text-emerald-600 dark:text-emerald-400">⚡ Fee POS (Counter)</span>
    </a>
    <a href="{{ route('finance.fee-profiles') }}" class="sidebar-link {{ request()->is('finance/fee-profiles*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-person-vcard"></i></span>
        <span class="sidebar-link-text">Fee Profiles</span>
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
    @role('admin|principal|accountant')
    <a href="{{ route('school.profile.payment-qr') }}" class="sidebar-link {{ request()->is('school/profile/payment-qr*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-qr-code-scan"></i></span>
        <span class="sidebar-link-text">Payment QR Codes</span>
    </a>
    @php try { $pendingProofCount = \App\Models\PaymentProof::where('status','pending')->when(auth()->user()->school_id, fn($q) => $q->where('school_id',auth()->user()->school_id))->count(); } catch(\Throwable $e){ $pendingProofCount=0; } @endphp
    <a href="{{ route('payment-proofs.index') }}" class="sidebar-link {{ request()->is('payment-proofs*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-shield-check {{ $pendingProofCount > 0 ? 'text-amber-500' : '' }}"></i></span>
        <span class="sidebar-link-text">QR Proof Approvals</span>
        @if($pendingProofCount > 0)
        <span class="sidebar-badge">{{ $pendingProofCount }}</span>
        @endif
    </a>
    @endrole
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
    <a href="{{ route('finance.tds.index') }}" class="sidebar-link {{ request()->is('finance/tds*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-bank2"></i></span>
        <span class="sidebar-link-text">TDS Remittance</span>
    </a>
    <a href="{{ route('finance.ledger') }}" class="sidebar-link {{ request()->is('finance/ledger*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-journal-bookmark-fill"></i></span>
        <span class="sidebar-link-text">Accounting Ledger</span>
    </a>
    <a href="{{ route('finance.day-book') }}" class="sidebar-link {{ request()->is('finance/day-book*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-book-half"></i></span>
        <span class="sidebar-link-text">Day Book (रोजनाम्चा)</span>
    </a>
    <a href="{{ route('finance.profit-loss') }}" class="sidebar-link {{ request()->is('finance/profit-loss*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-bar-chart-fill"></i></span>
        <span class="sidebar-link-text">Profit & Loss</span>
    </a>
    <a href="{{ route('finance.balance-sheet') }}" class="sidebar-link {{ request()->is('finance/balance-sheet*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-clipboard2-data-fill"></i></span>
        <span class="sidebar-link-text">Balance Sheet</span>
    </a>
    <a href="{{ route('finance.reports') }}" class="sidebar-link {{ request()->is('finance/reports*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-file-earmark-bar-graph-fill"></i></span>
        <span class="sidebar-link-text">Finance Reports</span>
    </a>
    @endcan
    @endcanany
    @endunlessrole

    {{-- Library --}}
    @unlessrole('student|parent|teacher|class-teacher')
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
    @unlessrole('student|parent|teacher|class-teacher')
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
    @unlessrole('student|parent|teacher|class-teacher')
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
    @unlessrole('student|parent|teacher|class-teacher')
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
        <span class="sidebar-link-icon"><i class="bi bi-speedometer2"></i></span>
        <span class="sidebar-link-text">HR Dashboard</span>
    </a>
    <a href="{{ route('staff.index') }}" class="sidebar-link {{ request()->is('staff') || (request()->is('staff/*') && !request()->is('staff/attendance*') && !request()->is('staff/contracts*') && !request()->is('staff/training*') && !request()->is('staff/exit*') && !request()->is('staff/recruitment*') && !request()->is('staff/create')) ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-people-fill"></i></span>
        <span class="sidebar-link-text">Staff Directory</span>
    </a>
    @can('create staff')
    <a href="{{ route('staff.create') }}" class="sidebar-link {{ request()->is('staff/create') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-person-plus-fill"></i></span>
        <span class="sidebar-link-text">Add Staff / User</span>
    </a>
    @endcan
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

    {{-- Reception & Front Desk --}}
    @unlessrole('student|parent|teacher|class-teacher')
    @canany(['view staff','create staff','view academic settings'])
    <p class="sidebar-section-label">Reception & Front Desk</p>
    <a href="{{ route('visitors.index') }}" class="sidebar-link {{ request()->is('visitors*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-person-badge-fill"></i></span>
        <span class="sidebar-link-text">Visitor Log & Passes</span>
    </a>
    @endcanany
    @endunlessrole

    {{-- Inventory --}}
    @unlessrole('student|parent|teacher|class-teacher')
    @canany(['view inventory','manage inventory'])
    <p class="sidebar-section-label">Inventory & Assets</p>
    <a href="{{ route('inventory.dashboard') }}" class="sidebar-link {{ request()->is('inventory') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-speedometer2"></i></span>
        <span class="sidebar-link-text">Inventory Overview</span>
    </a>
    <a href="{{ route('inventory.items') }}" class="sidebar-link {{ request()->is('inventory/items*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-boxes"></i></span>
        <span class="sidebar-link-text">Stock Items</span>
    </a>
    <a href="{{ route('inventory.assets') }}" class="sidebar-link {{ request()->is('inventory/assets*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-laptop"></i></span>
        <span class="sidebar-link-text">Fixed Assets</span>
    </a>
    <a href="{{ route('inventory.suppliers') }}" class="sidebar-link {{ request()->is('inventory/suppliers*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-truck"></i></span>
        <span class="sidebar-link-text">Suppliers</span>
    </a>
    <a href="{{ route('inventory.purchase-orders') }}" class="sidebar-link {{ request()->is('inventory/purchase-orders*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-cart-check"></i></span>
        <span class="sidebar-link-text">Purchase Orders</span>
    </a>
    @endcanany
    @endunlessrole

    {{-- Communication --}}
    @unlessrole('student|parent|teacher|class-teacher')
    @canany(['view notices','create notices','view events'])
    <p class="sidebar-section-label">Communication</p>
    @can('create notices')
    <a href="{{ route('notice.index') }}" class="sidebar-link {{ request()->is('notice*') ? 'active' : '' }}">
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
    <a href="{{ route('forum.index') }}" class="sidebar-link {{ request()->is('forum*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-chat-square-text-fill text-info"></i></span>
        <span class="sidebar-link-text">Discussion Forum</span>
    </a>
    <a href="{{ route('feedback.index') }}" class="sidebar-link {{ request()->is('feedback*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-chat-heart-fill text-warning"></i></span>
        <span class="sidebar-link-text">Feedback & Grievances</span>
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
    <a href="{{ route('school.setup') }}" class="sidebar-link {{ (request()->is('academics/settings*') || request()->is('school/setup*')) ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-gear-wide-connected"></i></span>
        <span class="sidebar-link-text">School Setup</span>
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
    <a href="{{ route('profile.edit') }}" class="sidebar-link {{ request()->is('profile*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-person-gear"></i></span>
        <span class="sidebar-link-text">My Profile</span>
    </a>
    <a href="{{ route('two-factor.setup') }}" class="sidebar-link {{ request()->is('two-factor*') ? 'active' : '' }}">
        <span class="sidebar-link-icon"><i class="bi bi-shield-lock-fill"></i></span>
        <span class="sidebar-link-text">Security (2FA)</span>
    </a>
    <a href="{{ route('password.change') }}" class="sidebar-link">
        <span class="sidebar-link-icon"><i class="bi bi-key-fill"></i></span>
        <span class="sidebar-link-text">Change Password</span>
    </a>
</div>
