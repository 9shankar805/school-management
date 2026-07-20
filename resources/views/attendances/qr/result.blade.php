@extends('layouts.app')
@section('content')
<div class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 max-w-sm w-full text-center">

        @if($success)
            <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4">
                <i class="bi bi-check-lg text-3xl text-emerald-600"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-800 mb-2">Attendance Marked!</h2>
        @else
            <div class="w-16 h-16 rounded-full bg-rose-100 flex items-center justify-center mx-auto mb-4">
                <i class="bi bi-x-lg text-3xl text-rose-600"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-800 mb-2">Unable to Mark Attendance</h2>
        @endif

        <p class="text-slate-500 text-sm leading-relaxed mb-6">{{ $message }}</p>

        @if($success && ($lateMinutes ?? 0) > 0)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-amber-700 text-sm mb-5">
            <i class="bi bi-clock-history me-1"></i> You were <strong>{{ $lateMinutes }} minute(s) late</strong>. Please aim to arrive before school start time.
        </div>
        @endif

        @auth
        <a href="{{ url('/home') }}" class="inline-block px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">
            <i class="bi bi-house me-1"></i> Go to Dashboard
        </a>
        @else
        <a href="{{ route('login') }}" class="inline-block px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition">
            <i class="bi bi-box-arrow-in-right me-1"></i> Log In
        </a>
        @endauth
    </div>
</div>
@endsection
