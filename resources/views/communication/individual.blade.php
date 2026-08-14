@extends('layouts.app')
@section('title', 'Individual WhatsApp Message')

@section('content')
@php $school = auth()->user()->school; @endphp

<div class="container-fluid px-4 py-4">

{{-- ── Alerts ── --}}
@if(session('status'))
<div class="mb-4 flex items-start gap-3 px-4 py-3 bg-emerald-50 border border-emerald-200
            text-emerald-800 text-sm rounded-2xl shadow-sm">
  <i class="bi bi-check-circle-fill text-emerald-500 text-lg flex-shrink-0 mt-0.5"></i>
  <div><strong class="font-bold">Sent!</strong> {{ session('status') }}</div>
</div>
@endif
@if(session('error'))
<div class="mb-4 flex items-start gap-3 px-4 py-3 bg-rose-50 border border-rose-200
            text-rose-800 text-sm rounded-2xl shadow-sm">
  <i class="bi bi-exclamation-triangle-fill text-rose-500 text-lg flex-shrink-0 mt-0.5"></i>
  <div><strong class="font-bold">Failed.</strong> {{ session('error') }}</div>
</div>
@endif
@if($errors->any())
<div class="mb-4 px-4 py-3 bg-rose-50 border border-rose-200 text-rose-800 text-sm rounded-2xl">
  <strong class="font-bold">Please fix:</strong>
  <ul class="mt-1 ps-4 list-disc">
    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
  </ul>
</div>
@endif

