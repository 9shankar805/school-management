<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\FuelLog;
use App\Models\MaintenanceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:manage transport');
    }

    public function index(Request $request)
    {
        $query = Vehicle::withCount('routes');

        if ($search = $request->input('search')) {
            $query->search($search);
        }
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $vehicles = $query->orderBy('name')->paginate(20)->withQueryString();

        // Expiry alerts
        $expiryAlerts = Vehicle::where(function ($q) {
            $q->where('insurance_expiry', '<=', now()->addDays(30))
              ->orWhere('fitness_expiry', '<=', now()->addDays(30))
              ->orWhere('permit_expiry', '<=', now()->addDays(30));
        })->where('status', 'active')->get();

        return view('transport.vehicles.index', compact('vehicles', 'expiryAlerts'));
    }

    public function create()
    {
        return view('transport.vehicles.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                => 'required|string|max:100',
            'registration_number' => 'required|string|max:50|unique:vehicles,registration_number',
            'type'                => 'required|in:bus,van,minibus,car',
            'make'                => 'nullable|string|max:100',
            'model'               => 'nullable|string|max:100',
            'year'                => 'nullable|integer|min:1990|max:' . date('Y'),
            'color'               => 'nullable|string|max:50',
            'capacity'            => 'required|integer|min:1|max:200',
            'fuel_type'           => 'required|in:diesel,petrol,cng,electric',
            'insurance_expiry'    => 'nullable|date',
            'fitness_expiry'      => 'nullable|date',
            'permit_expiry'       => 'nullable|date',
            'gps_device_id'       => 'nullable|string|max:100',
            'notes'               => 'nullable|string',
        ]);

        Vehicle::create($data);

        return redirect()->route('transport.vehicles.index')
                         ->with('status', 'Vehicle added successfully.');
    }

    public function show(int $id)
    {
        $vehicle = Vehicle::with([
            'driver',
            'routes.stops',
            'fuelLogs' => fn($q) => $q->latest('date')->take(10),
            'maintenanceLogs' => fn($q) => $q->latest('service_date')->take(10),
        ])->withCount('routes')->findOrFail($id);

        $totalFuelCost  = FuelLog::where('vehicle_id', $id)->sum('total_cost');
        $totalMaintCost = MaintenanceLog::where('vehicle_id', $id)->sum('cost');
        $upcomingMaint  = MaintenanceLog::where('vehicle_id', $id)->upcoming()->get();

        return view('transport.vehicles.show', compact(
            'vehicle', 'totalFuelCost', 'totalMaintCost', 'upcomingMaint'
        ));
    }

    public function edit(int $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        return view('transport.vehicles.edit', compact('vehicle'));
    }

    public function update(Request $request, int $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $data = $request->validate([
            'name'                => 'required|string|max:100',
            'registration_number' => 'required|string|max:50|unique:vehicles,registration_number,' . $id,
            'type'                => 'required|in:bus,van,minibus,car',
            'make'                => 'nullable|string|max:100',
            'model'               => 'nullable|string|max:100',
            'year'                => 'nullable|integer|min:1990|max:' . date('Y'),
            'color'               => 'nullable|string|max:50',
            'capacity'            => 'required|integer|min:1|max:200',
            'fuel_type'           => 'required|in:diesel,petrol,cng,electric',
            'insurance_expiry'    => 'nullable|date',
            'fitness_expiry'      => 'nullable|date',
            'permit_expiry'       => 'nullable|date',
            'status'              => 'required|in:active,maintenance,retired',
            'gps_device_id'       => 'nullable|string|max:100',
            'notes'               => 'nullable|string',
        ]);

        $vehicle->update($data);

        return redirect()->route('transport.vehicles.show', $id)
                         ->with('status', 'Vehicle updated successfully.');
    }

    public function destroy(int $id)
    {
        $vehicle = Vehicle::withCount('routes')->findOrFail($id);

        if ($vehicle->routes_count > 0) {
            return redirect()->route('transport.vehicles.index')
                             ->with('error', 'Cannot delete: vehicle is assigned to ' . $vehicle->routes_count . ' route(s).');
        }

        $vehicle->delete();

        return redirect()->route('transport.vehicles.index')
                         ->with('status', 'Vehicle deleted.');
    }

    // ── Fuel log ──────────────────────────────────────────────────────────────

    public function fuelStore(Request $request, int $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $data = $request->validate([
            'date'             => 'required|date',
            'litres'           => 'required|numeric|min:0.1',
            'cost_per_litre'   => 'required|numeric|min:0',
            'odometer_reading' => 'nullable|integer|min:0',
            'fuel_station'     => 'nullable|string|max:200',
            'notes'            => 'nullable|string|max:500',
        ]);

        $data['vehicle_id']  = $vehicle->id;
        $data['total_cost']  = round($data['litres'] * $data['cost_per_litre'], 2);
        $data['recorded_by'] = auth()->id();

        FuelLog::create($data);

        return redirect()->route('transport.vehicles.show', $id)
                         ->with('status', 'Fuel log added.');
    }

    public function fuelDestroy(int $vehicleId, int $logId)
    {
        FuelLog::where('vehicle_id', $vehicleId)->findOrFail($logId)->delete();
        return redirect()->route('transport.vehicles.show', $vehicleId)
                         ->with('status', 'Fuel log deleted.');
    }

    // ── Maintenance log ───────────────────────────────────────────────────────

    public function maintStore(Request $request, int $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $data = $request->validate([
            'type'              => 'required|string|max:50',
            'title'             => 'required|string|max:200',
            'description'       => 'nullable|string',
            'service_date'      => 'required|date',
            'next_service_date' => 'nullable|date|after:service_date',
            'odometer_reading'  => 'nullable|integer|min:0',
            'service_provider'  => 'nullable|string|max:200',
            'cost'              => 'required|numeric|min:0',
            'status'            => 'required|in:scheduled,in_progress,completed,cancelled',
            'notes'             => 'nullable|string|max:500',
        ]);

        $data['vehicle_id']  = $vehicle->id;
        $data['recorded_by'] = auth()->id();

        MaintenanceLog::create($data);

        // If maintenance just started, set vehicle status
        if ($data['status'] === 'in_progress') {
            $vehicle->update(['status' => 'maintenance']);
        }

        return redirect()->route('transport.vehicles.show', $id)
                         ->with('status', 'Maintenance log added.');
    }

    public function maintUpdate(Request $request, int $vehicleId, int $logId)
    {
        $log = MaintenanceLog::where('vehicle_id', $vehicleId)->findOrFail($logId);

        $data = $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'notes'  => 'nullable|string|max:500',
        ]);

        $log->update($data);

        // If completed, restore vehicle to active
        if ($data['status'] === 'completed') {
            Vehicle::where('id', $vehicleId)->where('status', 'maintenance')
                   ->update(['status' => 'active']);
        }

        return redirect()->route('transport.vehicles.show', $vehicleId)
                         ->with('status', 'Maintenance record updated.');
    }

    // ── GPS position update (webhook / API hook) ──────────────────────────────

    public function updateGps(Request $request, int $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $data = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $vehicle->update([
            'current_lat'    => $data['lat'],
            'current_lng'    => $data['lng'],
            'gps_updated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }
}
