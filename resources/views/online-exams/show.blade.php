@extends('layouts.app')
@section('title', $exam->title)
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10">
            <div class="row pt-2"><div class="col ps-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h1 class="display-6 mb-0">{{ $exam->title }}</h1>
                        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('online-exams.index') }}">Online Exams</a></li>
                            <li class="breadcrumb-item active">{{ Str::limit($exam->title, 40) }}</li>
                        </ol></nav>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('online-exams.questions', $exam->id) }}" class="btn btn-sm btn-outline-secondary">Manage Questions</a>
                        <a href="{{ route('online-exams.attempts', $exam->id) }}" class="btn btn-sm btn-outline-info">View Attempts</a>
                        @can('create exams')
                        <form method="POST" action="{{ route('online-exams.toggle-publish', $exam->id) }}">
                            @csrf @method('PATCH')
                            <button class="btn btn-sm {{ $exam->status==='published' ? 'btn-warning' : 'btn-success' }}">
                                {{ $exam->status==='published' ? 'Unpublish' : 'Publish' }}
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>

                @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
                @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

                <div class="row g-4">
                    {{-- Details card --}}
                    <div class="col-md-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-header fw-semibold">Exam Details</div>
                            <div class="card-body">
                                <table class="table table-sm mb-0">
                                    <tr><td class="text-muted">Status</td><td>
                                        @php $badges=['draft'=>'warning text-dark','published'=>'success','closed'=>'secondary']; @endphp
                                        <span class="badge bg-{{ $badges[$exam->status]??'secondary' }}">{{ ucfirst($exam->status) }}</span>
                                    </td></tr>
                                    <tr><td class="text-muted">Course</td><td>{{ $exam->course?->course_name ?? '—' }}</td></tr>
                                    <tr><td class="text-muted">Class</td><td>{{ $exam->schoolClass?->class_name ?? 'All' }}</td></tr>
                                    <tr><td class="text-muted">Duration</td><td>{{ $exam->duration_minutes }} minutes</td></tr>
                                    <tr><td class="text-muted">Total Marks</td><td>{{ $exam->total_marks }}</td></tr>
                                    <tr><td class="text-muted">Pass Marks</td><td>{{ $exam->pass_marks }}</td></tr>
                                    <tr><td class="text-muted">Max Attempts</td><td>{{ $exam->max_attempts }}</td></tr>
                                    <tr><td class="text-muted">Shuffle</td><td>{{ $exam->shuffle_questions ? 'Yes' : 'No' }}</td></tr>
                                    <tr><td class="text-muted">Available From</td><td>{{ $exam->available_from?->format('d M Y H:i') ?? '—' }}</td></tr>
                                    <tr><td class="text-muted">Available To</td><td>{{ $exam->available_to?->format('d M Y H:i') ?? '—' }}</td></tr>
                                    <tr><td class="text-muted">Questions</td><td>{{ $exam->questions->count() }}</td></tr>
                                </table>
                            </div>
                        </div>
                        @if($exam->instructions)
                        <div class="card shadow-sm border-0 mt-3">
                            <div class="card-header fw-semibold">Instructions</div>
                            <div class="card-body small">{{ $exam->instructions }}</div>
                        </div>
                        @endif
                    </div>

                    {{-- Question summary --}}
                    <div class="col-md-8">
                        <div class="card shadow-sm border-0">
                            <div class="card-header d-flex justify-content-between">
                                <span class="fw-semibold">Questions ({{ $exam->questions->count() }})</span>
                                <a href="{{ route('online-exams.questions', $exam->id) }}" class="btn btn-xs btn-outline-primary">Edit Questions</a>
                            </div>
                            <div class="list-group list-group-flush">
                                @forelse($exam->questions as $i => $q)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="badge bg-secondary me-1">{{ str_replace('_',' ',ucfirst($q->question_type)) }}</span>
                                            <span class="fw-semibold">Q{{ $i+1 }}.</span> {{ Str::limit($q->question_text, 100) }}
                                        </div>
                                        <span class="badge bg-primary ms-2">{{ $q->marks }}m</span>
                                    </div>
                                </div>
                                @empty
                                <div class="list-group-item text-center text-muted py-4">No questions added yet.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div></div>
        </div>
    </div>
</div>
@endsection
