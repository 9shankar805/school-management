<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorize('view inventory');

        $query = Warehouse::withCount(['assets', 'inventoryItems'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $warehouses = $query->paginate(25)->withQueryString();
        $types      = Warehouse::TYPES;

        return view('inventory.warehouses.index', compact('warehouses', 'types'));
    }

    public function create()
    {
        $this->authorize('manage inventory');

        return view('inventory.warehouses.create', ['types' => Warehouse::TYPES]);
    }

    public function store(Request $request)
    {
        $this->authorize('manage inventory');

        $data = $request->validate([
            'name'         => 'required|string|max:200',
            'code'         => 'required|string|max:30|unique:warehouses,code',
            'location'     => 'nullable|string|max:200',
            'manager_name' => 'nullable|string|max:150',
            'phone'        => 'nullable|string|max:30',
            'type'         => 'required|in:main,branch,classroom,lab,other',
            'status'       => 'required|in:active,inactive',
            'description'  => 'nullable|string|max:500',
        ]);

        $data['created_by'] = auth()->id();
        Warehouse::create($data);

        return redirect()->route('inventory.warehouses.index')
            ->with('status', 'Store / warehouse added.');
    }

    public function edit(int $id)
    {
        $this->authorize('manage inventory');
        $warehouse = Warehouse::findOrFail($id);

        return view('inventory.warehouses.edit', [
            'warehouse' => $warehouse,
            'types'     => Warehouse::TYPES,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('manage inventory');
        $warehouse = Warehouse::findOrFail($id);

        $data = $request->validate([
            'name'         => 'required|string|max:200',
            'code'         => 'required|string|max:30|unique:warehouses,code,' . $id,
            'location'     => 'nullable|string|max:200',
            'manager_name' => 'nullable|string|max:150',
            'phone'        => 'nullable|string|max:30',
            'type'         => 'required|in:main,branch,classroom,lab,other',
            'status'       => 'required|in:active,inactive',
            'description'  => 'nullable|string|max:500',
        ]);

        $warehouse->update($data);

        return redirect()->route('inventory.warehouses.index')
            ->with('status', 'Store updated.');
    }

    public function destroy(int $id)
    {
        $this->authorize('manage inventory');
        Warehouse::findOrFail($id)->delete();

        return back()->with('status', 'Store deleted.');
    }
}
