@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Achievements Leaderboard</h1>
                <p class="text-slate-400 text-sm mt-0.5">Top students by awards and accolades</p>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('achievements.leaderboard') }}" class="flex flex-wrap gap-3 mb-6">
            <select name="category" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">All Categories</option>
                @foreach(\App\Models\Achievement::CATEGORIES as $v => $l)
                <option value="{{ $v }}" {{ $category === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
            <select name="level" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">All Levels</option>
                @foreach(\App\Models\Achievement::LEVELS as $v => $l)
                <option value="{{ $v }}" {{ $level === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Filter</button>
            @if($category || $level)
            <a href="{{ route('achievements.leaderboard') }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-lg text-sm font-medium hover:bg-slate-50 transition">Clear</a>
            @endif
        </form>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Leaderboard --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-trophy-fill me-1 text-amber-500"></i>Top Achievers</p>
                </div>
                @if($leaderboard->count())
                <div class="divide-y divide-slate-50">
                    @foreach($leaderboard as $i => $entry)
                    @php $student = $entry->student; @endphp
                    @if($student)
                    <div class="px-5 py-3 flex items-center gap-3">
                        <span class="w-7 h-7 rounded-full flex-shrink-0 flex items-center justify-center text-xs font-bold
                            {{ $i === 0 ? 'bg-amber-400 text-white' : ($i === 1 ? 'bg-slate-300 text-slate-700' : ($i === 2 ? 'bg-orange-300 text-white' : 'bg-slate-100 text-slate-500')) }}">
                            {{ $i + 1 }}
                        </span>
                        <img src="{{ $student->avatar }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0" alt="">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-700 truncate">{{ $student->full_name }}</p>
                        </div>
                        <span class="text-sm font-bold text-indigo-600 flex-shrink-0">{{ $entry->total }} award{{ $entry->total !== 1 ? 's' : '' }}</span>
                        <a href="{{ route('student.profile.show', $student->id) }}#achievements" class="text-xs text-slate-400 hover:text-indigo-600 flex-shrink-0">View</a>
                    </div>
                    @endif
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-10">No achievements recorded.</p>
                @endif
            </div>

            {{-- Recent achievements --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100">
                    <p class="text-sm font-semibold text-slate-700"><i class="bi bi-star-fill me-1 text-violet-500"></i>Recent Awards</p>
                </div>
                @if($recent->count())
                <div class="divide-y divide-slate-50">
                    @foreach($recent as $ach)
                    <div class="px-5 py-3 flex items-start gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-700">{{ $ach->title }}</p>
                            <p class="text-xs text-slate-400">
                                {{ $ach->student?->full_name ?? '—' }} ·
                                {{ \App\Models\Achievement::CATEGORIES[$ach->category] ?? $ach->category }} ·
                                {{ $ach->awarded_date->format('d M Y') }}
                            </p>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium flex-shrink-0 {{ \App\Models\Achievement::LEVEL_BADGES[$ach->level] ?? 'bg-slate-100 text-slate-600' }}">
                            {{ \App\Models\Achievement::LEVELS[$ach->level] ?? $ach->level }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-slate-400 text-center py-10">No recent achievements.</p>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
