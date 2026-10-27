/**
 * app.js — School ERP Modern UI  (Module 20)
 * ─────────────────────────────────────────────
 * Bundled with Laravel Mix / webpack.
 * Covers:
 *   1. Dark mode  (persist in localStorage)
 *   2. Sidebar (mobile open/close, desktop collapse)
 *   3. Notification panel toggle
 *   4. User menu dropdown toggle
 *   5. Toast notification system
 *   6. Command palette  (Cmd/Ctrl + K)
 *   7. Floating Action Button (FAB)
 *   8. Keyboard shortcuts
 *   9. Auto-show session toasts from DOM markers
 *  10. Table column sorting (client-side)
 *  11. Inline search/filter for tables
 *  12. Skeleton loader helpers
 *  13. Confirm-delete helper
 *  14. Tooltip init (title → custom tooltip)
 */

'use strict';

/* ============================================================
   1. DARK MODE
   ============================================================ */
const THEME_KEY = 'erp_theme';

function getTheme() {
    return localStorage.getItem(THEME_KEY) || 'light';
}

function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    if (theme === 'dark') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
    localStorage.setItem(THEME_KEY, theme);
}

function toggleTheme() {
    applyTheme(getTheme() === 'dark' ? 'light' : 'dark');
}

// Apply on load (also done inline in <head> to avoid flash)
applyTheme(getTheme());

/* ============================================================
   2. SIDEBAR  (mobile + desktop collapse)
   ============================================================ */
const SIDEBAR_COLLAPSED_KEY = 'erp_sidebar_collapsed';

