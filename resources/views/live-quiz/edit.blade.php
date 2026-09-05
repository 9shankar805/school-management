@extends('layouts.app')

@section('title', 'Edit Live Quiz - ' . $quiz->title)

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <a href="{{ route('live-quiz.show', $quiz->id) }}" class="text-decoration-none text-muted small">
                        <i class="bi bi-arrow-left"></i> Back to Quiz Details
                    </a>
                    <h2 class="fw-bold text-dark mb-1">Edit Quiz Competition</h2>
                    <p class="text-muted small mb-0">Update metadata and default rules for this quiz.</p>
                </div>
            </div>

            <form action="{{ route('live-quiz.update', $quiz->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-sliders me-2 text-primary"></i> Competition Settings</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Quiz Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control rounded-3" value="{{ old('title', $quiz->title) }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Category / Topic</label>
                                <input type="text" name="category" class="form-control rounded-3" value="{{ old('category', $quiz->category) }}">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Description / Instructions</label>
                                <textarea name="description" class="form-control rounded-3" rows="2">{{ old('description', $quiz->description) }}</textarea>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Game Mode <span class="text-danger">*</span></label>
                                <select name="mode" class="form-select rounded-3">
                                    <option value="mcq" {{ $quiz->mode === 'mcq' ? 'selected' : '' }}>Multiple Choice (A/B/C/D)</option>
                                    <option value="buzzer" {{ $quiz->mode === 'buzzer' ? 'selected' : '' }}>Fast Buzzer Mode</option>
                                    <option value="mixed" {{ $quiz->mode === 'mixed' ? 'selected' : '' }}>Mixed Mode</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Default Time Limit (Seconds)</label>
                                <div class="input-group">
                                    <input type="number" name="default_time_limit" class="form-control" min="5" max="180" value="{{ old('default_time_limit', $quiz->default_time_limit) }}">
                                    <span class="input-group-text">sec</span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Default Points</label>
                                <input type="number" name="default_points" class="form-control" min="10" max="1000" step="10" value="{{ old('default_points', $quiz->default_points) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Assign to Class</label>
                                <select name="class_id" class="form-select rounded-3">
                                    <option value="">-- All Classes --</option>
                                    @foreach($classes as $c)
                                        <option value="{{ $c->id }}" {{ $quiz->class_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Course / Subject</label>
                                <select name="course_id" class="form-select rounded-3">
                                    <option value="">-- General Knowledge --</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ $quiz->course_id == $course->id ? 'selected' : '' }}>{{ $course->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 mt-3 d-flex gap-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="speed_bonus" id="speed_bonus" value="1" {{ $quiz->speed_bonus ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="speed_bonus">⚡ Speed Bonus Enabled</label>
                                </div>

                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ $quiz->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="is_active">Active for Hosting</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('live-quiz.show', $quiz->id) }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold">Update Quiz</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
