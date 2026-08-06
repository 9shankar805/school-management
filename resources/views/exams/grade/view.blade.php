@extends('layouts.app')
@section('title', 'Grading Systems')

@section('content')
<style>
  .header-icon-box-award {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%) !important;
    color: #ffffff !important;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
  }
  .header-icon-box-award i {
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
                <div class="header-icon-box-award">
                    <i class="bi bi-award-fill"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Grading Systems</h2>
                    <p class="text-slate-500 text-sm mb-0">Configure letter grades (A+, A, B, C, F), GPA points, and percentage ranges</p>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            @unlessrole('student|parent')
            <a href="{{ route('exam.grade.system.create') }}" class="btn btn-indigo bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold px-4 py-2.5 rounded-xl shadow-md shadow-indigo-500/20 transition">
                <i class="bi bi-plus-circle me-1.5"></i> Create Grading System
            </a>
            @endunlessrole
            <a href="{{ route('exam.list.show') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100 transition">
                <i class="bi bi-arrow-left me-1.5"></i> Back to Exams
            </a>
        </div>
    </div>

    @include('session-messages')

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
            <div>
                <h5 class="font-bold text-slate-800 mb-0">Active Grading Systems</h5>
                <p class="text-xs text-slate-400 mb-0">Assigned by class and semester term</p>
            </div>
            <span class="badge bg-purple-50 text-purple-700 font-semibold text-xs px-3 py-1 rounded-full">
                Total Systems: {{ count($gradingSystems ?? []) }}
            </span>
        </div>

        @if(isset($gradingSystems) && count($gradingSystems) > 0)
        <div class="table-responsive">
            <table class="table align-middle text-sm mb-0">
                <thead>
                    <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                        <th class="py-3 px-4 rounded-s-xl">System Name</th>
                        <th class="py-3 px-4">Class</th>
                        <th class="py-3 px-4">Semester / Term</th>
                        <th class="py-3 px-4">Created Date</th>
                        <th class="py-3 px-4 rounded-e-xl text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($gradingSystems as $gradingSystem)
                    <tr>
                        <td class="py-3 px-4 font-bold text-slate-800">
                            <i class="bi bi-award text-purple-600 me-2"></i> {{ $gradingSystem->system_name }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="badge bg-indigo-50 text-indigo-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                {{ $gradingSystem->schoolClass->class_name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="badge bg-slate-100 text-slate-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                {{ $gradingSystem->semester->semester_name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-xs text-slate-500">
                            {{ $gradingSystem->created_at?->format('M d, Y') ?? 'N/A' }}
                        </td>
                        <td class="py-3 px-4 text-end">
                            <div class="btn-group shadow-sm rounded-xl overflow-hidden" role="group">
                                <a href="{{ route('exam.grade.system.rule.create', ['grading_system_id' => $gradingSystem->id]) }}" class="btn btn-sm btn-indigo bg-indigo-600 text-white font-semibold text-xs py-1.5 px-3">
                                    <i class="bi bi-plus-circle me-1"></i> Add Grade Rule
                                </a>
                                <a href="{{ route('exam.grade.system.rule.show', ['grading_system_id' => $gradingSystem->id]) }}" class="btn btn-sm btn-light border border-slate-200 text-slate-700 font-semibold text-xs py-1.5 px-3">
                                    <i class="bi bi-eye me-1"></i> View Rules
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5 text-slate-400">
            <i class="bi bi-award display-2 text-slate-200 mb-3 block"></i>
            <h6 class="font-bold text-slate-600 mb-1">No Grading Systems Configured</h6>
            <p class="mb-4 text-xs">Create a grading system to define GPA points and grade boundaries (A+, A, B, C, F).</p>
            @can('create grading systems')
            <a href="{{ route('exam.grade.system.create') }}" class="btn btn-indigo bg-indigo-600 text-white font-bold px-4 py-2 rounded-xl text-sm shadow-md shadow-indigo-500/20">
                <i class="bi bi-plus-circle me-1.5"></i> Create Grading System Now
            </a>
            @endcan
        </div>
        @endif
    </div>
</div>
@endsection
