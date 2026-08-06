@extends('layouts.app')
@section('title', 'Online Exams')

@section('content')
<style>
  .header-icon-box-online {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%) !important;
    color: #ffffff !important;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
  }
  .header-icon-box-online i {
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
                <div class="header-icon-box-online">
                    <i class="bi bi-pc-display"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Online Exams & Quizzes</h2>
                    <p class="text-slate-500 text-sm mb-0">Computer-based testing (CBT), online quizzes, and automated scoring</p>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            @unlessrole('student|parent')
            <a href="{{ route('online-exams.create') }}" class="btn btn-indigo bg-gradient-to-r from-sky-600 to-blue-600 hover:from-sky-700 hover:to-blue-700 text-white font-bold px-4 py-2.5 rounded-xl shadow-md shadow-sky-500/20 transition">
                <i class="bi bi-plus-circle me-1.5"></i> Create Online Exam
            </a>
            @endunlessrole
            <a href="{{ route('exam.list.show') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100 transition">
                <i class="bi bi-journal-check me-1.5 text-blue-600"></i> Offline Exams
            </a>
        </div>
    </div>

    @if(session('status'))
    <div class="alert alert-success rounded-xl shadow-sm border-0 mb-4 bg-emerald-50 text-emerald-800 border-l-4 border-emerald-500">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger rounded-xl shadow-sm border-0 mb-4 bg-rose-50 text-rose-800 border-l-4 border-rose-500">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
    </div>
    @endif

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-12 col-md-5">
                <label class="form-label font-semibold text-slate-700 text-sm">Filter Class</label>
                <select name="class_id" class="form-select rounded-xl border-slate-200 py-2 text-sm text-slate-700">
                    <option value="">-- All Classes --</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>{{ $class->class_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-5">
                <label class="form-label font-semibold text-slate-700 text-sm">Filter Status</label>
                <select name="status" class="form-select rounded-xl border-slate-200 py-2 text-sm text-slate-700">
                    <option value="">-- All Statuses --</option>
                    <option value="draft" @selected(request('status')==='draft')>Draft</option>
                    <option value="published" @selected(request('status')==='published')>Published</option>
                    <option value="closed" @selected(request('status')==='closed')>Closed</option>
                </select>
            </div>

            <div class="col-12 col-md-2">
                <button class="btn btn-sky bg-sky-600 hover:bg-sky-700 text-white font-bold w-100 py-2 rounded-xl shadow-sm">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
            <div>
                <h5 class="font-bold text-slate-800 mb-0">Online Examination Directory</h5>
                <p class="text-xs text-slate-400 mb-0">Manage computer-based test questions, duration, and student attempts</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle text-sm mb-0">
                <thead>
                    <tr class="text-xs font-semibold text-slate-400 uppercase tracking-wider bg-slate-50">
                        <th class="py-3 px-4 rounded-s-xl">Exam Title</th>
                        <th class="py-3 px-4">Subject / Class</th>
                        <th class="py-3 px-4 text-center">Questions</th>
                        <th class="py-3 px-4 text-center">Duration</th>
                        <th class="py-3 px-4 text-center">Marks</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 rounded-e-xl text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($onlineExams as $exam)
                    <tr>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-800">{{ $exam->title }}</div>
                            <div class="text-xs text-slate-400">Created by {{ $exam->createdBy->fullName ?? 'Admin' }}</div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="badge bg-sky-50 text-sky-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                {{ $exam->course?->course_name ?? 'General' }}
                            </span>
                            <div class="text-xs text-slate-500 mt-0.5">{{ $exam->schoolClass?->class_name ?? 'All Classes' }}</div>
                        </td>
                        <td class="py-3 px-4 text-center font-semibold text-slate-700">
                            {{ $exam->questions_count ?? $exam->questions()->count() }} Qs
                        </td>
                        <td class="py-3 px-4 text-center font-medium text-slate-600 text-xs">
                            <i class="bi bi-clock me-1 text-slate-400"></i> {{ $exam->duration_minutes }} min
                        </td>
                        <td class="py-3 px-4 text-center font-bold text-slate-800">
                            {{ $exam->total_marks }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($exam->status === 'published')
                            <span class="badge bg-emerald-100 text-emerald-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                <i class="bi bi-broadcast me-1"></i> Published
                            </span>
                            @elseif($exam->status === 'draft')
                            <span class="badge bg-amber-100 text-amber-700 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                <i class="bi bi-pencil me-1"></i> Draft
                            </span>
                            @else
                            <span class="badge bg-slate-100 text-slate-600 text-xs px-2.5 py-1 rounded-lg font-semibold">
                                <i class="bi bi-lock me-1"></i> Closed
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-end">
                            <div class="btn-group shadow-sm rounded-xl overflow-hidden" role="group">
                                <a href="{{ route('online-exams.show', $exam->id) }}" class="btn btn-sm btn-light border border-slate-200 text-slate-700 font-semibold text-xs py-1.5 px-2.5" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('online-exams.questions', $exam->id) }}" class="btn btn-sm btn-light border border-slate-200 text-indigo-600 font-semibold text-xs py-1.5 px-2.5" title="Manage Questions">
                                    <i class="bi bi-list-ol"></i>
                                </a>
                                <a href="{{ route('online-exams.attempts', $exam->id) }}" class="btn btn-sm btn-light border border-slate-200 text-sky-600 font-semibold text-xs py-1.5 px-2.5" title="Student Attempts">
                                    <i class="bi bi-people"></i>
                                </a>
                                @can('create exams')
                                <form method="POST" action="{{ route('online-exams.toggle-publish', $exam->id) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm {{ $exam->status==='published' ? 'btn-warning text-dark' : 'btn-success text-white' }} text-xs py-1.5 px-2.5" title="{{ $exam->status==='published' ? 'Unpublish' : 'Publish' }}">
                                        <i class="bi bi-{{ $exam->status==='published' ? 'eye-slash' : 'broadcast' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('online-exams.destroy', $exam->id) }}" class="d-inline" onsubmit="return confirm('Delete this online exam?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger text-xs py-1.5 px-2.5"><i class="bi bi-trash"></i></button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-slate-400 py-5">
                            <i class="bi bi-pc-display display-3 text-slate-200 mb-2 block"></i>
                            <p class="mb-0 text-sm">No online exams found matching your filter criteria.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            {{ $onlineExams->links() }}
        </div>
    </div>
</div>
@endsection