function openSidebar() {
    const sidebar  = document.getElementById('sidebar');
    const overlay  = document.getElementById('sidebar-overlay');
    if (!sidebar) return;
    sidebar.classList.remove('-translate-x-full');
    if (overlay) overlay.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeSidebar() {
    const sidebar  = document.getElementById('sidebar');
    const overlay  = document.getElementById('sidebar-overlay');
    if (!sidebar) return;
    sidebar.classList.add('-translate-x-full');
    if (overlay) overlay.classList.add('hidden');
    document.body.style.overflow = '';
}

function toggleSidebarCollapse() {
    const shell     = document.getElementById('app-shell');
    const sidebar   = document.getElementById('sidebar');
    const colBtn    = document.getElementById('sidebar-collapse-btn');
    if (!shell || !sidebar) return;

    const isCollapsed = shell.classList.toggle('sidebar-collapsed');
    localStorage.setItem(SIDEBAR_COLLAPSED_KEY, isCollapsed ? '1' : '0');

    if (colBtn) {
        colBtn.setAttribute('title', isCollapsed ? 'Expand sidebar (⌘B)' : 'Collapse sidebar (⌘B)');
        colBtn.querySelector('i').className = isCollapsed ? 'bi bi-layout-sidebar-reverse text-base' : 'bi bi-layout-sidebar text-base';
    }
}

function restoreSidebarState() {
    if (localStorage.getItem(SIDEBAR_COLLAPSED_KEY) === '1') {
        const shell = document.getElementById('app-shell');
        if (shell) shell.classList.add('sidebar-collapsed');
    }
}

/* ============================================================
   3. NOTIFICATION PANEL DROPDOWN
   ============================================================ */
function toggleNotifPanel() {
    const panel = document.getElementById('notif-panel');
    if (!panel) return;
    const isHidden = panel.classList.toggle('hidden');
    if (!isHidden) {
        // close user menu if open
        const um = document.getElementById('user-menu-panel');
        if (um) um.classList.add('hidden');
    }
}

/* ============================================================
   4. USER MENU DROPDOWN
   ============================================================ */
function toggleUserMenu() {
    const panel = document.getElementById('user-menu-panel');
    if (!panel) return;
    const isHidden = panel.classList.toggle('hidden');
    if (!isHidden) {
        // close notif panel if open
        const np = document.getElementById('notif-panel');
        if (np) np.classList.add('hidden');
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function (e) {
    // Notification panel
    const nw = document.getElementById('notif-wrapper');
    const np = document.getElementById('notif-panel');
    if (np && nw && !nw.contains(e.target)) {
        np.classList.add('hidden');
    }
    // User menu
    const uw = document.getElementById('user-menu-wrapper');
    const um = document.getElementById('user-menu-panel');
    if (um && uw && !uw.contains(e.target)) {
        um.classList.add('hidden');
    }
    // FAB
    const fab = document.getElementById('fab-group');
    if (fab && !fab.contains(e.target)) {
        closeFab();
    }
});

/* ============================================================
   5. TOAST SYSTEM
   ============================================================ */
const TOAST_ICONS = {
    success: '<i class="bi bi-check-lg"></i>',
    error:   '<i class="bi bi-x-lg"></i>',
    warning: '<i class="bi bi-exclamation-lg"></i>',
    info:    '<i class="bi bi-info-lg"></i>',
};

const TOAST_TITLES = {
    success: 'Success',
    error:   'Error',
    warning: 'Warning',
    info:    'Info',
};

/**
 * Show a toast notification.
 * @param {string} msg     - Message text
 * @param {string} type    - 'success' | 'error' | 'warning' | 'info'
 * @param {string} [title] - Optional override title
 * @param {number} [duration] - ms before auto-dismiss (0 = persistent)
 */
function showToast(msg, type = 'info', title = null, duration = 4000) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const t = document.createElement('div');
    t.className = `toast-item toast-${type}`;
    t.innerHTML = `
        <div class="toast-icon">${TOAST_ICONS[type] || TOAST_ICONS.info}</div>
        <div class="toast-body">
            <div class="toast-title">${title || TOAST_TITLES[type] || 'Notice'}</div>
            <div class="toast-msg">${msg}</div>
        </div>
        <button class="toast-close" onclick="dismissToast(this.closest('.toast-item'))"
                aria-label="Dismiss">
            <i class="bi bi-x"></i>
        </button>`;

    container.appendChild(t);

    // Trigger transition
    requestAnimationFrame(() => {
        requestAnimationFrame(() => t.classList.add('toast-show'));
    });

    if (duration > 0) {
        setTimeout(() => dismissToast(t), duration);
    }

    return t;
}

function dismissToast(el) {
    if (!el) return;
    el.classList.remove('toast-show');
    el.classList.add('toast-hide');
    setTimeout(() => el.remove(), 350);
}

// Auto-show toasts injected from Blade (session flashes)
function initAutoToasts() {
    document.querySelectorAll('.toast-auto-show').forEach(function (el) {
        const type = el.dataset.type || 'info';
        const msg  = el.dataset.msg  || '';
        if (msg) showToast(msg, type);
        el.remove();
    });
}

/* ============================================================
   6. COMMAND PALETTE  (Cmd/Ctrl + K)
   ============================================================ */

// Registry of navigable commands (static + dynamically extendable)
const CMD_REGISTRY = [
    // ── Academic Management ──
    { label: 'Classes & Sections',          icon: 'bi-diagram-3-fill',         category: 'Academic', href: '/classes',                  keywords: ['class', 'classes', 'section', 'sections', 'grade', 'grades', 'room', 'rooms', 'classroom', 'class & section', 'classes & sections'] },
    { label: 'Courses & Subjects',          icon: 'bi-book-fill',              category: 'Academic', href: '/classes',                  keywords: ['course', 'courses', 'subject', 'subjects', 'curriculum', 'book', 'theory', 'practical', 'course & subject', 'courses & subjects'] },
    { label: 'Add New Course / Subject',    icon: 'bi-journal-plus',           category: 'Academic', href: '/courses/create',           keywords: ['add course', 'new course', 'add subject', 'new subject', 'create course', 'create subject'] },
    { label: 'Timetable',                   icon: 'bi-calendar4-range',        category: 'Academic', href: '/routine',                 keywords: ['routine', 'timetable', 'schedule', 'periods', 'time table', 'class schedule', 'bell schedule', 'routines'] },
    { label: 'Academic Programs',           icon: 'bi-mortarboard-fill',       category: 'Academic', href: '/programs',                keywords: ['program', 'programs', 'stream', 'science', 'management', 'humanities', 'faculty', 'academic programs'] },
    { label: 'Departments',                 icon: 'bi-building',               category: 'Academic', href: '/departments',             keywords: ['department', 'departments', 'dept', 'academic departments', 'faculty dept', 'faculty departments'] },
    { label: 'Academic Terms',              icon: 'bi-calendar-range',         category: 'Academic', href: '/terms',                   keywords: ['terms', 'term', 'semester', 'semesters', 'session', 'academic session', 'quarter', 'academic terms'] },
    { label: 'Curriculum',                  icon: 'bi-journal-richtext',       category: 'Academic', href: '/curriculums',             keywords: ['curriculum', 'curriculums', 'textbook', 'syllabus', 'lessons', 'course plan'] },
    { label: 'Syllabus',                    icon: 'bi-journal-bookmark-fill',  category: 'Academic', href: '/syllabus',                keywords: ['syllabus', 'syllabi', 'course outline', 'download syllabus', 'curriculum outline'] },
    { label: 'Lesson Plans',                icon: 'bi-journal-plus',           category: 'Academic', href: '/lesson-plans',            keywords: ['lesson plan', 'lesson plans', 'lesson', 'teaching plan', 'lecture notes'] },
    { label: 'Online Classes',              icon: 'bi-camera-video-fill',      category: 'Academic', href: '/online-classes',          keywords: ['online class', 'online classes', 'zoom', 'google meet', 'live class', 'virtual classroom', 'video class'] },
    { label: 'Promotions',                  icon: 'bi-graph-up-arrow',         category: 'Academic', href: '/promotions',              keywords: ['promotion', 'promotions', 'upgrade', 'next grade', 'promote students', 'pass class', 'next class'] },
    { label: 'Roll Numbers',                icon: 'bi-card-heading',           category: 'Academic', href: '/roll-numbers',            keywords: ['roll numbers', 'roll number', 'roll no', 'symbol number', 'student roll', 'assign roll'] },

    // ── Students & Admissions ──
    { label: 'Students Directory',          icon: 'bi-people-fill',            category: 'Students', href: '/students/view',           keywords: ['students', 'student list', 'scholars', 'pupil', 'view students'], shortcut: 'G S' },
    { label: 'Add New Student (Admission)', icon: 'bi-person-plus-fill',       category: 'Students', href: '/students/add',            keywords: ['add student', 'enroll', 'new admission', 'register student', 'new student'] },
    { label: 'Admissions & Inquiries',      icon: 'bi-inboxes-fill',           category: 'Students', href: '/admissions',              keywords: ['admission', 'admissions', 'inquiries', 'leads', 'applications', 'online apply'] },
    { label: 'Student ID Cards Generator',  icon: 'bi-qr-code-scan',          category: 'Students', href: '/student/id-cards/bulk',   keywords: ['id card', 'id cards', 'badge', 'identity card', 'print id', 'qr code'] },
    { label: 'Certificates & Marksheets',   icon: 'bi-patch-check-fill',       category: 'Students', href: '/certificates',            keywords: ['certificate', 'character certificate', 'transfer certificate', 'tc', 'transcript'] },
    { label: 'Disciplinary Records',        icon: 'bi-shield-exclamation',     category: 'Students', href: '/discipline',              keywords: ['discipline', 'behavior', 'incident', 'conduct', 'infraction', 'warning'] },
    { label: 'Student Houses & Clubs',      icon: 'bi-flag-fill',              category: 'Students', href: '/houses',                  keywords: ['house', 'houses', 'clubs', 'eca', 'sports team'] },

    // ── Teachers & Human Resources ──
    { label: 'Teachers & Faculty',          icon: 'bi-person-badge-fill',      category: 'Staff',    href: '/teachers/view/list',      keywords: ['teachers', 'teacher list', 'faculty', 'instructors', 'educators', 'staff'], shortcut: 'G T' },
    { label: 'Add New Teacher',             icon: 'bi-person-plus',            category: 'Staff',    href: '/teachers/add',            keywords: ['add teacher', 'new teacher', 'hire', 'onboard teacher'] },
    { label: 'Staff Daily Attendance',      icon: 'bi-person-check-fill',      category: 'Staff',    href: '/staff/attendance',        keywords: ['staff attendance', 'teacher attendance', 'punch in', 'clock in', 'faculty attendance'] },
    { label: 'Staff Leave Applications',    icon: 'bi-calendar2-minus-fill',   category: 'Staff',    href: '/staff-leaves',            keywords: ['staff leave', 'teacher leave', 'leave request', 'day off'] },
    { label: 'HR & Employee Management',    icon: 'bi-person-workspace',       category: 'Staff',    href: '/hr',                      keywords: ['hr', 'human resources', 'staff directory', 'contracts', 'designation'] },
    { label: 'Payroll & Salary Slips',      icon: 'bi-cash-coin',              category: 'Staff',    href: '/payroll',                 keywords: ['payroll', 'salary', 'payslip', 'wages', 'salary payment', 'allowance'] },

    // ── Attendance ──
    { label: 'Take Student Attendance',     icon: 'bi-check2-square',          category: 'Attendance', href: '/attendances/take',      keywords: ['take attendance', 'mark attendance', 'roll call', 'present', 'absent'], shortcut: 'G A' },
    { label: 'View Attendance Records',     icon: 'bi-calendar-check-fill',    category: 'Attendance', href: '/attendances/view',      keywords: ['view attendance', 'attendance report', 'monthly attendance', 'shortage'] },
    { label: 'QR Code Attendance Scanner',  icon: 'bi-upc-scan',               category: 'Attendance', href: '/attendance/qr',         keywords: ['qr attendance', 'scan attendance', 'barcode attendance', 'kiosk'] },

    // ── Examinations & Results ──
    { label: 'Examinations List',           icon: 'bi-file-earmark-text-fill', category: 'Exams',    href: '/exams/view',              keywords: ['exams', 'exam list', 'term exam', 'annual exam', 'unit test'] },
    { label: 'Create New Examination',      icon: 'bi-file-earmark-plus-fill', category: 'Exams',    href: '/exams/create',            keywords: ['create exam', 'add exam', 'schedule exam', 'new test'] },
    { label: 'Marks Entry Portal',          icon: 'bi-pencil-square',          category: 'Exams',    href: '/marks/create',            keywords: ['marks', 'mark entry', 'enter marks', 'grading', 'scores', 'input marks'] },
    { label: 'Results & Gradebook',         icon: 'bi-award-fill',             category: 'Exams',    href: '/results',                 keywords: ['results', 'result sheet', 'report card', 'gpa', 'transcript', 'rank', 'gradebook'], shortcut: 'G R' },
    { label: 'Grading System & GPA Scale',  icon: 'bi-percent',                category: 'Exams',    href: '/exam/grade/system/index', keywords: ['grading system', 'gpa scale', 'letter grade', 'percentage scale'] },
    { label: 'AI Question Paper Generator', icon: 'bi-stars',                  category: 'Exams',    href: '/question-papers/create',  keywords: ['ai paper', 'question paper', 'exam paper generator', 'generate quiz', 'test paper'] },
    { label: 'Online CBT Examinations',     icon: 'bi-laptop-fill',            category: 'Exams',    href: '/online-exams',            keywords: ['online exam', 'cbt', 'computer exam', 'mock test', 'online test'] },
    { label: 'Live Quiz Classroom',         icon: 'bi-trophy-fill',            category: 'Exams',    href: '/live-quiz',               keywords: ['live quiz', 'kahoot', 'gamified quiz', 'quiz battle', 'game'] },
    { label: 'Homework & Assignments',      icon: 'bi-journal-check',          category: 'Exams',    href: '/homework',                keywords: ['homework', 'assignments', 'daily homework', 'tasks', 'submissions'] },

    // ── Finance & Fees ──
    { label: 'Fee Invoices & Payments',     icon: 'bi-receipt-cutoff',         category: 'Finance',  href: '/payments',                keywords: ['fees', 'fee', 'invoices', 'payments', 'fee collection', 'dues', 'receipt', 'tuition'], shortcut: 'G F' },
    { label: 'Fee Structures & Heads',      icon: 'bi-wallet2',                category: 'Finance',  href: '/fee-structures',          keywords: ['fee structure', 'fee heads', 'monthly fee', 'transport fee', 'admission fee'] },
    { label: 'Fee Discounts & Waivers',     icon: 'bi-tags-fill',              category: 'Finance',  href: '/fee-discounts',           keywords: ['discounts', 'scholarships', 'waivers', 'concessions', 'financial aid'] },
    { label: 'Accounting, Income & Expense',icon: 'bi-cash-stack',             category: 'Finance',  href: '/finance',                 keywords: ['accounting', 'income', 'expenses', 'cash flow', 'vouchers', 'ledger'] },

    // ── Communication & Notices ──
    { label: 'Notice Board & Circulars',    icon: 'bi-megaphone-fill',         category: 'Communication', href: '/notice/create',      keywords: ['notices', 'notice', 'circular', 'announcements', 'bulletin', 'news'], shortcut: 'G N' },
    { label: 'SMS & WhatsApp Messages',     icon: 'bi-chat-dots-fill',         category: 'Communication', href: '/communication',      keywords: ['sms', 'whatsapp', 'bulk sms', 'broadcast', 'messages', 'push notification'] },
    { label: 'Event & Holiday Calendar',    icon: 'bi-calendar-event-fill',    category: 'Communication', href: '/events',             keywords: ['events', 'calendar', 'holidays', 'academic calendar', 'vacations', 'annual day'] },
    { label: 'Parent Portal & Messaging',   icon: 'bi-people',                 category: 'Communication', href: '/parents',            keywords: ['parents', 'parent portal', 'guardian', 'parent communication'] },
    { label: 'Feedback & Grievance Desk',   icon: 'bi-chat-heart-fill',        category: 'Communication', href: '/feedback',           keywords: ['feedback', 'grievance', 'complaints', 'suggestion box', 'tickets'] },

    // ── Facilities & Operations ──
    { label: 'Library & Book Catalog',      icon: 'bi-bookshelf',              category: 'Operations', href: '/library',               keywords: ['library', 'books', 'issue book', 'return book', 'catalog', 'isbn'], shortcut: 'G L' },
    { label: 'Transport & Fleet Routes',    icon: 'bi-bus-front-fill',         category: 'Operations', href: '/transport',             keywords: ['transport', 'bus', 'van', 'vehicle', 'routes', 'driver', 'pickup point'] },
    { label: 'Hostel & Dormitory Rooms',    icon: 'bi-building-fill-check',    category: 'Operations', href: '/hostel/hostels',        keywords: ['hostel', 'dorm', 'rooms', 'bed allocation', 'warden', 'hostel fee'] },
    { label: 'Inventory & Asset Store',     icon: 'bi-boxes',                  category: 'Operations', href: '/inventory',             keywords: ['inventory', 'assets', 'equipment', 'stationery', 'lab items', 'stock'] },
    { label: 'Visitor Book & Gate Passes',  icon: 'bi-person-lines-fill',      category: 'Operations', href: '/visitors',              keywords: ['visitors', 'guest', 'gate pass', 'visitor log', 'security check'] },

    // ── Settings & Configuration ──
    { label: 'School Profile & Website CMS',icon: 'bi-globe2',                 category: 'Settings', href: '/school-profile',          keywords: ['school profile', 'website', 'landing page', 'logo', 'theme', 'principal photo', 'banner'] },
    { label: 'Academic System Settings',    icon: 'bi-sliders2',               category: 'Settings', href: '/academics/settings',      keywords: ['academic settings', 'session settings', 'semester config', 'system configuration'] },
    { label: 'Roles & User Permissions',    icon: 'bi-shield-lock-fill',       category: 'Settings', href: '/roles',                   keywords: ['roles', 'permissions', 'access control', 'privileges', 'admin roles'] },
    { label: 'Reports & Analytics Hub',     icon: 'bi-file-earmark-bar-graph', category: 'Settings', href: '/reports',                 keywords: ['reports', 'analytics', 'data export', 'audit log', 'statistics'] },
    { label: 'AI Settings & Assistant',     icon: 'bi-robot',                  category: 'Settings', href: '/ai-features',             keywords: ['ai features', 'ai dashboard', 'gemini', 'gpt', 'smart tools'] },
    { label: 'Two-Factor Authentication (2FA)', icon: 'bi-shield-check',      category: 'Settings', href: '/two-factor/setup',         keywords: ['2fa', 'security', 'two factor', 'otp', 'authenticator'] },

    // ── Quick Actions ──
    { label: 'Toggle Dark / Light Mode',    icon: 'bi-moon-stars-fill',        category: 'Actions',  action: 'toggleTheme',            keywords: ['dark mode', 'light mode', 'theme', 'toggle dark', 'night mode'], shortcut: 'T' },
    { label: 'Change Account Password',     icon: 'bi-key-fill',               category: 'Actions',  href: '/password/change',         keywords: ['change password', 'reset password', 'security credentials'] },
    { label: 'Keyboard Shortcuts Help',     icon: 'bi-keyboard-fill',          category: 'Actions',  action: 'showShortcuts',          keywords: ['shortcuts', 'hotkeys', 'keyboard help'], shortcut: '?' },
];

let cmdSelected = -1;

function openCommandPalette() {
    const backdrop = document.getElementById('cmd-palette-backdrop');
    const input    = document.getElementById('cmd-input');
    if (!backdrop) return;
    backdrop.classList.add('active');
    backdrop.style.display = 'flex';
    if (input) {
        input.value = '';
        input.focus();
    }
    renderCmdResults('');
    cmdSelected = -1;
}

function closeCommandPalette() {
    const backdrop = document.getElementById('cmd-palette-backdrop');
    if (backdrop) {
        backdrop.classList.remove('active');
        backdrop.style.display = 'none';
    }
    cmdSelected = -1;
}

function highlight(text, query) {
    if (!query) return text;
    const re = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
    return text.replace(re, '<mark>$1</mark>');
}

function renderCmdResults(query) {
    const container = document.getElementById('cmd-results');
    if (!container) return;

    const q = query.trim().toLowerCase();
    const matched = CMD_REGISTRY.filter(c => {
        if (!q) return true;
        if (c.label.toLowerCase().includes(q) || c.category.toLowerCase().includes(q)) return true;
        if (c.keywords) {
            if (Array.isArray(c.keywords)) {
                return c.keywords.some(k => k.toLowerCase().includes(q) || q.includes(k.toLowerCase()));
            }
            return c.keywords.toLowerCase().includes(q);
        }
        return false;
    });

    // If query is empty, show all static navigation options grouped by category
    if (!q) {
        renderStaticResults(matched, q, container);
        return;
    }

    // Fetch dynamic database results in parallel with matching static commands
    fetch(`/search?q=${encodeURIComponent(q)}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => r.ok ? r.json() : { data: [] })
    .then(response => {
        let html = '';
        
        // Dynamic search results (People, Classes, Courses, Notices)
        if (response.data && response.data.length > 0) {
            html += `<div class="cmd-group-label">Records & People</div>`;
            response.data.forEach((item, i) => {
                const label = highlight(item.label, q);
                const sublabel = highlight(item.sublabel, q);
                html += `<a class="cmd-result-item" href="${item.href}" data-cmd-idx="${i}">
                    <span class="cmd-icon"><i class="bi ${item.icon}"></i></span>
                    <div class="cmd-result-content">
                        <span class="cmd-label">${label}</span>
                        <span class="cmd-sublabel">${sublabel}</span>
                    </div>
                </a>`;
            });
        }

        // Static navigation menu results
        if (matched.length > 0) {
            html += `<div class="cmd-group-label">Pages & Features</div>`;
            matched.forEach((c, i) => {
                const label = highlight(c.label, q);
                const shortcut = c.shortcut ? `<span class="cmd-shortcut">${c.shortcut}</span>` : '';
                const tag = c.href ? 'a' : 'button';
                const hrefAttr = c.href ? `href="${c.href}"` : '';
                const actionAttr = c.action ? `onclick="cmdRunAction('${c.action}')"` : '';
                html += `<${tag} class="cmd-result-item" ${hrefAttr} ${actionAttr} data-cmd-idx="${i}">
                    <span class="cmd-icon"><i class="bi ${c.icon}"></i></span>
                    <span class="cmd-label">${label}</span>
                    ${shortcut}
                </${tag}>`;
            });
        }

        if (!html) {
            html = `<div class="px-4 py-6 text-center text-sm" style="color:var(--text-muted)">
                <i class="bi bi-search-heart d-block" style="font-size:1.5rem;margin-bottom:.5rem"></i>
                No results for "${query}"
            </div>`;
        }

        container.innerHTML = html;
        cmdSelected = -1;
    })
    .catch((error) => {
        renderStaticResults(matched, q, container);
    });
}

function renderStaticResults(matched, query, container) {
    if (!matched.length) {
        container.innerHTML = `<div class="px-4 py-6 text-center text-sm" style="color:var(--text-muted)">
            <i class="bi bi-search-heart d-block" style="font-size:1.5rem;margin-bottom:.5rem"></i>
            No results for "${query}"
        </div>`;
        return;
    }

    // Group by category
    const groups = {};
    matched.forEach(c => {
        if (!groups[c.category]) groups[c.category] = [];
        groups[c.category].push(c);
    });

    let html = '';
    Object.keys(groups).forEach(cat => {
        html += `<div class="cmd-group-label">${cat}</div>`;
        groups[cat].forEach((c, i) => {
            const label = highlight(c.label, query);
            const shortcut = c.shortcut ? `<span class="cmd-shortcut">${c.shortcut}</span>` : '';
            const tag = c.href ? 'a' : 'button';
            const hrefAttr = c.href ? `href="${c.href}"` : '';
            const actionAttr = c.action ? `onclick="cmdRunAction('${c.action}')"` : '';
            html += `<${tag} class="cmd-result-item" ${hrefAttr} ${actionAttr} data-cmd-idx="${i}">
                <span class="cmd-icon"><i class="bi ${c.icon}"></i></span>
                <span class="cmd-label">${label}</span>
                ${shortcut}
            </${tag}>`;
        });
    });

    container.innerHTML = html;
    cmdSelected = -1;
}

function filterCmdResults(val) {
    renderCmdResults(val);
}

function cmdRunAction(action) {
    closeCommandPalette();
    if (action === 'toggleTheme')  toggleTheme();
    if (action === 'showShortcuts') openShortcutModal();
}

function cmdKeyNav(e) {
    const items = document.querySelectorAll('#cmd-results .cmd-result-item');
    if (!items.length) return;

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        cmdSelected = Math.min(cmdSelected + 1, items.length - 1);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        cmdSelected = Math.max(cmdSelected - 1, 0);
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (cmdSelected >= 0 && items[cmdSelected]) items[cmdSelected].click();
        else if (items[0]) items[0].click();
        closeCommandPalette();
        return;
    } else if (e.key === 'Escape') {
        closeCommandPalette();
        return;
    }

    items.forEach((el, i) => el.classList.toggle('active', i === cmdSelected));
    if (items[cmdSelected]) items[cmdSelected].scrollIntoView({ block: 'nearest' });
}

/* ============================================================
   7. FLOATING ACTION BUTTON (FAB)
   ============================================================ */
let fabOpen = false;

function toggleFab() {
    fabOpen = !fabOpen;
    const actions = document.getElementById('fab-actions');
    const icon    = document.getElementById('fab-icon');
    if (!actions) return;

    if (fabOpen) {
        actions.classList.remove('opacity-0', 'translate-y-2', 'pointer-events-none');
        if (icon) { icon.className = 'bi bi-x-lg text-xl'; }
    } else {
        closeFab();
    }
}

function closeFab() {
    fabOpen = false;
    const actions = document.getElementById('fab-actions');
    const icon    = document.getElementById('fab-icon');
    if (actions) actions.classList.add('opacity-0', 'translate-y-2', 'pointer-events-none');
    if (icon)    icon.className = 'bi bi-plus-lg text-xl';
}

/* ============================================================
   8. KEYBOARD SHORTCUT MODAL
   ============================================================ */
function openShortcutModal() {
    const m = document.getElementById('shortcut-modal');
    if (m) { m.style.display = 'flex'; m.classList.remove('hidden'); }
}

function closeShortcutModal() {
    const m = document.getElementById('shortcut-modal');
    if (m) { m.style.display = 'none'; }
}

/* ============================================================
   8b. GLOBAL KEYBOARD SHORTCUTS
   ============================================================ */
let gKeySeq = '';
let gKeyTimer = null;

document.addEventListener('keydown', function (e) {
    const tag = document.activeElement?.tagName?.toLowerCase();
    const isInput = tag === 'input' || tag === 'textarea' || tag === 'select'
                 || document.activeElement?.isContentEditable;

    // Cmd/Ctrl + K → command palette
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        openCommandPalette();
        return;
    }

    // Cmd/Ctrl + B → toggle sidebar
    if ((e.metaKey || e.ctrlKey) && e.key === 'b') {
        e.preventDefault();
        toggleSidebarCollapse();
        return;
    }

    // Cmd/Ctrl + D → toggle dark mode
    if ((e.metaKey || e.ctrlKey) && e.key === 'd') {
        e.preventDefault();
        toggleTheme();
        return;
    }

    // ESC → close palette / modals
    if (e.key === 'Escape') {
        closeCommandPalette();
        closeShortcutModal();
        closeFab();
        return;
    }

    // ? → keyboard shortcuts help (when not typing)
    if (!isInput && e.key === '?') {
        openShortcutModal();
        return;
    }

    // Two-key sequences (G H → home, etc.) — when not in input
    if (!isInput) {
        gKeySeq += e.key.toUpperCase();
        clearTimeout(gKeyTimer);
        gKeyTimer = setTimeout(() => { gKeySeq = ''; }, 800);

        const sequences = {
            'GH': '/home',
            'GS': '/students/view',
            'GT': '/teachers/view/list',
            'GA': '/attendances/take',
            'GE': '/exams/view',
            'GL': '/library',
        };

        if (sequences[gKeySeq]) {
            window.location.href = sequences[gKeySeq];
            gKeySeq = '';
        }
    }
});

/* ============================================================
   9. CLIENT-SIDE TABLE SORT
   ============================================================ */
function initTableSort() {
    document.querySelectorAll('.table-modern th.sortable, table th.sortable').forEach(function (th) {
        th.addEventListener('click', function () {
            const table = th.closest('table');
            if (!table) return;

            const idx      = Array.from(th.parentElement.children).indexOf(th);
            const tbody    = table.querySelector('tbody');
            const rows     = Array.from(tbody?.querySelectorAll('tr') || []);
            const isAsc    = th.classList.contains('sort-asc');

            // Reset all headers
            table.querySelectorAll('th').forEach(h => {
                h.classList.remove('sort-asc', 'sort-desc');
                const si = h.querySelector('.sort-icon');
                if (si) si.textContent = '';
            });

            // Apply sort direction
            th.classList.add(isAsc ? 'sort-desc' : 'sort-asc');

            rows.sort(function (a, b) {
                const aText = a.cells[idx]?.textContent?.trim() || '';
                const bText = b.cells[idx]?.textContent?.trim() || '';
                const aNum  = parseFloat(aText.replace(/[^0-9.-]/g, ''));
                const bNum  = parseFloat(bText.replace(/[^0-9.-]/g, ''));
                const numericSort = !isNaN(aNum) && !isNaN(bNum);
                const cmp = numericSort ? aNum - bNum : aText.localeCompare(bText, undefined, { sensitivity: 'base' });
                return isAsc ? -cmp : cmp;
            });

            rows.forEach(r => tbody.appendChild(r));
        });
    });
}

/* ============================================================
   10. CLIENT-SIDE TABLE SEARCH / FILTER
   ============================================================ */
function initTableSearch() {
    document.querySelectorAll('[data-table-search]').forEach(function (input) {
        const targetId = input.dataset.tableSearch;
        const table    = document.getElementById(targetId);
        if (!table) return;

        input.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            table.querySelectorAll('tbody tr').forEach(function (row) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(q) ? '' : 'none';
            });

            // Show empty state
            const tbody       = table.querySelector('tbody');
            const visibleRows = tbody ? Array.from(tbody.querySelectorAll('tr')).filter(r => r.style.display !== 'none') : [];
            let empty = table.parentElement?.querySelector('.table-empty-state');
            if (visibleRows.length === 0 && !empty) {
                empty = document.createElement('div');
                empty.className = 'table-empty-state px-6 py-10 text-center text-sm';
                empty.style.color = 'var(--text-muted)';
                empty.innerHTML = '<i class="bi bi-search" style="font-size:1.5rem;display:block;margin-bottom:.5rem"></i>No results match your search.';
                table.parentElement.appendChild(empty);
            } else if (visibleRows.length > 0 && empty) {
                empty.remove();
            }
        });
    });
}

/* ============================================================
   11. CONFIRM-DELETE HELPER  (data-confirm attribute)
   ============================================================ */
function initConfirmDelete() {
    document.addEventListener('submit', function (e) {
        const form = e.target;
        const msg  = form.dataset.confirm;
        if (msg && !confirm(msg)) {
            e.preventDefault();
        }
    });

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-confirm]');
        if (btn && btn.tagName !== 'FORM') {
            if (!confirm(btn.dataset.confirm)) {
                e.preventDefault();
                e.stopPropagation();
            }
        }
    });
}

/* ============================================================
   12. TOOLTIP  (data-tooltip attribute → simple tooltip)
   ============================================================ */
function initTooltips() {
    let tip = null;

    document.addEventListener('mouseover', function (e) {
        const el = e.target.closest('[data-tooltip]');
        if (!el) return;
        const text = el.getAttribute('data-tooltip');
        if (!text) return;

        tip = document.createElement('div');
        tip.textContent = text;
        tip.style.cssText = `
            position:fixed; z-index:9999; pointer-events:none;
            background:#0f172a; color:#f8fafc;
            font-size:.7rem; padding:.3rem .6rem; border-radius:.375rem;
            white-space:nowrap; box-shadow:0 4px 12px rgba(0,0,0,.2);
            transition: opacity .15s;
        `;
        document.body.appendChild(tip);
    });

    document.addEventListener('mousemove', function (e) {
        if (!tip) return;
        tip.style.left = (e.clientX + 12) + 'px';
        tip.style.top  = (e.clientY - 28) + 'px';
    });

    document.addEventListener('mouseout', function (e) {
        if (tip && e.target.closest('[data-tooltip]')) {
            tip.remove();
            tip = null;
        }
    });
}

/* ============================================================
   13. CARD GRID / TABLE VIEW TOGGLE
   ============================================================ */
function initViewToggle() {
    document.querySelectorAll('[data-view-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const view     = btn.dataset.viewToggle; // 'table' | 'grid'
            const targetId = btn.dataset.viewTarget;
            const target   = document.getElementById(targetId);
            if (!target) return;

            // Update button states
            btn.closest('[data-view-group]')?.querySelectorAll('[data-view-toggle]').forEach(b => {
                b.classList.toggle('active', b === btn);
            });

            // Toggle view classes
            if (view === 'grid') {
                target.classList.add('view-grid');
                target.classList.remove('view-table');
            } else {
                target.classList.add('view-table');
                target.classList.remove('view-grid');
            }

            localStorage.setItem('erp_view_' + targetId, view);
        });
    });

    // Restore saved view
    document.querySelectorAll('[data-view-target]').forEach(function (btn) {
        const id    = btn.dataset.viewTarget;
        const saved = localStorage.getItem('erp_view_' + id);
        if (saved) {
            const trigger = document.querySelector(`[data-view-toggle="${saved}"][data-view-target="${id}"]`);
            if (trigger) trigger.click();
        }
    });
}

/* ============================================================
   14. AUTO-CLOSE ALERTS
   ============================================================ */
function initAutoCloseAlerts() {
    document.querySelectorAll('[data-auto-close]').forEach(function (el) {
        const ms = parseInt(el.dataset.autoClose) || 5000;
        setTimeout(function () {
            el.style.transition = 'opacity .3s';
            el.style.opacity    = '0';
            setTimeout(() => el.remove(), 350);
        }, ms);
    });
}

/* ============================================================
   15. ACTIVE NAV LINK HIGHLIGHT (fallback for JS-rendered routes)
   ============================================================ */
function highlightActiveNav() {
    const path = window.location.pathname;
    document.querySelectorAll('.nav-item[href]').forEach(function (link) {
        const href = link.getAttribute('href');
        if (!href || href === '#') return;
        const isActive = path === href || (href !== '/' && path.startsWith(href));
        link.classList.toggle('active', isActive);
    });
}

/* ============================================================
   16. UNIVERSAL MOBILE RESPONSIVE TABLES
   ============================================================ */
function initResponsiveTables() {
    document.querySelectorAll('table').forEach(function (table) {
        if (!table.closest('.overflow-x-auto, .table-responsive, [class*="overflow-x"]')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive-wrapper overflow-x-auto w-full max-w-full';
            wrapper.style.webkitOverflowScrolling = 'touch';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
    });
}

/* ============================================================
   INIT — runs when DOM is ready
   ============================================================ */
document.addEventListener('DOMContentLoaded', function () {
    restoreSidebarState();
    initAutoToasts();
    initResponsiveTables();
    initTableSort();
    initTableSearch();
    initConfirmDelete();
    initTooltips();
    initViewToggle();
    initAutoCloseAlerts();
    highlightActiveNav();

    // Expose globals for Blade onclick usage
    window.toggleTheme          = toggleTheme;
    window.openSidebar          = openSidebar;
    window.closeSidebar         = closeSidebar;
    window.toggleSidebarCollapse= toggleSidebarCollapse;
    window.toggleNotifPanel     = toggleNotifPanel;
    window.toggleUserMenu       = toggleUserMenu;
    window.openCommandPalette   = openCommandPalette;
    window.closeCommandPalette  = closeCommandPalette;
    window.filterCmdResults     = filterCmdResults;
    window.cmdKeyNav            = cmdKeyNav;
    window.cmdRunAction         = cmdRunAction;
    window.toggleFab            = toggleFab;
    window.closeFab             = closeFab;
    window.openShortcutModal    = openShortcutModal;
    window.closeShortcutModal   = closeShortcutModal;
    window.showToast            = showToast;
    window.dismissToast         = dismissToast;
});
