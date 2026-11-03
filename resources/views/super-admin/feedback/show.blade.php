@extends('layouts.app')
@section('title', 'Ticket Details #' . $feedback->id . ' - Super Admin')

@section('content')
<div class="px-6 py-6 max-w-4xl mx-auto space-y-6">

    {{-- Header & Back button --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('super.feedback.index') }}" class="hover:text-indigo-600 transition flex items-center gap-1">
                    <i class="bi bi-chat-heart"></i> Feedback &amp; Grievances
                </a>
                <span>&bull;</span>
                <span>Ticket #{{ $feedback->id }}</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight mt-1">
                Ticket #{{ $feedback->id }}: {{ $feedback->title }}
            </h1>
        </div>

        <a href="{{ route('super.feedback.index') }}"
            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs transition">
            <i class="bi bi-arrow-left me-1"></i> Back to Feedback List
        </a>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-2">
            <i class="bi bi-check-circle-fill text-emerald-600"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Ticket Content --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-3">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Grievance Description</span>
                    <span class="text-xs text-slate-400">{{ $feedback->created_at ? $feedback->created_at->format('M d, Y - g:i A') : '' }}</span>
                </div>

                <div class="prose dark:prose-invert max-w-none text-sm text-slate-700 dark:text-slate-200 leading-relaxed whitespace-pre-line">
                    {{ $feedback->description }}
                </div>
            </div>

            {{-- Admin Response Form --}}
            <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-6 shadow-xs space-y-4">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2 border-b border-slate-100 dark:border-slate-700/60 pb-3">
                    <i class="bi bi-reply-fill text-indigo-600"></i>
                    Administrative Response &amp; Action (प्रशासनिक जवाफ तथा समाधान)
                </h3>

                <form action="{{ route('super.feedback.status', $feedback->id) }}" method="POST" class="space-y-4 text-xs">
                    @csrf

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Update Ticket Status <span class="text-rose-500">*</span>
                        </label>
                        <select name="status" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white font-semibold">
                            <option value="pending" {{ $feedback->status === 'pending' ? 'selected' : '' }}>Pending (प्रतिक्षारत)</option>
                            <option value="in_review" {{ $feedback->status === 'in_review' ? 'selected' : '' }}>Under Review (अनुसन्धान भइरहेको)</option>
                            <option value="resolved" {{ $feedback->status === 'resolved' ? 'selected' : '' }}>Resolved (समाधान भयो)</option>
                            <option value="rejected" {{ $feedback->status === 'rejected' ? 'selected' : '' }}>Rejected (अस्वीकृत)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Resolution Message / Reply to Submitter
                        </label>
                        <textarea name="admin_response" rows="4" placeholder="Explain the resolution or notes taken..."
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none dark:text-white">{{ $feedback->admin_response }}</textarea>
                    </div>

                    <div class="flex items-center justify-end">
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5">
                            <i class="bi bi-check2"></i> Save Status &amp; Response
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Meta Sidebar --}}
        <div class="space-y-4">
            <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 rounded-2xl p-5 shadow-xs space-y-4 text-xs">
                <h4 class="font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-700/60 pb-2">
                    Ticket Metadata
                </h4>

                <div>
                    <span class="text-slate-400 block text-[11px]">Submitter</span>
                    @if($feedback->is_anonymous)
                        <span class="font-semibold text-slate-600 dark:text-slate-300 italic"><i class="bi bi-incognito"></i> Anonymous</span>
                    @else
                        <span class="font-bold text-slate-800 dark:text-slate-100">{{ $feedback->submitter_name ?? $feedback->user?->full_name }}</span>
                        <span class="text-[10px] text-slate-400 block capitalize">{{ $feedback->submitter_role }}</span>
                    @endif
                </div>

                @if($school)
                    <div>
                        <span class="text-slate-400 block text-[11px]">School Name</span>
                        <span class="font-bold text-slate-800 dark:text-slate-100">{{ $school->name }}</span>
                    </div>
                @endif

                <div>
                    <span class="text-slate-400 block text-[11px]">Category</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-300 capitalize">{{ str_replace('_', ' ', $feedback->category) }}</span>
                </div>

                <div>
                    <span class="text-slate-400 block text-[11px]">Priority</span>
                    <span class="font-bold uppercase tracking-wider {{ $feedback->priority === 'urgent' ? 'text-rose-600' : ($feedback->priority === 'high' ? 'text-amber-600' : 'text-blue-600') }}">
                        {{ $feedback->priority }}
                    </span>
                </div>

                @if($feedback->resolved_at)
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-700/60">
                        <span class="text-slate-400 block text-[11px]">Resolved At</span>
                        <span class="font-semibold text-emerald-600">{{ $feedback->resolved_at->format('M d, Y') }}</span>
                        @if($feedback->resolver)
                            <span class="text-[10px] text-slate-400 block">by {{ $feedback->resolver->first_name }}</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

