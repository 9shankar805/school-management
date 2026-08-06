<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Certificate Verification — {{ config('app.name') }}</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">

<div class="max-w-lg w-full">

    {{-- Header --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-100 mb-4">
            <svg class="w-8 h-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-slate-800">Certificate Verification</h1>
        <p class="text-slate-400 text-sm mt-1">{{ config('app.name') }}</p>
    </div>

    @if($issued)

        @php
            $isRevoked = $issued->status === 'revoked';
            $isExpired = $issued->valid_until && $issued->valid_until->isPast();
            $isValid   = !$isRevoked && !$isExpired && $issued->status === 'issued';
        @endphp

        {{-- Status banner --}}
        @if($isRevoked)
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 mb-5 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-rose-700">Certificate Revoked</p>
                <p class="text-sm text-rose-500">This certificate has been officially revoked and is no longer valid.</p>
            </div>
        </div>
        @elseif($isExpired)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-5 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-amber-700">Certificate Expired</p>
                <p class="text-sm text-amber-500">This certificate expired on {{ $issued->valid_until->format('F j, Y') }}.</p>
            </div>
        </div>
        @else
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 mb-5 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-emerald-700">Certificate Verified</p>
                <p class="text-sm text-emerald-500">This is an authentic certificate issued by {{ config('app.name') }}.</p>
            </div>
        </div>
        @endif

        {{-- Certificate details --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between pb-4 border-b border-slate-50">
                <h2 class="font-bold text-slate-800 text-lg">{{ $issued->template->name ?? $issued->typeLabel() }}</h2>
                <span class="text-xs font-mono bg-slate-100 text-slate-600 px-2 py-1 rounded-lg">
                    {{ $issued->certificate_number }}
                </span>
            </div>

            <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <div>
                    <dt class="text-xs font-medium text-slate-400 uppercase tracking-wide mb-0.5">Student Name</dt>
                    <dd class="font-semibold text-slate-800">{{ $issued->student->full_name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-slate-400 uppercase tracking-wide mb-0.5">Certificate Type</dt>
                    <dd class="font-semibold text-slate-800">{{ $issued->typeLabel() }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-slate-400 uppercase tracking-wide mb-0.5">Issue Date</dt>
                    <dd class="font-semibold text-slate-800">{{ $issued->issued_date->format('F j, Y') }}</dd>
                </div>
                @if($issued->valid_until)
                <div>
                    <dt class="text-xs font-medium text-slate-400 uppercase tracking-wide mb-0.5">Valid Until</dt>
                    <dd class="font-semibold {{ $isExpired ? 'text-rose-600' : 'text-slate-800' }}">
                        {{ $issued->valid_until->format('F j, Y') }}
                    </dd>
                </div>
                @endif
                <div>
                    <dt class="text-xs font-medium text-slate-400 uppercase tracking-wide mb-0.5">Issued By</dt>
                    <dd class="font-semibold text-slate-800">{{ $issued->issuer->full_name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-slate-400 uppercase tracking-wide mb-0.5">Issued At</dt>
                    <dd class="font-semibold text-slate-800">{{ config('app.name') }}</dd>
                </div>
                @if($issued->purpose)
                <div class="col-span-2">
                    <dt class="text-xs font-medium text-slate-400 uppercase tracking-wide mb-0.5">Purpose</dt>
                    <dd class="font-semibold text-slate-800">{{ $issued->purpose }}</dd>
                </div>
                @endif
            </dl>
        </div>

    @else
    {{-- Not found --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center">
        <div class="w-16 h-16 rounded-full bg-rose-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <h2 class="text-lg font-bold text-slate-800 mb-2">Certificate Not Found</h2>
        <p class="text-slate-400 text-sm">The QR code or verification link is invalid or the certificate does not exist in our records.</p>
    </div>
    @endif

    <p class="text-center text-xs text-slate-300 mt-6">
        Verified at {{ now()->format('F j, Y, g:i a') }} &middot; {{ config('app.name') }}
    </p>

</div>
</body>
</html>
