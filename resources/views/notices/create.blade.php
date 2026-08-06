@extends('layouts.app')
@section('title', 'Post New Notice')

@section('content')
<style>
  .header-icon-box-blue {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%) !important;
    color: #ffffff !important;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
  }
  .header-icon-box-blue i {
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
                <div class="header-icon-box-blue">
                    <i class="bi bi-megaphone-fill"></i>
                </div>
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Post New School Notice</h2>
                    <p class="text-slate-500 text-sm mb-0">Publish official circulars and notices to student and parent bulletin boards</p>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('ai_features.web.notice') }}" class="btn btn-light border border-slate-200 text-indigo-700 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-indigo-50 transition">
                <i class="bi bi-robot me-1.5 text-indigo-600"></i> AI Notice Assistant
            </a>
            <a href="{{ route('communication.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100 transition">
                <i class="bi bi-arrow-left me-1.5"></i> Back to Hub
            </a>
        </div>
    </div>

    <!-- Status Messages -->
    @include('session-messages')

    <div class="row g-4">
        <div class="col-12 col-xl-9 mx-auto">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
                <form action="{{ route('notice.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">

                    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-b border-slate-100">
                        <div>
                            <h5 class="font-bold text-slate-800 mb-0">Notice Details & Content</h5>
                            <p class="text-xs text-slate-400 mb-0">Fill in the notice details to display on school dashboards</p>
                        </div>
                        <span class="badge bg-blue-100 text-blue-700 text-xs px-3 py-1 rounded-full font-semibold">Session ID: #{{ $current_school_session_id }}</span>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label font-semibold text-slate-700 text-sm">Notice Title / Subject <span class="text-rose-500">*</span></label>
                            <input type="text" name="title" class="form-control rounded-xl border-slate-200 py-2.5 text-sm text-slate-800 font-medium focus:ring-2 focus:ring-blue-500/20" placeholder="e.g. Annual Sports Day 2026 Announcement" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-sm">Target Audience</label>
                            <select name="audience" class="form-select rounded-xl border-slate-200 py-2.5 text-sm text-slate-700">
                                <option value="all">Everyone (All Parents & Students)</option>
                                <option value="parents">Parents Only</option>
                                <option value="students">Students Only</option>
                                <option value="teachers">Teachers & Staff</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-semibold text-slate-700 text-sm">Expiry Date <span class="text-xs text-slate-400 font-normal">(Optional)</span></label>
                            <input type="date" name="expiry_date" class="form-control rounded-xl border-slate-200 py-2.5 text-sm text-slate-700">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label font-semibold text-slate-700 text-sm">Notice Body Content <span class="text-rose-500">*</span></label>
                        <div class="rounded-xl overflow-hidden border border-slate-200">
                            @include('components.ckeditor.editor', ['name' => 'notice'])
                        </div>
                    </div>

                    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200/80 mb-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="font-bold text-slate-800 text-sm mb-1"><i class="bi bi-whatsapp text-emerald-600 me-1.5"></i> Send Copy via WhatsApp?</h6>
                                <p class="text-xs text-slate-500 mb-0">Also broadcast this notice as an instant WhatsApp notification to parents</p>
                            </div>
                            <a href="{{ route('communication.bulk') }}" class="btn btn-sm btn-emerald bg-emerald-600 text-white font-bold px-3 py-1.5 rounded-lg text-xs">
                                <i class="bi bi-send me-1"></i> Use Broadcaster
                            </a>
                        </div>
                    </div>

                    <hr class="my-4 border-slate-100">

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('communication.index') }}" class="btn btn-light border border-slate-200 text-slate-600 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-slate-100">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-blue bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold px-6 py-2.5 rounded-xl shadow-md shadow-blue-500/20 transition">
                            <i class="bi bi-check2-circle me-1.5"></i> Publish Notice
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection