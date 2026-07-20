@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Homework</h1>
                <p class="text-slate-400 text-sm mt-0.5">Assignments you have set for your classes</p>
            </div>
            @can('create homework')
            <a href="{{ route('homework.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                <i class="bi bi-plus-lg"></i> New Homework
            </a>
            @endcan
        </div>

        @include('session-messages')

        <div class="space-y-3">
            @forelse($homeworks as $hw)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:shadow-md transition">
                <div class="flex flex-wrap justify-between items-start gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <h3 class="font-semibold text-slate-800">{{ $hw->title }}</h3>
                            <span class="text-[11px] px-2 py-0.5 rounded-full font-medium
                                {{ $hw->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ ucfirst($hw->status) }}
                            </span>
                            @if($hw->due_date->isPast())
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-rose-100 text-rose-700">Overdue</span>
                            @elseif($hw->due_date->isToday())
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">Due Today</span>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-4 text-xs text-slate-500">
                            <span><i class="bi bi-book me-1"></i>{{ $hw->course->course_name ?? '—' }}</span>
                            <span><i class="bi bi-layers me-1"></i>{{ $hw->schoolClass->class_name ?? '—' }}
                                @if($hw->section) · {{ $hw->section->section_name }}@endif
                            </span>
                            <span><i class="bi bi-calendar-event me-1"></i>Due: {{ $hw->due_date->format('M d, Y') }}</span>
                            <span><i class="bi bi-award me-1"></i>{{ $hw->total_marks }} marks</span>
                            <span><i class="bi bi-people me-1"></i>{{ $hw->submissions_count }} submissions</span>
                        </div>
                        @if($hw->description)
                        <p class="mt-1.5 text-xs text-slate-400 line-clamp-2">{{ $hw->description }}</p>
                        @endif
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        <a href="{{ route('homework.show', $hw->id) }}"
                           class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-medium transition">
                            <i class="bi bi-eye text-xs"></i> View
                        </a>
                        <form action="{{ route('homework.toggle-status', $hw->id) }}" method="POST">
                            @csrf
                            <button class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-xs font-medium transition"
                                    title="{{ $hw->status === 'active' ? 'Close' : 'Reopen' }}">
                                <i class="bi bi-{{ $hw->status === 'active' ? 'lock' : 'unlock' }} text-xs"></i>
                                {{ $hw->status === 'active' ? 'Close' : 'Reopen' }}
                            </button>
                        </form>
                        <form action="{{ route('homework.destroy', $hw->id) }}" method="POST" onsubmit="return confirm('Delete this homework?')">
                            @csrf @method('DELETE')
                            <button class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                <i class="bi bi-trash text-sm"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
                <i class="bi bi-pencil-square text-5xl text-slate-200"></i>
                <p class="mt-3 text-slate-400">No homework assigned yet.</p>
                @can('create homework')
                <a href="{{ route('homework.create') }}" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                    <i class="bi bi-plus-lg"></i> Assign First Homework
                </a>
                @endcan
            </div>
            @endforelse
        </div>

        <div class="mt-4">{{ $homeworks->links() }}</div>
    </div>
</div>
@endsection
