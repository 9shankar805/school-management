<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Verification — {{ $student->full_name }} | {{ $school?->name ?? config('app.name') }}</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #0f172a; color: #f8fafc; }
  .glass-card { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.1); }
  .badge-verified { background: linear-gradient(135deg, #059669, #10b981); box-shadow: 0 0 15px rgba(16, 185, 129, 0.4); }
</style>
</head>
<body class="min-h-screen flex flex-col justify-between p-4 sm:p-6 md:p-10 bg-slate-950 text-slate-100">

  {{-- Header --}}
  <header class="max-w-3xl mx-auto w-full flex items-center justify-between pb-6 border-b border-slate-800">
    <div class="flex items-center gap-3">
      @if($school?->logo)
        <img src="{{ asset('storage/' . ltrim($school->logo, '/')) }}" alt="Logo" class="w-10 h-10 rounded-full bg-white p-1 object-contain border border-slate-700">
      @else
        <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center font-bold text-white shadow-lg">
          {{ strtoupper(substr($school?->name ?? 'S', 0, 1)) }}
        </div>
      @endif
      <div>
        <h1 class="font-bold text-base sm:text-lg text-white leading-tight">{{ $school?->name ?? 'Genesis Global School' }}</h1>
        <p class="text-xs text-slate-400">Official Student Identity Portal</p>
      </div>
    </div>
    <div class="hidden sm:block">
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold badge-verified text-white">
        <i class="bi bi-patch-check-fill text-sm"></i> VERIFIED STUDENT
      </span>
    </div>
  </header>

  {{-- Main Content --}}
  <main class="max-w-3xl mx-auto w-full my-6 flex-1">
    
    {{-- Verified Banner (Mobile) --}}
    <div class="sm:hidden mb-4 text-center">
      <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-semibold badge-verified text-white shadow-lg">
        <i class="bi bi-patch-check-fill text-sm"></i> OFFICIAL VERIFIED STUDENT
      </span>
    </div>

    {{-- Main Profile Card --}}
    <div class="glass-card rounded-3xl p-6 sm:p-8 shadow-2xl relative overflow-hidden mb-6">
      <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

      <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
        {{-- Student Photo --}}
        <div class="relative group flex-shrink-0">
          @if($student->photo)
            <img src="{{ asset('storage/' . ltrim($student->photo, '/')) }}" alt="{{ $student->full_name }}" class="w-28 h-32 sm:w-32 sm:h-36 rounded-2xl object-cover border-2 border-blue-500/40 shadow-xl">
          @else
            <div class="w-28 h-32 sm:w-32 sm:h-36 rounded-2xl bg-slate-800 border-2 border-slate-700 flex items-center justify-center text-3xl font-bold text-slate-400">
              {{ strtoupper(substr($student->first_name, 0, 1)) }}
            </div>
          @endif
          <div class="absolute -bottom-2 right-2 bg-emerald-500 text-white rounded-full p-1 border-2 border-slate-900 flex items-center justify-center">
            <i class="bi bi-check-lg text-xs"></i>
          </div>
        </div>

        {{-- Student Overview --}}
        <div class="flex-1 text-center sm:text-left min-w-0">
          <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">{{ $student->full_name }}</h2>
          <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mt-2">
            <span class="px-3 py-1 bg-blue-500/10 text-blue-400 border border-blue-500/20 rounded-lg text-xs font-bold">
              Class: {{ $promotion?->schoolClass?->class_name ?? '—' }} {{ $promotion?->section?->section_name ? '('.$promotion->section->section_name.')' : '' }}
            </span>
            <span class="px-3 py-1 bg-slate-800 text-slate-300 border border-slate-700 rounded-lg text-xs font-mono font-medium">
              ID: {{ $promotion?->id_card_number ?? ('GGS-' . date('Y') . '-' . str_pad($student->id, 3, '0', STR_PAD_LEFT)) }}
            </span>
          </div>

          <p class="text-xs text-slate-400 mt-3 flex items-center justify-center sm:justify-start gap-2">
            <i class="bi bi-geo-alt-fill text-blue-400"></i>
            {{ $student->address ?? 'Siraha-7, Nepal' }}
          </p>

          {{-- Quick Action Buttons --}}
          <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 mt-5">
            <a href="{{ route('student.qr-login', $token) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold text-xs sm:text-sm rounded-xl shadow-lg shadow-blue-500/20 transition-all hover:scale-105">
              <i class="bi bi-box-arrow-in-right text-base"></i> Instant Login to Portal
            </a>
            <a href="{{ route('student.id-card', $student->id) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs sm:text-sm rounded-xl border border-slate-700 transition">
              <i class="bi bi-file-earmark-pdf"></i> Download PDF ID Card
            </a>
          </div>
        </div>
      </div>
    </div>

    {{-- Detailed Info Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      
      {{-- Academic Details --}}
      <div class="glass-card rounded-2xl p-5">
        <h3 class="text-xs font-bold text-blue-400 uppercase tracking-wider mb-4 flex items-center gap-2">
          <i class="bi bi-mortarboard-fill"></i> Academic Information
        </h3>
        <dl class="space-y-2.5 text-xs">
          <div class="flex justify-between py-1.5 border-b border-slate-800">
            <dt class="text-slate-400 font-medium">Class & Section</dt>
            <dd class="font-bold text-slate-200">{{ $promotion?->schoolClass?->class_name ?? '—' }} {{ $promotion?->section?->section_name ? '('.$promotion->section->section_name.')' : '' }}</dd>
          </div>
          <div class="flex justify-between py-1.5 border-b border-slate-800">
            <dt class="text-slate-400 font-medium">Roll Number</dt>
            <dd class="font-bold text-slate-200">{{ $promotion?->roll_no ?? $student->roll_no ?? '01' }}</dd>
          </div>
          <div class="flex justify-between py-1.5 border-b border-slate-800">
            <dt class="text-slate-400 font-medium">Student Status</dt>
            <dd class="font-bold text-emerald-400">ACTIVE ENROLLED</dd>
          </div>
          <div class="flex justify-between py-1.5">
            <dt class="text-slate-400 font-medium">Academic Session</dt>
            <dd class="font-bold text-slate-200">{{ $schoolSession?->session_name ?? (date('Y').'-'.(date('Y')+1)) }}</dd>
          </div>
        </dl>
      </div>

      {{-- Guardian & Parent Details --}}
      <div class="glass-card rounded-2xl p-5">
        <h3 class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-4 flex items-center gap-2">
          <i class="bi bi-people-fill"></i> Guardian Information
        </h3>
        <dl class="space-y-2.5 text-xs">
          <div class="flex justify-between py-1.5 border-b border-slate-800">
            <dt class="text-slate-400 font-medium">Father's Name</dt>
            <dd class="font-bold text-slate-200">{{ $fatherName }}</dd>
          </div>
          <div class="flex justify-between py-1.5 border-b border-slate-800">
            <dt class="text-slate-400 font-medium">Mother's Name</dt>
            <dd class="font-bold text-slate-200">{{ $motherName }}</dd>
          </div>
          <div class="flex justify-between py-1.5 border-b border-slate-800">
            <dt class="text-slate-400 font-medium">Contact Phone</dt>
            <dd class="font-bold text-slate-200">{{ $student->phone ?? $student->emergency_contact ?? '+91 97110 00560' }}</dd>
          </div>
          <div class="flex justify-between py-1.5">
            <dt class="text-slate-400 font-medium">Emergency Contact</dt>
            <dd class="font-bold text-blue-400">{{ $school?->phone ?? '+91 97110 00560' }}</dd>
          </div>
        </dl>
      </div>

      {{-- Personal Details --}}
      <div class="glass-card rounded-2xl p-5 sm:col-span-2">
        <h3 class="text-xs font-bold text-emerald-400 uppercase tracking-wider mb-4 flex items-center gap-2">
          <i class="bi bi-person-vcard-fill"></i> Personal Identification
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
          <div class="bg-slate-900/50 p-3 rounded-xl border border-slate-800">
            <span class="text-slate-400 block text-[10px] uppercase font-semibold">Date of Birth</span>
            <span class="font-bold text-slate-200 text-sm mt-0.5 block">{{ $student->birthday ? \Carbon\Carbon::parse($student->birthday)->format('d-m-Y') : '—' }}</span>
          </div>
          <div class="bg-slate-900/50 p-3 rounded-xl border border-slate-800">
            <span class="text-slate-400 block text-[10px] uppercase font-semibold">Gender</span>
            <span class="font-bold text-slate-200 text-sm mt-0.5 block">{{ $student->gender ?? 'Male' }}</span>
          </div>
          <div class="bg-slate-900/50 p-3 rounded-xl border border-slate-800">
            <span class="text-slate-400 block text-[10px] uppercase font-semibold">Blood Group</span>
            <span class="font-bold text-rose-400 text-sm mt-0.5 block">{{ $student->blood_type ?? 'A+' }}</span>
          </div>
          <div class="bg-slate-900/50 p-3 rounded-xl border border-slate-800">
            <span class="text-slate-400 block text-[10px] uppercase font-semibold">Nationality</span>
            <span class="font-bold text-slate-200 text-sm mt-0.5 block">{{ $student->nationality ?? 'Nepali' }}</span>
          </div>
        </div>
      </div>

    </div>
  </main>

  {{-- Footer --}}
  <footer class="max-w-3xl mx-auto w-full pt-6 border-t border-slate-800 text-center text-xs text-slate-500">
    <p>© {{ date('Y') }} {{ $school?->name ?? 'Genesis Global School' }}. All rights reserved.</p>
    <p class="text-[10px] text-slate-600 mt-1">This digital card is an official verified student identity document issued by {{ $school?->name ?? 'the institution' }}.</p>
  </footer>

</body>
</html>