{{-- ── Page Header ── --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
  <div class="flex items-center gap-3">
    <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white text-xl shadow-md flex-shrink-0"
         style="background:linear-gradient(135deg,#25D366,#128C7E);">
      <i class="bi bi-whatsapp"></i>
    </div>
    <div>
      <h2 class="text-xl font-extrabold text-slate-800 mb-0">Send Individual WhatsApp</h2>
      <p class="text-sm text-slate-500 mb-0">Message one specific person directly via WhatsApp</p>
    </div>
  </div>
  <div class="flex items-center gap-2 flex-shrink-0">
    <a href="{{ route('communication.bulk') }}"
       class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200
              text-slate-700 text-xs font-semibold rounded-xl transition">
      <i class="bi bi-people-fill"></i> Bulk Broadcast
    </a>
    <a href="{{ route('communication.index') }}"
       class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200
              text-slate-700 text-xs font-semibold rounded-xl transition">
      <i class="bi bi-arrow-left"></i> Back to Hub
    </a>
  </div>
</div>

{{-- ── Main 2-column layout ── --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

{{-- ═══ LEFT: Form ═══ --}}
<div class="xl:col-span-2 space-y-5">

  {{-- Step 1 — Recipient ── --}}
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-100">
      <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white font-bold text-sm
                  flex items-center justify-center shadow shadow-indigo-500/25">1</div>
      <div>
        <h5 class="font-bold text-slate-800 mb-0 text-sm">Find Recipient</h5>
        <p class="text-xs text-slate-400 mb-0">Search by name, phone, or email</p>
      </div>
    </div>

    {{-- Search box --}}
    <div class="relative mb-3" id="search-wrapper">
      <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-slate-400">
        <i class="bi bi-search text-sm"></i>
      </div>
      <input type="text" id="person-search"
             class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm
                    text-slate-800 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500
                    outline-none transition"
             placeholder="Type name, phone or email…"
             autocomplete="off"
             value="{{ $selectedUser ? $selectedUser->full_name : '' }}">
      {{-- Spinner --}}
      <div id="search-spinner"
           class="hidden absolute inset-y-0 right-3 flex items-center">
        <svg class="animate-spin w-4 h-4 text-indigo-500"
             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10"
                  stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
        </svg>
      </div>
      {{-- Dropdown results --}}
      <div id="search-results"
           class="hidden absolute z-30 mt-1 w-full bg-white border border-slate-200
                  rounded-xl shadow-xl overflow-hidden text-sm">
      </div>
    </div>

    {{-- Selected person card --}}
    <div id="selected-card"
         class="{{ $selectedUser ? '' : 'hidden' }} flex items-center gap-3 p-3
                bg-emerald-50 border border-emerald-200 rounded-xl">
      <div class="w-10 h-10 rounded-full bg-emerald-600 text-white font-bold text-sm
                  flex items-center justify-center flex-shrink-0" id="card-avatar">
        {{ $selectedUser ? strtoupper(substr($selectedUser->full_name, 0, 2)) : '' }}
      </div>
      <div class="flex-1 min-w-0">
        <div class="font-bold text-slate-800 text-sm truncate" id="card-name">
          {{ $selectedUser?->full_name }}
        </div>
        <div class="text-xs text-slate-500 flex items-center gap-2 mt-0.5">
          <span id="card-phone">
            <i class="bi bi-telephone me-1 text-emerald-600"></i>
            {{ $selectedUser?->phone ?? '—' }}
          </span>
          <span class="text-slate-300">|</span>
          <span id="card-role"
                class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full font-semibold text-[10px] uppercase">
            {{ $selectedUser?->primary_role ?? $selectedUser?->role ?? '' }}
          </span>
        </div>
      </div>
      <button type="button" id="clear-selection"
              class="p-1 hover:bg-rose-100 text-slate-400 hover:text-rose-600
                     rounded-lg transition flex-shrink-0" title="Clear">
        <i class="bi bi-x-lg text-sm"></i>
      </button>
    </div>

  </div>{{-- /step 1 --}}

  {{-- Step 2 — Message ── --}}
  <form method="POST" action="{{ route('communication.individual.send') }}" id="send-form">
    @csrf
    <input type="hidden" name="user_id" id="hidden-user-id"
           value="{{ old('user_id', $selectedUser?->id) }}">

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white font-bold text-sm
                      flex items-center justify-center shadow shadow-indigo-500/25">2</div>
          <div>
            <h5 class="font-bold text-slate-800 mb-0 text-sm">Compose Message</h5>
            <p class="text-xs text-slate-400 mb-0">WhatsApp markdown supported (*bold*, _italic_)</p>
          </div>
        </div>
        {{-- Quick template dropdown --}}
        <div class="dropdown">
          <button class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100
                         hover:bg-slate-200 text-slate-700 text-xs font-semibold
                         rounded-lg transition" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-file-earmark-text text-indigo-500"></i> Templates
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow-lg rounded-xl border-0 p-2 text-sm">
            <li><a class="dropdown-item rounded-lg py-2 px-3 text-slate-700 cursor-pointer"
                   onclick="useTemplate('attendance')">📋 Absent Alert</a></li>
            <li><a class="dropdown-item rounded-lg py-2 px-3 text-slate-700 cursor-pointer"
                   onclick="useTemplate('fee')">💸 Fee Reminder</a></li>
            <li><a class="dropdown-item rounded-lg py-2 px-3 text-slate-700 cursor-pointer"
                   onclick="useTemplate('result')">📝 Result Ready</a></li>
            <li><a class="dropdown-item rounded-lg py-2 px-3 text-slate-700 cursor-pointer"
                   onclick="useTemplate('meeting')">🤝 Meeting Request</a></li>
            <li><a class="dropdown-item rounded-lg py-2 px-3 text-slate-700 cursor-pointer"
                   onclick="useTemplate('custom')">✏️ Blank</a></li>
          </ul>
        </div>
      </div>

      {{-- Variable chips --}}
      <div class="mb-3">
        <span class="text-xs font-semibold text-slate-400 mr-2">Insert:</span>
        @foreach(['{name}'=>'Name', '{school}'=>'School', '{date}'=>'Date', '{phone}'=>'Phone'] as $var => $label)
        <button type="button"
                onclick="insertVar('{{ $var }}')"
                class="inline-block mr-1.5 mb-1 bg-indigo-50 hover:bg-indigo-600 hover:text-white
                       text-indigo-700 border border-indigo-200 text-xs px-2.5 py-1
                       rounded-lg font-semibold transition">{{ $var }}</button>
        @endforeach
      </div>

      {{-- Textarea --}}
      <textarea name="message" id="message-body" rows="6"
                class="w-full rounded-xl border border-slate-200 p-3 text-sm text-slate-800
                       leading-relaxed focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500
                       outline-none resize-none transition"
                placeholder="Type your WhatsApp message here…"
                oninput="syncPreview()"
                required maxlength="1000">{{ old('message', '') }}</textarea>

      <div class="flex items-center justify-between mt-2 text-xs text-slate-400">
        <span><i class="bi bi-info-circle me-1"></i>
          School name header added automatically by the service.</span>
        <span><span id="char-count" class="font-bold text-slate-700">0</span> / 1000</span>
      </div>

      {{-- Send button --}}
      <div class="flex justify-end mt-4 pt-4 border-t border-slate-100">
        <button type="submit" id="send-btn"
                class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600
                       hover:bg-emerald-700 text-white text-sm font-bold rounded-xl
                       shadow-md shadow-emerald-600/25 transition disabled:opacity-50
                       disabled:cursor-not-allowed">
          <i class="bi bi-whatsapp"></i> Send WhatsApp
        </button>
      </div>
    </div>{{-- /step 2 card --}}
  </form>

</div>{{-- /left col --}}

{{-- ═══ RIGHT: Live preview + quota ═══ --}}
<div class="space-y-5">

  {{-- Phone mockup preview --}}
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sticky top-4">
    <div class="flex items-center justify-between mb-3">
      <h6 class="font-bold text-slate-800 text-sm mb-0">
        <i class="bi bi-phone me-1.5 text-indigo-500"></i> Live Preview
      </h6>
      <span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5
                   rounded-full uppercase tracking-wide">WhatsApp</span>
    </div>

    {{-- Mockup shell --}}
    <div class="rounded-2xl border-[6px] border-slate-800 overflow-hidden shadow-lg"
         style="background:#efeae2;
                background-image:radial-gradient(#d1d5db 0.75px,transparent 0.75px);
                background-size:12px 12px;">
      {{-- WA top bar --}}
      <div class="flex items-center gap-2 px-3 py-2 bg-emerald-700">
        <div class="w-7 h-7 rounded-full bg-white text-emerald-700 font-bold text-xs
                    flex items-center justify-center flex-shrink-0">
          {{ strtoupper(substr($school->name ?? 'NS', 0, 2)) }}
        </div>
        <div>
          <div class="text-xs font-semibold text-white leading-none truncate max-w-[150px]">
            {{ $school->name ?? 'School' }}
          </div>
          <div class="text-[9px] text-emerald-200">Official Alerts</div>
        </div>
      </div>
      {{-- Chat area --}}
      <div class="p-3 min-h-28">
        <div class="bg-white rounded-xl rounded-tl-none p-3 shadow-sm max-w-[85%] relative">
          <div class="absolute -top-0 -left-2 w-0 h-0
                      border-t-[10px] border-t-transparent
                      border-r-[10px] border-r-white
                      border-b-0"></div>
          <p class="text-[10px] font-bold text-emerald-700 mb-1 pb-1 border-b border-emerald-100">
            *[{{ $school->name ?? 'School' }}]*
          </p>
          <p id="preview-body" class="text-xs text-slate-700 leading-relaxed whitespace-pre-wrap break-words">
            Your message will appear here…
          </p>
          <div class="flex justify-end items-center gap-1 mt-1.5">
            <span class="text-[9px] text-slate-400">{{ now()->format('h:i A') }}</span>
            <i class="bi bi-check-all text-blue-500 text-sm"></i>
          </div>
        </div>
      </div>
    </div>{{-- /mockup --}}

    {{-- Recipient summary below mockup --}}
    <div id="preview-recipient"
         class="{{ $selectedUser ? '' : 'hidden' }} mt-3 flex items-center gap-2 px-3 py-2
                bg-slate-50 rounded-xl border border-slate-200 text-xs">
      <i class="bi bi-person-check-fill text-emerald-600 flex-shrink-0"></i>
      <span class="text-slate-600 font-medium">To:</span>
      <span id="preview-name" class="font-bold text-slate-800 truncate">
        {{ $selectedUser?->full_name }}
      </span>
      <span class="text-slate-300 mx-0.5">·</span>
      <span id="preview-phone" class="text-slate-500 font-mono">
        {{ $selectedUser?->phone }}
      </span>
    </div>

  </div>{{-- /preview card --}}

  {{-- Quota info --}}
  @php
    $quota    = $school?->whatsapp_quota ?? 0;
    $planName = ucfirst($school?->plan ?? 'free');
  @endphp
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
    <div class="flex items-center gap-3">
      <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600
                  flex items-center justify-center flex-shrink-0 text-lg">
        <i class="bi bi-chat-quote-fill"></i>
      </div>
      <div>
        <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">WhatsApp Quota</div>
        <div class="text-lg font-extrabold text-slate-800 leading-tight">
          {{ number_format($quota) }}
          <span class="text-xs font-medium text-slate-400">msgs remaining</span>
        </div>
        <div class="text-[10px] text-slate-400">{{ $planName }} plan</div>
      </div>
    </div>
    @if($quota <= 50)
    <div class="mt-3 flex items-center gap-2 text-xs text-amber-700 bg-amber-50
                border border-amber-200 rounded-lg px-3 py-2">
      <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
      Quota running low. Contact support to top up.
    </div>
    @endif
  </div>

</div>{{-- /right col --}}
</div>{{-- /grid --}}
</div>{{-- /container --}}


@push('scripts')
<script>
(function () {
  /* ── DOM refs ─────────────────────────────────────────────────── */
  const searchInput   = document.getElementById('person-search');
  const searchResults = document.getElementById('search-results');
  const spinner       = document.getElementById('search-spinner');
  const selectedCard  = document.getElementById('selected-card');
  const cardAvatar    = document.getElementById('card-avatar');
  const cardName      = document.getElementById('card-name');
  const cardPhone     = document.getElementById('card-phone');
  const cardRole      = document.getElementById('card-role');
  const clearBtn      = document.getElementById('clear-selection');
  const hiddenId      = document.getElementById('hidden-user-id');
  const messageArea   = document.getElementById('message-body');
  const previewBody   = document.getElementById('preview-body');
  const charCount     = document.getElementById('char-count');
  const sendBtn       = document.getElementById('send-btn');
  const prevRecipient = document.getElementById('preview-recipient');
  const prevName      = document.getElementById('preview-name');
  const prevPhone     = document.getElementById('preview-phone');

  /* ── Search autocomplete ─────────────────────────────────────── */
  let searchTimer = null;

  searchInput.addEventListener('input', () => {
    clearTimeout(searchTimer);
    const q = searchInput.value.trim();
    if (q.length < 2) { hideDropdown(); return; }
    searchTimer = setTimeout(() => fetchUsers(q), 280);
  });

  function fetchUsers(q) {
    spinner.classList.remove('hidden');
    fetch('{{ route("communication.individual.search") }}?q=' + encodeURIComponent(q), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(users => {
      spinner.classList.add('hidden');
      renderDropdown(users);
    })
    .catch(() => spinner.classList.add('hidden'));
  }

  function renderDropdown(users) {
    if (!users.length) {
      searchResults.innerHTML =
        `<div class="px-4 py-3 text-slate-400 text-xs text-center">
           No users found with a phone number.
         </div>`;
    } else {
      searchResults.innerHTML = users.map(u => `
        <button type="button"
                class="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-indigo-50
                       transition text-left border-b border-slate-100 last:border-0"
                data-id="${u.id}" data-name="${u.name}"
                data-phone="${u.phone}" data-role="${u.role}">
          <div class="w-8 h-8 rounded-full bg-indigo-600 text-white font-bold text-xs
                      flex items-center justify-center flex-shrink-0">
            ${u.name.substring(0,2).toUpperCase()}
          </div>
          <div class="flex-1 min-w-0">
            <div class="text-sm font-semibold text-slate-800 truncate">${u.name}</div>
            <div class="text-xs text-slate-400 flex items-center gap-2">
              <span class="font-mono">${u.phone}</span>
              <span class="bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded text-[10px]
                           font-semibold uppercase">${u.role}</span>
            </div>
          </div>
          <i class="bi bi-arrow-right text-slate-300 flex-shrink-0"></i>
        </button>`).join('');
    }
    searchResults.classList.remove('hidden');

    // Wire click events
    searchResults.querySelectorAll('button[data-id]').forEach(btn => {
      btn.addEventListener('click', () => selectUser(
        btn.dataset.id, btn.dataset.name, btn.dataset.phone, btn.dataset.role
      ));
    });
  }

  function selectUser(id, name, phone, role) {
    hiddenId.value          = id;
    cardAvatar.textContent  = name.substring(0,2).toUpperCase();
    cardName.textContent    = name;
    cardPhone.innerHTML     = `<i class="bi bi-telephone me-1 text-emerald-600"></i>${phone}`;
    cardRole.textContent    = role;
    prevName.textContent    = name;
    prevPhone.textContent   = phone;
    selectedCard.classList.remove('hidden');
    prevRecipient.classList.remove('hidden');
    searchInput.value       = name;
    hideDropdown();
    updateSendBtn();
  }

  function hideDropdown() {
    searchResults.classList.add('hidden');
    searchResults.innerHTML = '';
  }

  clearBtn.addEventListener('click', () => {
    hiddenId.value = '';
    searchInput.value = '';
    selectedCard.classList.add('hidden');
    prevRecipient.classList.add('hidden');
    hideDropdown();
    updateSendBtn();
  });

  /* Close dropdown on outside click */
  document.addEventListener('click', e => {
    if (!document.getElementById('search-wrapper').contains(e.target)) {
      hideDropdown();
    }
  });

  /* ── Live message preview ────────────────────────────────────── */
  function syncPreview() {
    const text = messageArea.value;
    charCount.textContent = text.length;
    previewBody.textContent = text || 'Your message will appear here…';
    updateSendBtn();
  }

  messageArea.addEventListener('input', syncPreview);
  syncPreview(); // initialise on page load

  /* ── Send button state ───────────────────────────────────────── */
  function updateSendBtn() {
    const ready = hiddenId.value && messageArea.value.trim().length >= 5;
    sendBtn.disabled = !ready;
  }

  /* ── Template helpers ────────────────────────────────────────── */
  window.useTemplate = function(type) {
    const templates = {
      attendance: `Dear parent,\n\nYour child was marked *absent* today, {date}. Please ensure timely attendance or inform the school if there is a valid reason.\n\nThank you.`,
      fee:        `Dear {name},\n\nThis is a gentle reminder that your *fee payment is due*. Kindly clear the outstanding amount at your earliest convenience to avoid a late fine.\n\nThank you.`,
      result:     `Dear {name},\n\nYour examination results are now available. Please log into the school portal to view your *result card*.\n\nBest regards.`,
      meeting:    `Dear {name},\n\nWe would like to schedule a *parent-teacher meeting* on {date}. Kindly confirm your availability at your earliest convenience.\n\nThank you.`,
      custom:     ``,
    };
    messageArea.value = templates[type] ?? '';
    syncPreview();
    messageArea.focus();
  };

  /* ── Variable insertion ──────────────────────────────────────── */
  window.insertVar = function(v) {
    const start = messageArea.selectionStart;
    const end   = messageArea.selectionEnd;
    messageArea.value = messageArea.value.slice(0, start) + v + messageArea.value.slice(end);
    messageArea.selectionStart = messageArea.selectionEnd = start + v.length;
    syncPreview();
    messageArea.focus();
  };

  /* ── Prevent submit without recipient ───────────────────────── */
  document.getElementById('send-form').addEventListener('submit', e => {
    if (!hiddenId.value) {
      e.preventDefault();
      searchInput.focus();
      searchInput.classList.add('ring-2', 'ring-rose-400', 'border-rose-400');
      setTimeout(() => searchInput.classList.remove('ring-2','ring-rose-400','border-rose-400'), 2000);
    }
  });

})();
</script>
@endpush

@endsection
