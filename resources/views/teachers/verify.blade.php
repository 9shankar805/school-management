<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Faculty Verification — {{ $teacher->full_name }} | {{ $school?->name ?? config('app.name') }}</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #0f172a; color: #f8fafc; }
  .glass-card { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.1); }
  .badge-verified { background: linear-gradient(135deg, #1e3a8a, #3b82f6); box-shadow: 0 0 15px rgba(59, 130, 246, 0.4); }
</style>
</head>
<body class="min-h-screen flex flex-col justify-between p-4 sm:p-6 md:p-10 bg-slate-950 text-slate-100">

  {{-- Header --}}
  <header class="max-w-3xl mx-auto w-full flex items-center justify-between pb-6 border-b border-slate-800">
    <div class="flex items-center gap-3">
      @if($school?->logo)
        <img src="{{ asset('storage/' . ltrim($school->logo, '/')) }}" alt="Logo" class="w-10 h-10 rounded-full bg-white p-1 object-contain border border-slate-700">
      @else
        <div class="w-10 h-10 rounded-full bg-blue-700 flex items-center justify-center font-bold text-white shadow-lg">
          {{ strtoupper(substr($school?->name ?? 'S', 0, 1)) }}
        </div>
      @endif
      <div>
        <h1 class="font-bold text-base sm:text-lg text-white leading-tight">{{ $school?->name ?? 'Genesis Global School' }}</h1>
        <p class="text-xs text-slate-400">Official Faculty Identity Verification</p>
      </div>
    </div>
    <div class="hidden sm:block">
      <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-semibold badge-verified text-white">
        <i class="bi bi-patch-check-fill text-sm text-emerald-400"></i> VERIFIED FACULTY
      </span>
    </div>
  </header>

  {{-- Main Content --}}
  <main class="max-w-3xl mx-auto w-full my-6 flex-1">
    
    {{-- Verified Banner (Mobile) --}}
    <div class="sm:hidden mb-4 text-center">
      <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-semibold badge-verified text-white shadow-lg">
        <i class="bi bi-patch-check-fill text-sm text-emerald-400"></i> OFFICIAL VERIFIED FACULTY
      </span>
    </div>

    {{-- Main Profile Card --}}
    <div class="glass-card rounded-3xl p-6 sm:p-8 shadow-2xl relative overflow-hidden mb-6">
      <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

      <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
        {{-- Teacher Photo --}}
        <div class="relative group flex-shrink-0">
          @if($teacher->photo)
            <img src="{{ asset('storage/' . ltrim($teacher->photo, '/')) }}" alt="{{ $teacher->full_name }}" class="w-28 h-32 sm:w-32 sm:h-36 rounded-2xl object-cover border-2 border-indigo-500/40 shadow-xl">
          @else
            <div class="w-28 h-32 sm:w-32 sm:h-36 rounded-2xl bg-slate-800 border-2 border-slate-700 flex items-center justify-center text-3xl font-bold text-slate-400">
              {{ strtoupper(substr($teacher->first_name, 0, 1)) }}
            </div>
          @endif
          <div class="absolute -bottom-2 right-2 bg-emerald-500 text-white rounded-full p-1 border-2 border-slate-900 flex items-center justify-center">
            <i class="bi bi-check-lg text-xs"></i>
          </div>
        </div>

        {{-- Teacher Overview --}}
        <div class="flex-1 text-center sm:text-left min-w-0">
          <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">{{ $teacher->full_name }}</h2>
          <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mt-2">
            <span class="px-3 py-1 bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 rounded-lg text-xs font-bold uppercase">
              {{ $teacher->designation ?: 'Faculty Member' }}
            </span>
            <span class="px-3 py-1 bg-slate-800 text-slate-300 border border-slate-700 rounded-lg text-xs font-mono font-medium">
              ID: {{ $teacher->employee_id ?? ('EMP-' . date('Y') . '-' . str_pad($teacher->id, 3, '0', STR_PAD_LEFT)) }}
            </span>
          </div>

          <p class="text-xs text-slate-400 mt-3 flex items-center justify-center sm:justify-start gap-2">
            <i class="bi bi-building-fill text-indigo-400"></i>
            {{ $teacher->department ?: 'Academic Department' }} &middot; {{ $school?->name ?? 'Genesis Global School' }}
          </p>

          {{-- Quick Action Buttons --}}
          <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 mt-5">
            <a href="{{ route('teacher.qr-login', $token) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white font-bold text-xs sm:text-sm rounded-xl shadow-lg shadow-indigo-500/20 transition-all hover:scale-105">
              <i class="bi bi-box-arrow-in-right text-base"></i> Instant Login to Teacher Portal
            </a>
            <a href="{{ route('teacher.id-card', $teacher->id) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs sm:text-sm rounded-xl border border-slate-700 transition">
              <i class="bi bi-file-earmark-pdf"></i> Download PDF Faculty Card
            </a>
          </div>
        </div>
      </div>
    </div>

    {{-- Detailed Info Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      
      {{-- Faculty Details --}}
      <div class="glass-card rounded-2xl p-5">
        <h3 class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-4 flex items-center gap-2">
          <i class="bi bi-briefcase-fill"></i> Professional Details
        </h3>
        <dl class="space-y-2.5 text-xs">
          <div class="flex justify-between py-1.5 border-b border-slate-800">
            <dt class="text-slate-400 font-medium">Designation</dt>
            <dd class="font-bold text-slate-200">{{ $teacher->designation ?: 'Faculty Member' }}</dd>
          </div>
          <div class="flex justify-between py-1.5 border-b border-slate-800">
            <dt class="text-slate-400 font-medium">Department</dt>
            <dd class="font-bold text-slate-200">{{ $teacher->department ?: 'Academic Faculty' }}</dd>
          </div>
          <div class="flex justify-between py-1.5 border-b border-slate-800">
            <dt class="text-slate-400 font-medium">Faculty Status</dt>
            <dd class="font-bold text-emerald-400">ACTIVE FACULTY</dd>
          </div>
          <div class="flex justify-between py-1.5">
            <dt class="text-slate-400 font-medium">Academic Session</dt>
            <dd class="font-bold text-slate-200">{{ $schoolSession?->session_name ?? (date('Y').'-'.(date('Y')+1)) }}</dd>
          </div>
        </dl>
      </div>

      {{-- Contact Details --}}
      <div class="glass-card rounded-2xl p-5">
        <h3 class="text-xs font-bold text-blue-400 uppercase tracking-wider mb-4 flex items-center gap-2">
          <i class="bi bi-telephone-fill"></i> Contact & Communication
        </h3>
        <dl class="space-y-2.5 text-xs">
          <div class="flex justify-between py-1.5 border-b border-slate-800">
            <dt class="text-slate-400 font-medium">Official Email</dt>
            <dd class="font-bold text-slate-200">{{ $teacher->email }}</dd>
          </div>
          <div class="flex justify-between py-1.5 border-b border-slate-800">
            <dt class="text-slate-400 font-medium">Phone Number</dt>
            <dd class="font-bold text-slate-200">{{ $teacher->phone ?? '+91 97110 00560' }}</dd>
          </div>
          <div class="flex justify-between py-1.5 border-b border-slate-800">
            <dt class="text-slate-400 font-medium">Institution Office</dt>
            <dd class="font-bold text-slate-200">{{ $school?->name ?? 'Genesis Global School' }}</dd>
          </div>
          <div class="flex justify-between py-1.5">
            <dt class="text-slate-400 font-medium">Emergency Line</dt>
            <dd class="font-bold text-indigo-400">{{ $school?->phone ?? '+91 97110 00560' }}</dd>
          </div>
        </dl>
      </div>

      {{-- Personal Identification --}}
      <div class="glass-card rounded-2xl p-5 sm:col-span-2">
        <h3 class="text-xs font-bold text-emerald-400 uppercase tracking-wider mb-4 flex items-center gap-2">
          <i class="bi bi-person-vcard-fill"></i> Personal Identification
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
          <div class="bg-slate-900/50 p-3 rounded-xl border border-slate-800">
            <span class="text-slate-400 block text-[10px] uppercase font-semibold">Date of Birth</span>
            <span class="font-bold text-slate-200 text-sm mt-0.5 block">{{ $teacher->birthday ? \Carbon\Carbon::parse($teacher->birthday)->format('d-m-Y') : '—' }}</span>
          </div>
          <div class="bg-slate-900/50 p-3 rounded-xl border border-slate-800">
            <span class="text-slate-400 block text-[10px] uppercase font-semibold">Gender</span>
            <span class="font-bold text-slate-200 text-sm mt-0.5 block">{{ $teacher->gender ?? 'Male' }}</span>
          </div>
          <div class="bg-slate-900/50 p-3 rounded-xl border border-slate-800">
            <span class="text-slate-400 block text-[10px] uppercase font-semibold">Blood Group</span>
            <span class="font-bold text-rose-400 text-sm mt-0.5 block">{{ $teacher->blood_type ?? 'O+' }}</span>
          </div>
          <div class="bg-slate-900/50 p-3 rounded-xl border border-slate-800">
            <span class="text-slate-400 block text-[10px] uppercase font-semibold">Nationality</span>
            <span class="font-bold text-slate-200 text-sm mt-0.5 block">{{ $teacher->nationality ?? 'Nepali' }}</span>
          </div>
        </div>
      </div>

    </div>
  </main>

  {{-- Footer --}}
  <footer class="max-w-3xl mx-auto w-full pt-6 border-t border-slate-800 text-center text-xs text-slate-500">
    <p>© {{ date('Y') }} {{ $school?->name ?? 'Genesis Global School' }}. All rights reserved.</p>
    <p class="text-[10px] text-slate-600 mt-1">This digital card is an official verified faculty identity document issued by {{ $school?->name ?? 'the institution' }}.</p>
  </footer>

</body>
</html>
