@extends('layouts.app')
@section('title', 'AI Notice Writer')

@section('content')
<style>
  .header-icon-box {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;
    color: #ffffff !important;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
  }
  .header-icon-box i {
    color: #ffffff !important;
    font-size: 22px !important;
    line-height: 1 !important;
    display: inline-block !important;
    opacity: 1 !important;
    visibility: visible !important;
  }
</style>

<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-3 mb-1">
                <div class="header-icon-box">
                    <i class="bi bi-envelope-paper"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">AI Notice Writer Assistant</h2>
                    <p class="text-slate-500 text-sm mb-0">Draft polished, professional school notices, circulars, and emails instantly using AI</p>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('notice.create') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100 transition">
                <i class="bi bi-megaphone me-1.5"></i> Manual Notice Form
            </a>
            <a href="{{ route('ai_features.web.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100 transition">
                <i class="bi bi-arrow-left me-1.5"></i> AI Dashboard
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Form Section -->
        <div class="col-12 col-xl-5">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
                <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-b border-slate-100">
                    <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white font-bold d-flex align-items-center justify-content-center shadow-md shadow-indigo-500/20">
                        <i class="bi bi-sliders"></i>
                    </div>
                    <div>
                        <h5 class="font-bold text-slate-800 mb-0">Notice Parameters</h5>
                        <p class="text-xs text-slate-400 mb-0">Specify what the AI should draft for you</p>
                    </div>
                </div>

                <form id="aiNoticeForm">
                    <div class="mb-3">
                        <label class="form-label font-semibold text-slate-700 text-sm">Subject / Topic <span class="text-rose-500">*</span></label>
                        <input type="text" id="subjectInput" class="form-control rounded-xl border-slate-200 py-2.5 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20" placeholder="e.g., Unexpected School Closure due to Heavy Rain..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-semibold text-slate-700 text-sm">Target Audience <span class="text-rose-500">*</span></label>
                        <select id="audienceInput" class="form-select rounded-xl border-slate-200 py-2.5 text-sm font-medium focus:ring-2 focus:ring-indigo-500/20" required>
                            <option value="" disabled selected>Select Audience...</option>
                            <option value="Parents">Parents</option>
                            <option value="Students">Students</option>
                            <option value="Teaching Staff">Teaching Staff</option>
                            <option value="All Staff">All Staff</option>
                            <option value="Everyone">Everyone (Parents, Students & Staff)</option>
                        </select>
                    </div>

                    <!-- Preset Topics Chips -->
                    <div class="mb-4">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider text-slate-400">Quick Prompt Presets</label>
                        <div class="d-flex flex-wrap gap-1.5 mt-1">
                            <button type="button" class="btn btn-xs btn-light border border-slate-200 rounded-lg text-xs font-medium" onclick="setPreset('Heavy Rain Emergency Closure', 'Parents')">☔ Emergency Rain Closure</button>
                            <button type="button" class="btn btn-xs btn-light border border-slate-200 rounded-lg text-xs font-medium" onclick="setPreset('Annual Sports Day Registration', 'Students')">🏆 Sports Day Registration</button>
                            <button type="button" class="btn btn-xs btn-light border border-slate-200 rounded-lg text-xs font-medium" onclick="setPreset('Parent-Teacher Meeting (PTM) Reminder', 'Parents')">👨‍👩‍👧 PTM Meeting Reminder</button>
                            <button type="button" class="btn btn-xs btn-light border border-slate-200 rounded-lg text-xs font-medium" onclick="setPreset('First Term Examination Schedule', 'Everyone')">📝 Exam Timetable Notice</button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-indigo bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-bold w-100 py-3 rounded-xl shadow-md shadow-indigo-500/25 transition" id="generateBtn">
                        <i class="bi bi-magic me-1.5"></i> Generate AI Draft
                    </button>
                </form>
            </div>
        </div>

        <!-- Result Output Section -->
        <div class="col-12 col-xl-7">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
                        <div class="d-flex align-items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 d-flex align-items-center justify-content-center flex-shrink-0">
                                <i class="bi bi-file-earmark-text"></i>
                            </span>
                            <h5 class="font-bold text-slate-800 mb-0">Generated Notice Draft</h5>
                        </div>

                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-light border border-slate-200 text-slate-700 font-semibold px-3 py-1.5 rounded-lg text-xs" onclick="copyToClipboard()" id="copyBtn" style="display: none;">
                                <i class="bi bi-clipboard me-1"></i> Copy Draft
                            </button>
                            <button type="button" class="btn btn-sm btn-emerald bg-emerald-600 text-white font-bold px-3 py-1.5 rounded-lg text-xs shadow-sm" id="waBtn" style="display: none;" onclick="sendOnWhatsApp()">
                                <i class="bi bi-whatsapp me-1"></i> Send on WhatsApp
                            </button>
                        </div>
                    </div>

                    <!-- Loading Spinner -->
                    <div id="loadingState" class="text-center py-5" style="display: none;">
                        <div class="spinner-border text-indigo-600" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-slate-600 font-semibold text-sm">AI is crafting your official notice... Please wait.</p>
                    </div>

                    <!-- Edit Helper Note -->
                    <div class="text-xs text-indigo-600 font-semibold mb-2" id="editNote" style="display:none;">
                        <i class="bi bi-pencil-square me-1"></i> You can edit this notice draft directly below to replace any dates or details before sending:
                    </div>

                    <!-- Output Box (Editable Textarea) -->
                    <textarea id="resultOutput" class="form-control p-4 bg-slate-50 border border-slate-200/80 rounded-2xl text-slate-800 text-sm leading-relaxed font-sans focus:ring-2 focus:ring-indigo-500/20" rows="12" style="display: none;"></textarea>

                    <!-- Empty Placeholder State -->
                    <div id="emptyState" class="text-center py-5 text-slate-400">
                        <i class="bi bi-chat-square-quote display-2 text-slate-200 mb-3 block"></i>
                        <p class="mb-0 text-sm font-medium">Select a topic on the left and click <strong>Generate AI Draft</strong> to create an instant notice!</p>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 text-xs text-slate-400 d-flex align-items-center justify-content-between">
                    <div><i class="bi bi-shield-check text-emerald-600 me-1"></i> Formatted specifically for Nepali schools</div>
                    <div>NepalSchoolHub AI Engine</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function setPreset(topic, aud) {
    document.getElementById('subjectInput').value = topic;
    document.getElementById('audienceInput').value = aud;
}

