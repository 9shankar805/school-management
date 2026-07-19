<?php

namespace App\Http\Controllers;

use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ── Leave Types CRUD (admin) ──────────────────────────────────────────
    public function types()
    {
        $this->authorize('view teachers');
        $types = LeaveType::withCount('applications')->get();
        return view('teachers.leave.types', compact('types'));
    }

    public function storeType(Request $request)
    {
        $this->authorize('create teachers');
        $data = $request->validate([
            'name'         => 'required|string|max:100|unique:leave_types,name',
            'code'         => 'nullable|string|max:10',
            'days_allowed' => 'required|integer|min:0|max:365',
            'is_paid'      => 'boolean',
            'carry_forward'=> 'boolean',
        ]);
        LeaveType::create(array_merge($data, [
            'is_paid'       => $request->boolean('is_paid'),
            'carry_forward' => $request->boolean('carry_forward'),
            'is_active'     => true,
        ]));
        return back()->with('status', 'Leave type created.');
    }

    public function updateType(Request $request, int $id)
    {
        $this->authorize('create teachers');
        $type = LeaveType::findOrFail($id);
        $data = $request->validate([
            'name'         => 'required|string|max:100|unique:leave_types,name,' . $id,
            'code'         => 'nullable|string|max:10',
            'days_allowed' => 'required|integer|min:0|max:365',
            'is_paid'      => 'boolean',
            'carry_forward'=> 'boolean',
            'is_active'    => 'boolean',
        ]);
        $type->update(array_merge($data, [
            'is_paid'       => $request->boolean('is_paid'),
            'carry_forward' => $request->boolean('carry_forward'),
            'is_active'     => $request->boolean('is_active'),
        ]));
        return back()->with('status', 'Leave type updated.');
    }

    public function destroyType(int $id)
    {
        $this->authorize('create teachers');
        LeaveType::findOrFail($id)->delete();
        return back()->with('status', 'Leave type deleted.');
    }

    // ── Leave Applications ────────────────────────────────────────────────
    public function index(Request $request)
    {
        $this->authorize('view teachers');
        $status  = $request->query('status', 'pending');
        $search  = $request->query('search');

        $query = LeaveApplication::with('user', 'leaveType', 'reviewer')->latest();

        if ($status !== 'all') $query->where('status', $status);
        if ($search) {
            $query->whereHas('user', fn($q) => $q
                ->where('first_name', 'like', "%$search%")
                ->orWhere('last_name',  'like', "%$search%")
            );
        }

        $applications = $query->paginate(20)->withQueryString();
        $counts = LeaveApplication::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')->pluck('total', 'status');

        return view('teachers.leave.index', compact('applications', 'counts', 'status', 'search'));
    }

    /** Teacher applies for leave */
    public function apply(Request $request)
    {
        $data = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'from_date'     => 'required|date|after_or_equal:today',
            'to_date'       => 'required|date|after_or_equal:from_date',
            'reason'        => 'required|string|max:1000',
            'attachment'    => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png',
        ]);

        $from  = Carbon::parse($data['from_date']);
        $to    = Carbon::parse($data['to_date']);
        $total = $from->diffInWeekdays($to) + 1;

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('leave-attachments', 'public');
        }

        LeaveApplication::create(array_merge($data, [
            'user_id'         => auth()->id(),
            'total_days'      => $total,
            'attachment_path' => $path,
            'status'          => 'pending',
        ]));

        return back()->with('status', "Leave application submitted ({$total} days).");
    }

    /** HR/Admin reviews an application */
    public function review(Request $request, int $id)
    {
        $this->authorize('create teachers');
        $request->validate([
            'status'         => 'required|in:approved,rejected',
            'reviewer_notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request, $id) {
            $application = LeaveApplication::with('leaveType')->findOrFail($id);
            $application->update([
                'status'         => $request->status,
                'reviewer_notes' => $request->reviewer_notes,
                'reviewed_by'    => auth()->id(),
                'reviewed_at'    => now(),
            ]);

            // Update leave balance when approved
            if ($request->status === 'approved') {
                $balance = LeaveBalance::firstOrCreate(
                    [
                        'user_id'       => $application->user_id,
                        'leave_type_id' => $application->leave_type_id,
                        'year'          => $application->from_date->year,
                    ],
                    [
                        'total_days'     => $application->leaveType->days_allowed,
                        'used_days'      => 0,
                        'remaining_days' => $application->leaveType->days_allowed,
                    ]
                );
                $balance->increment('used_days', $application->total_days);
                $balance->decrement('remaining_days', $application->total_days);
            }
        });

        return back()->with('status', 'Leave application ' . $request->status . '.');
    }

    /** Cancel own application */
    public function cancel(int $id)
    {
        $app = LeaveApplication::where('user_id', auth()->id())->findOrFail($id);
        abort_if($app->status !== 'pending', 403, 'Only pending applications can be cancelled.');
        $app->update(['status' => 'cancelled']);
        return back()->with('status', 'Leave application cancelled.');
    }
}
