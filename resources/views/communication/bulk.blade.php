@extends('layouts.app')
@section('title', 'Bulk WhatsApp & Multi-Channel Announcements')

@section('content')
<style>
  .channel-card {
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    border: 2px solid #e2e8f0;
    cursor: pointer;
  }
  .channel-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
  }
  .channel-card.selected-wa {
    border-color: #25D366;
    background-color: #f0fdf4;
  }
  .channel-card.selected-wa .channel-icon {
    background: #25D366;
    color: white;
  }
  .channel-card.selected-default {
    border-color: #6366f1;
    background-color: #eef2ff;
  }
  .channel-card.selected-default .channel-icon {
    background: #6366f1;
    color: white;
  }
  .wa-phone-mockup {
    background: #efeae2;
    background-image: radial-gradient(#d1d5db 0.75px, transparent 0.75px);
    background-size: 12px 12px;
    border-radius: 20px;
    border: 8px solid #1e293b;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
  }
  .wa-chat-bubble {
    background: #ffffff;
    border-radius: 12px 12px 12px 2px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.06);
    position: relative;
  }
  .wa-chat-bubble::before {
    content: '';
    position: absolute;
    top: 0;
    left: -8px;
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 0 10px 10px 0;
    border-color: transparent #ffffff transparent transparent;
  }
  .variable-tag {
    cursor: pointer;
    transition: all 0.15s ease;
  }
  .variable-tag:hover {
    transform: scale(1.05);
    background-color: #4f46e5 !important;
    color: white !important;
  }
</style>

