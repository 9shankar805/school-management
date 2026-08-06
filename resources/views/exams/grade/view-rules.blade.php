@extends('layouts.app')
@section('title', 'View Grading Rules')

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
                    <i class="bi bi-file-earmark-spreadsheet"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Grading System Rules List</h2>
                    <p class="text-slate-500 text-sm mb-0">View grade scale boundaries, GPA points, and percentage thresholds</p>
                </div>
            </div>
        </div>

        <a href="{{ route('exam.grade.system.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100 transition">
            <i class="bi bi-arrow-left me-1.5"></i> Back to Grading Systems
        </a>
    </div>

    @include('session-messages')

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
            <div>
                <h5 class="font-bold text-slate-800 mb-0">Grade Scale Table</h5>
                <p class="text-xs text-slate-400 mb-0">Configured grade boundaries</p>
            </div>
        </div>

        @if(isset($gradeRules) && count($gradeRules) > 0)
        <div class="table-responsive">
            <table class="table align-middle text-sm mb-0">
                <thead>
                    <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                        <th class="py-3 px-4 rounded-s-xl">System Name</th>
                        <th class="py-3 px-4">Grade Letter</th>
                        <th class="py-3 px-4">GPA Point</th>
                        <th class="py-3 px-4">Range (Percentage)</th>
                        <th class="py-3 px-4 rounded-e-xl text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($gradeRules as $gradeRule)
                    <tr>
                        <td class="py-3 px-4 font-bold text-slate-800">
                            {{ $gradeRule->gradingSystem->system_name ?? 'N/A' }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="badge bg-purple-100 text-purple-700 font-extrabold text-sm px-3 py-1 rounded-lg">
                                {{ $gradeRule->grade }}
                            </span>
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-800">
                            <span class="badge bg-indigo-50 text-indigo-700 font-bold text-xs px-2.5 py-1 rounded-lg">
                                {{ $gradeRule->point }} Point
                            </span>
                        </td>
                        <td class="py-3 px-4 text-xs font-medium text-slate-600">
                            {{ $gradeRule->start_at }}% &mdash; {{ $gradeRule->end_at }}%
                        </td>
                        <td class="py-3 px-4 text-end">
                            <form id="delete-form-{{ $gradeRule->id }}" action="{{ route('exam.grade.system.rule.delete') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="id" value="{{ $gradeRule->id }}">
                                <button type="submit" class="btn btn-sm btn-outline-danger font-semibold text-xs py-1.5 px-3 rounded-lg" onclick="return confirm('Are you sure you want to delete this grade rule?')">
                                    <i class="bi bi-trash me-1"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5 text-slate-400">
            <i class="bi bi-award display-2 text-slate-200 mb-3 block"></i>
            <h6 class="font-bold text-slate-600 mb-1">No Grade Rules Found</h6>
            <p class="mb-0 text-xs">Add grade rules (e.g. A+: 90% - 100%, 4.0 GPA) to complete this system.</p>
        </div>
        @endif
    </div>
</div>
@endsection
