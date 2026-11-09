@extends('school-landing.layout')

@section('title', 'School Blogs, Academic Essays & Educational Inquiry')
@section('seo_description', 'Read academic research, pedagogical essays, science symposium papers, and classroom reflections authored by ' . $school->name . ' faculty and scholars.')
@section('seo_keywords', 'school blogs Nepal, academic essays, education inquiry, ' . $school->name . ' research, teacher reflections')

@section('content')

{{-- ── 1. Page Header & Breadcrumb ── --}}
<div class="bg-white text-slate-900 py-12 border-b border-slate-200 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-3 uppercase tracking-wider">
            <a href="{{ $pageUrl('') }}" class="hover:underline text-slate-700">Home</a>
            <span>/</span>
            <a href="{{ $pageUrl('publications') }}" class="hover:underline text-slate-700">Publications</a>
            <span>/</span>
            <span class="text-[#8b151b] font-bold">School Blogs</span>
        </div>
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <p class="rbs-sub-title text-[#0b417e] mb-2">SCHOLARSHIP & THOUGHT LEADERSHIP</p>
                <h1 class="text-3xl sm:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    Official School Blogs
                </h1>
                <p class="text-slate-600 text-sm sm:text-base mt-2 max-w-2xl leading-relaxed">
                    Pedagogical insights, STEAM laboratory reflections, educational essays, and academic commentary authored by {{ $school->name }} faculty and scholars.
                </p>
            </div>
            
            {{-- Category Filter Navigation --}}
            <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none shrink-0">
                <a href="{{ $pageUrl('publications') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition bg-slate-100 hover:bg-slate-200 text-slate-700">
                    <i class="bi bi-collection mr-1"></i> All
                </a>
                <a href="{{ $pageUrl('blogs') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition bg-[#8b151b] text-white shadow-xs">
                    <i class="bi bi-journal-text mr-1"></i> Blogs
                </a>
                <a href="{{ $pageUrl('news') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition bg-slate-100 hover:bg-slate-200 text-slate-700">
                    <i class="bi bi-newspaper mr-1"></i> News & Press
                </a>
                <a href="{{ $pageUrl('stories') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition bg-slate-100 hover:bg-slate-200 text-slate-700">
                    <i class="bi bi-stars mr-1"></i> Stories & Voices
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ── 2. Featured Blog Hero ── --}}
@php
    $featured = collect($articles)->firstWhere('featured', true) ?? ($articles[0] ?? null);
    $otherBlogs = collect($articles)->reject(fn($a) => $featured && $a['slug'] === $featured['slug'])->values();
@endphp

