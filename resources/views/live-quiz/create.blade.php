@extends('layouts.app')

@section('title', 'Create Live Quiz Competition')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <a href="{{ route('live-quiz.index') }}" class="text-decoration-none text-muted small">
                        <i class="bi bi-arrow-left"></i> Back to Quizzes
                    </a>
                    <h2 class="fw-bold text-dark mb-1">Create Interactive Live Quiz</h2>
                    <p class="text-muted small mb-0">Set up competition rules and add initial questions for group play.</p>
                </div>
            </div>

            <form action="{{ route('live-quiz.store') }}" method="POST" id="quiz-form">
                @csrf

                <!-- Basic Quiz Config Card -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-sliders me-2 text-primary"></i> Quiz Competition Settings</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Quiz Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control rounded-3" placeholder="e.g. Inter-House Science & General Knowledge Challenge" required value="{{ old('title') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Category / Topic</label>
                                <input type="text" name="category" class="form-control rounded-3" placeholder="e.g. Science, History, Math" value="{{ old('category', 'General') }}">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Description / Instructions</label>
                                <textarea name="description" class="form-control rounded-3" rows="2" placeholder="Brief rules for the captains and groups...">{{ old('description') }}</textarea>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Game Mode <span class="text-danger">*</span></label>
                                <select name="mode" class="form-select rounded-3">
                                    <option value="mcq" selected>Multiple Choice (A/B/C/D Buttons)</option>
                                    <option value="buzzer">Fast Buzzer Mode (Race to Buzz)</option>
                                    <option value="mixed">Mixed Mode</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Time per Question (Seconds)</label>
                                <div class="input-group">
                                    <input type="number" name="default_time_limit" class="form-control rounded-start-3" min="5" max="180" value="{{ old('default_time_limit', 30) }}">
                                    <span class="input-group-text rounded-end-3">sec</span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Default Points per Question</label>
                                <input type="number" name="default_points" class="form-control rounded-3" min="10" max="1000" step="10" value="{{ old('default_points', 100) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Assign to Class (Optional)</label>
                                <select name="class_id" class="form-select rounded-3">
                                    <option value="">-- All Classes / Open Competition --</option>
                                    @foreach($classes as $c)
                                        <option value="{{ $c->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Course / Subject (Optional)</label>
                                <select name="course_id" class="form-select rounded-3">
                                    <option value="">-- General Knowledge / Multidisciplinary --</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>{{ $course->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 mt-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="speed_bonus" id="speed_bonus" value="1" checked>
                                    <label class="form-check-label fw-semibold" for="speed_bonus">
                                        ⚡ Enable Speed Bonus Points (Teams answering in fewer seconds earn higher bonus points)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Questions Section -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-question-circle-fill me-2 text-indigo"></i> Add Questions</h5>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="addQuestionCard()">
                        <i class="bi bi-plus-circle me-1"></i> Add Another Question
                    </button>
                </div>

                <div id="questions-container">
                    <!-- Question Item #1 -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white question-card" id="question-card-0">
                        <div class="card-header bg-light border-0 py-3 px-4 rounded-top-4 d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-primary question-number-label">Question #1</span>
                            <select onchange="switchType(this, 0)" name="questions[0][question_type]"
                                    class="form-select form-select-sm rounded-pill fw-bold w-auto border-0 bg-white text-primary">
                                <option value="mcq">⚡ MCQ — 4 Options</option>
                                <option value="direct">👑 Verbal Open — No Options (Direct/Pass)</option>
                                <option value="buzzer">🔔 Buzzer — First to Press</option>
                                <option value="true_false">⚖️ True / False</option>
                            </select>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Question Prompt <span class="text-danger">*</span></label>
                                <textarea name="questions[0][question_text]" class="form-control rounded-3" rows="2" placeholder="e.g. What is the chemical formula for water?" required></textarea>
                            </div>

                            {{-- MCQ options (shown for mcq / buzzer / true_false) --}}
                            <div class="options-block-0 options-block">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text bg-danger text-white fw-bold">A</span>
                                            <input type="text" name="questions[0][option_a]" class="form-control" placeholder="Option A text">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text bg-primary text-white fw-bold">B</span>
                                            <input type="text" name="questions[0][option_b]" class="form-control" placeholder="Option B text">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text bg-warning text-dark fw-bold">C</span>
                                            <input type="text" name="questions[0][option_c]" class="form-control" placeholder="Option C text">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text bg-success text-white fw-bold">D</span>
                                            <input type="text" name="questions[0][option_d]" class="form-control" placeholder="Option D text">
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-3 align-items-center mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Correct Answer <span class="text-danger">*</span></label>
                                        <select name="questions[0][correct_option]" class="form-select rounded-3 fw-bold text-success">
                                            <option value="A">Option A</option>
                                            <option value="B">Option B</option>
                                            <option value="C">Option C</option>
                                            <option value="D">Option D</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Verbal answer block (shown for direct) --}}
                            <div class="verbal-block-0 verbal-block d-none">
                                <div class="alert alert-warning border-warning rounded-3 py-2 px-3 small mb-3">
                                    <i class="bi bi-mic-fill me-1"></i>
                                    <strong>Verbal Round:</strong> No options are shown. Team answers verbally. Teacher judges correct/wrong on the live screen.
                                    The question stays with the first team for <strong>Phase 1 (Main Timer)</strong>.
                                    If passed, the next team gets <strong>Phase 2 (Pass Timer)</strong>.
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Expected Answer (Shown privately to teacher on reveal)</label>
                                    <input type="text" name="questions[0][direct_answer]" class="form-control rounded-3 fw-bold text-success" placeholder="e.g. Photosynthesis, 1947, Red and Blue">
                                </div>
                            </div>

                            <div class="row g-3 align-items-center">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Phase 1 Timer (s)</label>
                                    <input type="number" name="questions[0][time_limit_seconds]" class="form-control rounded-3" value="60" min="5" max="180">
                                    <div class="form-text">Main / first-team time</div>
                                </div>
                                <div class="col-md-3 pass-timer-col-0 pass-timer-col">
                                    <label class="form-label fw-semibold">Phase 2 Timer (s)</label>
                                    <input type="number" name="questions[0][pass_time_seconds]" class="form-control rounded-3" value="30" min="5" max="180">
                                    <div class="form-text">After pass to next team</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Main Points</label>
                                    <input type="number" name="questions[0][points]" class="form-control rounded-3" value="100" min="10" max="1000">
                                </div>
                                <div class="col-md-3 pass-pts-col-0 pass-pts-col">
                                    <label class="form-label fw-semibold">Pass Points</label>
                                    <input type="number" name="questions[0][pass_points]" class="form-control rounded-3" value="50" min="5" max="500">
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-muted small">Explanation / Fun Fact (Shown on teacher reveal)</label>
                                    <input type="text" name="questions[0][explanation]" class="form-control rounded-3 form-control-sm" placeholder="e.g. Water is composed of two hydrogen atoms and one oxygen atom.">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="addQuestionCard()">
                        <i class="bi bi-plus-lg me-1"></i> Add Another Question
                    </button>
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow">
                        <i class="bi bi-check-circle-fill me-1"></i> Save & Launch Live Quiz
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let questionCounter = 1;

