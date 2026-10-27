<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Admin / Management Feedback & Grievance Console.
     */
    public function index(Request $request)
    {
        $this->authorize('view staff'); // admin/management

        $status   = $request->query('status');
        $category = $request->query('category');
        $priority = $request->query('priority');
        $search   = $request->query('search');

        $query = Feedback::with(['user', 'resolver'])->latest('id');

        if ($status)   $query->where('status', $status);
        if ($category) $query->where('category', $category);
        if ($priority) $query->where('priority', $priority);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('submitter_name', 'like', "%{$search}%");
            });
        }

        $feedbacks = $query->paginate(20)->withQueryString();

        $stats = [
            'total'       => Feedback::count(),
            'pending'     => Feedback::where('status', 'pending')->count(),
            'in_review'   => Feedback::where('status', 'in_review')->count(),
            'resolved'    => Feedback::where('status', 'resolved')->count(),
            'urgent'      => Feedback::where('priority', 'urgent')->where('status', '!=', 'resolved')->count(),
        ];

        return view('feedback.index', compact('feedbacks', 'stats', 'status', 'category', 'priority', 'search'));
    }

    /**
     * Create new feedback / grievance.
     */
    public function create()
    {
        return view('feedback.create');
    }

    /**
     * Store feedback submission.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'category'     => 'required|string|in:academic,transport,fee_billing,facilities,canteen,other',
            'priority'     => 'required|string|in:normal,high,urgent',
            'description'  => 'required|string|min:10',
            'is_anonymous' => 'nullable|boolean',
        ]);

        $user = auth()->user();
        $isAnon = $request->boolean('is_anonymous');

        Feedback::create([
            'school_id'      => $user->school_id ?? 1,
            'user_id'        => $isAnon ? null : $user->id,
            'submitter_name' => $isAnon ? 'Anonymous Citizen' : $user->full_name,
            'submitter_role' => $user->primary_role ?? 'parent',
            'is_anonymous'   => $isAnon,
            'category'       => $data['category'],
            'priority'       => $data['priority'],
            'title'          => $data['title'],
            'description'    => $data['description'],
            'status'         => 'pending',
        ]);

        return redirect()->route('feedback.my')
            ->with('status', 'Your feedback/grievance has been recorded. Reference tracking active.');
    }

    /**
     * View feedback detail.
     */
    public function show(int $id)
    {
        $feedback = Feedback::with(['user', 'resolver'])->findOrFail($id);

        // Security: only admin/staff or original submitter can view
        $user = auth()->user();
        if ($feedback->user_id !== $user->id && !$user->hasRole(['admin', 'principal', 'teacher'])) {
            abort(403, 'Unauthorized');
        }

        return view('feedback.show', compact('feedback'));
    }

    /**
     * Admin: Respond and update status.
     */
    public function updateStatus(Request $request, int $id)
    {
        $this->authorize('view staff');

        $feedback = Feedback::findOrFail($id);

        $data = $request->validate([
            'status'         => 'required|in:pending,in_review,resolved,rejected',
            'admin_response' => 'nullable|string|max:2000',
        ]);

        $feedback->update([
            'status'         => $data['status'],
            'admin_response' => $data['admin_response'] ?? $feedback->admin_response,
            'resolved_by'    => auth()->id(),
            'resolved_at'    => $data['status'] === 'resolved' ? now() : $feedback->resolved_at,
        ]);

        return back()->with('status', 'Feedback ticket updated successfully.');
    }

    /**
     * Portal tracking for submitters (Parents / Students).
     */
    public function myFeedback()
    {
        $user = auth()->user();
        $feedbacks = Feedback::where('user_id', $user->id)
            ->latest('id')
            ->paginate(15);

        return view('feedback.my-feedback', compact('feedbacks'));
    }
}
