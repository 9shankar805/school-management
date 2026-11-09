@extends('school-landing.layout')

@section('title', ($notice->title ?: 'Notice Announcement') . ' — ' . $school->name)

@section('content')

{{-- ── 1. Breadcrumbs & Top Navigation ── --}}
<div class="bg-white border-b border-slate-200 py-6">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                <a href="{{ $pageUrl('') }}" class="hover:underline text-slate-700">Home</a>
                <span>/</span>
                <a href="{{ $pageUrl('notices') }}" class="hover:underline text-slate-700">Notice Board</a>
                <span>/</span>
                <span class="text-slate-900 font-bold truncate max-w-xs sm:max-w-md">
                    {{ $notice->title ?: 'Notice #' . $notice->id }}
                </span>
            </div>

            <a href="{{ $pageUrl('notices') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#f36c21] hover:underline">
                <i class="bi bi-arrow-left"></i>
                <span>Back to All Circulars</span>
            </a>
        </div>
    </div>
</div>

{{-- ── 2. Notice Detail & Content ── --}}
<section class="py-12 bg-slate-50 min-h-[70vh]">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            {{-- Main Notice Document Container --}}
            <div class="lg:col-span-8 bg-white rounded-3xl p-6 sm:p-10 border border-slate-200 shadow-sm" id="printableNoticeSection">
                
                {{-- Official Letterhead Banner --}}
                <div class="text-center pb-6 mb-6 border-b-2 border-slate-100">
                    <img src="{{ $schoolLogoUrl }}" alt="{{ $school->name }}" class="w-16 h-16 rounded-2xl object-contain mx-auto mb-3 bg-white p-1 border border-slate-200 shadow-xs">
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 uppercase tracking-tight">{{ $school->name }}</h2>
                    <p class="text-xs text-slate-500 mt-1 mb-3">
                        {{ $school->address ?? ($school->city . ', ' . ($school->district ?? 'Nepal')) }}
                        @if($school->phone) &bull; Phone: {{ $school->phone }} @endif
                        @if($school->email) &bull; Email: {{ $school->email }} @endif
                    </p>
                    <span class="inline-block bg-slate-900 text-white text-[11px] uppercase font-extrabold px-4 py-1 rounded-full tracking-widest shadow-xs">
                        Official Academic Circular / सूचना
                    </span>
                </div>

                {{-- Notice Meta Bar --}}
                @php
                    $priority = strtolower($notice->priority ?? 'normal');
                    $badgeClass = match($priority) {
                        'urgent'  => 'bg-rose-100 text-rose-800 border-rose-200',
                        'exam'    => 'bg-amber-100 text-amber-900 border-amber-200',
                        'holiday' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                        default   => 'bg-slate-100 text-slate-800 border-slate-200',
                    };
                    $priorityLabel = match($priority) {
                        'urgent'  => 'Urgent Announcement',
                        'exam'    => 'Examination Routine',
                        'holiday' => 'Holiday / Vacation',
                        default   => 'Academic Notice',
                    };
                @endphp
                <div class="flex flex-wrap items-center justify-between gap-3 p-4 rounded-2xl bg-slate-50 border border-slate-100 text-xs text-slate-600 mb-8">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-calendar3 text-[#f36c21]"></i>
                        <span>Published: <strong class="text-slate-900 font-bold">{{ $notice->created_at ? $notice->created_at->format('F d, Y') : date('F d, Y') }}</strong></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $badgeClass }}">
                            {{ $priorityLabel }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="bi bi-people text-slate-500"></i>
                        <span>Audience: <strong class="text-slate-900 font-bold capitalize">{{ $notice->audience ?? 'All' }}</strong></span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-mono text-[11px]">REF #{{ str_pad($notice->id, 5, '0', STR_PAD_LEFT) }}</span>
                    </div>
                </div>

                {{-- Notice Title --}}
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 leading-tight mb-6">
                    {{ $notice->title ?: 'Notice Announcement' }}
                </h1>

                {{-- Notice Body Content --}}
                <div class="prose prose-slate max-w-none text-slate-700 leading-relaxed text-sm sm:text-base mb-8 space-y-4 font-normal">
                    {!! $notice->notice !!}
                </div>

                {{-- Attached Circular Document (if any) --}}
                @if(!empty($notice->attachment_path))
                <div class="p-5 rounded-2xl bg-amber-50/70 border border-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-700 flex items-center justify-center text-2xl shrink-0">
                            <i class="bi bi-file-earmark-pdf-fill"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-sm text-slate-900 mb-0.5">Official Attached Document / Circular</h4>
                            <p class="text-xs text-slate-500 mb-0">Download or preview original circular schedule</p>
                        </div>
                    </div>
                    <a href="{{ asset('storage/' . $notice->attachment_path) }}" target="_blank" download class="btn-primary-dark text-xs py-2.5 px-4 flex items-center justify-center gap-2 shadow-xs shrink-0">
                        <i class="bi bi-download"></i> <span>Download File</span>
                    </a>
                </div>
                @endif

                {{-- Bottom Action Buttons --}}
                <div class="pt-6 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="window.print()" class="btn-secondary-white text-xs py-2 px-3.5 flex items-center gap-1.5 shadow-2xs">
                            <i class="bi bi-printer"></i> <span>Print Notice</span>
                        </button>
                        <button type="button" onclick="navigator.clipboard.writeText(window.location.href); alert('Notice link copied to clipboard!');" class="btn-secondary-white text-xs py-2 px-3.5 flex items-center gap-1.5 shadow-2xs">
                            <i class="bi bi-share"></i> <span>Share</span>
                        </button>
                    </div>

                    <a href="{{ $pageUrl('notices') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 inline-flex items-center gap-1">
                        <span>Browse All Circulars</span> &rarr;
                    </a>
                </div>
            </div>

            {{-- Sidebar: Recent Circulars & Quick Actions --}}
            <div class="lg:col-span-4 space-y-6">
                
                {{-- Quick Admissions Card --}}
                <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-3xl p-6 shadow-md border border-slate-700">
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#f36c21] mb-2 inline-block">
                        Admissions Open
                    </span>
                    <h3 class="text-lg font-bold text-white mb-2 leading-tight">
                        Join {{ $school->name }}
                    </h3>
                    <p class="text-xs text-slate-300 leading-relaxed mb-4">
                        Applications are actively accepted for Preschool, Grades 1–10, and +2 Science/Management.
                    </p>
                    <a href="{{ url('admissions/apply?school='.$school->slug) }}" class="w-full py-2.5 rounded-xl bg-[#f36c21] hover:bg-[#e05d15] text-white font-bold text-xs flex items-center justify-center gap-2 shadow-sm transition">
                        <span>Apply for Admission Online</span> &rarr;
                    </a>
                </div>

                {{-- Other Recent Notices --}}
                @if(isset($relatedNotices) && $relatedNotices->count() > 0)
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
                    <h4 class="font-extrabold text-sm text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center justify-between">
                        <span>Recent Circulars</span>
                        <a href="{{ $pageUrl('notices') }}" class="text-xs font-bold text-[#f36c21] hover:underline">View All</a>
                    </h4>
                    <div class="space-y-3">
                        @foreach($relatedNotices as $rel)
                        <a href="{{ $pageUrl('notice/' . $rel->id) }}" class="block p-3 rounded-xl hover:bg-slate-50 transition border border-transparent hover:border-slate-200">
                            <span class="text-[10px] text-slate-400 font-semibold block mb-0.5">
                                {{ $rel->created_at ? $rel->created_at->format('M d, Y') : 'Recent' }}
                            </span>
                            <h5 class="text-xs font-bold text-slate-800 line-clamp-2 leading-snug">
                                {{ $rel->title ?: \Illuminate\Support\Str::limit(strip_tags($rel->notice), 75) }}
                            </h5>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Contact Administration Desk --}}
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm text-xs text-slate-600 space-y-2">
                    <h4 class="font-extrabold text-sm text-slate-900 mb-2">School Administration</h4>
                    @if($school->phone)
                    <p class="flex items-center gap-2">
                        <i class="bi bi-telephone text-[#f36c21]"></i>
                        <span>{{ $school->phone }}</span>
                    </p>
                    @endif
                    @if($school->email)
                    <p class="flex items-center gap-2">
                        <i class="bi bi-envelope text-[#f36c21]"></i>
                        <span>{{ $school->email }}</span>
                    </p>
                    @endif
                    <a href="{{ $pageUrl('contact') }}" class="inline-block mt-2 text-xs font-bold text-[#f36c21] hover:underline">
                        <span>Contact & Campus Map</span> &rarr;
                    </a>
                </div>

            </div>
        </div>
    </div>
</section>

@endsection
