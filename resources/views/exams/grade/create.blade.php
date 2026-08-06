@extends('layouts.app')
@section('title', 'Create Grading System')

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
                    <i class="bi bi-file-earmark-plus"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Create Grading System</h2>
                    <p class="text-slate-500 text-sm mb-0">Set up a new grading system for a class and semester term</p>
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
                <form action="{{ route('exam.grade.system.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">

                    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
                        <div>
                            <h5 class="font-bold text-slate-800 mb-0">System Details</h5>
                            <p class="text-xs text-slate-400 mb-0">Define name and target academic level</p>
                        </div>
                        <span class="badge bg-purple-50 text-purple-700 text-xs px-3 py-1 rounded-full font-semibold">Session #{{ $current_school_session_id }}</span>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label font-semibold text-slate-700 text-sm">Grading System Name <span class="text-rose-500">*</span></label>
                            <input type="text" class="form-control rounded-xl border-slate-200 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-purple-500/20" placeholder="e.g. Standard 4.0 GPA System, Letter Grade System 2026" name="system_name" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-sm">Select Class <span class="text-rose-500">*</span></label>
                            <select class="form-select rounded-xl border-slate-200 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-purple-500/20" name="class_id" required>
                                <option value="" selected disabled>-- Select Class --</option>
                                @isset($school_classes)
                                    @foreach ($school_classes as $school_class)
                                    <option value="{{ $school_class->id }}">{{ $school_class->class_name }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-sm">Select Semester / Term <span class="text-rose-500">*</span></label>
                            <select class="form-select rounded-xl border-slate-200 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-purple-500/20" name="semester_id" required>
                                <option value="" selected disabled>-- Select Term --</option>
                                @isset($semesters)
                                    @foreach ($semesters as $semester)
                                    <option value="{{ $semester->id }}" {{ ($semester->id == request()->query('semester_id')) ? 'selected' : '' }}>{{ $semester->semester_name }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                    </div>

                    <hr class="my-4 border-slate-100">

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('exam.grade.system.index') }}" class="btn btn-light border border-slate-200 text-slate-600 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-indigo bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold px-6 py-2.5 rounded-xl shadow-md shadow-indigo-500/25 transition">
                            <i class="bi bi-check2-circle me-1.5"></i> Create System
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
