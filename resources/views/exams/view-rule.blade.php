@extends('layouts.app')
@section('title', 'Exam Rules Details')

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
                    <i class="bi bi-file-earmark-ruled-fill"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Exam Marking Scheme Rules</h2>
                    <p class="text-slate-500 text-sm mb-0">View and update mark distribution rules for this examination</p>
                </div>
            </div>
        </div>

        <a href="{{ route('exam.list.show') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100 transition">
            <i class="bi bi-arrow-left me-1.5"></i> Back to Exams List
        </a>
    </div>

    @include('session-messages')

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
            <div>
                <h5 class="font-bold text-slate-800 mb-0">Configured Rules List</h5>
                <p class="text-xs text-slate-400 mb-0">Total and passing marks breakdown</p>
            </div>
        </div>

        @if(isset($exam_rules) && count($exam_rules) > 0)
        <div class="table-responsive">
            <table class="table align-middle text-sm mb-0">
                <thead>
                    <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                        <th class="py-3 px-4 rounded-s-xl">Total Marks</th>
                        <th class="py-3 px-4">Pass Marks</th>
                        <th class="py-3 px-4">Marks Distribution Note</th>
                        <th class="py-3 px-4 rounded-e-xl text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($exam_rules as $exam_rule)
                    <tr>
                        <td class="py-3 px-4 font-bold text-slate-800 fs-6">
                            <span class="badge bg-indigo-100 text-indigo-700 text-sm px-3 py-1 rounded-lg">
                                {{ $exam_rule->total_marks }} Marks
                            </span>
                        </td>
                        <td class="py-3 px-4 font-semibold text-emerald-600">
                            <span class="badge bg-emerald-100 text-emerald-700 text-sm px-3 py-1 rounded-lg">
                                Pass: {{ $exam_rule->pass_marks }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-slate-700 font-medium leading-relaxed">
                            {{ $exam_rule->marks_distribution_note }}
                        </td>
                        <td class="py-3 px-4 text-end">
                            <a href="{{ route('exam.rule.edit', ['exam_rule_id' => $exam_rule->id]) }}" class="btn btn-sm btn-indigo bg-indigo-600 text-white font-semibold text-xs py-1.5 px-3 rounded-lg shadow-sm">
                                <i class="bi bi-pencil-square me-1"></i> Edit Rule
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5 text-slate-400">
            <i class="bi bi-file-earmark-x display-2 text-slate-200 mb-3 block"></i>
            <h6 class="font-bold text-slate-600 mb-1">No Marking Rules Added Yet</h6>
            <p class="mb-0 text-xs">Click the button below to add total and passing mark rules.</p>
        </div>
        @endif
    </div>
</div>
@endsection
