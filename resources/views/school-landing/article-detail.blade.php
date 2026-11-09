@extends('school-landing.layout')

@section('title', $article['title'] . ' — ' . $school->name)

@section('content')

{{-- ── 1. Breadcrumbs & Top Navigation ── --}}
<div class="bg-white border-b border-slate-200 py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                <a href="{{ $pageUrl('') }}" class="hover:underline text-slate-700">Home</a>
                <span>/</span>
                <a href="{{ $pageUrl('publications') }}" class="hover:underline text-slate-700">Publications</a>
                <span>/</span>
                <a href="{{ $pageUrl($article['type'] === 'blog' ? 'blogs' : ($article['type'] === 'news' ? 'news' : 'stories')) }}" class="hover:underline text-slate-700">
                    {{ $article['type_label'] }}
                </a>
            </div>

            <a href="{{ $pageUrl($article['type'] === 'blog' ? 'blogs' : ($article['type'] === 'news' ? 'news' : 'stories')) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#8b151b] hover:underline">
                <i class="bi bi-arrow-left"></i>
                <span>Back to {{ $article['type_label'] }} Archive</span>
            </a>
        </div>
    </div>
</div>

{{-- ── 2. Article Header ── --}}
<header class="py-12 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-2 mb-4">
            <span class="px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider bg-rose-50 text-[#8b151b] border border-rose-200">
                {{ $article['category'] }}
            </span>
            <span class="text-xs text-slate-400">•</span>
            <span class="text-xs font-semibold text-slate-500">
                <i class="bi bi-clock mr-1"></i> {{ $article['read_time'] }}
            </span>
        </div>

        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight mb-6">
            {{ $article['title'] }}
        </h1>

        <p class="text-slate-600 text-base sm:text-lg leading-relaxed mb-8">
            {{ $article['excerpt'] }}
        </p>

        {{-- Author & Date Meta Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 py-5 border-y border-slate-200">
            <div class="flex items-center gap-3">
                <img src="{{ $article['author_avatar'] }}" alt="{{ $article['author_name'] }}" class="w-12 h-12 rounded-full object-cover border-2 border-slate-200 shadow-xs">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">{{ $article['author_name'] }}</h3>
                    <p class="text-xs text-slate-500">{{ $article['author_role'] }} · {{ $school->name }}</p>
                </div>
            </div>

            <div class="flex items-center gap-4 text-xs text-slate-500">
                <div class="flex items-center gap-1.5">
                    <i class="bi bi-calendar3 text-[#8b151b]"></i>
                    <span>Published {{ $article['published_date'] }}</span>
                </div>
            </div>
        </div>
    </div>
</header>

{{-- ── 3. Main Hero Visual ── --}}
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mb-12">
    <div class="rounded-3xl overflow-hidden shadow-md aspect-[16/9] bg-slate-100 border border-slate-200">
        <img src="{{ $article['image'] }}" alt="{{ $article['title'] }}" class="w-full h-full object-cover">
    </div>
</div>

{{-- ── 4. Article Body Content & Social Share ── --}}
<article class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
    <div class="prose prose-slate prose-lg max-w-none text-slate-700 leading-relaxed space-y-4">
        {!! $article['content'] !!}
    </div>

    {{-- Tags --}}
    @if(!empty($article['tags']))
    <div class="mt-10 pt-6 border-t border-slate-200">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-2">Article Tags</span>
        <div class="flex flex-wrap gap-2">
            @foreach($article['tags'] as $tag)
            <span class="px-3 py-1 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                #{{ $tag }}
            </span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Social Share & Copy Link --}}
    <div class="mt-8 p-6 bg-slate-50 rounded-2xl border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 mb-0.5">Share this story</h4>
            <p class="text-[11px] text-slate-500">Distribute this piece with friends, parents, and alumni.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="https://api.whatsapp.com/send?text={{ urlencode($article['title'] . ' ' . request()->url()) }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white flex items-center justify-center transition shadow-xs" title="Share on WhatsApp">
                <i class="bi bi-whatsapp"></i>
            </a>
            <a href="https://twitter.com/intent/tweet?text={{ urlencode($article['title']) }}&url={{ urlencode(request()->url()) }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-xl bg-sky-500 hover:bg-sky-600 text-white flex items-center justify-center transition shadow-xs" title="Share on Twitter / X">
                <i class="bi bi-twitter-x"></i>
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-xl bg-blue-600 hover:bg-blue-700 text-white flex items-center justify-center transition shadow-xs" title="Share on Facebook">
                <i class="bi bi-facebook"></i>
            </a>
            <button type="button" onclick="navigator.clipboard.writeText(window.location.href); alert('Article URL copied to clipboard!');" class="px-3 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 text-xs font-bold border border-slate-300 transition shadow-xs flex items-center gap-1.5">
                <i class="bi bi-link-45deg text-sm"></i>
                <span>Copy Link</span>
            </button>
        </div>
    </div>

    {{-- Author Bio Card --}}
    <div class="mt-8 p-6 sm:p-8 bg-white rounded-3xl border border-slate-200/90 shadow-xs flex flex-col sm:flex-row items-center sm:items-start gap-5">
        <img src="{{ $article['author_avatar'] }}" alt="{{ $article['author_name'] }}" class="w-16 h-16 rounded-2xl object-cover border-2 border-slate-200 shrink-0">
        <div class="text-center sm:text-left">
            <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#8b151b]">About the Author</span>
            <h4 class="text-base font-extrabold text-slate-900 mb-1">{{ $article['author_name'] }}</h4>
            <p class="text-xs text-slate-500 font-medium mb-3">{{ $article['author_role'] }} at {{ $school->name }}</p>
            <p class="text-xs text-slate-600 leading-relaxed">
                Contributing scholarly essays, campus reflections, and academic updates to the official editorial publications and student journals of {{ $school->name }}.
            </p>
        </div>
    </div>
