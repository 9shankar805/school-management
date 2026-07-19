@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto max-w-3xl">

        <nav class="text-xs text-slate-400 mb-5">
            <a href="{{ route('certificates.index') }}" class="hover:text-indigo-600">Certificates</a>
            <span class="mx-1">/</span> Edit
        </nav>
        <h1 class="text-2xl font-bold text-slate-800 mb-7 tracking-tight">Edit Template</h1>

        <form method="POST" action="{{ route('certificates.update', $template->id) }}" class="space-y-5">
            @csrf @method('PUT')
            @include('students.certificates._form', ['template' => $template])
            <div class="flex gap-3">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">Save Changes</button>
                <a href="{{ route('certificates.index') }}" class="px-6 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
