@extends('school-landing.layout')

@section('title', 'Student Voices, Creative Writing & Alumni Journeys')
@section('seo_description', 'Explore student perspectives, creative literature, youth leadership journals, and alumni success chronicles from the scholars of ' . $school->name . '.')
@section('seo_keywords', 'student stories Nepal, creative writing, youth leadership, student voices, alumni spotlight, ' . $school->name)

@section('content')

{{-- ── 1. Page Header & Breadcrumb ── --}}
<div class="bg-white text-slate-900 py-12 border-b border-slate-200 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-3 uppercase tracking-wider">
            <a href="{{ $pageUrl('') }}" class="hover:underline text-slate-700">Home</a>
            <span>/</span>
            <a href="{{ $pageUrl('publications') }}" class="hover:underline text-slate-700">Publications</a>
            <span>/</span>
            <span class="text-[#8b151b] font-bold">Student Stories</span>
        </div>
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <p class="rbs-sub-title text-[#0b417e] mb-2">CAMPUS NARRATIVES & VOICES</p>
                <h1 class="text-3xl sm:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    Student Stories & Voices
                </h1>
                <p class="text-slate-600 text-sm sm:text-base mt-2 max-w-2xl leading-relaxed">
                    Personal memoirs, debate journeys, community service reflections, and alumni milestones authored by scholars of {{ $school->name }}.
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
                <a href="{{ $pageUrl('news') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition bg-slate-100 hover:bg-slate-200 text-slate-700">
                    <i class="bi bi-newspaper mr-1"></i> News & Press
                </a>
                <a href="{{ $pageUrl('stories') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition bg-[#8b151b] text-white shadow-xs">
                    <i class="bi bi-stars mr-1"></i> Stories & Voices
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ── 2. Inspiring Voice Hero Spotlight ── --}}
<section class="py-12 bg-gradient-to-br from-amber-50/60 via-white to-orange-50/50 border-b border-slate-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl border border-amber-200/80 p-8 sm:p-12 shadow-sm relative overflow-hidden">
            <div class="max-w-3xl relative z-10">
                <span class="text-[10px] font-extrabold uppercase tracking-widest text-amber-800 bg-amber-100 px-3 py-1 rounded-full border border-amber-200 inline-block mb-4">
                    ★ In Their Own Words
                </span>
                <blockquote class="text-xl sm:text-2xl font-serif italic text-slate-800 leading-relaxed mb-6">
                    “{{ $school->name }} taught me that intelligence without compassion is meaningless. The real education began when we stepped beyond our comfort zones—into the mountain trails, the chemistry laboratories, and the debate podiums.”
                </blockquote>
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-full bg-[#8b151b] text-white font-bold flex items-center justify-center text-sm shadow-sm">
                        PK
                    </div>
                    <div>
                        <div class="text-sm font-extrabold text-slate-900">Prerana K.</div>
                        <div class="text-xs text-slate-500">Alumni Class of 2022 · International Rhodes / Fellowship Scholar</div>
                    </div>
                </div>
            </div>
            <div class="absolute -right-8 -bottom-8 opacity-5 text-slate-900 text-[180px] pointer-events-none">
                <i class="bi bi-quote"></i>
            </div>
        </div>
    </div>
</section>

{{-- ── 3. Student Stories Grid ── --}}
<section class="py-16 sm:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-10 pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-xl font-extrabold text-slate-900">Student Perspectives & Alumni Chronicles</h3>
                <p class="text-xs text-slate-500 mt-1">Real narratives illustrating transformation, service, and creativity.</p>
            </div>
            <div class="text-xs font-semibold text-slate-500">
                Showing <strong class="text-slate-900">{{ count($articles) }}</strong> published narratives
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($articles as $story)
            <article class="bg-white rounded-3xl border border-slate-200/90 overflow-hidden shadow-xs hover:shadow-lg hover:border-slate-300 transition duration-300 flex flex-col justify-between group">
                <div>
                    {{-- Thumbnail --}}
                    <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
                        <img src="{{ $story['image'] }}" alt="{{ $story['title'] }}" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                        <div class="absolute top-3 left-3">
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-wider bg-white/95 backdrop-blur-sm text-slate-900 shadow-xs border border-white/40">
                                {{ $story['category'] }}
                            </span>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="p-6">
                        <div class="flex items-center gap-2 text-[11px] text-slate-400 mb-2">
                            <span>{{ $story['published_date'] }}</span>
                            <span>•</span>
                            <span><i class="bi bi-clock mr-0.5"></i> {{ $story['read_time'] }}</span>
                        </div>
                        <h4 class="font-extrabold text-lg text-slate-900 mb-2.5 group-hover:text-[#8b151b] transition leading-snug">
                            <a href="{{ $pageUrl('article/' . $story['slug']) }}">
                                {{ $story['title'] }}
                            </a>
                        </h4>
                        <p class="text-xs text-slate-600 leading-relaxed line-clamp-3 mb-4">
                            {{ $story['excerpt'] }}
                        </p>

                        {{-- Tags --}}
                        <div class="flex flex-wrap gap-1.5 mb-2">
                            @foreach(array_slice($story['tags'] ?? [], 0, 3) as $tag)
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
                        <img src="{{ $story['author_avatar'] }}" alt="{{ $story['author_name'] }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 shrink-0">
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-slate-900 truncate">{{ $story['author_name'] }}</div>
                            <div class="text-[10px] text-slate-500 truncate">{{ $story['author_role'] }}</div>
                        </div>
                    </div>
                    <a href="{{ $pageUrl('article/' . $story['slug']) }}" class="text-xs font-bold text-[#8b151b] hover:underline shrink-0 flex items-center gap-1">
                        <span>Read Full Story</span> &rarr;
                    </a>
                </div>
            </article>
            @endforeach
        </div>

    </div>
</section>

{{-- ── 4. Callout: Submit Your Story ── --}}
<section class="py-14 bg-white border-t border-slate-200">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-r from-blue-900 to-indigo-900 text-white rounded-3xl p-8 sm:p-10 text-center shadow-lg relative overflow-hidden">
            <span class="text-[11px] font-extrabold uppercase tracking-widest text-amber-300 bg-white/10 px-3.5 py-1 rounded-full border border-white/20 inline-block mb-3">
                Community Voices
            </span>
            <h3 class="text-2xl sm:text-3xl font-extrabold text-white mb-3">
                Are You a Student or Alumnus with a Story to Share?
            </h3>
            <p class="text-xs sm:text-sm text-slate-200 mb-6 max-w-xl mx-auto leading-relaxed">
                Whether you just launched an exciting initiative, won an international contest, or have thoughtful reflections on your time at {{ $school->name }}, the Student Editorial Council wants to feature your voice.
            </p>
            <a href="{{ $pageUrl('contact') }}#contact-form" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-extrabold text-xs sm:text-sm transition shadow-sm">
                <i class="bi bi-pencil-square"></i>
                <span>Submit a Story for Review</span>
            </a>
        </div>
    </div>
</section>

@endsection

