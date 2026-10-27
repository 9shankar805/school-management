@extends('layouts.app')

@section('title', 'Roles & Permissions - NepalSchoolHub')

@section('content')
<div class="flex-1 p-4 sm:p-6 lg:p-10 overflow-y-auto space-y-6 bg-slate-50/60 dark:bg-slate-950 min-h-screen">

    {{-- ── 1. Hero Header & Quick Actions ── --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 border border-slate-800 text-white p-6 sm:p-8 shadow-xl">
        {{-- Background Glow & Abstract Shapes --}}
        <div class="absolute -right-12 -top-12 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-1/3 -bottom-10 w-48 h-48 bg-purple-500/15 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 backdrop-blur-md">
                        <i class="bi bi-shield-lock-fill text-indigo-400"></i> Role-Based Access Control (RBAC)
                    </span>
                    <span class="text-xs text-slate-400">
                        <i class="bi bi-check2-circle text-emerald-400 me-1"></i>Security Scoped
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight text-white">
                    Roles & Permissions
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 max-w-2xl leading-relaxed">
                    Configure institutional access control, manage user roles, and define granular operational permissions across all departments.
                </p>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('roles.assignable-users') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs sm:text-sm font-bold shadow-lg shadow-emerald-600/25 transition transform hover:-translate-y-0.5 active:scale-95">
                    <i class="bi bi-person-check-fill text-sm"></i>
                    <span>Assign Users</span>
                </a>
                <a href="{{ route('roles.matrix') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white border border-white/15 text-xs sm:text-sm font-semibold backdrop-blur-md transition transform hover:-translate-y-0.5 active:scale-95">
                    <i class="bi bi-grid-3x3-gap-fill text-indigo-300 text-sm"></i>
                    <span>Permission Matrix</span>
                </a>
                <a href="{{ route('roles.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-500 to-violet-600 hover:from-indigo-400 hover:to-violet-500 text-white text-xs sm:text-sm font-bold shadow-lg shadow-indigo-600/30 transition transform hover:-translate-y-0.5 active:scale-95">
                    <i class="bi bi-plus-lg text-sm"></i>
                    <span>New Custom Role</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Flash Alerts --}}
    @if(session('success'))
    <div class="px-4 py-3.5 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 text-sm rounded-2xl border border-emerald-200 dark:border-emerald-900/50 flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-2.5">
            <span class="w-7 h-7 rounded-xl bg-emerald-100 dark:bg-emerald-900/80 text-emerald-600 dark:text-emerald-300 flex items-center justify-center flex-shrink-0 text-sm">
                <i class="bi bi-check-lg"></i>
            </span>
            <span class="font-medium text-xs sm:text-sm">{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 text-xs">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    @endif
    @if(session('error'))
    <div class="px-4 py-3.5 bg-rose-50 dark:bg-rose-950/50 text-rose-800 dark:text-rose-300 text-sm rounded-2xl border border-rose-200 dark:border-rose-900/50 flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-2.5">
            <span class="w-7 h-7 rounded-xl bg-rose-100 dark:bg-rose-900/80 text-rose-600 dark:text-rose-300 flex items-center justify-center flex-shrink-0 text-sm">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </span>
            <span class="font-medium text-xs sm:text-sm">{{ session('error') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800 text-xs">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    @endif

    {{-- ── 2. Real-Time Metrics Bar ── --}}
    @php
        $totalRolesCount = $roles->count();
        $systemRolesCount = $roles->filter(fn($r) => in_array($r->name, ['super-admin','admin','teacher','student','parent']))->count();
        $customRolesCount = $totalRolesCount - $systemRolesCount;
        $totalUsersCount = $roles->sum('users_count');
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-5">
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Defined Roles</span>
                <span class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-base">
                    <i class="bi bi-shield-check"></i>
                </span>
            </div>
            <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white mt-2">{{ $totalRolesCount }}</p>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> Active authorization profiles
            </p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-purple-500">Core System Roles</span>
                <span class="w-9 h-9 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-base">
                    <i class="bi bi-award-fill"></i>
                </span>
            </div>
            <p class="text-2xl sm:text-3xl font-black text-purple-600 dark:text-purple-400 mt-2">{{ $systemRolesCount }}</p>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span> Immutable core structure
            </p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-500">Custom Roles</span>
                <span class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-base">
                    <i class="bi bi-sliders"></i>
                </span>
            </div>
            <p class="text-2xl sm:text-3xl font-black text-amber-600 dark:text-amber-400 mt-2">{{ $customRolesCount }}</p>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Fully customizable
            </p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-500">Assigned Users</span>
                <span class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-base">
                    <i class="bi bi-people-fill"></i>
                </span>
            </div>
            <p class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400 mt-2">{{ number_format($totalUsersCount) }}</p>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Total active memberships
            </p>
        </div>
    </div>

    {{-- ── 3. Search & Interactive Role Filter Controls ── --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-4 shadow-sm space-y-3">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            {{-- Search Bar --}}
            <div class="relative flex-1 max-w-md">
                <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text"
                       id="role-filter-input"
                       placeholder="Filter by role name (e.g. teacher, admission, accountant)..."
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition"
                       oninput="filterRoleCards(this.value)">
            </div>

            {{-- Quick Filter Category Tabs --}}
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0" id="category-filter-chips">
                <button type="button" onclick="setRoleCategory('all', this)" class="role-cat-btn active-cat px-3 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap">
                    All Roles ({{ $totalRolesCount }})
                </button>
                <button type="button" onclick="setRoleCategory('system', this)" class="role-cat-btn px-3 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap">
                    System Core ({{ $systemRolesCount }})
                </button>
                <button type="button" onclick="setRoleCategory('academic', this)" class="role-cat-btn px-3 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap">
                    Academics
                </button>
                <button type="button" onclick="setRoleCategory('admin', this)" class="role-cat-btn px-3 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap">
                    Administration
                </button>
            </div>
        </div>
    </div>

    {{-- Helper for Role Metadata --}}
    @php
        function getRoleDetails($name) {
            return match($name) {
                'super-admin' => [
                    'icon' => 'bi-shield-shaded',
                    'gradient' => 'from-rose-600 to-red-600',
                    'shadow' => 'shadow-rose-500/20',
                    'badge' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-900',
                    'category' => 'system',
                    'desc' => 'Full administrative sovereignty across tenant, configuration, security, and global parameters.'
                ],
                'admin' => [
                    'icon' => 'bi-shield-fill',
                    'gradient' => 'from-rose-500 to-pink-600',
                    'shadow' => 'shadow-rose-500/20',
                    'badge' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-900',
                    'category' => 'admin',
                    'desc' => 'School-level operations manager with complete academic, staff, financial, and settings controls.'
                ],
                'organization-owner' => [
                    'icon' => 'bi-award-fill',
                    'gradient' => 'from-amber-500 to-yellow-600',
                    'shadow' => 'shadow-amber-500/20',
                    'badge' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-900',
                    'category' => 'admin',
                    'desc' => 'Institutional executive oversight, high-level financials, audit records, and campus analytics.'
                ],
                'principal' => [
                    'icon' => 'bi-building-fill-check',
                    'gradient' => 'from-purple-600 to-violet-600',
                    'shadow' => 'shadow-purple-500/20',
                    'badge' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-900',
                    'category' => 'admin',
                    'desc' => 'Head of school overseeing academic standards, staff evaluations, notices, and disciplinary routines.'
                ],
                'vice-principal' => [
                    'icon' => 'bi-building',
                    'gradient' => 'from-purple-500 to-indigo-600',
                    'shadow' => 'shadow-purple-500/20',
                    'badge' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-900',
                    'category' => 'admin',
                    'desc' => 'Assists executive head in timetable approvals, attendance enforcement, and daily operations.'
                ],
                'academic-coordinator' => [
                    'icon' => 'bi-diagram-3-fill',
                    'gradient' => 'from-indigo-600 to-blue-600',
                    'shadow' => 'shadow-indigo-500/20',
                    'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-900',
                    'category' => 'academic',
                    'desc' => 'Curriculum manager controlling CDC 2078 guidelines, syllabi, section timetables, and teacher assignments.'
                ],
                'teacher' => [
                    'icon' => 'bi-person-workspace',
                    'gradient' => 'from-blue-600 to-cyan-600',
                    'shadow' => 'shadow-blue-500/20',
                    'badge' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-900',
                    'category' => 'system',
                    'desc' => 'Faculty member managing classroom attendance, homework assignments, live quiz, and grading.'
                ],
                'class-teacher' => [
                    'icon' => 'bi-easel2-fill',
                    'gradient' => 'from-blue-500 to-indigo-500',
                    'shadow' => 'shadow-blue-500/20',
                    'badge' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-900',
                    'category' => 'academic',
                    'desc' => 'Direct classroom mentor with section student roster authority, report cards, and parent advisory.'
                ],
                'student' => [
                    'icon' => 'bi-backpack2-fill',
                    'gradient' => 'from-emerald-600 to-teal-600',
                    'shadow' => 'shadow-emerald-500/20',
                    'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-900',
                    'category' => 'system',
                    'desc' => 'Student portal access for routine schedules, homework submissions, exam results, notices, and quizzes.'
                ],
                'parent' => [
                    'icon' => 'bi-people-fill',
                    'gradient' => 'from-amber-500 to-orange-600',
                    'shadow' => 'shadow-amber-500/20',
                    'badge' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-900',
                    'category' => 'system',
                    'desc' => 'Guardian access for student progress tracking, live attendance notifications, and eSewa fee payments.'
                ],
                'accountant' => [
                    'icon' => 'bi-cash-coin',
                    'gradient' => 'from-emerald-500 to-green-600',
                    'shadow' => 'shadow-emerald-500/20',
                    'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-900',
                    'category' => 'admin',
                    'desc' => 'Finance controller managing fee structures, student invoices, receipts, payment gateways, and ledger.'
                ],
                'admission-officer' => [
                    'icon' => 'bi-person-plus-fill',
                    'gradient' => 'from-cyan-600 to-blue-600',
                    'shadow' => 'shadow-cyan-500/20',
                    'badge' => 'bg-cyan-50 text-cyan-700 border-cyan-200 dark:bg-cyan-950/60 dark:text-cyan-300 dark:border-cyan-900',
                    'category' => 'academic',
                    'desc' => 'Handles student enrollments, intake documentation, registration fees, and classroom placements.'
                ],
                'attendance-officer' => [
                    'icon' => 'bi-calendar-check-fill',
                    'gradient' => 'from-teal-600 to-emerald-600',
                    'shadow' => 'shadow-teal-500/20',
                    'badge' => 'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-950/60 dark:text-teal-300 dark:border-teal-900',
                    'category' => 'academic',
                    'desc' => 'Dedicated officer for campus-wide daily student attendance logs, leave verification, and parent alerts.'
                ],
                'exam-controller' => [
                    'icon' => 'bi-file-earmark-text-fill',
                    'gradient' => 'from-orange-500 to-amber-600',
                    'shadow' => 'shadow-orange-500/20',
                    'badge' => 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-950/60 dark:text-orange-300 dark:border-orange-900',
                    'category' => 'academic',
                    'desc' => 'Conducts term examinations, creates assessment schedules, oversees GPA gradebooks, and generates marksheet.'
                ],
                'librarian' => [
                    'icon' => 'bi-book-half',
                    'gradient' => 'from-violet-600 to-purple-600',
                    'shadow' => 'shadow-violet-500/20',
                    'badge' => 'bg-violet-50 text-violet-700 border-violet-200 dark:bg-violet-950/60 dark:text-violet-300 dark:border-violet-900',
                    'category' => 'admin',
                    'desc' => 'Manages library catalog, ISBN registry, book circulation, borrowing history, and overdue fines.'
                ],
                'hostel-manager' => [
                    'icon' => 'bi-house-heart-fill',
                    'gradient' => 'from-rose-500 to-pink-600',
                    'shadow' => 'shadow-rose-500/20',
                    'badge' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-900',
                    'category' => 'admin',
                    'desc' => 'Residential block allocations, room and bed assignments, student curfew logs, and warden coordination.'
                ],
                'transport-manager' => [
                    'icon' => 'bi-bus-front-fill',
                    'gradient' => 'from-amber-500 to-orange-600',
                    'shadow' => 'shadow-amber-500/20',
                    'badge' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-900',
                    'category' => 'admin',
                    'desc' => 'Transit route planning, bus stops, vehicle registry, driver profiles, and student transport passes.'
                ],
                'hr-manager' => [
                    'icon' => 'bi-person-badge-fill',
                    'gradient' => 'from-sky-600 to-blue-600',
                    'shadow' => 'shadow-sky-500/20',
                    'badge' => 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/60 dark:text-sky-300 dark:border-sky-900',
                    'category' => 'admin',
                    'desc' => 'Employee records, staff onboarding, leave requests, department distribution, and monthly payroll logs.'
                ],
                'receptionist' => [
                    'icon' => 'bi-telephone-fill',
                    'gradient' => 'from-pink-500 to-rose-600',
                    'shadow' => 'shadow-pink-500/20',
                    'badge' => 'bg-pink-50 text-pink-700 border-pink-200 dark:bg-pink-950/60 dark:text-pink-300 dark:border-pink-900',
                    'category' => 'admin',
                    'desc' => 'Front-desk operations, visitor log, general inquiries, student call coordination, and notice announcements.'
                ],
                'guest' => [
                    'icon' => 'bi-person-fill',
                    'gradient' => 'from-slate-500 to-slate-600',
                    'shadow' => 'shadow-slate-500/20',
                    'badge' => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
                    'category' => 'admin',
                    'desc' => 'Read-only public preview role for demo accounts or prospective parents.'
                ],
                default => [
                    'icon' => 'bi-shield-shaded',
                    'gradient' => 'from-indigo-600 to-violet-600',
                    'shadow' => 'shadow-indigo-500/20',
                    'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-900',
                    'category' => 'custom',
                    'desc' => 'Custom user authorization role with configured permission parameters.'
                ],
            };
        }
    @endphp

    {{-- ── 4. Dynamic Interactive Role Cards Grid ── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6" id="roles-grid-container">
        @foreach($roles as $role)
        @php 
            $meta = getRoleDetails($role->name); 
            $isSystem = in_array($role->name, ['super-admin','admin','teacher','student','parent']);
        @endphp
        <div class="role-grid-card group relative flex flex-col justify-between bg-white dark:bg-slate-900 rounded-3xl p-5 sm:p-6 border border-slate-200/80 dark:border-slate-800 hover:border-indigo-500/50 dark:hover:border-indigo-500/50 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-200"
             data-role-name="{{ strtolower($role->name) }}"
             data-category="{{ $isSystem ? 'system' : $meta['category'] }}">
            
            {{-- Top Row: Avatar Icon + Role Name + System Pill --}}
            <div>
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr {{ $meta['gradient'] }} text-white flex items-center justify-center text-xl shadow-lg {{ $meta['shadow'] }} flex-shrink-0 group-hover:scale-105 transition-transform">
                            <i class="bi {{ $meta['icon'] }}"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-bold text-slate-900 dark:text-white text-base capitalize tracking-tight truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                {{ str_replace('-', ' ', $role->name) }}
                            </h3>
                            <span class="text-[11px] font-mono text-slate-400 dark:text-slate-500 block truncate">
                                {{ $role->name }}
                            </span>
                        </div>
                    </div>

                    @if($isSystem)
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800/80 rounded-full flex-shrink-0">
                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500 animate-pulse"></span>
                        System
                    </span>
                    @else
                    <span class="inline-flex items-center text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700 rounded-full flex-shrink-0">
                        Custom
                    </span>
                    @endif
                </div>

                {{-- Description snippet --}}
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed mb-4 line-clamp-2">
                    {{ $meta['desc'] }}
                </p>

                {{-- Badges / Metrics Stats Row --}}
                <div class="flex items-center gap-2 mb-5 flex-wrap">
                    {{-- Permissions Chip --}}
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-900/60 px-3 py-1 rounded-xl">
                        <i class="bi bi-shield-check text-indigo-500"></i>
                        <span>{{ $role->permissions_count }} Permissions</span>
                    </span>

                    {{-- Active Users Chip --}}
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-900/60 px-3 py-1 rounded-xl">
                        <i class="bi bi-people-fill text-emerald-500"></i>
                        <span>{{ $role->users_count }} {{ $role->users_count === 1 ? 'User' : 'Users' }}</span>
                    </span>
                </div>
            </div>

            {{-- Bottom Row: Action Buttons --}}
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-2">
                {{-- View Assigned Users --}}
                <a href="{{ route('roles.assignable-users', ['role' => $role->name]) }}"
                   class="flex-1 inline-flex items-center justify-center gap-1.5 py-2.5 px-3 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 font-bold text-xs rounded-xl border border-emerald-200/80 dark:border-emerald-800/60 transition active:scale-95 shadow-xs">
                    <i class="bi bi-people-fill text-xs"></i>
                    <span>View Users</span>
                </a>

                {{-- Edit Permissions --}}
                <a href="{{ route('roles.edit', $role) }}"
                   class="flex-1 inline-flex items-center justify-center gap-1.5 py-2.5 px-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs rounded-xl transition active:scale-95">
                    <i class="bi bi-sliders2 text-xs"></i>
                    <span>Permissions</span>
                </a>

                {{-- Delete Role --}}
                @if(!$isSystem)
                <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('Are you sure you want to permanently delete the custom role \'{{ $role->name }}\'?')" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-2.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-300 rounded-xl border border-rose-200 dark:border-rose-900/60 transition flex items-center justify-center active:scale-95" title="Delete Custom Role">
                        <i class="bi bi-trash-fill text-xs"></i>
                    </button>
                </form>
                @endif
            </div>

        </div>
        @endforeach
    </div>

    {{-- Empty State --}}
    <div id="no-roles-match" class="hidden bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-12 text-center">
        <i class="bi bi-shield-x text-4xl text-slate-300 dark:text-slate-600 block mb-3"></i>
        <h3 class="text-base font-bold text-slate-700 dark:text-slate-200">No roles matched your search</h3>
        <p class="text-xs text-slate-400 mt-1">Try searching with a different role title or clear the filter.</p>
        <button type="button" onclick="resetRoleSearch()" class="mt-4 px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-xl shadow-sm">
            Clear Filter
        </button>
    </div>

</div>

<style>
.role-cat-btn {
    background: #f1f5f9;
    color: #475569;
}
[data-theme="dark"] .role-cat-btn,
html.dark .role-cat-btn {
    background: #1e293b;
    color: #94a3b8;
}
.role-cat-btn:hover {
    background: #e2e8f0;
    color: #0f172a;
}
[data-theme="dark"] .role-cat-btn:hover,
html.dark .role-cat-btn:hover {
    background: #334155;
    color: #f8fafc;
}
.role-cat-btn.active-cat {
    background: #4f46e5 !important;
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
}
</style>

<script>
let currentCategory = 'all';

function setRoleCategory(cat, btn) {
    currentCategory = cat;
    document.querySelectorAll('.role-cat-btn').forEach(b => b.classList.remove('active-cat'));
    if (btn) btn.classList.add('active-cat');
    applyCombinedFilters();
}

function filterRoleCards(query) {
    applyCombinedFilters();
}

function applyCombinedFilters() {
    const query = (document.getElementById('role-filter-input')?.value || '').toLowerCase().trim();
    const cards = document.querySelectorAll('.role-grid-card');
    let visible = 0;

    cards.forEach(card => {
        const name = card.getAttribute('data-role-name') || '';
        const cat  = card.getAttribute('data-category') || '';

        const matchesQuery = !query || name.includes(query);
        let matchesCat = true;

        if (currentCategory === 'system') {
            matchesCat = (cat === 'system');
        } else if (currentCategory === 'academic') {
            matchesCat = ['academic'].includes(cat) || ['teacher', 'class-teacher', 'academic-coordinator', 'exam-controller', 'admission-officer', 'attendance-officer'].some(k => name.includes(k));
        } else if (currentCategory === 'admin') {
            matchesCat = ['admin'].includes(cat) || ['super-admin', 'admin', 'principal', 'vice-principal', 'organization-owner', 'accountant', 'hr-manager', 'hostel-manager', 'transport-manager', 'receptionist', 'librarian'].some(k => name.includes(k));
        }

        const shouldShow = matchesQuery && matchesCat;
        card.style.display = shouldShow ? 'flex' : 'none';
        if (shouldShow) visible++;
    });

    const empty = document.getElementById('no-roles-match');
    if (empty) {
        empty.classList.toggle('hidden', visible > 0);
    }
}

function resetRoleSearch() {
    const input = document.getElementById('role-filter-input');
    if (input) input.value = '';
    setRoleCategory('all', document.querySelector('.role-cat-btn'));
}
</script>
@endsection
