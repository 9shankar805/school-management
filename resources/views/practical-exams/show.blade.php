@extends('layouts.app')
@section('title', 'Mark Entry — '.$practical->title)

@section('content')
<style>
  .header-icon-box-practical {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: linear-gradient(135deg, #0d9488 0%, #06b6d4 100%) !important;
    color: #ffffff !important;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);
  }
  .header-icon-box-practical i {
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
                <div class="header-icon-box-practical">
                    <i class="bi bi-pencil-square"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Practical Mark Entry: {{ $practical->title }}</h2>
                    <p class="text-slate-500 text-sm mb-0">
                        Course: <strong>{{ $practical->course?->course_name }}</strong> |
                        Class: <strong>{{ $practical->schoolClass?->class_name }}</strong> |
                        Date: <strong>{{ $practical->exam_date->format('d M Y') }}</strong>
                    </p>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('practical-exams.pdf', $practical->id) }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100 transition" target="_blank">
                <i class="bi bi-file-earmark-pdf-fill text-rose-500 me-1.5"></i> Export PDF Sheet
            </a>
            <a href="{{ route('practical-exams.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100 transition">
                <i class="bi bi-arrow-left me-1.5"></i> Back to Practical Exams
            </a>
        </div>
    </div>

    @include('session-messages')

    <!-- Exam Parameters Summary Banner -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-teal-100 text-teal-800 font-bold text-xs px-3 py-1.5 rounded-lg">
                Total Marks: {{ $practical->total_marks }}
            </span>
            <span class="badge bg-amber-100 text-amber-800 font-bold text-xs px-3 py-1.5 rounded-lg">
                Pass Marks: {{ $practical->pass_marks }}
            </span>
            @if($practical->venue)
            <span class="badge bg-slate-100 text-slate-700 font-semibold text-xs px-3 py-1.5 rounded-lg">
                Venue: {{ $practical->venue }}
            </span>
            @endif
        </div>

        <span class="badge bg-slate-100 text-slate-600 font-semibold text-xs px-3 py-1.5 rounded-full">
            Students Roster: {{ count($students ?? []) }}
        </span>
    </div>

    <!-- Mark Entry Form Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
        @if($students->isEmpty())
        <div class="text-center py-5 text-slate-400">
            <i class="bi bi-person-x display-3 text-slate-200 mb-3 block"></i>
            <h6 class="font-bold text-slate-600 mb-1">No Students Enrolled</h6>
            <p class="mb-0 text-xs">No active students were found for this class in the current academic session.</p>
        </div>
        @else
        <form method="POST" action="{{ route('practical-exams.marks.save', $practical->id) }}">
            @csrf

            <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
                <div>
                    <h5 class="font-bold text-slate-800 mb-0">Practical Score Sheet</h5>
                    <p class="text-xs text-slate-400 mb-0">Enter practical marks obtained out of {{ $practical->total_marks }}</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle text-sm mb-0">
                    <thead>
                        <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                            <th class="py-3 px-4 rounded-s-xl" style="width: 50px;">#</th>
                            <th class="py-3 px-4">Student Name</th>
                            <th class="py-3 px-4" style="width: 180px;">Marks Obtained (Max {{ $practical->total_marks }}) <span class="text-rose-500">*</span></th>
                            <th class="py-3 px-4">Examiner Remarks</th>
                            <th class="py-3 px-4 rounded-e-xl text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($students as $i => $student)
                        @php $saved = $existingMarks[$student->id] ?? null; @endphp
                        <input type="hidden" name="marks[{{ $i }}][student_id]" value="{{ $student->id }}">
                        <tr>
                            <td class="py-3 px-4 text-xs font-semibold text-slate-400">{{ $i+1 }}</td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-800">{{ $student->fullName }}</div>
                                <div class="text-xs text-slate-400">Roll #: {{ $student->academic_info?->roll_number ?? 'N/A' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <input type="number" name="marks[{{ $i }}][marks_obtained]"
                                       class="form-control rounded-xl border-slate-200 py-2 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-teal-500/20"
                                       min="0" max="{{ $practical->total_marks }}"
                                       value="{{ $saved?->marks_obtained ?? '' }}"
                                       placeholder="0–{{ $practical->total_marks }}"
                                       required>
                            </td>
                            <td class="py-3 px-4">
                                <input type="text" name="marks[{{ $i }}][remarks]"
                                       class="form-control rounded-xl border-slate-200 py-2 text-sm text-slate-700"
                                       value="{{ $saved?->remarks ?? '' }}"
                                       placeholder="Optional examiner remarks...">
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($saved)
                                    @if($saved->marks_obtained >= $practical->pass_marks)
                                    <span class="badge bg-emerald-100 text-emerald-700 text-xs px-3 py-1 rounded-full font-semibold">
                                        <i class="bi bi-check-circle-fill me-1"></i> Pass
                                    </span>
                                    @else
                                    <span class="badge bg-rose-100 text-rose-700 text-xs px-3 py-1 rounded-full font-semibold">
                                        <i class="bi bi-x-circle-fill me-1"></i> Fail
                                    </span>
                                    @endif
                                @else
                                    <span class="badge bg-slate-100 text-slate-400 text-xs px-2.5 py-1 rounded-lg font-medium">
                                        Not Entered
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <hr class="my-4 border-slate-100">

            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('practical-exams.index') }}" class="btn btn-light border border-slate-200 text-slate-600 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100">
                    Cancel
                </a>
                <button type="submit" class="btn btn-teal bg-gradient-to-r from-teal-600 to-cyan-600 hover:from-teal-700 hover:to-cyan-700 text-white font-bold px-6 py-2.5 rounded-xl shadow-md shadow-teal-500/25 transition">
                    <i class="bi bi-check2-circle me-1.5"></i> Save Practical Marks
                </button>
            </div>
        </form>
        @endif
    </div>
</div>
@endsection
