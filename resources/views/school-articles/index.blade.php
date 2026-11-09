@extends('layouts.app')
@section('title', 'School Blogs, News & Student Stories')

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-7xl mx-auto">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 text-white flex items-center justify-center text-2xl shadow-md">
                <i class="bi bi-journal-richtext"></i>
            </span>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">
                    School Blogs, News &amp; Student Stories
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Write, manage, and publish academic essays, campus press dispatches, student literary voices, and official magazines on your public school website.
                </p>
            </div>
        </div>

        <div class="flex items-center flex-wrap gap-2.5">
            {{-- Public Website Link --}}
            <a href="{{ url('/school/' . $school->slug . '/blogs') }}" target="_blank"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 shadow-xs transition">
                <i class="bi bi-box-arrow-up-right text-indigo-500"></i> View Live Website Blogs
            </a>

            {{-- Write New Blog Button --}}
            <a href="{{ route('school.articles.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-[#8b151b] to-red-700 hover:from-red-800 hover:to-red-900 shadow-md shadow-red-900/20 transition">
                <i class="bi bi-pen-fill"></i> Write New Blog / Story
            </a>
        </div>
    </div>

    @include('session-messages')

    {{-- Quick Metric Counter Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <a href="{{ route('school.articles.index', ['type' => 'blog']) }}"
           class="p-4 rounded-2xl bg-white dark:bg-slate-800 border {{ $filterType === 'blog' ? 'border-blue-500 ring-2 ring-blue-500/20' : 'border-slate-200 dark:border-slate-700' }} shadow-xs hover:border-blue-400 transition group">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">School Blogs</span>
                <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold">
                    <i class="bi bi-journal-text"></i>
                </span>
            </div>
            <span class="text-2xl font-black text-slate-900 dark:text-slate-100">{{ $stats['blogs'] }}</span>
            <span class="text-[11px] text-blue-600 font-semibold block mt-0.5">Academic Essays &rarr;</span>
        </a>

        <a href="{{ route('school.articles.index', ['type' => 'news']) }}"
           class="p-4 rounded-2xl bg-white dark:bg-slate-800 border {{ $filterType === 'news' ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-slate-200 dark:border-slate-700' }} shadow-xs hover:border-emerald-400 transition group">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">News &amp; Press</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">
                    <i class="bi bi-newspaper"></i>
                </span>
            </div>
            <span class="text-2xl font-black text-slate-900 dark:text-slate-100">{{ $stats['news'] }}</span>
            <span class="text-[11px] text-emerald-600 font-semibold block mt-0.5">Press Releases &rarr;</span>
        </a>

        <a href="{{ route('school.articles.index', ['type' => 'story']) }}"
           class="p-4 rounded-2xl bg-white dark:bg-slate-800 border {{ $filterType === 'story' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-slate-200 dark:border-slate-700' }} shadow-xs hover:border-amber-400 transition group">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Student Stories</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-bold">
                    <i class="bi bi-chat-quote"></i>
                </span>
            </div>
            <span class="text-2xl font-black text-slate-900 dark:text-slate-100">{{ $stats['stories'] }}</span>
            <span class="text-[11px] text-amber-600 font-semibold block mt-0.5">Scholar Voices &rarr;</span>
        </a>

        <a href="{{ route('school.articles.index', ['type' => 'publication']) }}"
           class="p-4 rounded-2xl bg-white dark:bg-slate-800 border {{ $filterType === 'publication' ? 'border-purple-500 ring-2 ring-purple-500/20' : 'border-slate-200 dark:border-slate-700' }} shadow-xs hover:border-purple-400 transition group">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Publications</span>
                <span class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm font-bold">
                    <i class="bi bi-book"></i>
                </span>
            </div>
            <span class="text-2xl font-black text-slate-900 dark:text-slate-100">{{ $stats['publications'] }}</span>
            <span class="text-[11px] text-purple-600 font-semibold block mt-0.5">Cornice &amp; The Circle &rarr;</span>
        </a>
    </div>

    {{-- Filter Nav Pills --}}
    <div class="flex items-center justify-between flex-wrap gap-3 mb-5">
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 no-scrollbar">
            <a href="{{ route('school.articles.index') }}"
               class="px-3.5 py-1.5 rounded-full text-xs font-bold transition {{ empty($filterType) ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                All Articles ({{ $stats['total'] }})
            </a>
            <a href="{{ route('school.articles.index', ['type' => 'blog']) }}"
               class="px-3.5 py-1.5 rounded-full text-xs font-bold transition {{ $filterType === 'blog' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                Blogs ({{ $stats['blogs'] }})
            </a>
            <a href="{{ route('school.articles.index', ['type' => 'news']) }}"
               class="px-3.5 py-1.5 rounded-full text-xs font-bold transition {{ $filterType === 'news' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                News ({{ $stats['news'] }})
            </a>
            <a href="{{ route('school.articles.index', ['type' => 'story']) }}"
               class="px-3.5 py-1.5 rounded-full text-xs font-bold transition {{ $filterType === 'story' ? 'bg-amber-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                Stories ({{ $stats['stories'] }})
            </a>
            <a href="{{ route('school.articles.index', ['type' => 'publication']) }}"
               class="px-3.5 py-1.5 rounded-full text-xs font-bold transition {{ $filterType === 'publication' ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                Publications ({{ $stats['publications'] }})
            </a>
        </div>

        <span class="text-xs text-slate-400">
            Showing {{ count($allArticles) }} published {{ count($allArticles) === 1 ? 'article' : 'articles' }}
        </span>
    </div>

    {{-- Articles Grid --}}
    @if(empty($allArticles))
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-400 flex items-center justify-center text-3xl mx-auto mb-4">
                <i class="bi bi-journal-x"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-1">No Articles Found</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto mb-6">
                Start sharing your school's academic achievements, campus life, student writings, and newsletters with your community.
            </p>
            <a href="{{ route('school.articles.create') }}" class="btn-primary-dark !text-xs !py-2.5 !px-5 inline-flex items-center gap-2">
                <i class="bi bi-pen-fill"></i> Write Your First Blog
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($allArticles as $art)
            @php
                $typeBadgeClasses = [
                    'blog'        => 'bg-blue-50 text-blue-700 border-blue-200',
                    'news'        => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'story'       => 'bg-amber-50 text-amber-800 border-amber-200',
                    'publication' => 'bg-purple-50 text-purple-700 border-purple-200',
                ];
                $badgeCls = $typeBadgeClasses[$art['type'] ?? 'blog'] ?? 'bg-slate-50 text-slate-700 border-slate-200';
            @endphp
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col justify-between group hover:border-slate-300 transition duration-300">
                
                {{-- Cover Image --}}
                <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
                    <img src="{{ $art['image'] }}" alt="{{ $art['title'] }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    
                    <div class="absolute top-3 left-3 flex items-center gap-1.5">
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full border shadow-xs backdrop-blur-sm bg-white/95 {{ $badgeCls }}">
                            {{ $art['type_label'] }}
                        </span>
                    </div>

                    @if(!empty($art['featured']))
                    <div class="absolute top-3 right-3 bg-amber-500 text-white text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md shadow-xs flex items-center gap-1">
                        <i class="bi bi-star-fill text-[9px]"></i> Featured
                    </div>
                    @endif
                </div>

                {{-- Content Body --}}
                <div class="p-5 flex-1 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between text-[11px] text-slate-400 mb-2">
                            <span>{{ $art['category'] }}</span>
                            <span>{{ $art['published_date'] }} · {{ $art['read_time'] }}</span>
                        </div>

                        <h3 class="font-bold text-base text-slate-900 dark:text-slate-100 mb-2 line-clamp-2 leading-snug group-hover:text-indigo-600 transition">
                            {{ $art['title'] }}
                        </h3>

                        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-3 leading-relaxed mb-4">
                            {{ $art['excerpt'] }}
                        </p>
                    </div>

                    {{-- Author & Actions Footer --}}
                    <div>
                        <div class="flex items-center gap-2.5 pt-3 border-t border-slate-100 dark:border-slate-700/60 mb-3">
                            <img src="{{ $art['author_avatar'] ?? asset('imgs/staff/principal.webp') }}"
                                 alt="{{ $art['author_name'] }}"
                                 class="w-7 h-7 rounded-full object-cover border border-slate-200">
                            <div class="min-w-0 flex-1">
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block truncate">{{ $art['author_name'] }}</span>
                                <span class="text-[10px] text-slate-400 block truncate">{{ $art['author_role'] }}</span>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-700/60">
                            <a href="{{ url('/school/' . $school->slug . '/article/' . $art['slug']) }}" target="_blank"
                               class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                                <span>View Live</span> <i class="bi bi-arrow-up-right text-[10px]"></i>
                            </a>

                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('school.articles.edit', $art['slug']) }}"
                                   class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-slate-100 dark:hover:bg-slate-700 transition"
                                   title="Edit Article">
                                    <i class="bi bi-pencil-square"></i>
                                </a>

                                <form method="POST" action="{{ route('school.articles.destroy', $art['slug']) }}"
                                      onsubmit="return confirm('Are you sure you want to delete this article?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40 transition"
                                            title="Delete Article">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            @endforeach
        </div>
    @endif

</div>
@endsection

