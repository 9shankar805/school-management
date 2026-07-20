@extends('layouts.app')
@section('content')
<div class="flex min-h-screen bg-slate-50">
    <div class="hidden lg:block w-64 flex-shrink-0 bg-white border-r border-slate-200">@include('layouts.left-menu')</div>
    <div class="flex-1 p-6 lg:p-8 overflow-auto">

        <div class="flex items-center gap-3 mb-7">
            <a href="{{ route('attendance.qr.index') }}" class="text-slate-400 hover:text-slate-600 transition"><i class="bi bi-arrow-left text-lg"></i></a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">QR Attendance — Live View</h1>
                <p class="text-slate-400 text-sm mt-0.5">
                    {{ $token->schoolClass?->class_name }}
                    @if($token->section_id) · {{ $token->section?->section_name }} @endif
                    · {{ $token->date->format('d M Y') }}
                </p>
            </div>
        </div>

        @if(session('status'))
        <div class="mb-5 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- QR Card --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex flex-col items-center gap-4">
                @if($token->is_active && $token->isValid())
                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-emerald-100 text-emerald-700"><i class="bi bi-circle-fill text-[8px] me-1"></i>ACTIVE</span>
                @else
                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-rose-100 text-rose-700"><i class="bi bi-x-circle me-1"></i>EXPIRED / REVOKED</span>
                @endif

                <img src="{{ $token->qr_image_url }}" alt="QR Code" class="w-52 h-52 rounded-xl border border-slate-100 shadow-sm">

                <div class="text-center text-sm text-slate-500 space-y-1">
                    <p><i class="bi bi-clock me-1"></i>
                        @if($token->valid_minutes > 0)
                            Expires {{ $token->created_at->addMinutes($token->valid_minutes)->format('H:i') }}
                            ({{ $token->valid_minutes }} min window)
                        @else
                            No expiry — close manually
                        @endif
                    </p>
                    <p><i class="bi bi-link-45deg me-1"></i>
                        <a href="{{ $token->scan_url }}" class="text-indigo-500 hover:underline text-xs break-all">{{ $token->scan_url }}</a>
                    </p>
                </div>

                @if($token->is_active)
                <form method="POST" action="{{ route('attendance.qr.destroy', $token->id) }}" onsubmit="return confirm('Deactivate this token?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-4 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-xl text-xs font-medium transition">
                        <i class="bi bi-x-circle me-1"></i> Revoke Token
                    </button>
                </form>
                @endif
            </div>

            {{-- Scanned students list --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                    <p class="text-sm font-semibold text-slate-700">Students Checked In</p>
                    <span class="text-xs bg-indigo-50 text-indigo-600 font-semibold px-2.5 py-0.5 rounded-full">{{ $scanned->count() }}</span>
                </div>
                @if($scanned->isEmpty())
                <div class="p-8 text-center text-slate-400 text-sm">No students have scanned yet.</div>
                @else
                <ul class="divide-y divide-slate-50 max-h-96 overflow-y-auto">
                    @foreach($scanned as $record)
                    <li class="flex items-center gap-3 px-5 py-3">
                        <img src="{{ $record->student?->avatar }}" alt="" class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-700 truncate">{{ $record->student?->full_name }}</p>
                            <p class="text-xs text-slate-400">Check-in: {{ $record->check_in ?? '—' }}</p>
                        </div>
                        @if($record->late_minutes > 0)
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">Late {{ $record->late_minutes }}m</span>
                        @else
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">On Time</span>
                        @endif
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>

        {{-- Auto-refresh every 20 seconds while token is active --}}
        @if($token->is_active && $token->isValid())
        <script>setTimeout(() => location.reload(), 20000);</script>
        <p class="text-xs text-slate-400 mt-4 text-center"><i class="bi bi-arrow-clockwise me-1"></i>Page auto-refreshes every 20 seconds.</p>
        @endif
    </div>
</div>
@endsection
