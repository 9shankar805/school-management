@extends('layouts.app')
@section('title', 'Class In-Charge — Unassigned')

@section('content')
<div class="flex-1 p-6 lg:p-12 overflow-auto flex items-center justify-center">
    <div class="max-w-md w-full text-center bg-white dark:bg-slate-800 rounded-3xl p-8 border border-slate-100 dark:border-slate-700 shadow-xl">
        <div class="w-20 h-20 rounded-3xl bg-amber-50 text-amber-500 mx-auto flex items-center justify-center text-3xl font-black mb-4 shadow-sm">
            <i class="bi bi-person-badge"></i>
        </div>
        <h2 class="text-xl font-extrabold text-slate-800 dark:text-slate-100 mb-2">No Class In-Charge Assigned</h2>
        <p class="text-xs text-slate-500 leading-relaxed mb-6">
            You are currently not designated as a Class Teacher (कक्षा इन्चार्ज) for any section in the active session. If you believe this is an error, please contact the Principal or School Administrator.
        </p>
        <a href="{{ route('home') }}" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md transition inline-flex items-center gap-2">
            <i class="bi bi-house-door-fill"></i> Return to Main Dashboard
        </a>
    </div>
</div>
@endsection
