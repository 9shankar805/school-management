<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\School;
use Illuminate\Http\Request;

class SaasFeedbackController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Cross-School User Feedback & Grievances Console.
     */
    public function index(Request $request)
    {
        $query = Feedback::withoutGlobalScopes()->with(['user', 'resolver', 'school'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('submitter_name', 'like', "%{$search}%");
            });
        }

        $feedbacks = $query->paginate(20)->withQueryString();

        $stats = [
            'total'     => Feedback::withoutGlobalScopes()->count(),
            'pending'   => Feedback::withoutGlobalScopes()->where('status', 'pending')->count(),
            'in_review' => Feedback::withoutGlobalScopes()->where('status', 'in_review')->count(),
            'resolved'  => Feedback::withoutGlobalScopes()->where('status', 'resolved')->count(),
            'urgent'    => Feedback::withoutGlobalScopes()->where('priority', 'urgent')->where('status', '!=', 'resolved')->count(),
        ];

        $schools = School::orderBy('name')->select('id', 'name')->get();

        return view('super-admin.feedback.index', compact('feedbacks', 'stats', 'schools'));
    }

    /**
     * View detailed user feedback ticket.
     */
    public function show($id)
    {
        $feedback = Feedback::withoutGlobalScopes()->with(['user', 'resolver', 'school'])->findOrFail($id);
        $school = $feedback->school ?? ($feedback->school_id ? School::find($feedback->school_id) : null);

        return view('super-admin.feedback.show', compact('feedback', 'school'));
    }

    /**
     * Update feedback ticket status and administrative response.
     */
    public function updateStatus(Request $request, $id)
    {
        $feedback = Feedback::withoutGlobalScopes()->findOrFail($id);

        $validated = $request->validate([
            'status'         => 'required|in:pending,in_review,resolved,rejected',
            'admin_response' => 'nullable|string',
        ]);

        $feedback->status = $validated['status'];
        $feedback->admin_response = $validated['admin_response'] ?? $feedback->admin_response;

        if ($validated['status'] === 'resolved') {
            $feedback->resolved_by = auth()->id();
            $feedback->resolved_at = now();
        }

        $feedback->save();

        return back()->with('success', "Ticket #{$feedback->id} updated successfully.");
    }
}
