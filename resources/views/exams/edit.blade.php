@extends('layouts.app')
@section('title', 'Edit Exam')

@section('content')
<style>
  .header-icon-box-exam {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
    color: #ffffff !important;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
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
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Edit Exam</h2>
                    <p class="text-slate-500 text-sm mb-0">Update the exam details for this scheduled examination</p>
                </div>
            </div>
        </div>

        <a href="{{ route('exam.list.show') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100 transition">
            <i class="bi bi-arrow-left me-1.5"></i> Back to Exam List
        </a>
    </div>

    @include('session-messages')

    <div class="row g-4">
        <div class="col-12 col-xl-7 mx-auto">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
                <form action="{{ route('exam.update', $exam->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">

                    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
                        <div>
                            <h5 class="font-bold text-slate-800 mb-0">Exam Specifications</h5>
                            <p class="text-xs text-slate-400 mb-0">Modify the class, course, semester, or schedule timing</p>
                        </div>
                        <span class="badge bg-amber-50 text-amber-700 text-xs px-3 py-1 rounded-full font-semibold">Exam #{{ $exam->id }}</span>
                    </div>

                    <div class="row g-3 mb-4">
                        <!-- Term / Semester -->
                        <div class="col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-sm">Select Term / Semester <span class="text-rose-500">*</span></label>
                            <select class="form-select rounded-xl border-slate-200 py-2.5 text-sm text-slate-800 font-medium" name="semester_id" required>
                                @isset($semesters)
                                    @foreach ($semesters as $semester)
                                    <option value="{{ $semester->id }}" {{ old('semester_id', $exam->semester_id) == $semester->id ? 'selected' : '' }}>
                                        {{ $semester->semester_name }}
                                    </option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>

                        <!-- Class Select -->
                        <div class="col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-sm">Select Class <span class="text-rose-500">*</span></label>
                            <select onchange="getCourses(this);" id="class-select" class="form-select rounded-xl border-slate-200 py-2.5 text-sm text-slate-800 font-medium" name="class_id" required>
                                <option value="" disabled>-- Select Class --</option>
                                @isset($classes)
                                    @foreach ($classes as $school_class)
                                    <option value="{{ $school_class->id }}" {{ old('class_id', $exam->class_id) == $school_class->id ? 'selected' : '' }}>
                                        {{ $school_class->class_name }}
                                    </option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>

                        <!-- Course Select -->
                        <div class="col-12">
                            <label class="form-label font-semibold text-slate-700 text-sm">Select Subject / Course <span class="text-rose-500">*</span></label>
                            <select class="form-select rounded-xl border-slate-200 py-2.5 text-sm text-slate-800 font-medium" id="course-select" name="course_id" required>
                                {{-- Seeded with current value; JS will refresh on class change --}}
                                @if($exam->course)
                                <option value="{{ $exam->course_id }}" selected>{{ $exam->course->course_name }}</option>
                                @else
                                <option value="" disabled selected>Select a class first to load subjects...</option>
                                @endif
                            </select>
                        </div>

                        <!-- Exam Title -->
                        <div class="col-12">
                            <label class="form-label font-semibold text-slate-700 text-sm">Exam Title / Name <span class="text-rose-500">*</span></label>
                            <input type="text"
                                   class="form-control rounded-xl border-slate-200 py-2.5 text-sm text-slate-800 font-medium @error('exam_name') is-invalid @enderror"
                                   name="exam_name"
                                   value="{{ old('exam_name', $exam->exam_name) }}"
                                   placeholder="e.g. First Term Final Exam, Unit Test 1, Mid-Term..."
                                   required>
                            @error('exam_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Start Date -->
                        <div class="col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-sm">Start Date & Time <span class="text-rose-500">*</span></label>
                            <input type="datetime-local"
                                   class="form-control rounded-xl border-slate-200 py-2.5 text-sm text-slate-800 font-medium @error('start_date') is-invalid @enderror"
                                   id="inputStarts"
                                   name="start_date"
                                   value="{{ old('start_date', $exam->start_date?->format('Y-m-d\TH:i')) }}"
                                   required>
                            @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- End Date -->
                        <div class="col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-sm">End Date & Time <span class="text-rose-500">*</span></label>
                            <input type="datetime-local"
                                   class="form-control rounded-xl border-slate-200 py-2.5 text-sm text-slate-800 font-medium @error('end_date') is-invalid @enderror"
                                   id="inputEnds"
                                   name="end_date"
                                   value="{{ old('end_date', $exam->end_date?->format('Y-m-d\TH:i')) }}"
                                   required>
                            @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <hr class="my-4 border-slate-100">

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('exam.list.show') }}" class="btn btn-light border border-slate-200 text-slate-600 font-semibold px-4 py-2.5 rounded-xl shadow-sm">
                            Cancel
                        </a>
                        <button type="submit" class="btn bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold px-6 py-2.5 rounded-xl shadow-md shadow-amber-500/25 transition">
                            <i class="bi bi-check2-circle me-1.5"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
var currentCourseId = {{ $exam->course_id ?? 'null' }};

function getCourses(obj, preSelectId) {
    var class_id = obj.options[obj.selectedIndex].value;
    var url = "{{ route('get.sections.courses.by.classId') }}?class_id=" + class_id;

    fetch(url)
    .then(resp => resp.json())
    .then(function(data) {
        var courseSelect = document.getElementById('course-select');
        courseSelect.options.length = 0;

        if (data.courses && data.courses.length > 0) {
            courseSelect.options[0] = new Option('-- Select Subject / Course --', '');
            data.courses.forEach(function(course) {
                var opt = new Option(course.course_name, course.id);
                if (preSelectId && course.id == preSelectId) {
                    opt.selected = true;
                }
                courseSelect.add(opt);
            });
        } else {
            courseSelect.options[0] = new Option('No courses found for this class', '');
        }
    })
    .catch(function(error) { console.log(error); });
}

// On page load, trigger course load for the already-selected class so the
// dropdown is fully populated (not just the single seeded option).
document.addEventListener('DOMContentLoaded', function () {
    var classSelect = document.getElementById('class-select');
    if (classSelect && classSelect.value) {
        getCourses(classSelect, currentCourseId);
    }
});
</script>
@endsection
