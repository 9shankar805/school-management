@extends('layouts.app')
@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">

    <nav class="text-xs text-slate-400 mb-5 flex items-center gap-1">
        <a href="{{ route('certificates.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Certificates</a>
        <i class="bi bi-chevron-right text-slate-300 text-xs"></i>
        <span class="text-slate-500 dark:text-slate-300">Edit Template</span>
    </nav>

    <div class="flex items-center gap-3 mb-7">
        <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400">
            <i class="bi bi-pencil-fill text-lg"></i>
        </div>
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Edit: {{ $template->name }}</h1>
            <p class="text-slate-400 text-sm">{{ $template->typeLabel() }} &middot; Last updated {{ $template->updated_at->diffForHumans() }}</p>
        </div>
    </div>

    @if(session('status'))
    <div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700 flex items-center gap-2">
        <i class="bi bi-check-circle-fill"></i> {{ session('status') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-5 p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl text-sm text-rose-700 dark:text-rose-300">
        <p class="font-semibold mb-1"><i class="bi bi-exclamation-triangle me-1"></i>Please fix the following errors:</p>
        @foreach($errors->all() as $e)<p class="mt-0.5">• {{ $e }}</p>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('certificates.update', $template->id) }}" enctype="multipart/form-data" class="w-full space-y-6">
        @csrf @method('PUT')
        @include('certificates._form')
        <div class="flex flex-wrap gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
            <a href="{{ route('certificates.builder', $template->id) }}"
               class="px-6 py-3 bg-fuchsia-600 hover:bg-fuchsia-700 text-white rounded-xl text-sm font-semibold transition shadow-md inline-flex items-center gap-2">
                <i class="bi bi-sliders"></i> Open Visual Drag & Drop Builder
            </a>
            <button type="submit"
                class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition shadow-md flex items-center gap-2 cursor-pointer">
                <i class="bi bi-check-circle-fill text-base"></i> Save Certificate Changes
            </button>
            <a href="{{ route('certificates.index') }}"
               class="px-6 py-3 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 rounded-xl text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-600 transition">
               Cancel
            </a>
        </div>
    </form>
</div>
@endsection
