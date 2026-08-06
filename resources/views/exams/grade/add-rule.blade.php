@extends('layouts.app')
@section('title', 'Add Grading Rule')

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
                    <i class="bi bi-plus-square"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Add Grading Rule Boundary</h2>
                    <p class="text-slate-500 text-sm mb-0">Define grade letter (A+, A, B), GPA points, and percentage range boundaries</p>
                </div>
            </div>
        </div>

        <a href="{{ route('exam.grade.system.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100 transition">
            <i class="bi bi-arrow-left me-1.5"></i> Back to Grading Systems
        </a>
    </div>

    @include('session-messages')

    <div class="row g-4">
        <div class="col-12 col-xl-6 mx-auto">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
                <form action="{{ route('exam.grade.system.rule.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="grading_system_id" value="{{ $grading_system_id }}">
                    <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">

                    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
                        <div>
                            <h5 class="font-bold text-slate-800 mb-0">Grade Boundary Definition</h5>
                            <p class="text-xs text-slate-400 mb-0">Grade letter, point value, and range</p>
                        </div>
                        <span class="badge bg-purple-50 text-purple-700 text-xs px-3 py-1 rounded-full font-semibold">System ID: #{{ $grading_system_id }}</span>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-sm">Grade Letter / Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="grade" class="form-control rounded-xl border-slate-200 py-2.5 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-purple-500/20" id="inputGrade" placeholder="e.g. A+, A, B+, C, F" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-sm">GPA Point Value <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="point" class="form-control rounded-xl border-slate-200 py-2.5 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-purple-500/20" id="inputPoint" placeholder="e.g. 4.0, 3.6, 3.2" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-sm">Start Percentage (Lower Bound) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="start_at" class="form-control rounded-xl border-slate-200 py-2.5 text-sm text-slate-800 focus:ring-2 focus:ring-purple-500/20" id="inputStarts" placeholder="e.g. 90, 80" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-sm">End Percentage (Upper Bound) <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="end_at" class="form-control rounded-xl border-slate-200 py-2.5 text-sm text-slate-800 focus:ring-2 focus:ring-purple-500/20" id="inputEnds" placeholder="e.g. 100, 89.9" required>
                        </div>
                    </div>

                    <hr class="my-4 border-slate-100">

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('exam.grade.system.index') }}" class="btn btn-light border border-slate-200 text-slate-600 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-indigo bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold px-6 py-2.5 rounded-xl shadow-md shadow-indigo-500/25 transition">
                            <i class="bi bi-plus-circle me-1.5"></i> Save Grade Rule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
