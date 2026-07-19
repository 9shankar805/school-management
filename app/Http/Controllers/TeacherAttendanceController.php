<?php

namespace App\Http\Controllers;

use App\Models\TeacherAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherAttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:view teachers']);
    }

    /** Daily attendance marking page */
    public function index(Request $request)
    {
        $date     = $request->query('date', now()->toDateString());
        $teachers = User::role(['teacher', 'class-teacher'])
            ->with(['teacherAttendance' => fn($q) => $q->whereDate('date', $date)])
            ->get();

        $summary = TeacherAttendance::whereDate('date', $date)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('teachers.attendance.index', compact('teachers', 'date', 'summary'));
    }

    /** Bulk mark attendance for a date */
    public function store(Request $request)
    {
        $this->authorize('create teachers');
        $request->validate([
            'date'         => 'required|date',
            'attendance'   => 'required|array',
            'attendance.*' => 'required|in:present,absent,late,half_day,on_leave',
        ]);

        $markedBy = auth()->id();
        foreach ($request->attendance as $teacherId => $status) {
            TeacherAttendance::updateOrCreate(
                ['teacher_id' => $teacherId, 'date' => $request->date],
                ['status' => $status, 'marked_by' => $markedBy]
            );
        }

        return back()->with('status', 'Attendance saved for ' . Carbon::parse($request->date)->format('d M Y') . '.');
    }

    /** Monthly attendance report for a single teacher */
    public function show(Request $request, int $teacherId)
    {
        $teacher = User::findOrFail($teacherId);
        $month   = (int) $request->query('month', now()->month);
        $year    = (int) $request->query('year',  now()->year);

        $records = TeacherAttendance::where('teacher_id', $teacherId)
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

        // Build calendar grid
        $startOfMonth = Carbon::create($year, $month, 1);
        $daysInMonth  = $startOfMonth->daysInMonth;

        return view('teachers.attendance.show', compact(
            'teacher', 'records', 'summary', 'month', 'year', 'daysInMonth', 'startOfMonth'
        ));
    }

    /** Quick check-in/out update */
    public function updateTime(Request $request, int $id)
    {
        $this->authorize('create teachers');
        $request->validate([
            'check_in'  => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i',
        ]);
        TeacherAttendance::findOrFail($id)->update($request->only('check_in', 'check_out'));
        return back()->with('status', 'Times updated.');
    }
}
