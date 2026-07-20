<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetMaintenanceLog;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorize('view inventory');

        $query = Asset::with('warehouse', 'supplier')->latest();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($qb) use ($q) {
                $qb->where('name', 'like', "%{$q}%")
                   ->orWhere('asset_code', 'like', "%{$q}%")
                   ->orWhere('serial_number', 'like', "%{$q}%");
            });
        }

        $assets     = $query->paginate(25)->withQueryString();
        $categories = Asset::CATEGORIES;
        $statuses   = Asset::STATUSES;
        $conditions = Asset::CONDITIONS;
        $warehouses = Warehouse::where('status', 'active')->pluck('name', 'id');

        return view('inventory.assets.index',
            compact('assets', 'categories', 'statuses', 'conditions', 'warehouses'));
    }

    public function create()
    {
        $this->authorize('manage inventory');

        return view('inventory.assets.create', [
            'categories' => Asset::CATEGORIES,
            'conditions' => Asset::CONDITIONS,
            'statuses'   => Asset::STATUSES,
            'warehouses' => Warehouse::where('status', 'active')->pluck('name', 'id'),
            'suppliers'  => Supplier::where('status', 'active')->pluck('name', 'id'),
            'nextCode'   => Asset::nextCode(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('manage inventory');

        $data = $request->validate([
            'asset_code'     => 'required|string|max:50|unique:assets,asset_code',
            'name'           => 'required|string|max:200',
            'category'       => 'required|string',
            'description'    => 'nullable|string|max:1000',
            'brand'          => 'nullable|string|max:100',
            'model'          => 'nullable|string|max:100',
            'serial_number'  => 'nullable|string|max:150',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_date'  => 'nullable|date',
            'warranty_expiry'=> 'nullable|date|after_or_equal:purchase_date',
            'current_value'  => 'nullable|numeric|min:0',
            'condition'      => 'required|in:new,good,fair,poor,damaged,disposed',
            'status'         => 'required|in:available,in_use,under_maintenance,disposed',
            'location'       => 'nullable|string|max:200',
            'assigned_to'    => 'nullable|string|max:200',
            'warehouse_id'   => 'nullable|exists:warehouses,id',
            'supplier_id'    => 'nullable|exists:suppliers,id',
            'image'          => 'nullable|file|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')
                ->store('inventory/assets', 'public');
        }

        $data['created_by'] = auth()->id();
        Asset::create($data);

        return redirect()->route('inventory.assets.index')
            ->with('status', 'Asset registered successfully.');
    }

    public function show(int $id)
    {
        $this->authorize('view inventory');

        $asset = Asset::with('warehouse', 'supplier', 'maintenanceLogs.performedBy')
            ->findOrFail($id);

        return view('inventory.assets.show', compact('asset'));
    }

    public function edit(int $id)
    {
        $this->authorize('manage inventory');

        $asset = Asset::findOrFail($id);

        return view('inventory.assets.edit', [
            'asset'      => $asset,
            'categories' => Asset::CATEGORIES,
            'conditions' => Asset::CONDITIONS,
            'statuses'   => Asset::STATUSES,
            'warehouses' => Warehouse::where('status', 'active')->pluck('name', 'id'),
            'suppliers'  => Supplier::where('status', 'active')->pluck('name', 'id'),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('manage inventory');

        $asset = Asset::findOrFail($id);

        $data = $request->validate([
            'asset_code'     => 'required|string|max:50|unique:assets,asset_code,' . $id,
            'name'           => 'required|string|max:200',
            'category'       => 'required|string',
            'description'    => 'nullable|string|max:1000',
            'brand'          => 'nullable|string|max:100',
            'model'          => 'nullable|string|max:100',
            'serial_number'  => 'nullable|string|max:150',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_date'  => 'nullable|date',
            'warranty_expiry'=> 'nullable|date',
            'current_value'  => 'nullable|numeric|min:0',
            'condition'      => 'required|in:new,good,fair,poor,damaged,disposed',
            'status'         => 'required|in:available,in_use,under_maintenance,disposed',
            'location'       => 'nullable|string|max:200',
            'assigned_to'    => 'nullable|string|max:200',
            'warehouse_id'   => 'nullable|exists:warehouses,id',
            'supplier_id'    => 'nullable|exists:suppliers,id',
            'image'          => 'nullable|file|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        if ($request->hasFile('image')) {
            if ($asset->image_path) {
                Storage::disk('public')->delete($asset->image_path);
            }
            $data['image_path'] = $request->file('image')
                ->store('inventory/assets', 'public');
        }

        $asset->update($data);

        return redirect()->route('inventory.assets.index')
            ->with('status', 'Asset updated.');
    }

    public function destroy(int $id)
    {
        $this->authorize('manage inventory');

        $asset = Asset::findOrFail($id);

        if ($asset->image_path) {
            Storage::disk('public')->delete($asset->image_path);
        }

        $asset->delete();

        return back()->with('status', 'Asset deleted.');
    }

    // ── Maintenance log actions ───────────────────────────────────────────────

    public function storeMaintenance(Request $request, int $assetId)
    {
        $this->authorize('manage inventory');

        $asset = Asset::findOrFail($assetId);

        $data = $request->validate([
            'type'             => 'required|in:preventive,corrective,inspection,upgrade,disposal',
            'maintenance_date' => 'required|date',
            'next_due_date'    => 'nullable|date|after:maintenance_date',
            'cost'             => 'nullable|numeric|min:0',
            'vendor'           => 'nullable|string|max:200',
            'status'           => 'required|in:scheduled,in_progress,completed,cancelled',
            'description'      => 'required|string|max:2000',
            'findings'         => 'nullable|string|max:2000',
        ]);

        $data['asset_id']    = $asset->id;
        $data['performed_by']= auth()->id();
        $data['created_by']  = auth()->id();

        AssetMaintenanceLog::create($data);

        // If maintenance is active, update asset status accordingly
        if (in_array($data['status'], ['scheduled', 'in_progress'])) {
            $asset->update(['status' => 'under_maintenance']);
        }

        return back()->with('status', 'Maintenance log added.');
    }

    public function destroyMaintenance(int $logId)
    {
        $this->authorize('manage inventory');

        AssetMaintenanceLog::findOrFail($logId)->delete();

        return back()->with('status', 'Maintenance log removed.');
    }
}