</article>

{{-- ── 5. Related Articles & Stories ── --}}
@if(!empty($relatedArticles))
<section class="py-16 bg-[#f8fafc] border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8 pb-3 border-b border-slate-200">
            <div>
                <span class="text-xs font-bold tracking-widest uppercase text-[#8b151b] bg-rose-50 px-3 py-1 rounded-md border border-rose-200 inline-block mb-1">
                    Continue Reading
                </span>
                <h3 class="text-2xl font-extrabold text-slate-900">Related Articles & Chronicles</h3>
            </div>
            <a href="{{ $pageUrl($article['type'] === 'blog' ? 'blogs' : ($article['type'] === 'news' ? 'news' : 'stories')) }}" class="text-xs font-bold text-[#8b151b] hover:underline">
                View All &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($relatedArticles as $rel)
            <article class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-xs hover:shadow-lg transition duration-300 flex flex-col justify-between group">
                <div>
                    <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
                        <img src="{{ $rel['image'] }}" alt="{{ $rel['title'] }}" class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                        <div class="absolute top-3 left-3">
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-wider bg-white/95 backdrop-blur-sm text-slate-900 shadow-xs border border-white/40">
                                {{ $rel['category'] }}
                            </span>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="text-[11px] text-slate-400 mb-2">{{ $rel['published_date'] }} • {{ $rel['read_time'] }}</div>
                        <h4 class="font-extrabold text-base text-slate-900 mb-2 group-hover:text-[#8b151b] transition leading-snug">
                            <a href="{{ $pageUrl('article/' . $rel['slug']) }}">
                                {{ $rel['title'] }}
                            </a>
                        </h4>
                        <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">
                            {{ $rel['excerpt'] }}
                        </p>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700 truncate">{{ $rel['author_name'] }}</span>
                    <a href="{{ $pageUrl('article/' . $rel['slug']) }}" class="text-xs font-bold text-[#8b151b] hover:underline shrink-0">
                        Read &rarr;
                    </a>
                </div>
            </article>
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection

