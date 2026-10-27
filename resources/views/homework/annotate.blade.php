@extends('layouts.app')
@section('title', 'Annotate & Grade Submission')

@section('content')
<style>
  .tool-btn {
    width: 38px;
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #475569;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.15s ease;
  }
  .tool-btn:hover { background: #f1f5f9; color: #1e293b; }
  .tool-btn.active { background: #4f46e5; color: #ffffff; border-color: #4338ca; box-shadow: 0 2px 8px rgba(79, 70, 229, 0.3); }
  #canvas-container {
    position: relative;
    overflow: auto;
    max-height: calc(100vh - 200px);
    background: #475569;
    border-radius: 16px;
    display: flex;
    justify-content: center;
    padding: 20px;
  }
  #annotation-canvas {
    background: #ffffff;
    box-shadow: 0 10px 30px rgba(0,0,0,0.25);
    cursor: crosshair;
    display: block;
  }
</style>

<div class="container-fluid px-4 py-3">
    <!-- Top Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('homework.show', $submission->homework_id) }}" class="btn btn-light border border-slate-200 text-slate-700 rounded-xl px-3 py-2">
                <i class="bi bi-arrow-left me-1"></i> Back to Submissions
            </a>
            <div>
                <h4 class="font-bold text-slate-800 mb-0">Teacher Grading & Document Annotation</h4>
                <p class="text-xs text-slate-500 mb-0">
                    {{ $submission->homework->title }} · Student: <strong>{{ $submission->student->full_name }}</strong>
                </p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="button" id="save-annotation-btn" class="btn btn-primary bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-xl shadow-md shadow-indigo-500/20 transition">
                <i class="bi bi-check2-circle me-1.5"></i> Save & Publish Evaluation
            </button>
        </div>
    </div>

    <!-- Main Workspace -->
    <div class="row g-3">
        <!-- Left: Canvas & Drawing Controls -->
        <div class="col-12 col-xl-9">
            <!-- Toolbar -->
            <div class="bg-white rounded-2xl border border-slate-200 p-2.5 shadow-sm mb-3 d-flex flex-wrap items-center justify-between gap-2">
                <div class="d-flex flex-wrap align-items-center gap-1.5">
                    <button type="button" class="tool-btn active" data-tool="pen" data-color="#dc2626" title="Red Pen (Teacher Mark)">
                        <i class="bi bi-pencil-fill text-danger"></i>
                    </button>
                    <button type="button" class="tool-btn" data-tool="pen" data-color="#16a34a" title="Green Pen (Correct)">
                        <i class="bi bi-pencil-fill text-success"></i>
                    </button>
                    <button type="button" class="tool-btn" data-tool="highlighter" data-color="rgba(250, 204, 21, 0.45)" title="Highlighter">
                        <i class="bi bi-highlighter text-warning"></i>
                    </button>
                    <div class="vr mx-1"></div>
                    <button type="button" class="tool-btn" data-tool="stamp" data-stamp="✔" title="Checkmark Stamp (Correct)">
                        <span class="font-bold text-success">✔</span>
                    </button>
                    <button type="button" class="tool-btn" data-tool="stamp" data-stamp="✘" title="Cross Stamp (Incorrect)">
                        <span class="font-bold text-danger">✘</span>
                    </button>
                    <button type="button" class="tool-btn" data-tool="text" title="Add Text Comment">
                        <i class="bi bi-fonts"></i>
                    </button>
                    <div class="vr mx-1"></div>
                    <button type="button" class="tool-btn" data-tool="eraser" title="Eraser">
                        <i class="bi bi-eraser-fill"></i>
                    </button>
                    <button type="button" id="clear-btn" class="tool-btn text-danger" title="Clear All Markings">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <label class="text-xs font-semibold text-slate-500 mb-0">Pen Size:</label>
                        <input type="range" id="brush-size" min="2" max="24" value="4" class="form-range" style="width: 90px;">
                        <span id="brush-size-val" class="text-xs font-mono font-bold text-slate-700">4px</span>
                    </div>
                </div>
            </div>

            <!-- Canvas Container -->
            <div id="canvas-container">
                <canvas id="annotation-canvas"></canvas>
            </div>
        </div>

        <!-- Right: Student Submission & Grading Form -->
        <div class="col-12 col-xl-3">
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm space-y-4">
                <div class="pb-3 border-b border-slate-100">
                    <h6 class="font-bold text-slate-800 text-sm mb-1">Student Details</h6>
                    <div class="d-flex align-items-center gap-2.5 mt-2">
                        <img src="{{ $submission->student->avatar }}" class="w-10 h-10 rounded-full object-cover border border-slate-200" alt="">
                        <div>
                            <div class="font-bold text-sm text-slate-800">{{ $submission->student->full_name }}</div>
                            <div class="text-xs text-slate-500">Submitted: {{ $submission->submitted_at?->format('M d, Y · h:i A') }}</div>
                        </div>
                    </div>
                </div>

                @if($submission->remarks)
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs">
                    <span class="font-semibold text-slate-700">Student Remarks:</span>
                    <p class="text-slate-600 mb-0 mt-1 italic">"{{ $submission->remarks }}"</p>
                </div>
                @endif

                <div class="pb-3 border-b border-slate-100">
                    <a href="{{ asset('storage/' . $submission->file_path) }}" target="_blank" class="btn btn-light border border-slate-200 text-slate-700 text-xs w-100 py-2 rounded-xl font-semibold">
                        <i class="bi bi-download me-1"></i> Download Original File
                    </a>
                </div>

                <!-- Grading Inputs -->
                <form id="grading-form">
                    <div class="mb-3">
                        <label class="form-label font-semibold text-slate-700 text-xs">
                            Score / Marks Obtained <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" id="marks-input" name="marks_obtained" value="{{ $submission->marks_obtained }}" min="0" max="{{ $submission->homework->total_marks }}" class="form-control rounded-start-xl border-slate-200 font-bold text-slate-800 text-sm" placeholder="e.g. 18" required>
                            <span class="input-group-text bg-slate-100 border-slate-200 font-bold text-slate-600 text-xs rounded-end-xl">
                                / {{ $submission->homework->total_marks }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-semibold text-slate-700 text-xs">Teacher Written Feedback</label>
                        <textarea id="feedback-input" name="teacher_feedback" rows="4" class="form-control rounded-xl border-slate-200 text-xs" placeholder="e.g. Excellent presentation! Please check question 3 formula steps corrections in red pen...">{{ $submission->teacher_feedback }}</textarea>
                    </div>

                    <div id="save-status" class="alert alert-success d-none text-xs rounded-xl py-2 px-3 mb-2"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('annotation-canvas');
    const ctx = canvas.getContext('2d');
    const originalFileUrl = "{{ asset('storage/' . ($submission->annotated_file_path ?: $submission->file_path)) }}";

    let currentTool = 'pen';
    let currentColor = '#dc2626';
    let currentSize = 4;
    let currentStamp = '✔';
    let isDrawing = false;
    let startX = 0, startY = 0;
    const history = [];

    // Load original image into canvas
    const img = new Image();
    img.crossOrigin = "anonymous";
    img.src = originalFileUrl;
    img.onload = () => {
        // Fit within reasonable dimensions
        const maxW = 1000;
        let scale = 1;
        if (img.width > maxW) scale = maxW / img.width;

        canvas.width = img.width * scale;
        canvas.height = img.height * scale;
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
        saveHistory();
    };

    img.onerror = () => {
        // Fallback placeholder if PDF or non-image format
        canvas.width = 800;
        canvas.height = 1050;
        ctx.fillStyle = '#f8fafc';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#475569';
        ctx.font = 'bold 18px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('Document Submission: {{ basename($submission->file_path) }}', 400, 480);
        ctx.font = '14px sans-serif';
        ctx.fillStyle = '#64748b';
        ctx.fillText('Click to draw annotation notes directly on this review sheet', 400, 520);
        saveHistory();
    };

    function saveHistory() {
        if (history.length > 20) history.shift();
        history.push(ctx.getImageData(0, 0, canvas.width, canvas.height));
    }

    // Tool switching
    document.querySelectorAll('.tool-btn[data-tool]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tool-btn[data-tool]').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentTool = btn.dataset.tool;
            if (btn.dataset.color) currentColor = btn.dataset.color;
            if (btn.dataset.stamp) currentStamp = btn.dataset.stamp;
        });
    });

    // Brush size
    const sizeSlider = document.getElementById('brush-size');
    const sizeVal = document.getElementById('brush-size-val');
    sizeSlider.addEventListener('input', () => {
        currentSize = parseInt(sizeSlider.value);
        sizeVal.textContent = currentSize + 'px';
    });

    // Clear
    document.getElementById('clear-btn').addEventListener('click', () => {
        if (confirm('Clear all annotations on this page?')) {
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            saveHistory();
        }
    });

    // Canvas Events
    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        return {
            x: (e.clientX - rect.left) * (canvas.width / rect.width),
            y: (e.clientY - rect.top) * (canvas.height / rect.height)
        };
    }

    canvas.addEventListener('mousedown', (e) => {
        const pos = getPos(e);
        startX = pos.x;
        startY = pos.y;

        if (currentTool === 'stamp') {
            ctx.font = `bold ${currentSize * 8}px sans-serif`;
            ctx.fillStyle = currentStamp === '✔' ? '#16a34a' : '#dc2626';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(currentStamp, pos.x, pos.y);
            saveHistory();
            return;
        }

        if (currentTool === 'text') {
            const comment = prompt('Enter annotation comment:');
            if (comment) {
                ctx.font = `bold ${Math.max(currentSize * 3.5, 14)}px sans-serif`;
                ctx.fillStyle = currentColor;
                ctx.fillText(comment, pos.x, pos.y);
                saveHistory();
            }
            return;
        }

        isDrawing = true;
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
    });

    canvas.addEventListener('mousemove', (e) => {
        if (!isDrawing) return;
        const pos = getPos(e);

        ctx.lineWidth = currentTool === 'highlighter' ? currentSize * 4 : currentSize;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        if (currentTool === 'eraser') {
            ctx.strokeStyle = '#ffffff';
        } else {
            ctx.strokeStyle = currentColor;
        }

        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
    });

    canvas.addEventListener('mouseup', () => {
        if (isDrawing) {
            isDrawing = false;
            saveHistory();
        }
    });

    canvas.addEventListener('mouseleave', () => {
        isDrawing = false;
    });

    // Save annotation to server
    document.getElementById('save-annotation-btn').addEventListener('click', async () => {
        const saveBtn = document.getElementById('save-annotation-btn');
        const statusDiv = document.getElementById('save-status');
        const marks = document.getElementById('marks-input').value;
        const feedback = document.getElementById('feedback-input').value;

        if (marks === '') {
            alert('Please enter marks obtained.');
            return;
        }

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

        const dataUrl = canvas.toDataURL('image/png');

        try {
            const res = await fetch("{{ route('homework.submission.annotate.save', $submission->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    marks_obtained: marks,
                    teacher_feedback: feedback,
                    image_data: dataUrl
                })
            });

            const data = await res.json();
            if (data.success) {
                statusDiv.className = 'alert alert-success text-xs rounded-xl py-2 px-3 mb-2';
                statusDiv.textContent = 'Evaluation and annotated document saved successfully!';
                statusDiv.classList.remove('d-none');
                setTimeout(() => {
                    window.location.href = "{{ route('homework.show', $submission->homework_id) }}";
                }, 1200);
            } else {
                alert(data.message || 'Save failed');
            }
        } catch (err) {
            alert('Network error while saving annotation.');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="bi bi-check2-circle me-1.5"></i> Save & Publish Evaluation';
        }
    });
});
</script>
@endsection
