@extends('layouts.app')
@section('title', 'Recruitment')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-bold text-gray-800 mb-1"><i class="bi bi-briefcase text-purple-500"></i> Recruitment</h2>
            <p class="text-muted mb-0">Manage job postings and applicant pipelines.</p>
        </div>
        <button class="btn btn-primary shadow-sm"><i class="bi bi-plus-lg"></i> Post New Job</button>
    </div>

    <div class="row g-4">
        <!-- Job Postings List -->
        <div class="col-12 col-xl-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="font-bold text-gray-800 mb-0">Active Job Postings</h5>
                </div>
                <div class="card-body p-4">
                    @forelse($jobs as $job)
                        <div class="border rounded-4 p-4 mb-3 position-relative">
                            @if($job->status == 'open')
                                <span class="position-absolute top-0 end-0 mt-3 me-3 badge bg-green-100 text-green-700 rounded-pill">Open</span>
                            @else
                                <span class="position-absolute top-0 end-0 mt-3 me-3 badge bg-gray-200 text-gray-700 rounded-pill">Closed</span>
                            @endif
                            <h5 class="font-bold text-indigo-700 mb-1">{{ $job->title }}</h5>
                            <p class="text-sm text-muted mb-3"><i class="bi bi-geo-alt"></i> {{ ucfirst($job->employment_type) }} • Posted: {{ $job->created_at->format('M d') }}</p>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-xs text-muted"><i class="bi bi-people"></i> {{ $job->applications_count ?? 0 }} Applicants</div>
                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3">View Details</button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 border rounded-4 bg-light">
                            <p class="text-muted mb-0">No active job postings.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Applications -->
        <div class="col-12 col-xl-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                    <h5 class="font-bold text-gray-800 mb-0">Recent Applications</h5>
                </div>
                <div class="card-body p-0 mt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-4 py-3 text-muted text-xs text-uppercase">Applicant</th>
                                    <th class="py-3 text-muted text-xs text-uppercase">Position</th>
                                    <th class="py-3 text-muted text-xs text-uppercase">Stage</th>
                                    <th class="px-4 py-3 text-muted text-xs text-uppercase text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($applications as $app)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-gray-800">{{ $app->applicant_name }}</div>
                                        <div class="text-xs text-muted"><i class="bi bi-envelope"></i> {{ $app->applicant_email }}</div>
                                    </td>
                                    <td class="py-3 text-sm font-semibold text-gray-700">
                                        {{ $app->jobPosting->title ?? 'N/A' }}
                                    </td>
                                    <td class="py-3">
                                        @php
                                            $stageBadge = match($app->status) {
                                                'applied' => 'bg-blue-100 text-blue-700',
                                                'interviewing' => 'bg-purple-100 text-purple-700',
                                                'offered' => 'bg-green-100 text-green-700',
                                                'rejected' => 'bg-red-100 text-red-700',
                                                default => 'bg-light text-dark'
                                            };
                                        @endphp
                                        <span class="badge {{ $stageBadge }} rounded-pill px-3 text-capitalize">{{ $app->status }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <button class="btn btn-sm btn-light text-primary" title="Review Application"><i class="bi bi-file-earmark-person"></i> Review</button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <div class="text-muted"><i class="bi bi-inbox display-4 text-gray-300"></i></div>
                                        <h5 class="mt-3 text-gray-600">No Applications Yet</h5>
                                        <p class="text-sm text-gray-400">Share your job postings to attract talent.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
