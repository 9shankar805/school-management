<?php

namespace App\Http\Controllers;

use App\Models\StaffAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StaffAttendanceController extends Controller
{
    /** Staff roles that this controller tracks (non-teacher). */
    private const STAFF_ROLES = [
        'admin', 'principal', 'vice-principal', 'academic-coordinator',
        'accountant', 'librarian', 'receptionist', 'hr-manager',
        'transport-manager', 'hostel-manager', 'exam-controller',
        'attendance-officer', 'admission-officer',
    ];

    public function __construct()
    {
        $this->middleware(['auth', 'can:view staff']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Daily attendance marking
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * GET /staff/attendance
     * List all non-teacher staff with today's attendance status.
     */
    public function index(Request $request)
    {
        $date = $request->query('date', today()->toDateString());

        $staff = User::role(self::STAFF_ROLES)
            ->with(['staffAttendance' => fn($q) => $q->whereDate('date', $date)])
            ->orderBy('first_name')
            ->get();

        // Summary counts for the date
        $summary = StaffAttendance::whereDate('date', $date)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return view('staff.attendance.index', compact('staff', 'date', 'summary'));
    }

    /**
     * POST /staff/attendance
     * Bulk mark staff attendance for a date.
     */
    public function store(Request $request)
    {
        $this->authorize('create staff');

        $request->validate([
            'date'              => 'required|date',
            'attendance'        => 'required|array',
            'attendance.*'      => 'required|in:present,absent,late,half_day,on_leave',
            'check_in'          => 'nullable|array',
            'check_in.*'        => 'nullable|date_format:H:i',
            'late_minutes'      => 'nullable|array',
            'late_minutes.*'    => 'nullable|integer|min:0',
            'notes'             => 'nullable|array',
            'notes.*'           => 'nullable|string|max:255',
        ]);

        $markedBy = auth()->id();

        foreach ($request->attendance as $staffId => $status) {
            $checkIn     = $request->check_in[$staffId]     ?? null;
            $lateMinutes = $request->late_minutes[$staffId]  ?? 0;
            $notes       = $request->notes[$staffId]         ?? null;

            // Auto-calculate late_minutes from check_in if not provided
            // (assumes 08:00 start; could be made configurable via settings)
            if ($checkIn && (int) $lateMinutes === 0 && $status === 'late') {
                $start       = Carbon::today()->setTimeFromTimeString('08:00');
                $arrival     = Carbon::today()->setTimeFromTimeString($checkIn);
                $lateMinutes = max(0, (int) $start->diffInMinutes($arrival, false));
            }

            StaffAttendance::updateOrCreate(
                ['staff_id' => $staffId, 'date' => $request->date],
                [
                    'status'       => $status,
                    'check_in'     => $checkIn ? $checkIn . ':00' : null,
                    'late_minutes' => max(0, (int) $lateMinutes),
                    'notes'        => $notes,
                    'marked_by'    => $markedBy,
                ]
            );
        }

        return back()->with('status', 'Staff attendance saved for ' . Carbon::parse($request->date)->format('d M Y') . '.');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Individual monthly report
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * GET /staff/{staffId}/attendance
     * Monthly calendar view for a single staff member.
     */
    public function show(Request $request, int $staffId)
    {
        $staffMember = User::findOrFail($staffId);
        $month       = (int) $request->query('month', now()->month);
        $year        = (int) $request->query('year',  now()->year);

        $records = StaffAttendance::where('staff_id', $staffId)
            ->whereMonth('date', $month)
            ->whereYear('date',  $year)
            ->orderBy('date')
            ->get()
            ->keyBy(fn($r) => $r->date->format('Y-m-d'));

        $summary = [
            'present'  => $records->where('status', 'present')->count(),
            'absent'   => $records->where('status', 'absent')->count(),
            'late'     => $records->where('status', 'late')->count(),
            'half_day' => $records->where('status', 'half_day')->count(),
            'on_leave' => $records->where('status', 'on_leave')->count(),
        ];

        $startOfMonth = Carbon::create($year, $month, 1);
        $daysInMonth  = $startOfMonth->daysInMonth;

        return view('staff.attendance.show', compact(
            'staffMember', 'records', 'summary', 'month', 'year', 'daysInMonth', 'startOfMonth'
        ));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Quick check-in / check-out time update
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * PATCH /staff/attendance/{id}/time
     */
    public function updateTime(Request $request, int $id)
    {
        $this->authorize('create staff');

        $request->validate([
            'check_in'  => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
        ]);

        StaffAttendance::findOrFail($id)->update([
            'check_in'  => $request->check_in  ? $request->check_in  . ':00' : null,
            'check_out' => $request->check_out ? $request->check_out . ':00' : null,
        ]);

        return back()->with('status', 'Times updated.');
    }
}
