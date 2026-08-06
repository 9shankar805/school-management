@extends('layouts.app')
@section('title', 'Manage Questions — '.$exam->title)
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10">
            <div class="row pt-2"><div class="col ps-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h1 class="display-6 mb-0"><i class="bi bi-list-ol"></i> Questions</h1>
                        <p class="text-muted mb-0">{{ $exam->title }} &mdash; {{ $exam->questions->count() }} questions, {{ $totalMark }} marks total</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('online-exams.show', $exam->id) }}" class="btn btn-sm btn-outline-secondary">← Back to Exam</a>
                        @if($exam->status === 'draft')
                        <form method="POST" action="{{ route('online-exams.toggle-publish', $exam->id) }}">
                            @csrf @method('PATCH')
                            <button class="btn btn-sm btn-success"><i class="bi bi-broadcast"></i> Publish</button>
                        </form>
                        @endif
                    </div>
                </div>

                @if(session('status'))<div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
                @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

                <div class="row g-4">
                    {{-- Add question form --}}
                    <div class="col-md-4">
                        <div class="card shadow-sm border-0 sticky-top" style="top:1rem">
                            <div class="card-header fw-semibold">Add Question</div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('online-exams.questions.store', $exam->id) }}" id="addQForm">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label small">Question Type</label>
                                        <select name="question_type" class="form-select form-select-sm" id="qType" onchange="toggleOptions()">
                                            <option value="mcq">MCQ (Multiple Choice)</option>
                                            <option value="true_false">True / False</option>
                                            <option value="short_answer">Short Answer</option>
                                            <option value="essay">Essay</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small">Question Text <span class="text-danger">*</span></label>
                                        <textarea name="question_text" class="form-control form-control-sm" rows="3" required></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small">Marks</label>
                                        <input type="number" name="marks" value="1" min="1" class="form-control form-control-sm">
                                    </div>

                                    {{-- MCQ options --}}
                                    <div id="mcqOptions" class="mb-3">
                                        <label class="form-label small">Options</label>
                                        <div id="optionsList">
                                            <input type="text" name="options[]" class="form-control form-control-sm mb-1" placeholder="Option A">
                                            <input type="text" name="options[]" class="form-control form-control-sm mb-1" placeholder="Option B">
                                            <input type="text" name="options[]" class="form-control form-control-sm mb-1" placeholder="Option C">
                                            <input type="text" name="options[]" class="form-control form-control-sm mb-1" placeholder="Option D">
                                        </div>
                                        <div class="mt-2">
                                            <label class="form-label small">Correct Option (0=A, 1=B, 2=C, 3=D)</label>
                                            <input type="number" name="correct_answer" min="0" max="9" class="form-control form-control-sm" placeholder="e.g. 0">
                                        </div>
                                    </div>

                                    {{-- True/False --}}
                                    <div id="tfOptions" class="mb-3 d-none">
                                        <label class="form-label small">Correct Answer</label>
                                        <select name="correct_answer" class="form-select form-select-sm">
                                            <option value="true">True</option>
                                            <option value="false">False</option>
                                        </select>
                                    </div>

                                    {{-- Short answer --}}
                                    <div id="shortOptions" class="mb-3 d-none">
                                        <label class="form-label small">Expected Answer (for auto-grading)</label>
                                        <input type="text" name="correct_answer" class="form-control form-control-sm">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small">Explanation (optional)</label>
                                        <textarea name="explanation" class="form-control form-control-sm" rows="2"></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-sm w-100">Add Question</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Question list --}}
                    <div class="col-md-8">
                        @forelse($exam->questions as $i => $q)
                        <div class="card shadow-sm border-0 mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <span class="badge bg-primary me-1">Q{{ $i+1 }}</span>
                                        <span class="badge bg-secondary me-2">{{ str_replace('_',' ',ucfirst($q->question_type)) }}</span>
                                        <span class="badge bg-info">{{ $q->marks }} mark{{ $q->marks>1?'s':'' }}</span>
                                        <p class="mt-2 mb-1">{{ $q->question_text }}</p>
                                        @if($q->options)
                                        <ul class="list-unstyled small text-muted ms-2 mb-1">
                                            @foreach($q->options as $idx => $opt)
                                            <li>
                                                <span class="{{ (string)$idx === (string)$q->correct_answer ? 'fw-bold text-success' : '' }}">
                                                    {{ chr(65+$loop->index) }}. {{ $opt }}
                                                    @if((string)$idx === (string)$q->correct_answer) ✓ @endif
                                                </span>
                                            </li>
                                            @endforeach
                                        </ul>
                                        @endif
                                        @if($q->correct_answer && !$q->options)
                                        <div class="small text-success"><strong>Answer:</strong> {{ $q->correct_answer }}</div>
                                        @endif
                                    </div>
                                    <form method="POST" action="{{ route('online-exams.questions.destroy', $q->id) }}" onsubmit="return confirm('Delete?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-xs btn-outline-danger ms-2"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-question-circle fs-1 d-block mb-2"></i>
                            No questions yet. Add your first question using the form.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div></div>
        </div>
    </div>
</div>
@push('scripts')
<script>
function toggleOptions() {
    const type = document.getElementById('qType').value;
    document.getElementById('mcqOptions').classList.toggle('d-none', type !== 'mcq');
    document.getElementById('tfOptions').classList.toggle('d-none', type !== 'true_false');
    document.getElementById('shortOptions').classList.toggle('d-none', type !== 'short_answer');
}
</script>
@endpush
@endsection
