<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobPosting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RecruitmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:view staff');
    }

    // ── Job Postings ──────────────────────────────────────────────────────────

    public function postings(Request $request)
    {
        $query = JobPosting::with('postedBy')->withCount('applications');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $postings = $query->latest()->paginate(20)->withQueryString();
        return view('staff.recruitment.postings', compact('postings'));
    }

    public function storePosting(Request $request)
    {
        $this->authorize('create staff');
        $data = $request->validate([
            'title'                => 'required|string|max:200',
            'department'           => 'nullable|string|max:150',
            'description'          => 'required|string',
            'requirements'         => 'nullable|string',
            'vacancies'            => 'required|integer|min:1',
            'salary_min'           => 'nullable|numeric|min:0',
            'salary_max'           => 'nullable|numeric|min:0',
            'application_deadline' => 'nullable|date',
            'status'               => 'required|in:draft,published',
        ]);

        $data['posted_by'] = auth()->id();
        JobPosting::create($data);
        return back()->with('status', 'Job posting created.');
    }

    public function updatePosting(Request $request, int $id)
    {
        $this->authorize('create staff');
        $posting = JobPosting::findOrFail($id);
        $data    = $request->validate([
            'title'                => 'required|string|max:200',
            'department'           => 'nullable|string|max:150',
            'description'          => 'required|string',
            'requirements'         => 'nullable|string',
            'vacancies'            => 'required|integer|min:1',
            'salary_min'           => 'nullable|numeric|min:0',
            'salary_max'           => 'nullable|numeric|min:0',
            'application_deadline' => 'nullable|date',
            'status'               => 'required|in:draft,published,closed',
        ]);
        $posting->update($data);
        return back()->with('status', 'Job posting updated.');
    }

    public function destroyPosting(int $id)
    {
        $this->authorize('create staff');
        JobPosting::findOrFail($id)->delete();
        return back()->with('status', 'Job posting deleted.');
    }

    // ── Applications ──────────────────────────────────────────────────────────

    public function applications(Request $request, int $postingId)
    {
        $posting = JobPosting::findOrFail($postingId);
        $query   = JobApplication::where('job_posting_id', $postingId);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $applications = $query->latest()->get();
        return view('staff.recruitment.applications', compact('posting', 'applications'));
    }

    public function updateApplication(Request $request, int $id)
    {
        $this->authorize('create staff');
        $app  = JobApplication::findOrFail($id);
        $data = $request->validate([
            'status'             => 'required|in:applied,shortlisted,interviewed,selected,rejected',
            'interview_date'     => 'nullable|date',
            'interview_time'     => 'nullable|date_format:H:i',
            'interview_location' => 'nullable|string|max:200',
            'notes'              => 'nullable|string|max:1000',
        ]);
        $app->update($data);
        return back()->with('status', 'Application updated.');
    }

    public function destroyApplication(int $id)
    {
        $this->authorize('create staff');
        $app = JobApplication::findOrFail($id);
        if ($app->resume) Storage::disk('public')->delete($app->resume);
        $app->delete();
        return back()->with('status', 'Application deleted.');
    }
}
