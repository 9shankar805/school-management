@extends('layouts.app')
@section('title', 'User Feedback & Grievances - Super Admin')

@section('content')
<div class="px-6 py-6 max-w-7xl mx-auto space-y-6">

    {{-- Top Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">
                    SaaS Platform
                </span>
                <span class="text-xs text-slate-400">&bull; प्रयोगकर्ता पृष्ठपोषण तथा गुनासो</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1 flex items-center gap-2.5">
                <i class="bi bi-chat-heart-fill text-rose-500"></i>
                User Feedback &amp; Grievances (प्रयोगकर्ता पृष्ठपोषण)
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Review, respond, and resolve complaints and suggestions submitted by teachers, students, parents, and school administrators.
            </p>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm flex items-center gap-2">
            <i class="bi bi-check-circle-fill text-emerald-600 text-base"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Metric Stat Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg">
                    <i class="bi bi-chat-dots-fill"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-400 font-medium">Total Feedback</p>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white">{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-amber-200/80 dark:border-amber-800/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <p class="text-xs text-amber-700 dark:text-amber-300 font-bold uppercase tracking-wider">Pending</p>
                    <h3 class="text-xl font-black text-amber-600 dark:text-amber-400">{{ $stats['pending'] }}</h3>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-blue-200/80 dark:border-blue-800/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg">
                    <i class="bi bi-eye-fill"></i>
                </div>
                <div>
                    <p class="text-xs text-blue-700 dark:text-blue-300 font-bold uppercase tracking-wider">In Review</p>
                    <h3 class="text-xl font-black text-blue-600 dark:text-blue-400">{{ $stats['in_review'] }}</h3>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-emerald-200/80 dark:border-emerald-800/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <p class="text-xs text-emerald-700 dark:text-emerald-300 font-bold uppercase tracking-wider">Resolved</p>
                    <h3 class="text-xl font-black text-emerald-600 dark:text-emerald-400">{{ $stats['resolved'] }}</h3>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-rose-200/80 dark:border-rose-800/80 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div>
                    <p class="text-xs text-rose-700 dark:text-rose-300 font-bold uppercase tracking-wider">Urgent</p>
                    <h3 class="text-xl font-black text-rose-600 dark:text-rose-400">{{ $stats['urgent'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-4 shadow-xs">
        <form action="{{ route('super.feedback.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[200px] relative">
                <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search feedback title, description, submitter..."
                    class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
            </div>

            <div class="w-40">
                <select name="status" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_review" {{ request('status') === 'in_review' ? 'selected' : '' }}>Under Review</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <div class="w-40">
                <select name="priority" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
                    <option value="">All Priorities</option>
                    <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                </select>
            </div>

            <div class="w-48">
                <select name="school_id" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">
                    <option value="">All Schools</option>
                    @foreach($schools as $sch)
                        <option value="{{ $sch->id }}" {{ request('school_id') == $sch->id ? 'selected' : '' }}>
                            {{ $sch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold">
                Filter
            </button>
            @if(request()->hasAny(['search', 'status', 'priority', 'school_id']))
                <a href="{{ route('super.feedback.index') }}" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-800 dark:hover:text-slate-200">
                    Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Feedback Table --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 dark:bg-slate-900/40 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200/80 dark:border-slate-700/80">
                    <tr>
                        <th class="px-5 py-3.5">Ticket #</th>
                        <th class="px-4 py-3.5">Title &amp; Category</th>
                        <th class="px-4 py-3.5">Submitter &amp; Role</th>
                        <th class="px-4 py-3.5">Priority</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5">Date</th>
                        <th class="px-5 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-medium">
                    @forelse($feedbacks as $fb)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-750 transition-colors">
                            <td class="px-5 py-3.5 whitespace-nowrap font-mono font-bold text-slate-400">
                                #{{ $fb->id }}
                            </td>

                            <td class="px-4 py-3.5">
                                <div>
                                    <a href="{{ route('super.feedback.show', $fb->id) }}" class="font-bold text-slate-900 dark:text-white text-sm hover:text-indigo-600 transition">
                                        {{ $fb->title }}
                                    </a>
                                    <div class="flex flex-wrap items-center gap-1.5 mt-0.5">
                                        @if($fb->school)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800">
                                                <i class="bi bi-building text-[9px]"></i>
                                                {{ $fb->school->name }}
                                            </span>
                                        @endif
                                        <span class="px-2 py-0.5 rounded text-[10px] bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                            {{ ucfirst(str_replace('_', ' ', $fb->category)) }}
                                        </span>
                                        @if($fb->admin_response)
                                            <span class="text-[10px] text-emerald-600 flex items-center gap-1 font-semibold">
                                                <i class="bi bi-check2"></i> Responded
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if($fb->is_anonymous)
                                    <span class="text-slate-400 italic flex items-center gap-1">
                                        <i class="bi bi-incognito"></i> Anonymous User
                                    </span>
                                @else
                                    <div>
                                        <span class="font-semibold text-slate-800 dark:text-slate-200">
                                            {{ $fb->submitter_name ?? $fb->user?->full_name ?? 'User' }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 block capitalize">
                                            {{ $fb->submitter_role ?? 'Member' }}
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if($fb->priority === 'urgent')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">Urgent</span>
                                @elseif($fb->priority === 'high')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">High</span>
                                @elseif($fb->priority === 'medium')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-700">Medium</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600">Low</span>
                                @endif
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if($fb->status === 'resolved')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700">Resolved</span>
                                @elseif($fb->status === 'in_review')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-100 text-blue-700">Under Review</span>
                                @elseif($fb->status === 'rejected')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-200 text-slate-700">Rejected</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">Pending</span>
                                @endif
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap text-slate-400 text-[11px]">
                                {{ $fb->created_at ? $fb->created_at->format('M d, Y') : '—' }}
                            </td>

                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <a href="{{ route('super.feedback.show', $fb->id) }}"
                                    class="px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold text-xs transition">
                                    View &amp; Reply
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <i class="bi bi-chat-heart text-3xl mb-2 block opacity-50"></i>
                                No user feedback tickets found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($feedbacks->hasPages())
            <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/30">
                {{ $feedbacks->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
