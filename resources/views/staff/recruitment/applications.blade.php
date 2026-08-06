@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2"><div class="col ps-4">
                <h1 class="display-6 mb-1"><i class="bi bi-person-lines-fill"></i> Applications — {{ $posting->title }}</h1>
                <nav aria-label="breadcrumb" class="mb-3"><ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('staff.recruitment.postings') }}">Recruitment</a></li>
                    <li class="breadcrumb-item active">Applications</li>
                </ol></nav>
                @if(session('status'))<div class="alert alert-success alert-dismissible fade show">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

                <div class="row g-3 mb-3">
                    @foreach(['applied'=>'secondary','shortlisted'=>'info','interviewed'=>'primary','selected'=>'success','rejected'=>'danger'] as $s=>$c)
                    <div class="col-auto">
                        <span class="badge bg-{{ $c }}">{{ ucfirst($s) }}: {{ $applications->where('status',$s)->count() }}</span>
                    </div>
                    @endforeach
                </div>

                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th>Applicant</th><th>Email</th><th>Phone</th><th>Interview</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                                @forelse($applications as $app)
                                <tr>
                                    <td class="fw-semibold small">{{ $app->applicant_name }}</td>
                                    <td class="small">{{ $app->email }}</td>
                                    <td class="small text-muted">{{ $app->phone ?? '—' }}</td>
                                    <td class="small">
                                        @if($app->interview_date)
                                            {{ $app->interview_date->format('d M Y') }} {{ $app->interview_time ?? '' }}<br>
                                            <span class="text-muted">{{ $app->interview_location ?? '' }}</span>
                                        @else — @endif
                                    </td>
                                    <td>{!! $app->status_badge !!}</td>
                                    <td>
                                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editApp{{ $app->id }}"><i class="bi bi-pencil"></i></button>
                                        @if($app->resume)<a href="{{ Storage::url($app->resume) }}" target="_blank" class="btn btn-outline-info btn-sm"><i class="bi bi-file-earmark"></i></a>@endif
                                        <form method="POST" action="{{ route('staff.recruitment.applications.destroy', $app->id) }}" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button></form>
                                    </td>
                                </tr>
                                <div class="modal fade" id="editApp{{ $app->id }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
                                    <form method="POST" action="{{ route('staff.recruitment.applications.update', $app->id) }}">@csrf @method('PUT')
                                        <div class="modal-header"><h5 class="modal-title">Update: {{ $app->applicant_name }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <div class="modal-body row g-2">
                                            <div class="col-12"><label class="form-label">Status</label><select name="status" class="form-select"><option value="applied" @selected($app->status==='applied')>Applied</option><option value="shortlisted" @selected($app->status==='shortlisted')>Shortlisted</option><option value="interviewed" @selected($app->status==='interviewed')>Interviewed</option><option value="selected" @selected($app->status==='selected')>Selected</option><option value="rejected" @selected($app->status==='rejected')>Rejected</option></select></div>
                                            <div class="col-6"><label class="form-label">Interview Date</label><input type="date" name="interview_date" class="form-control" value="{{ $app->interview_date?->format('Y-m-d') }}"></div>
                                            <div class="col-6"><label class="form-label">Interview Time</label><input type="time" name="interview_time" class="form-control" value="{{ $app->interview_time }}"></div>
                                            <div class="col-12"><label class="form-label">Interview Location</label><input type="text" name="interview_location" class="form-control" value="{{ $app->interview_location }}"></div>
                                            <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2">{{ $app->notes }}</textarea></div>
                                        </div>
                                        <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary btn-sm">Save</button></div>
                                    </form>
                                </div></div></div>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">No applications yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div></div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
