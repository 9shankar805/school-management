<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class InventoryItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorize('view inventory');

        $query = InventoryItem::with('warehouse', 'supplier')->latest();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->boolean('low_stock')) {
            $query->whereColumn('quantity_in_stock', '<=', 'reorder_level');
        }
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($qb) use ($q) {
                $qb->where('name', 'like', "%{$q}%")
                   ->orWhere('item_code', 'like', "%{$q}%");
            });
        }

        $items      = $query->paginate(25)->withQueryString();
        $categories = InventoryItem::CATEGORIES;
        $warehouses = Warehouse::where('status', 'active')->pluck('name', 'id');

        // Summary stats for alert banner
        $lowStockCount  = InventoryItem::where('status', 'active')
            ->whereColumn('quantity_in_stock', '<=', 'reorder_level')
            ->count();
        $outOfStockCount = InventoryItem::where('status', 'active')
            ->where('quantity_in_stock', 0)
            ->count();

        return view('inventory.items.index',
            compact('items', 'categories', 'warehouses', 'lowStockCount', 'outOfStockCount'));
    }

    public function create()
    {
        $this->authorize('manage inventory');

        return view('inventory.items.create', [
            'categories' => InventoryItem::CATEGORIES,
            'units'      => InventoryItem::UNITS,
            'warehouses' => Warehouse::where('status', 'active')->pluck('name', 'id'),
            'suppliers'  => Supplier::where('status', 'active')->pluck('name', 'id'),
            'nextCode'   => InventoryItem::nextCode(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('manage inventory');

        $data = $request->validate([
            'item_code'         => 'required|string|max:50|unique:inventory_items,item_code',
            'name'              => 'required|string|max:200',
            'category'          => 'required|string',
            'unit'              => 'required|string|max:20',
            'quantity_in_stock' => 'required|integer|min:0',
            'reorder_level'     => 'required|integer|min:0',
            'unit_price'        => 'nullable|numeric|min:0',
            'description'       => 'nullable|string|max:1000',
            'warehouse_id'      => 'nullable|exists:warehouses,id',
            'supplier_id'       => 'nullable|exists:suppliers,id',
            'status'            => 'required|in:active,inactive',
        ]);

        $data['created_by'] = auth()->id();
        InventoryItem::create($data);

        return redirect()->route('inventory.items.index')
            ->with('status', 'Consumable item added.');
    }

    public function edit(int $id)
    {
        $this->authorize('manage inventory');

        $item = InventoryItem::findOrFail($id);

        return view('inventory.items.edit', [
            'item'       => $item,
            'categories' => InventoryItem::CATEGORIES,
            'units'      => InventoryItem::UNITS,
            'warehouses' => Warehouse::where('status', 'active')->pluck('name', 'id'),
            'suppliers'  => Supplier::where('status', 'active')->pluck('name', 'id'),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('manage inventory');

        $item = InventoryItem::findOrFail($id);

        $data = $request->validate([
            'item_code'         => 'required|string|max:50|unique:inventory_items,item_code,' . $id,
            'name'              => 'required|string|max:200',
            'category'          => 'required|string',
            'unit'              => 'required|string|max:20',
            'quantity_in_stock' => 'required|integer|min:0',
            'reorder_level'     => 'required|integer|min:0',
            'unit_price'        => 'nullable|numeric|min:0',
            'description'       => 'nullable|string|max:1000',
            'warehouse_id'      => 'nullable|exists:warehouses,id',
            'supplier_id'       => 'nullable|exists:suppliers,id',
            'status'            => 'required|in:active,inactive',
        ]);

        $item->update($data);

        return redirect()->route('inventory.items.index')
            ->with('status', 'Item updated.');
    }

    public function destroy(int $id)
    {
        $this->authorize('manage inventory');
        InventoryItem::findOrFail($id)->delete();

        return back()->with('status', 'Item deleted.');
    }

    /** Quick-adjust stock (issue / receive without a full PO). */
    public function adjustStock(Request $request, int $id)
    {
        $this->authorize('manage inventory');

        $item = InventoryItem::findOrFail($id);

        $data = $request->validate([
            'adjustment' => 'required|integer',   // positive = add, negative = deduct
            'reason'     => 'nullable|string|max:300',
        ]);

        $newQty = max(0, $item->quantity_in_stock + (int) $data['adjustment']);
        $item->update(['quantity_in_stock' => $newQty]);

        return back()->with('status', "Stock adjusted. New quantity: {$newQty}.");
    }
}