<div class="container-fluid px-4 py-4">
    <!-- Flash Status Alert -->
    @if(session('status'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4 bg-emerald-50 text-emerald-800 border-l-4 border-emerald-500" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill fs-4 me-3 text-emerald-600"></i>
            <div>
                <strong class="font-semibold">Success!</strong>
                <p class="mb-0 text-sm mt-0.5">{{ session('status') }}</p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger rounded-3 shadow-sm border-0 mb-4 bg-rose-50 text-rose-800 border-l-4 border-rose-500">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-rose-600"></i>
            <div>
                <strong class="font-semibold">Please fix the following issues:</strong>
                <ul class="mb-0 ps-3 text-sm mt-1">
                    @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <!-- Header & Quota Bar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-3 mb-1">
                <div class="w-12 h-12 rounded-2xl text-white d-inline-flex align-items-center justify-content-center flex-shrink-0 shadow-md fs-4" style="background: linear-gradient(135deg, #10b981 0%, #0d9488 100%); color: #ffffff;">
                    <i class="bi bi-whatsapp"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Bulk & WhatsApp Alerts</h2>
                    <p class="text-slate-500 text-sm mb-0">Broadcast instant multi-channel messages across your entire school community</p>
                </div>
            </div>
        </div>
        
        <div class="d-flex align-items-center gap-3 w-100 w-md-auto">
            @php
                $school = auth()->user()->school;
                $quota = $school->whatsapp_quota ?? 5000;
                $planName = ucfirst($school->plan ?? 'Yearly');
            @endphp
            <div class="bg-white p-3 rounded-2xl border border-slate-200 shadow-sm d-flex align-items-center gap-3 w-100">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 d-flex align-items-center justify-content-center flex-shrink-0">
                    <i class="bi bi-chat-quote-fill fs-5"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">WhatsApp Quota</span>
                        <span class="badge bg-emerald-100 text-emerald-700 text-[10px] px-2 py-0.5 rounded-full font-semibold">{{ $planName }} Plan</span>
                    </div>
                    <div class="h5 font-extrabold text-slate-800 mb-0 mt-0.5">
                        {{ number_format($quota) }} <span class="text-xs font-medium text-slate-400">msgs remaining</span>
                    </div>
                </div>
            </div>
            
            <a href="{{ route('communication.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl flex-shrink-0 shadow-sm hover:bg-slate-100 transition">
                <i class="bi bi-arrow-left me-1"></i> Back to Hub
            </a>
            <a href="{{ route('communication.individual') }}" class="btn border border-green-300 font-semibold px-4 py-2.5 rounded-xl flex-shrink-0 shadow-sm transition"
               style="background:#f0fdf4;color:#16a34a;">
                <i class="bi bi-person-lines-fill me-1"></i> Direct Message
            </a>
        </div>
    </div>

    <!-- Main Layout -->
    <form method="POST" action="{{ route('communication.bulk.send') }}" id="bulkForm">
        @csrf
        <div class="row g-4">
            <!-- Left Column: Form Controls -->
            <div class="col-12 col-xl-8">
                
                <!-- 1. Target Audience -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5 mb-4">
                    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-b border-slate-100">
                        <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white font-bold d-flex align-items-center justify-content-center shadow-md shadow-indigo-500/20">1</div>
                        <div>
                            <h5 class="font-bold text-slate-800 mb-0">Select Target Audience</h5>
                            <p class="text-xs text-slate-400 mb-0">Choose who will receive this notification</p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-sm">Audience Group <span class="text-rose-500">*</span></label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="p-3 border rounded-xl d-flex align-items-center gap-2 cursor-pointer hover:bg-slate-50 transition border-slate-200">
                                    <input type="checkbox" name="audience[]" value="parents" class="form-check-input mt-0 accent-indigo-600" checked>
                                    <span class="text-sm font-semibold text-slate-700"><i class="bi bi-people me-1 text-indigo-500"></i> Parents</span>
                                </label>
                                <label class="p-3 border rounded-xl d-flex align-items-center gap-2 cursor-pointer hover:bg-slate-50 transition border-slate-200">
                                    <input type="checkbox" name="audience[]" value="students" class="form-check-input mt-0 accent-indigo-600">
                                    <span class="text-sm font-semibold text-slate-700"><i class="bi bi-mortarboard me-1 text-blue-500"></i> Students</span>
                                </label>
                                <label class="p-3 border rounded-xl d-flex align-items-center gap-2 cursor-pointer hover:bg-slate-50 transition border-slate-200">
                                    <input type="checkbox" name="audience[]" value="teachers" class="form-check-input mt-0 accent-indigo-600">
                                    <span class="text-sm font-semibold text-slate-700"><i class="bi bi-person-badge me-1 text-emerald-500"></i> Teachers</span>
                                </label>
                                <label class="p-3 border rounded-xl d-flex align-items-center gap-2 cursor-pointer hover:bg-slate-50 transition border-slate-200">
                                    <input type="checkbox" name="audience[]" value="staff" class="form-check-input mt-0 accent-indigo-600">
                                    <span class="text-sm font-semibold text-slate-700"><i class="bi bi-briefcase me-1 text-amber-500"></i> Staff</span>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label font-semibold text-slate-700 text-sm">Filter Class <span class="text-xs text-slate-400 font-normal">(Optional)</span></label>
                            <select class="form-select rounded-xl border-slate-200 py-2.5 text-sm text-slate-700 focus:ring-2 focus:ring-indigo-500/20">
                                <option value="">-- All Classes --</option>
                                <option>Grade 1</option>
                                <option>Grade 2</option>
                                <option>Grade 3</option>
                                <option>Grade 4</option>
                                <option>Grade 5</option>
                                <option>Grade 6</option>
                                <option>Grade 7</option>
                                <option>Grade 8</option>
                                <option>Grade 9</option>
                                <option>Grade 10</option>
                                <option>Grade 11</option>
                                <option>Grade 12</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label font-semibold text-slate-700 text-sm">Filter Section <span class="text-xs text-slate-400 font-normal">(Optional)</span></label>
                            <select class="form-select rounded-xl border-slate-200 py-2.5 text-sm text-slate-700 focus:ring-2 focus:ring-indigo-500/20">
                                <option value="">-- All Sections --</option>
                                <option>Section A</option>
                                <option>Section B</option>
                                <option>Section C</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 2. Delivery Channels -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5 mb-4">
                    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-b border-slate-100">
                        <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white font-bold d-flex align-items-center justify-content-center shadow-md shadow-indigo-500/20">2</div>
                        <div>
                            <h5 class="font-bold text-slate-800 mb-0">Delivery Channels</h5>
                            <p class="text-xs text-slate-400 mb-0">Select one or multiple delivery networks</p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <!-- WhatsApp Card -->
                        <div class="col-md-6 col-lg-3">
                            <label class="channel-card selected-wa p-3 rounded-2xl h-100 d-flex flex-column justify-content-between relative" id="card_wa">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="channel-icon w-10 h-10 rounded-xl bg-emerald-500 text-white d-flex align-items-center justify-content-center fs-5">
                                        <i class="bi bi-whatsapp"></i>
                                    </div>
                                    <input type="checkbox" name="channels[]" value="whatsapp" id="chk_wa" class="form-check-input fs-5 accent-emerald-500" checked onchange="toggleChannelUI(this, 'card_wa', 'selected-wa')">
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800 text-sm">WhatsApp</div>
                                    <div class="text-[11px] text-emerald-700 font-medium mt-0.5"><i class="bi bi-shield-check"></i> Meta Official API</div>
                                </div>
                            </label>
                        </div>

                        <!-- In-App Card -->
                        <div class="col-md-6 col-lg-3">
                            <label class="channel-card selected-default p-3 rounded-2xl h-100 d-flex flex-column justify-content-between" id="card_inapp">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="channel-icon w-10 h-10 rounded-xl bg-indigo-600 text-white d-flex align-items-center justify-content-center fs-5">
                                        <i class="bi bi-bell-fill"></i>
                                    </div>
                                    <input type="checkbox" name="channels[]" value="in_app" id="chk_inapp" class="form-check-input fs-5 accent-indigo-600" checked onchange="toggleChannelUI(this, 'card_inapp', 'selected-default')">
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800 text-sm">In-App Notice</div>
                                    <div class="text-[11px] text-indigo-600 font-medium mt-0.5">Parent & Student Portal</div>
                                </div>
                            </label>
                        </div>

                        <!-- SMS Card -->
                        <div class="col-md-6 col-lg-3">
                            <label class="channel-card p-3 rounded-2xl h-100 d-flex flex-column justify-content-between" id="card_sms">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="channel-icon w-10 h-10 rounded-xl bg-slate-200 text-slate-600 d-flex align-items-center justify-content-center fs-5">
                                        <i class="bi bi-chat-square-text-fill"></i>
                                    </div>
                                    <input type="checkbox" name="channels[]" value="sms" id="chk_sms" class="form-check-input fs-5 accent-indigo-600" onchange="toggleChannelUI(this, 'card_sms', 'selected-default')">
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800 text-sm">SMS Text</div>
                                    <div class="text-[11px] text-slate-500 font-medium mt-0.5">NTC / Ncell Gateway</div>
                                </div>
                            </label>
                        </div>

                        <!-- Push Card -->
                        <div class="col-md-6 col-lg-3">
                            <label class="channel-card p-3 rounded-2xl h-100 d-flex flex-column justify-content-between" id="card_push">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="channel-icon w-10 h-10 rounded-xl bg-slate-200 text-slate-600 d-flex align-items-center justify-content-center fs-5">
                                        <i class="bi bi-phone-vibrate-fill"></i>
                                    </div>
                                    <input type="checkbox" name="channels[]" value="push" id="chk_push" class="form-check-input fs-5 accent-indigo-600" onchange="toggleChannelUI(this, 'card_push', 'selected-default')">
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800 text-sm">Push Mobile</div>
                                    <div class="text-[11px] text-slate-500 font-medium mt-0.5">Mobile App Alert</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 3. Message Content -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
                    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
                        <div class="d-flex align-items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white font-bold d-flex align-items-center justify-content-center shadow-md shadow-indigo-500/20">3</div>
                            <div>
                                <h5 class="font-bold text-slate-800 mb-0">Message Content</h5>
                                <p class="text-xs text-slate-400 mb-0">Write your announcement body and title</p>
                            </div>
                        </div>
                        
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light border border-slate-200 text-slate-700 font-semibold px-3 py-1.5 rounded-lg text-xs dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-file-earmark-text text-indigo-600 me-1"></i> Quick Templates
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg rounded-xl border-0 p-2 text-sm">
                                <li><a class="dropdown-menu-item p-2 rounded-lg hover:bg-slate-50 cursor-pointer block text-slate-700" onclick="loadTemplate('holiday')">🎉 School Holiday Notice</a></li>
                                <li><a class="dropdown-menu-item p-2 rounded-lg hover:bg-slate-50 cursor-pointer block text-slate-700" onclick="loadTemplate('exam')">📝 Exam Schedule Announcement</a></li>
                                <li><a class="dropdown-menu-item p-2 rounded-lg hover:bg-slate-50 cursor-pointer block text-slate-700" onclick="loadTemplate('fee')">💸 Fee Due Reminder</a></li>
                                <li><a class="dropdown-menu-item p-2 rounded-lg hover:bg-slate-50 cursor-pointer block text-slate-700" onclick="loadTemplate('meeting')">👨‍👩‍👧 Parent-Teacher Meeting (PTM)</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label font-semibold text-slate-700 text-sm">Subject / Title <span class="text-rose-500">*</span></label>
                        <input type="text" name="subject" id="msgSubject" value="Important School Notice" class="form-control rounded-xl border-slate-200 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-indigo-500/20" placeholder="e.g. Upcoming Parent Teacher Meeting Notice" required oninput="updateLivePreview()">
                    </div>

                    <!-- Dynamic Variables Bar -->
                    <div class="mb-3">
                        <span class="text-xs font-semibold text-slate-500 me-2">Click to insert dynamic fields:</span>
                        <div class="d-inline-flex flex-wrap gap-1.5 mt-1">
                            <span class="variable-tag bg-indigo-50 text-indigo-700 text-xs px-2.5 py-1 rounded-lg border border-indigo-200/60 font-semibold" onclick="insertVariable('{student_name}')">{student_name}</span>
                            <span class="variable-tag bg-indigo-50 text-indigo-700 text-xs px-2.5 py-1 rounded-lg border border-indigo-200/60 font-semibold" onclick="insertVariable('{parent_name}')">{parent_name}</span>
                            <span class="variable-tag bg-indigo-50 text-indigo-700 text-xs px-2.5 py-1 rounded-lg border border-indigo-200/60 font-semibold" onclick="insertVariable('{date}')">{date}</span>
                            <span class="variable-tag bg-indigo-50 text-indigo-700 text-xs px-2.5 py-1 rounded-lg border border-indigo-200/60 font-semibold" onclick="insertVariable('{school_name}')">{school_name}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-semibold text-slate-700 text-sm">Message Body <span class="text-rose-500">*</span></label>
                        <textarea name="message" id="msgBody" rows="5" class="form-control rounded-xl border-slate-200 text-sm text-slate-800 p-3 leading-relaxed focus:ring-2 focus:ring-indigo-500/20" placeholder="Type your message here..." required oninput="updateLivePreview()">Dear parents, please note that the school will remain closed tomorrow on account of a public holiday. Classes will resume as scheduled on the next working day.</textarea>
                    </div>

                    <div class="d-flex align-items-center justify-content-between text-xs text-slate-400">
                        <div><i class="bi bi-info-circle me-1"></i> WhatsApp messages will automatically be prefixed with <strong>*[{{ $school->name ?? 'School Name' }}]*</strong></div>
                        <div><span id="charCount" class="font-bold text-slate-700">165</span> / 1000 chars</div>
                    </div>

                    <hr class="my-4 border-slate-100">

                    <div class="d-flex justify-content-end gap-3">
                        <button type="reset" class="btn btn-light border border-slate-200 text-slate-600 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100">Reset</button>
                        <button type="submit" class="btn btn-indigo bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-bold px-6 py-2.5 rounded-xl shadow-md shadow-indigo-500/25 transition">
                            <i class="bi bi-send-fill me-1.5"></i> Broadcast Announcement
                        </button>
                    </div>
                </div>

            </div>

            <!-- Right Column: Live Phone Preview -->
            <div class="col-12 col-xl-4">
                <div class="sticky-top" style="top: 2rem;">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="font-bold text-slate-800 mb-0"><i class="bi bi-phone me-1.5 text-indigo-600"></i> Live WhatsApp Preview</h6>
                            <span class="badge bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded-full"><i class="bi bi-circle-fill text-[8px] me-1 text-emerald-500"></i> Parent View</span>
                        </div>
                        
                        <!-- Phone Mockup Container -->
                        <div class="wa-phone-mockup p-3">
                            <!-- WhatsApp Header -->
                            <div class="bg-emerald-700 text-white p-2.5 rounded-t-xl d-flex align-items-center gap-2 shadow-sm mb-3">
                                <div class="w-7 h-7 rounded-full bg-white text-emerald-700 font-bold text-xs d-flex align-items-center justify-content-center flex-shrink-0">
                                    {{ strtoupper(substr($school->name ?? 'NS', 0, 2)) }}
                                </div>
                                <div class="overflow-hidden">
                                    <div class="font-semibold text-xs text-white truncate">{{ $school->name ?? 'NepalSchoolHub' }}</div>
                                    <div class="text-[9px] text-emerald-100">Official School Alerts</div>
                                </div>
                            </div>

                            <!-- Chat Message Bubble -->
                            <div class="wa-chat-bubble p-3 mb-2 max-w-[90%] ms-2">
                                <div class="text-[11px] font-bold text-emerald-800 mb-1 border-b border-emerald-100 pb-1" id="previewHeader">
                                    *[{{ $school->name ?? 'NepalSchoolHub' }}]*
                                </div>
                                <div class="text-xs font-bold text-slate-900 mb-1" id="previewSubject">
                                    Important School Notice
                                </div>
                                <div class="text-xs text-slate-700 leading-normal" id="previewBody">
                                    Dear parents, please note that the school will remain closed tomorrow on account of a public holiday.
                                </div>
                                <div class="d-flex justify-content-end align-items-center gap-1 mt-2 text-[9px] text-slate-400">
                                    <span id="previewTime">{{ now()->format('h:i A') }}</span>
                                    <i class="bi bi-check-all text-blue-500 fs-6"></i>
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-200/80 mt-3 text-xs text-slate-500">
                            <div class="font-semibold text-slate-700 mb-1"><i class="bi bi-shield-check text-emerald-600 me-1"></i> Delivery Guarantee</div>
                            WhatsApp messages are delivered instantly with 99.8% open rate across NTC and Ncell mobile networks in Nepal.
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

<script>
function toggleChannelUI(checkbox, cardId, selectedClass) {
    const card = document.getElementById(cardId);
    if (checkbox.checked) {
        card.classList.add(selectedClass);
        if (cardId === 'card_wa') {
            card.querySelector('.channel-icon').className = 'channel-icon w-10 h-10 rounded-xl bg-emerald-500 text-white d-flex align-items-center justify-content-center fs-5';
        } else {
            card.querySelector('.channel-icon').className = 'channel-icon w-10 h-10 rounded-xl bg-indigo-600 text-white d-flex align-items-center justify-content-center fs-5';
        }
    } else {
        card.classList.remove(selectedClass, 'selected-wa', 'selected-default');
        card.querySelector('.channel-icon').className = 'channel-icon w-10 h-10 rounded-xl bg-slate-200 text-slate-600 d-flex align-items-center justify-content-center fs-5';
    }
}

function updateLivePreview() {
    const subj = document.getElementById('msgSubject').value;
    const body = document.getElementById('msgBody').value;
    
    document.getElementById('previewSubject').innerText = subj || 'Announcement Subject';
    document.getElementById('previewBody').innerText = body || 'Message text body...';
    document.getElementById('charCount').innerText = body.length;
}

function insertVariable(varName) {
    const body = document.getElementById('msgBody');
    const start = body.selectionStart;
    const end = body.selectionEnd;
    const text = body.value;
    body.value = text.substring(0, start) + varName + text.substring(end);
    body.focus();
    body.selectionStart = body.selectionEnd = start + varName.length;
    updateLivePreview();
}

function loadTemplate(type) {
    const subj = document.getElementById('msgSubject');
    const body = document.getElementById('msgBody');
    
    if (type === 'holiday') {
        subj.value = "🎉 Public Holiday Notice";
        body.value = "Dear parents & students, please note that the school will remain closed tomorrow on account of a public holiday. Normal classes will resume on the next working day.";
    } else if (type === 'exam') {
        subj.value = "📝 Term Examination Schedule Released";
        body.value = "Dear parents, the terminal examination schedule for {student_name} has been published. Please log into the parent portal to view the detailed timetable.";
    } else if (type === 'fee') {
        subj.value = "💸 Fee Due Reminder";
        body.value = "Dear parent, this is a gentle reminder that the tuition fee invoice for {student_name} is due soon. Kindly clear the outstanding amount at your earliest convenience.";
    } else if (type === 'meeting') {
        subj.value = "👨‍👩‍👧 Parent-Teacher Meeting (PTM) Invitation";
        body.value = "Dear parents, you are cordially invited to the upcoming Parent-Teacher Meeting (PTM) on {date} at 10:00 AM. Your presence is highly appreciated.";
    }
    updateLivePreview();
}

document.addEventListener('DOMContentLoaded', function() {
    if (sessionStorage.getItem('wa_draft_body')) {
        document.getElementById('msgBody').value = sessionStorage.getItem('wa_draft_body');
        if (sessionStorage.getItem('wa_draft_subject')) {
            document.getElementById('msgSubject').value = sessionStorage.getItem('wa_draft_subject');
        }
        sessionStorage.removeItem('wa_draft_body');
        sessionStorage.removeItem('wa_draft_subject');
    }
    updateLivePreview();
});
</script>
@endsection
