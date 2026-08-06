@extends('layouts.app')
@section('content')
<div class="flex-1 p-6 lg:p-8 overflow-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight"><i class="bi bi-award-fill me-2 text-indigo-600"></i>My Results</h1>
        <p class="text-slate-400 text-sm mt-0.5">Academic marksheet & report card</p>
    </div>

    @include('session-messages')

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center max-w-lg mx-auto my-8">
        <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-3xl font-bold mx-auto mb-4">
            <i class="bi bi-journal-bookmark font-bold"></i>
        </div>
        <h2 class="text-lg font-bold text-slate-800 mb-1">No Results Published Yet</h2>
        <p class="text-sm text-slate-400">
            Results have not been recorded or published for your class in the current semester. Please check back later or contact your school administrator.
        </p>
    </div>
</div>
@endsection
