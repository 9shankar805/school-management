@extends('layouts.app')
@section('title', 'Practical Exams')

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
                    <i class="bi bi-clipboard2-check"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Practical & Lab Examinations</h2>
                    <p class="text-slate-500 text-sm mb-0">Schedule practical assessments, manage lab gear, and enter practical marks</p>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('exam.list.show') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100 transition">
                <i class="bi bi-journal-check me-1.5 text-indigo-600"></i> Main Exams
            </a>
            <a href="{{ route('exam.grade.system.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100 transition">
                <i class="bi bi-award me-1.5 text-teal-600"></i> Grading Systems
            </a>
        </div>
    </div>

    @include('session-messages')

    <div class="row g-4">
        <!-- Create Practical Form Card -->
        <div class="col-12 col-lg-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-b border-slate-100">
                    <div>
                        <h6 class="font-bold text-slate-800 mb-0">Schedule Practical Exam</h6>
                        <p class="text-xs text-slate-400 mb-0">Define practical subject, date & marks</p>
                    </div>
                    <span class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 d-flex align-items-center justify-content-center">
                        <i class="bi bi-plus-lg"></i>
                    </span>
                </div>

                <form method="POST" action="{{ route('practical-exams.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Exam Title <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" class="form-control rounded-xl border-slate-200 py-2.5 text-sm" required placeholder="e.g. Physics Practical Lab Assessment">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Subject / Course</label>
                            <select name="course_id" class="form-select rounded-xl border-slate-200 py-2 text-sm">
                                <option value="">-- Course --</option>
                                @foreach($courses as $c)
                                    <option value="{{ $c->id }}">{{ $c->course_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-6">
                            <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Class</label>
                            <select name="class_id" class="form-select rounded-xl border-slate-200 py-2 text-sm">
                                <option value="">-- Class --</option>
                                @foreach($classes as $cl)
                                    <option value="{{ $cl->id }}">{{ $cl->class_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Exam Date <span class="text-rose-500">*</span></label>
                        <input type="date" name="exam_date" class="form-control rounded-xl border-slate-200 py-2.5 text-sm font-medium" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Total Marks</label>
                            <input type="number" name="total_marks" value="50" min="1" class="form-control rounded-xl border-slate-200 py-2 text-sm font-bold text-slate-800">
                        </div>
                        <div class="col-6">
                            <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Pass Marks</label>
                            <input type="number" name="pass_marks" value="20" min="0" class="form-control rounded-xl border-slate-200 py-2 text-sm font-bold text-slate-800">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Venue / Lab Room</label>
                        <input type="text" name="venue" class="form-control rounded-xl border-slate-200 py-2.5 text-sm" placeholder="e.g. Science Lab B, Computer Room 3">
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Examiner Teacher</label>
                        <select name="examiner_id" class="form-select rounded-xl border-slate-200 py-2.5 text-sm">
                            <option value="">-- Select Instructor --</option>
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->fullName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label font-semibold text-slate-700 text-xs uppercase tracking-wider">Lab Equipment Required</label>
                        <textarea name="equipment_required" class="form-control rounded-xl border-slate-200 p-2.5 text-sm" rows="2" placeholder="e.g. Microscopes, Slide Samples, Vernier Calipers"></textarea>
                    </div>

                    <button type="submit" class="btn btn-teal bg-gradient-to-r from-teal-600 to-cyan-600 hover:from-teal-700 hover:to-cyan-700 text-white font-bold w-100 py-2.5 rounded-xl shadow-md shadow-teal-500/20 transition">
                        <i class="bi bi-check-circle me-1.5"></i> Schedule Practical Exam
                    </button>
                </form>
            </div>
        </div>

        <!-- Practical Exams Directory Table -->
        <div class="col-12 col-lg-8">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
                    <div>
                        <h5 class="font-bold text-slate-800 mb-0">Practical Exam Schedules</h5>
                        <p class="text-xs text-slate-400 mb-0">Active practical test rosters and mark entry sheets</p>
                    </div>
                    <span class="badge bg-teal-50 text-teal-700 font-semibold text-xs px-3 py-1 rounded-full">
                        Total Exams: {{ $practicals->total() }}
                    </span>
                </div>

                @if($practicals->count() > 0)
                <div class="table-responsive">
                    <table class="table align-middle text-sm mb-0">
                        <thead>
                            <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                                <th class="py-3 px-4 rounded-s-xl">Exam Title</th>
                                <th class="py-3 px-4">Subject</th>
                                <th class="py-3 px-4">Class</th>
                                <th class="py-3 px-4 text-center">Exam Date</th>
                                <th class="py-3 px-4 text-center">Max / Pass Marks</th>
                                <th class="py-3 px-4 rounded-e-xl text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($practicals as $p)
                            <tr>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800">{{ $p->title }}</div>
                                    <div class="text-xs text-slate-400">Venue: {{ $p->venue ?? 'Main Lab' }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="badge bg-slate-100 text-slate-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                        {{ $p->course?->course_name ?? 'General Science' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="badge bg-teal-50 text-teal-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                        {{ $p->schoolClass?->class_name ?? 'All Classes' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="font-semibold text-slate-800">{{ $p->exam_date->format('d M Y') }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $p->exam_date->diffForHumans() }}</div>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="badge bg-slate-100 text-slate-800 font-bold text-xs px-2.5 py-1 rounded-lg">
                                        {{ $p->total_marks }} <span class="text-slate-400 font-normal">(Pass: {{ $p->pass_marks }})</span>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <a href="{{ route('practical-exams.show', $p->id) }}" class="btn btn-sm btn-teal bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs py-1.5 px-3 rounded-xl shadow-sm transition">
                                        <i class="bi bi-pencil-square me-1"></i> Marks Entry
                                    </a>
                                    <a href="{{ route('practical-exams.pdf', $p->id) }}" class="btn btn-sm btn-light border border-slate-200 text-slate-700 text-xs py-1.5 px-2.5 rounded-xl ms-1" target="_blank" title="Download PDF Sheet">
                                        <i class="bi bi-file-earmark-pdf-fill text-rose-500"></i>
                                    </a>
                                    <form method="POST" action="{{ route('practical-exams.destroy', $p->id) }}" class="d-inline" onsubmit="return confirm('Delete practical exam?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger text-xs py-1.5 px-2.5 rounded-xl ms-1">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $practicals->links() }}
                </div>
                @else
                <div class="text-center py-5 text-slate-400">
                    <i class="bi bi-clipboard2-check display-3 text-slate-200 mb-3 block"></i>
                    <h6 class="font-bold text-slate-600 mb-1">No Practical Exams Scheduled</h6>
                    <p class="mb-0 text-xs">Create a new practical test assessment using the form on the left.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
