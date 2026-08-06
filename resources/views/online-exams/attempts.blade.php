@extends('layouts.app')
@section('title', 'Attempts — '.$exam->title)
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10">
            <div class="row pt-2"><div class="col ps-4">
                <h1 class="display-6 mb-1"><i class="bi bi-people"></i> Student Attempts</h1>
                <p class="text-muted">{{ $exam->title }}</p>

                @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif

                <div class="card shadow-sm border-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Student</th>
                                    <th class="text-center">Attempt #</th>
                                    <th class="text-center">Started</th>
                                    <th class="text-center">Submitted</th>
                                    <th class="text-center">Score</th>
                                    <th class="text-center">%</th>
                                    <th class="text-center">Result</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attempts as $attempt)
                                <tr>
                                    <td>{{ $attempt->student->fullName }}</td>
                                    <td class="text-center">{{ $attempt->attempt_number }}</td>
                                    <td class="text-center small">{{ $attempt->started_at->format('d M H:i') }}</td>
                                    <td class="text-center small">{{ $attempt->submitted_at?->format('d M H:i') ?? '—' }}</td>
                                    <td class="text-center fw-semibold">{{ $attempt->score ?? '—' }}/{{ $exam->total_marks }}</td>
                                    <td class="text-center">{{ $attempt->percentage ? $attempt->percentage.'%' : '—' }}</td>
                                    <td class="text-center">
                                        @if($attempt->passed !== null)
                                            <span class="badge {{ $attempt->passed ? 'bg-success' : 'bg-danger' }}">{{ $attempt->passed ? 'Pass' : 'Fail' }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php $sc=['in_progress'=>'warning text-dark','submitted'=>'info','timed_out'=>'danger','graded'=>'success']; @endphp
                                        <span class="badge bg-{{ $sc[$attempt->status]??'secondary' }}">{{ str_replace('_',' ',ucfirst($attempt->status)) }}</span>
                                    </td>
                                    <td class="text-end">
                                        @if($attempt->status === 'submitted')
                                        <a href="{{ route('online-exams.grade', $attempt->id) }}" class="btn btn-xs btn-warning">Grade</a>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="9" class="text-center text-muted py-5">No attempts yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">{{ $attempts->links() }}</div>
                </div>
            </div></div>
        </div>
    </div>
</div>
@endsection
