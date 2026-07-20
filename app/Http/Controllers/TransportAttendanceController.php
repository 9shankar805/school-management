<?php

namespace App\Http\Controllers;

use App\Models\TransportAttendance;
use App\Models\TransportRoute;
use App\Models\StudentTransport;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransportAttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:manage transport');
    }

    public function index(Request $request)
    {
        $routes = TransportRoute::active()->orderBy('name')->get();
        $date   = $request->input('date', today()->toDateString());
        $routeId = $request->input('route_id');
        $trip   = $request->input('trip', 'morning');

        $records = collect();
        $route   = null;

        if ($routeId) {
            $route = TransportRoute::with('activeStudents.student')->findOrFail($routeId);

            $records = TransportAttendance::with('student')
                ->where('route_id', $routeId)
                ->where('date', $date)
                ->where('trip', $trip)
                ->get()
                ->keyBy('student_id');
        }

        return view('transport.attendance.index', compact(
            'routes', 'date', 'routeId', 'trip', 'route', 'records'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'route_id'    => 'required|integer|exists:transport_routes,id',
            'date'        => 'required|date',
            'trip'        => 'required|in:morning,afternoon',
            'attendance'  => 'required|array',
            'attendance.*.student_id' => 'required|integer|exists:users,id',
            'attendance.*.status'     => 'required|in:present,absent,late',
            'attendance.*.remarks'    => 'nullable|string|max:200',
        ]);

        foreach ($data['attendance'] as $row) {
            TransportAttendance::updateOrCreate(
                [
                    'student_id' => $row['student_id'],
                    'route_id'   => $data['route_id'],
                    'date'       => $data['date'],
                    'trip'       => $data['trip'],
                ],
                [
                    'status'    => $row['status'],
                    'remarks'   => $row['remarks'] ?? null,
                    'marked_by' => auth()->id(),
                ]
            );
        }

        return redirect()->route('transport.attendance.index', [
            'route_id' => $data['route_id'],
            'date'     => $data['date'],
            'trip'     => $data['trip'],
        ])->with('status', 'Attendance saved for ' . Carbon::parse($data['date'])->format('d M Y') . '.');
    }

    public function report(Request $request)
    {
        $routes  = TransportRoute::active()->orderBy('name')->get();
        $routeId = $request->input('route_id');
        $month   = $request->input('month', now()->format('Y-m'));

        $report  = collect();
        $route   = null;

        if ($routeId) {
            $route      = TransportRoute::with('activeStudents.student')->findOrFail($routeId);
            [$year, $mon] = explode('-', $month);

            $startDate  = Carbon::createFromDate($year, $mon, 1)->startOfMonth();
            $endDate    = $startDate->copy()->endOfMonth();

            // All attendance for this route in the month
            $rawRecords = TransportAttendance::where('route_id', $routeId)
                ->whereBetween('date', [$startDate, $endDate])
                ->get()
                ->groupBy('student_id');

            foreach ($route->activeStudents as $alloc) {
                $studentRecords = $rawRecords->get($alloc->student_id, collect());
                $report[] = [
                    'student'       => $alloc->student,
                    'total_trips'   => $endDate->diffInDays($startDate) * 2,
                    'present'       => $studentRecords->where('status', 'present')->count(),
                    'absent'        => $studentRecords->where('status', 'absent')->count(),
                    'late'          => $studentRecords->where('status', 'late')->count(),
                ];
            }
        }

        return view('transport.attendance.report', compact('routes', 'routeId', 'month', 'route', 'report'));
    }
}
