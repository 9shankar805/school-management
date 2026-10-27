<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\StockTransaction;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InventoryReportExport;

class InventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:manage inventory');
    }

    // ── Dashboard ─────────────────────────────────────────────────────────────

    public function index()
    {
        try {
            $totalAssets      = Asset::count();
            $availableAssets  = Asset::where('status', 'available')->count();
            $assignedAssets   = Asset::where('status', 'assigned')->count();
            $maintenanceAssets = Asset::where('status', 'maintenance')->count();

            $totalItems       = InventoryItem::count();
            $lowStockItems    = InventoryItem::active()->lowStock()->count();
            $outOfStockItems  = InventoryItem::where('quantity_in_stock', 0)->count();

            $totalSuppliers   = Supplier::count();
            $pendingPOs       = PurchaseOrder::whereIn('status', ['pending', 'approved', 'ordered'])->count();

            // Asset category breakdown
            $assetsByCategory = AssetCategory::withCount('assets')
                ->where('type', 'asset')->orderByDesc('assets_count')->get();

            // Low stock list
            $lowStockList = InventoryItem::active()->lowStock()
                ->with('category')
                ->orderBy('quantity_in_stock')
                ->take(10)->get();

            // Recent stock transactions
            $recentTransactions = StockTransaction::with(['inventoryItem', 'processedByUser'])
                ->latest('transacted_at')->take(10)->get();

            // Recent POs
            $recentPOs = PurchaseOrder::with('supplier')
                ->latest('order_date')->take(5)->get();

            // Warranty expiry alerts
            $warrantyAlerts = Asset::whereNotNull('warranty_expiry')
                ->where('warranty_expiry', '<=', now()->addDays(30))
                ->where('status', '!=', 'disposed')
                ->with('category')->take(8)->get();

            return view('inventory.dashboard', compact(
                'totalAssets', 'availableAssets', 'assignedAssets', 'maintenanceAssets',
                'totalItems', 'lowStockItems', 'outOfStockItems',
                'totalSuppliers', 'pendingPOs',
                'assetsByCategory', 'lowStockList', 'recentTransactions',
                'recentPOs', 'warrantyAlerts'
            ));
        } catch (\Exception $e) {
            return view('inventory.dashboard', [
                'totalAssets' => 0, 'availableAssets' => 0, 'assignedAssets' => 0, 'maintenanceAssets' => 0,
                'totalItems' => 0, 'lowStockItems' => 0, 'outOfStockItems' => 0,
                'totalSuppliers' => 0, 'pendingPOs' => 0,
                'assetsByCategory' => collect(), 'lowStockList' => collect(), 'recentTransactions' => collect(),
                'recentPOs' => collect(), 'warrantyAlerts' => collect(),
                'error' => 'Unable to load inventory dashboard: ' . $e->getMessage()
            ]);
        }
    }

    // ── Reports ───────────────────────────────────────────────────────────────

    public function reportForm()
    {
        $categories = AssetCategory::orderBy('name')->get();
        $suppliers  = Supplier::active()->orderBy('name')->get();
        return view('inventory.reports', compact('categories', 'suppliers'));
    }

    public function reportExport(Request $request)
    {
        $data = $request->validate([
            'report_type' => 'required|in:assets,stock,suppliers,low_stock,transactions,purchase_orders',
            'format'      => 'required|in:pdf,excel',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date|after_or_equal:date_from',
            'category_id' => 'nullable|integer',
        ]);

        $type     = $data['report_type'];
        $format   = $data['format'];
        $dateFrom = $data['date_from'] ?? null;
        $dateTo   = $data['date_to']   ?? null;
        $catId    = $data['category_id'] ?? null;

        $records = match ($type) {
            'assets' => Asset::with(['category','supplier','assignedUser'])
                ->when($catId, fn($q) => $q->where('category_id', $catId))
                ->orderBy('name')->get(),

            'stock' => InventoryItem::with(['category','supplier'])
                ->when($catId, fn($q) => $q->where('category_id', $catId))
                ->orderBy('name')->get(),

            'suppliers' => Supplier::withCount(['assets','inventoryItems','purchaseOrders'])
                ->orderBy('name')->get(),

            'low_stock' => InventoryItem::active()->lowStock()
                ->with(['category','supplier'])->orderBy('quantity_in_stock')->get(),

            'transactions' => StockTransaction::with(['inventoryItem','processedByUser'])
                ->when($dateFrom, fn($q) => $q->where('transacted_at', '>=', $dateFrom))
                ->when($dateTo,   fn($q) => $q->where('transacted_at', '<=', $dateTo . ' 23:59:59'))
                ->latest('transacted_at')->get(),

            'purchase_orders' => PurchaseOrder::with(['supplier','raisedBy'])
                ->when($dateFrom, fn($q) => $q->where('order_date', '>=', $dateFrom))
                ->when($dateTo,   fn($q) => $q->where('order_date', '<=', $dateTo))
                ->latest('order_date')->get(),
        };

        $title = ucwords(str_replace('_', ' ', $type)) . ' Report';

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('inventory.reports-pdf', compact('records', 'type', 'title', 'dateFrom', 'dateTo'))
                      ->setPaper('a4', 'landscape');
            return $pdf->download("inventory-{$type}-report.pdf");
        }

        return Excel::download(
            new InventoryReportExport($records, $type),
            "inventory-{$type}-report.xlsx"
        );
    }

    // ── Management Views ──────────────────────────────────────────────────────

    public function items()
    {
        try {
            $items = InventoryItem::with('category')->paginate(15);
            return view('inventory.items', compact('items'));
        } catch (\Exception $e) {
            return view('inventory.items', ['items' => null, 'error' => 'Unable to load items: ' . $e->getMessage()]);
        }
    }

    public function createItem()
    {
        try {
            $categories = AssetCategory::orderBy('name')->get();
            return view('inventory.items-create', compact('categories'));
        } catch (\Exception $e) {
            return view('inventory.items-create', ['categories' => collect(), 'error' => 'Unable to load categories: ' . $e->getMessage()]);
        }
    }

    public function storeItem(Request $request)
    {
        try {
            $data = $request->validate([
                'name'             => 'required|string|max:255',
                'sku'              => 'nullable|string|max:100',
                'category_id'     => 'nullable|exists:asset_categories,id',
                'unit'            => 'required|string|max:50',
                'quantity_in_stock'=> 'required|integer|min:0',
                'reorder_level'   => 'required|integer|min:0',
                'unit_price'      => 'nullable|numeric|min:0',
                'location'        => 'nullable|string|max:255',
            ]);

            InventoryItem::create(array_merge($data, ['is_active' => true]));
            return redirect()->route('inventory.items')->with('success', 'Item added successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Unable to add item: ' . $e->getMessage())->withInput();
        }
    }

    public function assets()
    {
        try {
            $assets = Asset::with('category')->paginate(15);
            return view('inventory.assets', compact('assets'));
        } catch (\Exception $e) {
            return view('inventory.assets', ['assets' => null, 'error' => 'Unable to load assets: ' . $e->getMessage()]);
        }
    }

    public function createAsset()
    {
        try {
            $categories = AssetCategory::orderBy('name')->get();
            $suppliers  = Supplier::orderBy('name')->get();
            return view('inventory.assets-create', compact('categories', 'suppliers'));
        } catch (\Exception $e) {
            return view('inventory.assets-create', [
                'categories' => collect(),
                'suppliers'  => collect(),
                'error'      => 'Unable to load form data: ' . $e->getMessage(),
            ]);
        }
    }

    public function storeAsset(Request $request)
    {
        try {
            $data = $request->validate([
                'name'           => 'required|string|max:255',
                'asset_tag'      => 'required|string|max:100|unique:assets,asset_tag',
                'category_id'    => 'required|exists:asset_categories,id',
                'status'         => 'required|in:available,assigned,maintenance,disposed',
                'condition'      => 'nullable|in:new,good,fair,poor,damaged',
                'brand'          => 'nullable|string|max:100',
                'model_number'   => 'nullable|string|max:100',
                'serial_number'  => 'nullable|string|max:150',
                'location'       => 'nullable|string|max:255',
                'supplier_id'    => 'nullable|exists:suppliers,id',
                'purchase_date'  => 'nullable|date',
                'purchase_price' => 'nullable|numeric|min:0',
                'warranty_expiry'=> 'nullable|date',
                'description'    => 'nullable|string|max:1000',
            ]);

            Asset::create($data);
            return redirect()->route('inventory.assets')->with('success', 'Asset registered successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Unable to register asset: ' . $e->getMessage())->withInput();
        }
    }

    public function suppliers()
    {
        try {
            $suppliers = Supplier::paginate(15);
            return view('inventory.suppliers', compact('suppliers'));
        } catch (\Exception $e) {
            return view('inventory.suppliers', ['suppliers' => null, 'error' => 'Unable to load suppliers: ' . $e->getMessage()]);
        }
    }

    public function createSupplier()
    {
        return view('inventory.suppliers-create');
    }

    public function storeSupplier(Request $request)
    {
        try {
            $data = $request->validate([
                'name'           => 'required|string|max:255',
                'contact_person' => 'nullable|string|max:255',
                'email'          => 'nullable|email',
                'phone'          => 'nullable|string|max:50',
                'address'        => 'nullable|string'
            ]);
            Supplier::create(array_merge($data, ['status' => 'active']));
            return redirect()->route('inventory.suppliers')->with('success', 'Supplier created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Unable to create supplier: ' . $e->getMessage())->withInput();
        }
    }

    public function updateSupplier(Request $request, int $id)
    {
        try {
            $supplier = Supplier::findOrFail($id);
            $data = $request->validate([
                'name'           => 'required|string|max:255',
                'contact_person' => 'nullable|string|max:255',
                'email'          => 'nullable|email',
                'phone'          => 'nullable|string|max:50',
                'address'        => 'nullable|string'
            ]);
            $supplier->update($data);
            return redirect()->route('inventory.suppliers')->with('success', 'Supplier updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Unable to update supplier: ' . $e->getMessage())->withInput();
        }
    }

    public function destroySupplier(int $id)
    {
        try {
            Supplier::findOrFail($id)->delete();
            return redirect()->route('inventory.suppliers')->with('success', 'Supplier deleted.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Unable to delete supplier: ' . $e->getMessage());
        }
    }

    public function purchaseOrders()
    {
        try {
            $orders    = PurchaseOrder::with('supplier')->orderBy('created_at', 'desc')->paginate(15);
            $suppliers = Supplier::orderBy('name')->get();
            return view('inventory.purchase-orders', compact('orders', 'suppliers'));
        } catch (\Exception $e) {
            return view('inventory.purchase-orders', ['orders' => collect(), 'suppliers' => collect(), 'error' => 'Unable to load purchase orders: ' . $e->getMessage()]);
        }
    }

    public function createPurchaseOrder()
    {
        try {
            $suppliers = Supplier::all();
            return view('inventory.purchase-orders-create', compact('suppliers'));
        } catch (\Exception $e) {
            return view('inventory.purchase-orders-create', ['suppliers' => collect(), 'error' => 'Unable to load suppliers: ' . $e->getMessage()]);
        }
    }

    public function storePurchaseOrder(Request $request)
    {
        try {
            $data = $request->validate([
                'supplier_id'             => 'required|exists:suppliers,id',
                'po_number'               => 'required|string|max:100|unique:purchase_orders,po_number',
                'order_date'              => 'required|date',
                'total_amount'            => 'required|numeric|min:0',
                'expected_delivery_date'  => 'nullable|date|after_or_equal:order_date',
                'notes'                   => 'nullable|string|max:1000',
            ]);
            $data['status']     = 'draft';
            $data['raised_by']  = auth()->id();
            PurchaseOrder::create($data);
            return redirect()->route('inventory.purchase-orders')->with('success', 'Purchase order created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Unable to create purchase order: ' . $e->getMessage())->withInput();
        }
    }
}
