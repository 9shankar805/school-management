@extends('layouts.app')
@section('title', 'Promote Students')

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
                    <i class="bi bi-person-up"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Promote Students Batch</h2>
                    <p class="text-slate-500 text-sm mb-0">Assign target class and section for students in <strong>{{ $schoolClass->class_name }} - {{ $section->section_name }}</strong></p>
                </div>
            </div>
        </div>

        <a href="{{ route('promotions.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100 transition">
            <i class="bi bi-arrow-left me-1.5"></i> Back to Section List
        </a>
    </div>

    @include('session-messages')

    <!-- Information Alert -->
    <div class="alert alert-warning rounded-2xl border-0 shadow-sm p-4 mb-4 bg-amber-50 text-amber-900 border-l-4 border-amber-500">
        <div class="d-flex align-items-start gap-3">
            <i class="bi bi-exclamation-triangle-fill text-amber-600 font-bold fs-4"></i>
            <div>
                <h6 class="font-bold mb-1">Academic Batch Promotion Rules</h6>
                <p class="mb-0 text-xs text-amber-800 leading-relaxed">
                    Students should be promoted only once into the new academic session. Ensure you have created the new academic session before submitting promotions.
                </p>
            </div>
        </div>
    </div>

    <!-- Student Table Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
        <form action="{{ route('promotions.store') }}" method="POST">
            @csrf

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4 pb-3 border-b border-slate-100">
                <div>
                    <h5 class="font-bold text-slate-800 mb-0">Students Promotion Register</h5>
                    <p class="text-xs text-slate-400 mb-0">Map each student to their target class & section for the new term</p>
                </div>
                
                <span class="badge bg-indigo-50 text-indigo-700 font-semibold text-xs px-3 py-1.5 rounded-full">
                    Total Students: {{ count($students ?? []) }}
                </span>
            </div>

            @isset($students)
            @if(count($students) > 0)
            <div class="table-responsive">
                <table class="table align-middle text-sm mb-0">
                    <thead>
                        <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                            <th class="py-3 px-4 rounded-s-xl">ID Card Number</th>
                            <th class="py-3 px-4">Student Name</th>
                            <th class="py-3 px-4">Current Placement</th>
                            <th class="py-3 px-4">Promote to Class <span class="text-rose-500">*</span></th>
                            <th class="py-3 px-4 rounded-e-xl">Promote to Section <span class="text-rose-500">*</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($students as $index => $student)
                        <tr>
                            <td class="py-3 px-4">
                                <input type="text" class="form-control form-control-sm rounded-lg border-slate-200 text-xs font-semibold" name="id_card_number[{{ $student->student->id }}]" value="{{ $student->id_card_number }}">
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-800">
                                {{ $student->student->first_name }} {{ $student->student->last_name }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="badge bg-slate-100 text-slate-700 font-semibold text-xs px-2.5 py-1 rounded-lg">
                                    {{ $schoolClass->class_name }} ({{ $section->section_name }})
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <select onchange="getSections(this, {{ $index }});" class="form-select form-select-sm rounded-xl border-slate-200 text-xs font-medium text-slate-800" id="inputAssignToClass{{ $index }}" name="class_id[{{ $index }}]" required>
                                    <option value="" selected disabled>Select Target Class...</option>
                                    @isset($school_classes)
                                        @foreach ($school_classes as $school_class)
                                            <option value="{{ $school_class->id }}">{{ $school_class->class_name }}</option>
                                        @endforeach
                                    @endisset
                                </select>
                            </td>
                            <td class="py-3 px-4">
                                <select class="form-select form-select-sm rounded-xl border-slate-200 text-xs font-medium text-slate-800" id="inputAssignToSection{{ $index }}" name="section_id[{{ $index }}]" required>
                                    <option value="" disabled selected>Select class first...</option>
                                </select>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <hr class="my-4 border-slate-100">

            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('promotions.index') }}" class="btn btn-light border border-slate-200 text-slate-600 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100">
                    Cancel
                </a>
                <button type="submit" class="btn btn-indigo bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold px-6 py-2.5 rounded-xl shadow-md shadow-indigo-500/25 transition">
                    <i class="bi bi-graph-up-arrow me-1.5"></i> Submit Batch Promotion
                </button>
            </div>
            @else
            <div class="text-center py-5 text-slate-400">
                <i class="bi bi-people display-3 text-slate-200 mb-3 block"></i>
                <h6 class="font-bold text-slate-600 mb-1">No Active Students Found</h6>
                <p class="mb-0 text-xs">There are no active student records found in this section to promote.</p>
            </div>
            @endif
            @endisset
        </form>
    </div>
</div>

<script>
function getSections(obj, index) {
    var class_id = obj.options[obj.selectedIndex].value;
    var url = "{{ route('get.sections.courses.by.classId') }}?class_id=" + class_id;

    fetch(url)
    .then((resp) => resp.json())
    .then(function(data) {
        var sectionSelect = document.getElementById('inputAssignToSection' + index);
        sectionSelect.options.length = 0;
        
        if (data.sections && data.sections.length > 0) {
            sectionSelect.options[0] = new Option('-- Select Section --', '');
            data.sections.forEach(function(section) {
                sectionSelect.options[sectionSelect.options.length] = new Option(section.section_name, section.id);
            });
        } else {
            sectionSelect.options[0] = new Option('No sections found', '');
        }
    })
    .catch(function(error) {
        console.log(error);
    });
}
</script>
@endsection
