@extends('layouts.app')
@section('title', 'Edit Notice')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 font-extrabold text-slate-800 mb-0">Edit School Notice</h2>
            <p class="text-slate-500 text-sm mb-0">Update notice details, audience, or message content</p>
        </div>
        <a href="{{ route('notice.index') }}" class="btn btn-light border border-slate-200 text-slate-700 font-semibold px-4 py-2 rounded-xl">
            <i class="bi bi-arrow-left me-1"></i> Back to Notices
        </a>
    </div>

    @include('session-messages')

    <div class="row">
        <div class="col-12 col-xl-9 mx-auto">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 p-md-5">
                <form action="{{ route('notice.update', $notice->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label font-semibold text-slate-700 text-sm">Notice Title / Subject <span class="text-rose-500">*</span></label>
                            <input type="text" name="title" value="{{ old('title', $notice->title) }}" class="form-control rounded-xl border-slate-200 py-2.5 text-sm" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label font-semibold text-slate-700 text-sm">Priority</label>
                            <select name="priority" class="form-select rounded-xl border-slate-200 py-2.5 text-sm">
                                <option value="normal" {{ old('priority', $notice->priority) === 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="urgent" {{ old('priority', $notice->priority) === 'urgent' ? 'selected' : '' }}>Urgent Alert</option>
                                <option value="holiday" {{ old('priority', $notice->priority) === 'holiday' ? 'selected' : '' }}>Holiday Notice</option>
                                <option value="exam" {{ old('priority', $notice->priority) === 'exam' ? 'selected' : '' }}>Exam Schedule</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label font-semibold text-slate-700 text-sm">Target Audience</label>
                            <select name="audience" class="form-select rounded-xl border-slate-200 py-2.5 text-sm">
                                <option value="all" {{ old('audience', $notice->audience) === 'all' ? 'selected' : '' }}>Everyone (All)</option>
                                <option value="parents" {{ old('audience', $notice->audience) === 'parents' ? 'selected' : '' }}>Parents Only</option>
                                <option value="students" {{ old('audience', $notice->audience) === 'students' ? 'selected' : '' }}>Students Only</option>
                                <option value="teachers" {{ old('audience', $notice->audience) === 'teachers' ? 'selected' : '' }}>Teachers & Staff</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label font-semibold text-slate-700 text-sm">Expiry Date</label>
                            <input type="date" name="expiry_date" value="{{ old('expiry_date', $notice->expiry_date?->toDateString()) }}" class="form-control rounded-xl border-slate-200 py-2.5 text-sm">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label font-semibold text-slate-700 text-sm">Notice Body Content <span class="text-rose-500">*</span></label>
                        <div class="rounded-xl overflow-hidden border border-slate-200">
                            @include('components.ckeditor.editor', ['name' => 'notice', 'value' => $notice->notice])
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label font-semibold text-slate-700 text-sm">Attachment (PDF or Image)</label>
                        @if($notice->attachment_path)
                        <div class="mb-2 text-xs text-slate-500">
                            Current attachment: <a href="{{ asset('storage/' . $notice->attachment_path) }}" target="_blank" class="text-indigo-600 font-semibold underline">View Current File</a>
                        </div>
                        @endif
                        <input type="file" name="attachment" class="form-control rounded-xl border-slate-200 text-sm">
                    </div>

                    <hr class="my-4 border-slate-100">

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('notice.index') }}" class="btn btn-light border border-slate-200 text-slate-600 font-semibold px-4 py-2.5 rounded-xl">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-6 py-2.5 rounded-xl transition">
                            <i class="bi bi-check2-circle me-1"></i> Update Notice
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
