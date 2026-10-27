@extends('layouts.app')
@section('title', 'Upload Syllabus')

@section('content')
<style>
  .header-icon-box-syllabus {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;
    color: #ffffff !important;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
  }
  .header-icon-box-syllabus i {
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
                <div class="header-icon-box-syllabus">
                    <i class="bi bi-journal-plus"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 dark:text-slate-100 mb-0">Upload Course Syllabus</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mb-0">Publish official course outlines and syllabi for classes and subjects</p>
                </div>
            </div>
        </div>

        <a href="{{ route('course.syllabus.index') }}" class="btn btn-light border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100 dark:hover:bg-slate-700 transition">
            <i class="bi bi-arrow-left me-1.5"></i> Back to Syllabus Directory
        </a>
    </div>

    @include('session-messages')

    <div class="row g-4">
        <div class="col-12 col-xl-6 mx-auto">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-4 p-md-5">
                <form action="{{ route('syllabus.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">

                    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <h5 class="font-bold text-slate-800 dark:text-slate-100 mb-0">Syllabus Details</h5>
                            <p class="text-xs text-slate-400 mb-0">Target class, subject, title, and document file</p>
                        </div>
                        <span class="badge bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-xs px-3 py-1 rounded-full font-semibold border border-emerald-100 dark:border-emerald-800">Session #{{ $current_school_session_id }}</span>
                    </div>

                    <div class="row g-3 mb-4">
                        <!-- Select Class -->
                        <div class="col-md-6">
                            <label class="form-label font-semibold text-slate-700 dark:text-slate-300 text-sm">Select Class <span class="text-rose-500">*</span></label>
                            <select onchange="getCourses(this);" class="form-select rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 text-sm text-slate-800 dark:text-slate-100 font-medium focus:ring-2 focus:ring-emerald-500/20" name="class_id" required>
                                <option value="" selected disabled>-- Select Class --</option>
                                @isset($school_classes)
                                    @foreach ($school_classes as $school_class)
                                    <option value="{{ $school_class->id }}">{{ $school_class->class_name }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>

                        <!-- Select Course -->
                        <div class="col-md-6">
                            <label class="form-label font-semibold text-slate-700 dark:text-slate-300 text-sm">Select Subject / Course <span class="text-rose-500">*</span></label>
                            <select class="form-select rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 text-sm text-slate-800 dark:text-slate-100 font-medium focus:ring-2 focus:ring-emerald-500/20" id="course-select" name="course_id" required>
                                <option value="" disabled selected>Select class first...</option>
                            </select>
                        </div>

                        <!-- Syllabus Name -->
                        <div class="col-12">
                            <label class="form-label font-semibold text-slate-700 dark:text-slate-300 text-sm">Syllabus Title / Name <span class="text-rose-500">*</span></label>
                            <input type="text" class="form-control rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2.5 text-sm text-slate-800 dark:text-slate-100 font-medium focus:ring-2 focus:ring-emerald-500/20" id="syllabus-name" name="syllabus_name" placeholder="e.g. Mathematics Annual Curriculum & Syllabus 2026" required>
                        </div>

                        <!-- File Upload -->
                        <div class="col-12">
                            <label class="form-label font-semibold text-slate-700 dark:text-slate-300 text-sm">Upload File (PDF / Word / Zip) <span class="text-rose-500">*</span></label>
                            <input type="file" name="file" class="form-control rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-2 text-sm text-slate-800 dark:text-slate-100" id="syllabus-file" accept=".pdf,.doc,.docx,.xlsx,.txt,.zip" required>
                            <div class="form-text text-xs text-slate-400 mt-1">Supported formats: PDF, DOCX, XLSX, TXT, ZIP (Max 10MB)</div>
                        </div>
                    </div>

                    <hr class="my-4 border-slate-100 dark:border-slate-800">

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('course.syllabus.index') }}" class="btn btn-light border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100 dark:hover:bg-slate-700">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-emerald bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold px-6 py-2.5 rounded-xl shadow-md shadow-emerald-500/25 transition cursor-pointer">
                            <i class="bi bi-cloud-upload me-1.5"></i> Publish Syllabus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function getCourses(obj) {
    var class_id = obj.options[obj.selectedIndex].value;
    var url = "{{ route('get.sections.courses.by.classId') }}?class_id=" + class_id;

    fetch(url)
    .then((resp) => resp.json())
    .then(function(data) {
        var courseSelect = document.getElementById('course-select');
        courseSelect.options.length = 0;

        if (data.courses && data.courses.length > 0) {
            courseSelect.options[0] = new Option('-- Select Subject / Course --', '');
            data.courses.forEach(function(course) {
                courseSelect.options[courseSelect.options.length] = new Option(course.course_name, course.id);
            });
        } else {
            courseSelect.options[0] = new Option('No courses found for this class', '');
        }
    })
    .catch(function(error) {
        console.log(error);
    });
}
</script>
@endsection