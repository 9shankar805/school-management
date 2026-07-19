@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Admissions</h1>
                <p class="text-slate-400 text-sm mt-0.5">Manage student applications</p>
            </div>
            <a href="{{ route('admissions.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                <i class="bi bi-plus-lg"></i> New Application
            </a>
        </div>

        @if(session('status'))
        <div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>
        @endif

        {{-- Status filter tabs --}}
        <div class="flex gap-1 bg-white rounded-xl border border-slate-100 shadow-sm p-1 mb-6 overflow-x-auto">
            @foreach(['all' => 'All', 'pending' => 'Pending', 'under_review' => 'Under Review', 'approved' => 'Approved', 'rejected' => 'Rejected', 'enrolled' => 'Enrolled'] as $s => $l)
            <a href="{{ route('admissions.index', ['status' => $s]) }}"
               class="flex-shrink-0 flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition whitespace-nowrap
                      {{ $status === $s ? 'bg-indigo-50 text-indigo-700' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">
                {{ $l }}
                @if(isset($counts[$s]))<span class="ml-1 text-[10px] bg-slate-200 px-1.5 py-0.5 rounded-full">{{ $counts[$s] }}</span>@endif
            </a>
            @endforeach
        </div>

        {{-- Search --}}
        <form method="GET" action="{{ route('admissions.index') }}" class="mb-4 flex gap-2">
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, email, application number…"
                class="flex-1 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <button type="submit" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-700 hover:bg-slate-50 transition">Search</button>
        </form>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            @if($applications->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-slate-400 bg-slate-50">
                            <th class="px-5 py-3 font-medium">Application #</th>
                            <th class="px-5 py-3 font-medium">Name</th>
                            <th class="px-5 py-3 font-medium">Class</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium">Submitted</th>
                            <th class="px-5 py-3 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($applications as $app)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $app->application_number }}</td>
                            <td class="px-5 py-3 font-medium text-slate-700">{{ $app->applicant_name }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $app->schoolClass?->class_name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $app->status_badge }}">{{ ucwords(str_replace('_',' ',$app->status)) }}</span>
                            </td>
                            <td class="px-5 py-3 text-slate-400">{{ $app->created_at->format('d M Y') }}</td>
                            <td class="px-5 py-3">
                                <a href="{{ route('admissions.show', $app->id) }}" class="text-xs text-indigo-600 hover:underline">Review</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-slate-50">{{ $applications->links() }}</div>
            @else
            <p class="text-sm text-slate-400 text-center py-12">No applications found.</p>
            @endif
        </div>

    </div>
</div>
@endsection
