@extends('school-landing.layout')

@section('title', 'Campus News, Press Releases & Institutional Updates')
@section('seo_description', 'Official press releases, academic accolades, championship victories, and milestone announcements from ' . $school->name . ' in ' . ($school->city ?? 'Nepal') . '.')
@section('seo_keywords', 'school news Nepal, press releases, academic accolades, ' . $school->name . ' circulars, campus updates')

@section('content')

{{-- ── 1. Page Header & Breadcrumb ── --}}
<div class="bg-white text-slate-900 py-12 border-b border-slate-200 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-3 uppercase tracking-wider">
            <a href="{{ $pageUrl('') }}" class="hover:underline text-slate-700">Home</a>
            <span>/</span>
            <a href="{{ $pageUrl('publications') }}" class="hover:underline text-slate-700">Publications</a>
            <span>/</span>
            <span class="text-[#8b151b] font-bold">News & Articles</span>
        </div>
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <p class="rbs-sub-title text-[#0b417e] mb-2">CAMPUS HAPPENINGS & PRESS RELEASES</p>
                <h1 class="text-3xl sm:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    News & Media Articles
                </h1>
                <p class="text-slate-600 text-sm sm:text-base mt-2 max-w-2xl leading-relaxed">
                    Official announcements, academic milestones, inter-school tournament victories, and administrative press dispatches from {{ $school->name }}.
                </p>
            </div>
            
            {{-- Category Filter Navigation --}}
            <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none shrink-0">
                <a href="{{ $pageUrl('publications') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition bg-slate-100 hover:bg-slate-200 text-slate-700">
                    <i class="bi bi-collection mr-1"></i> All
                </a>
                <a href="{{ $pageUrl('blogs') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition bg-slate-100 hover:bg-slate-200 text-slate-700">
                    <i class="bi bi-journal-text mr-1"></i> Blogs
                </a>
                <a href="{{ $pageUrl('news') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition bg-[#8b151b] text-white shadow-xs">
                    <i class="bi bi-newspaper mr-1"></i> News & Press
                </a>
                <a href="{{ $pageUrl('stories') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition bg-slate-100 hover:bg-slate-200 text-slate-700">
                    <i class="bi bi-stars mr-1"></i> Stories & Voices
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ── 2. Breaking News Highlight Banner ── --}}
<section class="bg-red-50 border-b border-red-100 py-3">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-3">
            <span class="px-2.5 py-0.5 rounded-md bg-[#8b151b] text-white text-[10px] font-extrabold tracking-wider uppercase flex items-center gap-1.5 shrink-0">
                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                <span>LATEST DISPATCH</span>
            </span>
            <p class="text-xs sm:text-sm font-semibold text-slate-800 line-clamp-1">
                {{ $articles[0]['title'] ?? 'Admissions open for academic session 2082-83 across all grades.' }}
            </p>
        </div>
        <a href="{{ $pageUrl('notices') }}" class="text-xs font-bold text-[#8b151b] hover:underline flex items-center gap-1 shrink-0">
            <span>View Notice Board</span> &rarr;
        </a>
    </div>
</section>

{{-- ── 3. News Articles Grid ── --}}
<section class="py-16 sm:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-10 pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-xl font-extrabold text-slate-900">Campus Dispatches & Press Chronicles</h3>
                <p class="text-xs text-slate-500 mt-1">Verified media releases from our administrative and academic departments.</p>
            </div>
            <div class="text-xs font-semibold text-slate-500">
                Showing <strong class="text-slate-900">{{ count($articles) }}</strong> articles
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($articles as $news)
            <article class="bg-white rounded-3xl border border-slate-200/90 overflow-hidden shadow-xs hover:shadow-lg hover:border-slate-300 transition duration-300 flex flex-col justify-between group">
                <div>
                    {{-- Thumbnail --}}
                    <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
                        <img src="{{ $news['image'] }}" alt="{{ $news['title'] }}" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                        <div class="absolute top-3 left-3">
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-wider bg-white/95 backdrop-blur-sm text-slate-900 shadow-xs border border-white/40">
                                {{ $news['category'] }}
                            </span>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="p-6">
                        <div class="flex items-center gap-2 text-[11px] text-slate-400 mb-2">
                            <span class="font-bold text-slate-700"><i class="bi bi-calendar3 mr-1"></i> {{ $news['published_date'] }}</span>
                            <span>•</span>
                            <span><i class="bi bi-clock mr-0.5"></i> {{ $news['read_time'] }}</span>
                        </div>
                        <h4 class="font-extrabold text-lg text-slate-900 mb-2.5 group-hover:text-[#8b151b] transition leading-snug">
                            <a href="{{ $pageUrl('article/' . $news['slug']) }}">
                                {{ $news['title'] }}
                            </a>
                        </h4>
                        <p class="text-xs text-slate-600 leading-relaxed line-clamp-3 mb-4">
                            {{ $news['excerpt'] }}
                        </p>

                        {{-- Tags --}}
                        <div class="flex flex-wrap gap-1.5 mb-2">
                            @foreach(array_slice($news['tags'] ?? [], 0, 3) as $tag)
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-600">
                                #{{ $tag }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Author & Link --}}
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <img src="{{ $news['author_avatar'] }}" alt="{{ $news['author_name'] }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 shrink-0">
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-slate-900 truncate">{{ $news['author_name'] }}</div>
                            <div class="text-[10px] text-slate-500 truncate">{{ $news['author_role'] }}</div>
                        </div>
                    </div>
                    <a href="{{ $pageUrl('article/' . $news['slug']) }}" class="text-xs font-bold text-[#8b151b] hover:underline shrink-0 flex items-center gap-1">
                        <span>Read Story</span> &rarr;
                    </a>
                </div>
            </article>
            @endforeach
        </div>

    </div>
</section>

{{-- ── 4. Quick Links to Circulars & Result Archive ── --}}
<section class="py-12 bg-slate-50 border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#0b417e] flex items-center justify-center text-2xl shrink-0">
                    <i class="bi bi-bell-fill"></i>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-slate-900">Administrative Circulars</h4>
                    <p class="text-xs text-slate-500 mb-2">Review official routine and notice PDFs.</p>
                    <a href="{{ $pageUrl('notices') }}" class="text-xs font-bold text-[#0b417e] hover:underline">
                        Open Notice Board &rarr;
                    </a>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-orange-50 text-[#f36c21] flex items-center justify-center text-2xl shrink-0">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-slate-900">Academic Examination Results</h4>
                    <p class="text-xs text-slate-500 mb-2">Check student grade sheets and reports.</p>
                    <button type="button" onclick="openExamResultModal()" class="text-xs font-bold text-[#f36c21] hover:underline text-left">
                        Check Online Results &rarr;
                    </button>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-2xl shrink-0">
                    <i class="bi bi-images"></i>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-slate-900">Campus Photo Gallery</h4>
                    <p class="text-xs text-slate-500 mb-2">Explore photos from tournaments and fairs.</p>
                    <a href="{{ $pageUrl('gallery') }}" class="text-xs font-bold text-emerald-700 hover:underline">
                        Browse Gallery &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

