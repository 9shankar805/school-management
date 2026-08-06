@extends('layouts.app')
@section('title', 'Super Admin Dashboard - NepalSchoolHub')

@section('content')
<div class="px-6 py-5 space-y-5">

    {{-- ── 1. PAGE HEADER & MINI STATS ────────────────────────────────── --}}
    <div class="flex items-center justify-between gap-6">
        {{-- Greeting --}}
        <div class="flex-shrink-0">
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2 tracking-tight">
                Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 18 ? 'Afternoon' : 'Evening') }}, Super Admin! 👋
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5 font-medium">
                {{ now()->format('l, F j, Y') }} &bull; {{ now()->format('g:i A') }} (NPT)
            </p>
        </div>

        {{-- Mini Stat Pills (Top Right) — 4 pills in a tight row, no wrap --}}
        <div class="flex items-center gap-3 flex-shrink-0">
            {{-- Total Schools --}}
            <div class="bg-white dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-700/60 rounded-xl px-4 py-2.5 flex items-center gap-3 shadow-xs min-w-[130px]">
                <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center text-sm flex-shrink-0">
                    <i class="bi bi-building"></i>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider whitespace-nowrap">Total Schools</p>
                    <p class="text-sm font-bold text-slate-900 dark:text-slate-100 leading-none mt-0.5">{{ number_format($totalSchools) }}</p>
                </div>
            </div>

            {{-- Active Schools --}}
            <div class="bg-white dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-700/60 rounded-xl px-4 py-2.5 flex items-center gap-3 shadow-xs min-w-[140px]">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm flex-shrink-0">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider whitespace-nowrap">Active Schools</p>
                    <p class="text-sm font-bold text-slate-900 dark:text-slate-100 leading-none mt-0.5">{{ number_format($activeSchools) }}</p>
                </div>
            </div>

            {{-- Total Users --}}
            <div class="bg-white dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-700/60 rounded-xl px-4 py-2.5 flex items-center gap-3 shadow-xs min-w-[130px]">
                <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm flex-shrink-0">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider whitespace-nowrap">Total Users</p>
                    <p class="text-sm font-bold text-slate-900 dark:text-slate-100 leading-none mt-0.5">{{ number_format($totalUsers) }}</p>
                </div>
            </div>

            {{-- Active Subscriptions --}}
            <div class="bg-white dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-700/60 rounded-xl px-4 py-2.5 flex items-center gap-3 shadow-xs min-w-[160px]">
                <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm flex-shrink-0">
                    <i class="bi bi-award-fill"></i>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider whitespace-nowrap">Active Subscriptions</p>
                    <p class="text-sm font-bold text-slate-900 dark:text-slate-100 leading-none mt-0.5">{{ number_format($activeSubscriptions) }}</p>
                </div>
            </div>
        </div>
    </div>


    {{-- ── 2. QUICK ACTIONS (5 CARDS) ────────────────────────────────── --}}
    <div class="grid grid-cols-5 gap-4">
        {{-- Add School --}}
        <a href="{{ route('super.schools.index') }}"
           class="group bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 flex items-center gap-3.5 hover:border-blue-500 hover:shadow-md transition-all">
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg flex-shrink-0 group-hover:scale-105 transition-transform">
                <i class="bi bi-building-add"></i>
            </div>
            <div>
                <h4 class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-blue-600 whitespace-nowrap">Add School</h4>
                <p class="text-[10px] text-slate-400 whitespace-nowrap">Register a new school</p>
            </div>
        </a>

        {{-- Invite Admin --}}
        <a href="{{ route('teacher.create.show') }}"
           class="group bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 flex items-center gap-3.5 hover:border-emerald-500 hover:shadow-md transition-all">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg flex-shrink-0 group-hover:scale-105 transition-transform">
                <i class="bi bi-person-plus-fill"></i>
            </div>
            <div>
                <h4 class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-emerald-600 whitespace-nowrap">Invite Admin</h4>
                <p class="text-[10px] text-slate-400 whitespace-nowrap">Add new admin user</p>
            </div>
        </a>

        {{-- Generate Report --}}
        <a href="{{ route('reports.index') }}"
           class="group bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 flex items-center gap-3.5 hover:border-purple-500 hover:shadow-md transition-all">
            <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center text-lg flex-shrink-0 group-hover:scale-105 transition-transform">
                <i class="bi bi-file-earmark-bar-graph"></i>
            </div>
            <div>
                <h4 class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-purple-600 whitespace-nowrap">Generate Report</h4>
                <p class="text-[10px] text-slate-400 whitespace-nowrap">Download system report</p>
            </div>
        </a>

        {{-- Create Invoice --}}
        <a href="{{ route('payments.index') }}"
           class="group bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 flex items-center gap-3.5 hover:border-amber-500 hover:shadow-md transition-all">
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg flex-shrink-0 group-hover:scale-105 transition-transform">
                <i class="bi bi-file-earmark-text"></i>
            </div>
            <div>
                <h4 class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-amber-600 whitespace-nowrap">Create Invoice</h4>
                <p class="text-[10px] text-slate-400 whitespace-nowrap">Generate new invoice</p>
            </div>
        </a>

        {{-- System Settings --}}
        <a href="{{ url('academics/settings') }}"
           class="group bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 flex items-center gap-3.5 hover:border-sky-500 hover:shadow-md transition-all">
            <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-900/40 text-sky-600 dark:text-sky-400 flex items-center justify-center text-lg flex-shrink-0 group-hover:scale-105 transition-transform">
                <i class="bi bi-gear-fill"></i>
            </div>
            <div>
                <h4 class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-sky-600 whitespace-nowrap">System Settings</h4>
                <p class="text-[10px] text-slate-400 whitespace-nowrap">Manage platform settings</p>
            </div>
        </a>
    </div>


    {{-- ── 3. KEY METRICS STAT CARDS WITH SPARKLINE GRAPHS (5 CARDS) ──── --}}
    <div class="grid grid-cols-5 gap-4">

        {{-- 1. Total Students --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 flex flex-col justify-between shadow-xs relative overflow-hidden">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center text-base shadow-sm flex-shrink-0">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-semibold">Total Students</p>
                    <h3 class="text-xl font-extrabold text-slate-900 dark:text-white leading-tight">{{ number_format($studentCount) }}</h3>
                </div>
            </div>
            <div class="flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 mb-1">
                <i class="bi bi-arrow-up-short text-base"></i> 8.4% this month
            </div>
            <div id="sparkline-students" class="-mx-3 -mb-3 h-12"></div>
        </div>

        {{-- 2. Total Teachers --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 flex flex-col justify-between shadow-xs relative overflow-hidden">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-base shadow-sm flex-shrink-0">
                    <i class="bi bi-person-badge-fill"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-semibold">Total Teachers</p>
                    <h3 class="text-xl font-extrabold text-slate-900 dark:text-white leading-tight">{{ number_format($teacherCount) }}</h3>
                </div>
            </div>
            <div class="flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 mb-1">
                <i class="bi bi-arrow-up-short text-base"></i> 5.6% this month
            </div>
            <div id="sparkline-teachers" class="-mx-3 -mb-3 h-12"></div>
        </div>

        {{-- 3. Total Schools --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 flex flex-col justify-between shadow-xs relative overflow-hidden">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-xl bg-purple-600 text-white flex items-center justify-center text-base shadow-sm flex-shrink-0">
                    <i class="bi bi-building"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-semibold">Total Schools</p>
                    <h3 class="text-xl font-extrabold text-slate-900 dark:text-white leading-tight">{{ number_format($totalSchools) }}</h3>
                </div>
            </div>
            <div class="flex items-center gap-1 text-[11px] font-bold text-amber-600 dark:text-amber-400 mb-1">
                <i class="bi bi-arrow-up-short text-base"></i> 2 new this month
            </div>
            <div id="sparkline-schools" class="-mx-3 -mb-3 h-12"></div>
        </div>

        {{-- 4. Monthly Revenue --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 flex flex-col justify-between shadow-xs relative overflow-hidden">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-xl bg-teal-500 text-white flex items-center justify-center text-base shadow-sm flex-shrink-0">
                    <i class="bi bi-currency-rupee"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-semibold">Monthly Revenue</p>
                    <h3 class="text-xl font-extrabold text-slate-900 dark:text-white leading-tight">Rs. {{ number_format($monthRevenue) }}</h3>
                </div>
            </div>
            <div class="flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 mb-1">
                <i class="bi bi-arrow-up-short text-base"></i> 12.5% this month
            </div>
            <div id="sparkline-revenue" class="-mx-3 -mb-3 h-12"></div>
        </div>

        {{-- 5. Outstanding Fees --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 flex flex-col justify-between shadow-xs relative overflow-hidden">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center text-base shadow-sm flex-shrink-0">
                    <i class="bi bi-file-earmark-spreadsheet"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-semibold">Outstanding Fees</p>
                    <h3 class="text-xl font-extrabold text-slate-900 dark:text-white leading-tight">Rs. {{ number_format($outstandingFees) }}</h3>
                </div>
            </div>
            <div class="flex items-center gap-1 text-[11px] font-bold text-rose-500 dark:text-rose-400 mb-1">
                <i class="bi bi-arrow-down-short text-base"></i> 3.2% this month
            </div>
            <div id="sparkline-fees" class="-mx-3 -mb-3 h-12"></div>
        </div>

    </div>


    {{-- ── 4. MIDDLE GRID ROW 1 (3 CARDS) ────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Card 1: Today's Attendance Overview --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="bi bi-calendar-event text-blue-500"></i> Today's Attendance Overview
                    </h3>
                    <select class="text-xs border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 bg-slate-50 dark:bg-slate-900 text-slate-600 dark:text-slate-300 focus:outline-none cursor-pointer">
                        <option>Today</option>
                        <option>This Week</option>
                    </select>
                </div>

                {{-- Donut Chart container with centered text --}}
                <div class="relative flex items-center justify-center my-2">
                    <div id="chart-attendance-overview" class="w-full h-44"></div>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-2xl font-black text-slate-900 dark:text-white leading-none">92%</span>
                        <span class="text-[10px] text-slate-400 font-semibold mt-0.5">Attendance</span>
                    </div>
                </div>

                {{-- Breakdown items --}}
                <div class="space-y-2 mt-2 pt-2 border-t border-slate-100 dark:border-slate-700/60">
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-600 inline-block"></span> Students Present
                        </span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ number_format($studentsPresent) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                            <span class="w-2.5 h-2.5 rounded-full bg-sky-400 inline-block"></span> Students Absent
                        </span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ number_format($studentsAbsent) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                            <span class="w-2.5 h-2.5 rounded-full bg-purple-600 inline-block"></span> Teachers Present
                        </span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ number_format($teachersPresent) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 inline-block"></span> Late Arrivals
                        </span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ number_format($lateArrivals) }}</span>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-700/60 mt-4">
                <a href="{{ route('attendance.list.show') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                    View Attendance Report <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        {{-- Card 2: Revenue Overview --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="bi bi-currency-rupee text-emerald-500"></i> Revenue Overview
                    </h3>
                    <select class="text-xs border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 bg-slate-50 dark:bg-slate-900 text-slate-600 dark:text-slate-300 focus:outline-none cursor-pointer">
                        <option>This Month</option>
                        <option>Last Month</option>
                    </select>
                </div>

                <div class="mb-3">
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-900 dark:text-white">Rs. {{ number_format($monthRevenue) }}</span>
                        <span class="text-xs text-slate-400 font-semibold">Total Revenue</span>
                    </div>
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-md mt-1">
                        <i class="bi bi-arrow-up-short"></i> 12.5% from last month
                    </span>
                </div>

                {{-- Column Bar Chart --}}
                <div id="chart-revenue-bar" class="h-32 my-1"></div>

                {{-- Sub boxes --}}
                <div class="grid grid-cols-3 gap-2 pt-3 border-t border-slate-100 dark:border-slate-700/60">
                    <div class="bg-slate-50 dark:bg-slate-900/60 p-2 rounded-xl text-center">
                        <p class="text-[9px] text-slate-400 font-semibold truncate">Collected Today</p>
                        <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">Rs. {{ number_format($collectedToday) }}</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-900/60 p-2 rounded-xl text-center">
                        <p class="text-[9px] text-slate-400 font-semibold truncate">Outstanding Fees</p>
                        <p class="text-xs font-bold text-amber-600 dark:text-amber-400 mt-0.5">Rs. {{ number_format($outstandingFees) }}</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-900/60 p-2 rounded-xl text-center">
                        <p class="text-[9px] text-slate-400 font-semibold truncate">Total Invoices</p>
                        <p class="text-xs font-bold text-blue-600 dark:text-blue-400 mt-0.5">{{ number_format($totalInvoices) }}</p>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-700/60 mt-4">
                <a href="{{ route('finance.overview') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                    View Financial Report <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        {{-- Card 3: Pending Approvals & Tasks --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="bi bi-card-checklist text-amber-500"></i> Pending Approvals & Tasks
                    </h3>
                </div>

                <div class="space-y-3">
                    {{-- School Approvals --}}
                    <div class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm">
                                <i class="bi bi-building-check"></i>
                            </div>
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-200">School Approvals</span>
                        </div>
                        <span class="w-6 h-6 rounded-full bg-emerald-500 text-white text-xs font-bold flex items-center justify-center shadow-xs">
                            {{ $pendingTasks['school_approvals'] }}
                        </span>
                    </div>

                    {{-- New Registrations --}}
                    <div class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-sm">
                                <i class="bi bi-person-plus-fill"></i>
                            </div>
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-200">New Registrations</span>
                        </div>
                        <span class="w-6 h-6 rounded-full bg-blue-500 text-white text-xs font-bold flex items-center justify-center shadow-xs">
                            {{ $pendingTasks['new_registrations'] }}
                        </span>
                    </div>

                    {{-- Invoice Payments --}}
                    <div class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center text-sm">
                                <i class="bi bi-receipt"></i>
                            </div>
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-200">Invoice Payments</span>
                        </div>
                        <span class="w-6 h-6 rounded-full bg-rose-500 text-white text-xs font-bold flex items-center justify-center shadow-xs">
                            {{ $pendingTasks['invoice_payments'] }}
                        </span>
                    </div>

                    {{-- Support Tickets --}}
                    <div class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm">
                                <i class="bi bi-chat-dots-fill"></i>
                            </div>
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-200">Support Tickets</span>
                        </div>
                        <span class="w-6 h-6 rounded-full bg-amber-500 text-white text-xs font-bold flex items-center justify-center shadow-xs">
                            {{ $pendingTasks['support_tickets'] }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-700/60 mt-4">
                <a href="{{ route('super.schools.index') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                    View All Tasks <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

    </div>


    {{-- ── 5. MIDDLE GRID ROW 2 (3 CARDS) ────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Card 1: Revenue Trend --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="bi bi-graph-up-arrow text-blue-500"></i> Revenue Trend
                    </h3>
                    <select class="text-xs border border-slate-200 dark:border-slate-700 rounded-lg px-2 py-1 bg-slate-50 dark:bg-slate-900 text-slate-600 dark:text-slate-300 focus:outline-none cursor-pointer">
                        <option>This Year</option>
                        <option>Last Year</option>
                    </select>
                </div>

                <div class="mb-3">
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-900 dark:text-white">Rs. {{ number_format($annualRevenueTotal) }}</span>
                        <span class="text-xs text-slate-400 font-semibold">Total Revenue</span>
                    </div>
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-md mt-1">
                        <i class="bi bi-arrow-up-short"></i> 18.6% from last year
                    </span>
                </div>

                {{-- Area Chart --}}
                <div id="chart-revenue-trend" class="h-48 my-2"></div>
            </div>
        </div>

        {{-- Card 2: School Subscription Status --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="bi bi-pie-chart-fill text-emerald-500"></i> School Subscription Status
                    </h3>
                </div>

                {{-- Donut Chart --}}
                <div class="relative flex items-center justify-center my-2">
                    <div id="chart-subscription-status" class="w-full h-44"></div>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-xl font-black text-slate-900 dark:text-white leading-none">24</span>
                        <span class="text-[9px] text-slate-400 font-semibold mt-0.5">Total Schools</span>
                    </div>
                </div>

                {{-- Legend --}}
                <div class="space-y-2 mt-2 pt-2 border-t border-slate-100 dark:border-slate-700/60">
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                            <span class="w-2.5 h-2.5 rounded-sm bg-emerald-500 inline-block"></span> Active
                        </span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">18 (75%)</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                            <span class="w-2.5 h-2.5 rounded-sm bg-amber-400 inline-block"></span> Trial
                        </span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">3 (12.5%)</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                            <span class="w-2.5 h-2.5 rounded-sm bg-rose-500 inline-block"></span> Expired
                        </span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">2 (8.5%)</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                            <span class="w-2.5 h-2.5 rounded-sm bg-slate-400 inline-block"></span> Cancelled
                        </span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">1 (4%)</span>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-700/60 mt-4">
                <a href="{{ route('super.schools.index') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                    Manage Subscriptions <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        {{-- Card 3: Schools by Plan --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="bi bi-pie-chart text-purple-500"></i> Schools by Plan
                    </h3>
                </div>

                {{-- Donut Chart --}}
                <div class="relative flex items-center justify-center my-2">
                    <div id="chart-schools-by-plan" class="w-full h-44"></div>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-xl font-black text-slate-900 dark:text-white leading-none">24</span>
                        <span class="text-[9px] text-slate-400 font-semibold mt-0.5">Total Schools</span>
                    </div>
                </div>

                {{-- Legend --}}
                <div class="space-y-2 mt-2 pt-2 border-t border-slate-100 dark:border-slate-700/60">
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                            <span class="w-2.5 h-2.5 rounded-sm bg-purple-600 inline-block"></span> Enterprise
                        </span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">10 (41.7%)</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                            <span class="w-2.5 h-2.5 rounded-sm bg-blue-500 inline-block"></span> Growth
                        </span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">6 (25%)</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                            <span class="w-2.5 h-2.5 rounded-sm bg-teal-500 inline-block"></span> Starter
                        </span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">6 (25%)</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                            <span class="w-2.5 h-2.5 rounded-sm bg-amber-400 inline-block"></span> Free
                        </span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">2 (8.3%)</span>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-700/60 mt-4">
                <a href="{{ route('central_analytics.index') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                    View Plan Analytics <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

    </div>


    {{-- ── 6. BOTTOM GRID ROW 3 (3 CARDS) ────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Card 0: Contact Inbox --}}
        @php
            try { $unreadContactCount = \App\Models\ContactMessage::where('status','unread')->count();
                  $recentContacts = \App\Models\ContactMessage::latest()->limit(5)->get();
            } catch(\Throwable $e) { $unreadContactCount = 0; $recentContacts = collect(); }
        @endphp
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-5 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <i class="bi bi-envelope-fill text-indigo-500"></i> Contact Inbox
                </h3>
                @if($unreadContactCount > 0)
                <span class="bg-rose-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $unreadContactCount }} new</span>
                @endif
            </div>

            @if($recentContacts->isNotEmpty())
            <div class="space-y-2.5 flex-1">
                @foreach($recentContacts as $cm)
                <a href="{{ route('super.contact-messages.show', $cm) }}"
                   class="flex items-start gap-2.5 p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/40 transition group no-underline">
                    <div class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 font-bold flex items-center justify-content-center text-xs flex-shrink-0 mt-0.5">
                        {{ strtoupper(substr($cm->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-{{ $cm->status === 'unread' ? 'bold' : 'semibold' }} text-slate-800 dark:text-slate-100 truncate">{{ $cm->name }}</span>
                            @if($cm->status === 'unread')
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 flex-shrink-0"></span>
                            @endif
                        </div>
                        <p class="text-[11px] text-slate-400 truncate m-0">{{ $cm->subject }}</p>
                    </div>
                    <span class="text-[10px] text-slate-300 dark:text-slate-500 flex-shrink-0 mt-0.5">{{ $cm->created_at->diffForHumans(null, true) }}</span>
                </a>
                @endforeach
            </div>
            @else
            <div class="flex-1 flex flex-col items-center justify-center text-center py-4">
                <i class="bi bi-inbox text-3xl text-slate-200 dark:text-slate-600 mb-2"></i>
                <p class="text-xs text-slate-400">No messages yet</p>
            </div>
            @endif

            <a href="{{ route('super.contact-messages.index') }}"
               class="mt-4 text-xs font-semibold text-indigo-600 hover:text-indigo-700 flex items-center gap-1 no-underline">
                View all messages <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        {{-- Card 1: Recent Activity --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="bi bi-clock-history text-blue-500"></i> Recent Activity
                    </h3>
                </div>

                <div class="space-y-4">
                    @foreach($recentActivity as $act)
                    <div class="flex items-start gap-3">
                        <div class="w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-700/60 flex items-center justify-center text-xs flex-shrink-0 mt-0.5">
                            <i class="{{ $act['icon'] }}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 leading-snug">{{ $act['title'] }}</p>
                            <p class="text-[10px] text-slate-400 mt-0.5 font-medium">{{ $act['time'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-700/60 mt-4">
                <a href="{{ route('super.schools.index') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                    View All Activity <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        {{-- Card 2: Upcoming Events --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="bi bi-calendar3 text-indigo-500"></i> Upcoming Events
                    </h3>
                </div>

                <div class="space-y-3">
                    @foreach($upcomingEvents as $evt)
                    <div class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-900/50 transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-sm flex-shrink-0">
                                <i class="bi bi-calendar-event"></i>
                            </div>
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-200 truncate">{{ $evt['title'] }}</span>
                        </div>
                        <span class="px-2 py-1 rounded-md text-[10px] font-bold flex-shrink-0 {{ $evt['badge'] }}">
                            {{ $evt['date'] }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-700/60 mt-4">
                <a href="{{ route('events.show') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                    View Calendar <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        {{-- Card 3: System Overview --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <i class="bi bi-hdd-network text-teal-500"></i> System Overview
                    </h3>
                </div>

                <div class="space-y-4">
                    {{-- Storage Usage --}}
                    <div>
                        <div class="flex justify-between text-xs mb-1.5">
                            <span class="font-semibold text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                                <i class="bi bi-hdd text-slate-400"></i> Storage Usage
                            </span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">42.6 GB / 100 GB</span>
                        </div>
                        <div class="w-full h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-600 rounded-full" style="width: 42%"></div>
                        </div>
                    </div>

                    {{-- Email Usage --}}
                    <div>
                        <div class="flex justify-between text-xs mb-1.5">
                            <span class="font-semibold text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                                <i class="bi bi-envelope text-slate-400"></i> Email Usage (This Month)
                            </span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">12,540 / 50,000</span>
                        </div>
                        <div class="w-full h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-500 rounded-full" style="width: 25%"></div>
                        </div>
                    </div>

                    {{-- SMS Usage --}}
                    <div>
                        <div class="flex justify-between text-xs mb-1.5">
                            <span class="font-semibold text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                                <i class="bi bi-chat-text text-slate-400"></i> SMS Usage (This Month)
                            </span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">8,320 / 20,000</span>
                        </div>
                        <div class="w-full h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-600 rounded-full" style="width: 41%"></div>
                        </div>
                    </div>

                    {{-- Database Performance --}}
                    <div class="flex items-center justify-between text-xs pt-1">
                        <span class="font-semibold text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                            <i class="bi bi-database text-slate-400"></i> Database Performance
                        </span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                            Excellent <i class="bi bi-check-circle-fill text-emerald-500"></i>
                        </span>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-700/60 mt-4">
                <a href="{{ route('central_analytics.index') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                    View System Health <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

    </div>

    {{-- ── 7. FOOTER ─────────────────────────────────────────────────── --}}
    <div class="pt-6 border-t border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400 gap-2">
        <p>&copy; 2026 NepalSchoolHub. All rights reserved.</p>
        <div class="flex items-center gap-4">
            <span class="font-mono text-[11px]">v2.1.0</span>
            <a href="#" class="hover:text-slate-600 dark:hover:text-slate-300 transition-colors">Help</a>
            <a href="#" class="hover:text-slate-600 dark:hover:text-slate-300 transition-colors">Documentation</a>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    var textMuted = isDark ? '#94a3b8' : '#64748b';
    var gridBorder = isDark ? '#334155' : '#f1f5f9';

    // Helper for sparklines
    function createSparkline(elementId, color, data) {
        var el = document.getElementById(elementId);
        if (!el) return;
        new ApexCharts(el, {
            chart: {
                type: 'area',
                sparkline: { enabled: true },
                height: 48,
                animations: { enabled: true }
            },
            series: [{ data: data }],
            stroke: { curve: 'smooth', width: 2.5 },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                }
            },
            colors: [color],
            tooltip: { enabled: false }
        }).render();
    }

    createSparkline('sparkline-students', '#2563eb', @json($sparklineStudents));
    createSparkline('sparkline-teachers', '#10b981', @json($sparklineTeachers));
    createSparkline('sparkline-schools',  '#8b5cf6', @json($sparklineSchools));
    createSparkline('sparkline-revenue',  '#14b8a6', @json($sparklineRevenue));
    createSparkline('sparkline-fees',     '#f59e0b', @json($sparklineFees));

    // 1. Attendance Overview Donut Chart
    var attEl = document.getElementById('chart-attendance-overview');
    if (attEl) {
        new ApexCharts(attEl, {
            chart: { type: 'donut', height: 180 },
            series: [{{ $studentsPresent }}, {{ $studentsAbsent }}, {{ $teachersPresent }}, {{ $lateArrivals }}],
            labels: ['Students Present', 'Students Absent', 'Teachers Present', 'Late Arrivals'],
            colors: ['#2563eb', '#38bdf8', '#8b5cf6', '#f59e0b'],
            dataLabels: { enabled: false },
            legend: { show: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: { show: false }
                    }
                }
            },
            stroke: { width: 0 }
        }).render();
    }

    // 2. Revenue Overview Bar Chart
    var revBarEl = document.getElementById('chart-revenue-bar');
    if (revBarEl) {
        new ApexCharts(revBarEl, {
            chart: { type: 'bar', height: 140, toolbar: { show: false } },
            series: [{ name: 'Revenue', data: @json(array_values($revenueBarData)) }],
            xaxis: {
                categories: @json(array_keys($revenueBarData)),
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { colors: textMuted, fontSize: '10px' } }
            },
            yaxis: { show: false },
            colors: ['#2563eb'],
            plotOptions: { bar: { borderRadius: 4, columnWidth: '45%' } },
            dataLabels: { enabled: false },
            grid: { show: false },
            tooltip: { y: { formatter: function(v) { return '$' + v.toLocaleString(); } } }
        }).render();
    }

    // 3. Revenue Trend Area Chart
    var revTrendEl = document.getElementById('chart-revenue-trend');
    if (revTrendEl) {
        new ApexCharts(revTrendEl, {
            chart: { type: 'area', height: 190, toolbar: { show: false } },
            series: [{ name: 'Revenue', data: @json(array_values($revenueTrendData)) }],
            xaxis: {
                categories: @json(array_keys($revenueTrendData)),
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { colors: textMuted, fontSize: '10px' } }
            },
            yaxis: {
                labels: {
                    style: { colors: textMuted, fontSize: '10px' },
                    formatter: function(v) { return '$' + (v >= 1000 ? (v / 1000).toFixed(0) + 'K' : v); }
                }
            },
            colors: ['#2563eb'],
            stroke: { curve: 'smooth', width: 3 },
            fill: {
                type: 'gradient',
                gradient: { opacityFrom: 0.4, opacityTo: 0.05 }
            },
            dataLabels: { enabled: false },
            grid: { borderColor: gridBorder, strokeDashArray: 4 },
            markers: {
                size: 5,
                colors: ['#2563eb'],
                strokeColors: '#ffffff',
                strokeWidth: 2,
                hover: { size: 7 }
            },
            tooltip: { y: { formatter: function(v) { return '$' + v.toLocaleString(); } } }
        }).render();
    }

    // 4. Subscription Status Donut Chart
    var subStatusEl = document.getElementById('chart-subscription-status');
    if (subStatusEl) {
        new ApexCharts(subStatusEl, {
            chart: { type: 'donut', height: 170 },
            series: [18, 3, 2, 1],
            labels: ['Active', 'Trial', 'Expired', 'Cancelled'],
            colors: ['#10b981', '#fbbf24', '#f43f5e', '#94a3b8'],
            dataLabels: { enabled: false },
            legend: { show: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: { show: false }
                    }
                }
            },
            stroke: { width: 0 }
        }).render();
    }

    // 5. Schools by Plan Donut Chart
    var planEl = document.getElementById('chart-schools-by-plan');
    if (planEl) {
        new ApexCharts(planEl, {
            chart: { type: 'donut', height: 170 },
            series: [10, 6, 6, 2],
            labels: ['Enterprise', 'Growth', 'Starter', 'Free'],
            colors: ['#8b5cf6', '#3b82f6', '#14b8a6', '#fbbf24'],
            dataLabels: { enabled: false },
            legend: { show: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: { show: false }
                    }
                }
            },
            stroke: { width: 0 }
        }).render();
    }
});
</script>
@endpush
