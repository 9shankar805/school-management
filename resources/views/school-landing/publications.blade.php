@extends('school-landing.layout')

@section('title', 'Official Publications, Journals & Magazines — ' . $school->name)

@section('content')

{{-- ── 1. Page Header & Breadcrumb ── --}}
<div class="bg-white text-slate-900 py-12 border-b border-slate-200 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-3 uppercase tracking-wider">
            <a href="{{ $pageUrl('') }}" class="hover:underline text-slate-700">Home</a>
            <span>/</span>
            <span class="text-[#8b151b] font-bold">Official Publications & Media</span>
        </div>
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <p class="rbs-sub-title text-[#0b417e] mb-2">EDITORIAL & PRESS COUNCIL</p>
                <h1 class="text-3xl sm:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    Official Publications & Magazines
                </h1>
                <p class="text-slate-600 text-sm sm:text-base mt-2 max-w-2xl leading-relaxed">
                    Discover creative writing, literary journals, research blogs, and regular newsletters authored by scholars and faculty of {{ $school->name }}.
                </p>
            </div>
            
            {{-- Category Filter Navigation --}}
            <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none shrink-0">
                <a href="{{ $pageUrl('publications') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition bg-[#8b151b] text-white shadow-xs">
                    <i class="bi bi-collection mr-1"></i> All
                </a>
                <a href="{{ $pageUrl('blogs') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition bg-slate-100 hover:bg-slate-200 text-slate-700">
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