function switchType(sel, idx) {
    const val = sel.value;
    const isDirect = (val === 'direct');
    const opts   = document.querySelector(`.options-block-${idx}`);
    const verbal = document.querySelector(`.verbal-block-${idx}`);
    const passTc = document.querySelector(`.pass-timer-col-${idx}`);
    const passPc = document.querySelector(`.pass-pts-col-${idx}`);
    if (opts)   opts.classList.toggle('d-none', isDirect);
    if (verbal) verbal.classList.toggle('d-none', !isDirect);
    if (passTc) passTc.classList.toggle('d-none', false); // always show pass timer
    if (passPc) passPc.classList.toggle('d-none', !isDirect);
    // Adjust default timer based on type
    const timerInput = document.querySelector(`input[name="questions[${idx}][time_limit_seconds]"]`);
    if (timerInput && timerInput.value == 60 || timerInput?.value == 25 || timerInput?.value == 30) {
        timerInput.value = isDirect ? 60 : 25;
    }
}

function addQuestionCard() {
    const idx = questionCounter;
    const cardHtml = `
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white question-card" id="question-card-${idx}">
        <div class="card-header bg-light border-0 py-3 px-4 rounded-top-4 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-primary question-number-label">Question #${idx + 1}</span>
            <div class="d-flex align-items-center gap-2">
                <select onchange="switchType(this, ${idx})" name="questions[${idx}][question_type]"
                        class="form-select form-select-sm rounded-pill fw-bold w-auto border-0 bg-white text-primary">
                    <option value="mcq">⚡ MCQ — 4 Options</option>
                    <option value="direct">👑 Verbal Open — No Options (Direct/Pass)</option>
                    <option value="buzzer">🔔 Buzzer — First to Press</option>
                    <option value="true_false">⚖️ True / False</option>
                </select>
                <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removeQuestionCard(${idx})">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="mb-3">
                <label class="form-label fw-semibold">Question Prompt <span class="text-danger">*</span></label>
                <textarea name="questions[${idx}][question_text]" class="form-control rounded-3" rows="2" placeholder="Type question prompt here..." required></textarea>
            </div>

            <div class="options-block-${idx} options-block">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-danger text-white fw-bold">A</span>
                            <input type="text" name="questions[${idx}][option_a]" class="form-control" placeholder="Option A text">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white fw-bold">B</span>
                            <input type="text" name="questions[${idx}][option_b]" class="form-control" placeholder="Option B text">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-warning text-dark fw-bold">C</span>
                            <input type="text" name="questions[${idx}][option_c]" class="form-control" placeholder="Option C text">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-success text-white fw-bold">D</span>
                            <input type="text" name="questions[${idx}][option_d]" class="form-control" placeholder="Option D text">
                        </div>
                    </div>
                </div>
                <div class="row g-3 align-items-center mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Correct Answer <span class="text-danger">*</span></label>
                        <select name="questions[${idx}][correct_option]" class="form-select rounded-3 fw-bold text-success">
                            <option value="A">Option A</option>
                            <option value="B">Option B</option>
                            <option value="C">Option C</option>
                            <option value="D">Option D</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="verbal-block-${idx} verbal-block d-none">
                <div class="alert alert-warning border-warning rounded-3 py-2 px-3 small mb-3">
                    <i class="bi bi-mic-fill me-1"></i>
                    <strong>Verbal Round:</strong> No options shown. Phase 1 = main team's time. Phase 2 = pass timer for next team. Teacher judges manually.
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Expected Answer (private — shown to teacher on reveal)</label>
                    <input type="text" name="questions[${idx}][direct_answer]" class="form-control rounded-3 fw-bold text-success" placeholder="e.g. Photosynthesis, 1947, Red and Blue">
                </div>
            </div>

            <div class="row g-3 align-items-center">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Phase 1 Timer (s)</label>
                    <input type="number" name="questions[${idx}][time_limit_seconds]" class="form-control rounded-3" value="25" min="5" max="180">
                    <div class="form-text">Main / first-team time</div>
                </div>
                <div class="col-md-3 pass-timer-col-${idx} pass-timer-col">
                    <label class="form-label fw-semibold">Phase 2 Timer (s)</label>
                    <input type="number" name="questions[${idx}][pass_time_seconds]" class="form-control rounded-3" value="30" min="5" max="180">
                    <div class="form-text">After pass to next team</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Main Points</label>
                    <input type="number" name="questions[${idx}][points]" class="form-control rounded-3" value="100" min="10" max="1000">
                </div>
                <div class="col-md-3 pass-pts-col-${idx} pass-pts-col d-none">
                    <label class="form-label fw-semibold">Pass Points</label>
                    <input type="number" name="questions[${idx}][pass_points]" class="form-control rounded-3" value="50" min="5" max="500">
                </div>
                <div class="col-12">
                    <label class="form-label text-muted small">Explanation / Fun Fact</label>
                    <input type="text" name="questions[${idx}][explanation]" class="form-control rounded-3 form-control-sm" placeholder="Explanation for students...">
                </div>
            </div>
        </div>
    </div>
    `;
    document.getElementById('questions-container').insertAdjacentHTML('beforeend', cardHtml);
    questionCounter++;
}

function removeQuestionCard(idx) {
    const el = document.getElementById(`question-card-${idx}`);
    if (el) el.remove();
}
</script>
@endsection
