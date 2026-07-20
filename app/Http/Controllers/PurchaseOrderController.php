<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorize('view inventory');

        $query = PurchaseOrder::with('supplier', 'createdBy')->latest('order_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('from')) {
            $query->whereDate('order_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('order_date', '<=', $request->to);
        }

        $orders    = $query->paginate(25)->withQueryString();
        $suppliers = Supplier::where('status', 'active')->pluck('name', 'id');
        $statuses  = PurchaseOrder::STATUSES;

        $monthTotal = PurchaseOrder::where('status', '!=', 'cancelled')
            ->whereMonth('order_date', now()->month)
            ->whereYear('order_date', now()->year)
            ->sum('total_amount');

        return view('inventory.purchase-orders.index',
            compact('orders', 'suppliers', 'statuses', 'monthTotal'));
    }

    public function create()
    {
        $this->authorize('create purchase orders');

        return view('inventory.purchase-orders.create', [
            'suppliers'      => Supplier::where('status', 'active')->pluck('name', 'id'),
            'paymentMethods' => PurchaseOrder::PAYMENT_METHODS,
            'inventoryItems' => InventoryItem::where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'item_code', 'unit', 'unit_price']),
            'nextPo'         => PurchaseOrder::nextPoNumber(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create purchase orders');

        $data = $request->validate([
            'po_number'         => 'required|string|max:50|unique:purchase_orders,po_number',
            'supplier_id'       => 'nullable|exists:suppliers,id',
            'order_date'        => 'required|date',
            'expected_delivery' => 'nullable|date|after_or_equal:order_date',
            'payment_method'    => 'nullable|string',
            'reference_no'      => 'nullable|string|max:100',
            'notes'             => 'nullable|string|max:1000',
            'items'             => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:200',
            'items.*.item_type' => 'required|in:consumable,asset',
            'items.*.inventory_item_id' => 'nullable|exists:inventory_items,id',
            'items.*.quantity'  => 'required|integer|min:1',
            'items.*.unit_price'=> 'required|numeric|min:0',
            'items.*.notes'     => 'nullable|string|max:300',
        ]);

        $data['created_by'] = auth()->id();
        $data['status']     = 'draft';

        $order = PurchaseOrder::create($data);

        foreach ($data['items'] as $line) {
            $order->items()->create($line);
        }

        $order->recalculateTotal();

        return redirect()->route('inventory.purchase-orders.show', $order->id)
            ->with('status', 'Purchase order created.');
    }

    public function show(int $id)
    {
        $this->authorize('view inventory');

        $order = PurchaseOrder::with('supplier', 'items.inventoryItem', 'createdBy', 'approvedBy')
            ->findOrFail($id);

        return view('inventory.purchase-orders.show', compact('order'));
    }

    public function edit(int $id)
    {
        $this->authorize('create purchase orders');

        $order = PurchaseOrder::with('items')->findOrFail($id);

        if (!in_array($order->status, ['draft', 'submitted'])) {
            return back()->with('error', 'Only draft or submitted orders can be edited.');
        }

        return view('inventory.purchase-orders.edit', [
            'order'          => $order,
            'suppliers'      => Supplier::where('status', 'active')->pluck('name', 'id'),
            'paymentMethods' => PurchaseOrder::PAYMENT_METHODS,
            'inventoryItems' => InventoryItem::where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'item_code', 'unit', 'unit_price']),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('create purchase orders');

        $order = PurchaseOrder::findOrFail($id);

        if (!in_array($order->status, ['draft', 'submitted'])) {
            return back()->with('error', 'Cannot edit this order.');
        }

        $data = $request->validate([
            'supplier_id'       => 'nullable|exists:suppliers,id',
            'order_date'        => 'required|date',
            'expected_delivery' => 'nullable|date|after_or_equal:order_date',
            'payment_method'    => 'nullable|string',
            'reference_no'      => 'nullable|string|max:100',
            'notes'             => 'nullable|string|max:1000',
            'items'             => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:200',
            'items.*.item_type' => 'required|in:consumable,asset',
            'items.*.inventory_item_id' => 'nullable|exists:inventory_items,id',
            'items.*.quantity'  => 'required|integer|min:1',
            'items.*.unit_price'=> 'required|numeric|min:0',
            'items.*.notes'     => 'nullable|string|max:300',
        ]);

        $order->update($data);
        $order->items()->delete();

        foreach ($data['items'] as $line) {
            $order->items()->create($line);
        }

        $order->recalculateTotal();

        return redirect()->route('inventory.purchase-orders.show', $order->id)
            ->with('status', 'Purchase order updated.');
    }

    public function destroy(int $id)
    {
        $this->authorize('manage inventory');

        $order = PurchaseOrder::findOrFail($id);

        if ($order->status === 'received') {
            return back()->with('error', 'Cannot delete a received order.');
        }

        $order->items()->delete();
        $order->delete();

        return redirect()->route('inventory.purchase-orders.index')
            ->with('status', 'Purchase order deleted.');
    }

    /** Advance the PO status through the workflow. */
    public function updateStatus(Request $request, int $id)
    {
        $this->authorize('manage inventory');

        $order = PurchaseOrder::findOrFail($id);

        $data = $request->validate([
            'status'         => 'required|in:submitted,approved,received,cancelled',
            'delivered_date' => 'nullable|date',
        ]);

        $order->update($data);

        // When received, bump stock for each consumable line item
        if ($data['status'] === 'received') {
            foreach ($order->items as $line) {
                if ($line->item_type === 'consumable' && $line->inventory_item_id) {
                    $line->inventoryItem->increment('quantity_in_stock', $line->quantity);
                }
            }
        }

        return back()->with('status', 'Order status updated to ' . ucfirst($data['status']) . '.');
    }
}
