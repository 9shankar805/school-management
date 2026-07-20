@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex flex-wrap justify-between items-start mb-7 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight"><i class="bi bi-qr-code me-2"></i>QR Attendance</h1>
                <p class="text-slate-400 text-sm mt-0.5">Generate QR codes for contactless student check-in</p>
            </div>
            @can('take attendances')
            <a href="{{ route('attendance.qr.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition flex items-center gap-1.5">
                <i class="bi bi-plus-lg"></i> Generate New QR
            </a>
            @endcan
        </div>

        @if(session('status'))
        <div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>
        @endif

        @if($tokens->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center text-slate-400">
            <i class="bi bi-qr-code text-5xl mb-3 block"></i>
            <p class="text-sm">No QR tokens generated today. Click <strong>Generate New QR</strong> to start.</p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($tokens as $token)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex flex-col gap-3">
                {{-- Header --}}
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-semibold text-slate-700">{{ $token->schoolClass?->class_name ?? '—' }}
                            @if($token->section_id) · {{ $token->section?->section_name }} @endif
                            @if($token->course_id)  · {{ $token->course?->course_name }} @endif
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $token->date->format('d M Y') }}</p>
                    </div>
                    @if($token->is_active && $token->isValid())
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">ACTIVE</span>
                    @else
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">EXPIRED</span>
                    @endif
                </div>

                {{-- QR thumbnail --}}
                <div class="flex justify-center py-2">
                    <img src="{{ $token->qr_image_url }}" alt="QR Code" class="w-28 h-28 rounded-lg border border-slate-100">
                </div>

                {{-- Meta --}}
                <div class="text-xs text-slate-500 space-y-0.5">
                    <p><i class="bi bi-clock me-1"></i>
                        @if($token->valid_minutes > 0) Valid for {{ $token->valid_minutes }} min @else Unlimited (manual close) @endif
                    </p>
                    <p><i class="bi bi-alarm me-1"></i> School start: {{ \Carbon\Carbon::today()->setTimeFromTimeString($token->school_start)->format('H:i') }}</p>
                </div>

                {{-- Actions --}}
                <div class="flex gap-2 mt-auto pt-1 border-t border-slate-50">
                    <a href="{{ route('attendance.qr.show', $token->id) }}" class="flex-1 text-center text-xs font-medium py-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition">
                        <i class="bi bi-eye me-1"></i> View
                    </a>
                    @if($token->is_active)
                    <form method="POST" action="{{ route('attendance.qr.destroy', $token->id) }}" onsubmit="return confirm('Deactivate this QR token?')" class="flex-1">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full text-xs font-medium py-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition">
                            <i class="bi bi-x-circle me-1"></i> Revoke
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