const SCHOOL_NAME = @json(auth()->user()->school?->name ?? 'NepalSchoolHub');
const USER_NAME = @json(auth()->user()->name ?? 'School Administration');

document.getElementById('aiNoticeForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const subject = document.getElementById('subjectInput').value;
    const audience = document.getElementById('audienceInput').value;
    const btn = document.getElementById('generateBtn');
    
    // UI State updates
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> AI Drafting...';
    document.getElementById('emptyState').style.display = 'none';
    document.getElementById('resultOutput').style.display = 'none';
    document.getElementById('editNote').style.display = 'none';
    document.getElementById('copyBtn').style.display = 'none';
    document.getElementById('waBtn').style.display = 'none';
    document.getElementById('loadingState').style.display = 'block';

    try {
        const response = await fetch('{{ route("api.v1.ai_features.notice") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                subject: subject,
                audience: audience
            })
        });

        const data = await response.json();
        
        if (response.ok && (data.response || data.data)) {
            let outputText = data.response || data.data;
            // Pre-fill placeholders automatically
            outputText = outputText.replace(/\[School Name\]/g, SCHOOL_NAME);
            outputText = outputText.replace(/\[Your Name\]/g, USER_NAME);

            document.getElementById('resultOutput').value = outputText;
            document.getElementById('resultOutput').style.display = 'block';
            document.getElementById('editNote').style.display = 'block';
            document.getElementById('copyBtn').style.display = 'inline-block';
            document.getElementById('waBtn').style.display = 'inline-block';
        } else {
            alert('Error generating notice: ' + (data.message || 'Unknown error'));
        }
    } catch(err) {
        alert('Failed to connect to AI Service. Please try again.');
        console.error(err);
    } finally {
        document.getElementById('loadingState').style.display = 'none';
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-magic me-1.5"></i> Generate AI Draft';
    }
});

function copyToClipboard() {
    const text = document.getElementById('resultOutput').value;
    navigator.clipboard.writeText(text).then(() => {
        const btn = document.getElementById('copyBtn');
        btn.innerHTML = '<i class="bi bi-check2 me-1"></i> Copied!';
        setTimeout(() => btn.innerHTML = '<i class="bi bi-clipboard me-1"></i> Copy Draft', 2000);
    });
}

function sendOnWhatsApp() {
    const text = document.getElementById('resultOutput').value;
    const subj = document.getElementById('subjectInput').value;
    sessionStorage.setItem('wa_draft_subject', subj);
    sessionStorage.setItem('wa_draft_body', text);
    window.location.href = "{{ route('communication.bulk') }}";
}
</script>
@endsection
