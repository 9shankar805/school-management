@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Certificate Templates</h1>
                <p class="text-slate-400 text-sm mt-0.5">Create and manage reusable certificate templates</p>
            </div>
            <a href="{{ route('certificates.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                <i class="bi bi-plus-lg"></i> New Template
            </a>
        </div>

        @if(session('status'))
        <div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>
        @endif

        {{-- Token reference --}}
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 mb-6 text-xs text-blue-700">
            <p class="font-semibold mb-1">Available tokens for body text:</p>
            <div class="flex flex-wrap gap-2">
                @foreach(['{{student_name}}','{{first_name}}','{{last_name}}','{{class}}','{{section}}','{{roll_no}}','{{date}}','{{school_name}}','{{academic_year}}','{{extra_notes}}','{{issued_by}}'] as $tok)
                <code class="bg-blue-100 px-1.5 py-0.5 rounded font-mono">{{ $tok }}</code>
                @endforeach
            </div>
        </div>

        @if($templates->count())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($templates as $t)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex flex-col gap-3">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-semibold text-slate-800">{{ $t->name }}</p>
                        <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">{{ \App\Models\CertificateTemplate::TYPES[$t->type] ?? $t->type }}</span>
                    </div>
                    <span class="text-xs {{ $t->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }} px-2 py-0.5 rounded-full">{{ $t->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
                @if($t->header_text)
                <p class="text-xs text-slate-400 line-clamp-2"><span class="font-medium text-slate-500">Header:</span> {{ $t->header_text }}</p>
                @endif
                <p class="text-xs text-slate-500 line-clamp-3">{{ \Illuminate\Support\Str::limit(strip_tags($t->body_text), 120) }}</p>
                @if($t->signature_name)
                <p class="text-xs text-slate-400"><i class="bi bi-pen me-1"></i>{{ $t->signature_name }}@if($t->signature_title), {{ $t->signature_title }}@endif</p>
                @endif
                <div class="flex gap-2 mt-auto">
                    <a href="{{ route('certificates.edit', $t->id) }}" class="flex-1 text-center py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-medium transition">Edit</a>
                    <form method="POST" action="{{ route('certificates.destroy', $t->id) }}" class="flex-1">
                        @csrf @method('DELETE')
                        <button onclick="return confirm('Delete this template?')" class="w-full py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg text-xs font-medium transition">Delete</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
            <i class="bi bi-file-earmark-text text-4xl text-slate-200"></i>
            <p class="mt-3 text-slate-400 text-sm">No templates yet. Create one to start generating certificates.</p>
            <a href="{{ route('certificates.create') }}" class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                <i class="bi bi-plus-lg"></i> Create Template
            </a>
        </div>
        @endif

    </div>
</div>
@endsection