{{-- ── 2. The 4 Signature Flagship Publications (Exact Rato Bangala UI Match) ── --}}
<section class="py-16 bg-[#f8fafc] border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex items-center justify-between mb-8 pb-3 border-b border-slate-200">
            <div>
                <span class="text-xs font-bold tracking-widest uppercase text-rose-700 bg-rose-50 px-3 py-1 rounded-md border border-rose-200 inline-block mb-1">
                    Flagship Imprints
                </span>
                <h3 class="text-2xl font-extrabold text-slate-900">Student Magazines & Archival Journals</h3>
            </div>
            <span class="text-xs font-semibold text-slate-500 bg-white border border-slate-200 px-3 py-1.5 rounded-xl hidden sm:inline-flex items-center gap-1.5">
                <i class="bi bi-journal-richtext text-[#8b151b]"></i> Student Editorial Council
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- 1. School Blogs --}}
            <div onclick="window.location.href='{{ $pageUrl('blogs') }}'" 
                 class="group relative rounded-3xl overflow-hidden shadow-sm border border-slate-200 aspect-[3/4] cursor-pointer card-lift">
                <img src="{{ $school->sectionImage('pub_blogs_image', 'imgs/hero/hero_1.webp') }}" alt="School Blogs" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent"></div>
                <div class="absolute top-4 left-4">
                    <span class="px-2.5 py-1 rounded-md bg-white/20 backdrop-blur-md text-white text-[10px] font-extrabold tracking-wider uppercase border border-white/20">
                        Online Journal
                    </span>
                </div>
                <div class="absolute bottom-5 left-5 right-5 text-white">
                    <div class="flex items-center justify-between gap-2">
                        <h4 class="text-lg font-bold text-white group-hover:text-amber-300 transition">School Blogs</h4>
                        <i class="bi bi-arrow-right text-lg text-white group-hover:translate-x-1.5 transition"></i>
                    </div>
                    <p class="text-xs text-slate-300 mt-1 line-clamp-2">Student essays, science symposiums & contemporary debates.</p>
                </div>
            </div>

            {{-- 2. Newsletter Archive --}}
            <div onclick="openPublicationModal('Newsletter Archive', 'Browse quarterly newsletters highlighting academic honors, inter-school olympiads, workshops, and institutional milestones.', 'Quarterly Archive')" 
                 class="group relative rounded-3xl overflow-hidden shadow-sm border border-slate-200 aspect-[3/4] cursor-pointer card-lift">
                <img src="{{ $school->sectionImage('pub_newsletter_image', 'imgs/hero/hero_2.webp') }}" alt="Newsletter Archive" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent"></div>
                <div class="absolute top-4 left-4">
                    <span class="px-2.5 py-1 rounded-md bg-white/20 backdrop-blur-md text-white text-[10px] font-extrabold tracking-wider uppercase border border-white/20">
                        Quarterly Dispatch
                    </span>
                </div>
                <div class="absolute bottom-5 left-5 right-5 text-white">
                    <div class="flex items-center justify-between gap-2">
                        <h4 class="text-lg font-bold text-white group-hover:text-amber-300 transition">Newsletter Archive</h4>
                        <i class="bi bi-arrow-right text-lg text-white group-hover:translate-x-1.5 transition"></i>
                    </div>
                    <p class="text-xs text-slate-300 mt-1 line-clamp-2">Quarterly administrative reports & campus milestone chronicles.</p>
                </div>
            </div>

            {{-- 3. Cornice (Annual Student Anthology) --}}
            <div onclick="openPublicationModal('Cornice Literary Magazine', 'Cornice is our annual flagship student literary anthology, collecting original poems, creative short stories, photography, and fine artwork created by students across all grades.', 'Annual Literary Journal')" 
                 class="group relative rounded-3xl overflow-hidden shadow-sm border border-slate-200 aspect-[3/4] cursor-pointer card-lift">
                <img src="{{ $school->sectionImage('pub_cornice_image', 'imgs/hero/hero_3.webp') }}" alt="Cornice Literary Magazine" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent"></div>
                <div class="absolute top-4 left-4">
                    <span class="px-2.5 py-1 rounded-md bg-white/20 backdrop-blur-md text-white text-[10px] font-extrabold tracking-wider uppercase border border-white/20">
                        Annual Literary
                    </span>
                </div>
                <div class="absolute bottom-5 left-5 right-5 text-white">
                    <div class="flex items-center justify-between gap-2">
                        <h4 class="text-lg font-bold text-white group-hover:text-amber-300 transition">Cornice</h4>
                        <i class="bi bi-arrow-right text-lg text-white group-hover:translate-x-1.5 transition"></i>
                    </div>
                    <p class="text-xs text-slate-300 mt-1 line-clamp-2">Annual student anthology of poetry, creative prose & fine artwork.</p>
                </div>
            </div>

            {{-- 4. The Circle (Flagship Campus Review) --}}
            <div onclick="openPublicationModal('The Circle Magazine', 'The Circle is our flagship student-led journalism and features magazine, documenting campus happenings, sports championships, and alumni interviews.', 'Editorial Review')" 
                 class="group relative rounded-3xl overflow-hidden shadow-sm border border-slate-200 aspect-[3/4] cursor-pointer card-lift">
                <img src="{{ $school->sectionImage('pub_circle_image', file_exists(public_path('imgs/hero/school_hero_graduates.jpg')) ? 'imgs/hero/school_hero_graduates.jpg' : 'imgs/schools.webp') }}" alt="The Circle Magazine" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent"></div>
                <div class="absolute top-4 left-4">
                    <span class="px-2.5 py-1 rounded-md bg-white/20 backdrop-blur-md text-white text-[10px] font-extrabold tracking-wider uppercase border border-white/20">
                        Editorial Review
                    </span>
                </div>
                <div class="absolute bottom-5 left-5 right-5 text-white">
                    <div class="flex items-center justify-between gap-2">
                        <h4 class="text-lg font-bold text-white group-hover:text-amber-300 transition">The Circle</h4>
                        <i class="bi bi-arrow-right text-lg text-white group-hover:translate-x-1.5 transition"></i>
                    </div>
                    <p class="text-xs text-slate-300 mt-1 line-clamp-2">Flagship campus magazine produced by student editorial council.</p>
                </div>
            </div>
        </div>

    </div>
</section>

