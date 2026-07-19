<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AchievementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:view students']);
    }

    // ── Store a new achievement for a student ─────────────────────────────
    public function store(Request $request, int $studentId)
    {
        $this->authorize('create students');

        $categories = implode(',', array_keys(Achievement::CATEGORIES));
        $levels     = implode(',', array_keys(Achievement::LEVELS));

        $data = $request->validate([
            'category'     => "required|in:{$categories}",
            'title'        => 'required|string|max:255',
            'award_type'   => 'nullable|string|max:100',
            'description'  => 'nullable|string|max:2000',
            'issuing_body' => 'nullable|string|max:255',
            'level'        => "required|in:{$levels}",
            'awarded_date' => 'required|date',
            'attachment'   => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,webp',
        ]);

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store("achievements/{$studentId}", 'public');
        }

        Achievement::create(array_merge(
            $data,
            [
                'student_id'      => $studentId,
                'attachment_path' => $path,
                'recorded_by'     => auth()->id(),
            ]
        ));

        return back()->with('status', 'Achievement recorded.');
    }

    // ── Update an achievement ─────────────────────────────────────────────
    public function update(Request $request, int $id)
    {
        $this->authorize('create students');
        $achievement = Achievement::findOrFail($id);

        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'award_type'   => 'nullable|string|max:100',
            'description'  => 'nullable|string|max:2000',
            'issuing_body' => 'nullable|string|max:255',
            'awarded_date' => 'required|date',
        ]);

        $achievement->update($data);
        return back()->with('status', 'Achievement updated.');
    }

    // ── Delete an achievement ─────────────────────────────────────────────
    public function destroy(int $id)
    {
        $this->authorize('create students');
        $achievement = Achievement::findOrFail($id);

        if ($achievement->attachment_path) {
            Storage::disk('public')->delete($achievement->attachment_path);
        }
        $achievement->delete();

        return back()->with('status', 'Achievement removed.');
    }

    // ── School-wide achievements leaderboard ──────────────────────────────
    public function leaderboard(Request $request)
    {
        $category = $request->query('category');
        $level    = $request->query('level');

        $query = Achievement::with('student')
            ->select('student_id', \DB::raw('COUNT(*) as total'), \DB::raw('MAX(level) as top_level'))
            ->groupBy('student_id')
            ->orderByDesc('total');

        if ($category) $query->where('category', $category);
        if ($level)    $query->where('level', $level);

        $leaderboard = $query->take(20)->get();

        $recent = Achievement::with('student')
            ->when($category, fn($q) => $q->where('category', $category))
            ->orderByDesc('awarded_date')
            ->take(10)
            ->get();

        return view('students.achievements.leaderboard', compact('leaderboard', 'recent', 'category', 'level'));
    }
}
