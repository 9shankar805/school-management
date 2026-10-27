@extends('layouts.app')
@section('title', 'Notice Board & Circulars')

@section('content')
<style>
  .notice-hero-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%) !important;
    color: #ffffff !important;
    box-shadow: 0 4px 14px rgba(59, 130, 246, 0.3);
  }
  .notice-card {
    transition: all 0.2s ease-in-out;
    border: 1px solid #e2e8f0;
  }
  .notice-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
  }
</style>

<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="notice-hero-icon">
                <i class="bi bi-megaphone-fill fs-4"></i>
            </div>
            <div>
                <h2 class="h3 font-extrabold text-slate-800 mb-0">Notice Board & Official Circulars</h2>
                <p class="text-slate-500 text-sm mb-0">Publish and manage campus announcements, emergency alerts, and circulars</p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            @can('create notices')
            <a href="{{ route('notice.create') }}" class="btn btn-primary bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
                <i class="bi bi-plus-lg me-1.5"></i> Post New Notice
            </a>
            @endcan
            <a href="{{ route('communication.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100 transition">
                <i class="bi bi-arrow-left me-1.5"></i> Back to Hub
            </a>
        </div>
    </div>

    @include('session-messages')

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Notices</span>
                        <h3 class="text-2xl font-bold text-slate-800 mt-1 mb-0">{{ $stats['total'] ?? 0 }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 d-flex align-items-center justify-content-center">
                        <i class="bi bi-bell-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-xs font-semibold text-rose-500 uppercase tracking-wider">Urgent Alerts</span>
                        <h3 class="text-2xl font-bold text-rose-600 mt-1 mb-0">{{ $stats['urgent'] ?? 0 }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 d-flex align-items-center justify-content-center">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-xs font-semibold text-emerald-500 uppercase tracking-wider">Holiday Circulars</span>
                        <h3 class="text-2xl font-bold text-emerald-600 mt-1 mb-0">{{ $stats['holidays'] ?? 0 }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 d-flex align-items-center justify-content-center">
                        <i class="bi bi-calendar-check-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-xs font-semibold text-purple-500 uppercase tracking-wider">Exams & Tests</span>
                        <h3 class="text-2xl font-bold text-purple-600 mt-1 mb-0">{{ $stats['exams'] ?? 0 }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 d-flex align-items-center justify-content-center">
                        <i class="bi bi-journal-text fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-sm mb-4">
        <form action="{{ route('notice.index') }}" method="GET" class="row g-3 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-slate-50 border-slate-200 text-slate-400">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" class="form-control border-slate-200" placeholder="Search notices by keyword or title...">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <select name="audience" class="form-select border-slate-200" onchange="this.form.submit()">
                    <option value="all" {{ $audience === 'all' || !$audience ? 'selected' : '' }}>Audience: Everyone (All)</option>
                    <option value="parents" {{ $audience === 'parents' ? 'selected' : '' }}>Audience: Parents Only</option>
                    <option value="students" {{ $audience === 'students' ? 'selected' : '' }}>Audience: Students Only</option>
                    <option value="teachers" {{ $audience === 'teachers' ? 'selected' : '' }}>Audience: Teachers & Staff Only</option>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-dark w-100 font-semibold rounded-xl">
                    Filter
                </button>
                @if($search || ($audience && $audience !== 'all'))
                <a href="{{ route('notice.index') }}" class="btn btn-light border border-slate-200 rounded-xl">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Notices Listing -->
    @if($notices->isEmpty())
    <div class="bg-white rounded-2xl border border-slate-100 p-5 text-center shadow-sm">
        <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 d-inline-flex align-items-center justify-content-center mb-3">
            <i class="bi bi-megaphone fs-3"></i>
        </div>
        <h5 class="font-bold text-slate-700">No Notices Found</h5>
        <p class="text-slate-400 text-sm mb-3">No announcements have been posted for the selected criteria yet.</p>
        @can('create notices')
        <a href="{{ route('notice.create') }}" class="btn btn-primary bg-indigo-600 text-white font-semibold rounded-xl px-4 py-2">
            Create First Notice
        </a>
        @endcan
    </div>
    @else
    <div class="space-y-4">
        @foreach($notices as $item)
        @php
            $priorityBadge = match($item->priority) {
                'urgent'  => 'bg-rose-100 text-rose-700 border-rose-200',
                'holiday' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                'exam'    => 'bg-purple-100 text-purple-700 border-purple-200',
                default   => 'bg-blue-100 text-blue-700 border-blue-200',
            };
            $audienceLabel = match($item->audience) {
                'parents'  => 'Parents Only',
                'students' => 'Students Only',
                'teachers' => 'Staff / Teachers',
                default    => 'General (All)',
            };
        @endphp
        <div class="bg-white rounded-2xl p-5 shadow-sm notice-card">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                <div class="flex-grow-1">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="badge {{ $priorityBadge }} text-xs px-2.5 py-1 rounded-full font-bold uppercase border">
                            {{ ucfirst($item->priority ?? 'normal') }}
                        </span>
                        <span class="badge bg-slate-100 text-slate-600 text-xs px-2.5 py-1 rounded-full font-medium">
                            <i class="bi bi-people-fill me-1"></i>{{ $audienceLabel }}
                        </span>
                        @if($item->expiry_date)
                        <span class="badge bg-amber-50 text-amber-700 text-xs px-2.5 py-1 rounded-full border border-amber-200 font-medium">
                            <i class="bi bi-clock-history me-1"></i>Valid till: {{ $item->expiry_date->format('M d, Y') }}
                        </span>
                        @endif
                        <span class="text-xs text-slate-400 ms-auto">
                            <i class="bi bi-calendar3 me-1"></i>{{ $item->created_at->format('M d, Y · h:i A') }}
                        </span>
                    </div>

                    <h4 class="font-bold text-slate-800 text-lg mb-2">
                        {{ $item->title ?: 'Notice #' . $item->id }}
                    </h4>

                    <div class="text-slate-600 text-sm mb-3 prose max-w-none">
                        {!! \Illuminate\Support\Str::limit(strip_tags($item->notice), 260) !!}
                    </div>

                    @if($item->attachment_path)
                    <div class="mb-3">
                        <a href="{{ asset('storage/' . $item->attachment_path) }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 border border-indigo-100 px-3 py-1.5 rounded-lg hover:bg-indigo-100 transition">
                            <i class="bi bi-paperclip fs-6"></i> Download Official Circular Attachment
                        </a>
                    </div>
                    @endif

                    <div class="text-xs text-slate-400">
                        <span>Published by: <strong>{{ $item->publisher->full_name ?? 'School Administration' }}</strong></span>
                    </div>
                </div>

                <div class="d-flex flex-row flex-md-column gap-2 flex-shrink-0 align-self-end align-self-md-start">
                    <a href="{{ route('notice.show', $item->id) }}" class="btn btn-sm btn-light border border-slate-200 text-slate-700 font-medium rounded-xl px-3 py-1.5 hover:bg-slate-50">
                        <i class="bi bi-eye me-1"></i> View Full
                    </a>
                    @can('create notices')
                    <a href="{{ route('notice.edit', $item->id) }}" class="btn btn-sm btn-light border border-slate-200 text-blue-600 font-medium rounded-xl px-3 py-1.5 hover:bg-blue-50">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    <form action="{{ route('notice.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this notice?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-light border border-slate-200 text-rose-600 font-medium rounded-xl px-3 py-1.5 hover:bg-rose-50 w-100">
                            <i class="bi bi-trash me-1"></i> Delete
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
        @endforeach

        <div class="mt-4">
            {{ $notices->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
