@extends('layouts.app')
@section('title', 'AI Homework Generator')

@section('content')
<style>
  .header-icon-box-purple {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%) !important;
    color: #ffffff !important;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
  }
  .header-icon-box-purple i {
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
                <div class="header-icon-box-purple">
                    <i class="bi bi-pencil-square"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">AI Homework & Worksheet Generator</h2>
                    <p class="text-slate-500 text-sm mb-0">Powered by Groq Llama 3.3 70B — Generate comprehensive worksheets & question papers in seconds</p>
                </div>
            </div>
        </div>

        <a href="{{ route('ai_features.web.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100 transition">
            <i class="bi bi-arrow-left me-1.5"></i> Back to AI Dashboard
        </a>
    </div>

    <div class="row g-4">
        <!-- Form Section -->
        <div class="col-12 col-xl-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
                <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-b border-slate-100">
                    <div class="w-9 h-9 rounded-xl bg-purple-600 text-white font-bold d-flex align-items-center justify-content-center shadow-md shadow-purple-500/20">
                        <i class="bi bi-sliders"></i>
                    </div>
                    <div>
                        <h5 class="font-bold text-slate-800 mb-0">Assignment Parameters</h5>
                        <p class="text-xs text-slate-400 mb-0">Configure topic and difficulty level</p>
                    </div>
                </div>

                <form id="aiHomeworkForm">
                    <div class="mb-3">
                        <label class="form-label font-semibold text-slate-700 text-sm">Topic / Subject <span class="text-rose-500">*</span></label>
                        <input type="text" id="topicInput" class="form-control rounded-xl border-slate-200 py-2.5 text-sm font-medium focus:ring-2 focus:ring-purple-500/20" placeholder="e.g., Photosynthesis, Linear Algebra, Newton's Laws..." required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label font-semibold text-slate-700 text-sm">Grade Level / Difficulty <span class="text-rose-500">*</span></label>
                        <select id="gradeInput" class="form-select rounded-xl border-slate-200 py-2.5 text-sm font-medium focus:ring-2 focus:ring-purple-500/20" required>
                            <option value="" disabled selected>Select Grade Level...</option>
                            <option value="Grade 1">Grade 1 (Primary)</option>
                            <option value="Grade 3">Grade 3</option>
                            <option value="Grade 5">Grade 5</option>
                            <option value="Grade 8">Grade 8 (Lower Secondary)</option>
                            <option value="Grade 10">Grade 10 (SEE Level)</option>
                            <option value="High School">Grade 11-12 (Higher Secondary)</option>
                        </select>
                    </div>

                    <!-- Preset Suggestions -->
                    <div class="mb-4">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider text-slate-400">Quick Subject Samples</label>
                        <div class="d-flex flex-wrap gap-1.5 mt-1">
                            <button type="button" class="btn btn-xs btn-light border border-slate-200 rounded-lg text-xs font-medium" onclick="setPreset('Simple Algebra Equations', 'Grade 5')">📐 Algebra</button>
                            <button type="button" class="btn btn-xs btn-light border border-slate-200 rounded-lg text-xs font-medium" onclick="setPreset('Photosynthesis & Plant Life Cycle', 'Grade 8')">🌿 Photosynthesis</button>
                            <button type="button" class="btn btn-xs btn-light border border-slate-200 rounded-lg text-xs font-medium" onclick="setPreset('English Grammar: Past Tense vs Present Perfect', 'Grade 5')">📖 English Grammar</button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-purple bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold w-100 py-3 rounded-xl shadow-md shadow-purple-500/25 transition" id="generateBtn">
                        <i class="bi bi-magic me-1.5"></i> Generate Homework
                    </button>
                </form>
            </div>
        </div>

        <!-- Result Output Section -->
        <div class="col-12 col-xl-8">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
                        <div class="d-flex align-items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 d-flex align-items-center justify-content-center flex-shrink-0">
                                <i class="bi bi-file-earmark-text"></i>
                            </span>
                            <h5 class="font-bold text-slate-800 mb-0">Generated Homework & Worksheet</h5>
                        </div>

                        <button class="btn btn-sm btn-light border border-slate-200 text-slate-700 font-semibold px-3 py-1.5 rounded-lg text-xs" onclick="copyToClipboard()" id="copyBtn" style="display: none;">
                            <i class="bi bi-clipboard me-1"></i> Copy Content
                        </button>
                    </div>

                    <!-- Loading State -->
                    <div id="loadingState" class="text-center py-5" style="display: none;">
                        <div class="spinner-border text-purple-600" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-slate-600 font-semibold text-sm">Groq Llama 3.3 AI is crafting questions & answer key... Please wait.</p>
                    </div>

                    <!-- Output Box -->
                    <div id="resultOutput" class="p-4 bg-slate-50 border border-slate-200/80 rounded-2xl text-slate-800 text-sm leading-relaxed" style="min-height: 300px; white-space: pre-wrap; display: none;"></div>

                    <!-- Empty Placeholder State -->
                    <div id="emptyState" class="text-center py-5 text-slate-400">
                        <i class="bi bi-file-earmark-code display-2 text-slate-200 mb-3 block"></i>
                        <p class="mb-0 text-sm font-medium">Enter a topic on the left and click <strong>Generate Homework</strong>!</p>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 text-xs text-slate-400 d-flex align-items-center justify-content-between">
                    <div><i class="bi bi-lightning-charge-fill text-amber-500 me-1"></i> Powered by Groq Llama-3.3-70b-versatile</div>
                    <div>NepalSchoolHub AI Engine</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function setPreset(topic, grade) {
    document.getElementById('topicInput').value = topic;
    document.getElementById('gradeInput').value = grade;
}

document.getElementById('aiHomeworkForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const topic = document.getElementById('topicInput').value;
    const grade = document.getElementById('gradeInput').value;
    const btn = document.getElementById('generateBtn');
    
    // UI State updates
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Generating...';
    document.getElementById('emptyState').style.display = 'none';
    document.getElementById('resultOutput').style.display = 'none';
    document.getElementById('copyBtn').style.display = 'none';
    document.getElementById('loadingState').style.display = 'block';

    try {
        const response = await fetch('{{ route("api.v1.ai_features.homework") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                topic: topic,
                grade_level: grade
            })
        });

        const data = await response.json();
        
        if (response.ok && (data.response || data.data)) {
            const outputText = data.response || data.data;
            document.getElementById('resultOutput').textContent = outputText;
            document.getElementById('resultOutput').style.display = 'block';
            document.getElementById('copyBtn').style.display = 'inline-block';
        } else {
            alert('Error generating homework: ' + (data.message || data.error || 'Unknown error'));
        }
    } catch(err) {
        alert('Failed to connect to AI Service. Please try again.');
        console.error(err);
    } finally {
        document.getElementById('loadingState').style.display = 'none';
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-magic me-1.5"></i> Generate Homework';
    }
});

function copyToClipboard() {
    const text = document.getElementById('resultOutput').textContent;
    navigator.clipboard.writeText(text).then(() => {
        const btn = document.getElementById('copyBtn');
        btn.innerHTML = '<i class="bi bi-check2 me-1"></i> Copied!';
        setTimeout(() => btn.innerHTML = '<i class="bi bi-clipboard me-1"></i> Copy Content', 2000);
    });
}
</script>
@endsection
