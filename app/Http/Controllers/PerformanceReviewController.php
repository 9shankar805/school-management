<?php

namespace App\Http\Controllers;

use App\Models\PerformanceReview;
use App\Models\User;
use Illuminate\Http\Request;

class PerformanceReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:view teachers']);
    }

    public function index(Request $request)
    {
        $period  = $request->query('period');
        $reviews = PerformanceReview::with('teacher', 'reviewer')
            ->when($period, fn($q) => $q->where('review_period', $period))
            ->latest('review_date')
            ->paginate(20)->withQueryString();

        $periods = PerformanceReview::distinct()->pluck('review_period')->sort()->values();

        return view('teachers.reviews.index', compact('reviews', 'periods', 'period'));
    }

    public function store(Request $request, int $teacherId)
    {
        $this->authorize('create teachers');
        $data = $request->validate([
            'review_period'          => 'required|string|max:50',
            'review_date'            => 'required|date',
            'teaching_quality'       => 'required|integer|min:1|max:5',
            'punctuality'            => 'required|integer|min:1|max:5',
            'student_engagement'     => 'required|integer|min:1|max:5',
            'communication'          => 'required|integer|min:1|max:5',
            'professionalism'        => 'required|integer|min:1|max:5',
            'strengths'              => 'nullable|string|max:2000',
            'areas_for_improvement'  => 'nullable|string|max:2000',
            'goals'                  => 'nullable|string|max:2000',
            'reviewer_comments'      => 'nullable|string|max:2000',
        ]);

        $overall = round(
            ($data['teaching_quality'] + $data['punctuality'] +
             $data['student_engagement'] + $data['communication'] +
             $data['professionalism']) / 5, 1
        );

        PerformanceReview::create(array_merge($data, [
            'teacher_id'     => $teacherId,
            'reviewer_id'    => auth()->id(),
            'overall_rating' => $overall,
            'status'         => 'submitted',
        ]));

        return back()->with('status', "Performance review submitted (overall: {$overall}/5).");
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('create teachers');
        $review = PerformanceReview::findOrFail($id);
        $data   = $request->validate([
            'strengths'             => 'nullable|string|max:2000',
            'areas_for_improvement' => 'nullable|string|max:2000',
            'goals'                 => 'nullable|string|max:2000',
            'reviewer_comments'     => 'nullable|string|max:2000',
            'status'                => 'required|in:draft,submitted,acknowledged',
        ]);
        $review->update($data);
        return back()->with('status', 'Review updated.');
    }

    public function destroy(int $id)
    {
        $this->authorize('create teachers');
        PerformanceReview::findOrFail($id)->delete();
        return back()->with('status', 'Review deleted.');
    }
}
