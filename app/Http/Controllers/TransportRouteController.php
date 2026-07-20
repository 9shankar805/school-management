<?php

namespace App\Http\Controllers;

use App\Models\TransportRoute;
use App\Models\RouteStop;
use App\Models\Vehicle;
use App\Models\Driver;
use Illuminate\Http\Request;

class TransportRouteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:manage transport');
    }

    public function index(Request $request)
    {
        $query = TransportRoute::with(['vehicle', 'driver'])
            ->withCount('activeStudents');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $routes = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('transport.routes.index', compact('routes'));
    }

    public function create()
    {
        $vehicles = Vehicle::active()->orderBy('name')->get();
        $drivers  = Driver::active()->orderBy('name')->get();
        return view('transport.routes.create', compact('vehicles', 'drivers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                => 'required|string|max:150',
            'code'                => 'nullable|string|max:20|unique:transport_routes,code',
            'description'         => 'nullable|string',
            'vehicle_id'          => 'nullable|integer|exists:vehicles,id',
            'driver_id'           => 'nullable|integer|exists:drivers,id',
            'morning_departure'   => 'nullable|date_format:H:i',
            'morning_arrival'     => 'nullable|date_format:H:i',
            'afternoon_departure' => 'nullable|date_format:H:i',
            'afternoon_arrival'   => 'nullable|date_format:H:i',
            'distance_km'         => 'nullable|numeric|min:0',
            'monthly_fee'         => 'required|numeric|min:0',
        ]);

        TransportRoute::create($data);

        return redirect()->route('transport.routes.index')
                         ->with('status', 'Route created successfully.');
    }

    public function show(int $id)
    {
        $route = TransportRoute::with([
            'vehicle', 'driver',
            'stops' => fn($q) => $q->orderBy('stop_order'),
            'activeStudents.student',
            'activeStudents.stop',
        ])->withCount('activeStudents')->findOrFail($id);

        return view('transport.routes.show', compact('route'));
    }

    public function edit(int $id)
    {
        $route    = TransportRoute::with('stops')->findOrFail($id);
        $vehicles = Vehicle::active()->orderBy('name')->get();
        $drivers  = Driver::active()->orderBy('name')->get();
        return view('transport.routes.edit', compact('route', 'vehicles', 'drivers'));
    }

    public function update(Request $request, int $id)
    {
        $route = TransportRoute::findOrFail($id);

        $data = $request->validate([
            'name'                => 'required|string|max:150',
            'code'                => 'nullable|string|max:20|unique:transport_routes,code,' . $id,
            'description'         => 'nullable|string',
            'vehicle_id'          => 'nullable|integer|exists:vehicles,id',
            'driver_id'           => 'nullable|integer|exists:drivers,id',
            'morning_departure'   => 'nullable|date_format:H:i',
            'morning_arrival'     => 'nullable|date_format:H:i',
            'afternoon_departure' => 'nullable|date_format:H:i',
            'afternoon_arrival'   => 'nullable|date_format:H:i',
            'distance_km'         => 'nullable|numeric|min:0',
            'monthly_fee'         => 'required|numeric|min:0',
            'status'              => 'required|in:active,suspended,discontinued',
        ]);

        $route->update($data);

        return redirect()->route('transport.routes.show', $id)
                         ->with('status', 'Route updated successfully.');
    }

    public function destroy(int $id)
    {
        $route = TransportRoute::withCount('activeStudents')->findOrFail($id);

        if ($route->active_students_count > 0) {
            return redirect()->route('transport.routes.index')
                             ->with('error', 'Cannot delete: ' . $route->active_students_count . ' student(s) are assigned to this route.');
        }

        $route->delete();

        return redirect()->route('transport.routes.index')
                         ->with('status', 'Route deleted.');
    }

    // ── Route stops ───────────────────────────────────────────────────────────

    public function stopsStore(Request $request, int $routeId)
    {
        $route = TransportRoute::findOrFail($routeId);

        $data = $request->validate([
            'name'              => 'required|string|max:150',
            'stop_order'        => 'required|integer|min:1',
            'morning_pickup'    => 'nullable|date_format:H:i',
            'afternoon_dropoff' => 'nullable|date_format:H:i',
            'latitude'          => 'nullable|numeric|between:-90,90',
            'longitude'         => 'nullable|numeric|between:-180,180',
            'landmark'          => 'nullable|string|max:200',
            'stop_fee'          => 'nullable|numeric|min:0',
        ]);

        $data['route_id'] = $route->id;
        RouteStop::create($data);

        return redirect()->route('transport.routes.show', $routeId)
                         ->with('status', 'Stop added.');
    }

    public function stopsUpdate(Request $request, int $routeId, int $stopId)
    {
        $stop = RouteStop::where('route_id', $routeId)->findOrFail($stopId);

        $data = $request->validate([
            'name'              => 'required|string|max:150',
            'stop_order'        => 'required|integer|min:1',
            'morning_pickup'    => 'nullable|date_format:H:i',
            'afternoon_dropoff' => 'nullable|date_format:H:i',
            'landmark'          => 'nullable|string|max:200',
            'stop_fee'          => 'nullable|numeric|min:0',
        ]);

        $stop->update($data);

        return redirect()->route('transport.routes.show', $routeId)
                         ->with('status', 'Stop updated.');
    }

    public function stopsDestroy(int $routeId, int $stopId)
    {
        $stop = RouteStop::where('route_id', $routeId)->findOrFail($stopId);

        if ($stop->student_count > 0) {
            return redirect()->route('transport.routes.show', $routeId)
                             ->with('error', 'Cannot remove stop: students are assigned here.');
        }

        $stop->delete();

        return redirect()->route('transport.routes.show', $routeId)
                         ->with('status', 'Stop removed.');
    }
}
