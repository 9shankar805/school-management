@extends('layouts.app')

@section('title', $quiz->title . ' - Live Quiz Details')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header with Action Buttons -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <a href="{{ route('live-quiz.index') }}" class="text-decoration-none text-muted small">
                <i class="bi bi-arrow-left"></i> Back to All Quizzes
            </a>
            <h2 class="fw-bold text-dark mb-1">{{ $quiz->title }}</h2>
            <div class="d-flex flex-wrap gap-2 align-items-center mt-1">
                <span class="badge bg-indigo bg-opacity-10 text-primary fw-bold px-3 py-1">
                    <i class="bi bi-tag-fill me-1"></i> {{ $quiz->category ?: 'General' }}
                </span>
                <span class="badge {{ $quiz->mode === 'buzzer' ? 'bg-danger' : 'bg-success' }} px-2 py-1">
                    {{ strtoupper($quiz->mode) }} MODE
                </span>
                <span class="text-muted small">
                    Created by <strong>{{ $quiz->creator->first_name ?? 'Teacher' }}</strong> • {{ $quiz->created_at->format('M d, Y') }}
                </span>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('live-quiz.edit', $quiz->id) }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-pencil-square me-1"></i> Edit Settings
            </a>
            <button type="button" class="btn btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
                <i class="bi bi-plus-circle me-1"></i> Add Question
            </button>
            <a href="{{ route('live-quiz.host.create-session', $quiz->id) }}" class="btn btn-success btn-lg rounded-pill px-4 fw-bold shadow">
                <i class="bi bi-broadcast me-1"></i> Host Live Match Now
            </a>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-info-circle me-2 text-primary"></i> Competition Info</h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small mb-3">{{ $quiz->description ?: 'No additional description provided.' }}</p>

                    <div class="list-group list-group-flush small">
                        <div class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Total Questions</span>
                            <span class="fw-bold text-dark">{{ $quiz->questions_count }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Time per Question</span>
                            <span class="fw-bold text-dark">{{ $quiz->default_time_limit }} seconds</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Base Points</span>
                            <span class="fw-bold text-dark">{{ $quiz->default_points }} pts</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Speed Bonus</span>
                            <span class="badge {{ $quiz->speed_bonus ? 'bg-success' : 'bg-secondary' }}">{{ $quiz->speed_bonus ? 'Enabled' : 'Disabled' }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Total Live Sessions Hosted</span>
                            <span class="fw-bold text-primary">{{ $quiz->sessions_count }} matches</span>
                        </div>
                    </div>

                    <div class="mt-4 pt-2">
                        <a href="{{ route('live-quiz.host.create-session', $quiz->id) }}" class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm">
                            <i class="bi bi-play-circle-fill me-1"></i> Launch Projector Lobby
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Questions List -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 bg-white">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-card-checklist me-2 text-indigo"></i> Questions Repository ({{ $quiz->questions->count() }})
                    </h5>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
                        <i class="bi bi-plus-lg me-1"></i> Add Question
                    </button>
                </div>
                <div class="card-body p-4">
                    @if($quiz->questions->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-chat-square-dots text-muted" style="font-size: 3rem;"></i>
                            <h6 class="fw-bold mt-2 text-dark">No Questions in this Quiz</h6>
                            <p class="text-muted small">Click "Add Question" to start adding challenge questions for the students.</p>
                            <button type="button" class="btn btn-primary btn-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
                                Add First Question
                            </button>
                        </div>
                    @else
                        <div class="d-flex flex-column gap-3">
                            @foreach($quiz->questions as $index => $q)
                                <div class="border rounded-4 p-3 p-md-4 bg-light position-relative">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="d-flex gap-2 align-items-center">
                                            <span class="badge bg-primary rounded-pill px-3 py-1 fw-bold">Q{{ $index + 1 }}</span>
                                            @if($q->question_type === 'direct')
                                            <span class="badge bg-warning text-dark px-2 py-1">👑 Verbal</span>
                                            @elseif($q->question_type === 'buzzer')
                                            <span class="badge bg-danger px-2 py-1">🔔 Buzzer</span>
                                            @elseif($q->question_type === 'true_false')
                                            <span class="badge bg-info px-2 py-1">⚖️ T/F</span>
                                            @else
                                            <span class="badge bg-success px-2 py-1">⚡ MCQ</span>
                                            @endif
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="text-muted small"><i class="bi bi-stopwatch text-warning"></i> {{ $q->time_limit_seconds }}s</span>
                                            @if($q->question_type === 'direct')
                                            <span class="text-muted small"><i class="bi bi-arrow-repeat text-info"></i> {{ $q->pass_time_seconds ?? 30 }}s pass</span>
                                            @endif
                                            <span class="text-muted small"><i class="bi bi-star-fill text-warning"></i> {{ $q->points }}pts</span>
                                            <form action="{{ route('live-quiz.questions.destroy', [$quiz->id, $q->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this question?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger border-0 p-1" title="Delete Question">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <h6 class="fw-bold text-dark mb-3">{{ $q->question_text }}</h6>

                                    @if($q->question_type === 'direct')
                                    <div class="quiz-verbal-banner p-2.5 px-3 rounded-3 small border mb-2">
                                        <i class="bi bi-mic-fill me-1 text-warning"></i>
                                        <span class="fw-semibold">Verbal Answer:</span> <strong class="ms-1">{{ $q->direct_answer ?: '(Not set)' }}</strong>
                                        <span class="text-muted mx-2">·</span> Phase 1: <strong>{{ $q->time_limit_seconds }}s</strong>
                                        <span class="text-muted mx-2">·</span> Phase 2 (pass): <strong>{{ $q->pass_time_seconds ?? 30 }}s</strong>
                                        <span class="text-muted mx-2">·</span> Pass pts: <strong>{{ $q->pass_points ?? 50 }}</strong>
                                    </div>
                                    @endif

                                    <div class="row g-2 mb-2">
                                        @if(!empty($q->options) && is_array($q->options))
                                            @foreach($q->options as $opt)
                                                @php
                                                    $isCorrect = strtoupper((string)($q->correct_option ?? '')) === strtoupper((string)($opt['key'] ?? ''));
                                                @endphp
                                                <div class="col-sm-6">
                                                    <div class="quiz-opt-item {{ $isCorrect ? 'is-correct' : '' }}">
                                                        <span class="quiz-opt-key">{{ $opt['key'] }}</span>
                                                        <span class="quiz-opt-text">{{ $opt['text'] }}</span>
                                                        @if($isCorrect)
                                                            <i class="bi bi-check-circle-fill quiz-opt-check ms-auto" title="Correct Answer"></i>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>

                                    @if($q->explanation)
                                        <div class="small text-muted mt-2 border-top pt-2">
                                            <i class="bi bi-info-circle text-primary me-1"></i> <strong>Explanation:</strong> {{ $q->explanation }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Question Modal -->
<div class="modal fade" id="addQuestionModal" tabindex="-1" aria-labelledby="addQuestionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-dark" id="addQuestionModalLabel">
                    <i class="bi bi-plus-circle me-2 text-primary"></i> Add Question to Quiz
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('live-quiz.questions.store', $quiz->id) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Question Type</label>
                            <select name="question_type" id="modalQuestionType" class="form-select rounded-3 fw-bold text-primary" onchange="handleModalTypeChange(this.value)">
                                <option value="direct" selected>👑 Direct Turn & Pass Round (Inter-House School Bowl)</option>
                                <option value="mcq">⚡ Multiple Choice (4 Options)</option>
                                <option value="buzzer">🔔 Fast Buzzer Round</option>
                                <option value="true_false">⚖️ True / False</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Direct Text Answer <span class="text-danger">*</span></label>
                            <input type="text" name="direct_answer" id="modalDirectAnswer" class="form-control rounded-3" placeholder="e.g. Red, Green, Blue">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Question Prompt <span class="text-danger">*</span></label>
                        <textarea name="question_text" class="form-control rounded-3" rows="2" placeholder="e.g. Name the three primary colors of light in physics." required></textarea>
                    </div>

                    <!-- Options Grid (Hidden for Direct) -->
                    <div class="row g-2 mb-3 d-none" id="modalOptionsGrid">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-danger">Option A</label>
                            <input type="text" name="option_a" id="modalOptA" class="form-control" placeholder="Option A text">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-primary">Option B</label>
                            <input type="text" name="option_b" id="modalOptB" class="form-control" placeholder="Option B text">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-warning">Option C</label>
                            <input type="text" name="option_c" id="modalOptC" class="form-control" placeholder="Option C text">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-success">Option D</label>
                            <input type="text" name="option_d" id="modalOptD" class="form-control" placeholder="Option D text">
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-3" id="modalCorrectOptionCol" style="display: none;">
                            <label class="form-label fw-semibold">Correct Option</label>
                            <select name="correct_option" class="form-select rounded-3 fw-bold text-success">
                                <option value="A">Option A</option>
                                <option value="B">Option B</option>
                                <option value="C">Option C</option>
                                <option value="D">Option D</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Main Timer (s)</label>
                            <input type="number" name="time_limit_seconds" class="form-control rounded-3" value="60" min="5" max="180">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Pass Timer (s)</label>
                            <input type="number" name="pass_time_seconds" class="form-control rounded-3" value="30" min="5" max="180">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Main Points</label>
                            <input type="number" name="points" class="form-control rounded-3" value="100" min="10" max="1000">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Pass Points</label>
                            <input type="number" name="pass_points" class="form-control rounded-3" value="50" min="5" max="500">
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small">Explanation / Fun Fact (Optional)</label>
                            <input type="text" name="explanation" class="form-control rounded-3" placeholder="Displayed to audience when answer is revealed...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold">Save Question</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function handleModalTypeChange(val) {
    const optsGrid = document.getElementById('modalOptionsGrid');
    const correctCol = document.getElementById('modalCorrectOptionCol');
    if (val === 'direct') {
        optsGrid.classList.add('d-none');
        correctCol.style.display = 'none';
    } else {
        optsGrid.classList.remove('d-none');
        correctCol.style.display = 'block';
    }
}
</script>

<style>
.bg-indigo { background-color: #4f46e5 !important; }
.text-indigo { color: #4f46e5 !important; }

/* Quiz option items styling */
.quiz-opt-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 14px;
    border-radius: 12px;
    border: 1px solid rgba(0, 0, 0, 0.08);
    background-color: #ffffff;
    color: #1e293b;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.quiz-opt-key {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 6px;
    background-color: #475569;
    color: #ffffff;
    font-weight: 700;
    font-size: 0.75rem;
    flex-shrink: 0;
}

.quiz-opt-text {
    font-weight: 500;
    color: inherit;
    word-break: break-word;
}

/* Correct option highlight - Light Mode */
.quiz-opt-item.is-correct {
    background-color: #ecfdf5 !important;
    border-color: #10b981 !important;
    color: #065f46 !important;
    font-weight: 700 !important;
}

.quiz-opt-item.is-correct .quiz-opt-key {
    background-color: #10b981 !important;
    color: #ffffff !important;
}

.quiz-opt-item.is-correct .quiz-opt-text {
    color: #065f46 !important;
    font-weight: 700 !important;
}

.quiz-opt-check {
    color: #10b981 !important;
    font-size: 1.15rem !important;
    flex-shrink: 0;
}

/* Dark Mode support */
html[data-theme="dark"] .quiz-opt-item,
html.dark .quiz-opt-item {
    background-color: #162032 !important;
    border-color: #223049 !important;
    color: #cbd5e1 !important;
}

html[data-theme="dark"] .quiz-opt-item.is-correct,
html.dark .quiz-opt-item.is-correct {
    background-color: rgba(16, 185, 129, 0.18) !important;
    border-color: rgba(16, 185, 129, 0.6) !important;
    color: #f8fafc !important;
    box-shadow: 0 0 14px rgba(16, 185, 129, 0.15) !important;
}

html[data-theme="dark"] .quiz-opt-item.is-correct .quiz-opt-text,
html.dark .quiz-opt-item.is-correct .quiz-opt-text {
    color: #f8fafc !important;
    font-weight: 700 !important;
}

html[data-theme="dark"] .quiz-opt-item.is-correct .quiz-opt-key,
html.dark .quiz-opt-item.is-correct .quiz-opt-key {
    background-color: #10b981 !important;
    color: #ffffff !important;
}

html[data-theme="dark"] .quiz-opt-item.is-correct .quiz-opt-check,
html.dark .quiz-opt-item.is-correct .quiz-opt-check {
    color: #34d399 !important;
}

.quiz-verbal-banner {
    background-color: rgba(245, 158, 11, 0.1);
    border-color: rgba(245, 158, 11, 0.3);
    color: #92400e;
}

html[data-theme="dark"] .quiz-verbal-banner,
html.dark .quiz-verbal-banner {
    background-color: rgba(245, 158, 11, 0.15) !important;
    border-color: rgba(245, 158, 11, 0.4) !important;
    color: #fde68a !important;
}
</style>
@endsection
