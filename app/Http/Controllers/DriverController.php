<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DriverController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:manage transport');
    }

    public function index(Request $request)
    {
        $query = Driver::with('currentVehicle');

        if ($search = $request->input('search')) {
            $query->search($search);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $drivers = $query->orderBy('name')->paginate(20)->withQueryString();

        // License expiry alerts (next 30 days or already expired)
        $licenseAlerts = Driver::where('license_expiry', '<=', now()->addDays(30))
                               ->where('status', 'active')
                               ->get();

        return view('transport.drivers.index', compact('drivers', 'licenseAlerts'));
    }

    public function create()
    {
        $vehicles = Vehicle::active()->whereDoesntHave('driver')->orderBy('name')->get();
        return view('transport.drivers.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'               => 'required|string|max:150',
            'employee_id'        => 'nullable|string|max:50|unique:drivers,employee_id',
            'phone'              => 'nullable|string|max:30',
            'email'              => 'nullable|email|max:150',
            'address'            => 'nullable|string|max:300',
            'date_of_birth'      => 'nullable|date|before:today',
            'joining_date'       => 'nullable|date',
            'license_number'     => 'required|string|max:100|unique:drivers,license_number',
            'license_type'       => 'nullable|string|max:50',
            'license_expiry'     => 'nullable|date',
            'national_id'        => 'nullable|string|max:100',
            'current_vehicle_id' => 'nullable|integer|exists:vehicles,id',
            'salary'             => 'nullable|numeric|min:0',
            'notes'              => 'nullable|string',
            'photo'              => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('drivers/photos', 'public');
        }
        unset($data['photo_file']);

        Driver::create($data);

        return redirect()->route('transport.drivers.index')
                         ->with('status', 'Driver added successfully.');
    }

    public function show(int $id)
    {
        $driver = Driver::with(['currentVehicle', 'routes'])->findOrFail($id);
        return view('transport.drivers.show', compact('driver'));
    }

    public function edit(int $id)
    {
        $driver   = Driver::findOrFail($id);
        $vehicles = Vehicle::active()
            ->where(function ($q) use ($driver) {
                $q->whereDoesntHave('driver')
                  ->orWhereHas('driver', fn($d) => $d->where('id', $driver->id));
            })
            ->orderBy('name')->get();

        return view('transport.drivers.edit', compact('driver', 'vehicles'));
    }

    public function update(Request $request, int $id)
    {
        $driver = Driver::findOrFail($id);

        $data = $request->validate([
            'name'               => 'required|string|max:150',
            'employee_id'        => 'nullable|string|max:50|unique:drivers,employee_id,' . $id,
            'phone'              => 'nullable|string|max:30',
            'email'              => 'nullable|email|max:150',
            'address'            => 'nullable|string|max:300',
            'date_of_birth'      => 'nullable|date|before:today',
            'joining_date'       => 'nullable|date',
            'license_number'     => 'required|string|max:100|unique:drivers,license_number,' . $id,
            'license_type'       => 'nullable|string|max:50',
            'license_expiry'     => 'nullable|date',
            'national_id'        => 'nullable|string|max:100',
            'current_vehicle_id' => 'nullable|integer|exists:vehicles,id',
            'status'             => 'required|in:active,on_leave,terminated',
            'salary'             => 'nullable|numeric|min:0',
            'notes'              => 'nullable|string',
            'photo'              => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            if ($driver->photo) Storage::disk('public')->delete($driver->photo);
            $data['photo'] = $request->file('photo')->store('drivers/photos', 'public');
        }
        unset($data['photo_file']);

        $driver->update($data);

        return redirect()->route('transport.drivers.show', $id)
                         ->with('status', 'Driver updated successfully.');
    }

    public function destroy(int $id)
    {
        $driver = Driver::withCount('routes')->findOrFail($id);

        if ($driver->routes_count > 0) {
            return redirect()->route('transport.drivers.index')
                             ->with('error', 'Cannot delete: driver is assigned to routes.');
        }

        if ($driver->photo) Storage::disk('public')->delete($driver->photo);
        $driver->delete();

        return redirect()->route('transport.drivers.index')
                         ->with('status', 'Driver removed.');
    }
}
