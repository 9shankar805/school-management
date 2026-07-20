<?php

namespace App\Http\Controllers;

use App\Interfaces\QuestionPaperInterface;
use App\Models\QuestionPaper;
use Illuminate\Http\Request;

class QuestionApprovalController extends Controller
{
    public function __construct(protected QuestionPaperInterface $paperRepo)
    {
        $this->middleware(['auth']);
    }

    /**
     * GET /question-papers-pending
     * Approval dashboard — papers awaiting review.
     */
    public function index()
    {
        $this->authorize('review', QuestionPaper::class);

        $papers = QuestionPaper::with(['creator', 'course', 'approvals'])
            ->whereIn('status', ['submitted', 'reviewed'])
            ->orderByDesc('updated_at')
            ->get();

        return view('question-papers.approvals.index', compact('papers'));
    }

    /**
     * POST /question-papers/{id}/submit
     * Teacher submits a draft paper for review.
     */
    public function submit(Request $request, int $id)
    {
        $paper = QuestionPaper::findOrFail($id);
        $this->authorize('submit', $paper);

        if ($paper->status !== 'draft') {
            return back()->withErrors('Only draft papers can be submitted.');
        }

        $request->validate(['comments' => 'nullable|string|max:500']);

        $this->paperRepo->advanceStatus($id, 'submitted', $request->comments);

        return back()->with('status', 'Paper submitted for review.');
    }

    /**
     * POST /question-papers/{id}/review
     * Reviewer (HOD / principal) marks as reviewed.
     */
    public function review(Request $request, int $id)
    {
        $paper = QuestionPaper::findOrFail($id);
        $this->authorize('review', $paper);

        $request->validate(['comments' => 'nullable|string|max:500']);

        $this->paperRepo->advanceStatus($id, 'reviewed', $request->comments);

        return back()->with('status', 'Paper marked as reviewed.');
    }

    /**
     * POST /question-papers/{id}/approve
     * Admin / principal approves.
     */
    public function approve(Request $request, int $id)
    {
        $paper = QuestionPaper::findOrFail($id);
        $this->authorize('approve', $paper);

        $request->validate(['comments' => 'nullable|string|max:500']);

        $this->paperRepo->advanceStatus($id, 'approved', $request->comments);

        return back()->with('status', 'Paper approved.');
    }

    /**
     * POST /question-papers/{id}/reject
     * Reviewer rejects — paper goes back to draft.
     */
    public function reject(Request $request, int $id)
    {
        $paper = QuestionPaper::findOrFail($id);
        $this->authorize('review', $paper);

        $request->validate(['comments' => 'required|string|max:500']);

        $this->paperRepo->advanceStatus($id, 'rejected', $request->comments);

        return back()->with('status', 'Paper rejected and returned to author for revision.');
    }

    /**
     * POST /question-papers/{id}/lock
     * Lock for printing.
     */
    public function lock(Request $request, int $id)
    {
        $paper = QuestionPaper::findOrFail($id);
        $this->authorize('lock', $paper);

        $request->validate(['comments' => 'nullable|string|max:500']);

        $this->paperRepo->advanceStatus($id, 'locked', $request->comments);

        return back()->with('status', 'Paper locked. No further edits are permitted.');
    }
}