{{-- ── 3. All Articles Catalog (Filterable by Subcategory) ── --}}
<section class="py-16 sm:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-10 pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-xl font-extrabold text-slate-900">All Published Articles, Essays & Dispatches</h3>
                <p class="text-xs text-slate-500 mt-1">Explore our complete digital repository of academic thought, campus news, and creative writing.</p>
            </div>
            <div class="text-xs font-semibold text-slate-500">
                Total <strong class="text-slate-900">{{ count($articles) }}</strong> published works
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($articles as $item)
            <article class="bg-white rounded-3xl border border-slate-200/90 overflow-hidden shadow-xs hover:shadow-lg hover:border-slate-300 transition duration-300 flex flex-col justify-between group">
                <div>
                    {{-- Thumbnail --}}
                    <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
                        <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                        <div class="absolute top-3 left-3 flex items-center gap-1.5">
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-wider bg-slate-900 text-white shadow-xs">
                                {{ $item['type_label'] }}
                            </span>
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-white/95 backdrop-blur-sm text-slate-800 shadow-xs border border-white/40">
                                {{ $item['category'] }}
                            </span>
                        </div>
                    </div>

                    {{-- Text --}}
                    <div class="p-6">
                        <div class="flex items-center gap-2 text-[11px] text-slate-400 mb-2">
                            <span>{{ $item['published_date'] }}</span>
                            <span>•</span>
                            <span><i class="bi bi-clock mr-0.5"></i> {{ $item['read_time'] }}</span>
                        </div>
                        <h4 class="font-extrabold text-lg text-slate-900 mb-2.5 group-hover:text-[#8b151b] transition leading-snug">
                            <a href="{{ $pageUrl('article/' . $item['slug']) }}">
                                {{ $item['title'] }}
                            </a>
                        </h4>
                        <p class="text-xs text-slate-600 leading-relaxed line-clamp-3 mb-4">
                            {{ $item['excerpt'] }}
                        </p>
                    </div>
                </div>

                {{-- Author & Link --}}
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <img src="{{ $item['author_avatar'] }}" alt="{{ $item['author_name'] }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 shrink-0">
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-slate-900 truncate">{{ $item['author_name'] }}</div>
                            <div class="text-[10px] text-slate-500 truncate">{{ $item['author_role'] }}</div>
                        </div>
                    </div>
                    <a href="{{ $pageUrl('article/' . $item['slug']) }}" class="text-xs font-bold text-[#8b151b] hover:underline shrink-0 flex items-center gap-1">
                        <span>Read</span> &rarr;
                    </a>
                </div>
            </article>
            @endforeach
        </div>

    </div>
</section>

{{-- ── 4. Interactive Reading Modal (For Digital Magazines & Cornice Preview) ── --}}
<div id="publicationModal" class="fixed inset-0 z-[9999] bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 transition-all duration-300 opacity-0 pointer-events-none" onclick="closePublicationModal()">
    <div class="bg-white rounded-3xl overflow-hidden shadow-2xl border border-slate-200 max-w-md w-full transform transition-all duration-300 scale-95" id="publicationCard" onclick="event.stopPropagation()">
        <div class="bg-[#8b151b] text-white p-5 flex items-center justify-between border-b border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-400 text-slate-950 flex items-center justify-center text-lg font-extrabold shadow-md">
                    <i class="bi bi-book-half"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-white" id="pubModalTitle">Publication Title</h3>
                    <p class="text-[11px] text-amber-200 font-medium" id="pubModalTag">Student Journal</p>
                </div>
            </div>
            <button onclick="closePublicationModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>
        <div class="p-6">
            <p class="text-xs text-slate-600 leading-relaxed mb-5" id="pubModalDesc">
                Publication details and issue summary.
            </p>
            <div class="space-y-2.5">
                <button onclick="handleSampleRead()" class="w-full py-2.5 px-4 rounded-xl bg-[#8b151b] hover:bg-[#6b0f14] text-white font-bold text-xs flex items-center justify-center gap-2 shadow-xs transition">
                    <i class="bi bi-file-earmark-pdf"></i> Read Online Digital Edition
                </button>
                <button onclick="closePublicationModal()" class="w-full py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                    Close Preview
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

