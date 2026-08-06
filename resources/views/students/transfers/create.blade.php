@extends('layouts.app')
@section('title', 'Transfer Student')

@section('content')
<style>
  .header-icon-box-transfer {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: linear-gradient(135deg, #6366f1 0%, #3b82f6 100%) !important;
    color: #ffffff !important;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
  }
  .header-icon-box-transfer i {
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
                <div class="header-icon-box-transfer">
                    <i class="bi bi-person-gear"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Transfer Student: {{ $student->first_name }} {{ $student->last_name }}</h2>
                    <p class="text-slate-500 text-sm mb-0">Request a class or section transfer for this student</p>
                </div>
            </div>
        </div>

        <a href="{{ route('student.transfers.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100 transition">
            <i class="bi bi-arrow-left me-1.5"></i> Back to Transfers List
        </a>
    </div>

    @include('session-messages')

    <div class="row g-4">
        <div class="col-12 col-xl-6 mx-auto">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
                <form action="{{ route('student.transfer.store', $student->id) }}" method="POST">
                    @csrf

                    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
                        <div>
                            <h5 class="font-bold text-slate-800 mb-0">Transfer Specification</h5>
                            <p class="text-xs text-slate-400 mb-0">Current & target class/section</p>
                        </div>
                        <span class="badge bg-indigo-50 text-indigo-700 text-xs px-3 py-1 rounded-full font-semibold">Student ID: #{{ $student->id }}</span>
                    </div>

                    <div class="mb-4 p-3 bg-slate-50 border border-slate-200 rounded-xl">
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Current Placement</div>
                        <div class="font-bold text-slate-800 text-sm">
                            Class: {{ $fromPromotion->schoolClass->class_name ?? 'Unassigned' }} | Section: {{ $fromPromotion->section->section_name ?? 'Unassigned' }}
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-sm">Target Class <span class="text-rose-500">*</span></label>
                            <select name="to_class_id" class="form-select rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800" required>
                                <option value="" selected disabled>-- Select Class --</option>
                                @foreach($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->class_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-sm">Target Section</label>
                            <select name="to_section_id" class="form-select rounded-xl border-slate-200 py-2.5 text-sm font-medium text-slate-800">
                                <option value="">-- Select Section --</option>
                                @foreach($sections as $s)
                                <option value="{{ $s->id }}">{{ $s->section_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label font-semibold text-slate-700 text-sm">Reason for Transfer</label>
                            <textarea name="reason" rows="3" class="form-control rounded-xl border-slate-200 p-3 text-sm text-slate-800" placeholder="e.g. Parental request, academic reallocation, section balancing..."></textarea>
                        </div>
                    </div>

                    <hr class="my-4 border-slate-100">

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('student.transfers.index') }}" class="btn btn-light border border-slate-200 text-slate-600 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-indigo bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-bold px-6 py-2.5 rounded-xl shadow-md shadow-indigo-500/25 transition">
                            <i class="bi bi-send me-1.5"></i> Submit Transfer Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
