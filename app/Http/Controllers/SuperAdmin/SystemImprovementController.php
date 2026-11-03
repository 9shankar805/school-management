<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SystemImprovement;
use Illuminate\Http\Request;

class SystemImprovementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Display a listing of system improvements and feature tasks.
     */
    public function index(Request $request)
    {
        $query = SystemImprovement::with(['creator', 'completer'])->latest();

        // Status Filter (remaining, done, all)
        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'remaining') {
                $query->whereIn('status', ['remaining', 'in_progress']);
            } elseif ($request->status === 'done') {
                $query->where('status', 'done');
            } else {
                $query->where('status', $request->status);
            }
        }

        // Category Filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Priority Filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $improvements = $query->paginate(20)->withQueryString();

        $totalCount     = SystemImprovement::count();
        $doneCount      = SystemImprovement::where('status', 'done')->count();
        $remainingCount = SystemImprovement::whereIn('status', ['remaining', 'in_progress'])->count();
        $progressPct    = $totalCount > 0 ? round(($doneCount / $totalCount) * 100) : 0;

        $stats = [
            'total'     => $totalCount,
            'remaining' => $remainingCount,
            'done'      => $doneCount,
            'progress'  => $progressPct,
        ];

        $categories = [
            'billing'    => 'SaaS Billing & Invoices (बिलिङ तथा भुक्तानी)',
            'schools'    => 'School Management (विद्यालय व्यवस्थापन)',
            'academics'  => 'Academics & Examination (शैक्षिक तथा परीक्षा)',
            'attendance' => 'Attendance & Biometrics (हाजिरी तथा बायोमेट्रिक)',
            'ui_ux'      => 'UI / Design & Experience (डिजाइन तथा इन्टरफेस)',
            'mobile_app' => 'Mobile App & Push (मोबाइल एप)',
            'security'   => 'Security & Permissions (सुरक्षा तथा भूमिका)',
            'general'    => 'General Improvement (साधारण सुधार)',
        ];

        return view('super-admin.improvements.index', compact('improvements', 'stats', 'categories'));
    }

    /**
     * Store a newly created improvement item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'category'    => 'required|string|max:50',
            'priority'    => 'required|in:low,medium,high,urgent',
            'status'      => 'nullable|in:remaining,in_progress,done',
            'target_date' => 'nullable|date',
            'description' => 'nullable|string',
            'notes'       => 'nullable|string',
        ]);

        $data = $validated;
        $data['status'] = $data['status'] ?? 'remaining';
        $data['created_by'] = auth()->id();

        if ($data['status'] === 'done') {
            $data['completed_at'] = now();
            $data['completed_by'] = auth()->id();
        }

        SystemImprovement::create($data);

        return back()->with('success', "Improvement item \"{$data['title']}\" added successfully.");
    }

    /**
     * Update an improvement item.
     */
    public function update(Request $request, SystemImprovement $improvement)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'category'    => 'required|string|max:50',
            'priority'    => 'required|in:low,medium,high,urgent',
            'status'      => 'required|in:remaining,in_progress,done',
            'target_date' => 'nullable|date',
            'description' => 'nullable|string',
            'notes'       => 'nullable|string',
        ]);

        $data = $validated;

        if ($data['status'] === 'done' && $improvement->status !== 'done') {
            $data['completed_at'] = now();
            $data['completed_by'] = auth()->id();
        } elseif ($data['status'] !== 'done' && $improvement->status === 'done') {
            $data['completed_at'] = null;
            $data['completed_by'] = null;
        }

        $improvement->update($data);

        return back()->with('success', "Improvement item \"{$improvement->title}\" updated.");
    }

    /**
     * Toggle status between Remaining and Done.
     */
    public function toggle(SystemImprovement $improvement)
    {
        if ($improvement->status === 'done') {
            $improvement->update([
                'status'       => 'remaining',
                'completed_at' => null,
                'completed_by' => null,
            ]);
            $msg = "Item \"{$improvement->title}\" marked as Remaining (अझै गर्न बाँकी).";
        } else {
            $improvement->update([
                'status'       => 'done',
                'completed_at' => now(),
                'completed_by' => auth()->id(),
            ]);
            $msg = "Item \"{$improvement->title}\" marked as Done (सम्पन्न भयो)! 🎉";
        }

        return back()->with('success', $msg);
    }

    /**
     * Remove an improvement item.
     */
    public function destroy(SystemImprovement $improvement)
    {
        $title = $improvement->title;
        $improvement->delete();

        return back()->with('success', "Improvement item \"{$title}\" deleted.");
    }
}
