<?php

namespace App\Http\Controllers;

use App\Models\StudentTransport;
use App\Models\TransportRoute;
use App\Models\RouteStop;
use App\Models\User;
use Illuminate\Http\Request;

class StudentTransportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:manage transport');
    }

    public function index(Request $request)
    {
        $query = StudentTransport::with(['student', 'route', 'stop'])
            ->latest();

        if ($search = $request->input('search')) {
            $query->whereHas('student', fn($q) =>
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
            );
        }
        if ($route = $request->input('route_id')) {
            $query->where('route_id', $route);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $allocations = $query->paginate(25)->withQueryString();
        $routes      = TransportRoute::active()->orderBy('name')->get();

        return view('transport.students.index', compact('allocations', 'routes'));
    }

    public function create(Request $request)
    {
        $routes = TransportRoute::active()->with('stops')->orderBy('name')->get();

        // Pre-select student from query param (e.g. from student profile)
        $student = $request->input('student_id')
            ? User::find($request->input('student_id'))
            : null;

        // Students not yet on a route (or allow multiple if needed)
        $students = User::role('student')->orderBy('first_name')->get();

        return view('transport.students.create', compact('routes', 'students', 'student'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id'  => 'required|integer|exists:users,id',
            'route_id'    => 'required|integer|exists:transport_routes,id',
            'stop_id'     => 'nullable|integer|exists:route_stops,id',
            'start_date'  => 'required|date',
            'end_date'    => 'nullable|date|after:start_date',
            'direction'   => 'required|in:both,pickup_only,dropoff_only',
            'monthly_fee' => 'required|numeric|min:0',
            'notes'       => 'nullable|string|max:500',
        ]);

        // Check for existing active allocation on same route
        $exists = StudentTransport::where('student_id', $data['student_id'])
                                  ->where('route_id', $data['route_id'])
                                  ->where('status', 'active')
                                  ->exists();
        if ($exists) {
            return back()->with('error', 'This student is already allocated to this route.')->withInput();
        }

        StudentTransport::create($data);

        return redirect()->route('transport.students.index')
                         ->with('status', 'Student allocated to route successfully.');
    }

    public function edit(int $id)
    {
        $allocation = StudentTransport::with(['student', 'route', 'stop'])->findOrFail($id);
        $routes     = TransportRoute::active()->with('stops')->orderBy('name')->get();
        return view('transport.students.edit', compact('allocation', 'routes'));
    }

    public function update(Request $request, int $id)
    {
        $allocation = StudentTransport::findOrFail($id);

        $data = $request->validate([
            'route_id'    => 'required|integer|exists:transport_routes,id',
            'stop_id'     => 'nullable|integer|exists:route_stops,id',
            'start_date'  => 'required|date',
            'end_date'    => 'nullable|date|after:start_date',
            'direction'   => 'required|in:both,pickup_only,dropoff_only',
            'monthly_fee' => 'required|numeric|min:0',
            'status'      => 'required|in:active,suspended,cancelled',
            'notes'       => 'nullable|string|max:500',
        ]);

        $allocation->update($data);

        return redirect()->route('transport.students.index')
                         ->with('status', 'Allocation updated.');
    }

    public function destroy(int $id)
    {
        StudentTransport::findOrFail($id)->delete();

        return redirect()->route('transport.students.index')
                         ->with('status', 'Allocation removed.');
    }

    /** AJAX: get stops for a selected route */
    public function getStops(int $routeId)
    {
        $stops = RouteStop::where('route_id', $routeId)
                          ->orderBy('stop_order')
                          ->get(['id', 'name', 'stop_order', 'morning_pickup', 'stop_fee']);

        return response()->json($stops);
    }
}
