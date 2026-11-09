@extends('layouts.app')
@section('title', 'Edit Article: ' . $article['title'])

@section('content')
<div class="px-4 lg:px-8 py-6 max-w-4xl mx-auto">

    {{-- Breadcrumb & Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('school.articles.index') }}" class="hover:text-slate-600 dark:hover:text-slate-200">Blogs &amp; Stories</a>
                <span>/</span>
                <span class="text-slate-700 dark:text-slate-300">Edit Article</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">
                Edit Article
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Update the content, cover image, category, or author details for this publication.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ url('/school/' . $school->slug . '/article/' . $article['slug']) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-indigo-600 dark:text-indigo-400 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 transition">
                <i class="bi bi-box-arrow-up-right"></i> View Live
            </a>

            <a href="{{ route('school.articles.index') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 transition">
                <i class="bi bi-arrow-left"></i> All Articles
            </a>
        </div>
    </div>

    @include('session-messages')

    @if ($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800">
            <h4 class="text-xs font-bold text-red-800 dark:text-red-200 mb-2">Please correct the following:</h4>
            <ul class="text-xs text-red-700 dark:text-red-300 list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('school.articles.update', $slug) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- 1. Article Type & Category Card --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-2">
                <i class="bi bi-tag-fill text-indigo-500"></i> Publication Type &amp; Classification
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 mb-5">
                @php
                    $curType = old('type', $article['type'] ?? 'blog');
                @endphp
                <label class="p-3.5 rounded-xl border cursor-pointer transition flex flex-col justify-between {{ $curType === 'blog' ? 'bg-blue-50/70 border-blue-400 ring-2 ring-blue-400/20 text-blue-900' : 'bg-slate-50 dark:bg-slate-900/40 border-slate-200 dark:border-slate-700 hover:border-slate-300' }}">
                    <div class="flex items-center justify-between mb-2">
                        <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-sm font-bold">
                            <i class="bi bi-journal-text"></i>
                        </span>
                        <input type="radio" name="type" value="blog" class="text-blue-600 focus:ring-blue-500" {{ $curType === 'blog' ? 'checked' : '' }}>
                    </div>
                    <div>
                        <span class="text-xs font-bold block">School Blog</span>
                        <span class="text-[11px] text-slate-500 block leading-tight mt-0.5">Faculty essays &amp; pedagogical research</span>
                    </div>
                </label>

                <label class="p-3.5 rounded-xl border cursor-pointer transition flex flex-col justify-between {{ $curType === 'news' ? 'bg-emerald-50/70 border-emerald-400 ring-2 ring-emerald-400/20 text-emerald-900' : 'bg-slate-50 dark:bg-slate-900/40 border-slate-200 dark:border-slate-700 hover:border-slate-300' }}">
                    <div class="flex items-center justify-between mb-2">
                        <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold">
                            <i class="bi bi-newspaper"></i>
                        </span>
                        <input type="radio" name="type" value="news" class="text-emerald-600 focus:ring-emerald-500" {{ $curType === 'news' ? 'checked' : '' }}>
                    </div>
                    <div>
                        <span class="text-xs font-bold block">News &amp; Press</span>
                        <span class="text-[11px] text-slate-500 block leading-tight mt-0.5">Official announcements &amp; press releases</span>
                    </div>
                </label>

                <label class="p-3.5 rounded-xl border cursor-pointer transition flex flex-col justify-between {{ $curType === 'story' ? 'bg-amber-50/70 border-amber-400 ring-2 ring-amber-400/20 text-amber-900' : 'bg-slate-50 dark:bg-slate-900/40 border-slate-200 dark:border-slate-700 hover:border-slate-300' }}">
                    <div class="flex items-center justify-between mb-2">
                        <span class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-sm font-bold">
                            <i class="bi bi-chat-quote"></i>
                        </span>
                        <input type="radio" name="type" value="story" class="text-amber-600 focus:ring-amber-500" {{ $curType === 'story' ? 'checked' : '' }}>
                    </div>
                    <div>
                        <span class="text-xs font-bold block">Student Story</span>
                        <span class="text-[11px] text-slate-500 block leading-tight mt-0.5">Student voice &amp; alumni reflections</span>
                    </div>
                </label>

                <label class="p-3.5 rounded-xl border cursor-pointer transition flex flex-col justify-between {{ $curType === 'publication' ? 'bg-purple-50/70 border-purple-400 ring-2 ring-purple-400/20 text-purple-900' : 'bg-slate-50 dark:bg-slate-900/40 border-slate-200 dark:border-slate-700 hover:border-slate-300' }}">
                    <div class="flex items-center justify-between mb-2">
                        <span class="w-7 h-7 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-sm font-bold">
                            <i class="bi bi-book"></i>
                        </span>
                        <input type="radio" name="type" value="publication" class="text-purple-600 focus:ring-purple-500" {{ $curType === 'publication' ? 'checked' : '' }}>
                    </div>
                    <div>
                        <span class="text-xs font-bold block">Publication</span>
                        <span class="text-[11px] text-slate-500 block leading-tight mt-0.5">Quarterly journal or magazine</span>
                    </div>
                </label>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Category / Topic
                    </label>
                    <input type="text" name="category" list="categoryList"
                           value="{{ old('category', $article['category'] ?? 'Pedagogy & Learning') }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <datalist id="categoryList">
                        <option value="Pedagogy & Learning">
                        <option value="Campus Life">
                        <option value="Science, Tech & AI">
                        <option value="Athletics & Sports">
                        <option value="Arts, Culture & Music">
                        <option value="Student Leadership">
                        <option value="Alumni Spotlight">
                        <option value="Institutional Milestone">
                    </datalist>
                </div>

                <div class="flex items-center pt-5">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="featured" value="1" class="sr-only peer" {{ old('featured', $article['featured'] ?? false) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                        <span class="ml-2.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                            <i class="bi bi-star-fill text-amber-500 mr-1"></i> Featured on Hero Banner
                        </span>
                    </label>
                </div>
            </div>
        </div>

        {{-- 2. Headline & Content --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i class="bi bi-fonts text-indigo-500"></i> Headline &amp; Story Content
            </h3>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Article Title <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" required
                       value="{{ old('title', $article['title']) }}"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Short Excerpt / Teaser <span class="text-red-500">*</span>
                </label>
                <textarea name="excerpt" rows="2" required
                          class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 text-xs leading-relaxed focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('excerpt', $article['excerpt']) }}</textarea>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                        Full Article Body / Content <span class="text-red-500">*</span>
                    </label>
                    <span class="text-[11px] text-slate-400">Supports HTML formatting</span>
                </div>
                <textarea name="content" rows="14" required
                          class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 text-xs sm:text-sm font-mono leading-relaxed focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('content', $article['content'] ?? '') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Search Tags
                </label>
                <input type="text" name="tags"
                       value="{{ old('tags', !empty($article['tags']) ? (is_array($article['tags']) ? implode(', ', $article['tags']) : $article['tags']) : '') }}"
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        {{-- 3. Cover Image --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-2 flex items-center gap-2">
                <i class="bi bi-image text-indigo-500"></i> Cover Photo (Auto-WebP)
            </h3>

            <div class="flex flex-col sm:flex-row items-center gap-5">
                <div class="w-full sm:w-48 aspect-[16/10] rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 overflow-hidden relative">
                    <img id="coverPreviewImg" src="{{ $article['image'] }}" alt="Cover preview" class="w-full h-full object-cover">
                </div>

                <div class="flex-1 w-full">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Replace Cover Photo</label>
                    <input type="file" name="image" id="coverImageInput" accept="image/*"
                           onchange="previewCoverImage(this)"
                           class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="text-[11px] text-slate-400 mt-2">
                        Upload to replace current photo. Uploads are automatically converted to optimized WebP.
                    </p>
                </div>
            </div>
        </div>

        {{-- 4. Author & Meta --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-2">
                <i class="bi bi-person-badge text-indigo-500"></i> Author &amp; Publication Byline
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Author Name</label>
                    <input type="text" name="author_name"
                           value="{{ old('author_name', $article['author_name'] ?? '') }}"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Author Role</label>
                    <input type="text" name="author_role"
                           value="{{ old('author_role', $article['author_role'] ?? '') }}"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Estimated Read Time</label>
                    <input type="text" name="read_time"
                           value="{{ old('read_time', $article['read_time'] ?? '4 min read') }}"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Published Date</label>
                    <input type="text" name="published_date"
                           value="{{ old('published_date', $article['published_date'] ?? now()->format('M d, Y')) }}"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        {{-- Submit Buttons --}}
        <div class="flex items-center justify-between pt-4">
            <a href="{{ route('school.articles.index') }}"
               class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 dark:text-slate-300 hover:bg-slate-100 text-xs font-bold transition">
                Cancel
            </a>

            <button type="submit"
                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-xs font-extrabold shadow-md shadow-indigo-600/20 transition flex items-center gap-2">
                <i class="bi bi-check-lg text-sm"></i> Save &amp; Update Article
            </button>
        </div>

    </form>

</div>

<script>
function previewCoverImage(input) {
    const preview = document.getElementById('coverPreviewImg');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection

