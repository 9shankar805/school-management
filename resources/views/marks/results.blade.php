@extends('layouts.app')

@section('content')
<div class="container py-3">
    <div class="row justify-content-start">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-11 col-xl-10">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h1 class="h3 fw-bold text-gray-800 mb-1">
                        <i class="bi bi-award me-2 text-primary"></i> View Results
                    </h1>
                    <p class="text-muted small mb-0">View and analyze finalized student marks, points, and grades.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('course.mark.create') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-pencil-square me-1"></i> Enter Marks
                    </a>
                </div>
            </div>

            @if(isset($error))
                <div class="alert alert-danger d-flex align-items-center mb-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div>{{ $error }}</div>
                </div>
            @endif

            <!-- Filter Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-3">
                    <form action="{{ route('course.mark.list.show') }}" method="GET" id="resultsFilterForm">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold text-muted mb-1">Semester</label>
                                <select class="form-select form-select-sm" name="semester_id" required>
                                    @isset($semesters)
                                        @foreach ($semesters as $semester)
                                            <option value="{{ $semester->id }}" {{ request('semester_id') == $semester->id ? 'selected' : '' }}>
                                                {{ $semester->semester_name }}
                                            </option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold text-muted mb-1">Class</label>
                                <select onchange="getSectionsAndCourses(this.value);" class="form-select form-select-sm" id="class-select" name="class_id" required>
                                    <option value="" disabled {{ !request('class_id') ? 'selected' : '' }}>Select Class</option>
                                    @isset($classes)
                                        @foreach ($classes as $school_class)
                                            <option value="{{ $school_class->id }}" {{ request('class_id') == $school_class->id ? 'selected' : '' }}>
                                                {{ $school_class->class_name }}
                                            </option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold text-muted mb-1">Section</label>
                                <select class="form-select form-select-sm" id="section-select" name="section_id">
                                    <option value="0">All Sections</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold text-muted mb-1">Course / Subject</label>
                                <select class="form-select form-select-sm" id="course-select" name="course_id" required>
                                    <option value="0">Select Course</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-filter me-1"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results Summary & Table -->
            @if(request('class_id'))
                @if(isset($marks) && count($marks) > 0)
                    @php
                        $avgMark = round($marks->avg('final_marks'), 1);
                        $maxMark = $marks->max('final_marks');
                        $minMark = $marks->min('final_marks');
                    @endphp
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm bg-white p-3">
                                <div class="text-muted small">Total Students</div>
                                <div class="h4 fw-bold mb-0 text-primary">{{ count($marks) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm bg-white p-3">
                                <div class="text-muted small">Average Mark</div>
                                <div class="h4 fw-bold mb-0 text-success">{{ $avgMark }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm bg-white p-3">
                                <div class="text-muted small">Highest / Lowest</div>
                                <div class="h4 fw-bold mb-0 text-dark">{{ $maxMark }} <span class="text-muted fs-6 fw-normal">/ {{ $minMark }}</span></div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3" style="width: 50px;">#</th>
                                        <th>Student Name</th>
                                        <th class="text-center" style="width: 140px;">Final Marks</th>
                                        <th class="text-center" style="width: 120px;">Grade Points</th>
                                        <th class="text-center" style="width: 100px;">Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($marks) && count($marks) > 0)
                                        @foreach ($marks as $index => $mark)
                                        <tr>
                                            <td class="ps-3 text-muted small">{{ $index + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2 text-primary" style="width: 32px; height: 32px; font-size: 14px;">
                                                        <i class="bi bi-person"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold text-dark">
                                                            {{ optional($mark->student)->first_name ?? 'Student' }} {{ optional($mark->student)->last_name ?? '' }}
                                                        </div>
                                                        @if(optional($mark->student)->email)
                                                            <div class="text-muted small" style="font-size: 0.75rem;">{{ $mark->student->email }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="fw-bold fs-6">{{ $mark->final_marks }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark border">{{ $mark->getAttribute('point') ?? '-' }}</span>
                                            </td>
                                            <td class="text-center">
                                                @php
                                                    $grade = $mark->getAttribute('grade') ?? '-';
                                                    $badgeClass = str_starts_with($grade, 'A') ? 'bg-success' : (str_starts_with($grade, 'B') ? 'bg-primary' : (str_starts_with($grade, 'C') ? 'bg-info text-dark' : ($grade === 'F' ? 'bg-danger' : 'bg-secondary')));
                                                @endphp
                                                <span class="badge {{ $badgeClass }} px-2 py-1">{{ $grade }}</span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <i class="bi bi-inbox fs-2 text-secondary d-block mb-2"></i>
                                                No final marks found for the selected criteria.<br>
                                                <span class="small">Make sure marks have been entered and final marks submitted.</span>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="card shadow-sm border-0 text-center py-5">
                    <div class="card-body">
                        <i class="bi bi-funnel text-muted fs-1 mb-3 d-block"></i>
                        <h5 class="fw-bold text-gray-700">Select Criteria</h5>
                        <p class="text-muted small mb-0">Please choose a Semester, Class, and Course above and click "Filter" to display marks.</p>
                    </div>
                </div>
            @endif

            @include('layouts.footer')
        </div>
    </div>
</div>

<script>
    const initialClassId = "{{ request('class_id', '') }}";
    const initialSectionId = "{{ request('section_id', '0') }}";
    const initialCourseId = "{{ request('course_id', '0') }}";

    const classDataCache = window._resultsClassCache || {};
    window._resultsClassCache = classDataCache;

    function renderSectionsAndCourses(data, selectedSection, selectedCourse) {
        var sectionSelect = document.getElementById('section-select');
        if (sectionSelect) {
            sectionSelect.innerHTML = '<option value="0">All Sections</option>';
            if (data.sections && data.sections.length > 0) {
                data.sections.forEach(function(section) {
                    var opt = document.createElement('option');
                    opt.value = section.id;
                    opt.textContent = section.section_name;
                    if (selectedSection && String(selectedSection) === String(section.id)) {
                        opt.selected = true;
                    }
                    sectionSelect.appendChild(opt);
                });
            }
        }

        var courseSelect = document.getElementById('course-select');
        if (courseSelect) {
            courseSelect.innerHTML = '<option value="0" disabled>Select Course</option>';
            if (data.courses && data.courses.length > 0) {
                data.courses.forEach(function(course) {
                    var opt = document.createElement('option');
                    opt.value = course.id;
                    opt.textContent = course.course_name;
                    if (selectedCourse && String(selectedCourse) === String(course.id)) {
                        opt.selected = true;
                    }
                    courseSelect.appendChild(opt);
                });
            }
        }
    }

    function getSectionsAndCourses(class_id, selectedSection = null, selectedCourse = null) {
        if (!class_id) return;

        if (classDataCache[class_id]) {
            renderSectionsAndCourses(classDataCache[class_id], selectedSection, selectedCourse);
            return;
        }

        var url = "{{ route('get.sections.courses.by.classId') }}?class_id=" + class_id;

        fetch(url)
        .then((resp) => resp.json())
        .then(function(data) {
            classDataCache[class_id] = data;
            renderSectionsAndCourses(data, selectedSection, selectedCourse);
        })
        .catch(function(error) {
            console.error('Error fetching sections/courses:', error);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (initialClassId) {
            getSectionsAndCourses(initialClassId, initialSectionId, initialCourseId);
        }
    });
</script>
@endsection
