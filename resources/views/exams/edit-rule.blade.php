@extends('layouts.app')
@section('title', 'Edit Exam Rule')

@section('content')
<style>
  .header-icon-box-exam {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%) !important;
    color: #ffffff !important;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
  }
  .header-icon-box-exam i {
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
                <div class="header-icon-box-exam">
                    <i class="bi bi-pencil-square"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Edit Exam Rule</h2>
                    <p class="text-slate-500 text-sm mb-0">Update total marks, passing thresholds, or distribution notes</p>
                </div>
            </div>
        </div>

        <a href="{{ route('exam.list.show') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100 transition">
            <i class="bi bi-arrow-left me-1.5"></i> Back to Exams List
        </a>
    </div>

    @include('session-messages')

    <div class="row g-4">
        <div class="col-12 col-xl-6 mx-auto">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
                <form action="{{ route('exam.rule.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="exam_rule_id" value="{{ $exam_rule_id }}">

                    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
                        <div>
                            <h5 class="font-bold text-slate-800 mb-0">Rule Details</h5>
                            <p class="text-xs text-slate-400 mb-0">Modify passing parameters</p>
                        </div>
                        <span class="badge bg-indigo-50 text-indigo-700 text-xs px-3 py-1 rounded-full font-semibold">Rule ID: #{{ $exam_rule_id }}</span>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-sm">Total Marks <span class="text-rose-500">*</span></label>
                            <input type="number" class="form-control rounded-xl border-slate-200 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-indigo-500/20" value="{{ $exam_rule->total_marks }}" name="total_marks" step="0.01" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-sm">Pass Marks <span class="text-rose-500">*</span></label>
                            <input type="number" class="form-control rounded-xl border-slate-200 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-indigo-500/20" value="{{ $exam_rule->pass_marks }}" name="pass_marks" step="0.01" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label font-semibold text-slate-700 text-sm">Marks Distribution Note <span class="text-rose-500">*</span></label>
                            <textarea class="form-control rounded-xl border-slate-200 p-3 text-sm text-slate-800 focus:ring-2 focus:ring-indigo-500/20" rows="4" name="marks_distribution_note" required>{{ $exam_rule->marks_distribution_note }}</textarea>
                        </div>
                    </div>

                    <hr class="my-4 border-slate-100">

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('exam.list.show') }}" class="btn btn-light border border-slate-200 text-slate-600 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-indigo bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-bold px-6 py-2.5 rounded-xl shadow-md shadow-indigo-500/25 transition">
                            <i class="bi bi-save me-1.5"></i> Update Rule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
