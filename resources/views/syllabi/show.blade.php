@extends('layouts.app')
@section('title', 'Syllabus Directory')

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
                    <i class="bi bi-journal-bookmark-fill"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Course Syllabus Directory</h2>
                    <p class="text-slate-500 text-sm mb-0">View, search, and download official subject syllabi and academic course outlines</p>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            @unlessrole('student|parent')
            <a href="{{ route('class.syllabus.create') }}" class="btn btn-emerald bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold px-4 py-2.5 rounded-xl shadow-md shadow-emerald-500/20 transition">
                <i class="bi bi-plus-circle me-1.5"></i> Create Syllabus
            </a>
            @endunlessrole
            <a href="{{ url()->previous() }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100 transition">
                <i class="bi bi-arrow-left me-1.5"></i> Back to Courses
            </a>
        </div>
    </div>

    @include('session-messages')

    <!-- Main Card Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
            <div>
                <h5 class="font-bold text-slate-800 mb-0">Available Syllabi</h5>
                <p class="text-xs text-slate-400 mb-0">Published course documents for students & teachers</p>
            </div>
            <span class="badge bg-emerald-50 text-emerald-700 font-semibold text-xs px-3 py-1 rounded-full">
                Total Files: {{ count($syllabi ?? []) }}
            </span>
        </div>

        @if(isset($syllabi) && count($syllabi) > 0)
        <div class="table-responsive">
            <table class="table align-middle text-sm mb-0">
                <thead>
                    <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                        <th class="py-3 px-4 rounded-s-xl">Syllabus Title</th>
                        <th class="py-3 px-4">Subject / Course</th>
                        <th class="py-3 px-4">Class</th>
                        <th class="py-3 px-4 rounded-e-xl text-end">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($syllabi as $syllabus)
                    <tr>
                        <td class="py-3 px-4 font-bold text-slate-800">
                            <div class="d-flex align-items-center gap-2">
                                <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 d-flex align-items-center justify-content-center flex-shrink-0">
                                    <i class="bi bi-file-earmark-pdf-fill"></i>
                                </span>
                                <div>
                                    <div>{{ $syllabus->syllabus_name }}</div>
                                    <div class="text-xs text-slate-400 font-normal">Uploaded for academic session</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="badge bg-slate-100 text-slate-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                {{ $syllabus->course->course_name ?? 'General' }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="badge bg-emerald-50 text-emerald-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                {{ $syllabus->schoolClass->class_name ?? 'All Classes' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-end">
                            <a href="{{ asset('storage/'.$syllabus->syllabus_file_path) }}" target="_blank" class="btn btn-sm btn-emerald bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs py-1.5 px-3 rounded-xl shadow-sm transition">
                                <i class="bi bi-download me-1"></i> Download Syllabus
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5 text-slate-400">
            <i class="bi bi-journal-x display-2 text-slate-200 mb-3 block"></i>
            <h6 class="font-bold text-slate-600 mb-1">No Syllabi Found</h6>
            <p class="mb-4 text-xs">Upload course outlines or subject syllabi for students and teachers to download.</p>
            @can('create syllabi')
            <a href="{{ route('class.syllabus.create') }}" class="btn btn-emerald bg-emerald-600 text-white font-bold px-4 py-2 rounded-xl text-sm shadow-md shadow-emerald-500/20">
                <i class="bi bi-plus-circle me-1.5"></i> Upload Syllabus Now
            </a>
            @endcan
        </div>
        @endif
    </div>
</div>
@endsection
