@extends('layouts.app')
@section('title', 'Promote Class Section')

@section('content')
<style>
  .header-icon-box-promote {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;
    color: #ffffff !important;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
  }
  .header-icon-box-promote i {
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
                <div class="header-icon-box-promote">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Promote Class & Section</h2>
                    <p class="text-slate-500 text-sm mb-0">Promote student batches to their next academic grade level for the new session</p>
                </div>
            </div>
        </div>

        <a href="{{ route('student.list.show') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100 transition">
            <i class="bi bi-people me-1.5"></i> All Students
        </a>
    </div>

    @include('session-messages')

    <!-- Filter Section Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-4">
        <form action="{{ route('promotions.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-12 col-md-6 col-lg-5">
                <label class="form-label font-semibold text-slate-700 text-sm">Select Previous Session Class <span class="text-rose-500">*</span></label>
                <select class="form-select rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800 focus:ring-2 focus:ring-indigo-500/20" name="class_id" required>
                    <option value="" selected disabled>-- Select Class to Promote --</option>
                    @isset($previousSessionClasses)
                        @foreach ($previousSessionClasses as $school_class)
                        <option value="{{ $school_class->schoolClass->id }}" {{ request('class_id') == $school_class->schoolClass->id ? 'selected' : '' }}>
                            {{ $school_class->schoolClass->class_name }}
                        </option>
                        @endforeach
                    @endisset
                </select>
            </div>

            <div class="col-12 col-md-4 col-lg-3">
                <button type="submit" class="btn btn-indigo bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold w-100 py-2.5 rounded-xl shadow-md shadow-indigo-500/20 transition">
                    <i class="bi bi-funnel-fill me-1.5"></i> Load Section List
                </button>
            </div>
        </form>
    </div>

    <!-- Sections Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
            <div>
                <h5 class="font-bold text-slate-800 mb-0">Class Sections Promotion Status</h5>
                <p class="text-xs text-slate-400 mb-0">Section-wise batch promotion overview</p>
            </div>
            @isset($previousSessionSections)
            <span class="badge bg-indigo-50 text-indigo-700 font-semibold text-xs px-3 py-1 rounded-full">
                Sections Found: {{ count($previousSessionSections) }}
            </span>
            @endisset
        </div>

        @isset($previousSessionSections)
            @if(count($previousSessionSections) > 0)
            <div class="table-responsive">
                <table class="table align-middle text-sm mb-0">
                    <thead>
                        <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                            <th class="py-3 px-4 rounded-s-xl">Section Name</th>
                            <th class="py-3 px-4">Promotion Status</th>
                            <th class="py-3 px-4 rounded-e-xl text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($previousSessionSections as $previousSessionSection)
                        <tr>
                            <td class="py-3 px-4 font-bold text-slate-800">
                                <span class="badge bg-slate-100 text-slate-700 text-sm px-3 py-1.5 rounded-lg">
                                    Section {{ $previousSessionSection->section->section_name }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if ($currentSessionSectionsCounts > 0)
                                <span class="badge bg-emerald-100 text-emerald-700 text-xs px-3 py-1.5 rounded-lg font-semibold">
                                    <i class="bi bi-check-circle-fill me-1"></i> Already Promoted
                                </span>
                                @else
                                <span class="badge bg-amber-100 text-amber-700 text-xs px-3 py-1.5 rounded-lg font-semibold">
                                    <i class="bi bi-clock-history me-1"></i> Pending Promotion
                                </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-end">
                                @if ($currentSessionSectionsCounts > 0)
                                <span class="text-xs text-slate-400 font-medium">No action needed</span>
                                @else
                                <a href="{{ route('promotions.create', ['previousSessionId' => $previousSessionId, 'previous_section_id' => $previousSessionSection->section->id, 'previous_class_id' => $class_id]) }}" class="btn btn-sm btn-indigo bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold text-xs py-1.5 px-3 rounded-xl shadow-sm hover:from-indigo-700 hover:to-purple-700">
                                    <i class="bi bi-graph-up-arrow me-1"></i> Promote Section
                                </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5 text-slate-400">
                <i class="bi bi-diagram-3 display-3 text-slate-200 mb-3 block"></i>
                <h6 class="font-bold text-slate-600 mb-1">No Sections Found</h6>
                <p class="mb-0 text-xs">Select a class from the dropdown above to load sections.</p>
            </div>
            @endif
        @else
        <div class="text-center py-5 text-slate-400">
            <i class="bi bi-funnel display-3 text-slate-200 mb-3 block"></i>
            <h6 class="font-bold text-slate-600 mb-1">Select a Class Above</h6>
            <p class="mb-0 text-xs">Choose a previous session class to view sections and start the promotion process.</p>
        </div>
        @endisset
    </div>
</div>
@endsection
