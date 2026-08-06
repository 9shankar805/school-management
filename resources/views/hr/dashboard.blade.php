@extends('layouts.app')
@section('title', 'HR Dashboard')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-7xl mx-auto">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-10 h-10 rounded-2xl bg-blue-600/10 text-blue-600 flex items-center justify-center font-bold text-xl shadow-xs">
                    <i class="bi bi-people-fill"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">HR Dashboard</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Overview of staff, leave requests, and recruitment</p>
                </div>
            </div>
        </div>
        <div>
            <a href="{{ route('hr.employees') }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md transition flex items-center gap-2">
                <i class="bi bi-person-plus"></i> Add Employee
            </a>
        </div>
    </div>

    @include('session-messages')

    {{-- Quick Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-4">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center">
                    <i class="bi bi-people text-lg"></i>
                </span>
                <div>
                    <div class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $totalStaff ?? 0 }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">Total Active Staff</div>
                </div>
            </div>
        </div>
        
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-4">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center">
                    <i class="bi bi-calendar-x text-lg"></i>
                </span>
                <div>
                    <div class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $pendingLeaves ?? 0 }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">Pending Leave Requests</div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-4">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                    <i class="bi bi-briefcase text-lg"></i>
                </span>
                <div>
                    <div class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $activeJobs ?? 0 }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">Active Job Postings</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Area --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Recent Job Applications --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                <span class="w-2 h-5 bg-blue-600 rounded-full"></span>
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">Recent Job Applications</h2>
            </div>
            @if(isset($recentApplications) && $recentApplications->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-semibold bg-slate-50/50 dark:bg-slate-800/20">
                            <tr>
                                <th class="py-3 px-4">Applicant</th>
                                <th class="py-3 px-4">Position</th>
                                <th class="py-3 px-4">Applied On</th>
                                <th class="py-3 px-4 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentApplications as $app)
                            <tr class="border-b border-slate-50 dark:border-slate-800/50 hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                                <td class="py-3 px-4 font-semibold text-slate-800 dark:text-slate-100">{{ $app->applicant_name }}</td>
                                <td class="py-3 px-4">{{ $app->jobPosting->title ?? 'N/A' }}</td>
                                <td class="py-3 px-4">{{ $app->created_at->format('M d, Y') }}</td>
                                <td class="py-3 px-4 text-right">
                                    <span class="px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-semibold">{{ ucfirst($app->status) }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <div class="text-slate-300"><i class="bi bi-inbox text-4xl"></i></div>
                    <p class="mt-2 text-sm text-slate-400">No recent applications.</p>
                </div>
            @endif
        </div>

        {{-- Quick Links --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-6">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                <span class="w-2 h-5 bg-indigo-600 rounded-full"></span>
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-100">HR Operations</h2>
            </div>
            <div class="flex flex-col gap-3">
                <a href="{{ route('hr.leaves') }}" class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <span class="w-10 h-10 rounded-lg bg-white dark:bg-slate-700 flex items-center justify-center shadow-sm">
                        <i class="bi bi-calendar-check text-blue-600"></i>
                    </span>
                    <div>
                        <div class="font-semibold text-slate-800 dark:text-slate-100 text-sm">Review Leaves</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Approve or reject requests</div>
                    </div>
                </a>
                <a href="{{ route('hr.payroll') }}" class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <span class="w-10 h-10 rounded-lg bg-white dark:bg-slate-700 flex items-center justify-center shadow-sm">
                        <i class="bi bi-cash text-emerald-600"></i>
                    </span>
                    <div>
                        <div class="font-semibold text-slate-800 dark:text-slate-100 text-sm">Process Payroll</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Generate salary slips</div>
                    </div>
                </a>
                <a href="{{ route('hr.recruitment') }}" class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <span class="w-10 h-10 rounded-lg bg-white dark:bg-slate-700 flex items-center justify-center shadow-sm">
                        <i class="bi bi-person-bounding-box text-purple-600"></i>
                    </span>
                    <div>
                        <div class="font-semibold text-slate-800 dark:text-slate-100 text-sm">Recruitment</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Manage job postings</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