@if($featured)
<section class="py-12 bg-[#f8fafc] border-b border-slate-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition duration-300">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-0 items-stretch">
                {{-- Left: Image --}}
                <div class="lg:col-span-6 relative aspect-[16/10] lg:aspect-auto overflow-hidden bg-slate-100">
                    <img src="{{ $featured['image'] }}" alt="{{ $featured['title'] }}" class="w-full h-full object-cover">
                    <div class="absolute top-4 left-4">
                        <span class="px-3 py-1 rounded-full text-[11px] font-extrabold uppercase tracking-wider bg-rose-600 text-white shadow-sm">
                            ★ Featured Blog
                        </span>
                    </div>
                </div>

                {{-- Right: Content --}}
                <div class="lg:col-span-6 p-6 sm:p-10 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-3 text-xs text-slate-500 mb-3">
                            <span class="font-bold text-[#8b151b] uppercase tracking-wider">{{ $featured['category'] }}</span>
                            <span>•</span>
                            <span>{{ $featured['published_date'] }}</span>
                            <span>•</span>
                            <span><i class="bi bi-clock mr-1"></i> {{ $featured['read_time'] }}</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 leading-tight mb-4 hover:text-[#8b151b] transition">
                            <a href="{{ $pageUrl('article/' . $featured['slug']) }}">
                                {{ $featured['title'] }}
                            </a>
                        </h2>
                        <p class="text-slate-600 text-sm sm:text-base leading-relaxed mb-6">
                            {{ $featured['excerpt'] }}
                        </p>
                    </div>

                    <div class="pt-6 border-t border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="{{ $featured['author_avatar'] }}" alt="{{ $featured['author_name'] }}" class="w-10 h-10 rounded-full object-cover border border-slate-200">
                            <div>
                                <h4 class="text-xs font-bold text-slate-900">{{ $featured['author_name'] }}</h4>
                                <p class="text-[11px] text-slate-500">{{ $featured['author_role'] }}</p>
                            </div>
                        </div>
                        <a href="{{ $pageUrl('article/' . $featured['slug']) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-[#8b151b] hover:bg-[#6b0f14] transition shadow-xs">
                            <span>Read Article</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

{{-- ── 3. Blog Articles Grid ── --}}
<section class="py-16 sm:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-10 pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-xl font-extrabold text-slate-900">Recent Educational Articles & Essays</h3>
                <p class="text-xs text-slate-500 mt-1">Written by faculty, academic coordinators, and senior scholars.</p>
            </div>
            <div class="text-xs font-semibold text-slate-500">
                Showing <strong class="text-slate-900">{{ count($articles) }}</strong> published articles
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($otherBlogs as $blog)
            <article class="bg-white rounded-3xl border border-slate-200/90 overflow-hidden shadow-xs hover:shadow-lg hover:border-slate-300 transition duration-300 flex flex-col justify-between group">
                <div>
                    {{-- Thumbnail --}}
                    <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
                        <img src="{{ $blog['image'] }}" alt="{{ $blog['title'] }}" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                        <div class="absolute top-3 left-3">
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-wider bg-white/95 backdrop-blur-sm text-slate-900 shadow-xs border border-white/40">
                                {{ $blog['category'] }}
                            </span>
                        </div>
                    </div>

                    {{-- Text --}}
                    <div class="p-6">
                        <div class="flex items-center gap-2 text-[11px] text-slate-400 mb-2">
                            <span>{{ $blog['published_date'] }}</span>
                            <span>•</span>
                            <span><i class="bi bi-clock mr-0.5"></i> {{ $blog['read_time'] }}</span>
                        </div>
                        <h4 class="font-extrabold text-lg text-slate-900 mb-2.5 group-hover:text-[#8b151b] transition leading-snug">
                            <a href="{{ $pageUrl('article/' . $blog['slug']) }}">
                                {{ $blog['title'] }}
                            </a>
                        </h4>
                        <p class="text-xs text-slate-600 leading-relaxed line-clamp-3 mb-4">
                            {{ $blog['excerpt'] }}
                        </p>

                        {{-- Tags --}}
                        <div class="flex flex-wrap gap-1.5 mb-2">
                            @foreach(array_slice($blog['tags'] ?? [], 0, 3) as $tag)
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
                        <img src="{{ $blog['author_avatar'] }}" alt="{{ $blog['author_name'] }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 shrink-0">
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-slate-900 truncate">{{ $blog['author_name'] }}</div>
                            <div class="text-[10px] text-slate-500 truncate">{{ $blog['author_role'] }}</div>
                        </div>
                    </div>
                    <a href="{{ $pageUrl('article/' . $blog['slug']) }}" class="text-xs font-bold text-[#8b151b] hover:underline shrink-0 flex items-center gap-1">
                        <span>Read</span> &rarr;
                    </a>
                </div>
            </article>
            @endforeach
        </div>

    </div>
</section>

{{-- ── 4. Academic Newsletter & Dispatch Subscription Bar ── --}}
<section class="py-14 bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 text-white relative overflow-hidden">
    <div class="max-w-4xl mx-auto px-4 text-center relative z-10">
        <span class="text-[11px] font-extrabold uppercase tracking-widest text-amber-300 bg-white/10 px-3.5 py-1 rounded-full border border-white/20 inline-block mb-3">
            Academic Dispatch
        </span>
        <h3 class="text-2xl sm:text-3xl font-extrabold text-white mb-2">
            Receive Our Thought Leadership Directly
        </h3>
        <p class="text-xs sm:text-sm text-slate-300 mb-6 max-w-xl mx-auto leading-relaxed">
            Subscribe to receive regular blogs, curriculum updates, research papers, and student literary announcements in your inbox.
        </p>

        <form action="{{ $pageUrl('newsletter') }}" method="POST" class="flex flex-col sm:flex-row gap-2 max-w-md mx-auto" onsubmit="handleInlineNewsletter(event, this)">
            @csrf
            <input type="email" name="email" required placeholder="Enter your email address" 
                   class="flex-1 px-4 py-3 rounded-xl border border-white/20 bg-white/10 backdrop-blur-sm text-xs sm:text-sm text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#8b151b]">
            <button type="submit" class="px-6 py-3 rounded-xl bg-[#8b151b] hover:bg-[#6b0f14] text-white font-bold text-xs sm:text-sm transition shadow-sm shrink-0">
                Subscribe Free
            </button>
        </form>
    </div>
</section>

@endsection

