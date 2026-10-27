@extends('layouts.app')
@section('title', 'Submit Feedback or Grievance')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 font-extrabold text-slate-800 mb-0">Submit Feedback / Grievance</h2>
                    <p class="text-slate-500 text-sm mb-0">Help us improve school operations or voice a concern</p>
                </div>
                <a href="{{ route('feedback.my') }}" class="btn btn-light border border-slate-200 text-slate-700 rounded-xl px-3 py-2 text-sm font-semibold">
                    <i class="bi bi-clock-history me-1"></i> My Past Feedback
                </a>
            </div>

            @include('session-messages')

            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-4 p-md-5">
                <form action="{{ route('feedback.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label font-bold text-slate-700 text-sm">Subject / Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control rounded-xl border-slate-200 py-2.5 text-sm font-medium" placeholder="e.g. Request for additional bus route stop at Baneshwor / Science lab equipment query" required>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label font-bold text-slate-700 text-sm">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select rounded-xl border-slate-200 text-sm" required>
                                <option value="academic">Academic & Teaching</option>
                                <option value="transport">Bus & Transportation</option>
                                <option value="fee_billing">Fees & Billing</option>
                                <option value="facilities">Campus Facilities & Cleanliness</option>
                                <option value="canteen">Canteen & Meals</option>
                                <option value="other" selected>General / Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-bold text-slate-700 text-sm">Priority Level <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select rounded-xl border-slate-200 text-sm" required>
                                <option value="normal" selected>Normal Inquiry</option>
                                <option value="high">High Priority</option>
                                <option value="urgent">Urgent / Critical Issue</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label font-bold text-slate-700 text-sm">Detailed Description <span class="text-danger">*</span></label>
                        <textarea name="description" rows="6" class="form-control rounded-2xl border-slate-200 text-sm" placeholder="Please describe the issue, suggestion, or feedback with as much specific detail as possible..." required></textarea>
                    </div>

                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 mb-4">
                        <div class="form-check form-switch d-flex align-items-center gap-3">
                            <input class="form-check-input ms-0" type="checkbox" role="switch" id="anonCheck" name="is_anonymous" value="1">
                            <label class="form-check-label text-sm text-slate-700 font-semibold mb-0" for="anonCheck">
                                <i class="bi bi-incognito text-slate-600 me-1"></i> Submit Anonymously
                                <div class="text-xs text-slate-400 font-normal mt-0.5">Your name and profile will be hidden from school staff. Only the message content will be submitted.</div>
                            </label>
                        </div>
                    </div>

                    <hr class="my-4 border-slate-100">

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ url()->previous() }}" class="btn btn-light border border-slate-200 rounded-xl px-4 py-2 text-slate-600 font-semibold">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-6 py-2.5 rounded-xl shadow-md shadow-emerald-500/20 transition">
                            <i class="bi bi-send-fill me-1.5"></i> Submit Ticket
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
