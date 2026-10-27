@extends('layouts.app')
@section('title', 'Start a Discussion Topic')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Ask a Question / Start Topic</h2>
                    <p class="text-slate-500 text-sm mb-0">Post a question to classmates and teachers for academic help</p>
                </div>
                <a href="{{ route('forum.index') }}" class="btn btn-light border border-slate-200 text-slate-700 rounded-xl px-3 py-2 text-sm font-semibold">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>

            @include('session-messages')

            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-4 p-md-5">
                <form action="{{ route('forum.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label font-bold text-slate-700 text-sm">Topic Title / Question Summary <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control rounded-xl border-slate-200 py-2.5 text-sm font-medium" placeholder="e.g. How do we calculate internal resistance in Class 10 Physics Ohm's experiment?" required>
                        <div class="form-text text-xs text-slate-400">Be specific and imagine you're asking a question to your subject teacher.</div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label font-bold text-slate-700 text-sm">Subject / Course <span class="text-xs text-slate-400">(Optional)</span></label>
                            <select name="course_id" class="form-select rounded-xl border-slate-200 text-sm">
                                <option value="">General / All Subjects</option>
                                @foreach($courses as $c)
                                <option value="{{ $c->id }}">{{ $c->course_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-bold text-slate-700 text-sm">Class Grade <span class="text-xs text-slate-400">(Optional)</span></label>
                            <select name="class_id" class="form-select rounded-xl border-slate-200 text-sm">
                                <option value="">General / All Classes</option>
                                @foreach($classes as $cls)
                                <option value="{{ $cls->id }}">Class {{ $cls->class_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label font-bold text-slate-700 text-sm">Question Details & Description <span class="text-danger">*</span></label>
                        <textarea name="content" rows="7" class="form-control rounded-2xl border-slate-200 text-sm" placeholder="Provide all the details, formulas, context, or code of the problem you're facing..." required></textarea>
                    </div>

                    <hr class="my-4 border-slate-100">

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('forum.index') }}" class="btn btn-light border border-slate-200 rounded-xl px-4 py-2 text-slate-600 font-semibold">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary bg-sky-600 hover:bg-sky-700 text-white font-bold px-5 py-2.5 rounded-xl transition">
                            <i class="bi bi-check2-circle me-1.5"></i> Publish Question
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
