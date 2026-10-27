@extends('school-landing.layout')

@section('title', 'Notices, Examination Routines & Academic Circulars')

@section('content')

{{-- Page Header --}}
<div class="bg-white text-slate-900 py-12 border-b border-slate-200 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-2 uppercase tracking-wider">
            <a href="{{ $pageUrl('') }}" class="hover:underline text-slate-700">Home</a>
            <span>/</span>
            <span class="text-slate-900 font-bold">Notice Board</span>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900">Notices & Circulars Archive</h1>
        <p class="text-slate-600 text-sm sm:text-base mt-2 max-w-2xl">
            Official announcements, examination routines, holiday schedules, and administrative directives.
        </p>
    </div>
</div>

<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Upcoming Examination Routine & Academic Schedule Board --}}
        <div class="mb-10 bg-white text-slate-900 rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-md relative overflow-hidden">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 relative z-10">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                        <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-slate-900 text-white uppercase tracking-wider flex items-center gap-1.5 shadow-sm">
                            <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span>
                            <span>OFFICIAL EXAM SCHEDULE</span>
                        </span>
                        @if($school->exam_start_date)
                        <span class="px-2.5 py-1 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                            <i class="bi bi-calendar-event text-slate-700 mr-1"></i> {{ $school->exam_start_date }} {{ $school->exam_end_date ? ' – ' . $school->exam_end_date : '' }}
                        </span>
                        @endif
                    </div>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 mb-2 leading-tight">
                        {{ $school->exam_title ?: 'Academic Terminal Examination & SEE Model Routine' }}
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-4 max-w-3xl">
                        {{ $school->exam_instructions ?: 'Students are advised to review the subject-wise routine, reporting timings, and bring their valid Student ID & Admit Card to the examination hall.' }}
                    </p>

                    @if($school->exam_shift_details)
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-200 text-xs text-slate-800">
                        <i class="bi bi-clock-history text-slate-700"></i>
                        <span>{{ $school->exam_shift_details }}</span>
                    </div>
                    @endif
                </div>

                <div class="flex flex-col sm:flex-row lg:flex-col gap-2.5 shrink-0 w-full sm:w-auto">
                    @if($school->exam_routine_file_url)
                    <a href="{{ $school->exam_routine_file_url }}" target="_blank" class="px-5 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold text-center flex items-center justify-center gap-2 shadow-sm">
                        <i class="bi bi-file-earmark-arrow-down-fill text-base"></i>
                        <span>Download Routine (PDF/Sheet)</span>
                    </a>
                    @else
                    <button type="button" onclick="openExamResultModal()" class="px-5 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold text-center flex items-center justify-center gap-2 shadow-sm">
                        <i class="bi bi-mortarboard-fill text-base"></i>
                        <span>Check Online Results</span>
                    </button>
                    @endif
                    <button type="button" onclick="openProspectusModal()" class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-800 text-xs font-bold border border-slate-300 transition flex items-center justify-center gap-2 shadow-sm">
                        <i class="bi bi-file-earmark-pdf"></i> Academic Prospectus
                    </button>
                </div>
            </div>
        </div>

        {{-- Search filter & View flyer action --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-8">
            <div class="relative w-full max-w-md">
                <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" id="noticeSearchInput" placeholder="Search notices by keyword, exam, routine..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-medium focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-slate-900 transition shadow-sm">
            </div>

            @if($school->hasActivePopup())
            <button onclick="openSchoolPopup()" class="px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs font-bold hover:bg-slate-50 transition flex items-center gap-2 shrink-0 shadow-sm">
                <i class="bi bi-megaphone-fill text-slate-700"></i> View Urgent Notice Flyer
            </button>
            @endif
        </div>

        @php
            $noticesList = [];
            if(isset($recentNotices) && $recentNotices->count() > 0) {
                foreach($recentNotices as $rn) {
                    $noticesList[] = [
                        'title' => $rn->notice,
                        'date' => $rn->created_at ? $rn->created_at->format('M d, Y') : 'Recent',
                        'tag' => 'Academic Notice',
                    ];
                }
            } elseif($school->notice_board && strlen($school->notice_board) > 10) {
                $lines = array_filter(explode("\n", trim($school->notice_board)));
                foreach($lines as $idx => $line) {
                    $tag = 'Official Notice';
                    if(str_contains(strtoupper($line), 'ADMISSION')) $tag = 'Admissions';
                    elseif(str_contains(strtoupper($line), 'SCHOLARSHIP')) $tag = 'Scholarship';
                    elseif(str_contains(strtoupper($line), 'EXAM') || str_contains(strtoupper($line), 'ROUTINE')) $tag = 'Examination';
                    elseif(str_contains(strtoupper($line), 'SPORT')) $tag = 'Sports & ECA';
                    $noticesList[] = [
                        'title' => trim($line),
                        'date' => date('M d, Y', strtotime('-'.($idx*2).' days')),
                        'tag' => $tag,
                    ];
                }
            } else {
                $noticesList = [
                    ['title' => 'Admissions Open for Academic Session 2026-2027 (+2 Science, Management & Law)', 'date' => date('M d, Y'), 'tag' => 'Admissions'],
                    ['title' => 'First Term Terminal Examination Routine Published for Grades XI & XII', 'date' => date('M d, Y', strtotime('-3 days')), 'tag' => 'Examination'],
                    ['title' => 'Annual Inter-House Sports Meet & Athletics Championship Schedule', 'date' => date('M d, Y', strtotime('-7 days')), 'tag' => 'Sports & ECA'],
                    ['title' => 'National Science Olympiad & Project Exhibition Registrations Active', 'date' => date('M d, Y', strtotime('-12 days')), 'tag' => 'Academic'],
                    ['title' => 'Parent-Teacher Conference (PTC) for Grade X SEE Candidates', 'date' => date('M d, Y', strtotime('-16 days')), 'tag' => 'Official Notice'],
                ];
            }
        @endphp

        <div class="space-y-4" id="noticesListContainer">
            @forelse($noticesList as $item)
            <div class="notice-item bg-white hover:bg-slate-50 rounded-2xl p-6 border border-slate-200 transition-all card-lift flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
                <div class="flex items-start gap-4 flex-1">
                    <div class="w-14 h-14 rounded-xl bg-slate-100 text-slate-900 flex flex-col items-center justify-center shrink-0 border border-slate-200">
                        <i class="bi bi-calendar3 text-xs"></i>
                        <span class="text-[10px] font-bold mt-0.5">{{ explode(' ', $item['date'])[0] }}</span>
                        <span class="text-[9px] text-slate-500 font-mono">{{ explode(' ', $item['date'])[1] ?? '' }}</span>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-800 uppercase tracking-wider border border-slate-200">
                                {{ $item['tag'] }}
                            </span>
                            <span class="text-xs text-slate-500">{{ $item['date'] }}</span>
                        </div>
                        <h3 class="notice-title font-bold text-base text-slate-900 leading-snug">
                            {{ $item['title'] }}
                        </h3>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ url('admissions/apply?school='.$school->slug) }}" class="btn-primary-dark text-xs py-2 px-3.5">
                        <span>Details</span> &rarr;
                    </a>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-2xl p-12 text-center text-slate-500 border border-slate-200 shadow-sm">
                <i class="bi bi-bell-slash text-3xl text-slate-400 block mb-2"></i>
                No circulars matching your query.
            </div>
            @endforelse
        </div>
    </div>
</section>

@endsection
