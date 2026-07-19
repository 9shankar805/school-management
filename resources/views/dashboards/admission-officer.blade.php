@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">
        @include('layouts.left-menu')
    </div>

    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Admission Officer Dashboard</h1>
                <p class="text-slate-400 text-sm mt-0.5">{{ now()->format('l, F j, Y') }}</p>
            </div>
            <div class="flex gap-2">
                @can('create students')
                <a href="{{ route('student.create.show') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                    <i class="bi bi-person-plus"></i> Admit Student
                </a>
                @endcan
                @can('view students')
                <a href="{{ route('student.list.show') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-sm font-medium rounded-lg hover:bg-slate-50 transition text-slate-700">
                    <i class="bi bi-people"></i> All Students
                </a>
                @endcan
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Students</p>
                    <span class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 text-sm"><i class="bi bi-people-fill"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ number_format($totalStudents) }}</p>
                <p class="mt-1 text-xs text-indigo-600">This session</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">This Month</p>
                    <span class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 text-sm"><i class="bi bi-person-plus"></i></span>
                </div>
                <p class="text-3xl font-bold text-emerald-600">{{ $thisMonthAdmissions }}</p>
                <p class="mt-1 text-xs text-emerald-500">New admissions</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Classes</p>
                    <span class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600 text-sm"><i class="bi bi-mortarboard"></i></span>
                </div>
                <p class="text-3xl font-bold text-slate-800">{{ $classCount }}</p>
                <p class="mt-1 text-xs text-amber-500">Available</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Avg / Month</p>
                    <span class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 text-sm"><i class="bi bi-graph-up"></i></span>
                </div>
                @php $avgPerMonth = $totalStudents > 0 ? round($totalStudents / max(1, now()->month)) : 0; @endphp
                <p class="text-3xl font-bold text-slate-800">{{ $avgPerMonth }}</p>
                <p class="mt-1 text-xs text-blue-500">Students/month</p>
            </div>
        </div>

        {{-- Recent Admissions Table --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                <p class="text-sm font-semibold text-slate-700"><i class="bi bi-person-check me-1 text-emerald-500"></i>Recent Admissions</p>
                @can('view students')
                <a href="{{ route('student.list.show') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
                @endcan
            </div>
            @if($recentAdmissions->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-slate-400 bg-slate-50">
                            <th class="px-5 py-2.5 font-medium">Student</th>
                            <th class="px-5 py-2.5 font-medium">Email</th>
                            <th class="px-5 py-2.5 font-medium">Gender</th>
                            <th class="px-5 py-2.5 font-medium">Admitted</th>
                            @can('view students')
                            <th class="px-5 py-2.5 font-medium"></th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($recentAdmissions as $s)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <img src="{{ $s->avatar }}" class="w-7 h-7 rounded-full object-cover flex-shrink-0" alt="">
                                    <span class="font-medium text-slate-700">{{ $s->full_name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-slate-500">{{ $s->email }}</td>
                            <td class="px-5 py-3 text-slate-500 capitalize">{{ $s->gender ?? '—' }}</td>
                            <td class="px-5 py-3 text-slate-400">{{ $s->created_at->format('M d, Y') }}</td>
                            @can('view students')
                            <td class="px-5 py-3">
                                <a href="{{ route('student.profile.show', $s->id) }}" class="text-xs text-indigo-600 hover:underline">View</a>
                            </td>
                            @endcan
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-sm text-slate-400 text-center py-10">No students admitted yet.</p>
            @endif
        </div>

    </div>
</div>
@endsection
