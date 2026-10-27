@extends('layouts.app')
@section('title', 'Class In-Charge — Student Dossier')

@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">

    <nav class="text-xs text-slate-400 mb-5 flex items-center gap-1.5">
        <a href="{{ route('home') }}" class="hover:text-indigo-600">Home</a>
        <i class="bi bi-chevron-right text-[9px]"></i>
        <a href="{{ route('class-incharge.dashboard') }}" class="hover:text-indigo-600">Class In-Charge</a>
        <i class="bi bi-chevron-right text-[9px]"></i>
        <span class="text-slate-800 font-bold">Student & Parent Dossier</span>
    </nav>

    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 dark:text-slate-100">
                Student & Parent Directory
            </h1>
            <p class="text-xs text-slate-500">
                {{ $assignment->schoolClass?->class_name }} — Section {{ preg_replace('/^Section\s+/i', '', $assignment->section?->section_name ?? 'A') }} &middot; {{ $students->count() }} enrolled
            </p>
        </div>
        <a href="{{ route('class-incharge.dashboard') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold hover:bg-slate-200 transition">
            ← Back to Overview
        </a>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left font-bold w-12">Roll</th>
                        <th class="px-4 py-3 text-left font-bold">Student Name</th>
                        <th class="px-4 py-3 text-left font-bold">Admission / IEMIS No</th>
                        <th class="px-4 py-3 text-left font-bold">Gender</th>
                        <th class="px-4 py-3 text-left font-bold">Parent / Guardian</th>
                        <th class="px-4 py-3 text-left font-bold">Contact Phone</th>
                        <th class="px-4 py-3 text-right font-bold">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach($students as $p)
                    @php 
                        $stu = $p->student; 
                        $parent = $stu?->parent;
                    @endphp
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40 transition">
                        <td class="px-4 py-3 font-bold text-slate-700 dark:text-slate-200">#{{ $p->roll_no ?? '—' }}</td>
                        <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-100">
                            <div class="flex items-center gap-2.5">
                                <img src="{{ $stu?->avatar }}" alt="" class="w-8 h-8 rounded-full object-cover border border-slate-200">
                                <div>
                                    <p class="font-bold leading-tight">{{ $stu?->full_name }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $stu?->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 font-mono text-slate-600 dark:text-slate-300">
                            {{ $stu?->academic_info?->admission_no ?? $p->id_card_number ?? '—' }}
                        </td>
                        <td class="px-4 py-3 capitalize text-slate-600 dark:text-slate-300">{{ $stu?->gender ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-700 dark:text-slate-200 font-medium">
                            {{ $parent?->full_name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300 font-mono">
                            @if($parent?->phone || $stu?->phone)
                            <a href="tel:{{ $parent?->phone ?? $stu?->phone }}" class="text-indigo-600 hover:underline">
                                <i class="bi bi-telephone me-1"></i>{{ $parent?->phone ?? $stu?->phone }}
                            </a>
                            @else
                            <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('student.profile.show', $stu?->id) }}" class="text-indigo-600 font-bold hover:underline">
                                Full Profile →
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
