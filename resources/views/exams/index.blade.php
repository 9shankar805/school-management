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
            <div class="col-12 col-md-4">
                <label class="form-label font-semibold text-slate-700 text-sm">Filter Class</label>
                <select class="form-select rounded-xl border-slate-200 py-2.5 text-sm text-slate-700 focus:ring-2 focus:ring-indigo-500/20" name="class_id">
                    <option value="">-- All Classes --</option>
                    @isset($classes)
                        @foreach ($classes as $school_class)
                            <option value="{{ $school_class->id }}" {{ request('class_id') == $school_class->id ? 'selected' : '' }}>
                                {{ $school_class->class_name }}
                            </option>
                        @endforeach
                    @endisset
                </select>
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label font-semibold text-slate-700 text-sm">Filter Term / Semester</label>
                <select class="form-select rounded-xl border-slate-200 py-2.5 text-sm text-slate-700 focus:ring-2 focus:ring-indigo-500/20" name="semester_id">
                    <option value="">-- All Semesters --</option>
                    @isset($semesters)
                        @foreach ($semesters as $semester)
                            <option value="{{ $semester->id }}" {{ request('semester_id') == $semester->id ? 'selected' : '' }}>
                                {{ $semester->semester_name }}
                            </option>
                        @endforeach
                    @endisset
                </select>
            </div>

            <div class="col-12 col-md-4">
                <button type="submit" class="btn btn-indigo bg-indigo-600 hover:bg-indigo-700 text-white font-bold w-100 py-2.5 rounded-xl shadow-sm transition">
                    <i class="bi bi-funnel-fill me-1.5"></i> Apply Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Exam Table List -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
            <div>
                <h5 class="font-bold text-slate-800 mb-1">Scheduled Examinations List</h5>
                <p class="text-xs text-slate-400 mb-0">Showing active exams for your assigned courses and classes</p>
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
                        <th class="py-3 px-4">Subject / Course</th>
                        <th class="py-3 px-4">Start Date</th>
                        <th class="py-3 px-4">End Date</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 rounded-e-xl text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($exams as $exam)
                        @php
                            $isTeacher = Auth::user()->hasRole('teacher');
                            $userRole = Auth::user()->role;
                            
                            // Check if teacher is assigned to course
                            if ($userRole == "teacher") {
                                $isAssigned = false;
                                foreach ($teacher_courses ?? [] as $tc) {
                                    if ($exam->course?->id == $tc->course_id) {
                                        $isAssigned = true;
                                        break;
                                    }
                                }
                                if (!$isAssigned) continue;
                            }

                            $now = now();
                            $startDate = \Carbon\Carbon::parse($exam->start_date);
                            $endDate = \Carbon\Carbon::parse($exam->end_date);
                        @endphp
                        <tr>
                            <td class="py-3 px-4 font-bold text-slate-800">
                                <i class="bi bi-file-earmark-text text-indigo-600 me-2"></i> {{ $exam->exam_name }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="badge bg-slate-100 text-slate-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                    {{ $exam->course->course_name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-600 font-medium">
                                <i class="bi bi-calendar-event text-slate-400 me-1"></i> {{ $startDate->format('M d, Y - h:i A') }}
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-600 font-medium">
                                <i class="bi bi-calendar-check text-slate-400 me-1"></i> {{ $endDate->format('M d, Y - h:i A') }}
                            </td>
                            <td class="py-3 px-4">
                                @if($now->lt($startDate))
                                    <span class="badge bg-amber-100 text-amber-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                        <i class="bi bi-clock me-1"></i> Upcoming
                                    </span>
                                @elseif($now->between($startDate, $endDate))
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
                                <div class="btn-group shadow-sm rounded-xl overflow-hidden" role="group">
                                    @can('create exams rule')
                                    <a href="{{ route('exam.rule.create', ['exam_id' => $exam->id]) }}" class="btn btn-sm btn-indigo bg-indigo-600 text-white font-semibold text-xs py-1.5 px-3">
                                        <i class="bi bi-plus-circle me-1"></i> Add Rule
                                    </a>
                                    @endcan
                                    @can('view exams rule')
                                    <a href="{{ route('exam.rule.show', ['exam_id' => $exam->id]) }}" class="btn btn-sm btn-light border border-slate-200 text-slate-700 font-semibold text-xs py-1.5 px-3">
                                        <i class="bi bi-eye me-1"></i> Rules
                                    </a>
                                    @endcan
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
            <h6 class="font-bold text-slate-600 mb-1">No Exams Scheduled Yet</h6>
            <p class="mb-4 text-xs">Create your first examination to set up rules, gradebooks, and mark sheets.</p>
            @can('create exams')
            <a href="{{ route('exam.create.show') }}" class="btn btn-indigo bg-indigo-600 text-white font-bold px-4 py-2 rounded-xl text-sm shadow-md shadow-indigo-500/20">
                <i class="bi bi-plus-circle me-1.5"></i> Schedule Exam Now
            </a>
            @endcan
        </div>
        @endif
    </div>

</div>
@endsection
