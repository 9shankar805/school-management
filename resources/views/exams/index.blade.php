@extends('layouts.app')
@section('title', 'Exams Management')

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
                    <i class="bi bi-journal-check"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Exams & Examinations</h2>
                    <p class="text-slate-500 text-sm mb-0">Manage term exams, subject rules, gradebooks, and mark sheets</p>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            @unlessrole('student|parent')
            <a href="{{ route('exam.create.show') }}" class="btn btn-indigo bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-bold px-4 py-2.5 rounded-xl shadow-md shadow-indigo-500/20 transition">
                <i class="bi bi-plus-circle me-1.5"></i> Create Exam
            </a>
            <a href="{{ route('exam.grade.system.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100 transition">
                <i class="bi bi-award me-1.5 text-indigo-600"></i> Grading Systems
            </a>
            @endunlessrole
        </div>
    </div>

    @include('session-messages')

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-4">
        <form action="{{ route('exam.list.show') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-12 col-md-5">
                <label class="form-label font-semibold text-slate-700 text-sm">Filter Class</label>
                <select class="form-select rounded-xl border-slate-200 py-2.5 text-sm text-slate-700 focus:ring-2 focus:ring-indigo-500/20" name="class_id">
                    <option value="0">-- All Classes --</option>
                    @isset($classes)
                        @foreach ($classes as $school_class)
                            <option value="{{ $school_class->id }}" {{ (request('class_id') == $school_class->id || ($class_id ?? 0) == $school_class->id) ? 'selected' : '' }}>
                                {{ $school_class->class_name }}
                            </option>
                        @endforeach
                    @endisset
                </select>
            </div>

            <div class="col-12 col-md-5">
                <label class="form-label font-semibold text-slate-700 text-sm">Filter Term / Semester</label>
                <select class="form-select rounded-xl border-slate-200 py-2.5 text-sm text-slate-700 focus:ring-2 focus:ring-indigo-500/20" name="semester_id">
                    <option value="0">-- All Semesters / Terms --</option>
                    @isset($semesters)
                        @foreach ($semesters as $semester)
                            <option value="{{ $semester->id }}" {{ (request('semester_id') == $semester->id || ($semester_id ?? 0) == $semester->id) ? 'selected' : '' }}>
                                {{ $semester->semester_name }}
                            </option>
                        @endforeach
                    @endisset
                </select>
            </div>

            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-indigo bg-indigo-600 hover:bg-indigo-700 text-white font-bold w-100 py-2.5 rounded-xl shadow-sm transition">
                    <i class="bi bi-funnel-fill me-1"></i> Filter
                </button>
                @if(request('class_id') || request('semester_id'))
                <a href="{{ route('exam.list.show') }}" class="btn btn-light border border-slate-200 text-slate-600 font-semibold py-2.5 px-3 rounded-xl shadow-sm hover:bg-slate-100" title="Clear Filter">
                    <i class="bi bi-x-lg"></i>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Exam Table List -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
            <div>
                <h5 class="font-bold text-slate-800 mb-1">Scheduled Examinations List</h5>
                <p class="text-xs text-slate-400 mb-0">Showing scheduled exams across classes and academic terms</p>
            </div>
            <span class="badge bg-indigo-50 text-indigo-700 font-semibold text-xs px-3 py-1.5 rounded-full">
                Total: {{ count($exams ?? []) }} Exams
            </span>
        </div>

        @if(isset($exams) && count($exams) > 0)
        <div class="table-responsive">
            <table class="table align-middle text-sm mb-0">
                <thead>
                    <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                        <th class="py-3 px-4 rounded-s-xl">Exam Title</th>
                        <th class="py-3 px-4">Class</th>
                        <th class="py-3 px-4">Subject / Course</th>
                        <th class="py-3 px-4">Term</th>
                        <th class="py-3 px-4">Start Date</th>
                        <th class="py-3 px-4">End Date</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 rounded-e-xl text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($exams as $exam)
                        @php
                            $now = now();
                            $startDate = $exam->start_date ? \Carbon\Carbon::parse($exam->start_date) : null;
                            $endDate = $exam->end_date ? \Carbon\Carbon::parse($exam->end_date) : null;
                        @endphp
                        <tr>
                            <td class="py-3 px-4 font-bold text-slate-800">
                                <i class="bi bi-file-earmark-text text-indigo-600 me-2"></i> {{ $exam->exam_name }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="badge bg-indigo-50 text-indigo-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                    {{ $exam->schoolClass->class_name ?? 'All Classes' }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="badge bg-slate-100 text-slate-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                    {{ $exam->course->course_name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-xs font-medium text-slate-600">
                                {{ $exam->semester->semester_name ?? 'N/A' }}
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-600 font-medium">
                                @if($startDate)
                                    <i class="bi bi-calendar-event text-slate-400 me-1"></i>
                                    {{ $startDate->format('M d, Y - h:i A') }}
                                    <span class="text-slate-400 text-xs ms-1">({{ bsDate($startDate->format('Y-m-d'), true, 'short') }})</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-600 font-medium">
                                @if($endDate)
                                    <i class="bi bi-calendar-check text-slate-400 me-1"></i>
                                    {{ $endDate->format('M d, Y - h:i A') }}
                                    <span class="text-slate-400 text-xs ms-1">({{ bsDate($endDate->format('Y-m-d'), true, 'short') }})</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($startDate && $now->lt($startDate))
                                    <span class="badge bg-amber-100 text-amber-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                        <i class="bi bi-clock me-1"></i> Upcoming
                                    </span>
                                @elseif($startDate && $endDate && $now->between($startDate, $endDate))
                                    <span class="badge bg-emerald-100 text-emerald-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                        <i class="bi bi-play-circle-fill me-1"></i> Ongoing
                                    </span>
                                @else
                                    <span class="badge bg-slate-100 text-slate-600 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                        <i class="bi bi-check-all me-1"></i> Completed
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-end">
                                <div class="d-flex justify-content-end gap-1 flex-wrap">
                                    {{-- Admit Card Generator Button --}}
                                    <button type="button" 
                                            class="btn btn-sm btn-success border-0 text-white font-semibold text-xs py-1.5 px-2.5 rounded-lg shadow-2xs" 
                                            style="background-color: #059669 !important; color: #fff !important;"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#admitCardModal{{ $exam->id }}"
                                            title="Print Examination Admit Cards (प्रवेश-पत्र)">
                                        <i class="bi bi-person-badge-fill me-1"></i> Admit Card
                                    </button>

                                    @can('create exams rule')
                                    <a href="{{ route('exam.rule.create', ['exam_id' => $exam->id]) }}" class="btn btn-sm btn-indigo bg-indigo-600 text-white font-semibold text-xs py-1.5 px-3 rounded-lg" style="background-color: #4f46e5 !important; color: #fff !important;">
                                        <i class="bi bi-plus-circle me-1"></i> Rule
                                    </a>
                                    @endcan
                                    @can('view exams rule')
                                    <a href="{{ route('exam.rule.show', ['exam_id' => $exam->id]) }}" class="btn btn-sm btn-light border border-slate-200 text-slate-700 font-semibold text-xs py-1.5 px-3 rounded-lg">
                                        <i class="bi bi-eye me-1"></i> Rules
                                    </a>
                                    @endcan
                                    @can('edit exams')
                                    <a href="{{ route('exam.edit', $exam->id) }}" class="btn btn-sm btn-warning border-0 text-white font-semibold text-xs py-1.5 px-3 rounded-lg" style="background-color: #f59e0b !important; color: #fff !important;">
                                        <i class="bi bi-pencil me-1"></i> Edit
                                    </a>
                                    @endcan
                                    @can('delete exams')
                                    <form action="{{ route('exam.delete') }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this exam? This cannot be undone.');">
                                        @csrf
                                        <input type="hidden" name="exam_id" value="{{ $exam->id }}">
                                        <button type="submit" class="btn btn-sm btn-danger border-0 font-semibold text-xs py-1.5 px-3 rounded-lg" style="background-color: #ef4444 !important; color: #fff !important;">
                                            <i class="bi bi-trash me-1"></i> Delete
                                        </button>
                                    </form>
                                    @endcan
                                </div>

                                {{-- Batch Admit Card Generation Modal --}}
                                <div class="modal fade" id="admitCardModal{{ $exam->id }}" tabindex="-1" aria-labelledby="admitCardModalLabel{{ $exam->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered text-start">
                                        <div class="modal-content rounded-3xl border-0 shadow-2xl overflow-hidden">
                                            <div class="modal-header bg-[#1E1B4B] text-white p-4">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-base">
                                                        <i class="bi bi-person-badge-fill"></i>
                                                    </span>
                                                    <div>
                                                        <h6 class="modal-title font-bold text-white mb-0" id="admitCardModalLabel{{ $exam->id }}">Print Admit Cards (प्रवेश-पत्र)</h6>
                                                        <p class="text-xs text-indigo-200 mb-0">{{ $exam->exam_name }}</p>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('exam.admit-card.batch', $exam->id) }}" method="GET" target="_blank">
                                                <div class="modal-body p-4 space-y-3">
                                                    <div>
                                                        <label class="form-label font-bold text-xs text-slate-700">Select Class <span class="text-rose-500">*</span></label>
                                                        <select name="class_id" required class="form-select rounded-xl border-slate-200 text-xs py-2.5">
                                                            <option value="">-- Choose Class --</option>
                                                            @isset($classes)
                                                                @foreach ($classes as $sc)
                                                                    <option value="{{ $sc->id }}" {{ ($exam->class_id == $sc->id) ? 'selected' : '' }}>
                                                                        {{ $sc->class_name }}
                                                                    </option>
                                                                @endforeach
                                                            @endisset
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="form-label font-bold text-xs text-slate-700">Select Section (Optional)</label>
                                                        <select name="section_id" class="form-select rounded-xl border-slate-200 text-xs py-2.5">
                                                            <option value="0">All Sections</option>
                                                            @if(isset($exam->schoolClass) && $exam->schoolClass->sections)
                                                                @foreach($exam->schoolClass->sections as $sec)
                                                                    <option value="{{ $sec->id }}">Section {{ $sec->section_name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                    <div class="p-3 bg-indigo-50/70 rounded-xl border border-indigo-100 text-[11px] text-indigo-900">
                                                        <i class="bi bi-info-circle-fill text-indigo-600 me-1"></i>
                                                        Admit cards are generated with official school branding, student photo, roll number, exam schedule routine, and verifiable QR code.
                                                    </div>
                                                </div>
                                                <div class="modal-footer p-3 bg-slate-50 border-t border-slate-100">
                                                    <button type="button" class="btn btn-light rounded-xl text-xs font-bold px-3.5 py-2" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success text-white rounded-xl text-xs font-bold px-4 py-2 flex items-center gap-1.5 shadow-sm" style="background-color: #059669 !important;">
                                                        <i class="bi bi-printer-fill"></i> Generate PDF Admit Cards
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5 text-slate-400">
            <i class="bi bi-journal-x display-2 text-slate-200 mb-3 block"></i>
            <h6 class="font-bold text-slate-600 mb-1">No Exams Scheduled</h6>
            <p class="mb-4 text-xs">No exams found for the selected criteria. Clear filters or create a new examination.</p>
            @can('create exams')
            <a href="{{ route('exam.create.show') }}" class="btn btn-indigo bg-indigo-600 text-white font-bold px-4 py-2 rounded-xl text-sm shadow-md shadow-indigo-500/20" style="background-color: #4f46e5 !important; color: #fff !important;">
                <i class="bi bi-plus-circle me-1.5"></i> Schedule Exam Now
            </a>
            @endcan
        </div>
        @endif
    </div>

</div>
@endsection
