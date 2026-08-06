@extends('layouts.app')
@section('title', 'Contact Messages Inbox')

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,#4f46e5,#6366f1);display:inline-flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(79,70,229,.3);">
                <i class="bi bi-envelope-fill text-white" style="font-size:22px;"></i>
            </div>
            <div>
                <h2 class="h3 font-extrabold text-slate-800 mb-0">Contact Messages Inbox</h2>
                <p class="text-slate-500 text-sm mb-0">Messages submitted via the NepalSchoolHub contact form</p>
            </div>
        </div>
        @if($counts['unread'] > 0)
        <span class="badge bg-rose-600 text-white rounded-full px-3 py-1.5 font-bold text-sm">
            <i class="bi bi-envelope-fill me-1"></i> {{ $counts['unread'] }} Unread
        </span>
        @endif
    </div>

    @include('session-messages')

    {{-- Status filter tabs --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-4">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            @php
                $tabs = [
                    'all'      => ['label' => 'All',      'icon' => 'bi-inbox',         'count' => $counts['all']],
                    'unread'   => ['label' => 'Unread',   'icon' => 'bi-envelope-fill', 'count' => $counts['unread'],  'color' => 'rose'],
                    'read'     => ['label' => 'Read',     'icon' => 'bi-envelope-open', 'count' => $counts['read'],    'color' => 'slate'],
                    'replied'  => ['label' => 'Replied',  'icon' => 'bi-reply-fill',    'count' => $counts['replied'], 'color' => 'emerald'],
                    'archived' => ['label' => 'Archived', 'icon' => 'bi-archive',       'count' => $counts['archived'],'color' => 'amber'],
                ];
            @endphp
            @foreach($tabs as $key => $tab)
            <a href="{{ route('super.contact-messages.index', array_filter(['status' => $key === 'all' ? null : $key, 'search' => $search])) }}"
               class="btn btn-sm {{ $status === $key || ($key === 'all' && $status === 'all') ? 'btn-indigo' : 'btn-light border border-slate-200 text-slate-600' }} rounded-xl font-semibold px-3 py-1.5 text-xs">
                <i class="bi {{ $tab['icon'] }} me-1"></i> {{ $tab['label'] }}
                <span class="badge {{ $status === $key ? 'bg-white text-indigo-700' : 'bg-slate-200 text-slate-600' }} ms-1 rounded-full px-1.5" style="font-size:10px;">{{ $tab['count'] }}</span>
            </a>
            @endforeach
        </div>

        {{-- Search --}}
        <form method="GET" action="{{ route('super.contact-messages.index') }}" class="d-flex gap-2">
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search name, email, school, subject…"
                   class="form-control form-control-sm rounded-xl border-slate-200 text-sm" style="max-width:360px;">
            <button type="submit" class="btn btn-sm btn-indigo rounded-xl px-3"><i class="bi bi-search"></i></button>
            @if($search)
            <a href="{{ route('super.contact-messages.index', ['status' => $status]) }}" class="btn btn-sm btn-light border border-slate-200 rounded-xl px-3">Clear</a>
            @endif
        </form>
    </div>

    {{-- Messages table --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        @if($messages->count())
        <div class="table-responsive">
            <table class="table align-middle text-sm mb-0">
                <thead>
                    <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                        <th class="py-3 px-4 rounded-s-xl">From</th>
                        <th class="py-3 px-4">Subject</th>
                        <th class="py-3 px-4">School</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Received</th>
                        <th class="py-3 px-4 rounded-e-xl text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($messages as $msg)
                    <tr class="{{ $msg->status === 'unread' ? 'bg-indigo-50/40' : '' }}">
                        <td class="py-3 px-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-bold d-flex align-items-center justify-content-center text-xs flex-shrink-0">
                                    {{ strtoupper(substr($msg->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800 {{ $msg->status === 'unread' ? '' : 'font-normal' }}">{{ $msg->name }}</div>
                                    <div class="text-xs text-slate-400">{{ $msg->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-semibold text-slate-700">{{ Str::limit($msg->subject, 45) }}</span>
                            <div class="text-xs text-slate-400 mt-0.5">{{ Str::limit($msg->message, 60) }}</div>
                        </td>
                        <td class="py-3 px-4">
                            @if($msg->school_name)
                            <span class="badge bg-slate-100 text-slate-700 text-xs px-2 py-1 rounded-lg font-semibold">{{ $msg->school_name }}</span>
                            @else
                            <span class="text-slate-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">{!! $msg->status_badge !!}</td>
                        <td class="py-3 px-4 text-xs text-slate-500 whitespace-nowrap">
                            {{ $msg->created_at->format('M j, Y') }}<br>
                            <span class="text-slate-400">{{ $msg->created_at->format('g:i A') }}</span>
                        </td>
                        <td class="py-3 px-4 text-end">
                            <a href="{{ route('super.contact-messages.show', $msg) }}"
                               class="btn btn-sm btn-indigo rounded-xl text-xs px-3 py-1">
                                <i class="bi bi-eye me-1"></i> View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($messages->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">
            {{ $messages->links() }}
        </div>
        @endif

        @else
        <div class="text-center py-5 text-slate-400">
            <i class="bi bi-inbox display-3 text-slate-200 mb-3 block"></i>
            <h6 class="font-bold text-slate-600 mb-1">No messages found</h6>
            <p class="mb-0 text-xs">
                @if($search) No messages match "{{ $search }}". @else No {{ $status !== 'all' ? $status : '' }} messages yet. @endif
            </p>
        </div>
        @endif
    </div>
</div>
@endsection
